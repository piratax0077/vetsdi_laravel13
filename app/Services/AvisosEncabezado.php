<?php

namespace App\Services;

use App\Models\HoraMedica;
use App\Models\Mascota;
use App\Models\Paciente;
use App\Models\User;
use App\Models\UsuarioRol;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Avisos de la campana del encabezado, con la misma idea de las notificaciones de Facebook:
 * la campana muestra cuántos avisos nuevos hay y al abrirla quedan como vistos.
 *
 * Los avisos no se guardan en una tabla: se calculan cada vez con el estado real de la
 * agenda y del plan, así desaparecen solos cuando el tutor confirma la hora o renueva.
 * Lo único que se guarda es cuáles ya vio, para no seguir marcando el contador.
 *
 *  - Tutor: horas por confirmar y recordatorio de la cita el mismo día.
 *  - Tutor: próxima dosis de vacuna o desparasitación (7 días antes) y dosis atrasadas.
 *  - Todos: recordatorio de renovación del plan cuando lo está pagando.
 */
class AvisosEncabezado
{
    /** Estados de hora que el tutor todavía puede confirmar */
    private const ESTADOS_POR_CONFIRMAR = [1, 16];

    /** Estados de una cita que sigue en pie (por confirmar o confirmada) */
    private const ESTADOS_VIGENTES = [1, 2, 16];

    /** Días antes del vencimiento en que se empieza a recordar la renovación */
    private const DIAS_AVISO_PLAN = 7;

    /** Días antes de la próxima dosis de vacuna o desparasitación en que se empieza a avisar */
    private const DIAS_AVISO_DOSIS = 7;

    private const ZONA = 'America/Santiago';

    /**
     * Lista de avisos del usuario, del más urgente al menos urgente.
     * Cada aviso: clave, tipo, icono, titulo, texto, fecha (Carbon) y enlace.
     */
    public function delUsuario(?User $usuario, bool $esTutor): Collection
    {
        if (! $usuario) {
            return collect();
        }

        $avisos = collect();

        if ($esTutor) {
            $avisos = $avisos->merge($this->citasDelTutor($usuario, $esTutor))
                ->merge($this->dosisDeMascotas($usuario));
        }

        return $avisos->merge($this->renovacionPlan($usuario, $esTutor))->values();
    }

    /** Claves de avisos que el usuario ya vio al abrir la campana */
    public function vistos(User $usuario): array
    {
        return (array) Cache::get($this->claveCache($usuario), []);
    }

    /** Marca como vistos los avisos que el usuario tiene en este momento */
    public function marcarVistos(User $usuario, bool $esTutor): void
    {
        $claves = $this->delUsuario($usuario, $esTutor)->pluck('clave')->all();

        // Se guardan solo los avisos vigentes, así la lista no crece con avisos que ya no existen
        Cache::put($this->claveCache($usuario), $claves, now()->addDays(30));
    }

    private function claveCache(User $usuario): string
    {
        return 'avisos_encabezado_vistos_'.$usuario->id;
    }

    /** Horas por confirmar y citas del día del tutor */
    private function citasDelTutor(User $usuario, bool $esTutor): Collection
    {
        $paciente = Paciente::where('id_usuario', $usuario->id)->first();

        if (! $paciente) {
            return collect();
        }

        $ahora = Carbon::now(self::ZONA);
        $hoy = $ahora->toDateString();
        $enlace = Route::has('paciente.home') ? route('paciente.home') : null;

        $horas = HoraMedica::with(['Mascota', 'Profesional', 'LugarAtencion'])
            ->where('id_paciente', $paciente->id)
            ->whereIn('id_estado', self::ESTADOS_VIGENTES)
            ->whereDate('fecha_consulta', '>=', $hoy)
            ->orderBy('fecha_consulta')
            ->orderBy('hora_inicio')
            ->limit(30)
            ->get();

        $avisos = collect();

        foreach ($horas as $hora) {
            $dia = Carbon::parse($hora->fecha_consulta, self::ZONA)->toDateString();
            $inicio = Carbon::parse($dia.' '.$hora->hora_inicio, self::ZONA);
            $termino = $hora->hora_termino ? Carbon::parse($dia.' '.$hora->hora_termino, self::ZONA) : $inicio->copy()->addHour();

            // Una cita que ya terminó no tiene nada que recordar
            if ($termino->lt($ahora)) {
                continue;
            }

            $porConfirmar = in_array((int) $hora->id_estado, self::ESTADOS_POR_CONFIRMAR, true);
            $detalle = $this->detalleCita($hora, $inicio);

            if ($dia === $hoy) {
                $avisos->push([
                    'clave' => 'cita_hoy_'.$hora->id.'_'.$hoy.($porConfirmar ? '_p' : '_c'),
                    'tipo' => 'cita_hoy',
                    'icono' => 'icon-clock',
                    'titulo' => 'Hoy tienes una cita a las '.$inicio->format('H:i'),
                    'texto' => $detalle.($porConfirmar ? ' Aún no la confirmas.' : ''),
                    'fecha' => $inicio,
                    'enlace' => $enlace,
                ]);
            } elseif ($porConfirmar) {
                $avisos->push([
                    'clave' => 'confirmar_'.$hora->id,
                    'tipo' => 'confirmar',
                    'icono' => 'icon-check-circle',
                    'titulo' => 'Confirma tu hora del '.$inicio->locale('es')->isoFormat('dddd D [de] MMMM [a las] HH:mm'),
                    'texto' => $detalle,
                    'fecha' => $inicio,
                    'enlace' => $enlace,
                ]);
            }
        }

        return $avisos;
    }

