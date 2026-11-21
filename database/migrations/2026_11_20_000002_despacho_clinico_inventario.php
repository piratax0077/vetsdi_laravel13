<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('vet_inventario_movimientos', function (Blueprint $t) {
            $t->unsignedBigInteger('profesional_uso_id')->nullable();
            $t->unsignedBigInteger('despachador_id')->nullable();
            $t->string('profesional_uso',200)->nullable();
            $t->string('despachado_por',200)->nullable();
            $t->string('uso_clinico',250)->nullable();
        });
    }
    public function down(): void {
        Schema::table('vet_inventario_movimientos', fn (Blueprint $t) => $t->dropColumn(['profesional_uso_id','despachador_id','profesional_uso','despachado_por','uso_clinico']));
    }
};
