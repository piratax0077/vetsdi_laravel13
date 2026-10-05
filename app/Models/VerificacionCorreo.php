<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Token de confirmacion de correo. Se invalida al usarse o al vencer.
 */
class VerificacionCorreo extends Model
{
    use HasFactory;

    protected $table = 'verificaciones_correo';

    protected $fillable = [
        'id_usuario',
        'email',
        'token',
        'expira_en',
        'verificado_en',
        'enviado_en',
    ];

    protected $casts = [
        'expira_en' => 'datetime',
        'verificado_en' => 'datetime',
        'enviado_en' => 'datetime',
    ];

    public function Usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function estaVigente(): bool
    {
        return $this->verificado_en === null && $this->expira_en->isFuture();
    }
}
