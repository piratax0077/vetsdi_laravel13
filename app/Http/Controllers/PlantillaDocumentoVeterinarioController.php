<?php

namespace App\Http\Controllers;

use App\Models\{DocumentoFcPaciente, FichaAtencion, HoraMedica, LugarAtencion, Mascota, Paciente, PlantillaDocumentoVeterinario, Profesional};
use App\Services\{AccesoAgendaVeterinaria, ContenidoDocumentoVeterinario, DocumentoVeterinarioEmitido, SvgDocumentoVeterinario};
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlantillaDocumentoVeterinarioController extends Controller
{
    private function profesional(Request $request): Profesional
    {
        return Profesional::where('id_usuario', $request->user()->id)->firstOrFail();
    }

    private function propia(Request $request, PlantillaDocumentoVeterinario $plantilla): void
    {
        abort_unless((int) $plantilla->id_profesional === (int) $this->profesional($request)->id, 404);
    }

    private function autorizarUso(Profesional $profesional, PlantillaDocumentoVeterinario $plantilla, ?int $lugar): void
    {
        abort_unless((int) $plantilla->id_profesional === (int) $profesional->id
            || ($plantilla->activo && $plantilla->alcance === 'compartida' && $lugar
                && (int) $plantilla->id_lugar_atencion === $lugar
                && PlantillaDocumentoVeterinario::profesionalesDelLugar($lugar)
                    ->whereIn('id_profesional', [$profesional->id, $plantilla->id_profesional])
                    ->distinct()->count('id_profesional') === 2), 404);
    }

    public function index(Request $request)
    {
        $profesional = $this->profesional($request);
        $lugar = $request->integer('id_lugar_atencion') ?: null;
        abort_if($lugar && !PlantillaDocumentoVeterinario::lugaresDelProfesional($profesional->id)
            ->where('id_lugar_atencion', $lugar)->exists(), 404);

        $secciones = config('plantillas_documentos_veterinarios');
        $registros = PlantillaDocumentoVeterinario::delProfesional($profesional->id, $lugar, true);
        foreach ($secciones as $clave => &$seccion) {
            $seccion['registros'] = $registros[$clave];
            $seccion['categorias_filtro'] = array_values(array_unique(array_column($registros[$clave], 'categoria')));
        }
        if ($request->expectsJson()) {
            return response()->json(['estado' => 1, 'msj' => 'Plantillas cargadas.', 'secciones' => $secciones]);
        }
        return view('app.profesional.indicaciones_medicas', compact('secciones', 'lugar'));
    }

    public function guardar(Request $request, ?PlantillaDocumentoVeterinario $plantilla = null)
    {
        if ($plantilla?->exists) {
            $this->propia($request, $plantilla);
        } else {
            $plantilla = new PlantillaDocumentoVeterinario();
            $plantilla->id_profesional = $this->profesional($request)->id;
        }
        $datos = $request->validate([
            'seccion' => 'required|in:indicaciones,consentimientos',
            'titulo' => 'required|string|max:150',
            'categoria' => 'required|string|max:100',
            'tipo' => 'required|in:texto,archivo',
            'alcance' => 'required|in:personal,compartida',
            'id_lugar_atencion' => 'sometimes|nullable|integer|min:1',
            'contenido' => 'nullable|string|max:200000',
            'logo' => 'nullable|string|max:2800000',
            'adjunto' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg,gif,webp,svg|max:5120',
        ], [
            'adjunto.mimes' => 'Selecciona un archivo PDF, DOC, DOCX o una imagen compatible.',
            'adjunto.max' => 'El archivo no puede superar los 5 MB.',
            'logo.max' => 'El logo no puede superar los 2 MB.',
        ]);
        $lugares = PlantillaDocumentoVeterinario::lugaresDelProfesional($plantilla->id_profesional);
        if (array_key_exists('id_lugar_atencion', $datos)) {
            $lugar = $datos['id_lugar_atencion'];
            abort_if($lugar && !$lugares->where('id_lugar_atencion', $lugar)->exists(), 422, 'Selecciona un lugar de atención asociado al profesional.');
            $plantilla->id_lugar_atencion = $lugar;
        } elseif ($datos['alcance'] === 'compartida' && !$plantilla->id_lugar_atencion) {
            $ids = $lugares->distinct()->limit(2)->pluck('id_lugar_atencion');
            $plantilla->id_lugar_atencion = $ids->count() === 1 ? $ids->first() : null;
        }

        $rutaAnterior = $plantilla->archivo_ruta;
        $logoAnterior = $plantilla->logo_ruta;
        $plantilla->fill(collect($datos)->only(['titulo', 'categoria', 'tipo', 'alcance'])->all());
        $plantilla->seccion = array_search($datos['seccion'], PlantillaDocumentoVeterinario::SECCIONES, true);
        if ($datos['tipo'] === 'texto') {
            $plantilla->contenido = app(ContenidoDocumentoVeterinario::class)->limpiar($datos['contenido'] ?? '');
            abort_if(trim(strip_tags($plantilla->contenido)) === '', 422, 'Escribe el contenido del documento.');
            if (array_key_exists('logo', $datos)) {
                $plantilla->logo_ruta = $datos['logo'] ? $this->guardarLogo($datos['logo']) : null;
            }
            $plantilla->archivo_ruta = $plantilla->archivo_nombre = $plantilla->archivo_mime = $plantilla->archivo_peso = null;
        } else {
            $plantilla->contenido = $plantilla->logo_ruta = null;
            if ($adjunto = $request->file('adjunto')) {
                $mime = $adjunto->getMimeType();
                $peso = $adjunto->getSize();
                if ($mime === 'image/svg+xml') {
                    $svg = app(SvgDocumentoVeterinario::class)->limpiar($adjunto->getContent());
                    abort_unless($svg, 422, 'La imagen SVG no es válida.');
                    $ruta = 'plantillas-veterinarias/'.Str::uuid().'.svg';
                    abort_unless(Storage::disk('local')->put($ruta, $svg), 500, 'No se pudo guardar la imagen.');
                    $plantilla->archivo_ruta = $ruta;
                    $peso = strlen($svg);
                } else {
                    $plantilla->archivo_ruta = $adjunto->store('plantillas-veterinarias', 'local');
                }
                abort_unless($plantilla->archivo_ruta, 500, 'No se pudo guardar el archivo.');
                $plantilla->archivo_nombre = mb_substr($adjunto->getClientOriginalName(), 0, 255);
                $plantilla->archivo_mime = $mime;
                $plantilla->archivo_peso = $peso;
            }
            abort_unless($plantilla->archivo_ruta, 422, 'Selecciona un archivo.');
        }
        try {
            $plantilla->saveOrFail();
        } catch (\Throwable $error) {
            $this->borrarArchivoReemplazado($plantilla->archivo_ruta, $rutaAnterior);
            $this->borrarArchivoReemplazado($plantilla->logo_ruta, $logoAnterior);
            throw $error;
        }
        $this->borrarArchivoReemplazado($rutaAnterior, $plantilla->archivo_ruta);
        $this->borrarArchivoReemplazado($logoAnterior, $plantilla->logo_ruta);
        return response()->json(['estado' => 1, 'msj' => 'Plantilla guardada.', 'plantilla' => $plantilla->paraVista()]);
    }

    private function guardarLogo(string $logo): string
    {
        abort_unless(preg_match('~\Adata:image/(png|jpeg|svg\+xml|webp);base64,([A-Za-z0-9+/=]+)\z~', $logo, $partes), 422, 'Selecciona un logo PNG, JPG, SVG o WebP.');
        $bytes = base64_decode($partes[2], true);
        abort_if(!$bytes, 422, 'El logo seleccionado no es válido.');
        abort_if(strlen($bytes) > 2 * 1024 * 1024, 422, 'El logo no puede superar los 2 MB.');

        $formato = $partes[1];
        if ($formato === 'svg+xml') {
            $bytes = app(SvgDocumentoVeterinario::class)->limpiar($bytes);
            $formato = 'svg';
        } else {
            abort_unless((@getimagesizefromstring($bytes)['mime'] ?? null) === 'image/'.$formato, 422, 'El logo seleccionado no es válido.');
        }
        abort_unless($bytes, 422, 'El logo seleccionado no es válido.');

        $ruta = 'plantillas-veterinarias/logos/'.Str::uuid().'.'.$formato;
        abort_unless(Storage::disk('local')->put($ruta, $bytes), 500, 'No se pudo guardar el logo.');
        return $ruta;
    }

    private function borrarArchivoReemplazado(?string $ruta, ?string $vigente): void
    {
        if ($ruta && $ruta !== $vigente && !str_starts_with($ruta, 'data:')) {
            Storage::disk('local')->delete($ruta);
        }
    }

    public function estado(Request $request, PlantillaDocumentoVeterinario $plantilla)
    {
        $this->propia($request, $plantilla);
        $plantilla->update($request->validate(['activo' => 'required|boolean']));
        return response()->json(['estado' => 1, 'msj' => $plantilla->activo ? 'Plantilla habilitada.' : 'Plantilla deshabilitada.', 'activo' => $plantilla->activo]);
    }

    public function eliminar(Request $request, PlantillaDocumentoVeterinario $plantilla)
    {
        $this->propia($request, $plantilla);
        $plantilla->deleteOrFail();
        return response()->json(['estado' => 1, 'msj' => 'Plantilla eliminada.']);
    }

    public function archivo(Request $request, PlantillaDocumentoVeterinario $plantilla)
    {
        $this->autorizarUso($this->profesional($request), $plantilla, $request->integer('lugar'));
        abort_unless($plantilla->archivo_ruta && Storage::disk('local')->exists($plantilla->archivo_ruta), 404);
        $cabeceras = [
            'Content-Type' => $plantilla->archivo_mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ];
        if ($plantilla->archivo_mime === 'image/svg+xml') {
            $cabeceras['Content-Security-Policy'] = "sandbox; default-src 'none'; style-src 'unsafe-inline'";
        }
        $enLinea = $plantilla->archivo_mime === 'application/pdf' || str_starts_with($plantilla->archivo_mime ?? '', 'image/');
        return Storage::disk('local')->response($plantilla->archivo_ruta, $plantilla->archivo_nombre, $cabeceras, $enLinea ? 'inline' : 'attachment');
    }

    public function paraAtencion(int $hora, AccesoAgendaVeterinaria $agenda)
    {
        $atencion = $agenda->hora($hora, 'emitir');
        abort_unless($atencion->id_mascota, 404);

        return response()->json([
            'estado' => 1,
            'msj' => 'Plantillas de la atención cargadas.',
            'plantillas' => PlantillaDocumentoVeterinario::delProfesional(
                $atencion->id_profesional, $atencion->id_lugar_atencion, soloActivas: true
            ),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function emitir(Request $request, AccesoAgendaVeterinaria $agenda, DocumentoVeterinarioEmitido $emitidos)
    {
        $datos = $request->validate([
            'id_hora_medica' => 'required|integer|min:1',
            'id_documento_profesional' => 'required|integer|min:1',
            'contenido' => 'nullable|string|max:200000',
            'datos_usados' => 'sometimes|array|max:30',
            'datos_usados.*' => 'string|max:500',
        ]);
        $hora = $agenda->hora($datos['id_hora_medica'], 'emitir');
        $profesional = Profesional::findOrFail($hora->id_profesional);
        $plantilla = PlantillaDocumentoVeterinario::findOrFail($datos['id_documento_profesional']);
        $this->autorizarUso($profesional, $plantilla, $hora->id_lugar_atencion);
        abort_unless($plantilla->activo, 409, 'La plantilla ya no está habilitada.');

        return $this->emitirDocumento($request, $emitidos, $hora, $profesional, $datos, $plantilla);
    }

    public function emitirBiopsia(Request $request, AccesoAgendaVeterinaria $agenda, DocumentoVeterinarioEmitido $emitidos)
    {
        $datos = $request->validate([
            'id_hora_medica' => 'required|integer|min:1',
            'localizacion' => 'required|string|max:500',
            'tecnica' => 'required|in:Incisional,Excisional,Tru-cut,Endoscópica,Médula ósea,Otra',
            'otra_tecnica' => 'exclude_unless:tecnica,Otra|required|string|max:255',
            'inmovilizacion' => 'sometimes|array|max:3',
            'inmovilizacion.*' => 'required|string|distinct|in:Anestesia local,Sedación,Anestesia general',
            'descripcion' => 'required|string|max:5000',
            'riesgos' => 'nullable|string|max:5000',
            'alternativas' => 'nullable|string|max:5000',
            'laboratorio' => 'required|string|max:255',
            'presupuesto' => 'required|integer|min:0|max:999999999999',
            'peso' => 'nullable|numeric|gt:0|max:99999',
            'registro_profesional' => 'nullable|string|max:100',
        ]);
        $hora = $agenda->hora($datos['id_hora_medica'], 'emitir');
        $profesional = Profesional::findOrFail($hora->id_profesional);

        return $this->emitirDocumento($request, $emitidos, $hora, $profesional, $datos, vistaPrevia: $request->boolean('vista_previa'));
    }

    public function emitirConsentimientoPersonalizado(Request $request, AccesoAgendaVeterinaria $agenda, DocumentoVeterinarioEmitido $emitidos)
    {
        $reglas = ['id_hora_medica' => 'required|integer|min:1', 'titulo' => 'required|string|max:180'];
        $atributos = [];
        foreach (config('consentimiento_veterinario_personalizado') as $campo => $detalle) {
            $reglas[$campo] = ($detalle['requerido'] ? 'required' : 'nullable').'|string|max:5000';
            $atributos[$campo] = mb_strtolower($detalle['etiqueta']);
        }
        $datos = $request->validate($reglas, [], $atributos);
        $hora = $agenda->hora($datos['id_hora_medica'], 'emitir');
        $profesional = Profesional::findOrFail($hora->id_profesional);

        return $this->emitirDocumento($request, $emitidos, $hora, $profesional, $datos, vistaPrevia: $request->boolean('vista_previa'));
    }

    public function emitirIndicacionPersonalizada(Request $request, AccesoAgendaVeterinaria $agenda, DocumentoVeterinarioEmitido $emitidos)
    {
        $datos = $request->validate([
            'id_hora_medica' => 'required|integer|min:1',
            'titulo' => 'required|string|max:180',
            'indicaciones' => 'required|string|max:10000',
        ]);
        $hora = $agenda->hora($datos['id_hora_medica'], 'emitir');
        $profesional = Profesional::findOrFail($hora->id_profesional);

        return $this->emitirDocumento($request, $emitidos, $hora, $profesional, $datos, vistaPrevia: $request->boolean('vista_previa'), indicacionPersonalizada: true);
    }

    private function emitirDocumento(Request $request, DocumentoVeterinarioEmitido $emitidos, HoraMedica $hora, Profesional $profesional, array $datos, ?PlantillaDocumentoVeterinario $plantilla = null, bool $vistaPrevia = false, bool $indicacionPersonalizada = false)
    {
        abort_unless($hora->id_ficha_atencion && $hora->id_mascota, 409, 'Guarda la ficha de atención antes de emitir el documento.');
        $ficha = FichaAtencion::whereKey($hora->id_ficha_atencion)
            ->where('id_profesional', $hora->id_profesional)
            ->where('id_mascota', $hora->id_mascota)
            ->where('id_paciente', $hora->id_paciente)
            ->where('id_lugar_atencion', $hora->id_lugar_atencion)->firstOrFail();
        $mascota = Mascota::findOrFail($ficha->id_mascota);
        abort_unless((int) $mascota->id_responsable === (int) $ficha->id_paciente, 409, 'El tutor de la mascota cambió. Revisa los datos de la atención.');
        $tutor = Paciente::findOrFail($ficha->id_paciente);
        $consentimiento = !$indicacionPersonalizada && (!$plantilla || $plantilla->seccion === 'consentimiento');
        $firmaProfesional = $consentimiento || $indicacionPersonalizada;
        $esArchivo = $plantilla?->tipo === 'archivo';
        $titulo = $plantilla?->titulo ?? $datos['titulo'] ?? 'Consentimiento para biopsia oncológica';
        $tokenPrevia = null;
        $huellaPrevia = null;
        if (!$plantilla) {
            if ($firmaProfesional) {
                $huellaPrevia = hash('sha256', serialize([$request->user()->id, $ficha->id, $tutor->id, $datos]));
                $tokenPrevia = $this->tokenVistaPrevia($request, $huellaPrevia);
            }
            $lugar = LugarAtencion::with('Direccion')->find($ficha->id_lugar_atencion);
            $fecha = now('America/Santiago')->format('d/m/Y H:i');
            $vista = match (true) {
                $indicacionPersonalizada => 'PDF.contenido_indicacion_personalizada_vet',
                isset($datos['titulo']) => 'PDF.contenido_consentimiento_personalizado_vet',
                default => 'PDF.contenido_consentimiento_biopsia',
            };
            $datos['contenido'] = view($vista, compact(
                'lugar', 'ficha', 'mascota', 'tutor', 'profesional', 'fecha', 'datos'
            ))->render();
            $datos['datos_usados'] = collect($datos)->except('contenido')->all();
        }
        $contenido = $esArchivo ? null : app(ContenidoDocumentoVeterinario::class)->limpiar($datos['contenido'] ?? '');
        if (!$esArchivo) {
            abort_if(trim(html_entity_decode(strip_tags($contenido))) === '', 422, 'Escribe el contenido del documento.');
        }

        $cuerpo = [
            'id_documento_profesional' => $plantilla?->id,
            'id_hora_medica' => $hora->id,
            'id_mascota' => $mascota->id,
            'titulo' => $titulo,
            'contenido_final' => $contenido,
            'datos_usados' => (object) ($esArchivo ? [] : ($datos['datos_usados'] ?? [])),
            'fecha_documento' => now('America/Santiago')->locale('es_CL')->isoFormat('D [de] MMMM [de] YYYY'),
            'tutor' => ['nombre' => $emitidos->nombreTutor($tutor), 'rut' => mb_strtoupper(trim($tutor->rut ?? ''))],
            'estado_firma' => $consentimiento ? DocumentoVeterinarioEmitido::PENDIENTE : 'no_requiere',
            'firma_profesional' => null,
            'firma_tutor' => null,
        ];

        if ($vistaPrevia) {
            $cabeceras = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="vista-previa-documento-veterinario.pdf"',
                'Cache-Control' => 'private, no-store',
            ];
            if ($firmaProfesional) {
                $firma = $emitidos->firma($request, $profesional->nombreCompleto(), $profesional->rut, 'validacion.consentimiento.profesional', $tokenPrevia);
                $cuerpo['firma_profesional'] = $firma;
                $cuerpo['vista_previa'] = true;
                $cabeceras['X-Vista-Previa-Biopsia'] = Crypt::encryptString(json_encode([
                    'token' => $firma['token'],
                    'huella' => $huellaPrevia,
                    'vence' => now()->addMinutes(30)->timestamp,
                ], JSON_THROW_ON_ERROR));
            }
            return response($emitidos->pdf($cuerpo, $consentimiento), 200, $cabeceras);
        }

        $documento = $emitidos->transaccion(function (array &$archivos) use (
            $request, $emitidos, $ficha, $mascota, $profesional, $tutor, $plantilla, $consentimiento, $esArchivo, $cuerpo, $titulo, $tokenPrevia, $firmaProfesional
        ) {
            $firma = $firmaProfesional ? $emitidos->firma($request, $profesional->nombreCompleto(), $profesional->rut, 'validacion.consentimiento.profesional', $tokenPrevia) : null;
            $cuerpo['firma_profesional'] = $firma;

            if ($esArchivo) {
                $bytes = $plantilla->archivo_ruta ? Storage::disk('local')->get($plantilla->archivo_ruta) : null;
                abort_unless($bytes !== null, 404, 'El archivo de la plantilla no está disponible.');
                $cuerpo['adjunto'] = [
                    'ruta' => $emitidos->guardarArchivo($bytes, $archivos),
                    'nombre' => $plantilla->archivo_nombre,
                    'mime' => $plantilla->archivo_mime ?: 'application/octet-stream',
                    'sha256' => hash('sha256', $bytes),
                ];
            }
            if ($logo = $plantilla?->logoParaDocumento()) {
                $cuerpo['logo_ruta'] = $emitidos->guardarArchivo($logo, $archivos);
            }
            if (!$esArchivo) {
                $cuerpo['pdf_ruta'] = $emitidos->guardarArchivo($emitidos->pdf($cuerpo, $consentimiento), $archivos);
            }

            $documento = (new DocumentoFcPaciente())->forceFill([
                'id_tipo_documento' => 1,
                'id_paciente' => $tutor->id,
                'id_profesional' => $profesional->id,
                'id_ficha_atencion' => $ficha->id,
                'id_lugar_atencion' => $ficha->id_lugar_atencion,
                'documento' => $esArchivo ? $plantilla->archivo_nombre : (Str::slug($titulo) ?: 'documento-veterinario').'.pdf',
                'url' => '',
                'observacion' => $titulo,
                'otro' => $consentimiento ? DocumentoVeterinarioEmitido::CONSENTIMIENTO : DocumentoVeterinarioEmitido::INDICACION,
                'firma_profesional_token' => $firma['token'] ?? null,
                'fecha_envio' => now(),
                'estado_envio' => $tutor->id_usuario ? 1 : 0,
                'estado' => 1,
            ]);
            $emitidos->guardarCuerpo($documento, $cuerpo);
            $documento->url = route('vet.documentos.emitido', $documento, false);
            $documento->saveOrFail();

            if ($tutor->id_usuario) {
                $emitidos->avisar($profesional->id_usuario, $tutor->id_usuario,
                    $consentimiento ? 'Consentimiento veterinario para autorizar' : 'Nueva indicación veterinaria',
                    'Se emitió «'.$titulo.'» para '.$mascota->nombre.'. Revísalo en Mis documentos.', $documento);
            }
            return $documento;
        });

        return response()->json([
            'estado' => 1,
            'msj' => $tutor->id_usuario ? 'Documento guardado en la atención y tutor avisado.' : 'Documento guardado en la atención. El tutor no tiene cuenta para recibir el aviso.',
            'url' => asset($documento->url),
            'consulta_url' => $consentimiento ? route('profesional.plantillas.firma_consulta', $documento) : null,
            'compartir_url' => $indicacionPersonalizada ? route('profesional.plantillas.preparar_indicacion', $documento) : null,
        ]);
    }

    private function tokenVistaPrevia(Request $request, string $huella): ?string
    {
        $comprobante = $request->input('comprobante_vista_previa');
        if (!is_string($comprobante) || strlen($comprobante) > 2048) {
            return null;
        }

        try {
            $datos = json_decode(Crypt::decryptString($comprobante), true, flags: JSON_THROW_ON_ERROR);
        } catch (DecryptException | \JsonException) {
            return null;
        }

        $token = $datos['token'] ?? null;
        if (!is_string($token) || !hash_equals($huella, $datos['huella'] ?? '')
            || ($datos['vence'] ?? 0) <= now()->timestamp
            || DocumentoFcPaciente::where('firma_profesional_token', $token)->exists()) {
            return null;
        }

        return $token;
    }
}
