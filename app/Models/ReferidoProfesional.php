<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferidoProfesional extends Model
{
    use HasFactory;

    protected $table = 'referidos_profesionales';

    protected $guarded = [];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'fecha_visita' => 'datetime',
        'fecha_registro' => 'datetime',
        'fecha_activacion' => 'datetime',
        'fecha_bonificacion' => 'datetime',
    ];

    public function referente()
    {
        return $this->belongsTo(User::class, 'id_usuario_referente');
    }

    public function invitado()
    {
        return $this->belongsTo(User::class, 'id_usuario_invitado');
    }
}
