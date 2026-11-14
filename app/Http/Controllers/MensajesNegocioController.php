<?php

namespace App\Http\Controllers;

use App\Models\CentroServiciosMascotas;
use App\Models\Mensajes;
use App\Models\User;
use App\Services\PeluqueriaAcceso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Mensajes de los negocios de servicios (peluquería, centro estético, hotel y cementerio)
 * con sus tutores.
 *
 * Usa la misma tabla y los mismos criterios que el resto del sistema:
 * - Al tutor se le escribe con id_receptor = id del paciente y tipo 3 (directo) o 4 (difusión).
 * - La respuesta del tutor llega con id_receptor = id del usuario del negocio y tipo 1.
 * - Estado 1 es no leído y 0 leído.
 */
class MensajesNegocioController extends Controller
{
    private const TIPOS_PELUQUERIA = ['peluqueria', 'centro_estetico'];

    public function index(Request $request)
    {
        $contexto = $this->contexto($request);

        $recibidos = $this->consultaRecibidos()->orderByDesc('fecha_envio')->orderByDesc('id')->limit(100)->get();

        // Al llegar con ?mensaje=ID se abre ese mensaje y queda leído
        $abierto = null;
        if ($request->filled('mensaje')) {
            $abierto = $recibidos->firstWhere('id', (int) $request->query('mensaje'));
            if ($abierto && (int) $abierto->estado === 1) {
                $abierto->estado = 0;
                $abierto->save();
            }
        }

        $tutores = $this->tutores($contexto);
        $nombresTutores = $tutores->pluck('nombre', 'id');

        $enviados = Mensajes::where('id_usuario', Auth::id())
            ->whereIn('tipo_mensaje', [3, 4])
            ->orderByDesc('id')->limit(50)->get()
            ->map(function ($mensaje) use ($nombresTutores) {
                $datos = $this->datos($mensaje);

                return [
                    'asunto' => $datos['asunto'] ?? $datos['titulo'] ?? 'Sin asunto',
                    'destinatario' => $nombresTutores[$mensaje->id_receptor] ?? 'Tutor',
                    'tipo' => (int) $mensaje->tipo_mensaje === 4 ? 'Difusión' : 'Directo',
                    'fecha' => $this->fecha($mensaje),
                ];
            });

        return view('app.general.mensajes.negocio', $contexto['vista'] + [
            'layoutMensajes' => $contexto['layout'],
            'rutaBaseMensajes' => $contexto['base'],
            'tutores' => $tutores,
            'recibidos' => $recibidos->map(fn ($mensaje) => $this->formatearRecibido($mensaje)),
            'abierto' => $abierto ? $this->formatearRecibido($abierto) : null,
            'enviados' => $enviados,
        ]);
    }

    public function enviar(Request $request)
    {
        $contexto = $this->contexto($request);
        $tutores = $this->tutores($contexto);

        $datos = $request->validate([
            'destinatario' => 'required|string|max:20',
            'asunto' => 'required|string|max:150',
            'mensaje' => 'required|string|max:3000',
        ], [], ['destinatario' => 'destinatario', 'asunto' => 'asunto', 'mensaje' => 'mensaje']);

        $paraTodos = $datos['destinatario'] === 'todos';
        $destinatarios = $paraTodos
            ? $tutores->pluck('id')
            : $tutores->pluck('id')->intersect([(int) $datos['destinatario']]);

        if ($destinatarios->isEmpty()) {
            return back()->withInput()->withErrors(['destinatario' => 'Elige un tutor de tu lista de clientes.']);
        }

        $contenido = json_encode([
            'asunto' => $datos['asunto'],
            'mensaje' => $datos['mensaje'],
            'negocio' => $contexto['nombre'],
        ], JSON_UNESCAPED_UNICODE);

        DB::transaction(function () use ($destinatarios, $contenido, $paraTodos) {
            foreach ($destinatarios as $idTutor) {
                $mensaje = new Mensajes();
                $mensaje->id_usuario = Auth::id();
                $mensaje->id_receptor = $idTutor;
                $mensaje->datos_mensaje = $contenido;
                $mensaje->tipo_mensaje = $paraTodos ? 4 : 3;
                $mensaje->fecha_envio = Carbon::now()->format('Y-m-d H:i:s');
                $mensaje->estado = 1;
                $mensaje->save();
            }
        });

        $cantidad = $destinatarios->count();

        return redirect()->route($contexto['base'].'.mensajes')
            ->with('mensaje', $cantidad === 1 ? 'Mensaje enviado.' : "Mensaje enviado a {$cantidad} tutores.");
    }

    /** Mensajes que recibe la cuenta del negocio; quedan fuera los tipos del tutor, que usan el id del paciente. */
    private function consultaRecibidos()
    {
        return Mensajes::where('id_receptor', Auth::id())
            ->where(function ($consulta) {
                $consulta->whereNotIn('tipo_mensaje', MensajesTutorController::TIPOS_TUTOR)
                    ->orWhereNull('tipo_mensaje');
            });
    }

