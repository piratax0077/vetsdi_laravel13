<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MascotaGenealogia extends Model
{
    use HasFactory;

    protected $table = 'mascota_genealogias';

    protected $fillable = [
        'mascota_id',
        'padre_id',
        'madre_id',
        'abuelo_paterno_id',
        'abuela_paterna_id',
        'abuelo_materno_id',
        'abuela_materna_id',
        'padre_nombre',
        'madre_nombre',
        'abuelo_paterno_nombre',
        'abuela_paterna_nombre',
        'abuelo_materno_nombre',
        'abuela_materna_nombre',
        'padre_especie',
        'madre_especie',
        'abuelo_paterno_especie',
        'abuela_paterna_especie',
        'abuelo_materno_especie',
        'abuela_materna_especie',
        'padre_foto',
        'madre_foto',
        'abuelo_paterno_foto',
        'abuela_paterna_foto',
        'abuelo_materno_foto',
        'abuela_materna_foto',
        'numero_registro',
        'criador',
        'observaciones',
    ];

    public function mascota() { return $this->belongsTo(Mascota::class, 'mascota_id'); }
    public function padre() { return $this->belongsTo(Mascota::class, 'padre_id'); }
    public function madre() { return $this->belongsTo(Mascota::class, 'madre_id'); }
    public function abueloPaterno() { return $this->belongsTo(Mascota::class, 'abuelo_paterno_id'); }
    public function abuelaPaterna() { return $this->belongsTo(Mascota::class, 'abuela_paterna_id'); }
    public function abueloMaterno() { return $this->belongsTo(Mascota::class, 'abuelo_materno_id'); }
    public function abuelaMaterna() { return $this->belongsTo(Mascota::class, 'abuela_materna_id'); }
}
