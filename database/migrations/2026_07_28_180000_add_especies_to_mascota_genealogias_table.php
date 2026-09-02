<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascota_genealogias', function (Blueprint $table) {
            $table->string('padre_especie', 20)->nullable()->after('abuela_materna_nombre');
            $table->string('madre_especie', 20)->nullable()->after('padre_especie');
            $table->string('abuelo_paterno_especie', 20)->nullable()->after('madre_especie');
            $table->string('abuela_paterna_especie', 20)->nullable()->after('abuelo_paterno_especie');
            $table->string('abuelo_materno_especie', 20)->nullable()->after('abuela_paterna_especie');
            $table->string('abuela_materna_especie', 20)->nullable()->after('abuelo_materno_especie');
        });
    }

    public function down(): void
    {
        Schema::table('mascota_genealogias', function (Blueprint $table) {
            $table->dropColumn([
                'padre_especie',
                'madre_especie',
                'abuelo_paterno_especie',
                'abuela_paterna_especie',
                'abuelo_materno_especie',
                'abuela_materna_especie',
            ]);
        });
    }
};
