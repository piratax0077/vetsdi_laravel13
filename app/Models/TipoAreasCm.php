<?php

namespace App\Models;

use App\Services\OperacionVeterinaria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAreasCm extends Model
{
    use HasFactory;
    protected $table = 'tipos_areas_cm';

    public function scopeVeterinarias($query)
    {
        return $query->whereIn('nombre', OperacionVeterinaria::AREAS)->orderBy('id');
    }

    public function scopeNoVeterinarias($query)
    {
        return $query->whereNotIn('nombre', OperacionVeterinaria::AREAS);
    }

    // Clave del área en la configuración del establecimiento; null si es un tipo antiguo.
    public static function claveServicio($id): ?string
    {
        $nombre = static::whereKey($id)->value('nombre');
        return array_search($nombre, OperacionVeterinaria::AREAS, true) ?: null;
    }
}
