<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mascotas_tutorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mascota_id');
            $table->unsignedBigInteger('titular_id');
            $table->unsignedBigInteger('usuario_id')->nullable()->index();
            $table->string('correo', 254);
            $table->string('nombre')->nullable();
            $table->string('rol', 16);
            $table->string('estado', 16);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('vence_el');
            $table->timestamps();
            $table->unique(['mascota_id', 'titular_id', 'correo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascotas_tutorias');
    }
};