    /** Negocio activo según la ruta: plantilla, nombre y datos que necesita su layout. */
    private function contexto(Request $request): array
    {
        $base = strstr((string) $request->route()->getName(), '.', true);

        if (in_array($base, self::TIPOS_PELUQUERIA, true)) {
            $acceso = app(PeluqueriaAcceso::class);
            $perfil = $acceso->perfil();
            $permisos = $acceso->permisos($perfil);

            // Lo mismo que comparte PeluqueriaController con su layout
            view()->share([
                'peluqueriaRutaBase' => $acceso->rutaBase(),
                'esCentroEstetico' => $acceso->rutaBase() === 'centro_estetico',
                'peluqueriaPermisos' => $permisos,
                'peluqueriaSucursales' => $acceso->sucursales($perfil),
                'peluqueriaContextos' => $acceso->opciones(),
            ]);

            return [
                'base' => $acceso->rutaBase(),
                'layout' => 'app.peluqueria.layout',
                'nombre' => (string) $perfil->nombre,
                'vista' => ['perfil' => $perfil],
                'peluqueria' => $perfil,
                'verClientes' => (bool) ($permisos['ver_clientes'] ?? false),
            ];
        }

        $centro = CentroServiciosMascotas::where('id_usuario', $request->user()->id)->where('tipo', $base)->firstOrFail();

        return [
            'base' => $base,
            'layout' => 'app.centros_servicios.layout',
            'nombre' => (string) $centro->nombre,
            'vista' => ['centro' => $centro],
            'centro' => $centro,
        ];
    }

    /**
     * Tutores del negocio que tienen cuenta en el sistema: solo a ellos les llega el mensaje.
     * Peluquería: los de sus clientes (por mascota registrada o por RUT).
     * Hotel y cementerio: los que le han hecho solicitudes.
     */
    private function tutores(array $contexto): Collection
    {
        $ids = collect();

        if (isset($contexto['peluqueria'])) {
            if (! $contexto['verClientes']) {
                return collect();
            }

            $clientes = DB::table('peluqueria_clientes')->where('peluqueria_id', $contexto['peluqueria']->id)->get(['mascota_id', 'rut']);

            $idsMascotas = $clientes->pluck('mascota_id')->filter()->unique();
            if ($idsMascotas->isNotEmpty()) {
                $ids = $ids->merge(DB::table('mascotas')->whereIn('id', $idsMascotas)->pluck('id_responsable'));
            }

            $ruts = $clientes->pluck('rut')
                ->map(fn ($rut) => strtoupper(preg_replace('/[.\s-]/', '', (string) $rut)))
                ->filter()->unique();
            if ($ruts->isNotEmpty()) {
                $ids = $ids->merge(
                    DB::table('pacientes')->whereIn(DB::raw("UPPER(REPLACE(REPLACE(rut,'.',''),'-',''))"), $ruts->all())->pluck('id')
                );
            }
        } else {
            $ids = DB::table('servicios_mascotas_solicitudes')->where('centro_id', $contexto['centro']->id)->pluck('tutor_id');
        }

        $ids = $ids->filter()->unique();
        if ($ids->isEmpty()) {
            return collect();
        }

        return DB::table('pacientes')->whereIn('id', $ids)->whereNotNull('id_usuario')
            ->orderBy('nombres')->orderBy('apellido_uno')
            ->get(['id', 'nombres', 'apellido_uno', 'apellido_dos', 'rut'])
            ->map(fn ($tutor) => [
                'id' => (int) $tutor->id,
                'nombre' => trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos) ?: 'Tutor',
                'rut' => (string) $tutor->rut,
            ]);
    }

    private function datos(Mensajes $mensaje): array
    {
        $datos = json_decode((string) $mensaje->datos_mensaje, true);

        return is_array($datos) ? $datos : [];
    }

    private function fecha(Mensajes $mensaje): string
    {
        // fecha_envio solo guarda el día; la hora sale de created_at
        $fecha = $mensaje->created_at ?: $mensaje->fecha_envio;

        return $fecha ? Carbon::parse($fecha)->format('d-m-Y H:i') : '';
    }

    private function formatearRecibido(Mensajes $mensaje): array
    {
        $datos = $this->datos($mensaje);
        $remitente = User::find($mensaje->id_usuario);

        return [
            'id' => $mensaje->id,
            'asunto' => trim((string) ($datos['asunto'] ?? $datos['titulo'] ?? '')) ?: 'Sin asunto',
            'mensaje' => (string) ($datos['mensaje'] ?? ''),
            'remitente' => trim((string) ($datos['remitente'] ?? '')) ?: ($remitente ? $remitente->nombreParaMostrar() : 'Tutor'),
            'fecha' => $this->fecha($mensaje),
            'leido' => (int) $mensaje->estado !== 1,
        ];
    }
}
