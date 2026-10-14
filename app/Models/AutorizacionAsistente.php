<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutorizacionAsistente extends Model
{
    protected $table = 'autorizaciones_asistentes';
    protected $guarded = ['id'];
    protected $casts = ['permisos' => 'array'];
}
