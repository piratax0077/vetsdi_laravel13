<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens de confirmacion de correo. Se guardan en tabla propia (y no como enlace
 * firmado) para poder controlar el reenvio y anular los tokens anteriores.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verificaciones_correo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->timestamp('expira_en');
            $table->timestamp('verificado_en')->nullable();
            $table->timestamp('enviado_en')->nullable();
            $table->timestamps();

            $table->index(['id_usuario', 'verificado_en'], 'verificaciones_correo_usuario_index');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verificaciones_correo');
    }
};
