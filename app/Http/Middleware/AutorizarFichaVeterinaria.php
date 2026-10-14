<?php

namespace App\Http\Middleware;

use App\Models\{FichaAtencion, Mascota, Paciente, Profesional};
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutorizarFichaVeterinaria
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(Auth::check(), 401);
        $lectura = $request->isMethod('GET');
        if (!$lectura) abort_unless(Auth::user()->hasRole('Profesional'), 403, 'Solo el profesional puede modificar los registros sanitarios.');
        $ids = collect(['id_ficha','id_ficha_atencion','id_fc'])->map(fn ($k) => $request->input($k))->filter(fn ($v) => $v !== null && $v !== '');
        abort_unless($ids->isNotEmpty() && $ids->every(fn ($v) => filter_var($v, FILTER_VALIDATE_INT) && (int) $v > 0) && $ids->map(fn ($v)=>(int)$v)->unique()->count() === 1, 422, 'Indique una única ficha veterinaria.');
        $ficha = FichaAtencion::findOrFail((int) $ids->first());
        $mascota = Mascota::findOrFail($ficha->id_mascota);
        $profesional = Auth::user()->hasRole('Profesional') ? Profesional::where('id_usuario',Auth::id())->first() : null;
        $propio = $profesional && (int) $profesional->id === (int) $ficha->id_profesional;
        $tutor = $lectura && Paciente::where('id_usuario',Auth::id())->whereKey($mascota->id_responsable)->exists();
        abort_unless($propio || $tutor, 403);
        if (!$lectura) abort_unless((int)$mascota->id_responsable === (int)$ficha->id_paciente, 403);
        foreach (['id_profesional','id_paciente','id_lugar_atencion'] as $campo) {
            abort_if($request->filled($campo) && (int)$request->input($campo) !== (int)$ficha->{$campo}, 403);
            $request->merge([$campo=>$ficha->{$campo}]);
        }
        $request->merge(['id_ficha'=>$ficha->id,'id_ficha_atencion'=>$ficha->id]);
        foreach (['id_ficha_otros_prof','id_ficha_gineco_obstetrica','id_ingreso_paciente','id_recuperacion','id_sala'] as $campo) {
            abort_if($request->filled($campo) && (int)$request->input($campo) !== 0, 422, 'Utilice la ficha veterinaria.');
            $request->merge([$campo=>null]);
        }
        return $next($request);
    }
}
