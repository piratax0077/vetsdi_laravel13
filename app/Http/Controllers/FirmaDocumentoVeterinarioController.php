<?php

namespace App\Http\Controllers;

use App\Models\{DocumentoFcPaciente, Mascota, Paciente, Profesional};
use App\Services\DocumentoVeterinarioEmitido;
use App\Services\ArchivoPdfVeterinario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Mail, Storage, URL};
use Illuminate\Support\Str;

class FirmaDocumentoVeterinarioController extends Controller
{
    public function archivo(Request $request, DocumentoFcPaciente $documento, DocumentoVeterinarioEmitido $emitidos)
    {
        $this->exigirVigente($documento);
        $usuario = $request->user()->id;
        abort_unless(
            Profesional::whereKey($documento->id_profesional)->where('id_usuario', $usuario)->exists()
            || Paciente::whereKey($documento->id_paciente)->where('id_usuario', $usuario)->exists(),
            404
        );
        return $emitidos->archivo($documento, $request->boolean('original'));
    }

    public function qrConsulta(Request $request, DocumentoFcPaciente $documento, DocumentoVeterinarioEmitido $emitidos)
    {
        $this->exigirPendiente($documento, $emitidos);
        abort_unless(Profesional::whereKey($documento->id_profesional)->where('id_usuario', $request->user()->id)->exists(), 404);
        $emitidos->exigirTutorActual($emitidos->cuerpo($documento), $this->tutorConCorreo($documento));
        $url = URL::temporarySignedRoute('vet.firma.consulta', now()->addMinutes(30), ['documento' => $documento]);
        return response()->json(['estado' => 1, 'msj' => 'Escanea el QR para firmar. Vence en 30 minutos.',
            'qr' => 'data:image/svg+xml;base64,'.base64_encode(GeneradorQrController::generar($url)),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function prepararIndicacion(Request $request, DocumentoFcPaciente $documento, DocumentoVeterinarioEmitido $emitidos, ArchivoPdfVeterinario $archivo)
    {
        $this->exigirVigente($documento);
        abort_unless($documento->otro === DocumentoVeterinarioEmitido::INDICACION
            && Profesional::whereKey($documento->id_profesional)->where('id_usuario', $request->user()->id)->exists(), 404);
        $cuerpo = $emitidos->cuerpo($documento);
        $tutor = Paciente::findOrFail($documento->id_paciente);
        $emitidos->exigirTutorActual($cuerpo, $tutor);
        $ruta = $cuerpo['pdf_ruta'] ?? '';
        abort_unless(str_starts_with($ruta, 'documentos-atencion/'), 404);
        $pdf = Storage::disk('local')->get($ruta);
        abort_unless(is_string($pdf) && str_starts_with($pdf, '%PDF-'), 404);
        $nombre = (Str::slug($cuerpo['titulo']) ?: 'indicaciones').'.pdf';
        $archivado = $archivo->registrar($request, response($pdf, 200, ['Content-Disposition' => 'inline; filename="'.$nombre.'"']), [
            'id_mascota' => (int) $cuerpo['id_mascota'],
            'id_ficha_atencion' => (int) $documento->id_ficha_atencion,
        ]);
        abort_unless($archivado, 422, 'No se pudo preparar el envío de las indicaciones.');

        return response()->json(['estado' => 1, 'url' => route('vet.pdf.detalle', $archivado)]);
    }

    public function consulta(Request $request, DocumentoFcPaciente $documento, DocumentoVeterinarioEmitido $emitidos)
    {
        $this->exigirVigente($documento, true);
        $cuerpo = $emitidos->cuerpo($documento);
        $firmado = (bool) $documento->firma_token;
        if (!$firmado) {
            $this->exigirPendiente($documento, $emitidos);
        }
        $vence = now()->setTimestamp($request->integer('expires'));
        $archivo = URL::temporarySignedRoute('vet.firma.archivo', $vence, ['documento' => $documento]);
        $original = isset($cuerpo['adjunto']) && $firmado
            ? URL::temporarySignedRoute('vet.firma.archivo', $vence, ['documento' => $documento, 'original' => 1]) : null;
        return response()->view('validacion.firma_consentimiento_consulta', [
            'titulo' => $cuerpo['titulo'],
            'archivo' => $archivo,
            'original' => $original,
            'firmado' => $firmado,
            'codigoVigente' => ($cuerpo['firma_consulta']['vence'] ?? 0) > now()->timestamp,
        ])->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'private, no-store');
    }

    public function archivoConsulta(Request $request, DocumentoFcPaciente $documento, DocumentoVeterinarioEmitido $emitidos)
    {
        $this->exigirVigente($documento, true);
        return $emitidos->archivo($documento, $request->boolean('original'));
    }

    public function guardarConsulta(Request $request, DocumentoFcPaciente $documento, DocumentoVeterinarioEmitido $emitidos)
    {
        $datos = $request->validate([
            'accion' => 'required|in:codigo,firmar',
            'codigo' => 'exclude_unless:accion,firmar|required|digits:6',
            'acepta' => 'exclude_unless:accion,firmar|accepted',
        ]);
        $this->exigirVigente($documento, true);
        $tutor = $this->tutorConCorreo($documento);
        $resultado = $datos['accion'] === 'codigo'
            ? $this->enviarCodigo($documento, $tutor, $emitidos)
            : $emitidos->firmarTutor($request, $documento->id, $tutor, $datos['codigo']);
        return redirect()->to($request->fullUrl())->with('firma_consulta', $resultado);
    }

    private function enviarCodigo(DocumentoFcPaciente $documento, Paciente $tutor, DocumentoVeterinarioEmitido $emitidos): array
    {
        return DB::transaction(function () use ($documento, $tutor, $emitidos) {
            $documento = DocumentoFcPaciente::whereKey($documento->id)->lockForUpdate()->firstOrFail();
            $this->exigirPendiente($documento, $emitidos);
            $cuerpo = $emitidos->cuerpo($documento);
            $emitidos->exigirTutorActual($cuerpo, $tutor);
            if (($cuerpo['firma_consulta']['vence'] ?? 0) > now()->timestamp) {
                return ['estado' => 1, 'msj' => 'Ya hay un código vigente en el correo registrado.'];
            }
            abort_if(in_array(config('mail.default'), ['log', 'array'], true) && !app()->environment('testing'), 503,
                'El envío de códigos por correo no está disponible.');
            $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $cuerpo['firma_consulta'] = [
                'hash' => Hash::make($emitidos->protegerCodigo($codigo)),
                'correo' => $tutor->email,
                'vence' => now()->addMinutes(10)->timestamp,
                'intentos' => 0,
            ];
            $emitidos->guardarCuerpo($documento, $cuerpo);
            Mail::raw("Tu código para firmar el consentimiento veterinario es {$codigo}. Vence en 10 minutos.",
                fn ($mensaje) => $mensaje->to($tutor->email)->subject('Código para autorizar el consentimiento veterinario'));
            return ['estado' => 1, 'msj' => 'Enviamos el código al correo registrado del tutor.'];
        });
    }

    public function validarProfesional(string $token, DocumentoVeterinarioEmitido $emitidos)
    {
        abort_unless(preg_match('/\A[A-Za-z0-9]{64}\z/', $token), 404);
        $documento = DocumentoFcPaciente::where('firma_profesional_token', $token)
            ->whereIn('otro', [DocumentoVeterinarioEmitido::CONSENTIMIENTO, DocumentoVeterinarioEmitido::INDICACION])
            ->where('estado', 1)->firstOrFail();
        $cuerpo = $emitidos->cuerpo($documento);
        abort_unless(hash_equals($cuerpo['firma_profesional']['token'] ?? '', $token), 404);
        return view('validacion.consentimiento_firma_tutor', [
            'documento' => $documento,
            'firmaProfesionalValidada' => true,
            'firmaTutor' => $cuerpo['firma_profesional'],
            'pdfSha256' => $cuerpo['pdf_sha256'] ?? null,
            'ficha' => $documento->ficha_atencion,
            'mascota' => Mascota::find($cuerpo['id_mascota'] ?? null),
            'profesional' => $documento->profesional,
        ]);
    }

    private function exigirVigente(DocumentoFcPaciente $documento, bool $soloConsentimiento = false): void
    {
        $tipos = $soloConsentimiento ? [DocumentoVeterinarioEmitido::CONSENTIMIENTO]
            : [DocumentoVeterinarioEmitido::CONSENTIMIENTO, DocumentoVeterinarioEmitido::INDICACION];
        abort_unless((int) $documento->estado === 1 && in_array($documento->otro, $tipos, true), 404);
    }

    private function exigirPendiente(DocumentoFcPaciente $documento, DocumentoVeterinarioEmitido $emitidos): void
    {
        $this->exigirVigente($documento, true);
        abort_unless(!$documento->firma_token && $documento->firma_profesional_token
            && ($emitidos->cuerpo($documento)['estado_firma'] ?? null) === DocumentoVeterinarioEmitido::PENDIENTE, 409,
            'El consentimiento ya está firmado o no está disponible para firmar.');
    }

    private function tutorConCorreo(DocumentoFcPaciente $documento): Paciente
    {
        $tutor = Paciente::findOrFail($documento->id_paciente);
        abort_unless(filter_var($tutor->email, FILTER_VALIDATE_EMAIL), 422, 'El tutor no tiene un correo válido registrado.');
        return $tutor;
    }
}
