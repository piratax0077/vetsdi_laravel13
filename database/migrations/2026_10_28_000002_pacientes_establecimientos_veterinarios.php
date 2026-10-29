<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vet_pacientes_establecimiento',function(Blueprint $t){
            $t->id(); $t->unsignedBigInteger('id_institucion'); $t->unsignedBigInteger('id_mascota');
            $t->unsignedBigInteger('id_usuario'); $t->timestamps();
            $t->unique(['id_institucion','id_mascota'],'vet_paciente_establecimiento_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_pacientes_establecimiento');
    }
};
