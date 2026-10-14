<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El nombre tambien es parte de la identidad: se pide una vez al registrarse y
 * desde ahi se copia a la tabla de perfil de cada rol. Se guardan por separado
 * con los mismos nombres de columna que usan pacientes y profesionales.
 *
 * users.name se mantiene con el nombre completo porque Jetstream y varias
 * vistas antiguas lo siguen usando.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['nombres', 'apellido_uno', 'apellido_dos'] as $columna) {
                if (! Schema::hasColumn('users', $columna)) {
                    $table->string($columna)->nullable()->after('name');
                }
            }
        });

        // Las cuentas antiguas heredan lo que ya tenian en su perfil de paciente.
        DB::statement("
            UPDATE users
            INNER JOIN pacientes ON pacientes.id_usuario = users.id
            SET users.nombres = pacientes.nombres,
                users.apellido_uno = pacientes.apellido_uno,
                users.apellido_dos = pacientes.apellido_dos
            WHERE users.nombres IS NULL
              AND pacientes.nombres IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['nombres', 'apellido_uno', 'apellido_dos'] as $columna) {
                if (Schema::hasColumn('users', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
