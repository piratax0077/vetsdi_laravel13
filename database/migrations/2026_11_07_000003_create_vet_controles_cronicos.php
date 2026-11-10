<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vet_controles_cronicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mascota');
            $table->unsignedBigInteger('id_ficha_atencion');
            $table->string('condicion', 40);
            $table->string('otra', 120)->nullable();
            $table->json('mediciones');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['id_mascota', 'condicion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_controles_cronicos');
    }
};
