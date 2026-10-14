<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SeleccionCuentaController;
use App\Services\CuentasService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mientras el rol activo tenga el perfil sin completar, no se llega a ninguna
 * pantalla del sistema: da lo mismo si la URL se escribe a mano.
 *
 * Corre en todas las rutas web. Las cuentas antiguas, que no tienen roles del
 * modelo nuevo, siguen funcionando igual que antes.
 */
class PerfilPendiente
{
    /** Rutas que sí se pueden visitar con el perfil a medio llenar. */
    private const RUTAS_PERMITIDAS = [
        'perfil.completar',
        'perfil.completar.guardar',
        'cuenta.seleccion',
        'cuenta.entrar',
        'home.ingreso',
        'home.buscar_ciudad_region',
        'login',
        'logout',
        'registro.cuenta',
        'registro.enviado',
        'registro.reenviar',
        'registro.verificar',
    ];

    public function __construct(private readonly CuentasService $cuentas)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if (! $usuario || $request->expectsJson()) {
            return $next($request);
        }

        $nombreRuta = $request->route()?->getName();

        if ($nombreRuta !== null && in_array($nombreRuta, self::RUTAS_PERMITIDAS, true)) {
            return $next($request);
        }

        $tipo = $request->session()->get(SeleccionCuentaController::SESION_ROL_ACTIVO);

        if (! CuentasService::esTipoValido($tipo)) {
            return $next($request);
        }

        if (! $this->cuentas->tieneRol($usuario, $tipo)) {
            $request->session()->forget(SeleccionCuentaController::SESION_ROL_ACTIVO);

            return $next($request);
        }

        if ($this->cuentas->perfilCompleto($usuario, $tipo)) {
            return $next($request);
        }

        return redirect()->route('perfil.completar', ['tipo' => $tipo]);
    }
}
