<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

// En WAMP (Apache en Windows) varios proyectos comparten el mismo proceso: con putenv
// las variables del .env de otro proyecto (ej. medsdi) se colaban en este y cambiaban la BD.
// Sin putenv el .env queda solo en $_ENV/$_SERVER de cada petición.
Env::disablePutenv();

return Application::configure(basePath: dirname(__DIR__))->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/integraciones.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Nadie navega el sistema con el perfil de su rol activo a medio llenar.
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\PerfilPendiente::class,
        ]);

        // Registramos los alias de los middlewares de Spatie para Laravel 13
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            // Guard de los escritorios por rol de cuenta: rol.cuenta:tutor, :profesional, etc.
            'rol.cuenta' => \App\Http\Middleware\RolCuenta::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La sesión expiró. Actualice la página e inténtelo nuevamente.',
                ], 419);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $response = redirect('/Ingreso')->with(
                'mensaje_error',
                'La sesión fue renovada. Ingrese nuevamente.'
            );

            foreach (array_unique([config('session.cookie'), 'vet_sdi_v13_session']) as $cookieName) {
                if ($cookieName) {
                    $response->withCookie(Cookie::forget($cookieName));
                }
            }

            return $response;
        });
    })
    ->withProviders([
        \App\Providers\FortifyServiceProvider::class,
    ])->create();
