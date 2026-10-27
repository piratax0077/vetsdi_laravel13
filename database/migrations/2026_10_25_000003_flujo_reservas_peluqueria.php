<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('peluqueria_citas',function(Blueprint $t){
  $t->string('via_confirmacion',40)->nullable();$t->timestamp('confirmada_en')->nullable();
  $t->string('medio_pago',30)->nullable();$t->decimal('monto_pagado',12,0)->nullable();
  $t->string('referencia_pago',100)->nullable();$t->timestamp('pagada_en')->nullable();$t->unsignedBigInteger('pago_usuario_id')->nullable();
  $t->timestamp('espera_en')->nullable();
 }); }
 public function down(): void { Schema::table('peluqueria_citas',fn(Blueprint $t)=>$t->dropColumn(['via_confirmacion','confirmada_en','medio_pago','monto_pagado','referencia_pago','pagada_en','pago_usuario_id','espera_en'])); }
};
