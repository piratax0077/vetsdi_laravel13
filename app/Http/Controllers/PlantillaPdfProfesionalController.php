<?php

namespace App\Http\Controllers;

use App\Models\PlantillaPdfProfesional;
use App\Models\Profesional;
use App\Services\SvgDocumentoVeterinario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

// Pestañas "Plantilla receta" y "Plantilla de exámenes" de Configurar emisión de documentos:
// guarda cómo se ve el PDF y arma la vista previa con datos de ejemplo
class PlantillaPdfProfesionalController extends Controller
{
    private function profesional(Request $request): Profesional
    {
        return Profesional::where('id_usuario', $request->user()->id)->firstOrFail();
    }

    // Reglas de los campos del formulario; se usan para guardar y para la vista previa
    private function validar(Request $request, string $tipo): array
    {
        $secciones = array_keys(PlantillaPdfProfesional::SECCIONES[$tipo]);

        $datos = $request->validate([
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'texto_marca' => 'nullable|string|max:40',
            'posicion_logo' => 'required|in:izquierda,centro,derecha',
            'titulo' => 'required|string|max:80',
            'formato' => 'required|in:tabla,lista',
            'tamano_letra' => 'required|in:pequena,normal,grande',
            'mostrar' => 'nullable|array',
            'mostrar.*' => 'in:'.implode(',', $secciones),
            'pie' => 'nullable|string|max:300',
            'logo' => 'nullable|string|max:2800000',
            'quitar_logo' => 'nullable|boolean',
        ], [
            'color.regex' => 'Elige un color válido.',
            'titulo.required' => 'Escribe el título del documento.',
            'logo.max' => 'El logo no puede superar los 2 MB.',
        ]);

        $elegidas = $datos['mostrar'] ?? [];
        $datos['mostrar'] = array_combine($secciones, array_map(fn ($seccion) => in_array($seccion, $elegidas, true), $secciones));
        $datos['texto_marca'] = trim((string) ($datos['texto_marca'] ?? ''));
        $datos['pie'] = trim((string) ($datos['pie'] ?? ''));

        return $datos;
    }

