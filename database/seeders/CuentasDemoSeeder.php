<?php

namespace Database\Seeders;

use App\Models\Asistente;
use App\Models\Instituciones;
use App\Models\Mascota;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\User;
use App\Models\UsuarioRol;
use App\Services\CuentasService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cuenta de prueba con dos roles, para revisar la pantalla "Elige tu perfil"
 * y el guard de perfil incompleto sin tener que contratar un plan.
 *
 * Se puede correr las veces que haga falta: deja la cuenta siempre en el mismo
 * punto de partida, con los dos perfiles por completar.
 *
 * php artisan db:seed --class=CuentasDemoSeeder
 */
class CuentasDemoSeeder extends Seeder
{
    private const CORREO = 'demo.multirol@veterchile.cl';

    private const CLAVE = 'Demo1234';

    public function run(): void
    {
        $cuentas = app(CuentasService::class);

        $usuario = User::updateOrCreate(
            ['email' => self::CORREO],
            [
                'name' => 'Camila Soto Pérez',
                'nombres' => 'Camila',
                'apellido_uno' => 'Soto',
                'apellido_dos' => 'Pérez',
                'rut' => rut_normalizar('16.489.235-2'),
                'telefono' => '+56 9 6543 2109',
                'password' => Hash::make(self::CLAVE),
                'email_verified_at' => now(),
            ]
        );

        $this->limpiarPerfiles($usuario);

        $cuentas->asignarRol($usuario, 'tutor');
        $cuentas->asignarRol($usuario, 'profesional');

        $this->command?->info('Cuenta de prueba: '.self::CORREO.' / '.self::CLAVE);
        $this->command?->info('RUT: '.rut_formatear($usuario->rut).' — roles: tutor y profesional, ambos con el perfil por completar.');
        $this->command?->info('Para sumarle un tercer rol: php artisan cuenta:asignar-rol '.self::CORREO.' asistente');
    }

    /**
     * Borra lo que haya quedado de una corrida anterior para que el perfil
     * vuelva a pedirse. Ojo: mascotas e instituciones son MyISAM, así que esto
     * no se deshace solo.
     */
    private function limpiarPerfiles(User $usuario): void
    {
        $paciente = Paciente::where('id_usuario', $usuario->id)->first();

        if ($paciente) {
            Mascota::where('id_responsable', $paciente->id)->delete();
            $paciente->delete();
        }

        Mascota::where('id_user', $usuario->id)->delete();
        Profesional::where('id_usuario', $usuario->id)->delete();
        Asistente::where('id_usuario', $usuario->id)->delete();
        Instituciones::where('id_usuario', $usuario->id)->delete();
        UsuarioRol::where('id_usuario', $usuario->id)->delete();
    }
}
