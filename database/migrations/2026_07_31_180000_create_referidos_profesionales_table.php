<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referidos_profesionales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_referente');
            $table->unsignedBigInteger('id_profesional_referente')->nullable();
            $table->enum('tipo', ['profesional', 'centro'])->default('profesional');
            $table->string('nombre_invitado', 150);
            $table->string('email_invitado', 190);
            $table->string('nombre_centro', 190)->nullable();
            $table->string('token', 64)->unique();
            $table->string('codigo', 20)->unique();
            $table->enum('estado', ['enviada', 'visitada', 'registrada', 'activada', 'bonificada', 'cancelada'])->default('enviada');
            $table->unsignedBigInteger('id_usuario_invitado')->nullable();
            $table->unsignedInteger('puntos_propuestos')->default(0);
            $table->unsignedInteger('puntos_otorgados')->default(0);
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_visita')->nullable();
            $table->timestamp('fecha_registro')->nullable();
            $table->timestamp('fecha_activacion')->nullable();
            $table->timestamp('fecha_bonificacion')->nullable();
            $table->timestamps();

            $table->index(['id_usuario_referente', 'estado']);
            $table->unique(['id_usuario_referente', 'email_invitado'], 'ref_usuario_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referidos_profesionales');
    }
};
