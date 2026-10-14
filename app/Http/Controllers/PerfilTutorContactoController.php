<?php

namespace App\Http\Controllers;

use App\Models\{Ciudad, ContactoEmergencia, Direccion, Paciente, PacienteContactoEmergencia, Region};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class PerfilTutorContactoController extends Controller
{
    private function tutor(Request $request): Paciente
    {
        $tutor=Paciente::where('id_usuario',Auth::id())->firstOrFail();
        foreach(['id_paciente','id_paciente_contacto'] as $campo)abort_if($request->filled($campo) && (int)$request->input($campo)!==(int)$tutor->id,403);
        return $tutor;
    }

    public function buscar(Request $request)
    {
        $tutor=$this->tutor($request);
        if($tutor->rut===$request->rut_contacto)return 'identicos';
        return $tutor->ContactosEmergencia()->where('rut',$request->rut_contacto)->exists()?'existe':'vacio';
    }

    public function listar(Request $request)
    {
        $tutor=$this->tutor($request);
        return response()->json(['id_paciente'=>$tutor->id,'registros'=>$tutor->ContactosEmergencia()->get()]);
    }

    public function datos(Request $request)
    {
        $contacto=$this->tutor($request)->ContactosEmergencia()->where('contactos_emergencia.id',$request->id_contacto)->firstOrFail();
        $direccion=Direccion::find($contacto->id_direccion);$ciudad=$direccion?Ciudad::find($direccion->id_ciudad):null;
        $contacto->direccion=$direccion ?? (object)['direccion'=>'','numero_dir'=>''];
        $contacto->ciudad=$ciudad ?? (object)['id'=>0,'id_region'=>0,'nombre'=>''];
        $contacto->region=($ciudad ? Region::find($ciudad->id_region) : null) ?? (object)['id'=>0,'nombre'=>''];
        return response()->json($contacto);
    }

    public function guardar(Request $request)
    {
        $tutor=$this->tutor($request);
        $datos=$request->validate(['id_contacto'=>'sometimes|nullable|integer','rut'=>'required|string|max:20','nombres'=>'required|string|max:255','apellido_uno'=>'required|string|max:255','apellido_dos'=>'nullable|string|max:255','email'=>'nullable|email|max:255','telefono'=>'nullable|string|max:30','fecha'=>'nullable|date|before_or_equal:today','id_ciudad'=>'nullable|integer|exists:ciudades,id','direccion'=>'nullable|string|max:255','numero_dir'=>'nullable|string|max:30','parentezco'=>'nullable|string|max:255','prioridad'=>'nullable|integer|min:1|max:9']);
        $contacto=$request->filled('id_contacto')?$tutor->ContactosEmergencia()->where('contactos_emergencia.id',$request->id_contacto)->firstOrFail():($tutor->ContactosEmergencia()->where('rut',$datos['rut'])->first() ?? new ContactoEmergencia());
        DB::transaction(function()use(&$contacto,$datos,$tutor){
            $direccion=Direccion::find($contacto->id_direccion) ?? new Direccion();
            if($contacto->exists && PacienteContactoEmergencia::where('id_contacto',$contacto->id)->where('id_paciente','<>',$tutor->id)->exists()){
                $viejo=$contacto->id;$contacto=$contacto->replicate();
                $tutor->ContactosEmergencia()->detach($viejo);
                if($direccion->exists)$direccion=$direccion->replicate();
            }
            if(!empty($datos['id_ciudad'])||!empty($datos['direccion'])){
                $direccion->id_ciudad=$datos['id_ciudad']??null;$direccion->direccion=$datos['direccion']??null;$direccion->numero_dir=$datos['numero_dir']??null;$direccion->saveOrFail();$contacto->id_direccion=$direccion->id;
            }
            foreach(['rut','apellido_uno','apellido_dos','email','telefono','parentezco','prioridad'] as $campo)$contacto->{$campo}=$datos[$campo]??null;
            $contacto->nombre=$datos['nombres'];
            if(array_key_exists('fecha',$datos))$contacto->fecha_nac=$datos['fecha'];
            $contacto->saveOrFail();
            $tutor->ContactosEmergencia()->syncWithoutDetaching([$contacto->id=>['relacion'=>$datos['parentezco']??null]]);
        });
        // Compatible con el formulario existente, que interpreta el JSON explícitamente.
        return json_encode($contacto);
    }

    public function eliminar(Request $request)
    {
        $tutor=$this->tutor($request);
        $contacto=$tutor->ContactosEmergencia()->where('contactos_emergencia.id',$request->id_contacto)->firstOrFail();
        $tutor->ContactosEmergencia()->detach($contacto->id);
        return 'ok';
    }
}
