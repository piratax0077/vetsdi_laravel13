<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('vet_controles_hospitalarios',function(Blueprint $t){
            $t->id();$t->foreignId('internacion_id')->constrained('vet_internaciones')->restrictOnDelete();
            $t->dateTime('fecha');$t->string('motivo',500);$t->string('estado',25)->default('programado');
            $t->text('resultado')->nullable();$t->unsignedBigInteger('id_usuario');$t->timestamps();
            $t->index(['internacion_id','fecha']);
        });
    }
    public function down(): void {Schema::dropIfExists('vet_controles_hospitalarios');}
};
