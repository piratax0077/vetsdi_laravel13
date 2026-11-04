<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vet_trabajadores', function (Blueprint $t) {
            $t->unsignedBigInteger('region_id')->nullable();
            $t->unsignedBigInteger('comuna_id')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('vet_trabajadores', fn (Blueprint $t) => $t->dropColumn(['region_id','comuna_id']));
    }
};
