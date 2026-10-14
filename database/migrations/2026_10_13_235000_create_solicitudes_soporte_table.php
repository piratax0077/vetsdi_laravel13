<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('solicitudes_soporte')) return;
        Schema::create('solicitudes_soporte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->index();
            $table->string('tipo_solicitud', 50);
            $table->string('asunto');
            $table->text('descripcion');
            $table->string('prioridad', 20)->default('media');
            $table->string('estado', 20)->default('abierta');
            $table->string('numero_ticket')->unique();
            $table->text('respuesta')->nullable();
            $table->timestamp('fecha_respuesta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Los reportes se conservan al revertir la aplicación.
    }
};
