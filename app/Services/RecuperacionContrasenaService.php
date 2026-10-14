<?php

namespace App\Services;

use App\Http\Controllers\SendMailController;
use App\Models\User;
use App\Services\Mensajeria\MensajeriaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Recuperación de contraseña.
 *
 * Hay dos caminos y los dos terminan en la misma pantalla para escribir la
 * contraseña nueva: un enlace que llega al correo, o un código que llega al
 * celular registrado y que, al escribirlo bien, lleva directo a esa pantalla.
 * La contraseña anterior no se toca hasta que la persona guarda la nueva.
 */
class RecuperacionContrasenaService
{
    /** Minutos que dura el código enviado al celular. */
    public const MINUTOS_CODIGO = 10;

    /** Veces que se puede fallar un mismo código antes de tener que pedir otro. */
    public const INTENTOS_CODIGO = 5;

    /** Segundos de espera entre un envío y el siguiente al mismo destino. */
    public const SEGUNDOS_ENTRE_ENVIOS = 60;

    public function __construct(private readonly MensajeriaService $mensajeria)
    {
    }

    /**
     * Controla la espera entre envíos al mismo correo o celular. Se cuenta
     * exista o no la cuenta, para que la respuesta no delate cuáles existen.
     */
    public function debeEsperar(string $destino): bool
    {
        $clave = 'recuperar-contrasena:'.sha1($destino);

        if (RateLimiter::tooManyAttempts($clave, 1)) {
            return true;
        }

        RateLimiter::hit($clave, self::SEGUNDOS_ENTRE_ENVIOS);

        return false;
    }

    public function buscarPorCorreo(string $correo): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [correo_normalizar($correo)])->first();
    }

    /**
     * Busca la cuenta por su celular. Los teléfonos antiguos se guardaron con
     * formatos distintos, así que se comparan solo los dígitos. Si el número
     * está en más de una cuenta no se elige ninguna: no hay cómo saber de quién es.
     */
    public function buscarPorTelefono(string $telefono): ?User
    {
        $normalizado = telefono_normalizar($telefono);

        if ($normalizado === null) {
            return null;
        }

        // De +56 9 1234 5678 quedan los nueve dígitos nacionales.
        $nacional = substr(preg_replace('/\D/', '', $normalizado), 2);

        $soloDigitos = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefono, ' ', ''), '+', ''), '-', ''), '(', ''), ')', ''), '.', '')";

        $cuentas = User::whereRaw("{$soloDigitos} IN (?, ?, ?)", [$nacional, '56'.$nacional, '0'.$nacional])
            ->limit(2)
            ->get();

        return $cuentas->count() === 1 ? $cuentas->first() : null;
    }

    /** Dirección de la pantalla para escribir la contraseña nueva, con su token de un solo uso. */
    public function enlaceParaCambiar(User $usuario): string
    {
        return route('password.reset', [
            'token' => Password::broker()->createToken($usuario),
            'email' => $usuario->getEmailForPasswordReset(),
        ]);
    }

    /** Envía el enlace al correo de la cuenta. Devuelve false si el correo no pudo salir. */
    public function enviarEnlace(User $usuario): bool
    {
        $resultado = SendMailController::envioCorreo(
            'restablecer_contrasena',
            [['email' => $usuario->email, 'name' => $usuario->nombreParaMostrar()]],
            [],
            [],
            config('app.name').' - Cambia tu contraseña',
            [
                'nombre' => $usuario->nombreParaMostrar(),
                'enlace' => $this->enlaceParaCambiar($usuario),
                'minutos' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ],
            '',
            ''
        );

        if ((int) ($resultado['estado'] ?? 0) !== 1) {
            Log::warning('No se pudo enviar el correo para cambiar la contraseña', [
                'id_usuario' => $usuario->id,
                'detalle' => $resultado['msj'] ?? null,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Genera un código de seis dígitos y lo envía al celular de la cuenta.
     * Se guarda solo su huella, nunca el código tal cual.
     */
    public function enviarCodigo(User $usuario): bool
    {
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $vence = now()->addMinutes(self::MINUTOS_CODIGO);

        Cache::put($this->claveCodigo($usuario), [
            'huella' => $this->huella($codigo),
            'intentos' => 0,
            'vence' => $vence->timestamp,
        ], $vence);

        $resultado = $this->mensajeria->enviarWhatsapp(
            (string) $usuario->telefono,
            'Tu código de '.config('app.name').' para cambiar la contraseña es '.$codigo
                .'. Vence en '.self::MINUTOS_CODIGO.' minutos. Si no lo pediste, ignora este mensaje.',
            ['motivo' => 'recuperar_contrasena', 'id_usuario' => $usuario->id]
        );

        if ((int) ($resultado['estado'] ?? 0) !== 1) {
            Cache::forget($this->claveCodigo($usuario));

            Log::warning('No se pudo enviar el código para cambiar la contraseña', [
                'id_usuario' => $usuario->id,
                'detalle' => $resultado['msj'] ?? null,
            ]);

            return false;
        }

        return true;
    }

    /** Revisa el código. Sirve una sola vez y se anula al agotar los intentos. */
    public function codigoCorrecto(User $usuario, string $codigo): bool
    {
        $clave = $this->claveCodigo($usuario);
        $guardado = Cache::get($clave);

        if (! is_array($guardado) || $guardado['vence'] < now()->timestamp) {
            return false;
        }

        if (hash_equals($guardado['huella'], $this->huella($codigo))) {
            Cache::forget($clave);

            return true;
        }

        $guardado['intentos']++;

        if ($guardado['intentos'] >= self::INTENTOS_CODIGO) {
            Cache::forget($clave);
        } else {
            Cache::put($clave, $guardado, now()->setTimestamp($guardado['vence']));
        }

        return false;
    }

    private function claveCodigo(User $usuario): string
    {
        return 'recuperar-contrasena:codigo:'.$usuario->id;
    }

    private function huella(string $codigo): string
    {
        return hash_hmac('sha256', $codigo, (string) config('app.key'));
    }
}
