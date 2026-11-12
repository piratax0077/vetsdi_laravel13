<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vet_plantillas_documentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('profesional_id')->index();
            $table->string('seccion', 20);
            $table->string('titulo', 150);
            $table->string('categoria', 100);
            $table->string('tipo', 10);
            $table->string('alcance', 12);
            $table->boolean('activo')->default(true);
            $table->longText('contenido')->nullable();
            $table->longText('logo')->nullable();
            $table->string('archivo')->nullable();
            $table->string('ruta')->nullable();
            $table->string('mime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_plantillas_documentos');
    }
};
