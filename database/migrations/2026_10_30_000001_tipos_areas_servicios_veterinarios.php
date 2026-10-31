<?php

use App\Services\OperacionVeterinaria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // El responsable de cada área se guarda en areas_cm, que necesita un tipo para cada servicio.
        foreach (OperacionVeterinaria::AREAS as $clave => $nombre) {
            if (!DB::table('tipos_areas_cm')->where('nombre', $nombre)->exists()) {
                DB::table('tipos_areas_cm')->insert(['nombre'=>$nombre, 'descripcion'=>$nombre, 'otro'=>$clave, 'created_at'=>now(), 'updated_at'=>now()]);
            }
        }
    }

    public function down(): void
    {
        // Un tipo con áreas registradas se conserva para no dejarlas fuera del listado.
        DB::table('tipos_areas_cm')->whereIn('nombre', OperacionVeterinaria::AREAS)
            ->whereNotIn('id', DB::table('areas_cm')->select('id_tipo_area_cm'))->delete();
    }
};
