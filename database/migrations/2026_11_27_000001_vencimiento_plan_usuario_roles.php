<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fecha en que vence el plan pagado de cada rol. Con ella la campana del encabezado
 * avisa unos días antes que el plan se debe renovar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('usuario_roles', 'fecha_vencimiento_plan')) {
            return;
        }

        Schema::table('usuario_roles', function (Blueprint $table) {
            $table->date('fecha_vencimiento_plan')->nullable()->after('id_plan');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuario_roles', 'fecha_vencimiento_plan')) {
            Schema::table('usuario_roles', function (Blueprint $table) {
                $table->dropColumn('fecha_vencimiento_plan');
            });
        }
    }
};
