<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('peluqueria_citas',fn(Blueprint $t)=>$t->text('procedimientos_reservados')->nullable()); }
 public function down(): void { Schema::table('peluqueria_citas',fn(Blueprint $t)=>$t->dropColumn('procedimientos_reservados')); }
};
