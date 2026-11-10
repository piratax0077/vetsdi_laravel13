<?php

namespace App\Http\Controllers;

use App\Models\HoraMedica;
use App\Models\JitsiVideo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoconsultaVeterinariaController extends Controller
{
    public function sala(Request $request, HoraMedica $hora): Response
    {
        $hora->loadMissing(['Mascota.Responsable', 'Profesional', 'Paciente']);
        abort_unless($hora->Mascota, 404);

        $usuario = (int) $request->user()->id;
        $profesional = $usuario === (int) $hora->Profesional?->id_usuario;
        $tutor = $usuario === (int) $hora->Paciente?->id_usuario
            && $usuario === (int) $hora->Mascota->Responsable?->id_usuario;
        abort_unless($profesional || $tutor, 403);
        abort_unless($hora->permiteVideoconsulta(), 409);
        $appId = config('services.jitsi.app_id');
        abort_unless($appId && $hora->id_jitsi_video_consulta, 503, 'La sala de video todavía no está disponible.');

        $sala = JitsiVideo::where('id_profesional', $hora->id_profesional)
            ->where('id_paciente', $hora->id_paciente)->findOrFail($hora->id_jitsi_video_consulta);
        $token = $profesional ? $sala->token_moderator : $sala->token_invitado;
        abort_unless($token && $sala->nombre_grupo, 503, 'La sala de video todavía no está disponible.');

        $fvu = $profesional
            ? route('profesional.mascota.ficha_veterinaria', ['mascota' => $hora->id_mascota])
            : route('paciente.mascota.ficha_veterinaria', ['id_mascota' => $hora->id_mascota]);

        return response()->view('videoconsulta.sala', compact('hora', 'token', 'appId', 'sala', 'fvu', 'profesional'))
            ->header('Cache-Control', 'private, no-store');
    }
}
