<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoLaboratorioMascota extends Model
{
    use HasFactory;

    protected $table = 'documentos_laboratorio_mascota';

    protected $fillable = [
        'id_mascota', 'id_responsable', 'id_usuario_laboratorio',
        'id_profesional_revisor', 'nombre_original', 'ruta', 'mime',
        'tamano', 'observacion', 'tipo_examen', 'estado', 'revisado_at',
    ];

    protected $casts = [
        'revisado_at' => 'datetime',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota');
    }

    public function responsable()
    {
        return $this->belongsTo(Paciente::class, 'id_responsable');
    }
}
