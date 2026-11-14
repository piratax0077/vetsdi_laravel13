<?php

namespace App\Http\Controllers;

use App\Models\CentroServiciosMascotas;
use App\Models\Ciudad;
use App\Models\Region;
use App\Models\EspecieMascota;
use App\Models\Mascota;
use App\Models\Paciente;
use App\Models\Peluqueria;
use App\Services\CentrosServiciosMascotas;
use App\Services\PeluqueriaAgenda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reservas que el tutor hace desde el modal "Reservar hora" de su escritorio
 * para servicios que no son de un veterinario: peluquería y hotel.
 * Peluquería usa la agenda y los procedimientos que cada peluquería configura en su perfil;
 * hotel usa los centros de guardería/hotel publicados, igual que su página de reserva.
 */
class ReservaServiciosTutorController extends Controller
{
    // Cada cuántos minutos se ofrece una hora de peluquería
    private const INTERVALO_PELUQUERIA = 30;

    private function tutor(): Paciente
    {
        $tutor = Paciente::where('id_usuario', Auth::id())->first();
        abort_unless($tutor, 403, 'No encontramos al tutor de la cuenta.');

        return $tutor;
    }

    private function zona(): string
    {
        return config('peluqueria.timezone') ?: 'America/Santiago';
    }

    /**
     * Peluquerías y hoteles guardan su dirección como texto (sin región ni comuna aparte),
     * así que se filtra buscando en la dirección el nombre de la comuna elegida o, si solo
     * hay región, el de alguna comuna de esa región o el de la región misma.
     */
    private function filtroUbicacion(Request $request): ?callable
    {
        $normalizar = fn ($texto) => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $texto));
        $nombres = [];

        if ($request->filled('id_ciudad')) {
            $nombres = Ciudad::where('id', (int) $request->input('id_ciudad'))->pluck('nombre')->all();
        } elseif ($request->filled('id_region')) {
            $nombres = Ciudad::where('id_region', (int) $request->input('id_region'))->pluck('nombre')->all();
            $nombres[] = Region::where('id', (int) $request->input('id_region'))->value('nombre');
        } else {
            return null;
        }

        $nombres = array_values(array_filter(array_map($normalizar, $nombres), fn ($n) => mb_strlen($n) > 2));

        return function (?string $direccion) use ($nombres, $normalizar) {
            $direccion = ' '.preg_replace('/[^a-z0-9]+/', ' ', $normalizar($direccion)).' ';
            foreach ($nombres as $nombre) {
                if (str_contains($direccion, ' '.preg_replace('/[^a-z0-9]+/', ' ', $nombre).' ')) {
                    return true;
                }
            }

            return false;
        };
    }

    /* ---------------------------------------------------------------------
     * Peluquería
     * ------------------------------------------------------------------- */

    // Peluquerías con sus sucursales activas y los procedimientos que ofrecen
    public function peluquerias(Request $request)
    {
        $enUbicacion = $this->filtroUbicacion($request);
        $peluquerias = Peluqueria::orderBy('nombre')->get();
        $sucursales = DB::table('peluqueria_sucursales')->whereIn('peluqueria_id', $peluquerias->pluck('id'))
            ->where('activo', true)->orderBy('id')->get()->groupBy('peluqueria_id');
        $procedimientos = DB::table('peluqueria_procedimientos')->whereIn('peluqueria_id', $peluquerias->pluck('id'))
            ->where('activo', true)->orderBy('nombre')->get()->groupBy('peluqueria_id');

        $lista = [];
        foreach ($peluquerias as $peluqueria) {
            foreach ($sucursales[$peluqueria->id] ?? [] as $sucursal) {
                if ($enUbicacion && !$enUbicacion($sucursal->direccion ?: $peluqueria->direccion)) {
                    continue;
                }
                // Sin horario de atención registrado la sucursal no aparece en la búsqueda
                if (empty($sucursal->agenda)) {
                    continue;
                }
                $lista[] = [
                    'peluqueria_id' => $peluqueria->id,
                    'sucursal_id' => $sucursal->id,
                    'nombre' => $peluqueria->nombre,
                    'sucursal' => $sucursal->nombre,
                    'direccion' => $sucursal->direccion ?: $peluqueria->direccion,
                    'telefono' => $sucursal->telefono ?: $peluqueria->telefono,
                    // Sin horario configurado no se pueden ofrecer horas
                    'con_agenda' => !empty($sucursal->agenda),
                    'procedimientos' => collect($procedimientos[$peluqueria->id] ?? [])->map(fn ($p) => [
                        'id' => $p->id,
                        'nombre' => $p->nombre,
                        'duracion' => (int) $p->duracion,
                        'precio' => $p->precio !== null ? (int) $p->precio : null,
                    ])->values(),
                ];
            }
        }

        return response()->json(['estado' => 1, 'registros' => $lista]);
    }

    // Días que atiende la sucursal y, si llega una fecha, las horas libres de ese día
    public function peluqueriaHoras(Request $request)
    {
        $datos = $request->validate([
            'sucursal_id' => 'required|integer|min:1',
            'duracion' => 'required|integer|min:1|max:480',
            'fecha' => 'nullable|date_format:Y-m-d',
        ]);
        $sucursal = DB::table('peluqueria_sucursales')->where('id', $datos['sucursal_id'])->where('activo', true)->first();
        abort_unless($sucursal, 404);
        $perfil = Peluqueria::findOrFail($sucursal->peluqueria_id);
        $horario = $sucursal->agenda ? json_decode($sucursal->agenda, true) : null;

        if (!$horario) {
            return response()->json(['estado' => 0, 'msj' => 'Esta peluquería aún no publica su horario de atención.']);
        }

        $dias = isset($horario['tramos'])
            ? array_values(array_unique(array_map(fn ($t) => (int) $t['dia'], $horario['tramos'])))
            : array_map('intval', $horario['dias'] ?? []);
        $ahora = Carbon::now($this->zona());
        $respuesta = [
            'estado' => 1,
            'dias' => $dias,
            'desde' => $ahora->copy()->addHours((int) ($horario['anticipacion_horas'] ?? 0))->toDateString(),
            'hasta' => $ahora->copy()->addDays((int) ($horario['anticipacion_dias'] ?? 30))->toDateString(),
            'horas' => [],
        ];

        if (!empty($datos['fecha'])) {
            $respuesta['horas'] = $this->horasLibresPeluqueria($perfil, $sucursal, $horario, $datos['fecha'], (int) $datos['duracion']);
        }

        return response()->json($respuesta);
    }

    private function horasLibresPeluqueria(Peluqueria $perfil, object $sucursal, array $horario, string $fecha, int $duracion): array
    {
        $dia = Carbon::parse($fecha, $this->zona())->startOfDay();
        $tramos = [];

        if (isset($horario['tramos'])) {
            foreach ($horario['tramos'] as $tramo) {
                if ((int) $tramo['dia'] === $dia->dayOfWeekIso) {
                    $tramos[] = [$tramo['inicio'], $tramo['fin']];
                }
            }
        } elseif (in_array($dia->dayOfWeekIso, array_map('intval', $horario['dias'] ?? []), true)) {
            if (!empty($horario['pausa_inicio']) && !empty($horario['pausa_fin'])) {
                $tramos[] = [$horario['apertura'], $horario['pausa_inicio']];
                $tramos[] = [$horario['pausa_fin'], $horario['cierre']];
            } else {
                $tramos[] = [$horario['apertura'], $horario['cierre']];
            }
        }

        $agenda = app(PeluqueriaAgenda::class);
        $horas = [];
        foreach ($tramos as [$abre, $cierra]) {
            $inicio = $dia->copy()->setTimeFromTimeString($abre);
            $limite = $dia->copy()->setTimeFromTimeString($cierra);
            while ($inicio->copy()->addMinutes($duracion)->lte($limite)) {
                // La misma validación que usa la peluquería al agendar: horario, anticipación, bloqueos y capacidad
                try {
                    $agenda->validar($perfil, $inicio->copy(), $inicio->copy()->addMinutes($duracion), $sucursal);
                    $horas[] = $inicio->format('H:i');
                } catch (ValidationException $e) {
                    // hora ocupada o fuera de plazo: no se ofrece
                }
                $inicio->addMinutes(self::INTERVALO_PELUQUERIA);
            }
        }

        return array_values(array_unique($horas));
    }

    // Deja la cita en la agenda de la peluquería, a nombre del tutor y su mascota
    public function peluqueriaReservar(Request $request)
    {
        $datos = $request->validate([
            'sucursal_id' => 'required|integer|min:1',
            'mascota_id' => 'required|integer|min:1',
            'procedimiento_ids' => 'required|array|min:1|max:20',
            'procedimiento_ids.*' => 'required|integer|distinct',
            'inicio' => 'required|date_format:Y-m-d\TH:i',
            'observaciones' => 'nullable|string|max:3000',
        ], [
            'procedimiento_ids.required' => 'Elija al menos un servicio de peluquería.',
            'inicio.required' => 'Elija el día y la hora.',
        ]);

        $tutor = $this->tutor();
        $mascota = Mascota::accesiblesPor($tutor)->seleccionables()->findOrFail($datos['mascota_id']);
        $sucursal = DB::table('peluqueria_sucursales')->where('id', $datos['sucursal_id'])->where('activo', true)->first();
        abort_unless($sucursal, 404);
        $perfil = Peluqueria::findOrFail($sucursal->peluqueria_id);

        if (empty($sucursal->agenda)) {
            throw ValidationException::withMessages(['inicio' => 'Esta peluquería aún no publica su horario de atención.']);
        }

        $catalogo = DB::table('peluqueria_procedimientos')->where('peluqueria_id', $perfil->id)
            ->whereIn('id', $datos['procedimiento_ids'])->where('activo', true)->get()->keyBy('id');
        if ($catalogo->count() !== count($datos['procedimiento_ids'])) {
            throw ValidationException::withMessages(['procedimiento_ids' => 'Uno de los servicios ya no está disponible.']);
        }
        $seleccionados = collect($datos['procedimiento_ids'])->map(fn ($id) => $catalogo[$id]);
        $duracion = max(1, (int) $seleccionados->sum('duracion'));

        $citaId = DB::transaction(function () use ($perfil, $sucursal, $tutor, $mascota, $seleccionados, $duracion, $datos) {
            Peluqueria::whereKey($perfil->id)->lockForUpdate()->firstOrFail();
            $sucursal = DB::table('peluqueria_sucursales')->where('id', $sucursal->id)->lockForUpdate()->first();

            $inicio = Carbon::parse($datos['inicio'], $this->zona());
            if ($inicio->lt(Carbon::now($this->zona()))) {
                throw ValidationException::withMessages(['inicio' => 'La reserva debe ser para una fecha y hora futura.']);
            }
            $fin = $inicio->copy()->addMinutes($duracion);
            app(PeluqueriaAgenda::class)->validar($perfil, $inicio, $fin, $sucursal);

            // La peluquería ve a la mascota como su cliente; si ya lo era se reutiliza
            $clienteId = DB::table('peluqueria_clientes')->where('peluqueria_id', $perfil->id)->where('mascota_id', $mascota->id)->value('id');
            if (!$clienteId) {
                $clienteId = DB::table('peluqueria_clientes')->insertGetId([
                    'peluqueria_id' => $perfil->id,
                    'rut' => $tutor->rut,
                    'mascota_id' => $mascota->id,
                    'tutor' => trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos),
                    'telefono' => $tutor->telefono_uno ?? '',
                    'email' => $tutor->email,
                    'mascota' => $mascota->nombre,
                    'especie' => optional(EspecieMascota::find($mascota->especie_id))->nombre ?: ($mascota->otra_especie ?: ''),
                    'raza' => optional($mascota->razaMascota)->nombre,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return DB::table('peluqueria_citas')->insertGetId([
                'peluqueria_id' => $perfil->id,
                'cliente_id' => $clienteId,
                'sucursal_id' => $sucursal->id,
                'personal_id' => null,
                'inicio' => $inicio,
                'fin' => $fin,
                'servicio' => $seleccionados->count() === 1 ? $seleccionados->first()->nombre : 'Reserva de '.$seleccionados->count().' servicios',
                'valor' => (int) $seleccionados->sum('precio'),
                'procedimiento_id' => $seleccionados->first()->id,
                'procedimientos_reservados' => json_encode($seleccionados->map(fn ($p) => [
                    'procedimiento_id' => $p->id, 'nombre' => $p->nombre, 'precio' => (int) $p->precio, 'duracion' => (int) $p->duracion,
                ])->values(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'observaciones' => $datos['observaciones'] ?? null,
                'estado' => 'reservada',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['estado' => 1, 'id' => $citaId, 'msj' => 'Hora de peluquería reservada.']);
    }

    /* ---------------------------------------------------------------------
     * Hotel / guardería
     * ------------------------------------------------------------------- */

    // Hoteles publicados con lo que ofrecen y sus valores; la reserva va a su solicitud de siempre
    public function hoteles(Request $request)
    {
        $servicio = app(CentrosServiciosMascotas::class);
        $enUbicacion = $this->filtroUbicacion($request);
        $hoteles = CentroServiciosMascotas::where('tipo', 'guarderia_hotel')->where('publicado', true)->orderBy('nombre')->get()
            ->map(function (CentroServiciosMascotas $centro) use ($servicio) {
                $config = $centro->configuracion ?? [];
                $disponibles = array_intersect_key($servicio->serviciosDisponibles($centro), ['hotel' => 1, 'guarderia' => 1]);

                return [
                    'id' => $centro->id,
                    'nombre' => $centro->nombre,
                    'direccion' => $centro->direccion,
                    'telefono' => $centro->telefono,
                    'servicios' => $disponibles,
                    'dias' => array_map('intval', $config['dias'] ?? []),
                    'precios' => [
                        'noche' => isset($config['precio_noche']) ? (int) $config['precio_noche'] : null,
                        'semana' => isset($config['precio_semana']) ? (int) $config['precio_semana'] : null,
                        'mes' => isset($config['precio_mes']) ? (int) $config['precio_mes'] : null,
                    ],
                    'solicitar' => route('paciente.centros_servicios.solicitar', $centro->id),
                ];
            })
            // Sin días de atención registrados el hotel no aparece en la búsqueda
            ->filter(fn ($hotel) => !empty($hotel['servicios']) && !empty($hotel['dias']) && (!$enUbicacion || $enUbicacion($hotel['direccion'])))
            ->values();

        return response()->json(['estado' => 1, 'registros' => $hoteles]);
    }
}
