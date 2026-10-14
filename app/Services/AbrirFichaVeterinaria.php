<?php

namespace App\Services;

use App\Models\{FichaAtencion, HoraMedica, Mascota};
use Illuminate\Support\Facades\DB;

class AbrirFichaVeterinaria
{
    public function abrir(int $idHora, int $idProfesional): FichaAtencion
    {
        return DB::transaction(function () use ($idHora, $idProfesional) {
            $hora = HoraMedica::whereKey($idHora)->where('id_profesional', $idProfesional)->lockForUpdate()->firstOrFail();
            $mascota = Mascota::whereKey($hora->id_mascota)->lockForUpdate()->firstOrFail();
            abort_unless((int) $mascota->id_responsable === (int) $hora->id_paciente, 403);
            if ($hora->id_ficha_atencion) {
                $ficha = FichaAtencion::whereKey($hora->id_ficha_atencion)->lockForUpdate()->firstOrFail();
                foreach (['id_profesional', 'id_paciente', 'id_mascota', 'id_lugar_atencion'] as $campo) {
                    abort_unless((int) $ficha->{$campo} === (int) $hora->{$campo}, 403, 'La ficha vinculada no corresponde a esta atención.');
                }
                return $ficha;
            }
            $ficha = new FichaAtencion();
            foreach (['id_profesional', 'id_paciente', 'id_mascota', 'id_lugar_atencion'] as $campo) $ficha->{$campo} = $hora->{$campo};
            $ficha->finalizada = 0;
            $ficha->confidencial = 0;
            $ficha->cronico = 0;
            $ficha->ges = 0;
            $ficha->saveOrFail();
            $hora->id_ficha_atencion = $ficha->id;
            $hora->id_estado = 5;
            $hora->fecha_realizacion_consulta = now();
            $hora->saveOrFail();
            return $ficha;
        });
    }
}
