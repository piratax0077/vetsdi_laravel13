<?php

namespace App\Services;

use App\Models\HoraMedica;
use Carbon\Carbon;

class UltimaConsultaAgenda
{
    public function fecha(int $idPaciente, int $idProfesional, int $idLugarAtencion): ?string
    {
        $fecha = HoraMedica::query()
            ->join('fichas_atenciones', 'horas_medicas.id_ficha_atencion', '=', 'fichas_atenciones.id')
            ->where('horas_medicas.id_paciente', $idPaciente)
            ->where('horas_medicas.id_profesional', $idProfesional)
            ->where('horas_medicas.id_lugar_atencion', $idLugarAtencion)
            ->where('fichas_atenciones.finalizada', 1)
            ->max('horas_medicas.fecha_consulta');

        return $fecha ? Carbon::parse($fecha)->toDateString() : null;
    }
}
