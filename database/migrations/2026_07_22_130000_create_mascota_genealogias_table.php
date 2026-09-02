<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mascota_genealogias')) {
            return;
        }

        Schema::create('mascota_genealogias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mascota_id')->unique();
            $table->unsignedBigInteger('padre_id')->nullable()->index();
            $table->unsignedBigInteger('madre_id')->nullable()->index();
            $table->unsignedBigInteger('abuelo_paterno_id')->nullable()->index();
            $table->unsignedBigInteger('abuela_paterna_id')->nullable()->index();
            $table->unsignedBigInteger('abuelo_materno_id')->nullable()->index();
            $table->unsignedBigInteger('abuela_materna_id')->nullable()->index();
            $table->string('padre_nombre')->nullable();
            $table->string('madre_nombre')->nullable();
            $table->string('abuelo_paterno_nombre')->nullable();
            $table->string('abuela_paterna_nombre')->nullable();
            $table->string('abuelo_materno_nombre')->nullable();
            $table->string('abuela_materna_nombre')->nullable();
            $table->string('numero_registro')->nullable();
            $table->string('criador')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascota_genealogias');
    }
};
