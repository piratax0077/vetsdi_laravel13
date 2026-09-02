<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class LugarAtencionInstitucionResolver
{
    /**
     * Obtiene la institución asociada a un lugar de atención, cuando existe.
     */
    public static function resolve(?int $idLugarAtencion): ?int
    {
        if (empty($idLugarAtencion)) {
            return null;
        }

        $tables = [
            'sucursal',
            'profesionales_lugares_atencion',
            'asistentes_lugar_atencion',
            'administrativos_lugar_atencion',
            'especialidades_cm',
        ];

        foreach ($tables as $table) {
            $idInstitucion = DB::table($table)
                ->where('id_lugar_atencion', $idLugarAtencion)
                ->whereNotNull('id_institucion')
                ->value('id_institucion');

            if (!empty($idInstitucion)) {
                return (int) $idInstitucion;
            }
        }

        return null;
    }
}
