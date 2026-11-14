<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Lugar de atención donde se dejó la nota: la ven todos los profesionales de ese lugar
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tutor_notas_internas') && !Schema::hasColumn('tutor_notas_internas', 'id_lugar_atencion')) {
            Schema::table('tutor_notas_internas', function (Blueprint $table) {
                $table->unsignedBigInteger('id_lugar_atencion')->nullable()->after('id_profesional');
                $table->index(['id_paciente', 'id_lugar_atencion'], 'tutor_notas_paciente_lugar');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tutor_notas_internas', 'id_lugar_atencion')) {
            Schema::table('tutor_notas_internas', function (Blueprint $table) {
                $table->dropIndex('tutor_notas_paciente_lugar');
                $table->dropColumn('id_lugar_atencion');
            });
        }
    }
};
