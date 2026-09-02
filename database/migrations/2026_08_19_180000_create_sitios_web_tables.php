<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sitios_web')) {
            Schema::create('sitios_web', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('users');
            $table->enum('tipo', ['profesional', 'clinica'])->default('profesional');
            $table->string('slug', 80)->unique();
            $table->string('titulo', 160);
            $table->string('slogan', 180)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('whatsapp', 40)->nullable();
            $table->string('instagram', 180)->nullable();
            $table->string('facebook', 180)->nullable();
            $table->string('tiktok', 180)->nullable();
            $table->string('youtube', 180)->nullable();
            $table->string('linkedin', 180)->nullable();
            $table->string('web', 180)->nullable();
            $table->boolean('publicado')->default(false);
            $table->timestamps();
            });
        }

        if (!Schema::hasTable('sitio_web_lugares')) {
            Schema::create('sitio_web_lugares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sitio_web')->constrained('sitios_web')->cascadeOnDelete();
            $table->foreignId('id_lugar_atencion')->constrained('lugares_atencion');
            $table->timestamps();
            $table->unique(['id_sitio_web', 'id_lugar_atencion']);
            });
        }
    }

    public function down(): void
    {
        // Estas tablas pueden ser compartidas con versiones anteriores de Vet SDI.
        // No se eliminan en rollback para proteger sitios ya publicados.
    }
};
