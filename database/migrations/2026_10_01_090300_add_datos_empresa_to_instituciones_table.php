<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de empresa que pide el perfil de la clinica veterinaria y que la tabla
 * de instituciones todavia no tenia. Es MyISAM, asi que no lleva llaves foraneas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            if (! Schema::hasColumn('instituciones', 'nombre_fantasia')) {
                $table->string('nombre_fantasia')->nullable()->after('razon_social');
            }
            if (! Schema::hasColumn('instituciones', 'giro')) {
                $table->string('giro')->nullable()->after('nombre_fantasia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            foreach (['nombre_fantasia', 'giro'] as $columna) {
                if (Schema::hasColumn('instituciones', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};
