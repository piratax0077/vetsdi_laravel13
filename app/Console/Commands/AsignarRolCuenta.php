<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CuentasService;
use Illuminate\Console\Command;

/**
 * Agrega un rol a una cuenta que ya existe, igual que lo hará la contratación
 * de un plan. Sirve para probar el caso de una persona con varios roles.
 *
 * Ejemplo: php artisan cuenta:asignar-rol 12.345.678-5 profesional
 */
class AsignarRolCuenta extends Command
{
    protected $signature = 'cuenta:asignar-rol
                            {identificador : RUT o correo de la persona}
                            {tipo : tutor, profesional, asistente o clinica}
                            {--plan= : Id del plan contratado, si corresponde}';

    protected $description = 'Agrega un rol a una cuenta existente sin crear otra identidad';

    public function handle(CuentasService $cuentas): int
    {
        $identificador = (string) $this->argument('identificador');
        $tipo = strtolower((string) $this->argument('tipo'));

        if (! CuentasService::esTipoValido($tipo)) {
            $this->error('Tipo no válido. Use: '.implode(', ', CuentasService::TIPOS));

            return self::FAILURE;
        }

        $usuario = $this->buscarUsuario($identificador);

        if (! $usuario) {
            $this->error('No existe una cuenta con "'.$identificador.'".');

            return self::FAILURE;
        }

        $plan = $this->option('plan');
        $rol = $cuentas->asignarRol($usuario, $tipo, $plan !== null ? (int) $plan : null);

        $this->info(sprintf(
            'Rol "%s" asignado a %s (id %d). Perfil completo: %s.',
            $cuentas->etiqueta($tipo),
            $usuario->email,
            $usuario->id,
            $rol->perfil_completo ? 'sí' : 'no'
        ));

        $this->line('Roles activos: '.$cuentas->rolesActivos($usuario)->pluck('tipo')->implode(', '));

        return self::SUCCESS;
    }

    private function buscarUsuario(string $identificador): ?User
    {
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
