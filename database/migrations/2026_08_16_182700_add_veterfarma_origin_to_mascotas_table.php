<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->string('origen_registro', 40)->nullable()->after('estado');
            $table->boolean('ficha_incompleta_veterfarma')->default(false)->after('origen_registro');
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropColumn(['origen_registro', 'ficha_incompleta_veterfarma']);
        });
    }
};