    /** Mascota, profesional y lugar de la cita en una línea */
    private function detalleCita(HoraMedica $hora, Carbon $inicio): string
    {
        $profesional = $hora->Profesional
            ? trim(collect([$hora->Profesional->nombre, $hora->Profesional->apellido_uno])->filter()->implode(' '))
            : '';

        $partes = collect([
            optional($hora->Mascota)->nombre ? 'Mascota: '.$hora->Mascota->nombre : null,
            $profesional !== '' ? 'con '.$profesional : null,
            optional($hora->LugarAtencion)->nombre ? 'en '.$hora->LugarAtencion->nombre : null,
        ])->filter();

        return $partes->isEmpty() ? '' : $partes->implode(' ').'.';
    }

    /**
     * Próximas dosis de vacunas y desparasitaciones de las mascotas del tutor.
     * Avisa desde 7 días antes y sigue avisando mientras la dosis esté atrasada.
     * Una dosis deja de avisar cuando se registra otra posterior de la misma vacuna
     * o una desparasitación posterior que cubra el mismo tipo.
     */
    private function dosisDeMascotas(User $usuario): Collection
    {
        $tutor = Paciente::where('id_usuario', $usuario->id)->first();

        if (! $tutor) {
            return collect();
        }

        $hoy = Carbon::now(self::ZONA)->startOfDay();
        $limite = $hoy->copy()->addDays(self::DIAS_AVISO_DOSIS);

        $mascotas = Mascota::accesiblesPor($tutor)
            ->seleccionables()
            ->get(['mascotas.id', 'mascotas.nombre', 'mascotas.vacunas_registro', 'mascotas.desparasitaciones_registro']);

        $avisos = collect();

        foreach ($mascotas as $mascota) {
            $enlace = Route::has('paciente.mascotas.carnet_sanitario')
                ? route('paciente.mascotas.carnet_sanitario', $mascota->id)
                : null;

            $pendientes = collect($this->dosisPendientes($mascota->vacunas_registro, 'vacuna'))
                ->merge($this->dosisPendientes($mascota->desparasitaciones_registro, 'desparasitacion'));

            foreach ($pendientes as $pendiente) {
                $fecha = $pendiente['proxima'];

                if ($fecha->gt($limite)) {
                    continue;
                }

                $dias = (int) round($hoy->diffInDays($fecha, false));
                $esVacuna = $pendiente['tipo'] === 'vacuna';
                $queToca = $esVacuna ? 'vacuna' : 'desparasitación';

                if ($dias < 0) {
                    $estado = 'atrasada';
                    $titulo = ($esVacuna ? 'Vacuna atrasada' : 'Desparasitación atrasada').' de '.$mascota->nombre;
                    $texto = $pendiente['nombre'].': debía aplicarse el '.$fecha->format('d-m-Y')
                        .' ('.abs($dias).(abs($dias) === 1 ? ' día' : ' días').' de atraso).';
                } else {
                    $estado = $dias === 0 ? 'hoy' : 'proxima';
                    $cuando = $dias === 0 ? 'hoy' : ($dias === 1 ? 'mañana' : 'en '.$dias.' días');
                    $titulo = 'A '.$mascota->nombre.' le toca su '.$queToca.' '.$cuando;
                    $texto = $pendiente['nombre'].' · '.$fecha->format('d-m-Y').'.';
                }

                $avisos->push([
                    // La clave cambia con el estado, así vuelve a marcar el contador cuando llega el día o se atrasa
                    'clave' => 'dosis_'.$pendiente['tipo'].'_'.$mascota->id.'_'.$pendiente['id'].'_'.$fecha->toDateString().'_'.$estado,
                    'tipo' => $pendiente['tipo'],
                    'icono' => $esVacuna ? 'icon-shield' : 'icon-droplet',
                    'titulo' => $titulo,
                    'texto' => $texto,
                    'fecha' => $fecha,
                    'enlace' => $enlace,
                ]);
            }
        }

        // Primero lo atrasado y lo más próximo
        return $avisos->sortBy(fn ($aviso) => $aviso['fecha']->timestamp)->values();
    }

