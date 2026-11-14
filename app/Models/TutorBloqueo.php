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
    ];

    public static function estaBloqueado($idPaciente, $idProfesional): bool
    {
        if (!$idPaciente || !$idProfesional) {
            return false;
        }

        return static::where('id_paciente', $idPaciente)
            ->where('id_profesional', $idProfesional)
            ->exists();
    }
}
