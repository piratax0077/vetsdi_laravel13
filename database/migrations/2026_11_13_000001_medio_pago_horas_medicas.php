<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Forma de pago que elige el tutor al reservar: 'tarjeta' o 'en_consulta'
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('horas_medicas', 'medio_pago')) {
            Schema::table('horas_medicas', function (Blueprint $t) {
                $t->string('medio_pago', 30)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('horas_medicas', 'medio_pago')) {
            Schema::table('horas_medicas', fn (Blueprint $t) => $t->dropColumn('medio_pago'));
        }
    }
};
