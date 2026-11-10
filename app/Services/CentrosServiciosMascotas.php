<?php

namespace App\Services;

use App\Models\{CentroServiciosMascotas, Mascota, Paciente, User};
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};

class CentrosServiciosMascotas
{
    public const TIPOS = ['guarderia_hotel', 'cementerio_mascotas'];
    public const SERVICIOS = [
        'guarderia_hotel' => ['guarderia' => 'Cuidado diurno', 'hotel' => 'Alojamiento', 'peluqueria' => 'Acompañamiento a peluquería y belleza'],
        'cementerio_mascotas' => ['entierro' => 'Entierro', 'retiro' => 'Retiro a domicilio', 'lugar' => 'Compra de lugar'],
    ];
    public const ESTADOS = [
        'pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'en_servicio' => 'En servicio',
        'finalizada' => 'Finalizada', 'cancelada' => 'Cancelada',
    ];
    public const EVENTOS = [
        'retiro' => 'Retiro realizado', 'ingreso' => 'Ingreso registrado', 'asistencia' => 'Asistencia diaria', 'seguimiento' => 'Seguimiento',
        'peluqueria' => 'Informe de peluquería', 'entrega' => 'Entrega registrada',
        'entierro' => 'Informe de entierro', 'venta' => 'Venta registrada',
    ];
    public const PRECIOS = [
        'guarderia_hotel' => ['precio_semana' => 'Cuidado diurno semanal', 'precio_mes' => 'Cuidado diurno mensual',
            'precio_noche' => 'Alojamiento por noche', 'precio_retiro' => 'Retiro a domicilio', 'precio_peluqueria' => 'Acompañamiento a peluquería'],
        'cementerio_mascotas' => ['precio_retiro' => 'Retiro a domicilio', 'precio_entierro' => 'Entierro'],
    ];

    public function crear(Request $request, User $usuario, string $tipo): CentroServiciosMascotas
    {
        $datos = $request->validate($this->reglasCentro());

        return CentroServiciosMascotas::updateOrCreate(['id_usuario' => $usuario->id, 'tipo' => $tipo], $datos);
    }

    public function configurar(Request $request, CentroServiciosMascotas $centro): void
    {
        $reglas = $this->reglasCentro() + ['publicado' => 'nullable|boolean', 'ofrece_retiro' => 'nullable|boolean'];
        foreach (self::PRECIOS[$centro->tipo] as $clave => $nombre) $reglas[$clave] = 'nullable|integer|min:0|max:100000000';
        if ($centro->tipo === 'guarderia_hotel') {
            $reglas += [
                'cupos_guarderia' => 'required|integer|min:0|max:1000', 'cupos_hotel' => 'required|integer|min:0|max:1000',
                'dias' => 'required|array|min:1|max:7', 'dias.*' => 'required|integer|distinct|between:1,7',
                'ofrece_peluqueria' => 'nullable|boolean',
            ];
        } else {
            $reglas['ofrece_entierro'] = 'nullable|boolean';
        }
        $datos = $request->validate($reglas);
        $datos['publicado'] = $request->boolean('publicado');
        $configuracion = array_diff_key($datos, $this->reglasCentro(), ['publicado' => true]);
        foreach (['ofrece_retiro', 'ofrece_peluqueria', 'ofrece_entierro'] as $opcion) {
            if (isset($reglas[$opcion])) $configuracion[$opcion] = $request->boolean($opcion);
        }
        $centro->fill(array_intersect_key($datos, $this->reglasCentro() + ['publicado' => true]))
            ->fill(['configuracion' => $configuracion])->saveOrFail();
    }

    private function reglasCentro(): array
    {
        return ['nombre' => 'required|string|max:150', 'direccion' => 'required|string|max:250',
            'telefono' => 'required|string|max:30', 'email' => 'required|email:rfc|max:254'];
    }

    public function serviciosDisponibles(CentroServiciosMascotas $centro): array
    {
        $config = $centro->configuracion ?? [];
        $habilitados = $centro->tipo === 'guarderia_hotel'
            ? ['guarderia' => ($config['cupos_guarderia'] ?? 0) > 0, 'hotel' => ($config['cupos_hotel'] ?? 0) > 0,
                'peluqueria' => $config['ofrece_peluqueria'] ?? false]
            : ['lugar' => true, 'entierro' => $config['ofrece_entierro'] ?? false, 'retiro' => $config['ofrece_retiro'] ?? false];

        return array_intersect_key(self::SERVICIOS[$centro->tipo], array_filter($habilitados));
    }

