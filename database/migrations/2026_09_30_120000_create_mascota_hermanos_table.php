<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mascota_hermanos')) {
            return;
        }

        // Hermanos agregados a mano en el árbol: registrados (hermano_id) o externos (nombre)
        Schema::create('mascota_hermanos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mascota_id')->index();
            $table->unsignedBigInteger('hermano_id')->nullable()->index();
            $table->string('nombre')->nullable();
            $table->string('especie', 20)->nullable();
            $table->char('sexo', 1)->nullable();
            $table->string('foto')->nullable();
            $table->string('tipo', 20)->default('completo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascota_hermanos');
    }
};
