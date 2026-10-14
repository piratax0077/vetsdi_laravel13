<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roles de cuenta de una persona. Complementa a spatie: aqui vive el estado
 * propio de cada rol (si el perfil esta completo, el plan contratado, etc.),
 * que el pivote de spatie no puede guardar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->enum('tipo', ['tutor', 'profesional', 'asistente', 'clinica']);
            $table->boolean('estado')->default(true);
            $table->boolean('perfil_completo')->default(false);
            $table->unsignedBigInteger('id_plan')->nullable();
            $table->timestamp('fecha_activacion')->nullable();
            $table->timestamp('fecha_perfil_completo')->nullable();
            $table->timestamps();

            $table->unique(['id_usuario', 'tipo'], 'usuario_roles_usuario_tipo_unique');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_roles');
    }
};
