<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Premium;
use App\Models\LugarAtencion;
use App\Models\Direccion;
use App\Models\Profesional;
use App\Models\Paciente;
use App\Models\ContactoEmergencia;
use Illuminate\Support\Facades\Auth;

class Asistente extends Model
{
    use HasFactory;
    protected $table = 'asistentes';
    protected $fillable = [
        'foto_perfil',
    ];

    public function Premium()
    {
        return $this->hasOne(Premium::class, 'id', 'id_premium');
    }

    public function Direccion()
    {
        return $this->hasOne(Direccion::class, 'id', 'id_direccion');
    }

    public function LugarAtencion()
    {
        // return $this->hasOne(LugarAtencion::class, 'id', 'id_lugar_atencion');
        return $this->belongsToMany(LugarAtencion::class, 'asistentes_lugar_atencion','id_asistente', 'id_lugar_atencion');
    }

    public function Profesionales()
    {
        return $this->belongsToMany(Profesional::class,'profesionales_asistentes','id_asistente','id_profesional');
    }

    public function ContactosEmergencia()
    {
        return $this->belongsToMany(ContactoEmergencia::class, 'asistente_contacto_emergencia', 'id_asistente', 'id_contacto')
                        ->withPivot('relacion')
                        ->orderBy('prioridad');
    }

    public function Paciente_normal()
    {
        $pacientes = [];
        foreach ($this->Profesionales()->get() as $pro) {
            foreach ($pro->FichaAtencion()->get() as $fi) {
                foreach ($fi->Paciente()->get() as $pa) {
                    $valida = true;
                    if(count($pacientes) > 0 ){
                        foreach ($pacientes as $va) {
                            if($va->id == $pa->id){
                                $valida = false;
                            }
                        }
                    }
                    if($valida){
                        array_push($pacientes, $pa);
                    }
                }
            }
        }
        return $pacientes;
    }

    public function AsistenteTipo()
    {
        return $this->hasOne(AsistenteTipo::class,'id','id_asistente_tipo');
    }

    public function ScopeAsistentesLugarAtencion($query, $id_lugar_atencion){
        return $this->hasMany(AsistenteLugarAtencion::class, 'id_asistente', 'id')->where('id_lugar_atencion', $id_lugar_atencion);
    }

    /**
     * Cuando la persona ya está registrada con otro rol (tutor o profesional),
     * trae desde ahí los datos que al asistente le falten: sexo, fecha de
     * nacimiento y residencia. Nunca pisa lo que el asistente ya tiene.
     *
     * La residencia se copia a un registro propio, para que al editarla desde
     * este perfil no cambie la de sus otras cuentas.
     */
    public function completar_con_datos_registrados()
    {
        if(!$this->id_usuario || ($this->sexo && $this->fecha_nac && $this->id_direccion))
            return false;

        $paciente = Paciente::where('id_usuario', $this->id_usuario)->first();
        $profesional = Profesional::where('id_usuario', $this->id_usuario)->first();

        $cambio = false;

        if(!$this->sexo)
        {
            $sexo = ($paciente->sexo ?? null) ?: ($profesional->sexo ?? null);
            if($sexo)
            {
                $this->sexo = $sexo;
                $cambio = true;
            }
        }

        if(!$this->fecha_nac)
        {
            $fecha_nac = ($paciente->fecha_nac ?? null) ?: ($profesional->fecha_nacimiento ?? null);
            if($fecha_nac)
            {
                $this->fecha_nac = $fecha_nac;
                $cambio = true;
            }
        }

        if(!$this->id_direccion)
        {
            $id_direccion = ($paciente->id_direccion ?? null) ?: ($profesional->id_direccion ?? null);
            $registrada = $id_direccion ? Direccion::find($id_direccion) : null;

            if($registrada && $registrada->id_ciudad)
            {
                $direccion = new Direccion();
                $direccion->direccion = $registrada->direccion;
                $direccion->numero_dir = $registrada->numero_dir;
                $direccion->id_ciudad = $registrada->id_ciudad;
                $direccion->save();

                $this->id_direccion = $direccion->id;
                $cambio = true;
            }
        }

        if($cambio && $this->exists)
            $this->save();

        return $cambio;
    }
}
