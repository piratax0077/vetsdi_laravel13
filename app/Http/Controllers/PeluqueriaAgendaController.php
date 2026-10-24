<?php
namespace App\Http\Controllers;

use App\Models\Peluqueria;
use App\Services\CuentasService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Agenda de peluquería: guarda las horas del modal "Agendar hora", reconoce al tutor por su RUT y entrega
// las atenciones al calendario. La ficha y el resto del módulo siguen en PeluqueriaController.
// Las citas se guardan con los mismos campos que lee la ficha (servicio, valor, procedimiento_id y estado)
class PeluqueriaAgendaController extends Controller
{
    private function perfil(): Peluqueria {
        abort_unless(app(CuentasService::class)->tieneRol(Auth::user(), 'peluqueria'), 403);
        return Peluqueria::where('id_usuario', Auth::id())->firstOrFail();
    }

    // Deja solo las columnas que existen: si la migración de la agenda aún no se corre, se guarda lo de siempre
    private function soloExistentes(string $tabla, array $valores): array {
        static $columnas = [];
        $columnas[$tabla] ??= array_flip(Schema::getColumnListing($tabla));
        return array_intersect_key($valores, $columnas[$tabla]);
    }

    // RUT sin puntos, con guión y K mayúscula (12345678-5); null si el dígito verificador no calza
    public static function rutNormalizado(?string $rut): ?string {
        $limpio = strtoupper(preg_replace('/[^0-9kK]/', '', (string) $rut));
        $cuerpo = substr($limpio, 0, -1);
        $dv = substr($limpio, -1);
        if (strlen($cuerpo) < 7 || !ctype_digit($cuerpo)) return null;
        $suma = 0;
        $multiplo = 2;
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += (int) $cuerpo[$i] * $multiplo;
            $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
        }
        $esperado = 11 - ($suma % 11);
        $esperado = $esperado === 11 ? '0' : ($esperado === 10 ? 'K' : (string) $esperado);
        return $dv === $esperado ? $cuerpo . '-' . $dv : null;
    }

    // Precio y duración que le tocan a la mascota según la lista del procedimiento, con los mismos criterios del modal:
    // gana el más específico, primero por especie y tipo de exótico y después por tamaño o peso.
    // Devuelve [precio elegido o null, true si falta el peso para decidir]
    private function tarifa(object $procedimiento, ?string $especie, ?string $exotico, ?float $peso): array {
        $lista = Schema::hasTable('peluqueria_procedimiento_precios')
            ? DB::table('peluqueria_procedimiento_precios')->where('procedimiento_id', $procedimiento->id)->orderBy('orden')->orderBy('id')->get()
            : collect();
        if ($lista->isEmpty()) {
            return [(object) ['id' => null, 'precio' => $procedimiento->precio, 'duracion' => $procedimiento->duracion], false];
        }

        $normal = fn ($texto) => Str::lower(Str::ascii(trim((string) $texto)));
        $puntosEspecie = function ($precio) use ($especie, $exotico, $normal) {
            if (!$precio->especie || $precio->especie === 'Todas') return 0;
            if ($normal($precio->especie) !== $normal($especie)) return -1;
            if ($precio->especie === 'Exótico' && $precio->especie_exotico) {
                return $normal($precio->especie_exotico) === $normal($exotico) ? 4 : -1;
            }
            return 2;
        };
        $porTamano = fn ($precio) => $precio->tamano && $precio->tamano !== 'Todos';
        // Pequeño hasta 10 kg, Mediano de 10 a 25 kg y Grande más de 25 kg, igual que en el panel de configuración
        $calzaTamano = fn ($precio) => match ($precio->tamano) {
            'Pequeño' => $peso <= 10,
            'Mediano' => $peso > 10 && $peso <= 25,
            'Grande' => $peso > 25,
            'Peso' => $peso >= (float) $precio->peso_desde && $peso <= (float) $precio->peso_hasta,
            default => true,
        };

        if ($peso === null && $lista->contains(fn ($precio) => $puntosEspecie($precio) >= 0 && $porTamano($precio))) {
            return [null, true];
        }
        $elegido = null;
        $puntaje = -1;
        foreach ($lista as $precio) {
            $puntos = $puntosEspecie($precio);
            if ($puntos < 0) continue;
            if ($porTamano($precio)) {
                if ($peso === null || !$calzaTamano($precio)) continue;
                $puntos++;
            }
            if ($puntos > $puntaje) {
                $elegido = $precio;
                $puntaje = $puntos;
            }
        }
        return [$elegido, false];
    }

    public function guardar(Request $request) {
        $perfil = $this->perfil();
        $nuevoCliente = $request->boolean('nuevo_cliente');
        $datos = $request->validate([
            'procedimiento_id' => 'required|integer',
            'inicio' => 'required|date_format:Y-m-d\TH:i|after_or_equal:now',
            'observaciones' => 'nullable|string|max:3000',
            'rut' => ['nullable', 'string', 'max:12', function ($campo, $valor, $falla) {
                if ($valor !== null && $valor !== '' && !self::rutNormalizado($valor)) $falla('El RUT del tutor no es válido.');
            }],
            'cliente_id' => $nuevoCliente ? 'nullable' : 'required|integer',
            'peso' => 'nullable|numeric|min:0.1|max:150',
            'forma_pago' => 'nullable|in:efectivo,en_linea',
            'monto_recibido' => 'nullable|integer|min:0|max:999999999',
        ]);
        $rut = self::rutNormalizado($datos['rut'] ?? null);

        $cliente = null;
        $registrado = null;
        if ($nuevoCliente) {
            $cliente = $request->validate([
                'tutor' => 'required|string|max:255',
                'nombres' => 'nullable|string|max:150',
                'apellido_uno' => 'nullable|string|max:100',
                'telefono' => 'required|string|max:30',
                'email' => 'nullable|email|max:255',
                'direccion' => 'nullable|string|max:255',
                'id_ciudad' => 'nullable|integer|exists:ciudades,id',
                'id_region' => 'nullable|integer|exists:regiones,id',
                'mascota' => 'required|string|max:100',
                'especie' => 'required|string|max:60',
                'especie_exotico' => 'nullable|required_if:especie,Exótico|string|max:100',
                'raza' => 'nullable|string|max:100',
                'cuidados' => 'nullable|string|max:3000',
            ]);
            if ($cliente['especie'] !== 'Exótico') $cliente['especie_exotico'] = null;
            $cliente['rut'] = $rut;
            // Mascota nueva de un tutor ya registrado: se completa con los datos que el tutor tiene en sus otras mascotas
            if ($rut && Schema::hasColumn('peluqueria_clientes', 'rut')) {
                $tutor = DB::table('peluqueria_clientes')->where('peluqueria_id', $perfil->id)->where('rut', $rut)->orderByDesc('id')->first();
                foreach (['nombres', 'apellido_uno', 'telefono', 'email', 'direccion', 'id_ciudad', 'id_region'] as $campo) {
                    if (empty($cliente[$campo]) && !empty($tutor->$campo)) $cliente[$campo] = $tutor->$campo;
                }
            }
        } else {
            $registrado = DB::table('peluqueria_clientes')->where('peluqueria_id', $perfil->id)->where('id', $datos['cliente_id'])->first();
            abort_unless($registrado, 404);
        }

        $procedimiento = DB::table('peluqueria_procedimientos')->where('peluqueria_id', $perfil->id)
            ->where('id', $datos['procedimiento_id'])->where('activo', true)->first();
        abort_unless($procedimiento, 404);

        $mascota = $registrado ? (array) $registrado : $cliente;
        $peso = isset($datos['peso']) ? (float) $datos['peso'] : null;
        [$tarifa, $faltaPeso] = $this->tarifa($procedimiento, $mascota['especie'] ?? null, $mascota['especie_exotico'] ?? null, $peso);
        if ($faltaPeso) throw ValidationException::withMessages(['peso' => 'Indica el peso de la mascota para calcular el valor del procedimiento.']);
        if (!$tarifa) throw ValidationException::withMessages(['procedimiento_id' => 'Este procedimiento no tiene un precio para esta mascota.']);
        $valor = (int) $tarifa->precio;
        $duracion = (int) $tarifa->duracion;

        // En efectivo, con el monto recibido queda pagado; sin monto, o en línea mientras no haya pasarela, queda pendiente
        $formaPago = $datos['forma_pago'] ?? null;
        $monto = $formaPago === 'efectivo' && isset($datos['monto_recibido']) ? (int) $datos['monto_recibido'] : null;
        if ($monto !== null && $monto < $valor) {
            throw ValidationException::withMessages(['monto_recibido' => 'El monto recibido no alcanza el valor de $' . number_format($valor, 0, ',', '.') . '.']);
        }
        $estadoPago = $formaPago === null ? null : ($monto !== null ? 'pagado' : 'pendiente');

        $citaId = DB::transaction(function () use ($perfil, $datos, $cliente, $registrado, $procedimiento, $tarifa, $valor, $duracion, $peso, $formaPago, $monto, $estadoPago) {
            Peluqueria::whereKey($perfil->id)->lockForUpdate()->firstOrFail();
            $inicio = Carbon::createFromFormat('Y-m-d\TH:i', $datos['inicio']);
            $fin = $inicio->copy()->addMinutes($duracion);
            if (DB::table('peluqueria_citas')->where('peluqueria_id', $perfil->id)->where('estado', '<>', 'cancelada')
                ->where('inicio', '<', $fin)->where('fin', '>', $inicio)->exists()) {
                throw ValidationException::withMessages(['inicio' => 'Ya existe una reserva en ese horario.']);
            }
            $clienteId = $registrado->id ?? DB::table('peluqueria_clientes')->insertGetId($this->soloExistentes('peluqueria_clientes',
                $cliente + ['peluqueria_id' => $perfil->id, 'created_at' => now(), 'updated_at' => now()]));
            // La hora agendada desde el modal ya pasó por el pago, así que queda confirmada y lista para abrir la ficha
            return DB::table('peluqueria_citas')->insertGetId($this->soloExistentes('peluqueria_citas', [
                'peluqueria_id' => $perfil->id, 'cliente_id' => $clienteId, 'procedimiento_id' => $procedimiento->id,
                'servicio' => $procedimiento->nombre, 'valor' => $valor, 'inicio' => $inicio, 'fin' => $fin,
                'observaciones' => $datos['observaciones'] ?? null, 'estado' => 'confirmada',
                'peso' => $peso, 'procedimiento_precio_id' => $tarifa->id ?? null,
                'forma_pago' => $formaPago, 'monto_recibido' => $monto, 'estado_pago' => $estadoPago,
                'created_at' => now(), 'updated_at' => now(),
            ]));
        });

        if ($request->expectsJson()) {
            $guardado = DB::table('peluqueria_clientes as m')->join('peluqueria_citas as c', 'c.cliente_id', '=', 'm.id')
                ->where('c.id', $citaId)->where('c.peluqueria_id', $perfil->id)->select('m.id', 'm.mascota', 'm.tutor')->first();
            return response()->json([
                'estado' => 1, 'mensaje' => 'Hora agendada correctamente.', 'cita_id' => $citaId,
                'ficha_url' => route('peluqueria.ficha', $citaId), 'cliente' => $guardado,
                'valor' => $valor, 'duracion' => $duracion,
                'pago' => ['forma_pago' => $formaPago, 'estado_pago' => $estadoPago, 'monto_recibido' => $monto],
            ]);
        }
        return back()->with('mensaje', 'Hora agendada.');
    }

    // Tutor y mascotas registrados con ese RUT en la peluquería; 404 si no hay ninguno
    public function buscarTutor(Request $request) {
        $perfil = $this->perfil();
        $rut = self::rutNormalizado($request->query('rut'));
        if (!$rut || !Schema::hasColumn('peluqueria_clientes', 'rut')) return response()->json(['mensaje' => 'Tutor no registrado.'], 404);
        $mascotas = DB::table('peluqueria_clientes')->where('peluqueria_id', $perfil->id)->where('rut', $rut)->orderBy('mascota')->get();
        if ($mascotas->isEmpty()) return response()->json(['mensaje' => 'Tutor no registrado.'], 404);

        $tutor = $mascotas->sortByDesc('id')->first();
        $comuna = !empty($tutor->id_ciudad) ? DB::table('ciudades')->where('id', $tutor->id_ciudad)->value('nombre') : null;
        return response()->json([
            'tutor' => [
                'tutor' => $tutor->tutor, 'telefono' => $tutor->telefono, 'email' => $tutor->email,
                'direccion' => collect([$tutor->direccion ?? null, $comuna])->filter()->implode(', '),
            ],
            'mascotas' => $mascotas->map(fn ($mascota) => [
                'id' => $mascota->id, 'nombre' => $mascota->mascota, 'especie' => $mascota->especie,
                'especie_exotico' => $mascota->especie_exotico ?? '', 'raza' => $mascota->raza, 'cuidados' => $mascota->cuidados,
            ])->values(),
        ]);
    }

    // Atenciones del calendario, con lo que muestra el modal de estado: tutor, valor y pago
    public function eventos(Request $request) {
        $perfil = $this->perfil();
        $datos = $request->validate(['start' => 'required|date', 'end' => 'required|date|after:start']);
        $inicio = Carbon::parse($datos['start']);
        $fin = Carbon::parse($datos['end']);
        abort_if($inicio->diffInDays($fin) > 93, 422);
        $citas = DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m', 'm.id', '=', 'c.cliente_id')
            ->where('c.peluqueria_id', $perfil->id)->where('c.estado', '<>', 'cancelada')
            ->where('c.inicio', '<', $fin)->where('c.fin', '>', $inicio)
            ->select('c.*', 'm.mascota', 'm.tutor')->get();
        return response()->json($citas->map(fn ($cita) => [
            'id' => $cita->id, 'title' => $cita->mascota . ' · ' . $cita->servicio,
            'start' => Carbon::parse($cita->inicio)->format('Y-m-d\TH:i:s'), 'end' => Carbon::parse($cita->fin)->format('Y-m-d\TH:i:s'),
            'url' => route('peluqueria.ficha', $cita->id),
            'color' => match ($cita->estado) { 'finalizada' => '#31a69c', 'en_atencion' => '#7043b5', default => '#df9b25' },
            'mascota' => $cita->mascota, 'servicio' => $cita->servicio, 'tutor' => $cita->tutor,
            'estado' => $cita->estado, 'valor' => (int) $cita->valor,
            'forma_pago' => $cita->forma_pago ?? null, 'estado_pago' => $cita->estado_pago ?? null,
            'monto_recibido' => isset($cita->monto_recibido) ? (int) $cita->monto_recibido : null,
        ]));
    }
}
