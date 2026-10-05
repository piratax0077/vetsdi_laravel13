<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SeleccionCuentaController;
use App\Services\CuentasService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard de los escritorios: revisa en el servidor que la persona tenga ese rol
 * activo y que su perfil esté completo.
 *
 * No basta con ocultar enlaces, así que esto se aplica a cada grupo de rutas:
 * escribir la URL a mano tampoco deja entrar.
 *
 * Uso: ->middleware('rol.cuenta:tutor')
 */
class RolCuenta
{
    public function __construct(private readonly CuentasService $cuentas)
    {
    }

    public function handle(Request $request, Closure $next, string $tipo): Response
    {
        $usuario = Auth::user();

        if (! $usuario) {
            return redirect()->route('home.ingreso');
        }

        if (! CuentasService::esTipoValido($tipo) || ! $this->cuentas->tieneRol($usuario, $tipo)) {
            return redirect()->route('cuenta.seleccion')
                ->with('mensaje_error', 'No tienes una cuenta de ese tipo.');
        }

        // Completar un rol no habilita los otros: se revisa el de esta ruta.
        if (! $this->cuentas->perfilCompleto($usuario, $tipo)) {
            return redirect()->route('perfil.completar', ['tipo' => $tipo])
                ->with('mensaje_error', 'Completa tu perfil para entrar a este escritorio.');
        }

        // El rol activo sigue a la ruta que se está visitando.
        $request->session()->put(SeleccionCuentaController::SESION_ROL_ACTIVO, $tipo);

        return $next($request);
    }
}
