<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mascotas')) {
            return;
        }

        Schema::table('mascotas', function (Blueprint $table) {
            if (!Schema::hasColumn('mascotas', 'solicitudes_apareamiento_registro')) {
                $table->json('solicitudes_apareamiento_registro')->nullable()->after('reservas_servicios_registro');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('mascotas')) {
            return;
        }

        Schema::table('mascotas', function (Blueprint $table) {
            if (Schema::hasColumn('mascotas', 'solicitudes_apareamiento_registro')) {
                $table->dropColumn('solicitudes_apareamiento_registro');
            }
        });
    }
};

