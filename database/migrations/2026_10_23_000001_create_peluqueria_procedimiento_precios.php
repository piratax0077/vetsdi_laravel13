<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
// Un procedimiento puede tener varios precios según la especie y el tamaño (o rango de peso) de la mascota
return new class extends Migration {
    public function up(): void {
        Schema::create('peluqueria_procedimiento_precios',function(Blueprint $t){$t->id();$t->unsignedBigInteger('procedimiento_id')->index();$t->string('especie',20)->default('Todas');$t->string('especie_exotico',100)->nullable();$t->string('tamano',20)->default('Todos');$t->decimal('peso_desde',5,1)->nullable();$t->decimal('peso_hasta',5,1)->nullable();$t->unsignedInteger('duracion');$t->unsignedInteger('precio');$t->unsignedSmallInteger('orden')->default(0);$t->timestamps();});
        // Los procedimientos que ya existían quedan con un precio para todas las mascotas
        DB::table('peluqueria_procedimientos')->orderBy('id')->each(function($p){
            DB::table('peluqueria_procedimiento_precios')->insert(['procedimiento_id'=>$p->id,'duracion'=>$p->duracion,'precio'=>$p->precio,'created_at'=>now(),'updated_at'=>now()]);
        });
    }
    public function down(): void {Schema::dropIfExists('peluqueria_procedimiento_precios');}
};
