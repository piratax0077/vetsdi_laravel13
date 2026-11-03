<?php

namespace App\Http\Controllers;

use App\Services\{HospitalizacionAcceso,OperacionVeterinaria};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB};

class ServicioHospitalizacionVeterinariaController extends Controller
{
    private function contexto(Request $r,HospitalizacionAcceso $acceso): array
    {
        $r->validate(['id_institucion'=>'nullable|integer|min:1','internacion'=>'nullable|integer|min:1']);
        $centros=$acceso->centros(Auth::user());
        $institucion=$r->filled('id_institucion') ? $centros->firstWhere('id',(int)$r->id_institucion) : $centros->first();
        abort_unless($institucion,403,'No tiene un centro asignado al servicio de hospitalización.');
        $perfil=$acceso->perfil(Auth::user(),$institucion->id);
        return compact('centros','institucion','perfil');
    }

    public function index(Request $r,HospitalizacionAcceso $acceso,OperacionVeterinaria $s)
    {
        $r->validate(['id_institucion'=>'nullable|integer|min:1']);
        $administrados=$acceso->administrados(Auth::user());
        if ($acceso->centros(Auth::user())->isEmpty() && $administrados->isNotEmpty()) {
            $centro=$r->filled('id_institucion') ? $administrados->firstWhere('id',(int)$r->id_institucion) : $administrados->first();
            abort_unless($centro,403);
            return redirect()->route('vet.areas.index',['area'=>'hospitalizacion','id_institucion'=>$centro->id]);
        }
        $datos=$this->contexto($r,$acceso);
        if($datos['perfil']==='profesional') return redirect()->route('vet.hospital.profesional',array_filter(['id_institucion'=>$datos['institucion']->id,'internacion'=>$r->input('internacion')]));
        $institucion=$datos['institucion'];
        $pacientes=DB::table('vet_internaciones')->where('id_institucion',$institucion->id)->where('estado','hospitalizada')->orderBy('ingreso')->paginate(20)->withQueryString();
        $internacion=$r->filled('internacion') ? $s->registro('vet_internaciones',$institucion->id,(int)$r->internacion) : null;
        $mascotas=$s->mascotas($institucion)->get()->keyBy('id');
        $evoluciones=$internacion ? DB::table('vet_evoluciones as e')->leftJoin('users as u','u.id','=','e.id_usuario')->where('e.internacion_id',$internacion->id)->select('e.*','u.name as autor')->orderByDesc('e.fecha')->paginate(20,['*'],'notas')->withQueryString() : null;
        return view('app.veterinaria.hospitalizacion.enfermeria',$datos+compact('pacientes','internacion','mascotas','evoluciones'));
    }

    public function guardar(Request $r,HospitalizacionAcceso $acceso,OperacionVeterinaria $s)
    {
        $datos=$this->contexto($r,$acceso);
        abort_unless($datos['perfil']==='enfermeria',403);
        $r->validate(['internacion_id'=>'required|integer|min:1','frecuencia_cardiaca'=>'nullable|integer|min:1|max:600','frecuencia_respiratoria'=>'nullable|integer|min:1|max:300','dolor'=>'nullable|integer|min:0|max:10']);
        $r->merge(['tipo_registro'=>'enfermeria','procedimiento'=>null]);
        DB::transaction(function()use($r,$datos,$s){
            \App\Models\Instituciones::whereKey($datos['institucion']->id)->lockForUpdate()->firstOrFail();
            $s->ejecutar($r,$datos['institucion'],'evolucion');
        });
        return redirect()->route('vet.hospital.servicio',['id_institucion'=>$datos['institucion']->id,'internacion'=>$r->internacion_id])->with('success','Control de enfermería registrado.');
    }

    public function equipo(Request $r,OperacionVeterinaria $s,HospitalizacionAcceso $acceso)
    {
        $institucion=$s->institucion($r);
        $candidatos=$acceso->candidatos($institucion->id);
        $equipo=DB::table('vet_equipo_hospitalizacion as e')->join('users as u','u.id','=','e.id_usuario')->where('e.id_institucion',$institucion->id)->select('e.*','u.name')->orderBy('u.name')->get();
        return view('app.veterinaria.hospitalizacion.equipo',compact('institucion','candidatos','equipo'));
    }

    public function guardarEquipo(Request $r,OperacionVeterinaria $s,HospitalizacionAcceso $acceso)
    {
        $institucion=$s->institucion($r);
        $d=$r->validate(['id_usuario'=>'required|integer|min:1','perfil'=>'required|in:profesional,enfermeria','activo'=>'required|boolean']);
        abort_unless($acceso->candidatos($institucion->id)->contains('id',(int)$d['id_usuario']),403,'El integrante debe estar vinculado al centro.');
        if($d['perfil']==='profesional') abort_unless($acceso->profesionales($institucion->id)->contains((int)$d['id_usuario']),422,'Seleccione un profesional veterinario vinculado al centro.');
        DB::transaction(function()use($d,$institucion){
            $scope=['id_institucion'=>$institucion->id,'id_usuario'=>$d['id_usuario']];
            $antes=DB::table('vet_equipo_hospitalizacion')->where($scope)->first();
            DB::table('vet_equipo_hospitalizacion')->updateOrInsert($scope,['perfil'=>$d['perfil'],'activo'=>$d['activo'],'created_at'=>$antes->created_at ?? now(),'updated_at'=>now()]);
            DB::table('vet_operacion_auditoria')->insert(['id_institucion'=>$institucion->id,'id_usuario'=>Auth::id(),'tabla'=>'vet_equipo_hospitalizacion','registro_id'=>$d['id_usuario'],'antes'=>json_encode($antes),'despues'=>json_encode($d),'created_at'=>now()]);
        });
        return back()->with('success','Equipo hospitalario actualizado.');
    }
}
