<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Models\FichaAtencion;
use App\Models\ReferidoProfesional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReferidoProfesionalController extends Controller
{
    public function index()
    {
        $this->sincronizarRegistrados();

        $referidos = ReferidoProfesional::where('id_usuario_referente', Auth::id())
            ->latest()->get();

        $resumen = [
            'enviadas' => $referidos->whereNotIn('estado', ['cancelada'])->count(),
            'registradas' => $referidos->whereIn('estado', ['registrada', 'activada', 'bonificada'])->count(),
            'activadas' => $referidos->whereIn('estado', ['activada', 'bonificada'])->count(),
            'puntos' => $referidos->sum('puntos_otorgados'),
        ];

        return view('app.profesional.referidos.index', compact('referidos', 'resumen'));
    }

    public function indexTutor()
    {
        $this->sincronizarRegistrados();

        $referidos = ReferidoProfesional::where('id_usuario_referente', Auth::id())
            ->latest()->get();

        $resumen = [
            'enviadas' => $referidos->whereNotIn('estado', ['cancelada'])->count(),
            'registradas' => $referidos->whereIn('estado', ['registrada', 'activada', 'bonificada'])->count(),
            'activadas' => $referidos->whereIn('estado', ['activada', 'bonificada'])->count(),
            'puntos' => $referidos->sum('puntos_otorgados'),
        ];

        return view('app.paciente.referidos.index', compact('referidos', 'resumen'));
    }

    public function store(Request $request)
    {
        return $this->guardarInvitacion($request, 'profesional.referidos.index');
    }

    public function storeTutor(Request $request)
    {
        return $this->guardarInvitacion($request, 'paciente.referidos.index');
    }

    private function guardarInvitacion(Request $request, string $rutaRetorno)
    {
        $data = $request->validate([
            'tipo' => 'required|in:profesional,centro,tutor,alimentacion,farmacia',
            'nombre_invitado' => 'required|string|max:150',
            'email_invitado' => 'required|email|max:190',
            'telefono_invitado' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+() .-]+$/'],
            'nombre_centro' => 'nullable|required_if:tipo,centro,alimentacion,farmacia|string|max:190',
        ]);

        if (mb_strtolower($data['email_invitado']) === mb_strtolower((string) Auth::user()->email)) {
            return back()->withErrors(['email_invitado' => 'No puedes referirte a ti mismo.'])->withInput();
        }

        if (User::whereRaw('LOWER(email) = ?', [mb_strtolower($data['email_invitado'])])->exists()) {
            return back()->withErrors(['email_invitado' => 'Este correo ya pertenece a una cuenta VET SDI y no puede generar un referido nuevo.'])->withInput();
        }

        $existente = ReferidoProfesional::where('id_usuario_referente', Auth::id())
            ->where('email_invitado', mb_strtolower($data['email_invitado']))
            ->where('estado', '!=', 'cancelada')->first();
        if ($existente) {
            return back()->withErrors(['email_invitado' => 'Esta invitación ya está registrada.']);
        }

        $profesional = Profesional::where('id_usuario', Auth::id())->first();
        $referido = ReferidoProfesional::create([
            'id_usuario_referente' => Auth::id(),
            'id_profesional_referente' => optional($profesional)->id,
            'tipo' => $data['tipo'],
            'nombre_invitado' => $data['nombre_invitado'],
            'email_invitado' => mb_strtolower($data['email_invitado']),
            'telefono_invitado' => $data['telefono_invitado'] ?? null,
            'nombre_centro' => $data['nombre_centro'] ?? null,
            'token' => hash('sha256', Str::uuid().'|'.Str::random(40)),
            'codigo' => 'VET-'.strtoupper(Str::random(8)),
            'estado' => 'enviada',
            'puntos_propuestos' => in_array($data['tipo'], ['centro', 'alimentacion', 'farmacia'], true) ? 500 : 300,
            'fecha_envio' => now(),
        ]);

        $url = route('referidos.aceptar', $referido->token);
        $correoEnviado = true;
        try {
            Mail::raw(
                "Hola {$referido->nombre_invitado},\n\n".
                Auth::user()->name." te invita a conocer VET SDI.\n".
                "Abre tu invitación aquí: {$url}\n\nCódigo: {$referido->codigo}",
                fn ($message) => $message->to($referido->email_invitado, $referido->nombre_invitado)
                    ->subject('Invitación a VET SDI')
            );
        } catch (\Throwable $e) {
            report($e);
            $correoEnviado = false;
        }

        return redirect()->route($rutaRetorno)->with(
            $correoEnviado ? 'success' : 'warning',
            $correoEnviado
                ? 'Invitación enviada y registrada correctamente.'
                : 'La invitación quedó registrada. El correo se reenviará cuando el servicio de email esté disponible.'
        );
    }

    public function cancelarTutor(ReferidoProfesional $referido)
    {
        return $this->cancelar($referido);
    }

    public function cancelar(ReferidoProfesional $referido)
    {
        abort_unless($referido->id_usuario_referente === Auth::id(), 403);
        abort_if(in_array($referido->estado, ['activada', 'bonificada']), 422, 'Un referido activado no puede cancelarse.');
        $referido->update(['estado' => 'cancelada']);
        return back()->with('success', 'Invitación cancelada.');
    }

    public function aceptar(string $token)
    {
        $referido = ReferidoProfesional::where('token', $token)->firstOrFail();
        if ($referido->estado === 'enviada') {
            $referido->update(['estado' => 'visitada', 'fecha_visita' => now()]);
        }
        return view('referidos.aceptar', compact('referido'));
    }

    private function sincronizarRegistrados(): void
    {
        ReferidoProfesional::where('id_usuario_referente', Auth::id())
            ->whereIn('estado', ['enviada', 'visitada'])
            ->get()->each(function (ReferidoProfesional $referido) {
                $usuario = User::whereRaw('LOWER(email) = ?', [mb_strtolower($referido->email_invitado)])->first();
                if ($usuario) {
                    $cambios = [
                        'estado' => 'registrada',
                        'id_usuario_invitado' => $usuario->id,
                        'fecha_registro' => $referido->fecha_registro ?: now(),
                        'puntos_otorgados' => max(100, $referido->puntos_otorgados),
                    ];

                    $profesional = Profesional::where('id_usuario', $usuario->id)->first();
                    if ($referido->tipo === 'profesional' && $profesional
                        && FichaAtencion::where('id_profesional', $profesional->id)->exists()) {
                        $cambios['estado'] = 'activada';
                        $cambios['fecha_activacion'] = $referido->fecha_activacion ?: now();
                        $cambios['puntos_otorgados'] = 300;
                    }

                    $referido->update($cambios);
                }
            });
    }
}
