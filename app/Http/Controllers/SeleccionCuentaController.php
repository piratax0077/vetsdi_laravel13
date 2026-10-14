<?php

namespace App\Http\Controllers;

use App\Services\CuentasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Pantalla "Elige tu perfil" y cambio de escritorio.
 *
 * El rol activo vive en la sesión. Cambiar de rol no vuelve a pedir contraseña,
 * pero el servidor igual revisa que la persona tenga ese rol activo.
 */
class SeleccionCuentaController extends Controller
{
    /** Clave en sesión donde queda guardado el rol con el que se está trabajando. */
    public const SESION_ROL_ACTIVO = 'rol_activo';

    public function __construct(private readonly CuentasService $cuentas)
    {
    }

    public function index(): View|RedirectResponse
    {
        $usuario = Auth::user();
        $roles = $this->cuentas->rolesActivos($usuario);

        // Con un solo rol no tiene sentido preguntar: se entra directo.
        if ($roles->count() === 1) {
            return redirect()->route('cuenta.entrar', ['tipo' => $roles->first()->tipo]);
        }

        if ($roles->isEmpty()) {
            return redirect('/Acceso');
        }

        return view('auth.seleccion_cuenta', [
            'usuario' => $usuario,
            'roles' => $roles,
            'rolActivo' => session(self::SESION_ROL_ACTIVO),
        ]);
    }

    /** Deja un rol como activo y manda al escritorio o a completar su perfil. */
    public function entrar(Request $request, string $tipo): RedirectResponse
    {
        $usuario = Auth::user();

        if (! CuentasService::esTipoValido($tipo) || ! $this->cuentas->tieneRol($usuario, $tipo)) {
            return redirect()->route('cuenta.seleccion')
                ->with('mensaje_error', 'No tienes una cuenta de ese tipo.');
        }

        $request->session()->put(self::SESION_ROL_ACTIVO, $tipo);

        if (! $this->cuentas->perfilCompleto($usuario, $tipo)) {
            return redirect()->route('perfil.completar', ['tipo' => $tipo]);
        }

        return redirect()->route($this->cuentas->rutaEscritorio($tipo));
    }
}
