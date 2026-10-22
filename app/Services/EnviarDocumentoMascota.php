<?php

namespace App\Services;

use App\Http\Controllers\{RecomendacionController, ficha_atencionController};
use App\Mail\CorreoGenerico;
use App\Models\{FichaAtencion, Paciente};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class EnviarDocumentoMascota
{
    public function pdf(FichaAtencion $ficha, string $tipo): Response
    {
        $request = new Request(['id_ficha_atencion' => $ficha->id]);
        $pdf = $tipo === 'receta'
            ? app(RecomendacionController::class)->verPDF($request)
            : app(ficha_atencionController::class)->pdf_orden_examenes($request);
        if (!$pdf instanceof Response || $pdf->getStatusCode() !== 200 || !str_starts_with((string) $pdf->getContent(), '%PDF-')) {
            throw new \RuntimeException('No se pudo generar el documento PDF.');
        }
        return $pdf;
    }

    public function enviar(FichaAtencion $ficha, string $tipo): array
    {
        try {
            if (in_array(config('mail.default'), ['log', 'array'], true)) {
                return ['correo_enviado'=>false, 'mensaje_correo'=>'Documento guardado en el escritorio de la mascota. El envío de correo no está configurado en este entorno.'];
            }
            $tutor = Paciente::findOrFail($ficha->id_paciente);
            if (!filter_var($tutor->email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('El tutor no tiene un correo válido registrado.');
            }
            $pdf = $this->pdf($ficha, $tipo);
            $titulo = $tipo === 'receta' ? 'Receta veterinaria' : 'Orden de exámenes veterinarios';
            $correo = new CorreoGenerico([
                'asunto' => $titulo,
                'blade' => 'plantilla-correo-electronico',
                'body' => ['titulo' => $titulo, 'contenido' => 'Se adjunta el documento emitido durante la atención de su mascota. También está disponible en el escritorio de la mascota.'],
            ]);
            $correo->attachData($pdf->getContent(), $tipo.'-veterinaria.pdf', ['mime' => 'application/pdf']);
            Mail::to($tutor->email)->send($correo);
            return ['correo_enviado' => true, 'mensaje_correo' => 'Documento enviado al correo del tutor.'];
        } catch (\Throwable $error) {
            report($error);
            return ['correo_enviado' => false, 'mensaje_correo' => 'Documento guardado en el escritorio de la mascota. No se pudo enviar el correo; revise el email del tutor y reintente el envío.'];
        }
    }
}
