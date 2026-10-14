<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Crea la fila de usuario_roles para las cuentas que ya existian, a partir de los
 * roles de spatie que tienen hoy. Se marcan con el perfil completo para que el
 * guard no las devuelva a llenar formularios que ya habian llenado antes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $equivalencias = [
            'Paciente' => ['tipo' => 'tutor', 'tabla' => 'pacientes'],
            'Profesional' => ['tipo' => 'profesional', 'tabla' => 'profesionales'],
            'Asistente' => ['tipo' => 'asistente', 'tabla' => 'asistentes'],
            'Adm_Institucion' => ['tipo' => 'clinica', 'tabla' => 'instituciones'],
            'Institucion' => ['tipo' => 'clinica', 'tabla' => 'instituciones'],
        ];

        $ahora = now();

        foreach ($equivalencias as $nombreRol => $datos) {
            $rol = DB::table('roles')->where('name', $nombreRol)->first();
            if (! $rol) {
                continue;
            }

            $usuarios = DB::table('model_has_roles')
                ->where('role_id', $rol->id)
                ->where('model_type', 'App\Models\User')
                ->pluck('model_id');

            $conPerfil = DB::table($datos['tabla'])
                ->whereNotNull('id_usuario')
                ->pluck('id_usuario')
                ->flip();

            foreach ($usuarios->chunk(500) as $grupo) {
                $filas = [];

                foreach ($grupo as $idUsuario) {
                    $filas[] = [
                        'id_usuario' => $idUsuario,
                        'tipo' => $datos['tipo'],
                        'estado' => 1,
                        'perfil_completo' => $conPerfil->has($idUsuario) ? 1 : 0,
                        'fecha_activacion' => $ahora,
                        'fecha_perfil_completo' => $conPerfil->has($idUsuario) ? $ahora : null,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }

                // insertOrIgnore: Institucion y Adm_Institucion caen en el mismo tipo.
                DB::table('usuario_roles')->insertOrIgnore($filas);
            }
        }
    }

    public function down(): void
    {
        DB::table('usuario_roles')->truncate();
    }
};
