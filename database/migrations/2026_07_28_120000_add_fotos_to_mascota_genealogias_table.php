<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascota_genealogias', function (Blueprint $table) {
            $table->string('padre_foto')->nullable()->after('abuela_materna_nombre');
            $table->string('madre_foto')->nullable()->after('padre_foto');
            $table->string('abuelo_paterno_foto')->nullable()->after('madre_foto');
            $table->string('abuela_paterna_foto')->nullable()->after('abuelo_paterno_foto');
            $table->string('abuelo_materno_foto')->nullable()->after('abuela_paterna_foto');
            $table->string('abuela_materna_foto')->nullable()->after('abuelo_materno_foto');
        });
    }

    public function down(): void
    {
        Schema::table('mascota_genealogias', function (Blueprint $table) {
            $table->dropColumn([
                'padre_foto', 'madre_foto',
                'abuelo_paterno_foto', 'abuela_paterna_foto',
                'abuelo_materno_foto', 'abuela_materna_foto',
            ]);
        });
    }
};
