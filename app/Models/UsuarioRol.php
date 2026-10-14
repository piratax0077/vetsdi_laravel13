<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Rol de cuenta de una persona (tutor, profesional, asistente o clinica).
 * Una misma identidad puede tener varios, cada uno con su propio estado.
 */
class UsuarioRol extends Model
{
    use HasFactory;

    protected $table = 'usuario_roles';

    protected $fillable = [
        'id_usuario',
        'tipo',
        'estado',
        'perfil_completo',
        'id_plan',
        'fecha_activacion',
        'fecha_perfil_completo',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'perfil_completo' => 'boolean',
        'fecha_activacion' => 'datetime',
        'fecha_perfil_completo' => 'datetime',
    ];

    public function Usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }
}
