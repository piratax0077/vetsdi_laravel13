<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HorarioOperacionVeterinaria
{
    public function handle(Request $request, Closure $next)
    {
        $anterior = date_default_timezone_get();
        $configAnterior = config('app.timezone');
        $zona = config('operacion_veterinaria.timezone', 'America/Santiago');
        date_default_timezone_set($zona);
        config(['app.timezone'=>$zona]);
        try { return $next($request); }
        finally { date_default_timezone_set($anterior); config(['app.timezone'=>$configAnterior]); }
    }
}
