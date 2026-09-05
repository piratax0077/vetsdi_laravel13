<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('razas_mascotas', function (Blueprint $table) {
            $table->string('slug', 190)
                ->nullable()
                ->after('nombre');
        });

        DB::table('razas_mascotas')
            ->select('id', 'nombre')
            ->orderBy('id')
            ->chunkById(200, function ($razas) {
                foreach ($razas as $raza) {
                    $slug = Str::slug($raza->nombre);

                    // Evita slug vacío en nombres excepcionales.
                    if ($slug === '') {
                        $slug = (string) $raza->id;
                    }

                    DB::table('razas_mascotas')
                        ->where('id', $raza->id)
                        ->update([
                            'slug' => $slug,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('razas_mascotas', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
