<?php

namespace App\Http\Controllers;

use App\Models\DocumentoLaboratorioMascota;
use App\Models\Mascota;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentoLaboratorioMascotaController extends Controller
{
    private const EXTENSIONES_PERMITIDAS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf', 'odt', 'ods',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'zip', 'rar', '7z',
    ];

    public function index()
    {
        return view('app.laboratorio.veterinario.carga_documentos');
    }

    public function buscarMascotas(Request $request)
    {
        $data = $request->validate(['busqueda' => 'required|string|max:50']);
        $termino = trim($data['busqueda']);

        $mascotas = Mascota::with(['Responsable', 'especieMascota'])
            ->where('estado', '!=', 0)
            ->where(function ($query) use ($termino) {
                $query->where('chip', $termino)
                    ->orWhereHas('Responsable', function ($responsable) use ($termino) {
                        $responsable->where('rut', $termino);
                    });
            })
            ->orderBy('nombre')
            ->limit(30)
            ->get()
            ->map(function ($mascota) {
                return [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'chip' => $mascota->chip ?: 'Sin chip',
                    'especie' => optional($mascota->especieMascota)->nombre ?: ($mascota->otra_especie ?: 'Sin especie'),
                    'tutor' => trim(optional($mascota->Responsable)->nombres.' '.optional($mascota->Responsable)->apellido_uno),
                    'rut' => optional($mascota->Responsable)->rut,
                ];
            });

        return response()->json(['estado' => 1, 'mascotas' => $mascotas]);
    }

    public function guardar(Request $request)
    {
        $data = $request->validate([
            'id_mascota' => 'required|integer|exists:mascotas,id',
            'archivo' => [
                'required',
                'file',
                'max:20480',
                function (string $attribute, $value, \Closure $fail) {
                    $extension = strtolower((string) $value->getClientOriginalExtension());
                    if (! in_array($extension, self::EXTENSIONES_PERMITIDAS, true)) {
                        $fail('El tipo de archivo no está permitido.');
                    }
                },
            ],
            'observacion' => 'nullable|string|max:1000',
        ]);

        $mascota = Mascota::findOrFail($data['id_mascota']);
        $archivo = $request->file('archivo');
        $directorioRelativo = $this->directorioRelativoMascota((int) $mascota->id);

        $nombreOriginal = $archivo->getClientOriginalName();
        $tamano = $archivo->getSize() ?: 0;
        $extension = strtolower((string) $archivo->getClientOriginalExtension());
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';
        $nombre = now()->format('Ymd_His').'_'.bin2hex(random_bytes(4)).'.'.$extension;
        $mime = $archivo->getMimeType() ?: 'application/octet-stream';
        $rutaGuardada = $directorioRelativo.'/'.$nombre;

        if (! $this->guardarArchivoSubido($archivo, $rutaGuardada)) {
            return response()->json([
                'estado' => 0,
                'msj' => 'No fue posible guardar el archivo en el servidor.',
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }

        if (! $this->resolverRutaArchivoDesdeRuta($rutaGuardada)) {
            return response()->json([
                'estado' => 0,
                'msj' => 'El archivo se procesó pero no quedó disponible para visualización.',
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }

        $documento = DocumentoLaboratorioMascota::create([
            'id_mascota' => $mascota->id,
            'id_responsable' => $mascota->id_responsable,
            'id_usuario_laboratorio' => Auth::id(),
            'nombre_original' => $nombreOriginal,
            'ruta' => $rutaGuardada,
            'mime' => $mime,
            'tamano' => $tamano,
            'observacion' => $data['observacion'] ?? null,
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'estado' => 1,
            'msj' => 'Documento guardado en los archivos de la mascota y enviado al profesional.',
            'documento' => $this->serializar($documento),
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function listar(Request $request)
    {
        $data = $request->validate(['id_mascota' => 'required|integer|exists:mascotas,id']);
        $documentos = DocumentoLaboratorioMascota::where('id_mascota', $data['id_mascota'])
            ->latest()->get()->map(fn ($documento) => $this->serializar($documento));

        return response()->json(['estado' => 1, 'documentos' => $documentos]);
    }

    public function clasificar(Request $request, DocumentoLaboratorioMascota $documento)
    {
        $data = $request->validate([
            'tipo_examen' => ['required', Rule::in(['sangre', 'radiologico', 'especialidad', 'otro'])],
        ]);
        $profesional = Profesional::where('id_usuario', Auth::id())->first();
        if (!$profesional) {
            return response()->json(['estado' => 0, 'msj' => 'No se encontró el profesional conectado.'], 422);
        }

        $documento->update([
            'tipo_examen' => $data['tipo_examen'],
            'estado' => 'clasificado',
            'id_profesional_revisor' => $profesional->id,
            'revisado_at' => now(),
        ]);

        return response()->json(['estado' => 1, 'msj' => 'Examen revisado y clasificado.', 'documento' => $this->serializar($documento)]);
    }

    public function ver(DocumentoLaboratorioMascota $documento)
    {
        $ruta = $this->resolverRutaArchivo($documento);
        abort_unless($ruta && File::exists($ruta), 404, 'Documento no encontrado en el servidor.');

        return $this->responderArchivo($ruta, $documento);
    }

    private function directorioRelativoMascota(int $idMascota): string
    {
        return 'documentos/mascotas/'.$idMascota.'/examenes';
    }

    private function guardarArchivoSubido($archivo, string $rutaRelativa): bool
    {
        $rutaRelativa = trim(str_replace('\\', '/', $rutaRelativa), '/');
        $destinoStorage = storage_path('app/public/'.$rutaRelativa);
        $destinoPublico = public_path($rutaRelativa);

        File::ensureDirectoryExists(dirname($destinoStorage));
        File::ensureDirectoryExists(dirname($destinoPublico));

        $origen = $archivo->getRealPath();
        if (! $origen || ! is_readable($origen)) {
            return false;
        }

        $contenido = file_get_contents($origen);
        if ($contenido === false || $contenido === '') {
            return false;
        }

        if (File::put($destinoStorage, $contenido) === false) {
            return false;
        }

        if (! is_file($destinoStorage) || filesize($destinoStorage) <= 0) {
            return false;
        }

        if (! File::exists($destinoPublico)) {
            File::put($destinoPublico, $contenido);
        }

        return is_file($destinoStorage) && filesize($destinoStorage) > 0;
    }

    private function resolverRutaArchivo(DocumentoLaboratorioMascota $documento): ?string
    {
        return $this->resolverRutaArchivoDesdeRuta((string) $documento->ruta);
    }

    private function resolverRutaArchivoDesdeRuta(string $rutaRelativa): ?string
    {
        $ruta = trim(str_replace('\\', '/', $rutaRelativa));
        if ($ruta === '') {
            return null;
        }

        $candidatos = [
            storage_path('app/public/'.$ruta),
            public_path($ruta),
            public_path('storage/'.$ruta),
            base_path('public/'.$ruta),
            storage_path('app/'.$ruta),
        ];

        foreach ($candidatos as $candidato) {
            if ($candidato && is_file($candidato)) {
                return $candidato;
            }
        }

        return null;
    }

    private function responderArchivo(string $ruta, DocumentoLaboratorioMascota $documento): BinaryFileResponse
    {
        $mime = $documento->mime ?: (File::mimeType($ruta) ?: 'application/octet-stream');
        $nombre = $documento->nombre_original ?: basename($ruta);
        $inline = $this->esVisualizableEnNavegador($mime, $nombre);

        return response()->file($ruta, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.$this->nombreArchivoSeguro($nombre).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function esVisualizableEnNavegador(string $mime, string $nombre): bool
    {
        if (str_starts_with($mime, 'image/') || str_starts_with($mime, 'text/')) {
            return true;
        }

        if ($mime === 'application/pdf') {
            return true;
        }

        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        return in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'txt', 'csv'], true);
    }

    private function nombreArchivoSeguro(string $nombre): string
    {
        $nombre = str_replace(['"', "\r", "\n"], '', $nombre);

        return $nombre !== '' ? $nombre : 'documento';
    }

    private function urlDocumento(DocumentoLaboratorioMascota $documento): string
    {
        $rutaRelativa = trim(str_replace('\\', '/', (string) $documento->ruta), '/');

        if ($rutaRelativa !== '' && $this->resolverRutaArchivo($documento)) {
            if (is_file(public_path($rutaRelativa))) {
                return $this->rutaPublicaRelativa($rutaRelativa);
            }

            if (is_file(storage_path('app/public/'.$rutaRelativa))) {
                return $this->rutaPublicaRelativa('storage/'.$rutaRelativa);
            }
        }

        try {
            return route('documentos.laboratorio.mascota.ver', ['documento' => $documento->id], false);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function rutaPublicaRelativa(string $path): string
    {
        $path = '/'.ltrim(str_replace('\\', '/', $path), '/');

        if (app()->runningInConsole() || ! app()->bound('request') || ! request()) {
            return $path;
        }

        $baseUrl = (string) request()->getBaseUrl();
        if ($baseUrl !== '' && $baseUrl !== '/') {
            return rtrim($baseUrl, '/').$path;
        }

        return $path;
    }

    private function serializar(DocumentoLaboratorioMascota $documento): array
    {
        return [
            'id' => $documento->id,
            'nombre' => $documento->nombre_original,
            'observacion' => $documento->observacion,
            'tipo_examen' => $documento->tipo_examen,
            'estado' => $documento->estado,
            'fecha' => optional($documento->created_at)->format('d-m-Y H:i'),
            'url' => $this->urlDocumento($documento),
            'disponible' => (bool) $this->resolverRutaArchivo($documento),
        ];
    }
}



