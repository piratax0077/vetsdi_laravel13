<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ficha_atencion_app', function (Blueprint $table) {
            $table->unsignedBigInteger('id_mascota')->nullable()->after('id_paciente')->index();
        });
    }

    public function down(): void
    {
        Schema::table('ficha_atencion_app', function (Blueprint $table) {
            $table->dropColumn('id_mascota');
        });
    }
};
