<?php

namespace App\Services;

use App\Http\Controllers\GeneradorQrController;
use App\Models\{DocumentoFcPaciente, Mensajes, Paciente, Profesional};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Storage};
use Illuminate\Support\Str;

class DocumentoVeterinarioEmitido
{
    public const INDICACION = 'indicacion_veterinaria';
    public const CONSENTIMIENTO = 'consentimiento_veterinario_plantilla';
    public const PENDIENTE = 'pendiente_firma';
    private const DIRECTORIO = 'documentos-atencion/';

    public function cuerpo(DocumentoFcPaciente $documento): array
    {
        return json_decode((string) $documento->cuerpo, true) ?: [];
    }

    public function firma(Request $request, string $nombre, ?string $rut, string $ruta, ?string $token = null): array
    {
        $fecha = now('America/Santiago');
        $token ??= Str::random(64);
        return [
            'estado' => 'firmado',
            'nombre' => $nombre,
            'rut' => $rut,
            'fecha' => $fecha->format('d/m/Y H:i'),
            'fecha_iso' => $fecha->toIso8601String(),
            'token' => $token,
            'url' => route($ruta, ['token' => $token]),
            'ip' => $request->ip(),
        ];
    }

    public function pdf(array $cuerpo, bool $consentimiento): string
    {
        $qr = fn (?array $firma) => empty($firma['url']) ? null
            : 'data:image/svg+xml;base64,'.base64_encode(GeneradorQrController::generar($firma['url']));
        $logo = !empty($cuerpo['logo_ruta']) ? $this->disco()->get($cuerpo['logo_ruta']) : null;
        return Pdf::loadView('PDF.documento_personalizado_vet', [
            'titulo' => $cuerpo['titulo'],
            'contenido' => app(ContenidoDocumentoVeterinario::class)->limpiar($cuerpo['contenido_final'] ?? ''),
            'fecha' => $cuerpo['fecha_documento'],
            'logo' => $logo ?: 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/vet-color-h.png'))),
            'consentimiento' => $consentimiento,
            'firmaProfesional' => $cuerpo['firma_profesional'],
            'vistaPrevia' => $cuerpo['vista_previa'] ?? false,
            'firmaTutor' => $cuerpo['firma_tutor'],
            'qrProfesional' => $qr($cuerpo['firma_profesional']),
            'qrTutor' => $qr($cuerpo['firma_tutor']),
            'tutor' => $cuerpo['tutor'],
            'adjunto' => $cuerpo['adjunto'] ?? null,
        ])->setPaper('a4')->output();
    }

    public function guardarArchivo(string $bytes, array &$archivos): string
    {
        $ruta = self::DIRECTORIO.Str::uuid();
        $archivos[] = $ruta;
        abort_unless($this->disco()->put($ruta, $bytes), 500, 'No se pudo guardar el documento.');
        return $ruta;
    }

    public function transaccion(callable $operacion): mixed
    {
        $archivos = [];
        try {
            return DB::transaction(function () use ($operacion, &$archivos) {
                return $operacion($archivos);
            });
        } catch (\Throwable $error) {
            $this->disco()->delete($archivos);
            throw $error;
        }
    }

