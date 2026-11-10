<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentroServiciosMascotas extends Model
{
    protected $table = 'centros_servicios_mascotas';
    protected $fillable = ['id_usuario', 'tipo', 'nombre', 'direccion', 'telefono', 'email', 'publicado', 'configuracion'];
    protected $casts = ['configuracion' => 'array', 'publicado' => 'boolean'];
}
