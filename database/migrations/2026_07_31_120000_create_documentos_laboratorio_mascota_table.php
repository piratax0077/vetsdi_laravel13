<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_laboratorio_mascota', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mascota')->index();
            $table->unsignedBigInteger('id_responsable')->nullable()->index();
            $table->unsignedBigInteger('id_usuario_laboratorio')->nullable()->index();
            $table->unsignedBigInteger('id_profesional_revisor')->nullable()->index();
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('mime', 100)->default('application/pdf');
            $table->unsignedBigInteger('tamano')->default(0);
            $table->text('observacion')->nullable();
            $table->string('tipo_examen', 30)->nullable()->index();
            $table->string('estado', 20)->default('pendiente')->index();
            $table->timestamp('revisado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_laboratorio_mascota');
    }
};
