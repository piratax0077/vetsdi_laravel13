<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('vet_evoluciones',function(Blueprint $t){$t->string('tipo_registro',25)->default('veterinaria');}); }
    public function down(): void { Schema::table('vet_evoluciones',function(Blueprint $t){$t->dropColumn('tipo_registro');}); }
};
