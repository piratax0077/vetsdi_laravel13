<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('odontogramas_mascotas')) {
            Schema::create('odontogramas_mascotas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mascota_id')->index();
                $table->unsignedBigInteger('ficha_atencion_id')->nullable()->index();
                $table->unsignedBigInteger('profesional_id')->index();
                $table->unsignedBigInteger('lugar_atencion_id')->nullable();
                $table->string('especie', 20);
                $table->string('pieza', 4);
                $table->string('hallazgo', 100);
                $table->string('tratamiento', 150)->nullable();
                $table->json('caras')->nullable();
                $table->text('observaciones')->nullable();
                $table->boolean('estado')->default(true);
                $table->timestamps();
                $table->index(['mascota_id', 'pieza', 'estado'], 'odonto_mascota_pieza_estado');
            });
        }

        if (!Schema::hasTable('periodontogramas_mascotas')) {
            Schema::create('periodontogramas_mascotas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mascota_id')->index();
                $table->unsignedBigInteger('ficha_atencion_id')->nullable()->index();
                $table->unsignedBigInteger('profesional_id')->index();
                $table->string('especie', 20);
                $table->string('pieza', 4);
                $table->unsignedTinyInteger('profundidad')->default(0);
                $table->unsignedTinyInteger('recesion')->default(0);
                $table->boolean('sangrado')->default(false);
                $table->boolean('placa')->default(false);
                $table->unsignedTinyInteger('movilidad')->default(0);
                $table->unsignedTinyInteger('furcacion')->default(0);
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->unique(['mascota_id', 'ficha_atencion_id', 'pieza'], 'periodonto_mascota_ficha_pieza');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('periodontogramas_mascotas');
        Schema::dropIfExists('odontogramas_mascotas');
    }
};
