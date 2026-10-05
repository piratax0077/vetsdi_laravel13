<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VerificacionCorreoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Confirmación del correo: pantalla posterior al registro, reenvío del mensaje
 * y apertura del enlace que llega por correo.
 */
class VerificacionCorreoController extends Controller
{
    public function __construct(private readonly VerificacionCorreoService $verificacion)
    {
    }

    /** "Te enviamos un correo para confirmar tu cuenta". */
    public function enviado(Request $request): View|RedirectResponse
    {
        $usuario = $this->usuarioPendiente($request);

        if (! $usuario) {
            return redirect()->route('home.ingreso');
        }

        // Se mantiene en sesión para que el botón de reenvío siga funcionando.
        $request->session()->keep(['registro_pendiente']);
        $request->session()->put('registro_pendiente', $usuario->id);

        return view('auth.verificacion_enviada', [
            'usuario' => $usuario,
            'correoEnviado' => $request->session()->get('correo_enviado', true),
            'segundosEspera' => $this->verificacion->segundosParaReenviar($usuario),
        ]);
    }

    public function reenviar(Request $request): RedirectResponse
    {
        $usuario = $this->usuarioPendiente($request);

        if (! $usuario) {
            return redirect()->route('home.ingreso')
                ->with('mensaje_error', 'No pudimos identificar tu cuenta. Ingresa para que te reenviemos el correo.');
        }

        if ($usuario->correoVerificado()) {
            return redirect()->route('home.ingreso')
                ->with('mensaje', 'Tu correo ya estaba confirmado. Puedes ingresar.');
        }

        if (! $this->verificacion->puedeReenviar($usuario)) {
            return back()
                ->with('registro_pendiente', $usuario->id)
                ->with('mensaje_error', 'Espera unos segundos antes de pedir otro correo.');
        }

        $enviado = $this->verificacion->enviar($usuario);

        return back()
            ->with('registro_pendiente', $usuario->id)
            ->with('correo_enviado', $enviado)
            ->with($enviado ? 'mensaje' : 'mensaje_error', $enviado
                ? 'Te reenviamos el correo de confirmación.'
                : 'No pudimos enviar el correo. Inténtalo de nuevo en unos minutos.');
    }

    /** Abre el enlace que llegó por correo. */
    public function verificar(string $token): RedirectResponse
    {
        $usuario = $this->verificacion->confirmar($token);

        if (! $usuario) {
            return redirect()->route('registro.enviado')
                ->with('mensaje_error', 'El enlace venció o no es válido. Pide uno nuevo.');
        }

        return redirect()->route('home.ingreso')
            ->with('mensaje', 'Confirmamos tu correo. Ya puedes ingresar con tu RUT o tu correo.');
    }

    /**
     * La cuenta por confirmar viene de la sesión del registro o del intento de
     * ingreso que quedó bloqueado por falta de verificación.
     */
    private function usuarioPendiente(Request $request): ?User
    {
        $id = $request->session()->get('registro_pendiente');

        if ($id) {
            return User::find($id);
        }

        $correo = $request->session()->get('correo_por_verificar');

        if ($correo) {
            return User::whereRaw('LOWER(email) = ?', [correo_normalizar($correo)])->first();
        }

        return null;
    }
}
