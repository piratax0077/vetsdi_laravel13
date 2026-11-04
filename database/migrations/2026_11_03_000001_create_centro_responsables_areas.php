<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('centro_responsables_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_institucion');
            $table->unsignedBigInteger('id_profesional')->index();
            $table->string('area', 40);
            $table->timestamps();
            $table->unique(['id_institucion', 'area']);
        });
    }

    public function down(): void { Schema::dropIfExists('centro_responsables_areas'); }
};
