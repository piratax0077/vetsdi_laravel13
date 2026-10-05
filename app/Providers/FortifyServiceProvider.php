<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\ContrasenaCambiadaResponse;
use App\Http\Responses\ContrasenaNoCambiadaResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
// PRODUCCIÓN 2FA: descomentar junto con la característica en config/fortify.php.
// use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // El ingreso acepta RUT o correo, y exige el correo ya confirmado.
        Fortify::authenticateUsing(function (Request $request) {
            $usuario = $this->buscarUsuario((string) $request->input(Fortify::username()));

            if (! $usuario || ! Hash::check((string) $request->input('password'), $usuario->password)) {
                return null;
            }

            if (! $usuario->correoVerificado()) {
                $request->session()->put('correo_por_verificar', $usuario->email);

                throw ValidationException::withMessages([
                    Fortify::username() => 'Confirma tu correo para poder ingresar. Te podemos reenviar el mensaje.',
                ]);
            }

            return $usuario;
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Al cambiar la contraseña (o si el enlace ya no sirve) se vuelve a la tarjeta de Veterchile.
        $this->app->singleton(PasswordResetResponse::class, ContrasenaCambiadaResponse::class);
        $this->app->singleton(FailedPasswordResetResponse::class, ContrasenaNoCambiadaResponse::class);
        // PRODUCCIÓN 2FA: descomentar al habilitar la autenticación por APP.
        // Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        // PRODUCCIÓN 2FA: descomentar junto con la característica de Fortify.
        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });
    }

    /**
     * Busca la cuenta por RUT o por correo. El RUT se normaliza igual que al
     * registrarse, así da lo mismo si lo escriben con puntos o sin ellos.
     */
    private function buscarUsuario(string $identificador): ?User
    {
        $identificador = trim($identificador);

        if ($identificador === '') {
            return null;
        }

        $rut = rut_normalizar($identificador);

        if ($rut !== null) {
            $usuario = User::where('rut', $rut)->first();

            if ($usuario) {
                return $usuario;
            }
        }

        return User::whereRaw('LOWER(email) = ?', [correo_normalizar($identificador)])->first();
    }
}
