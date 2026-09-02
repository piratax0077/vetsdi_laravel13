<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        // Registramos los alias de los middlewares de Spatie para Laravel 13
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
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
