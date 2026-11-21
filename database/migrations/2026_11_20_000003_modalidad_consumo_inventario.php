<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vet_inventario_lotes', function (Blueprint $table) {
            $table->string('modalidad_consumo', 20)->default('fraccionable');
        });
    }

    public function down(): void
    {
        Schema::table('vet_inventario_lotes', fn (Blueprint $table) => $table->dropColumn('modalidad_consumo'));
    }
};
