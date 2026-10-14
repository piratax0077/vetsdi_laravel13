<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MascotaHermano extends Model
{
    protected $table = 'mascota_hermanos';

    protected $fillable = [
        'mascota_id',
        'hermano_id',
        'nombre',
        'especie',
        'sexo',
        'foto',
        'tipo',
    ];

    public function mascota() { return $this->belongsTo(Mascota::class, 'mascota_id'); }
    public function hermano() { return $this->belongsTo(Mascota::class, 'hermano_id'); }
}