    public function lugaresParaTutor(CentroServiciosMascotas $centro, int $tutor)
    {
        $propios = $this->solicitudes($centro)->where('tutor_id', $tutor)->where('servicio', 'lugar')
            ->where('estado', 'finalizada')->select('lugar_id');

        return DB::table('cementerio_lugares')->where('centro_id', $centro->id)->where(fn ($q) => $q
            ->where('disponible', true)->orWhereIn('id', $propios))->orderBy('codigo')->get();
    }

    public function solicitudes(CentroServiciosMascotas $centro)
    {
        return DB::table('servicios_mascotas_solicitudes')->where('centro_id', $centro->id);
    }

    public function solicitar(Request $request, CentroServiciosMascotas $centro, Paciente $tutor): int
    {
        $datos = $request->validate([
            'mascota_id' => 'required|integer|min:1', 'servicio' => ['required', Rule::in(array_keys(self::SERVICIOS[$centro->tipo]))],
            'inicio' => 'required|date_format:Y-m-d', 'fin' => 'nullable|date_format:Y-m-d',
            'periodo' => 'exclude_unless:servicio,guarderia|required|in:semana,mes',
            'cantidad' => 'exclude_unless:servicio,guarderia|required|integer|between:1,12',
            'retiro' => 'nullable|boolean', 'direccion_retiro' => 'nullable|string|max:250',
            'peluqueria' => 'nullable|boolean', 'lugar_id' => 'nullable|integer|min:1',
            'indicaciones' => 'nullable|string|max:2000',
        ]);
        $servicio = $datos['servicio'];
        $inicio = CarbonImmutable::parse($datos['inicio'], 'America/Santiago');
        if ($inicio->lt(today('America/Santiago'))) $this->error('inicio', 'Selecciona una fecha actual o futura.');

        $fin = match ($servicio) {
            'hotel' => isset($datos['fin']) ? CarbonImmutable::parse($datos['fin'], 'America/Santiago') : $inicio,
            'guarderia' => $datos['periodo'] === 'semana' ? $inicio->addWeeks($datos['cantidad']) : $inicio->addMonthsNoOverflow($datos['cantidad']),
            default => $inicio->addDay(),
        };
        if ($fin->lte($inicio) || $fin->gt($inicio->addYear())) $this->error('fin', 'El término debe ser posterior al inicio y quedar dentro de un año.');
        $retiro = $servicio === 'retiro' || $request->boolean('retiro');
        $peluqueria = $servicio === 'peluqueria' || $request->boolean('peluqueria');
        if ($retiro && empty($datos['direccion_retiro'])) $this->error('direccion_retiro', 'Indica la dirección del retiro.');

        return DB::transaction(function () use ($centro, $tutor, $datos, $servicio, $inicio, $fin, $retiro, $peluqueria) {
            $centro = CentroServiciosMascotas::lockForUpdate()->findOrFail($centro->id);
            $config = $centro->configuracion ?? [];
            if (!$centro->publicado || !isset($this->serviciosDisponibles($centro)[$servicio])) $this->error('servicio', 'El servicio ya no está disponible.');
            if ($retiro && (empty($config['ofrece_retiro']) || $servicio === 'lugar')) $this->error('retiro', 'Este servicio no incluye retiro a domicilio.');
            if ($peluqueria && empty($config['ofrece_peluqueria'])) $this->error('peluqueria', 'El centro no ofrece acompañamiento a peluquería.');
            $mascota = Mascota::where('id_responsable', $tutor->id)->where('estado', 1)->lockForUpdate()->findOrFail($datos['mascota_id']);
            if ($servicio !== 'lugar' && (bool) $mascota->fallecida !== ($centro->tipo === 'cementerio_mascotas')) {
                $this->error('mascota_id', 'La mascota no cumple las condiciones de este servicio.');
            }
            $lugar = in_array($servicio, ['entierro', 'lugar'], true)
                ? $this->lugar($centro, $tutor->id, $datos['lugar_id'] ?? null, $servicio) : null;
            if ($servicio === 'guarderia' && !in_array($inicio->dayOfWeekIso, array_map('intval', $config['dias'] ?? []), true)) {
                $this->error('inicio', 'El plan debe comenzar un día de atención de la guardería.');
            }

            $solicitud = [
                'centro_id' => $centro->id, 'tutor_id' => $tutor->id, 'mascota_id' => $mascota->id, 'lugar_id' => $lugar?->id,
                'servicio' => $servicio, 'estado' => 'pendiente', 'inicio' => $inicio->toDateString(), 'fin' => $fin->toDateString(),
                'periodo' => $datos['periodo'] ?? null, 'cantidad' => $datos['cantidad'] ?? 1, 'retiro' => $retiro,
                'direccion_retiro' => $retiro ? $datos['direccion_retiro'] : null, 'peluqueria' => $peluqueria,
                'indicaciones' => $datos['indicaciones'] ?? null, 'created_at' => now(), 'updated_at' => now(),
            ];
            $solicitud['precio'] = $this->precio($config, (object) $solicitud, $lugar);
            $this->validarCupo($centro, (object) $solicitud);

            return DB::table('servicios_mascotas_solicitudes')->insertGetId($solicitud);
        });
    }

