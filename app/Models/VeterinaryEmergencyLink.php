<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VeterinaryEmergencyLink extends Model
{
    use HasFactory;

    protected $table = 'veterinary_emergency_links';

    protected $fillable = [
        'id_mascota',
        'id_profesional',
        'id_tutor',
        'status',
        'requested_by',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'id_profesional');
    }

    public function tutor()
    {
        return $this->belongsTo(Paciente::class, 'id_tutor');
    }
}
