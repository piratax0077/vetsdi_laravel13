<?php

namespace App\Services;

use App\Models\{Asistente, Ciudad, Direccion, Paciente, Profesional, Region, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\{Rule, ValidationException};

class PerfilTutor
{
    public function guardar(Request $request, string $seccion='todo'): array
    {
        $tutor=Paciente::where('id_usuario',Auth::id())->firstOrFail();
        abort_if($request->filled('id_paciente') && (int)$request->id_paciente !== (int)$tutor->id,403);
        $reglas=[];
        if(in_array($seccion,['todo','personales']))$reglas += [
            'perfil_nombre'=>'sometimes|required|string|max:255','perfil_apellido_uno'=>'sometimes|required|string|max:255','perfil_apellido_dos'=>'sometimes|nullable|string|max:255',
            'perfil_sexo'=>'sometimes|nullable|in:M,F,O,N','perfil_nac'=>'sometimes|nullable|date_format:Y-m-d|before_or_equal:today',
        ];
        if(in_array($seccion,['todo','contacto']))$reglas += [
            'perfil_email'=>['sometimes','required','email','max:255',Rule::unique('users','email')->ignore(Auth::id()),Rule::unique('pacientes','email')->ignore($tutor->id)],
            'perfil_fono'=>'sometimes|nullable|string|max:30',
        ];
        if ($request->exists('perfil_email')) $request->merge(['perfil_email'=>mb_strtolower(trim((string)$request->perfil_email))]);
        $datos=$request->validate($reglas);
        $direccionDatos=null;
        if(in_array($seccion,['todo','direccion']) && ($request->exists('perfil_dire') || $request->exists('perfil_ciudad') || $request->exists('perfil_numero_dir'))) {
            $direccionDatos=$request->validate(['perfil_dire'=>'required|string|max:255','perfil_region'=>'required|integer|exists:regiones,id','perfil_ciudad'=>'required|integer|exists:ciudades,id','perfil_numero_dir'=>'sometimes|nullable|string|max:30']);
            if((int)Ciudad::findOrFail($direccionDatos['perfil_ciudad'])->id_region !== (int)$direccionDatos['perfil_region'])throw ValidationException::withMessages(['perfil_ciudad'=>'La comuna no pertenece a la región seleccionada.']);
        }
        DB::transaction(function()use($request,$tutor,$datos,$direccionDatos){
            foreach(['perfil_nombre'=>'nombres','perfil_apellido_uno'=>'apellido_uno','perfil_apellido_dos'=>'apellido_dos','perfil_sexo'=>'sexo','perfil_nac'=>'fecha_nac','perfil_email'=>'email','perfil_fono'=>'telefono_uno'] as $campo=>$atributo)if(array_key_exists($campo,$datos))$tutor->{$atributo}=$datos[$campo];
            if($direccionDatos){
                $direccion=DireccionesPerfil::editable($tutor);
                $direccion->direccion=$direccionDatos['perfil_dire'];$direccion->id_ciudad=$direccionDatos['perfil_ciudad'];
                if(array_key_exists('perfil_numero_dir',$direccionDatos))$direccion->numero_dir=$direccionDatos['perfil_numero_dir'];
                $direccion->saveOrFail();$tutor->id_direccion=$direccion->id;
            }
            $tutor->saveOrFail();
            $usuario=User::findOrFail(Auth::id());
            if(array_key_exists('perfil_email',$datos))$usuario->email=$datos['perfil_email'];
            if(array_intersect(['perfil_nombre','perfil_apellido_uno','perfil_apellido_dos'],array_keys($datos)))$usuario->name=trim(implode(' ',array_filter([$tutor->nombres,$tutor->apellido_uno,$tutor->apellido_dos])));
            if(array_intersect(['perfil_nombre','perfil_apellido_uno','perfil_apellido_dos'],array_keys($datos))) {
                foreach(['nombres','apellido_uno','apellido_dos'] as $campo) $usuario->{$campo}=$tutor->{$campo};
            }
            if(array_key_exists('perfil_fono',$datos))$usuario->telefono=$tutor->telefono_uno;
            $usuario->saveOrFail();
        });
        $direccion=Direccion::find($tutor->id_direccion);
        $ciudad=$direccion ? Ciudad::find($direccion->id_ciudad) : null;
        return ['success'=>true,'estado'=>1,'message'=>'Perfil del tutor guardado.','paciente'=>$tutor,'direccion'=>$direccion,'ciudad'=>$ciudad,'region'=>$ciudad ? Region::find($ciudad->id_region) : null];
    }
}
