<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mascotas')) {
            return;
        }

        Schema::table('mascotas', function (Blueprint $table) {
            if (! Schema::hasColumn('mascotas', 'fallecida')) {
                $table->boolean('fallecida')->default(false)->after('estado');
            }
            if (! Schema::hasColumn('mascotas', 'fecha_fallecimiento')) {
                $table->date('fecha_fallecimiento')->nullable()->after('fallecida');
            }
            if (! Schema::hasColumn('mascotas', 'memorial_registro')) {
                $table->json('memorial_registro')->nullable()->after('fecha_fallecimiento');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mascotas')) {
            return;
        }

        Schema::table('mascotas', function (Blueprint $table) {
            if (Schema::hasColumn('mascotas', 'memorial_registro')) {
                $table->dropColumn('memorial_registro');
            }
            if (Schema::hasColumn('mascotas', 'fecha_fallecimiento')) {
                $table->dropColumn('fecha_fallecimiento');
            }
            if (Schema::hasColumn('mascotas', 'fallecida')) {
                $table->dropColumn('fallecida');
            }
        });
    }
};

