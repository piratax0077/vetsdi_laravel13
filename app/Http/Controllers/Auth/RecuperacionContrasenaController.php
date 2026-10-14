<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RecuperacionContrasenaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Recuperación de contraseña desde la tarjeta del ingreso: enlace al correo o
 * código al celular. Las respuestas son las mismas exista o no la cuenta, para
 * no revelar qué correos y teléfonos están registrados.
 */
class RecuperacionContrasenaController extends Controller
{
    /** Donde queda en sesión el celular que está esperando su código. */
    public const SESION_TELEFONO = 'recuperacion_telefono';

    public function __construct(private readonly RecuperacionContrasenaService $recuperacion)
    {
    }

    /** Envía al correo el enlace para escribir una contraseña nueva. */
    public function enlace(Request $request): RedirectResponse
    {
        $request->merge(['correo_recuperacion' => correo_normalizar($request->input('correo_recuperacion'))]);

        $datos = $request->validate([
            'correo_recuperacion' => ['required', 'string', 'email:filter', 'max:255'],
        ], [
            'correo_recuperacion.required' => 'Ingresa tu correo electrónico.',
            'correo_recuperacion.*' => 'Ingresa un correo electrónico válido.',
        ]);

        $correo = $datos['correo_recuperacion'];

        if ($this->recuperacion->debeEsperar('correo|'.$correo)) {
            return $this->volver('correo')->with('mensaje_error', 'Espera un minuto antes de pedir otro enlace.');
        }

        $usuario = $this->recuperacion->buscarPorCorreo($correo);

        if ($usuario && ! $this->recuperacion->enviarEnlace($usuario)) {
            return $this->volver('correo')
                ->with('mensaje_error', 'No pudimos enviar el correo. Inténtalo de nuevo en unos minutos.');
        }

        return $this->volver('correo')->with(
            'mensaje',
            'Si el correo está registrado, te enviamos un enlace para cambiar tu contraseña. Revisa también el correo no deseado.'
        );
    }

    /** Envía el código de seis dígitos al celular registrado. */
    public function codigo(Request $request): RedirectResponse
    {
        $telefono = telefono_normalizar($request->input('telefono_recuperacion'));

        if ($telefono === null || ! preg_match('/^\+56 9 /', $telefono)) {
            throw ValidationException::withMessages([
                'telefono_recuperacion' => 'Ingresa un celular chileno con el formato +56 9 1234 5678.',
            ]);
        }

        $yaEsperabaCodigo = $this->telefonoPendiente($request) !== null;

        if ($this->recuperacion->debeEsperar('telefono|'.$telefono)) {
            return $this->volver($yaEsperabaCodigo ? 'codigo' : 'telefono')
                ->withInput()
                ->with('mensaje_error', 'Espera un minuto antes de pedir otro código.');
        }

        $usuario = $this->recuperacion->buscarPorTelefono($telefono);

        if ($usuario && ! $this->recuperacion->enviarCodigo($usuario)) {
            return $this->volver('telefono')
                ->withInput()
                ->with('mensaje_error', 'No pudimos enviar el código. Inténtalo de nuevo o usa tu correo.');
        }

        $request->session()->put(self::SESION_TELEFONO, [
            'id' => $usuario?->id,
            'telefono' => $telefono,
            'vence' => now()->addMinutes(RecuperacionContrasenaService::MINUTOS_CODIGO)->timestamp,
        ]);

        return $this->volver('codigo')
            ->with('mensaje', 'Si el celular está registrado, te enviamos un código por WhatsApp.');
    }

    /** Con el código correcto se pasa directo a la pantalla de la contraseña nueva. */
    public function verificar(Request $request): RedirectResponse
    {
        $pendiente = $this->telefonoPendiente($request);

        if ($pendiente === null) {
            return $this->volver('telefono')->with('mensaje_error', 'El código venció. Pide uno nuevo.');
        }

        $codigo = preg_replace('/\D/', '', (string) $request->input('codigo_recuperacion'));
        $usuario = $pendiente['id'] ? User::find($pendiente['id']) : null;

        if (strlen($codigo) !== 6 || ! $usuario || ! $this->recuperacion->codigoCorrecto($usuario, $codigo)) {
            throw ValidationException::withMessages([
                'codigo_recuperacion' => 'El código no es correcto o ya venció.',
            ]);
        }

        $request->session()->forget(self::SESION_TELEFONO);

        return redirect()->to($this->recuperacion->enlaceParaCambiar($usuario));
    }

    /** Vuelve a la tarjeta de recuperación, abierta en la opción indicada. */
    private function volver(string $opcion): RedirectResponse
    {
        return redirect()->route('home.ingreso')->with('recuperar', $opcion);
    }

    private function telefonoPendiente(Request $request): ?array
    {
        $pendiente = $request->session()->get(self::SESION_TELEFONO);

        if (! is_array($pendiente) || ($pendiente['vence'] ?? 0) < now()->timestamp) {
            return null;
        }

        return $pendiente;
    }
}
