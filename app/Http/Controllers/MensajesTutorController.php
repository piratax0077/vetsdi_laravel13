<?php

namespace App\Http\Controllers;

use App\Models\Mensajes;
use App\Models\Paciente;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Bandeja de mensajes del tutor.
 *
 * Los mensajes al tutor se guardan con id_receptor = id del paciente (no el id de usuario)
 * y estos tipos: 3 directo, 4 difusión y 5 aviso del sistema (por ejemplo apareamiento).
 * Estado 1 es no leído y 0 leído.
 */
class MensajesTutorController extends Controller
{
    public const TIPOS_TUTOR = [3, 4, 5];

    /** Consulta de los mensajes del tutor conectado; null si la cuenta no tiene ficha de tutor. */
    public static function consultaDelTutor()
    {
        $paciente = Auth::check() ? Paciente::where('id_usuario', Auth::id())->first() : null;

        if (! $paciente) {
            return null;
        }

        return Mensajes::where('id_receptor', $paciente->id)->whereIn('tipo_mensaje', self::TIPOS_TUTOR);
    }

    public function index()
    {
        $consulta = self::consultaDelTutor();
        $mensajes = $consulta
            ? $consulta->orderByDesc('fecha_envio')->orderByDesc('id')->get()->map(fn ($mensaje) => $this->formatear($mensaje))
            : collect();

        return view('app.paciente.mensajes', [
            'mensajes' => $mensajes,
            'sinLeer' => $mensajes->where('leido', false)->count(),
        ]);
    }

    /** Devuelve el mensaje completo y lo marca como leído. */
    public function ver($id)
    {
        $consulta = self::consultaDelTutor();
        $mensaje = $consulta ? $consulta->where('id', $id)->first() : null;

        if (! $mensaje) {
            return response()->json(['estado' => 0, 'mensaje' => 'No encontramos ese mensaje.'], 404);
        }

        if ((int) $mensaje->estado === 1) {
            $mensaje->estado = 0;
            $mensaje->save();
        }

        return response()->json(['estado' => 1, 'datos' => $this->formatear($mensaje)]);
    }

    /**
     * Respuesta del tutor a un negocio de servicios (peluquería, hotel o cementerio).
     * Llega a la bandeja del usuario que escribió, como mensaje directo (tipo 1).
     */
    public function responder(\Illuminate\Http\Request $request, $id)
    {
        $consulta = self::consultaDelTutor();
        $original = $consulta ? $consulta->where('id', $id)->first() : null;
        $datosOriginal = $original ? json_decode((string) $original->datos_mensaje, true) : null;

        if (! $original || ! is_array($datosOriginal) || empty($datosOriginal['negocio']) || ! $original->id_usuario) {
            return response()->json(['estado' => 0, 'mensaje' => 'Este mensaje no admite respuesta.'], 422);
        }

        $datos = $request->validate(['mensaje' => 'required|string|max:3000']);
        $asunto = 'Re: '.trim((string) ($datosOriginal['asunto'] ?? $datosOriginal['titulo'] ?? 'Mensaje'));

        $respuesta = new Mensajes();
        $respuesta->id_usuario = Auth::id();
        $respuesta->id_receptor = (int) $original->id_usuario;
        $respuesta->datos_mensaje = json_encode([
            'titulo' => $asunto,
            'asunto' => $asunto,
            'mensaje' => $datos['mensaje'],
            'remitente' => Auth::user()->nombreParaMostrar(),
            'respuesta_a' => (int) $original->id,
        ], JSON_UNESCAPED_UNICODE);
        $respuesta->tipo_mensaje = 1;
        $respuesta->fecha_envio = Carbon::now()->format('Y-m-d H:i:s');
        $respuesta->estado = 1;
        $respuesta->save();

        return response()->json(['estado' => 1, 'mensaje' => 'Respuesta enviada.']);
    }

    /** Arma los datos que muestra la vista, tolerando mensajes antiguos con campos faltantes. */
    private function formatear(Mensajes $mensaje): array
    {
        $datos = json_decode((string) $mensaje->datos_mensaje, true);
        $datos = is_array($datos) ? $datos : [];

        $remitente = User::find($mensaje->id_usuario);
        $fecha = $mensaje->fecha_envio ?: $mensaje->created_at;
        $tipos = [3 => 'Mensaje directo', 4 => 'Mensaje para todos los tutores', 5 => 'Aviso'];

        $archivos = collect($datos['archivos'] ?? [])
            ->filter(fn ($archivo) => is_array($archivo) && ! empty($archivo['url']))
            ->map(fn ($archivo) => ['nombre' => $archivo['nombre'] ?? 'Archivo adjunto', 'url' => asset(ltrim($archivo['url'], '/'))])
            ->values()
            ->all();

        return [
            'id' => $mensaje->id,
            'asunto' => trim((string) ($datos['asunto'] ?? $datos['titulo'] ?? '')) ?: 'Sin asunto',
            'mensaje' => (string) ($datos['mensaje'] ?? ''),
            // Los negocios de servicios firman con el nombre del negocio
            'remitente' => trim((string) ($datos['negocio'] ?? '')) ?: ($remitente ? $remitente->nombreParaMostrar() : 'VetSDI'),
            'puede_responder' => ! empty($datos['negocio']) && (bool) $mensaje->id_usuario,
            'tipo' => $tipos[(int) $mensaje->tipo_mensaje] ?? 'Mensaje',
            'fecha' => $fecha ? Carbon::parse($fecha)->format('d-m-Y H:i') : '',
            'hace' => $fecha ? Carbon::parse($fecha)->locale('es')->diffForHumans() : '',
            'leido' => (int) $mensaje->estado !== 1,
            'archivos' => $archivos,
        ];
    }
}
