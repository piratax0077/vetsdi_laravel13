<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odontogramas_pacientes', function (Blueprint $table) {
            $table->unsignedTinyInteger('grado_avance')->default(0)->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('odontogramas_pacientes', function (Blueprint $table) {
            $table->dropColumn('grado_avance');
        });
    }
};
