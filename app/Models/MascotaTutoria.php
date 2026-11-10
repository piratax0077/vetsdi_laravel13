<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MascotaTutoria extends Model
{
    protected $table = 'mascotas_tutorias';
    protected $fillable = ['mascota_id', 'titular_id', 'usuario_id', 'correo', 'nombre', 'rol', 'estado', 'token_hash', 'vence_el'];
    protected $hidden = ['token_hash'];
    protected $casts = ['vence_el' => 'datetime'];

    public function estadoActual(): string
    {
        return $this->estado === 'pendiente' && $this->vence_el->isPast() ? 'expirada' : $this->estado;
    }
}
