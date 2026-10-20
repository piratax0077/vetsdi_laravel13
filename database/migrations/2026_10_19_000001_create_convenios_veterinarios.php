<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('convenios_veterinarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_profesional')->index();
            $table->unsignedBigInteger('id_lugar_atencion')->nullable();
            $table->string('nombre', 150);
            $table->decimal('porcentaje', 5, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        Schema::table('bonos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_convenio_veterinario')->nullable();
            $table->string('nombre_convenio_veterinario', 150)->nullable();
            $table->decimal('porcentaje_convenio_veterinario', 5, 2)->nullable();
            $table->unsignedInteger('descuento_convenio_veterinario')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bonos', fn (Blueprint $table) => $table->dropColumn([
            'id_convenio_veterinario', 'nombre_convenio_veterinario',
            'porcentaje_convenio_veterinario', 'descuento_convenio_veterinario',
        ]));
        Schema::dropIfExists('convenios_veterinarios');
    }
};
