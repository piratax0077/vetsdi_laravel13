<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('peluqueria_clientes',function(Blueprint $t){$t->string('rut',12)->nullable()->index();$t->unsignedBigInteger('mascota_id')->nullable()->index();}); }
 public function down(): void { Schema::table('peluqueria_clientes',fn(Blueprint $t)=>$t->dropColumn(['rut','mascota_id'])); }
};
