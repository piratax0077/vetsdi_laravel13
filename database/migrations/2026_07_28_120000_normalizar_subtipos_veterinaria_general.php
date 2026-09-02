<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $subtipos = [
            36 => 'Mascotas y animales mayores',
            37 => 'Mascotas',
            38 => 'Atención veterinaria a domicilio',
        ];

        foreach ($subtipos as $id => $nombre) {
            DB::table('sub_tipo_especialidad')
                ->where('id', $id)
                ->where('id_tipo_especialidad', 8)
                ->update([
                    'nombre' => $nombre,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $subtipos = [
            36 => 'Medicina Familiar',
            37 => 'Medicina general adultos y niños',
            38 => 'Medicina general a Domicilio',
        ];

        foreach ($subtipos as $id => $nombre) {
            DB::table('sub_tipo_especialidad')
                ->where('id', $id)
                ->where('id_tipo_especialidad', 8)
                ->update([
                    'nombre' => $nombre,
                    'updated_at' => now(),
                ]);
        }
    }
};
