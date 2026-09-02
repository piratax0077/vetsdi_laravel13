<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterinary_emergency_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mascota')->index();
            $table->unsignedBigInteger('id_profesional')->index();
            $table->unsignedBigInteger('id_tutor')->index();
            $table->string('status', 20)->default('pending')->index();
            $table->string('requested_by', 20)->default('patient');
            $table->timestamps();
            $table->index(['id_profesional', 'status']);
            $table->index(['id_tutor', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinary_emergency_links');
    }
};
