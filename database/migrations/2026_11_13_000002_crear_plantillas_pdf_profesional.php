<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Cómo quiere cada profesional que se vean sus PDF de receta y de orden de exámenes:
// logo, color, título, formato del listado y qué datos se muestran
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plantillas_pdf_profesional')) {
            return;
        }

        Schema::create('plantillas_pdf_profesional', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedBigInteger('id_profesional');
            $table->string('tipo', 20); // receta | examenes
            $table->json('configuracion')->nullable();
            $table->string('logo_ruta', 255)->nullable();
            $table->timestamps();

            $table->unique(['id_profesional', 'tipo'], 'plantillas_pdf_profesional_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantillas_pdf_profesional');
    }
};
