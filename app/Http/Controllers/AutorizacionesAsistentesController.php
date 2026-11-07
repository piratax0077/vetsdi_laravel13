<?php

namespace App\Http\Controllers;

use App\Models\{Asistente, AsistenteLugarAtencion, AutorizacionAsistente, ContratoDependiente, LugarAtencion};
use App\Services\AutorizacionesAsistentes;
use Illuminate\Http\Request;

class AutorizacionesAsistentesController extends Controller
{
    public function index(Request $request, AutorizacionesAsistentes $servicio)
    {
        $ambitos = $servicio->ambitosAdministrador($request->user());
        abort_if($ambitos->isEmpty(), 403, 'Solo el profesional responsable o el administrador del centro puede autorizar.');
        $seleccion = $ambitos->first(fn ($a) => $a['id_lugar_atencion'] === (int) $request->id_lugar_atencion
            && $a['id_profesional'] === (int) $request->id_profesional) ?? $ambitos->first();
        $lugares = LugarAtencion::whereIn('id', $ambitos->pluck('id_lugar_atencion'))->get()->keyBy('id');
        $ids = AsistenteLugarAtencion::where('id_lugar_atencion', $seleccion['id_lugar_atencion'])->where('estado', 1);
        if ($seleccion['id_profesional']) {
            $ids->where(fn ($q) => $q->where('id_profesional', $seleccion['id_profesional'])->orWhereNull('id_profesional')->orWhere('id_profesional', 0));
        }
        $ids = $ids->pluck('id_asistente')->merge(ContratoDependiente::where('id_lugar_atencion', $seleccion['id_lugar_atencion'])
            ->where('estado', 2)->where('tipo_empleado', 'like', 'ASISTENTE%')->pluck('id_empleado'));
        $asistentes = Asistente::whereIn('id', $ids)->orderBy('apellido_uno')->get();
        $registros = AutorizacionAsistente::where('id_lugar_atencion', $seleccion['id_lugar_atencion'])
            ->where('id_profesional', $seleccion['id_profesional'])->get()->keyBy('id_asistente');
        $generales = AutorizacionAsistente::where('id_lugar_atencion', $seleccion['id_lugar_atencion'])->where('id_profesional', 0)->pluck('id_asistente');
        return view('app.asistente_unificado.autorizaciones', compact('ambitos', 'seleccion', 'lugares', 'asistentes', 'registros', 'generales'));
    }

    /**
     * Formulario de autorizaciones de un solo asistente, para el modal de la tabla de asistentes.
     * Solo ofrece los ámbitos (lugar / profesional) donde ese asistente está vinculado.
     */
    public function formulario(Request $request, Asistente $asistente, AutorizacionesAsistentes $servicio)
    {
        $ambitos = $servicio->ambitosAdministrador($request->user())
            ->filter(fn ($a) => $servicio->vinculado($asistente->id, $a['id_lugar_atencion'], $a['id_profesional'] ?: null))
            ->values();
        abort_if($ambitos->isEmpty(), 403, 'Este asistente no tiene una vinculación activa en un lugar que usted pueda autorizar.');
        $seleccion = $ambitos->first(fn ($a) => $a['id_lugar_atencion'] === (int) $request->id_lugar_atencion
            && $a['id_profesional'] === (int) $request->id_profesional) ?? $ambitos->first();
        $lugares = LugarAtencion::whereIn('id', $ambitos->pluck('id_lugar_atencion'))->get()->keyBy('id');
        $registro = AutorizacionAsistente::where('id_asistente', $asistente->id)
            ->where('id_lugar_atencion', $seleccion['id_lugar_atencion'])->where('id_profesional', $seleccion['id_profesional'])->first();
        $heredaCentro = $seleccion['id_profesional']
            && AutorizacionAsistente::where('id_asistente', $asistente->id)->where('id_lugar_atencion', $seleccion['id_lugar_atencion'])->where('id_profesional', 0)->exists();
        return view('app.asistente_unificado.partials.autorizaciones_modal', compact('asistente', 'ambitos', 'seleccion', 'lugares', 'registro', 'heredaCentro'));
    }

    public function guardar(Request $request, AutorizacionesAsistentes $servicio)
    {
        $rules = ['id_asistente' => 'required|integer|exists:asistentes,id', 'id_lugar_atencion' => 'required|integer', 'id_profesional' => 'required|integer|min:0', 'permisos' => 'nullable|array'];
        foreach (config('asistentes.permisos') as $clave => $nombre) $rules['permisos.'.$clave] = 'sometimes|boolean';
        $datos = $request->validate($rules);
        $servicio->guardar($request->user(), Asistente::findOrFail($datos['id_asistente']), (int) $datos['id_lugar_atencion'],
            (int) $datos['id_profesional'], $datos['permisos'] ?? []);
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'mensaje' => 'Autorizaciones guardadas. Los cambios se aplican desde la siguiente solicitud del asistente.']);
        }
        return redirect()->route('asistentes.autorizaciones', ['id_lugar_atencion' => $datos['id_lugar_atencion'], 'id_profesional' => $datos['id_profesional']])
            ->with('success', 'Autorizaciones guardadas. Los cambios se aplican desde la siguiente solicitud del asistente.');
    }

    public function quitarPoliticaCentro(Request $request, AutorizacionesAsistentes $servicio)
    {
        $datos = $request->validate(['id_asistente' => 'required|integer', 'id_lugar_atencion' => 'required|integer']);
        abort_unless($servicio->ambitosAdministrador($request->user())->contains(fn ($a) => $a['id_profesional'] === 0 && $a['id_lugar_atencion'] === (int) $datos['id_lugar_atencion']), 403);
        AutorizacionAsistente::where('id_asistente', $datos['id_asistente'])->where('id_lugar_atencion', $datos['id_lugar_atencion'])->where('id_profesional', 0)->delete();
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'mensaje' => 'Se aplicarán las autorizaciones particulares de cada profesional.']);
        }
        return back()->with('success', 'Se aplicarán las autorizaciones particulares de cada profesional.');
    }
}