    public function guardarCuerpo(DocumentoFcPaciente $documento, array $cuerpo): void
    {
        $documento->cuerpo = json_encode($cuerpo, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $documento->saveOrFail();
    }

    public function avisar(int $emisor, int $receptor, string $asunto, string $texto, DocumentoFcPaciente $documento): void
    {
        $mensaje = new Mensajes();
        $mensaje->id_usuario = $emisor;
        $mensaje->id_receptor = $receptor;
        $mensaje->datos_mensaje = json_encode([
            'asunto' => $asunto,
            'mensaje' => $texto,
            'documento_url' => asset($documento->url),
            'id_documento' => $documento->id,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $mensaje->tipo_mensaje = 1;
        $mensaje->fecha_envio = now();
        $mensaje->estado = 1;
        $mensaje->saveOrFail();
    }

    public function firmarTutor(Request $request, int $id, Paciente $tutor, ?string $codigo = null): array
    {
        return $this->transaccion(function (array &$archivos) use ($request, $id, $tutor, $codigo) {
            $documento = DocumentoFcPaciente::whereKey($id)->where('id_paciente', $tutor->id)
                ->where('otro', self::CONSENTIMIENTO)->where('estado', 1)->lockForUpdate()->firstOrFail();
            $cuerpo = $this->cuerpo($documento);
            if ($documento->firma_token) {
                return ['estado' => 1, 'msj' => 'El consentimiento ya fue firmado.', 'ruta' => asset($documento->url)];
            }
            abort_unless($documento->firma_profesional_token && ($cuerpo['estado_firma'] ?? null) === self::PENDIENTE, 409,
                'El consentimiento requiere una nueva emisión con la firma del profesional.');

            if ($codigo !== null) {
                $desafio = $cuerpo['firma_consulta'] ?? [];
                if (($desafio['vence'] ?? 0) <= now()->timestamp || ($desafio['correo'] ?? null) !== $tutor->email) {
                    return ['estado' => 0, 'msj' => 'Solicita un código vigente para el correo registrado.'];
                }
                if ($desafio['intentos'] >= 5) {
                    return ['estado' => 0, 'msj' => 'Se alcanzaron los cinco intentos. Espera a que venza el código.'];
                }
                if (!Hash::check($this->protegerCodigo($codigo), $desafio['hash'])) {
                    $cuerpo['firma_consulta']['intentos']++;
                    $this->guardarCuerpo($documento, $cuerpo);
                    return ['estado' => 0, 'msj' => $cuerpo['firma_consulta']['intentos'] === 5
                        ? 'Se alcanzaron los cinco intentos. Espera a que venza el código.' : 'El código no coincide.'];
                }
            }

            $firma = $this->firma($request, $this->nombreTutor($tutor), $tutor->rut, 'validacion.consentimiento.tutor');
            if ($codigo !== null) {
                $firma['correo'] = $tutor->email;
                $firma['navegador'] = mb_substr((string) $request->userAgent(), 0, 1000);
            }
            $cuerpo['firma_tutor'] = $firma;
            $cuerpo['estado_firma'] = 'firmado';
            unset($cuerpo['firma_consulta']);
            $pdf = $this->pdf($cuerpo, true);
            $cuerpo['pdf_firmado_ruta'] = $this->guardarArchivo($pdf, $archivos);
            $cuerpo['pdf_sha256'] = hash('sha256', $pdf);
            $documento->firma_token = $firma['token'];
            $documento->fecha_envio = now();
            $documento->estado_envio = 1;
            $this->guardarCuerpo($documento, $cuerpo);

            $profesional = Profesional::findOrFail($documento->id_profesional);
            $this->avisar($tutor->id_usuario ?: $profesional->id_usuario, $profesional->id_usuario,
                'Consentimiento veterinario firmado por el tutor',
                $firma['nombre'].' firmó y autorizó el consentimiento de la ficha N° '.$documento->id_ficha_atencion.'.', $documento);

            return ['estado' => 1, 'msj' => 'Consentimiento firmado, autorizado y enviado al profesional.', 'ruta' => asset($documento->url)];
        });
    }

    public function nombreTutor(Paciente $tutor): string
    {
        return trim(implode(' ', array_filter([$tutor->nombres, $tutor->apellido_uno, $tutor->apellido_dos])));
    }

    public function protegerCodigo(string $codigo): string
    {
        return hash_hmac('sha256', $codigo, config('app.key'));
    }

    public function archivo(DocumentoFcPaciente $documento, bool $original = false)
    {
        $cuerpo = $this->cuerpo($documento);
        $adjunto = $cuerpo['adjunto'] ?? null;
        $pdf = $cuerpo['pdf_firmado_ruta'] ?? $cuerpo['pdf_ruta'] ?? null;
        $usarAdjunto = $adjunto && ($original || !$pdf);
        $ruta = $usarAdjunto ? $adjunto['ruta'] : $pdf;
        abort_unless(is_string($ruta) && str_starts_with($ruta, self::DIRECTORIO) && $this->disco()->exists($ruta), 404);
        $huella = $usarAdjunto ? $adjunto['sha256'] : ($cuerpo['pdf_sha256'] ?? null);
        if ($huella) {
            abort_unless(hash_equals($huella, hash('sha256', $this->disco()->get($ruta))), 409, 'El archivo ya no coincide con su huella de integridad.');
        }
        $mime = $usarAdjunto ? $adjunto['mime'] : 'application/pdf';
        $nombre = $usarAdjunto ? $adjunto['nombre'] : (Str::slug($cuerpo['titulo']) ?: 'documento-veterinario').'.pdf';
        $cabeceras = ['Content-Type' => $mime, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'no-referrer'];
        if ($mime === 'image/svg+xml') {
            $cabeceras['Content-Security-Policy'] = "sandbox; default-src 'none'; style-src 'unsafe-inline'";
        }
        $enLinea = $mime === 'application/pdf' || str_starts_with($mime, 'image/');
        return $this->disco()->response($ruta, $nombre, $cabeceras, $enLinea ? 'inline' : 'attachment');
    }

    private function disco()
    {
        return Storage::disk('local');
    }
}
