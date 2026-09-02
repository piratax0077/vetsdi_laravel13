<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('denuncias_ram')) {
            return;
        }

        Schema::create('denuncias_ram', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_medicamento');
            $table->string('principio_activo')->nullable();
            $table->string('laboratorio_fabricante')->nullable();
            $table->unsignedBigInteger('id_paciente')->nullable()->index();
            $table->unsignedBigInteger('id_profesional')->nullable()->index();
            $table->unsignedBigInteger('id_usuario')->nullable()->index();
            $table->string('tipo_reaccion')->nullable();
            $table->enum('gravedad', ['leve', 'moderada', 'grave', 'mortal'])->default('leve');
            $table->date('fecha_reaccion')->nullable();
            $table->text('descripcion_reaccion');
            $table->text('observaciones')->nullable();
            $table->string('accion_tomada')->nullable();
            $table->enum('estado', ['pendiente', 'en_revision', 'cerrado'])->default('pendiente');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denuncias_ram');
    }
};
