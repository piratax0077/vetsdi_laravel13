<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boxes_cm', fn (Blueprint $table) => $table->string('numero_box', 255)->change());
    }

    public function down(): void
    {
        DB::table('boxes_cm')->select('id', 'numero_box')->chunkById(500, function ($boxes) {
            foreach ($boxes as $box) {
                $numero = filter_var($box->numero_box, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => -2147483648, 'max_range' => 2147483647],
                ]);
                if ($numero === false || (string) $numero !== (string) $box->numero_box) {
                    throw new RuntimeException('Hay nombres de boxes que no pueden convertirse a números sin perder datos.');
                }
            }
        });
        Schema::table('boxes_cm', fn (Blueprint $table) => $table->integer('numero_box')->change());
    }
};
