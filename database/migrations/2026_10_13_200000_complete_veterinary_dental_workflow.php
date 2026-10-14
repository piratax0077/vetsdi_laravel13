<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('presupuestos_dental', fn (Blueprint $table) => $table->unsignedBigInteger('id_mascota')->nullable()->index());
        Schema::table('odontogramas_mascotas', fn (Blueprint $table) => $table->string('tratamiento', 190)->nullable()->change());
        Schema::table('pagos_presupuesto_dental', function (Blueprint $table) {
            $table->uuid('referencia_operacion')->nullable();
            $table->unique(['id_presupuesto', 'referencia_operacion'], 'pago_dental_operacion_unique');
        });
        Schema::create('odontogramas_fichas_mascotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ficha_atencion_id')->unique();
            $table->unsignedBigInteger('mascota_id')->index();
            $table->unsignedBigInteger('profesional_id');
            $table->unsignedBigInteger('lugar_atencion_id')->nullable();
            $table->string('especie', 20);
            $table->json('odontograma');
            $table->json('periodontograma');
            $table->timestamps();
        });
        DB::table('presupuestos_dental')->whereNull('id_mascota')->orderBy('id')->chunkById(200, function ($presupuestos) {
            foreach ($presupuestos as $presupuesto) {
                $mascota = DB::table('fichas_atenciones')->where('id', $presupuesto->id_ficha_atencion)->value('id_mascota');
                if ($mascota) DB::table('presupuestos_dental')->where('id', $presupuesto->id)->update(['id_mascota' => $mascota]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontogramas_fichas_mascotas');
        Schema::table('pagos_presupuesto_dental', function (Blueprint $table) {
            $table->dropUnique('pago_dental_operacion_unique');
            $table->dropColumn('referencia_operacion');
        });
        Schema::table('presupuestos_dental', fn (Blueprint $table) => $table->dropColumn('id_mascota'));
    }
};
