<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConvenioVeterinario extends Model
{
    protected $table = 'convenios_veterinarios';
    protected $fillable = ['id_profesional', 'id_lugar_atencion', 'nombre', 'porcentaje', 'activo'];
    protected $casts = ['activo' => 'boolean', 'porcentaje' => 'decimal:2'];
}
