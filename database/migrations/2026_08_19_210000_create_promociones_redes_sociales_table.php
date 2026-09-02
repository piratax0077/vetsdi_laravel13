<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('promociones_redes_sociales')) {
            return;
        }

        Schema::create('promociones_redes_sociales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users');
            $table->foreignId('id_sitio_web')->constrained('sitios_web')->cascadeOnDelete();
            $table->string('plan', 40);
            $table->string('nombre_plan', 100);
            $table->json('redes');
            $table->unsignedSmallInteger('duracion_dias');
            $table->unsignedInteger('monto');
            $table->string('objetivo', 40);
            $table->string('estado', 40)->default('pendiente_pago');
            $table->string('metodo_pago', 40)->nullable();
            $table->string('referencia_pago', 100)->nullable();
            $table->string('comprobante')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_termino')->nullable();
            $table->timestamps();
            $table->index(['id_usuario', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promociones_redes_sociales');
    }
};