    private function lugar(CentroServiciosMascotas $centro, int $tutor, ?int $id, string $servicio, ?int $solicitud = null): object
    {
        $lugar = DB::table('cementerio_lugares')->where('centro_id', $centro->id)->where('id', $id)->first();
        $propio = $lugar && $servicio === 'entierro' && $this->solicitudes($centro)->where('lugar_id', $id)
            ->where('servicio', 'lugar')->where('tutor_id', $tutor)->where('estado', 'finalizada')->exists();
        $reservado = $this->solicitudes($centro)->where('lugar_id', $id)
            ->when($solicitud, fn ($q) => $q->where('id', '!=', $solicitud))
            ->where(fn ($q) => $q->whereIn('estado', ['confirmada', 'en_servicio'])
                ->orWhere(fn ($q) => $q->where('servicio', 'entierro')->where('estado', 'finalizada')))->exists();

        if (!$lugar || (!$lugar->disponible && !$propio) || $reservado) $this->error('lugar_id', 'Selecciona un lugar disponible en este cementerio.');
        return $lugar;
    }

    private function precio(array $config, object $s, ?object $lugar): ?int
    {
        $base = match ($s->servicio) {
            'guarderia' => $config[$s->periodo === 'mes' ? 'precio_mes' : 'precio_semana'] ?? null,
            'hotel' => $config['precio_noche'] ?? null,
            'peluqueria' => $config['precio_peluqueria'] ?? null,
            'retiro' => $config['precio_retiro'] ?? null,
            'entierro' => $config['precio_entierro'] ?? null,
            'lugar' => $lugar->precio,
        };
        $retiro = $s->retiro && $s->servicio !== 'retiro' ? ($config['precio_retiro'] ?? null) : 0;
        $peluqueria = $s->peluqueria && $s->servicio !== 'peluqueria' ? ($config['precio_peluqueria'] ?? null) : 0;
        if ($base === null || $retiro === null || $peluqueria === null) return null;
        $unidades = $s->servicio === 'hotel' ? (int) CarbonImmutable::parse($s->inicio)->diffInDays($s->fin) : $s->cantidad;

        return (int) $base * $unidades + $retiro + $peluqueria + ($s->servicio === 'entierro' && $lugar->disponible ? (int) $lugar->precio : 0);
    }

    private function validarCupo(CentroServiciosMascotas $centro, object $s): void
    {
        if (!in_array($s->servicio, ['guarderia', 'hotel', 'peluqueria', 'entierro'], true)) return;
        $activas = DB::table('servicios_mascotas_solicitudes')->whereIn('estado', ['confirmada', 'en_servicio']);
        if (isset($s->id)) $activas->where('id', '!=', $s->id);
        if ($s->servicio === 'entierro' && DB::table('servicios_mascotas_solicitudes')->where('mascota_id', $s->mascota_id)
            ->where('servicio', 'entierro')->whereIn('estado', ['confirmada', 'en_servicio', 'finalizada'])
            ->when(isset($s->id), fn ($q) => $q->where('id', '!=', $s->id))->exists()) {
            $this->error('mascota_id', 'La mascota ya tiene un entierro registrado.');
        }
        $solapes = fn ($q) => $q->where('inicio', '<', $s->fin)->where('fin', '>', $s->inicio);
        if ($solapes((clone $activas)->where('mascota_id', $s->mascota_id)->whereIn('servicio', ['guarderia', 'hotel', 'peluqueria', 'entierro']))->exists()) {
            $this->error('inicio', 'La mascota ya tiene un servicio confirmado durante esas fechas.');
        }
        if (!in_array($s->servicio, ['guarderia', 'hotel'], true)) return;

        $config = $centro->configuracion ?? [];
        $limite = (int) ($config['cupos_'.$s->servicio] ?? 0);
        $ocupadas = $solapes($activas->where('centro_id', $centro->id)->where('servicio', $s->servicio))->get(['inicio', 'fin']);
        $dias = array_map('intval', $config['dias'] ?? []);
        for ($dia = CarbonImmutable::parse($s->inicio); $dia->lt($s->fin); $dia = $dia->addDay()) {
            if ($s->servicio === 'guarderia' && !in_array($dia->dayOfWeekIso, $dias, true)) continue;
            $fecha = $dia->toDateString();
            if ($ocupadas->filter(fn ($reserva) => $reserva->inicio <= $fecha && $reserva->fin > $fecha)->count() >= $limite) {
                $this->error('inicio', 'No hay cupos disponibles para todo el período seleccionado.');
            }
        }
    }

