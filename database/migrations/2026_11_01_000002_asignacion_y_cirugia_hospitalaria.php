<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('vet_evoluciones',function(Blueprint $t){$t->string('procedimiento',150)->nullable();});
        Schema::create('vet_internacion_profesionales',function(Blueprint $t){
            $t->id();$t->foreignId('internacion_id')->constrained('vet_internaciones')->restrictOnDelete();
            $t->unsignedBigInteger('id_usuario');$t->unsignedBigInteger('asignado_por');$t->timestamps();
            $t->unique(['internacion_id','id_usuario'],'vet_internacion_prof_usuario_unique');
        });
    }
    public function down(): void {Schema::dropIfExists('vet_internacion_profesionales');Schema::table('vet_evoluciones',function(Blueprint $t){$t->dropColumn('procedimiento');});}
};
