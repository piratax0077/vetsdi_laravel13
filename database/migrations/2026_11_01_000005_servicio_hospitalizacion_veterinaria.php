<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vet_equipo_hospitalizacion',function(Blueprint $t){
            $t->id(); $t->unsignedBigInteger('id_institucion'); $t->unsignedBigInteger('id_usuario');
            $t->string('perfil',20); $t->boolean('activo')->default(true); $t->timestamps();
            $t->unique(['id_institucion','id_usuario']);
        });
        Schema::table('vet_evoluciones',function(Blueprint $t){
            $t->unsignedSmallInteger('frecuencia_cardiaca')->nullable();
            $t->unsignedSmallInteger('frecuencia_respiratoria')->nullable();
            $t->unsignedTinyInteger('dolor')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('vet_evoluciones',fn(Blueprint $t)=>$t->dropColumn(['frecuencia_cardiaca','frecuencia_respiratoria','dolor']));
        Schema::dropIfExists('vet_equipo_hospitalizacion');
    }
};