    private function error(string $campo, string $mensaje): never
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }

    public function operar(Request $request, CentroServiciosMascotas $centro, int $id): void
    {
        $datos = $request->validate([
            'accion' => ['required', Rule::in(['confirmar', 'iniciar', 'finalizar', 'cancelar', ...array_keys(self::EVENTOS)])],
            'detalle' => 'nullable|string|max:3000', 'precio' => 'nullable|integer|min:0|max:2000000000',
        ]);
        $accion = $datos['accion'];
        DB::transaction(function () use ($centro, $id, $accion, $datos, $request) {
            $centro = CentroServiciosMascotas::lockForUpdate()->findOrFail($centro->id);
            $s = $this->solicitudes($centro)->where('id', $id)->lockForUpdate()->first();
            abort_unless($s, 404);
            $evento = null;
            $cambios = [];
            if ($accion === 'confirmar' && $s->estado === 'pendiente') {
                $mascota = Mascota::lockForUpdate()->findOrFail($s->mascota_id);
                if ((int) $mascota->id_responsable !== (int) $s->tutor_id || (int) $mascota->estado !== 1 ||
                    ($s->servicio !== 'lugar' && (bool) $mascota->fallecida !== ($centro->tipo === 'cementerio_mascotas'))) {
                    $this->error('accion', 'La mascota ya no cumple las condiciones de esta solicitud.');
                }
                if (!isset($this->serviciosDisponibles($centro)[$s->servicio])) $this->error('accion', 'El servicio ya no está disponible.');
                if ($s->lugar_id) $this->lugar($centro, $s->tutor_id, $s->lugar_id, $s->servicio, $s->id);
                $this->validarCupo($centro, $s);
                if ($s->servicio === 'lugar') {
                    $evento = 'venta';
                    $cambios['estado'] = 'finalizada';
                    DB::table('cementerio_lugares')->where('id', $s->lugar_id)->update(['disponible' => false, 'updated_at' => now()]);
                } else {
                    $cambios['estado'] = 'confirmada';
                }
                if (isset($datos['precio'])) $cambios['precio'] = $datos['precio'];
            } elseif ($accion === 'cancelar' && in_array($s->estado, ['pendiente', 'confirmada'], true)) {
                $cambios['estado'] = 'cancelada';
            } elseif ($accion === 'iniciar' && $s->estado === 'confirmada') {
                $cambios['estado'] = 'en_servicio';
                $evento = 'ingreso';
            } elseif ($accion === 'finalizar' && $s->estado === 'en_servicio') {
                $obligatorios = array_filter(['retiro' => $s->retiro, 'peluqueria' => $s->peluqueria, 'entierro' => $s->servicio === 'entierro']);
                $registrados = DB::table('servicios_mascotas_bitacora')->where('solicitud_id', $id)->pluck('tipo')->all();
                if (array_diff(array_keys($obligatorios), $registrados)) $this->error('accion', 'Completa el retiro o los informes pendientes antes de finalizar.');
                $cambios['estado'] = 'finalizada';
                if ($s->servicio === 'entierro') DB::table('cementerio_lugares')->where('id', $s->lugar_id)->update(['disponible' => false, 'updated_at' => now()]);
            } elseif (in_array($s->estado, ['confirmada', 'en_servicio'], true) && in_array($accion, $this->eventosDisponibles($s), true)) {
                if (empty($datos['detalle'])) $this->error('detalle', 'Escribe el detalle de la gestión o del informe.');
                $evento = $accion;
            } else {
                $this->error('accion', 'La acción no corresponde al estado actual.');
            }

            if ($cambios) $this->solicitudes($centro)->where('id', $id)->update($cambios + ['updated_at' => now()]);
            DB::table('servicios_mascotas_bitacora')->insert([
                'solicitud_id' => $id, 'usuario_id' => $request->user()->id, 'tipo' => $evento ?? 'estado',
                'detalle' => $datos['detalle'] ?? self::ESTADOS[$cambios['estado']], 'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function eventosDisponibles(object $s): array
    {
        return array_keys(array_filter([
            'seguimiento' => true, 'asistencia' => $s->servicio === 'guarderia', 'retiro' => $s->retiro,
            'peluqueria' => $s->peluqueria, 'entrega' => in_array($s->servicio, ['guarderia', 'hotel', 'peluqueria'], true),
            'entierro' => $s->servicio === 'entierro',
        ]));
    }
}
