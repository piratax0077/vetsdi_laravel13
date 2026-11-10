<?php

namespace App\Http\Controllers;

use App\Models\{CentroServiciosMascotas, Mascota, Paciente};
use App\Services\{CentrosServiciosMascotas, CuentasService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CentroServiciosMascotasController extends Controller
{
    public function __construct(private CentrosServiciosMascotas $servicio)
    {
    }

    private function centro(Request $request): CentroServiciosMascotas
    {
        $tipo = strstr($request->route()->getName(), '.', true);
        return CentroServiciosMascotas::where('id_usuario', $request->user()->id)->where('tipo', $tipo)->firstOrFail();
    }

    private function tutor(Request $request): Paciente
    {
        return Paciente::where('id_usuario', $request->user()->id)->firstOrFail();
    }

    public function activar(Request $request)
    {
        $datos = $request->validate(['tipo' => ['required', Rule::in(CentrosServiciosMascotas::TIPOS)]]);
        app(CuentasService::class)->asignarRol($request->user(), $datos['tipo']);

        return redirect()->route('cuenta.entrar', $datos['tipo']);
    }

    public function index(Request $request)
    {
        $centro = $this->centro($request);
        $estado = $request->query('estado');
        $solicitudes = $this->servicio->solicitudes($centro)
            ->leftJoin('mascotas as m', 'm.id', '=', 'servicios_mascotas_solicitudes.mascota_id')
            ->leftJoin('pacientes as p', 'p.id', '=', 'servicios_mascotas_solicitudes.tutor_id')
            ->select('servicios_mascotas_solicitudes.*', 'm.nombre as mascota', 'p.nombres as tutor', 'p.apellido_uno')
            ->when(is_string($estado) && isset(CentrosServiciosMascotas::ESTADOS[$estado]), fn ($query) => $query->where('servicios_mascotas_solicitudes.estado', $estado))
            ->latest('servicios_mascotas_solicitudes.id')->paginate(20)->withQueryString();

        return view('app.centros_servicios.index', ['centro' => $centro, 'solicitudes' => $solicitudes]);
    }

    public function configuracion(Request $request)
    {
        return view('app.centros_servicios.configuracion', ['centro' => $this->centro($request)]);
    }

    public function guardarConfiguracion(Request $request)
    {
        $this->servicio->configurar($request, $this->centro($request));
        return back()->with('mensaje', 'Configuración guardada.');
    }

    public function inventario(Request $request)
    {
        $centro = $this->centro($request);
        $lugares = DB::table('cementerio_lugares')->where('centro_id', $centro->id)->orderBy('codigo')->paginate(20);

        return view('app.centros_servicios.inventario', compact('centro', 'lugares'));
    }

    public function guardarLugar(Request $request, ?int $id = null)
    {
        $centro = $this->centro($request);
        DB::transaction(function () use ($request, $centro, $id) {
            CentroServiciosMascotas::lockForUpdate()->findOrFail($centro->id);
            $datos = $request->validate([
                'codigo' => ['required', 'string', 'max:50', Rule::unique('cementerio_lugares')->where('centro_id', $centro->id)->ignore($id)],
                'sector' => 'required|string|max:100', 'descripcion' => 'nullable|string|max:250',
                'precio' => 'required|integer|min:0|max:100000000', 'disponible' => 'nullable|boolean',
            ]);
            $datos['disponible'] = $request->boolean('disponible');
            $datos['updated_at'] = now();
            if ($id === null) {
                DB::table('cementerio_lugares')->insert($datos + ['centro_id' => $centro->id, 'created_at' => now()]);
                return;
            }

            $lugar = DB::table('cementerio_lugares')->where('centro_id', $centro->id)->where('id', $id);
            abort_unless((clone $lugar)->exists(), 404);
            $ocupado = $this->servicio->solicitudes($centro)->where('lugar_id', $id)
                ->whereIn('estado', ['confirmada', 'en_servicio', 'finalizada'])->exists();
            if ($ocupado) unset($datos['disponible'], $datos['codigo'], $datos['sector']);
            $lugar->update($datos);
        });

        return back()->with('mensaje', 'Lugar guardado.');
    }

    public function operar(Request $request, int $id)
    {
        $this->servicio->operar($request, $this->centro($request), $id);

        return back()->with('mensaje', 'Gestión registrada.');
    }

    public function detalleCentro(Request $request, int $id)
    {
        $centro = $this->centro($request);
        $solicitud = $this->servicio->solicitudes($centro)->where('id', $id)->first();
        abort_unless($solicitud, 404);

        return view('app.centros_servicios.detalle', $this->detalle($solicitud, $centro) + [
            'eventos' => $this->servicio->eventosDisponibles($solicitud),
        ]);
    }

    public function catalogo(Request $request)
    {
        $tutor = $this->tutor($request);
        $centros = CentroServiciosMascotas::where('publicado', true)->orderBy('nombre')->paginate(15);
        $solicitudes = DB::table('servicios_mascotas_solicitudes as s')->join('centros_servicios_mascotas as c', 'c.id', '=', 's.centro_id')
            ->leftJoin('mascotas as m', 'm.id', '=', 's.mascota_id')->where('s.tutor_id', $tutor->id)
            ->select('s.*', 'c.nombre as centro', 'c.tipo', 'm.nombre as mascota')
            ->orderByDesc('s.id')->paginate(10, ['*'], 'solicitudes');

        return view('app.centros_servicios.catalogo', compact('centros', 'solicitudes'));
    }

    public function formulario(Request $request, int $id)
    {
        $centro = CentroServiciosMascotas::where('publicado', true)->findOrFail($id);
        $tutor = $this->tutor($request);
        $mascotas = Mascota::where('id_responsable', $tutor->id)->where('estado', 1)
            ->when($centro->tipo === 'guarderia_hotel', fn ($q) => $q->vivas())->orderBy('nombre')->get();

        return view('app.centros_servicios.reservar', [
            'centro' => $centro, 'mascotas' => $mascotas, 'servicios' => $this->servicio->serviciosDisponibles($centro),
            'lugares' => $centro->tipo === 'cementerio_mascotas' ? $this->servicio->lugaresParaTutor($centro, $tutor->id) : collect(),
        ]);
    }

    public function solicitar(Request $request, int $id)
    {
        $centro = CentroServiciosMascotas::where('publicado', true)->findOrFail($id);
        $solicitud = $this->servicio->solicitar($request, $centro, $this->tutor($request));

        return redirect()->route('paciente.centros_servicios.detalle', $solicitud)->with('mensaje', 'Solicitud recibida. El centro revisará la disponibilidad.');
    }

    public function detalleTutor(Request $request, int $id)
    {
        $solicitud = DB::table('servicios_mascotas_solicitudes')->where('tutor_id', $this->tutor($request)->id)->where('id', $id)->first();
        abort_unless($solicitud, 404);
        $centro = CentroServiciosMascotas::findOrFail($solicitud->centro_id);

        return view('app.centros_servicios.detalle_tutor', $this->detalle($solicitud, $centro));
    }

    public function cancelarTutor(Request $request, int $id)
    {
        $tutor = $this->tutor($request);
        DB::transaction(function () use ($request, $id, $tutor) {
            $consulta = DB::table('servicios_mascotas_solicitudes')->where('id', $id)->where('tutor_id', $tutor->id);
            $solicitud = (clone $consulta)->lockForUpdate()->first();
            abort_unless($solicitud, 404);
            abort_unless(in_array($solicitud->estado, ['pendiente', 'confirmada'], true), 409, 'La solicitud ya no se puede cancelar.');
            $consulta->update(['estado' => 'cancelada', 'updated_at' => now()]);
            DB::table('servicios_mascotas_bitacora')->insert(['solicitud_id' => $id, 'usuario_id' => $request->user()->id,
                'tipo' => 'estado', 'detalle' => 'Cancelada por el tutor.', 'created_at' => now(), 'updated_at' => now()]);
        });

        return back()->with('mensaje', 'Solicitud cancelada.');
    }

    private function detalle(object $solicitud, CentroServiciosMascotas $centro): array
    {
        return [
            'solicitud' => $solicitud, 'centro' => $centro,
            'mascota' => Mascota::find($solicitud->mascota_id),
            'tutor' => Paciente::find($solicitud->tutor_id),
            'lugar' => $solicitud->lugar_id ? DB::table('cementerio_lugares')->where('centro_id', $centro->id)->find($solicitud->lugar_id) : null,
            'bitacora' => DB::table('servicios_mascotas_bitacora')->where('solicitud_id', $solicitud->id)->orderBy('id')->get(),
        ];
    }
}