    public function guardar(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, PlantillaPdfProfesional::TIPOS, true), 404);
        $datos = $this->validar($request, $tipo);

        $plantilla = PlantillaPdfProfesional::firstOrNew([
            'id_profesional' => $this->profesional($request)->id,
            'tipo' => $tipo,
        ]);
        $logoAnterior = $plantilla->logo_ruta;

        if (!empty($datos['logo'])) {
            $plantilla->logo_ruta = $this->guardarLogo($datos['logo']);
        } elseif ($request->boolean('quitar_logo')) {
            $plantilla->logo_ruta = null;
        }

        $plantilla->configuracion = collect($datos)->except(['logo', 'quitar_logo'])->all();
        $plantilla->save();

        if ($logoAnterior && $logoAnterior !== $plantilla->logo_ruta) {
            Storage::disk('local')->delete($logoAnterior);
        }

        return response()->json(['estado' => 1, 'msj' => 'Plantilla guardada. Los próximos PDF saldrán con este formato.']);
    }

    // Vuelve a los valores de siempre y borra el logo propio
    public function restablecer(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, PlantillaPdfProfesional::TIPOS, true), 404);
        $plantilla = PlantillaPdfProfesional::where('id_profesional', $this->profesional($request)->id)
            ->where('tipo', $tipo)->first();

        if ($plantilla) {
            if ($plantilla->logo_ruta) {
                Storage::disk('local')->delete($plantilla->logo_ruta);
            }
            $plantilla->delete();
        }

        return response()->json([
            'estado' => 1,
            'msj' => 'Plantilla restablecida.',
            'configuracion' => PlantillaPdfProfesional::predeterminada($tipo),
        ]);
    }

    // Vista previa con lo que hay en el formulario (aún sin guardar): HTML para el panel o PDF real
    public function vistaPrevia(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, PlantillaPdfProfesional::TIPOS, true), 404);
        $datos = $this->validar($request, $tipo);
        $profesional = $this->profesional($request);

        $configuracion = array_merge(PlantillaPdfProfesional::predeterminada($tipo), collect($datos)->except(['logo', 'quitar_logo'])->all());
        if (!empty($datos['logo'])) {
            $configuracion['logo'] = $this->validarImagenLogo($datos['logo']);
        } elseif (!$request->boolean('quitar_logo')) {
            $configuracion['logo'] = PlantillaPdfProfesional::paraPdf($profesional->id, $tipo)['logo'];
        }

        [$titulo, $cuerpo, $plantillaVista] = $tipo === 'receta'
            ? $this->ejemploReceta($profesional, $configuracion)
            : $this->ejemploExamenes($profesional, $configuracion);

        $date = date('Y-m-d');
        $html = View::make('PDF.'.$plantillaVista, compact('date', 'titulo', 'cuerpo'))->render();

        if ($request->input('salida') === 'pdf') {
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($html);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

            return $pdf->stream('Vista previa '.$tipo.'.pdf', ['Attachment' => 0]);
        }

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function nombreProfesional(Profesional $profesional): string
    {
        return trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos);
    }

    private function ejemploReceta(Profesional $profesional, array $configuracion): array
    {
        $qr = GeneradorQrController::generar(config('app.url'));
        $receta = (object) [
            'paciente' => (object) ['nombres' => 'Camila', 'apellido_uno' => 'Rojas', 'apellido_dos' => 'Muñoz', 'rut' => '12.345.678-9', 'telefono_uno' => '+56 9 1234 5678'],
            'profesional' => $profesional,
            'lugar_atencion' => (object) ['nombre' => 'Centro veterinario de ejemplo'],
            'control' => 1,
            'detalle' => [
                ['producto' => 'Amoxicilina 250 mg', 'farmaco' => 'Amoxicilina', 'presentacion' => 'Comprimidos', 'posologia' => '1 comprimido cada 12 horas con comida', 'via_administracion' => 'Oral', 'periodo' => '7 días', 'cantidad_compra' => '1 caja'],
                ['producto' => 'Meloxicam 1,5 mg/ml', 'farmaco' => 'Meloxicam', 'presentacion' => 'Suspensión oral', 'posologia' => '0,1 mg/kg cada 24 horas', 'via_administracion' => 'Oral', 'periodo' => '5 días', 'cantidad_compra' => '1 frasco'],
            ],
            'qr' => (object) ['documento' => $qr, 'token' => 'EJEMPLO-'.Str::upper(Str::random(12))],
            'qr_prof' => (object) ['profesional' => $qr],
            'ficha_atencion' => (object) ['id' => 0],
        ];
        $mascota = (object) [
            'nombre' => 'Luna', 'especie' => 'Canina', 'sexo' => 'H', 'chip' => '900123456789012',
            'especieMascota' => (object) ['nombre' => 'Canina'],
            'razaMascota' => (object) ['nombre' => 'Labrador retriever'],
        ];

        $cuerpo = [
            'recomendacion' => collect([$receta]),
            'cantidad_recetas' => 1,
            'fecha_atencion' => now(),
            'mascota' => $mascota,
            'controles_receta' => [1 => 'Receta simple'],
            'plantilla' => $configuracion,
        ];

        return ['RECETA VETERINARIA', $cuerpo, 'pdf_receta_veterinaria'];
    }

    private function ejemploExamenes(Profesional $profesional, array $configuracion): array
    {
        $qr = GeneradorQrController::generar(config('app.url'));
        $cuerpo = [
            'array_ficha_atencion' => ['created_at' => now()->format('d-m-Y H:i'), 'qr' => $qr, 'token' => 'EJEMPLO-'.Str::upper(Str::random(12))],
            'array_lugar_atencion' => ['nombre' => 'Centro veterinario de ejemplo', 'direccion' => 'Av. Ejemplo 123, Santiago'],
            'array_profesional' => [
                'nombre' => $this->nombreProfesional($profesional),
                'especialidad' => '',
                'rut' => $profesional->rut,
                'num_colegio' => $profesional->num_colegio,
                'qr' => $qr,
            ],
            'array_paciente' => ['nombre' => 'Luna (Camila Rojas Muñoz)', 'rut' => '12.345.678-9', 'sexo' => 'Hembra', 'direccion' => 'Av. Ejemplo 456, Santiago'],
            'detalle_orden' => [
                'Laboratorio clínico' => [
                    ['examen' => 'Hemograma completo', 'sospecha' => 'Control prequirúrgico', 'contraste' => false, 'prioridad' => 'Normal', 'otro' => '', 'codigo' => '0301045'],
                    ['examen' => 'Perfil bioquímico', 'sospecha' => '', 'contraste' => false, 'prioridad' => 'Urgente', 'otro' => '', 'codigo' => '0301075'],
                ],
            ],
            'plantilla' => $configuracion,
        ];

        return ['Solicitud de Exámenes: Laboratorio clínico', $cuerpo, 'pdf_orden_examen'];
    }

    // Revisa que el logo sea una imagen válida y lo devuelve listo para el PDF (sin guardarlo)
    private function validarImagenLogo(string $logo): string
    {
        abort_unless(preg_match('~\Adata:image/(png|jpeg|svg\+xml|webp);base64,([A-Za-z0-9+/=]+)\z~', $logo, $partes), 422, 'Selecciona un logo PNG, JPG, SVG o WebP.');
        $bytes = base64_decode($partes[2], true);
        abort_if(!$bytes, 422, 'El logo seleccionado no es válido.');
        abort_if(strlen($bytes) > 2 * 1024 * 1024, 422, 'El logo no puede superar los 2 MB.');

        if ($partes[1] === 'svg+xml') {
            $bytes = app(SvgDocumentoVeterinario::class)->limpiar($bytes);
            abort_unless($bytes, 422, 'El logo seleccionado no es válido.');

            return 'data:image/svg+xml;base64,'.base64_encode($bytes);
        }
        abort_unless((@getimagesizefromstring($bytes)['mime'] ?? null) === 'image/'.$partes[1], 422, 'El logo seleccionado no es válido.');

        return $logo;
    }

    private function guardarLogo(string $logo): string
    {
        $limpio = $this->validarImagenLogo($logo);
        preg_match('~\Adata:image/(png|jpeg|svg\+xml|webp);base64,(.+)\z~', $limpio, $partes);
        $formato = $partes[1] === 'svg+xml' ? 'svg' : $partes[1];

        $ruta = 'plantillas-pdf-profesional/logos/'.Str::uuid().'.'.$formato;
        abort_unless(Storage::disk('local')->put($ruta, base64_decode($partes[2])), 500, 'No se pudo guardar el logo.');

        return $ruta;
    }
}
