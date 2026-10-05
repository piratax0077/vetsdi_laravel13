<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El RUT y el telefono pasan a vivir en la identidad (users), no en cada perfil.
 * Asi una misma persona usa un solo RUT y un solo correo aunque tenga varios roles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'rut')) {
                $table->string('rut', 12)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono', 20)->nullable()->after('rut');
            }
        });

        // Indice unico aparte: MySQL permite varios NULL, asi conviven las cuentas antiguas sin RUT.
        if (! $this->existeIndice('users', 'users_rut_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('rut', 'users_rut_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->existeIndice('users', 'users_rut_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_rut_unique');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['rut', 'telefono'] as $columna) {
                if (Schema::hasColumn('users', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }

    private function existeIndice(string $tabla, string $indice): bool
    {
        return collect(Schema::getIndexes($tabla))
            ->contains(fn ($datos) => ($datos['name'] ?? null) === $indice);
    }
};
