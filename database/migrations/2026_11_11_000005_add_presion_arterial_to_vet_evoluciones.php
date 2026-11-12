<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vet_evoluciones', function (Blueprint $table) {
            $table->unsignedSmallInteger('presion_pas')->nullable();
            $table->unsignedSmallInteger('presion_pad')->nullable();
            $table->decimal('presion_pam', 4, 1)->nullable();
            $table->string('presion_metodo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vet_evoluciones', function (Blueprint $table) {
            $table->dropColumn(['presion_pas', 'presion_pad', 'presion_pam', 'presion_metodo']);
        });
    }
};
