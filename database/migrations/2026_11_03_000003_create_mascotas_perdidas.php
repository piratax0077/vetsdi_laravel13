<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('mascotas', 'perdida')) {
            Schema::table('mascotas', fn (Blueprint $t) => $t->boolean('perdida')->default(false));
        }
        Schema::create('vet_perdidas_avisos', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('tutor_id')->index();
            $t->unsignedBigInteger('mascota_id')->nullable()->index();
            $t->uuid('token')->unique(); $t->string('estado', 15);
            $t->string('foto_perfil', 1000)->nullable();
            $t->string('nombre', 100); $t->string('especie', 100); $t->string('raza', 100)->nullable();
            $t->unsignedBigInteger('region_id'); $t->unsignedBigInteger('comuna_id')->index();
            $t->string('direccion', 500); $t->decimal('latitud', 10, 7); $t->decimal('longitud', 10, 7);
            $t->date('fecha'); $t->string('hora', 5)->nullable(); $t->text('descripcion');
            $t->unsignedInteger('recompensa_monto')->nullable(); $t->timestamp('cerrado_en')->nullable()->index();
            $t->timestamps();
        });
        Schema::create('vet_perdidas_respuestas', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('aviso_id')->index(); $t->unsignedBigInteger('tutor_id')->index();
            $t->uuid('token')->unique(); $t->string('tipo', 15); $t->unsignedBigInteger('mascota_id')->nullable();
            $t->string('lugar', 500)->nullable(); $t->decimal('latitud', 10, 7)->nullable(); $t->decimal('longitud', 10, 7)->nullable();
            $t->date('fecha')->nullable(); $t->string('hora', 5)->nullable(); $t->text('mensaje'); $t->string('sena', 500)->nullable();
            $t->boolean('compartir_telefono')->default(false); $t->timestamp('contacto_compartido_en')->nullable(); $t->timestamps();
        });
        Schema::create('vet_perdidas_notificaciones', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('tutor_id')->index(); $t->unsignedBigInteger('aviso_id')->index();
            $t->unsignedBigInteger('respuesta_id')->nullable(); $t->string('tipo', 30); $t->timestamp('leida_en')->nullable(); $t->timestamps();
        });
        Schema::create('vet_perdidas_fotos', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('tutor_id')->index(); $t->string('ruta');
            $t->unsignedBigInteger('aviso_id')->nullable()->index(); $t->unsignedBigInteger('respuesta_id')->nullable()->index(); $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['vet_perdidas_fotos', 'vet_perdidas_notificaciones', 'vet_perdidas_respuestas', 'vet_perdidas_avisos'] as $tabla) Schema::dropIfExists($tabla);
        // El indicador puede existir desde versiones anteriores; conservarlo y no perder datos.
    }
};
