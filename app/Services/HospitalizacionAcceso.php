<?php

namespace App\Services;

use App\Models\{Instituciones,User};
use Illuminate\Support\Facades\{DB,Schema};

class HospitalizacionAcceso
{
    private function institucionLugar(int $lugar): ?int
    {
        foreach(['sucursal','profesionales_lugares_atencion','asistentes_lugar_atencion','administrativos_lugar_atencion'] as $tabla) {
            if(!Schema::hasTable($tabla) || !Schema::hasColumns($tabla,['id_lugar_atencion','id_institucion'])) continue;
            $id=DB::table($tabla)->where('id_lugar_atencion',$lugar)->where('id_institucion','>',0)->value('id_institucion');
            if($id) return (int)$id;
        }
        return null;
    }

    public function profesionales(int $centro)
    {
        if(!Schema::hasTable('profesionales_lugares_atencion') || !Schema::hasTable('profesionales')) return collect();
        return DB::table('profesionales_lugares_atencion as l')->join('profesionales as p','p.id','=','l.id_profesional')->where('l.estado',1)->select('p.id_usuario','l.id_institucion','l.id_lugar_atencion')->get()
            ->filter(fn($l)=>(int)($l->id_institucion ?: $this->institucionLugar((int)$l->id_lugar_atencion)) === $centro)->pluck('id_usuario')->unique();
    }
    public function centros(User $user)
    {
        $ids=collect();
        if(Schema::hasTable('profesionales_lugares_atencion') && Schema::hasTable('profesionales')) {
            $vinculos=DB::table('profesionales_lugares_atencion as l')->join('profesionales as p','p.id','=','l.id_profesional')->where('p.id_usuario',$user->id)->where('l.estado',1)->select('l.id_institucion','l.id_lugar_atencion')->get();
            $ids=$ids->merge($vinculos->map(fn($l)=>$l->id_institucion ?: $this->institucionLugar((int)$l->id_lugar_atencion))->filter());
        }
        if(Schema::hasTable('vet_equipo_hospitalizacion')) $ids=$ids->merge(DB::table('vet_equipo_hospitalizacion')->where('id_usuario',$user->id)->where('activo',1)->pluck('id_institucion'));
        return Instituciones::whereIn('id',$ids->unique())->orderBy('nombre')->get()->filter(function($centro)use($user){
            if(!Schema::hasTable('vet_equipo_hospitalizacion')) return true;
            $miembro=DB::table('vet_equipo_hospitalizacion')->where('id_institucion',$centro->id)->where('id_usuario',$user->id)->first();
            return !$miembro || ($miembro->activo && $this->candidatos($centro->id)->contains('id',$user->id));
        })->values();
    }

    public function tieneAcceso(User $user): bool
    {
        return Schema::hasTable('instituciones') && ($this->centros($user)->isNotEmpty() || $this->administrados($user)->isNotEmpty());
    }

    public function administrados(User $user)
    {
        $admins=Schema::hasTable('admin_inst_serv') ? DB::table('admin_inst_serv')->where('id_admin',$user->id)->pluck('id') : collect();
        $contratos=Schema::hasTable('contrato_dependiente') ? DB::table('contrato_dependiente')->whereIn('id_empleado',$admins)->where('tipo_empleado','like','%ADMINISTRADOR%')->where('estado',2)->pluck('id_institucion') : collect();
        return Instituciones::where(function($q)use($user,$admins,$contratos){$q->where('id_usuario',$user->id)->orWhereIn('id_responsable',$admins)->orWhereIn('id',$contratos);})->orderBy('nombre')->get();
    }

    public function perfil(User $user,int $centro): string
    {
        abort_unless($this->centros($user)->contains('id',$centro),403,'No pertenece al equipo hospitalario de este centro.');
        $miembro=Schema::hasTable('vet_equipo_hospitalizacion') ? DB::table('vet_equipo_hospitalizacion')->where('id_institucion',$centro)->where('id_usuario',$user->id)->first() : null;
        // Una baja explícita prevalece sobre la vinculación profesional general.
        abort_if($miembro && !$miembro->activo,403,'El acceso al servicio fue desactivado.');
        return $miembro->perfil ?? 'profesional';
    }

    public function candidatos(int $centro)
    {
        $ids=$this->profesionales($centro);
        if(Schema::hasTable('asistentes')) {
            $ids=$ids->merge(DB::table('asistentes as a')->join('contrato_dependiente as c','c.id_empleado','=','a.id')->where('c.id_institucion',$centro)->where('c.estado',2)->where(function($q){$q->where('c.tipo_empleado','like','%ASISTENTE%')->orWhere('c.tipo_empleado','like','%ENFERMER%');})->pluck('a.id_usuario'));
        }
        return User::whereIn('id',$ids->filter()->unique())->orderBy('name')->get(['id','name']);
    }
}
