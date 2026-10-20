<?php

namespace App\Services;

use App\Models\{DocumentoFcPaciente, Paciente};
use App\Services\Mensajeria\MensajeriaService;
use Illuminate\Support\Facades\Mail;

class NotificarConsentimientoTutor
{
    public function enviar(DocumentoFcPaciente $documento): array
    {
        $tutor = Paciente::findOrFail($documento->id_paciente);
        $url = route('paciente.receta.documentos', ['documento'=>$documento->id]);
        $mensaje = 'Tiene un consentimiento informado pendiente de firma en Medichile. Ingrese con su cuenta de tutor a Documentos, revise el consentimiento y firme: '.$url;
        $correo = false;
        $whatsapp = false;
        $telefono = preg_replace('/\D/', '', (string) ($tutor->telefono_uno ?: $tutor->telefono));
        if (strlen($telefono) === 9 && str_starts_with($telefono, '9')) $telefono = '56'.$telefono;
        $telefonoValido = (bool) preg_match('/^\d{10,15}$/', $telefono);
        // No confundir los transportes de desarrollo con notificaciones entregadas.
        if (filter_var($tutor->email, FILTER_VALIDATE_EMAIL) && !in_array(config('mail.default'), ['log','array',null], true)) {
            try {
                Mail::raw($mensaje, fn ($mail) => $mail->to($tutor->email)->subject('Medichile: consentimiento pendiente de firma'));
                $correo = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }
        if ($telefonoValido && in_array(config('mensajeria.driver'), ['twilio','api'], true)) {
            try {
                $respuesta = app(MensajeriaService::class)->enviarWhatsapp($telefono, $mensaje, ['id_documento'=>$documento->id]);
                $whatsapp = (int) ($respuesta['estado'] ?? 0) === 1;
            } catch (\Throwable $e) {
                report($e);
            }
        }
        return [
            'correo'=>$correo, 'whatsapp'=>$whatsapp, 'escritorio_url'=>$url,
            'whatsapp_url'=>$telefonoValido ? 'https://wa.me/'.$telefono.'?text='.rawurlencode($mensaje) : null,
            'resumen'=>($correo ? 'Correo enviado.' : 'Correo pendiente: revise el correo del tutor y la configuración de envío.').' '.
                ($whatsapp ? 'Solicitud enviada por WhatsApp.' : 'WhatsApp automático pendiente; puede compartir el enlace con el tutor.'),
        ];
    }
}
