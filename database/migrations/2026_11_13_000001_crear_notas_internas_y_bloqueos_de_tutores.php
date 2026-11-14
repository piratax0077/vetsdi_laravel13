<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Notas que el profesional deja sobre un tutor (el tutor no las ve) y los tutores que bloqueó.
// Con tres notas conflictivas la vista del tutor propone bloquearlo; bloqueado, no puede tomar hora con ese profesional.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tutor_notas_internas')) {
            Schema::create('tutor_notas_internas', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('id_paciente');
                $table->unsignedBigInteger('id_profesional');
                $table->text('nota');
                $table->boolean('conflictivo')->default(false);
                $table->timestamps();

                $table->index(['id_paciente', 'id_profesional'], 'tutor_notas_paciente_profesional');
            });
        }

        if (!Schema::hasTable('tutor_bloqueos')) {
            Schema::create('tutor_bloqueos', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->unsignedBigInteger('id_paciente');
                $table->unsignedBigInteger('id_profesional');
                $table->string('motivo', 255);
                $table->timestamps();

                $table->unique(['id_paciente', 'id_profesional'], 'tutor_bloqueos_paciente_profesional');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_bloqueos');
        Schema::dropIfExists('tutor_notas_internas');
    }
};
