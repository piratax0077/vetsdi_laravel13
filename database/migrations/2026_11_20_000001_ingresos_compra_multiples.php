<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('vet_ingresos_compra', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('titular',60)->index();
            $t->string('proveedor',200);
            $t->string('documento',150);
            $t->unsignedBigInteger('usuario_id');
            $t->string('ruta')->nullable();
            $t->string('archivo')->nullable();
            $t->string('mime')->nullable();
            $t->timestamp('created_at');
        });
    }
    public function down(): void { Schema::dropIfExists('vet_ingresos_compra'); }
};
