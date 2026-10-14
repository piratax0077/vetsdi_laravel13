<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('autorizaciones_asistentes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_asistente');
            $table->unsignedBigInteger('id_lugar_atencion');
            // Zero identifies a policy granted by the centre for the whole location.
            $table->unsignedBigInteger('id_profesional')->default(0);
            $table->unsignedBigInteger('id_usuario_autoriza');
            $table->json('permisos');
            $table->timestamps();
            $table->unique(['id_asistente', 'id_lugar_atencion', 'id_profesional'], 'autorizacion_asistente_ambito_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizaciones_asistentes');
    }
};
