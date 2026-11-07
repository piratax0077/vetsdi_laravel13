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
            'remitente' => $remitente ? $remitente->nombreParaMostrar() : 'VetSDI',
            'tipo' => $tipos[(int) $mensaje->tipo_mensaje] ?? 'Mensaje',
            'fecha' => $fecha ? Carbon::parse($fecha)->format('d-m-Y H:i') : '',
            'hace' => $fecha ? Carbon::parse($fecha)->locale('es')->diffForHumans() : '',
            'leido' => (int) $mensaje->estado !== 1,
            'archivos' => $archivos,
        ];
    }
}
