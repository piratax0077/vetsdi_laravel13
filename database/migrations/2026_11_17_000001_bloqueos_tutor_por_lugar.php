<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tutor_bloqueos', function (Blueprint $tabla) {
            $tabla->unsignedBigInteger('id_lugar_atencion')->default(0);
            $tabla->unique(['id_paciente', 'id_profesional', 'id_lugar_atencion'], 'tutor_bloqueos_por_lugar');
            $tabla->dropUnique('tutor_bloqueos_paciente_profesional');
        });
    }

    public function down(): void
    {
        if (DB::table('tutor_bloqueos')->where('id_lugar_atencion', '>', 0)->exists()) {
            throw new RuntimeException('Existen bloqueos por lugar de atención.');
        }
        Schema::table('tutor_bloqueos', function (Blueprint $tabla) {
            $tabla->unique(['id_paciente', 'id_profesional'], 'tutor_bloqueos_paciente_profesional');
            $tabla->dropUnique('tutor_bloqueos_por_lugar');
            $tabla->dropColumn('id_lugar_atencion');
        });
    }
};
