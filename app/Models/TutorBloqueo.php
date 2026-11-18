<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Tutor que un profesional bloqueó: mientras exista el registro no puede tomar hora con él
class TutorBloqueo extends Model
{
    protected $table = 'tutor_bloqueos';

    protected $fillable = [
        'id_paciente',
        'id_profesional',
        'motivo',
        'id_lugar_atencion',
    ];

    public static function estaBloqueado($idPaciente, $idProfesional, $idLugar = null): bool
    {
        if (!$idPaciente || !$idProfesional) {
            return false;
        }

        return static::where('id_paciente', $idPaciente)
            ->where(function ($consulta) use ($idProfesional, $idLugar) {
                $consulta->where(fn ($personal) => $personal->where('id_profesional', $idProfesional)
                    ->where('id_lugar_atencion', 0));
                if ($idLugar) {
                    $consulta->orWhere('id_lugar_atencion', $idLugar);
                }
            })
            ->exists();
    }
}
