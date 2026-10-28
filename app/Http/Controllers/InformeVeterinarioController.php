<?php

namespace App\Http\Controllers;

use App\Models\{FichaAtencion, HoraMedica, InformeMedico, Mascota, Profesional};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class InformeVeterinarioController extends Controller
{
    public function registrar(Request $request)
    {
        abort_unless(Auth::check(), 401);
        $request->validate([
            'comentarios_informe_medico' => 'required|string',
            'hora_medica' => 'required|integer|min:1',
        ]);
        $profesional = Profesional::where('id_usuario', Auth::id())->firstOrFail();
        $informe = DB::transaction(function () use ($request, $profesional) {
            $hora = HoraMedica::where('id_profesional', $profesional->id)->lockForUpdate()->findOrFail($request->hora_medica);
            $mascota = Mascota::findOrFail($hora->id_mascota);
            abort_unless((int) $mascota->id_responsable === (int) $hora->id_paciente, 403);
            $ficha = $hora->id_ficha_atencion
                ? FichaAtencion::findOrFail($hora->id_ficha_atencion)
                : app(\App\Services\AbrirFichaVeterinaria::class)->abrir($hora->id, $profesional->id);
            abort_unless((int) $ficha->id_profesional === (int) $profesional->id && (int) $ficha->id_mascota === (int) $mascota->id && (int) $ficha->id_paciente === (int) $hora->id_paciente, 403);
            $informe = new InformeMedico();
            $informe->forceFill([
                'id_tipo_informe' => 1, 'informe_medico' => trim($request->comentarios_informe_medico),
                'fecha_informe_medico' => now()->toDateString(), 'id_paciente' => $ficha->id_paciente,
                'id_ficha_atencion' => $ficha->id, 'id_profesional' => $profesional->id,
                'id_lugar_atencion' => $ficha->id_lugar_atencion, 'cod_auto' => session('lic_token'),
            ])->saveOrFail();
            return $informe;
        });

        // A delivery failure must not discard the saved report or require generating it again.
        try {
            $envio = app(DocumentoVeterinarioCorreoController::class)->informe(Request::create('/', 'POST', ['id_informe_medico' => $informe->id]))->getData(true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $envio = ['estado' => 0, 'msj' => collect($e->errors())->flatten()->implode(' ')];
        } catch (\Throwable $e) {
            report($e);
            $envio = ['estado' => 0, 'msj' => 'No fue posible enviar el PDF al tutor. Puede reintentar el envío.'];
        }
        return response()->json([
            'estado' => 1, 'id_last' => $informe->id, 'envio' => $envio,
            'pdf_url' => route('pdf.informe_medico', ['id_informe_medico' => $informe->id]),
            'msj' => ($envio['estado'] ?? 0) == 1 ? 'Informe guardado y PDF enviado al tutor.' : 'Informe guardado. '.($envio['msj'] ?? 'Envío pendiente.'),
        ]);
    }
}
