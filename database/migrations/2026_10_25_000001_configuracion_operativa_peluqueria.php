<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
return new class extends Migration {
 public function up(): void {
  Schema::create('peluqueria_sucursales',function(Blueprint $t){$t->id();$t->unsignedBigInteger('peluqueria_id')->index();$t->string('nombre',150);$t->string('direccion');$t->string('telefono',30)->nullable();$t->boolean('activo')->default(true);$t->unsignedSmallInteger('capacidad')->default(1);$t->text('agenda')->nullable();$t->timestamps();});
  Schema::create('peluqueria_personal',function(Blueprint $t){$t->id();$t->unsignedBigInteger('peluqueria_id');$t->unsignedBigInteger('usuario_id')->index();$t->unsignedBigInteger('sucursal_id')->nullable();$t->string('cargo',100);$t->text('permisos');$t->boolean('activo')->default(true);$t->timestamps();$t->unique(['peluqueria_id','usuario_id']);});
  Schema::create('peluqueria_bloqueos',function(Blueprint $t){$t->id();$t->unsignedBigInteger('peluqueria_id')->index();$t->unsignedBigInteger('sucursal_id')->index();$t->dateTime('inicio');$t->dateTime('fin');$t->string('motivo',255);$t->timestamps();});
  Schema::table('peluqueria_citas',function(Blueprint $t){$t->unsignedBigInteger('sucursal_id')->nullable()->index();$t->unsignedBigInteger('personal_id')->nullable()->index();});
  DB::table('peluquerias')->orderBy('id')->each(function($p){
   $id=DB::table('peluqueria_sucursales')->insertGetId(['peluqueria_id'=>$p->id,'nombre'=>'Sucursal principal','direccion'=>$p->direccion,'telefono'=>$p->telefono,'activo'=>true,'capacidad'=>1,'created_at'=>now(),'updated_at'=>now()]);
   DB::table('peluqueria_citas')->where('peluqueria_id',$p->id)->update(['sucursal_id'=>$id]);
  });
 }
 public function down(): void {
  Schema::table('peluqueria_citas',function(Blueprint $t){$t->dropColumn(['sucursal_id','personal_id']);});
  Schema::dropIfExists('peluqueria_bloqueos');Schema::dropIfExists('peluqueria_personal');Schema::dropIfExists('peluqueria_sucursales');
 }
};
