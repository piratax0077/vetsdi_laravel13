<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('vet_documentos_pdf',function(Blueprint $t){
        $t->uuid('id')->primary(); $t->unsignedBigInteger('id_mascota')->nullable()->index();
        $t->unsignedBigInteger('id_institucion')->nullable()->index(); $t->unsignedBigInteger('id_ficha_atencion')->nullable()->index();
        $t->unsignedBigInteger('id_usuario'); $t->string('nombre',200); $t->string('origen',180);
        $t->string('ruta'); $t->char('huella',64)->unique(); $t->timestamps();
    }); }
    public function down(): void { Schema::dropIfExists('vet_documentos_pdf'); }
};
