<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Nota que el profesional deja sobre un tutor; el tutor no la ve
class TutorNotaInterna extends Model
{
    protected $table = 'tutor_notas_internas';

    protected $fillable = [
        'id_paciente',
        'id_profesional',
        'id_lugar_atencion',
        'nota',
        'conflictivo',
    ];

    protected $casts = [
        'conflictivo' => 'boolean',
    ];
}
