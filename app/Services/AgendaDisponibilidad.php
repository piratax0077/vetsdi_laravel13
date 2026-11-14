<?php

namespace App\Services;

use App\Models\HoraMedica;
use App\Models\ProfesionalHorario;
use App\Models\ProfesionalHorariosBloqueo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Horas libres de la agenda de un profesional en un lugar de atención.
 *
 * Usa los mismos criterios que AccesoAgendaVeterinaria::guardarReserva, así toda hora que se
 * ofrece después se puede reservar:
 *  - el día del turno se guarda como 0 = domingo … 6 = sábado (un 7 antiguo también es domingo);
 *  - se recorren todos los turnos del día (mañana y tarde), no solo el primero;
 *  - los bloques avanzan de a la duración de consulta del turno y deben terminar dentro de él;
 *  - un bloque no se ofrece si ya pasó, si cae en un bloqueo activo de ese lugar o si choca con
 *    otra hora del profesional en cualquiera de sus lugares (rechazadas y anuladas no cuentan).
 */
class AgendaDisponibilidad
{
    public const ZONA = 'America/Santiago';

    // Rechazada, bloqueo anulado y hora anulada por el profesional: el bloque queda libre
    public const ESTADOS_LIBRES = [3, 14, 15];

    /** Días de un turno ("0,1", "3,4"…) como números del 0 (domingo) al 6 (sábado) */
    public static function diasDelTurno($dia): array
    {
        $dias = preg_split('/[^0-9]+/', (string) $dia, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_map(fn ($d) => ((int) $d) % 7, $dias)));
    }

    /** "00:30:00" → 30. Sin duración válida se usan bloques de 15 minutos */
    public static function minutos($duracion): int
    {
        $partes = explode(':', (string) $duracion);
        $total = ((int) ($partes[0] ?? 0)) * 60 + (int) ($partes[1] ?? 0);

        return $total > 0 ? $total : 15;
    }

    /** Turnos del profesional en uno o varios lugares (tipo_agenda opcional: "1", "1,2"…) */
    public function turnos(int $idProfesional, $lugares, $tipoAgenda = null): Collection
    {
        $lugares = array_values(array_filter(array_map('intval', (array) $lugares)));

        return ProfesionalHorario::where('id_profesional', $idProfesional)
            ->whereIn('id_lugar_atencion', $lugares)
            ->when(!empty($tipoAgenda), fn ($q) => $q->whereIn('tipo_agenda', explode(',', (string) $tipoAgenda)))
            ->orderBy('hora_inicio')
            ->get();
    }

    /** Días de la semana (0 = domingo … 6 = sábado) en que el profesional atiende en ese lugar */
    public function diasConAtencion(int $idProfesional, int $idLugar, $tipoAgenda = null): array
    {
        $dias = $this->turnos($idProfesional, $idLugar, $tipoAgenda)
            ->flatMap(fn ($turno) => self::diasDelTurno($turno->dia))
            ->unique()->sort()->values()->all();

        return array_map('intval', $dias);
    }

    /**
     * Bloques libres de un día. Devuelve [['hora' => 'H:i:s', 'duracion' => minutos], …] ordenados.
     * $duracionForzada sirve cuando la atención dura más que un bloque (por ejemplo varios servicios).
     */
    public function bloquesLibres(int $idProfesional, int $idLugar, string $fecha, $tipoAgenda = null, int $duracionForzada = 0): array
    {
        $dia = CarbonImmutable::parse($fecha, self::ZONA)->startOfDay();

        return $this->calcularBloques(
            $dia,
            $this->turnos($idProfesional, $idLugar, $tipoAgenda),
            $this->horasOcupadas($idProfesional, $dia, $dia),
            $this->bloqueos($idProfesional, $idLugar, $dia, $dia),
            $duracionForzada
        );
    }

    /**
     * Próxima hora libre en cada lugar, buscando hasta $dias días hacia adelante.
     * Devuelve [id_lugar => ['dia' => 'Y-m-d', 'hora' => 'H:i:s']] solo con los lugares que tienen hora.
     */
    public function proximaPorLugar(int $idProfesional, array $lugares, $tipoAgenda = null, int $dias = 60): array
    {
        $lugares = array_values(array_unique(array_filter(array_map('intval', $lugares))));
        if (!$lugares) {
            return [];
        }

        $desde = CarbonImmutable::now(self::ZONA)->startOfDay();
        $hasta = $desde->addDays($dias);

        // Todo se carga una vez y el cálculo de cada día se hace en memoria
        $turnos = $this->turnos($idProfesional, $lugares, $tipoAgenda);
        $ocupadas = $this->horasOcupadas($idProfesional, $desde, $hasta);
        $bloqueos = $this->bloqueos($idProfesional, $lugares, $desde, $hasta);
        $proximas = [];

        foreach ($lugares as $idLugar) {
            $turnosLugar = $turnos->where('id_lugar_atencion', $idLugar);
            if ($turnosLugar->isEmpty()) {
                continue;
            }
            $bloqueosLugar = $bloqueos->where('id_lugar_atencion', $idLugar);
            $diasLugar = $turnosLugar->flatMap(fn ($t) => self::diasDelTurno($t->dia))->unique()->all();

            for ($dia = $desde; $dia->lte($hasta); $dia = $dia->addDay()) {
                if (!in_array($dia->dayOfWeek, $diasLugar, true)) {
                    continue;
                }
                $bloques = $this->calcularBloques($dia, $turnosLugar, $ocupadas, $bloqueosLugar);
                if ($bloques) {
                    $proximas[$idLugar] = ['dia' => $dia->toDateString(), 'hora' => $bloques[0]['hora']];
                    break;
                }
            }
        }

        return $proximas;
    }

    /** Horas que ocupan la agenda del profesional (en cualquier lugar) entre dos fechas */
    public function horasOcupadas(int $idProfesional, CarbonImmutable $desde, CarbonImmutable $hasta): Collection
    {
        return HoraMedica::where('id_profesional', $idProfesional)
            ->whereBetween('fecha_consulta', [$desde->toDateString(), $hasta->toDateString()])
            ->whereNotIn('id_estado', self::ESTADOS_LIBRES)
            ->get(['fecha_consulta', 'hora_inicio', 'hora_termino']);
    }

    /** Bloqueos activos del profesional en uno o varios lugares que tocan el rango de fechas */
    public function bloqueos(int $idProfesional, $lugares, CarbonImmutable $desde, CarbonImmutable $hasta): Collection
    {
        return ProfesionalHorariosBloqueo::where('id_profesional', $idProfesional)
            ->whereIn('id_lugar_atencion', array_map('intval', (array) $lugares))
            ->where('estado', 1)
            ->whereDate('fecha_inicio', '<=', $hasta->toDateString())
            ->whereDate('fecha_termino', '>=', $desde->toDateString())
            ->get();
    }

    /** ¿El rango choca con algún bloqueo? (mismo criterio que guardarReserva) */
    public static function chocaConBloqueo(Collection $bloqueos, CarbonImmutable $inicio, CarbonImmutable $fin): bool
    {
        $dia = $inicio->toDateString();

        return $bloqueos->contains(function ($bloqueo) use ($inicio, $fin, $dia) {
            $fechaInicio = substr((string) $bloqueo->fecha_inicio, 0, 10);
            $fechaTermino = substr((string) $bloqueo->fecha_termino, 0, 10);
            if ($fechaInicio > $dia || $fechaTermino < $dia) {
                return false;
            }
            if ((int) $bloqueo->todo_dia === 1) {
                return true;
            }
            $desde = CarbonImmutable::parse($fechaInicio.' '.$bloqueo->hora_inicio, self::ZONA);
            $hasta = CarbonImmutable::parse($fechaTermino.' '.$bloqueo->hora_termino, self::ZONA);

            return $desde->lt($fin) && $hasta->gt($inicio);
        });
    }

    private function calcularBloques(CarbonImmutable $dia, Collection $turnos, Collection $ocupadas, Collection $bloqueos, int $duracionForzada = 0): array
    {
        $fecha = $dia->toDateString();
        $ahora = CarbonImmutable::now(self::ZONA);
        $ocupadasDia = $ocupadas->filter(fn ($h) => substr((string) $h->fecha_consulta, 0, 10) === $fecha);
        $bloques = [];

        foreach ($turnos as $turno) {
            if (!in_array($dia->dayOfWeek, self::diasDelTurno($turno->dia), true)) {
                continue;
            }

            // Los horarios se guardan con un segundo de desfase (09:30:01): se compara por minuto
            $apertura = CarbonImmutable::parse($fecha.' '.$turno->hora_inicio, self::ZONA)->startOfMinute();
            $cierre = CarbonImmutable::parse($fecha.' '.$turno->hora_termino, self::ZONA)->startOfMinute();
            $paso = self::minutos($turno->duracion_consulta);
            $largo = $duracionForzada > 0 ? $duracionForzada : $paso;

            for ($inicio = $apertura; $inicio->addMinutes($largo)->lte($cierre); $inicio = $inicio->addMinutes($paso)) {
                $fin = $inicio->addMinutes($largo);
                $hora = $inicio->format('H:i:s');

                if (isset($bloques[$hora]) || $inicio->lte($ahora) || self::chocaConBloqueo($bloqueos, $inicio, $fin)) {
                    continue;
                }

                $inicioTexto = $hora;
                $finTexto = $fin->format('H:i:s');
                $ocupada = $ocupadasDia->contains(fn ($h) => $h->hora_inicio < $finTexto && $h->hora_termino > $inicioTexto);
                if (!$ocupada) {
                    $bloques[$hora] = ['hora' => $hora, 'duracion' => $largo];
                }
            }
        }

        ksort($bloques);

        return array_values($bloques);
    }
}
