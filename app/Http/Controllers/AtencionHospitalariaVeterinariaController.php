<?php
namespace App\Http\Controllers;

use App\Models\Instituciones;
use App\Services\OperacionVeterinaria;
use App\Support\UserCenterContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Schema};

class AtencionHospitalariaVeterinariaController extends Controller
{
    private function centro(Request $r): Instituciones
    {
        $r->validate(['id_institucion'=>'nullable|integer|min:1']);
        $contextos=UserCenterContext::forProfessional(Auth::user(),$r);
        $contexto=$r->filled('id_institucion') ? $contextos['contexts']->firstWhere('id_institucion',(int)$r->id_institucion) : $contextos['active'];
        abort_unless($contexto && $contexto['id_institucion'],403,'Debe estar vinculado como profesional a este establecimiento.');
        abort_unless(app(\App\Services\HospitalizacionAcceso::class)->perfil(Auth::user(),(int)$contexto['id_institucion']) === 'profesional',403,'El perfil de enfermería no puede realizar acciones del médico veterinario.');
        $r->session()->put('professional_center_context',$contexto['key']);
        return Instituciones::findOrFail($contexto['id_institucion']);
    }

    public function index(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$this->centro($r);
        $r->validate(['internacion'=>'nullable|integer|min:1','pestana_hospital'=>'nullable|in:clinica,enfermeria,examenes']);
        $centros=UserCenterContext::forProfessional(Auth::user(),$r)['contexts']->unique('id_institucion');
        $pacientes=DB::table('vet_internaciones')->where('id_institucion',$institucion->id)->whereIn('id',DB::table('vet_internacion_profesionales')->where('id_usuario',Auth::id())->select('internacion_id'))->orderByRaw("CASE WHEN estado = 'hospitalizada' THEN 0 ELSE 1 END")->orderByDesc('ingreso')->paginate(20)->withQueryString();
        $internacion=$r->filled('internacion') ? $s->registro('vet_internaciones',$institucion->id,(int)$r->internacion) : null;
        if ($internacion) $this->asignado($internacion->id);
        $notas=$internacion ? DB::table('vet_evoluciones as e')->leftJoin('users as u','u.id','=','e.id_usuario')->where('e.internacion_id',$internacion->id)->select('e.*','u.name as autor')->orderByDesc('e.fecha')->orderByDesc('e.id') : null;
        $evoluciones=$notas ? (clone $notas)->where('e.tipo_registro','!=','enfermeria')->paginate(20,['*'],'notas')->withQueryString()->appends(['pestana_hospital'=>'clinica']) : null;
        $evolucionesEnfermeria=$notas ? (clone $notas)->where('e.tipo_registro','enfermeria')->paginate(20,['*'],'notas_enfermeria')->withQueryString()->appends(['pestana_hospital'=>'enfermeria']) : null;
        $examenesHospitalarios=$internacion && Schema::hasTable('documentos_laboratorio_mascota')
            ? DB::table('documentos_laboratorio_mascota')->where('id_mascota',$internacion->id_mascota)->orderByDesc('created_at')->orderByDesc('id')->paginate(20,['*'],'examenes')->withQueryString()->appends(['pestana_hospital'=>'examenes']) : null;
        $mascotas=$s->mascotas($institucion)->pluck('nombre','id');
        $plazas=DB::table('vet_plazas')->where('id_institucion',$institucion->id)->where('activa',1)->whereNotIn('id',DB::table('vet_internaciones')->where('id_institucion',$institucion->id)->where('estado','hospitalizada')->select('plaza_id'))->orderBy('codigo')->get();
        $hospitalizacionHabilitada=$s->configuracion($institucion->id)->hospitalizacion_habilitada;
        $hayPlazasConfiguradas=DB::table('vet_plazas')->where('id_institucion',$institucion->id)->exists();
        $puedeConfigurarPlazas=app(\App\Services\HospitalizacionAcceso::class)->administrados(Auth::user())->contains('id',$institucion->id);
        $controles=$internacion ? DB::table('vet_controles_hospitalarios')->where('internacion_id',$internacion->id)->orderBy('fecha')->get() : collect();
        return view('app.veterinaria.hospitalizacion.profesional',compact('institucion','pacientes','internacion','evoluciones','evolucionesEnfermeria','examenesHospitalarios','mascotas','controles','plazas','hospitalizacionHabilitada','hayPlazasConfiguradas','puedeConfigurarPlazas','centros'));

    }

    public function guardar(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$this->centro($r);
        $r->validate(['tipo_registro'=>'required|in:veterinaria,enfermeria,cirugia,control,evolucion','internacion_id'=>'required|integer|min:1']);
        $s->registro('vet_internaciones',$institucion->id,(int)$r->internacion_id);
        DB::transaction(function() use($r,$s,$institucion){
            Instituciones::whereKey($institucion->id)->lockForUpdate()->firstOrFail();
            $this->asignado((int)$r->internacion_id);
            $s->ejecutar($r,$institucion,'evolucion');
        });
        return redirect()->route('vet.hospital.profesional',['id_institucion'=>$institucion->id,'internacion'=>$r->internacion_id])->with('success','Atención hospitalaria registrada.');
    }

