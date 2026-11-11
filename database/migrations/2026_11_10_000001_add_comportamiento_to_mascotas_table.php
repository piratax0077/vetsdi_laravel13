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
            if (! Schema::hasColumn('mascotas', 'comportamiento')) {
                $table->text('comportamiento')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('mascotas') && Schema::hasColumn('mascotas', 'comportamiento')) {
            Schema::table('mascotas', function (Blueprint $table) {
                $table->dropColumn('comportamiento');
            });
        }
    }
};
