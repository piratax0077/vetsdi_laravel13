<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('razas_mascotas', function (Blueprint $table) {
            if (! Schema::hasColumn('razas_mascotas', 'slug')) {
                $table->string('slug', 120)->nullable()->after('nombre');
            }
        });

        $razas = DB::table('razas_mascotas')->orderBy('id')->get();

        foreach ($razas as $raza) {
            $slugBase = Str::slug((string) ($raza->nombre ?? 'raza-' . $raza->id));
            $slug = $slugBase !== '' ? $slugBase : 'raza-' . $raza->id;
            $finalSlug = $slug;
            $counter = 2;

            while (
                DB::table('razas_mascotas')
                    ->where('especie_id', $raza->especie_id)
                    ->where('slug', $finalSlug)
                    ->where('id', '!=', $raza->id)
                    ->exists()
            ) {
                $finalSlug = $slug . '-' . $counter;
                $counter++;
            }

            DB::table('razas_mascotas')->where('id', $raza->id)->update(['slug' => $finalSlug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('razas_mascotas', function (Blueprint $table) {
            if (Schema::hasColumn('razas_mascotas', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
