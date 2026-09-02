<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create('sitio_web_articulos',function(Blueprint $table){$table->id();$table->foreignId('sitio_web_id')->constrained('sitios_web')->cascadeOnDelete();$table->string('titulo',180);$table->string('slug',190)->unique();$table->string('resumen',300)->nullable();$table->longText('contenido');$table->string('imagen')->nullable();$table->boolean('publicado')->default(false);$table->timestamp('publicado_at')->nullable();$table->timestamps();});}public function down():void{Schema::dropIfExists('sitio_web_articulos');}};
