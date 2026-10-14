<?php

namespace App\Http\Controllers;

use App\Models\{ConConsentimientosPcte, FichaAtencion, LogUsersDevices, Mascota, Paciente, Profesional};
use App\Services\AccesoSanitarioMascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class RevocacionVeterinariaController extends Controller
{
    public function autorizarProfesional(ConConsentimientosPcte $registro): void
    {
        $ficha = FichaAtencion::findOrFail($registro->id_fc);
        $mascota = Mascota::findOrFail($ficha->id_mascota);
        app(AccesoSanitarioMascota::class)->exigirEscritura($mascota);
        $profesional = Profesional::where('id_usuario', Auth::id())->firstOrFail();
        abort_unless((int) $registro->id_profesional === (int) $profesional->id
            && (int) $ficha->id_profesional === (int) $profesional->id
            && (int) $registro->id_paciente === (int) $mascota->id_responsable, 403);
    }

    private function autorizacion(ConConsentimientosPcte $registro, int $estado, ?string $token = null): LogUsersDevices
    {
        $consulta = LogUsersDevices::whereKey($registro->id_log_user_devices_revocacion)
            ->where('tipo', 5)->where('estado', $estado);
        if ($token !== null) $consulta->where('token', $token);
        $log = $consulta->lockForUpdate()->first();
        abort_unless($log && $log->fecha_exp && now()->lessThanOrEqualTo($log->fecha_exp), 403, 'Autorización inválida o vencida.');
        $tutor = Paciente::findOrFail($registro->id_paciente);
        abort_unless($tutor->id_usuario && (int) $log->id_user_recept === (int) $tutor->id_usuario, 403);
        $ficha = FichaAtencion::findOrFail($registro->id_fc);
        $mascota = Mascota::findOrFail($ficha->id_mascota);
        abort_unless((int) $mascota->id_responsable === (int) $tutor->id, 403);
        return $log;
    }

    public function formulario(Request $request)
    {
        $datos = $request->validate(['id_consentimiento'=>'required|integer', 'token'=>'required|string|max:255']);
        $registro = ConConsentimientosPcte::findOrFail($datos['id_consentimiento']);
        $this->autorizacion($registro, 0, $datos['token']);
        return view('app.mail.consentimiento_revocacion', ['id_consentimiento'=>$registro->id, 'token'=>$datos['token']]);
    }

    public function confirmar(Request $request)
    {
        $datos = $request->validate(['id_consentimiento'=>'required|integer', 'token'=>'required|string|max:255', 'acepta_consentimiento'=>'accepted']);
        DB::transaction(function () use ($datos) {
            $registro = ConConsentimientosPcte::whereKey($datos['id_consentimiento'])->lockForUpdate()->firstOrFail();
            $log = $this->autorizacion($registro, 0, $datos['token']);
            $log->estado = 1;
            $log->save();
            $registro->revocacion = 1;
            $registro->fecha_revocacion = now();
            $registro->save();
        });
        return redirect()->route('consentimiento.exito');
    }

    public function guardar(Request $request)
    {
        $datos = $request->validate(['id_consentimiento_pcte'=>'required|integer', 'observaciones_rev'=>'required|string|max:5000']);
        DB::transaction(function () use ($datos) {
            $registro = ConConsentimientosPcte::whereKey($datos['id_consentimiento_pcte'])->lockForUpdate()->firstOrFail();
            $this->autorizarProfesional($registro);
            $this->autorizacion($registro, 1);
            $registro->observaciones_rev = $datos['observaciones_rev'];
            $registro->revocacion = 1;
            $registro->fecha_revocacion = $registro->fecha_revocacion ?: now();
            $registro->save();
        });
        return response()->json(['estado'=>1, 'msj'=>'Revocación autorizada y registrada.']);
    }

    public function estado(Request $request)
    {
        $datos = $request->validate(['id_consentimiento'=>'required|integer', 'estado'=>'required|in:1,2']);
        DB::transaction(function () use ($datos) {
            $registro = ConConsentimientosPcte::whereKey($datos['id_consentimiento'])->lockForUpdate()->firstOrFail();
            $this->autorizarProfesional($registro);
            $this->autorizacion($registro, (int) $datos['estado']);
            $registro->revocacion = (int) $datos['estado'];
            $registro->fecha_revocacion = now();
            $registro->save();
        });
        return response()->json(['estado'=>1, 'msj'=>'Decisión del tutor registrada.']);
    }
}
