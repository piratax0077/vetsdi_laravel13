<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vet_responsables_area', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion'); $t->string('area',30);
            $t->string('nombre',150); $t->string('contacto',150)->nullable(); $t->timestamps();
            $t->unique(['id_institucion','area']);
        });
        Schema::create('vet_gastos_area', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('id_institucion'); $t->string('area',30);
            $t->string('tipo',20); $t->date('fecha'); $t->string('concepto',200);
            $t->string('proveedor',150)->nullable(); $t->string('comprobante',100)->nullable();
            $t->unsignedBigInteger('monto'); $t->unsignedBigInteger('id_usuario'); $t->timestamps();
            $t->index(['id_institucion','area','fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vet_gastos_area');
        Schema::dropIfExists('vet_responsables_area');
    }
};
