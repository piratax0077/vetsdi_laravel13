<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('vet_inventario_movimientos', function (Blueprint $t) {
            $t->unsignedBigInteger('ficha_id')->nullable()->index();
            $t->string('indicado_por', 200)->nullable();
            $t->string('administrado_por', 200)->nullable();
            $t->unsignedBigInteger('administrador_id')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('vet_inventario_movimientos', fn (Blueprint $t) => $t->dropColumn(['ficha_id','indicado_por','administrado_por','administrador_id']));
    }
};
