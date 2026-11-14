<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Razas genéricas que se usan a diario en la consulta:
 * perros sin raza definida (SRD) y gatos domésticos según el largo del pelo (DPC, DPM, DPL).
 */
return new class extends Migration
{
    private array $razas = [
        'canina' => ['SRD (Sin Raza Definida)'],
        'felina' => [
            'DPC (Doméstico de Pelo Corto)',
            'DPM (Doméstico de Pelo Medio)',
            'DPL (Doméstico de Pelo Largo)',
        ],
    ];

    public function up(): void
    {
        $ahora = now();
        foreach ($this->razas as $slugEspecie => $nombres) {
            $especieId = DB::table('especies_mascotas')->where('slug', $slugEspecie)->value('id');
            if (!$especieId) {
                continue;
            }

            foreach ($nombres as $nombre) {
                $existe = DB::table('razas_mascotas')->where('especie_id', $especieId)->where('nombre', $nombre)->exists();
                if (!$existe) {
                    DB::table('razas_mascotas')->insert([
                        'especie_id' => $especieId,
                        'nombre' => $nombre,
                        'slug' => Str::slug($nombre),
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Se conservan: alguna mascota ya puede tener una de estas razas.
    }
};