    private function asignado(int $id): void
    {
        abort_unless(DB::table('vet_internacion_profesionales')->where('internacion_id',$id)->where('id_usuario',Auth::id())->exists(),403,'Este paciente no está asignado al profesional.');
    }

    public function examen(Request $r,OperacionVeterinaria $s,int $documento)
    {
        $institucion=$this->centro($r);
        $r->validate(['internacion'=>'required|integer|min:1']);
        $internacion=$s->registro('vet_internaciones',$institucion->id,(int)$r->internacion);
        $this->asignado($internacion->id);
        $archivo=\App\Models\DocumentoLaboratorioMascota::where('id_mascota',$internacion->id_mascota)->findOrFail($documento);
        return app(DocumentoLaboratorioMascotaController::class)->ver($archivo);
    }

    public function asignaciones(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$s->institucion($r);
        $pacientes=DB::table('vet_internaciones')->where('id_institucion',$institucion->id)->where('estado','hospitalizada')->orderByDesc('ingreso')->paginate(20)->withQueryString();
        $profesionales=$this->profesionales($institucion->id)->select('p.id_usuario','p.nombre','p.apellido')->distinct()->get();
        $asignaciones=DB::table('vet_internacion_profesionales')->whereIn('internacion_id',$pacientes->pluck('id'))->get()->groupBy('internacion_id');
        $mascotas=$s->mascotas($institucion)->pluck('nombre','id');
        return view('app.veterinaria.hospitalizacion.asignaciones',compact('institucion','pacientes','profesionales','asignaciones','mascotas'));
    }

    private function profesionales(int $id)
    {
        return DB::table('profesionales as p')->join('profesionales_lugares_atencion as l','l.id_profesional','=','p.id')->where('l.id_institucion',$id)->where('l.estado',1);
    }

    public function asignar(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$s->institucion($r);
        $r->validate(['internacion_id'=>'required|integer|min:1','profesionales'=>'sometimes|array','profesionales.*'=>'integer|min:1|distinct']);
        $ids=array_map('intval',$r->input('profesionales',[]));
        $permitidos=$this->profesionales($institucion->id)->pluck('p.id_usuario')->map(fn($id)=>(int)$id)->all();
        abort_if(array_diff($ids,$permitidos),403,'Profesional ajeno al establecimiento.');
        DB::transaction(function() use($r,$s,$institucion,$ids){
            Instituciones::whereKey($institucion->id)->lockForUpdate()->firstOrFail();
            $paciente=$s->registro('vet_internaciones',$institucion->id,(int)$r->internacion_id);
            abort_unless($paciente->estado === 'hospitalizada',422,'Solo se asignan hospitalizaciones activas.');
            $antes=DB::table('vet_internacion_profesionales')->where('internacion_id',$paciente->id)->get();
            DB::table('vet_internacion_profesionales')->where('internacion_id',$paciente->id)->whereNotIn('id_usuario',$ids)->delete();
            foreach($ids as $id) DB::table('vet_internacion_profesionales')->insertOrIgnore(['internacion_id'=>$paciente->id,'id_usuario'=>$id,'asignado_por'=>Auth::id(),'created_at'=>now(),'updated_at'=>now()]);
            DB::table('vet_operacion_auditoria')->insert(['id_institucion'=>$institucion->id,'id_usuario'=>Auth::id(),'tabla'=>'vet_internacion_profesionales','registro_id'=>$paciente->id,'antes'=>json_encode($antes),'despues'=>json_encode($ids),'created_at'=>now()]);
        });
        return redirect()->route('vet.hospital.asignaciones',['id_institucion'=>$institucion->id])->with('success','Asignación actualizada.');
    }

    public function ingreso(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$this->centro($r);
        $id=DB::transaction(function() use($r,$s,$institucion){
            Instituciones::whereKey($institucion->id)->lockForUpdate()->firstOrFail();
            $datos=$r->duplicate($r->only(['id_mascota','plaza_id','ingreso','motivo','indicaciones'])+['responsable'=>Auth::user()->name,'estado'=>'hospitalizada']);
            $id=$s->ejecutar($datos,$institucion,'internacion');
            DB::table('vet_internacion_profesionales')->insert(['internacion_id'=>$id,'id_usuario'=>Auth::id(),'asignado_por'=>Auth::id(),'created_at'=>now(),'updated_at'=>now()]);
            DB::table('vet_operacion_auditoria')->insert(['id_institucion'=>$institucion->id,'id_usuario'=>Auth::id(),'tabla'=>'vet_internacion_profesionales','registro_id'=>$id,'antes'=>null,'despues'=>json_encode(['id_usuario'=>Auth::id()]),'created_at'=>now()]);
            return $id;
        });
        return redirect()->route('vet.hospital.profesional',['id_institucion'=>$institucion->id,'internacion'=>$id])->with('success','Ingreso registrado y paciente asignado.');
    }

