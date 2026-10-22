<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('peluqueria_procedimientos',function(Blueprint $t){$t->id();$t->unsignedBigInteger('peluqueria_id')->index();$t->string('nombre',150);$t->unsignedInteger('duracion');$t->unsignedInteger('precio');$t->boolean('activo')->default(true);$t->text('indicaciones')->nullable();$t->timestamps();});
        Schema::table('peluqueria_citas',function(Blueprint $t){$t->unsignedBigInteger('procedimiento_id')->nullable();});
    }
    public function down(): void {Schema::table('peluqueria_citas',fn(Blueprint $t)=>$t->dropColumn('procedimiento_id'));Schema::dropIfExists('peluqueria_procedimientos');}
};
