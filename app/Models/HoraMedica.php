<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parametro;
use App\Models\Mascota;

class HoraMedica extends Model
{
    use HasFactory;
    protected $table = 'horas_medicas';

    protected $fillable = [
        'id_paciente',
        'id_mascota',
        'voucher_externo_id',
        'voucher_codigo',
        'voucher_estado',
        'descripcion',  // Reemplaza con el nombre real del primer campo
    ];

    // Un tutor bloqueado por el profesional no puede tomar hora con él, venga la reserva de donde venga
    protected static function booted()
    {
        static::creating(function (HoraMedica $hora) {
            if (TutorBloqueo::estaBloqueado($hora->id_paciente, $hora->id_profesional)) {
                throw new \App\Exceptions\TutorBloqueadoException();
            }
        });
    }

    public function Estado()
    {
        return $this->hasOne(Parametro::class,'id','id_estado');
    }

    public function permiteVideoconsulta(): bool
    {
        return (bool) $this->id_mascota
            && ($this->tipo_hora_medica === 'T' || $this->id_jitsi_video_consulta)
            && in_array((int) $this->id_estado, [2, 4, 5, 8], true);
    }

    public function permiteAbrirAtencion(): bool
    {
        return in_array((int) $this->id_estado, [4, 5, 6, 8], true);
    }

    public function Paciente()
    {
        return $this->hasOne(Paciente::class,'id','id_paciente');
    }

    public function Mascota()
    {
        return $this->hasOne(Mascota::class, 'id', 'id_mascota');
    }

    public function Profesional()
    {
        return $this->hasOne(Profesional::class,'id','id_profesional');
    }

    public function LugarAtencion()
    {
        return $this->hasOne(LugarAtencion::class,'id','id_lugar_atencion');
    }

    public function scopeIdNotIn($query, $array)
    {
        if(count($array) > 0)
            return $query->whereNotIn('id',$array);
    }

    public function scopeProximasCitas($query)
    {
        $hoy = today('America/Santiago');
        $inicio = $hoy->copy()->setTimezone(config('app.timezone'))->toDateTimeString();
        $fin = $hoy->copy()->addDay()->setTimezone(config('app.timezone'))->toDateTimeString();
        return $query->where(function ($citas) use ($hoy, $inicio, $fin) {
            $citas->where(fn ($pendientes) => $pendientes->where('id_estado', '!=', 6)
                ->whereDate('fecha_consulta', '>=', $hoy->toDateString()))
                ->orWhere(function ($realizadas) use ($hoy, $inicio, $fin) {
                    $realizadas->where('id_estado', 6)->where(function ($fechas) use ($hoy, $inicio, $fin) {
                        $realizacion = \Illuminate\Support\Facades\DB::raw('COALESCE(fecha_realizacion_consulta, updated_at)');
                        $fechas->where(fn ($rango) => $rango->where($realizacion, '>=', $inicio)->where($realizacion, '<', $fin))
                            ->orWhere(fn ($sinFecha) => $sinFecha->whereNull('fecha_realizacion_consulta')
                                ->whereNull('updated_at')->whereDate('fecha_consulta', $hoy->toDateString()));
                    });
                });
        });
    }

    public function scopeParaMascotaAgenda($query, $idMascota)
    {
        // El tutor puede reservar para distintas mascotas el mismo día.
        // Las horas antiguas sin mascota conservan su validación por paciente.
        return $idMascota ? $query->where('id_mascota', $idMascota) : $query;
    }

    public function Notificacionesconfirmacion()
    {
        return $this->hasOne(NotificacionConfirmacion::class, 'id_evento', 'id')->where('tipo_notificacion',1);
    }

    public function FichaOtrosProfesionales()
    {
        return $this->hasOne(FichaOtrosProfesionales::class,'id','id_ficha_otros_prof');
    }

    public function ProcedimientoCentro()
    {
        return $this->hasOne(ProcedimientosCentro::class,'id','id_procedimiento');
    }

    public function FichaAtencion()
    {
        return $this->hasOne(FichaAtencion::class,'id','id_ficha_atencion');
    }
}
