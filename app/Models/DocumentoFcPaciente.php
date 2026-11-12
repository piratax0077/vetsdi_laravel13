<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoFcPaciente extends Model
{
    use HasFactory;
    protected $table = 'documento_fc_paciente';

    public function scopeIndicacionesMascota($query, int $mascota, int $usuario)
    {
        return $query->where('otro', \App\Services\DocumentoVeterinarioEmitido::INDICACION)
            ->where('estado', 1)
            ->whereHas('ficha_atencion', fn ($ficha) => $ficha->where('id_mascota', $mascota))
            ->where(fn ($documentos) => $documentos
                ->whereHas('paciente', fn ($tutor) => $tutor->where('id_usuario', $usuario))
                ->orWhereHas('profesional', fn ($profesional) => $profesional->where('id_usuario', $usuario)));
    }

    public function tipo_documento ()
    {
        return $this->hasOne(TipoDocumento::class, 'id', 'id_tipo_documento');
    }

    public function paciente ()
    {
        return $this->hasOne(Paciente::class, 'id', 'id_paciente');
    }

    public function profesional ()
    {
        return $this->hasOne(Profesional::class, 'id', 'id_profesional');
    }

    public function ficha_atencion ()
    {
        return $this->hasOne(FichaAtencion::class, 'id', 'id_ficha_atencion');
    }

    public function lugar_atencion ()
    {
        return $this->hasOne(LugarAtencion::class, 'id', 'id_lugar_atencion');
    }

}