    public function alta(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$this->centro($r);
        $r->validate(['internacion_id'=>'required|integer|min:1','egreso'=>'required|date|before_or_equal:now','observaciones_alta'=>'required|string|max:10000','indicaciones'=>'required|string|max:20000']);
        DB::transaction(function() use($r,$s,$institucion){
            Instituciones::whereKey($institucion->id)->lockForUpdate()->firstOrFail();
            $internacion=$s->registro('vet_internaciones',$institucion->id,(int)$r->internacion_id);
            $this->asignado($internacion->id);
            $datos=$r->duplicate(array_merge((array)$internacion,$r->only(['egreso','observaciones_alta','indicaciones']),['id'=>$internacion->id,'estado'=>'alta']));
            $s->ejecutar($datos,$institucion,'internacion');
        });
        return redirect()->route('vet.hospital.profesional',['id_institucion'=>$institucion->id,'internacion'=>$r->internacion_id])->with('success','Alta registrada. El carné está disponible para imprimir.');
    }

    public function control(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$this->centro($r);
        $r->validate(['internacion_id'=>'required|integer|min:1','control_id'=>'nullable|integer|min:1','fecha'=>'required_without:control_id|date|after:now','motivo'=>'required_without:control_id|string|max:500','estado'=>'required_with:control_id|in:realizado,cancelado,no_asiste','resultado'=>'nullable|required_if:estado,realizado|string|max:10000']);
        DB::transaction(function() use($r,$s,$institucion){
            Instituciones::whereKey($institucion->id)->lockForUpdate()->firstOrFail();
            $internacion=$s->registro('vet_internaciones',$institucion->id,(int)$r->internacion_id);
            $this->asignado($internacion->id);
            abort_unless(in_array($internacion->estado,['hospitalizada','alta']),422,'No se programan controles para este egreso.');
            $antes=null;
            if($r->filled('control_id')) {
                $antes=DB::table('vet_controles_hospitalarios')->where('internacion_id',$internacion->id)->where('id',$r->control_id)->first();
                abort_unless($antes,404);abort_unless($antes->estado === 'programado',422,'El control ya está cerrado.');
                abort_if(in_array($r->estado,['realizado','no_asiste']) && \Carbon\Carbon::parse($antes->fecha)->isFuture(),422,'El control todavía no ocurre.');
                $id=$antes->id;
                DB::table('vet_controles_hospitalarios')->where('id',$id)->update(['estado'=>$r->estado,'resultado'=>$r->resultado,'id_usuario'=>Auth::id(),'updated_at'=>now()]);
            } else {
                $fecha=\Carbon\Carbon::parse($r->fecha)->format('Y-m-d H:i:s');
                abort_if(DB::table('vet_controles_hospitalarios')->where('internacion_id',$internacion->id)->where('fecha',$fecha)->where('estado','programado')->exists(),422,'Ya existe un control para esa fecha.');
                $id=DB::table('vet_controles_hospitalarios')->insertGetId(['internacion_id'=>$internacion->id,'fecha'=>$fecha,'motivo'=>$r->motivo,'id_usuario'=>Auth::id(),'created_at'=>now(),'updated_at'=>now()]);
            }
            DB::table('vet_operacion_auditoria')->insert(['id_institucion'=>$institucion->id,'id_usuario'=>Auth::id(),'tabla'=>'vet_controles_hospitalarios','registro_id'=>$id,'antes'=>$antes ? json_encode($antes) : null,'despues'=>json_encode(DB::table('vet_controles_hospitalarios')->find($id)),'created_at'=>now()]);
        });
        return redirect()->route('vet.hospital.profesional',['id_institucion'=>$institucion->id,'internacion'=>$r->internacion_id])->with('success','Control registrado.');
    }

    public function carne(Request $r, OperacionVeterinaria $s)
    {
        $institucion=$this->centro($r);
        $r->validate(['internacion'=>'required|integer|min:1']);
        $internacion=$s->registro('vet_internaciones',$institucion->id,(int)$r->internacion);
        $this->asignado($internacion->id);
        abort_unless($internacion->estado === 'alta',404,'El carné estará disponible cuando se registre el alta.');
        $mascota=DB::table('mascotas')->find($internacion->id_mascota);
        $controles=DB::table('vet_controles_hospitalarios')->where('internacion_id',$internacion->id)->where('estado','programado')->orderBy('fecha')->get();
        $cirugias=DB::table('vet_evoluciones')->where('internacion_id',$internacion->id)->where('tipo_registro','cirugia')->orderBy('fecha')->get();
        $autor=DB::table('users')->find($internacion->id_usuario);
        return view('app.veterinaria.hospitalizacion.carne',compact('institucion','internacion','mascota','controles','cirugias','autor'));
    }
}
