<?php

namespace App\Support;

use App\Models\Profesional;

class FichaVeterinariaVista
{
    public static function para(Profesional $profesional): string
    {
        if ((int) $profesional->id_especialidad === 2) {
            return 'atencion_odontologica.atencion_odonto_gral';
        }
        if ((int) $profesional->id_sub_tipo_especialidad === 119) {
            return 'atencion_veterinaria.atencion_medica_cirugia_general';
        }
        return 'atencion_veterinaria.atencion_veterinaria_general';
    }
}