    /**
     * Registros con próxima dosis que todavía no se cumple: se descartan los que ya tienen
     * una dosis posterior de la misma vacuna o una desparasitación posterior del mismo tipo.
     */
    private function dosisPendientes($registros, string $tipo): array
    {
        if (is_string($registros)) {
            $registros = json_decode($registros, true);
        }

        $lista = [];

        foreach ((array) $registros as $indice => $registro) {
            if (! is_array($registro) || empty($registro['fecha_dosis'])) {
                continue;
            }

            try {
                $aplicada = Carbon::parse($registro['fecha_dosis'], self::ZONA)->startOfDay();
                $proxima = ! empty($registro['proxima_dosis']) ? Carbon::parse($registro['proxima_dosis'], self::ZONA)->startOfDay() : null;
            } catch (\Throwable $error) {
                continue;
            }

            $nombre = trim((string) ($tipo === 'vacuna' ? ($registro['vacuna'] ?? '') : ($registro['antiparasitario'] ?? '')));

            $lista[] = [
                'id' => (string) ($registro['id'] ?? $indice),
                'tipo' => $tipo,
                'nombre' => $nombre !== '' ? $nombre : ($tipo === 'vacuna' ? 'Vacuna' : 'Desparasitación'),
                'grupo' => $tipo === 'vacuna' ? $this->vacunaBase($nombre) : $this->tipoDesparasitacion($registro),
                'aplicada' => $aplicada,
                'proxima' => $proxima,
            ];
        }

        return array_values(array_filter($lista, function ($dosis) use ($lista, $tipo) {
            if (! $dosis['proxima']) {
                return false;
            }

            foreach ($lista as $otra) {
                if ($otra['id'] === $dosis['id'] || ! $otra['aplicada']->gt($dosis['aplicada'])) {
                    continue;
                }

                $cubre = $tipo === 'vacuna'
                    ? $otra['grupo'] === $dosis['grupo']
                    : ($otra['grupo'] === $dosis['grupo']
                        || ($otra['grupo'] === 'interno y externo' && in_array($dosis['grupo'], ['interno', 'externo'], true)));

                if ($cubre) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Nombre base de la vacuna para reconocer sus dosis siguientes:
     * "9 a 11 semanas · Triple felina 2ª dosis" queda como "triple felina".
     */
    private function vacunaBase(string $nombre): string
    {
        $base = \Illuminate\Support\Str::of($nombre)->afterLast('·')->lower()->ascii()->toString();
        $base = preg_replace('/\b(\d+\s*[aªo°º]?\s*)?(dosis|refuerzo)\b|\b(primera|segunda|tercera|cuarta|unica)\b/u', ' ', $base);

        return trim(preg_replace('/\s+/', ' ', $base));
    }

    /** Tipo de desparasitación en minúsculas; el tipo "Otro" usa lo que escribió el tutor */
    private function tipoDesparasitacion(array $registro): string
    {
        $tipo = mb_strtolower(trim((string) ($registro['tipo'] ?? '')));

        if ($tipo === 'otro' && ! empty($registro['tipo_otro'])) {
            return 'otro: '.mb_strtolower(trim((string) $registro['tipo_otro']));
        }

        return $tipo;
    }

    /** Recordatorio de renovación para los roles con plan pagado que vence pronto o ya venció */
    private function renovacionPlan(User $usuario, bool $esTutor): Collection
    {
        $hoy = Carbon::now(self::ZONA)->startOfDay();

        $roles = UsuarioRol::where('id_usuario', $usuario->id)
            ->activos()
            ->whereNotNull('id_plan')
            ->whereNotNull('fecha_vencimiento_plan')
            ->whereDate('fecha_vencimiento_plan', '<=', $hoy->copy()->addDays(self::DIAS_AVISO_PLAN)->toDateString())
            ->orderBy('fecha_vencimiento_plan')
            ->get();

        $nombres = ['tutor' => 'tutor', 'profesional' => 'profesional', 'asistente' => 'asistente', 'clinica' => 'clínica'];
        $enlace = $esTutor && Route::has('paciente.mascotas.pagos_suscripcion') ? route('paciente.mascotas.pagos_suscripcion') : null;

        return $roles->map(function (UsuarioRol $rol) use ($hoy, $nombres, $enlace) {
            // Se toma solo el día del vencimiento para no arrastrar la zona horaria del servidor
            $vence = Carbon::parse($rol->fecha_vencimiento_plan->toDateString(), self::ZONA)->startOfDay();
            $dias = (int) round($hoy->diffInDays($vence, false));
            $cuenta = $nombres[$rol->tipo] ?? $rol->tipo;

            if ($dias < 0) {
                $titulo = 'Tu plan de '.$cuenta.' venció';
                $texto = 'Venció el '.$vence->format('d-m-Y').'. Renuévalo para seguir usando todos los servicios.';
            } elseif ($dias === 0) {
                $titulo = 'Tu plan de '.$cuenta.' vence hoy';
                $texto = 'Renuévalo hoy para no perder tus servicios.';
            } else {
                $titulo = 'Tu plan de '.$cuenta.' vence en '.$dias.($dias === 1 ? ' día' : ' días');
                $texto = 'Vence el '.$vence->format('d-m-Y').'. Recuerda renovarlo.';
            }

            return [
                // La clave cambia con los días que faltan, así cada día vuelve a marcar el contador
                'clave' => 'plan_'.$rol->id.'_'.$vence->toDateString().'_'.$dias,
                'tipo' => 'plan',
                'icono' => 'icon-credit-card',
                'titulo' => $titulo,
                'texto' => $texto,
                'fecha' => $vence,
                'enlace' => $enlace,
            ];
        });
    }
}
