<?php

namespace App\Http\Middleware;

use App\Models\{Asistente, Bono, ContratoDependiente, FichaAtencion, HoraMedica, Paciente, Profesional};
use App\Services\AutorizacionesAsistentes;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AutorizarAsistente
{
    public function handle(Request $request, Closure $next)
    {
        $servicio = app(AutorizacionesAsistentes::class);
        if (!$servicio->esAsistente($request->user())) return $next($request);
        $nombre = $request->route()?->getName() ?? '';
        if ($request->user()->hasRole('Paciente') && $request->is('Paciente', 'Paciente/*')) return $next($request);
        if (Str::is(['asistente*.home', 'asistentecm*.home', 'asistentejcm.home'], $nombre)) {
            return redirect()->route('asistentes.home');
        }
        if ($nombre !== 'asistentes.perfil' && Str::is(['asistente*.perfil', 'asistentecm*.perfil'], $nombre)) {
            return redirect()->route('asistentes.perfil');
        }
        if (Str::is(['asistentes.perfil', 'asistentes.perfil.*', 'asistentes.foto.*', 'perfil.usuario.contrasena', 'asistente.editar_configuracion_busqueda', 'asistente.archivo.carga', 'liquidacion.agregar', 'liquidacion.modificar', 'liquidacion.eliminar'], $nombre)) return $next($request);
        if (Str::is(['asistentes.home', 'asistentes.contexto', 'asistentes.permisos', 'logout', 'home.ingreso', 'home.home', 'home.buscar_ciudad_region', 'home.registro_asistente', 'login', 'verification.*', 'password.*', 'two-factor.*', 'workspace.*'], $nombre)) return $next($request);
        // Only personal profile/contact operations are available without a clinical context.
        if (Str::is(['asistente*.perfil', 'asistentecm*.perfil', 'asistente.editar_datos_*_perfil', 'asistente.*contacto*', 'asistentecm.*contacto*'], $nombre)) return $next($request);

        $permiso = null;
        foreach (config('asistentes.rutas') as $patron => $clave) {
            if (Str::is($patron, $nombre)) { $permiso = $clave; break; }
        }
        abort_unless($permiso, 403, 'Esta operación no está habilitada para asistentes.');
        $asistente = Asistente::where('id_usuario', $request->user()->id)->firstOrFail();
        $contexto = $request->session()->get('asistente_contexto', []);
        $lugar = (int) ($contexto['id_lugar_atencion'] ?? 0);
        $profesional = (int) ($contexto['id_profesional'] ?? 0);
        $servicio->exigir($asistente, $lugar, $profesional, $permiso);
        view()->share('permisos_asistente',(object)$servicio->permisos($asistente->id,$lugar,$profesional));

        foreach (['id_profesional', 'id_profesional_fc', 'profesional_id'] as $campo) {
            abort_if($request->filled($campo) && (int) $request->input($campo) !== $profesional, 403);
        }
        foreach (['id_lugar_atencion', 'lugar_atencion_id', 'id_lugar', 'ficha_id_lugar_atencion'] as $campo) {
            abort_if($request->filled($campo) && (int) $request->input($campo) !== $lugar, 403);
        }
        foreach (['id_asistente', 'asistente_id'] as $campo) {
            $valor = $request->input($campo, $request->route($campo));
            abort_if($valor && (int) $valor !== (int) $asistente->id, 403);
        }
        foreach ($request->route()->parameters() as $recurso) {
            if ($recurso instanceof HoraMedica || $recurso instanceof FichaAtencion) {
                abort_unless((int) $recurso->id_profesional === $profesional && (int) $recurso->id_lugar_atencion === $lugar, 403);
            }
        }
        foreach (['id_hora_medica', 'id_hora', 'hora_medica'] as $campo) {
            if ($request->filled($campo)) {
                abort_unless(HoraMedica::whereKey($request->input($campo))->where('id_profesional', $profesional)->where('id_lugar_atencion', $lugar)->exists(), 403);
            }
        }
        if ($permiso === 'permiso_editar_pacientes') {
            foreach (['id_paciente', 'paciente_id'] as $campo) {
                abort_if($request->filled($campo) && !HoraMedica::where('id_paciente', $request->input($campo))->where('id_profesional', $profesional)->where('id_lugar_atencion', $lugar)->exists(), 403);
            }
        }
        // Scope legacy Eloquent lists and updates to the selected authorised workspace.
        $alcance = fn ($q) => $q->where($q->getModel()->getTable().'.id_profesional', $profesional)
            ->where($q->getModel()->getTable().'.id_lugar_atencion', $lugar);
        $scopesAnteriores = HoraMedica::getAllGlobalScopes();
        HoraMedica::addGlobalScope('autorizacion_asistente', $alcance);
        FichaAtencion::addGlobalScope('autorizacion_asistente', $alcance);
        Profesional::addGlobalScope('autorizacion_asistente', fn ($q) => $q->where('profesionales.id', $profesional));
        ContratoDependiente::addGlobalScope('autorizacion_asistente', fn ($q) => $q->where('contrato_dependiente.id_empleado', $asistente->id)
            ->where('contrato_dependiente.id_lugar_atencion', $lugar)->where('contrato_dependiente.estado', 2)->where('contrato_dependiente.tipo_empleado', 'like', 'ASISTENTE%'));
        Bono::addGlobalScope('autorizacion_asistente', fn ($q) => $q->where('bonos.id_profesional', $profesional)
            ->whereExists(fn ($hora) => $hora->selectRaw('1')->from('horas_medicas')->whereColumn('horas_medicas.id', 'bonos.id_referencia')->where('horas_medicas.id_lugar_atencion', $lugar)->where('horas_medicas.id_profesional', $profesional)));
        try {
            return $next($request);
        } finally {
            // Prevent workspace leakage in long-lived workers and test processes.
            HoraMedica::setAllGlobalScopes($scopesAnteriores);
        }
    }
}
