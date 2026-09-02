<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('presupuestos_vet', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tarifa_veterinaria')->nullable()->after('id_tratamiento')->index();
        });
    }

    public function down(): void
    {
        Schema::table('presupuestos_vet', function (Blueprint $table) {
            $table->dropColumn('id_tarifa_veterinaria');
        });
    }
};
