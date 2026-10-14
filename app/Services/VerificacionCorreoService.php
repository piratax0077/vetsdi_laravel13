<?php

namespace App\Services;

use App\Http\Controllers\SendMailController;
use App\Models\User;
use App\Models\VerificacionCorreo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Confirmación del correo de la cuenta.
 *
 * La verificación se hace una sola vez por persona: los roles que se agreguen
 * después no la repiten. Si alguien cambia su correo, el nuevo vuelve a pasar
 * por aquí antes de reemplazar al anterior.
 */
class VerificacionCorreoService
{
    /** Horas que dura el enlace antes de vencer. */
    public const HORAS_VIGENCIA = 24;

    /** Minutos de espera entre un envío y el siguiente. */
    public const MINUTOS_ENTRE_ENVIOS = 2;

    /**
     * Genera un token nuevo y envía el correo. Devuelve false si el correo no
     * pudo salir, para que la pantalla lo diga en vez de quedarse en silencio.
     */
    public function enviar(User $usuario, ?string $correo = null): bool
    {
        $correo = correo_normalizar($correo ?? $usuario->email);

        // Un token a la vez: los anteriores quedan vencidos.
        VerificacionCorreo::where('id_usuario', $usuario->id)
            ->whereNull('verificado_en')
            ->update(['expira_en' => now()->subSecond()]);

        $verificacion = VerificacionCorreo::create([
            'id_usuario' => $usuario->id,
            'email' => $correo,
            'token' => Str::random(64),
            'expira_en' => now()->addHours(self::HORAS_VIGENCIA),
            'enviado_en' => now(),
        ]);

        $resultado = SendMailController::envioCorreo(
            'verificacion_cuenta',
            [['email' => $correo, 'name' => $usuario->nombreParaMostrar()]],
            [],
            [],
            config('app.name').' - Confirma tu cuenta',
            [
                'nombre' => $usuario->nombreParaMostrar(),
                'enlace' => route('registro.verificar', ['token' => $verificacion->token]),
                'horas' => self::HORAS_VIGENCIA,
            ],
            '',
            ''
        );

        if ((int) ($resultado['estado'] ?? 0) !== 1) {
            Log::warning('No se pudo enviar el correo de verificación', [
                'id_usuario' => $usuario->id,
                'detalle' => $resultado['msj'] ?? null,
            ]);

            return false;
        }

        return true;
    }

    /** Indica si todavía hay que esperar antes de reenviar. */
    public function puedeReenviar(User $usuario): bool
    {
        $ultimo = VerificacionCorreo::where('id_usuario', $usuario->id)
            ->orderByDesc('id')
            ->first();

        if (! $ultimo || $ultimo->enviado_en === null) {
            return true;
        }

        return $ultimo->enviado_en->addMinutes(self::MINUTOS_ENTRE_ENVIOS)->isPast();
    }

    public function segundosParaReenviar(User $usuario): int
    {
        $ultimo = VerificacionCorreo::where('id_usuario', $usuario->id)
            ->orderByDesc('id')
            ->first();

        if (! $ultimo || $ultimo->enviado_en === null) {
            return 0;
        }

        $disponibleEn = $ultimo->enviado_en->addMinutes(self::MINUTOS_ENTRE_ENVIOS);

        return $disponibleEn->isFuture() ? now()->diffInSeconds($disponibleEn) : 0;
    }

    /**
     * Confirma el correo a partir del token del enlace.
     * Devuelve el usuario cuando el token era válido, o null si venció o no existe.
     */
    public function confirmar(string $token): ?User
    {
        $verificacion = VerificacionCorreo::where('token', $token)->first();

        if (! $verificacion || ! $verificacion->estaVigente()) {
            return null;
        }

        $usuario = $verificacion->Usuario;

        if (! $usuario) {
            return null;
        }

        // El correo confirmado pasa a ser el correo de la cuenta: así funciona
        // también el cambio de correo, que valida el nuevo antes de reemplazar.
        $usuario->email = $verificacion->email;
        $usuario->email_verified_at = now();
        $usuario->save();

        $verificacion->verificado_en = now();
        $verificacion->save();

        return $usuario;
    }
}
