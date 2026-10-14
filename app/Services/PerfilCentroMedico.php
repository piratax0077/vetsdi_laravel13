<?php

namespace App\Services;

use App\Models\{AdminInstServ, Ciudad, ContratoDependiente, Direccion, Instituciones, Profesional, Servicios, User};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash};
use Illuminate\Validation\{Rule, ValidationException};

class PerfilCentroMedico
{
    public function permitidos(string $tipo = 'institucion'): Builder
    {
        $modelo=$tipo === 'servicio' ? Servicios::class : Instituciones::class;
        $responsables=AdminInstServ::where('id_admin',Auth::id())->pluck('id');
        $contratos=$tipo === 'servicio' ? collect() : ContratoDependiente::whereIn('id_empleado',$responsables)->where('tipo_empleado','like','%ADMINISTRADOR%')->where('estado',2)->pluck('id_institucion');
        return $modelo::where(fn($q)=>$q->where('id_usuario',Auth::id())->orWhereIn('id_responsable',$responsables)->orWhereIn('id',$contratos));
    }

    public function centro(Request $request): Model
    {
        $request->validate(['id_institucion'=>'sometimes|nullable|integer','tipo_institucion'=>'sometimes|nullable|in:institucion,servicio']);
        $query=$this->permitidos($request->input('tipo_institucion') ?: 'institucion');
        if($request->filled('id_institucion')) {
            $centro=$query->find($request->id_institucion);
            abort_unless($centro,403,'No tiene acceso a este centro.');
            return $centro;
        }
        return $query->orderBy('id')->firstOrFail();
    }

    public function guardarCentro(Request $request): array
    {
        $centro=$this->centro($request);
        $datos=$request->validate([
            'nombre'=>'sometimes|required|string|max:255','razon_social'=>'sometimes|nullable|string|max:255',
            'rut'=>['sometimes','required','string','max:20',Rule::unique($centro->getTable(),'rut')->ignore($centro->id)],
            'certificado_supersalud'=>'sometimes|nullable|string|max:255','telefono'=>'sometimes|nullable|string|max:30',
            'whatsapp'=>'sometimes|nullable|string|max:30','email'=>'sometimes|nullable|email|max:255',
            'sitio_web'=>'sometimes|nullable|url|max:255','horario_atencion'=>'sometimes|nullable|string|max:2000','sucursales'=>'sometimes|boolean',
        ]);
        DB::transaction(function()use($request,$centro,$datos){
            foreach($datos as $campo=>$valor)$centro->{$campo}=$valor;
            $this->direccion($centro,$request);
            $centro->saveOrFail();
        });
        return ['estado'=>1,'success'=>true,'msj'=>'Datos del centro guardados.','institucion'=>$centro];
    }

    private function direccion(Model $perfil, Request $request): void
    {
        if(!$request->exists('ciudad') && !$request->exists('direccion') && !$request->exists('numero_dir'))return;
        $datos=$request->validate(['ciudad'=>'required|integer|exists:ciudades,id','region'=>'sometimes|nullable|integer|exists:regiones,id','direccion'=>'required|string|max:255','numero_dir'=>'nullable|string|max:30']);
        $ciudad=Ciudad::findOrFail($datos['ciudad']);
        if(!empty($datos['region']) && (int)$ciudad->id_region !== (int)$datos['region'])throw ValidationException::withMessages(['ciudad'=>'La ciudad no pertenece a la región seleccionada.']);
        $direccion=Direccion::find($perfil->id_direccion) ?? new Direccion();
        $direccion->id_ciudad=$ciudad->id;
        $direccion->direccion=$datos['direccion'];
        $direccion->numero_dir=$datos['numero_dir'] ?? null;
        $direccion->saveOrFail();
        $perfil->id_direccion=$direccion->id;
    }

    public function guardarResponsable(Request $request): array
    {
        $centro=$this->centro($request);
        $request->validate(['id_responsable'=>'sometimes|nullable|integer']);
        $responsable=$request->filled('id_responsable') ? AdminInstServ::findOrFail($request->id_responsable) : ($centro->id_responsable ? AdminInstServ::findOrFail($centro->id_responsable) : AdminInstServ::where('id_admin',Auth::id())->first());
        if($responsable) abort_unless((int)$responsable->id === (int)$centro->id_responsable || (int)$responsable->id_admin === (int)Auth::id(),403);
        else {
            $request->validate(['nombres'=>'required|string|max:255','apellido_uno'=>'required|string|max:255']);
            $responsable=new AdminInstServ();$responsable->id_admin=Auth::id();
        }
        DB::transaction(function()use($responsable,$centro,$request){
            $this->persona($responsable,$request,false);
            if(!$centro->id_responsable){$centro->id_responsable=$responsable->id;$centro->saveOrFail();}
        });
        return ['estado'=>1,'success'=>true,'msj'=>'Datos del responsable guardados.','responsable'=>$responsable];
    }

    public function guardarResponsableMedico(Request $request): array
    {
        $centro=$this->centro($request);
        $request->validate(['id_responsable'=>'required|integer']);
        $profesional=Profesional::findOrFail($request->id_responsable);
        $usuarios=collect(['id_director_medico','id_subdirector_medico','id_director_gestion_cuidado','id_director_comercial','id_director_tecnico'])->map(fn($campo)=>$centro->{$campo})->filter()->map(fn($id)=>(int)$id);
        $vinculado=$usuarios->contains((int)$profesional->id_usuario) || DB::table('areas_cm')->where('id_institucion',$centro->id)->where('id_responsable',$profesional->id)->exists();
        abort_unless($vinculado,403,'El profesional no es responsable de este centro.');
        DB::transaction(fn()=>$this->persona($profesional,$request,true));
        return ['estado'=>1,'success'=>true,'mensaje'=>'Datos médicos guardados.','profesional'=>$profesional];
    }

    private function persona(Model $persona, Request $request, bool $profesional): void
    {
        $idUsuario=$profesional ? $persona->id_usuario : $persona->id_admin;
        $datos=$request->validate([
            'rut'=>['sometimes','required','string','max:20',Rule::unique($persona->getTable(),'rut')->ignore($persona->id)],
            'nombres'=>'sometimes|required|string|max:255','apellido_uno'=>'sometimes|required|string|max:255','apellido_dos'=>'sometimes|nullable|string|max:255',
            'sexo'=>'sometimes|nullable|in:M,F,O','fecha_nac'=>'sometimes|nullable|date_format:Y-m-d|before_or_equal:today',
            'telefono_uno'=>'sometimes|nullable|string|max:30','telefono_dos'=>'sometimes|nullable|string|max:30',
            'email'=>['sometimes','required','email','max:255',Rule::unique('users','email')->ignore($idUsuario),Rule::unique($persona->getTable(),'email')->ignore($persona->id)],
        ]);
        foreach($datos as $campo=>$valor){
            if($profesional && $campo==='nombres')$campo='nombre';
            if($profesional && $campo==='fecha_nac')$campo='fecha_nacimiento';
            $persona->{$campo}=$valor;
        }
        $this->direccion($persona,$request);
        $persona->saveOrFail();
        if($idUsuario && ($usuario=User::find($idUsuario))){
            if(array_key_exists('email',$datos))$usuario->email=$datos['email'];
            if(array_intersect(['nombres','apellido_uno','apellido_dos'],array_keys($datos)))$usuario->name=trim(implode(' ',array_filter([$persona->{$profesional?'nombre':'nombres'},$persona->apellido_uno,$persona->apellido_dos])));
            $usuario->saveOrFail();
        }
    }

    public function contrasenaResponsable(Request $request): array
    {
        $centro=$this->centro($request);
        $responsable=AdminInstServ::find($centro->id_responsable);
        $id=(int)$request->input('responsable_id',Auth::id());
        $directores=collect(['id_director_medico','id_subdirector_medico','id_director_gestion_cuidado','id_director_comercial','id_director_tecnico'])->map(fn($campo)=>(int)$centro->{$campo})->filter();
        abort_unless($id === (int)Auth::id() || ($responsable && $id === (int)$responsable->id_admin) || $directores->contains($id),403);
        $datos=$request->validate(['responsable_actual'=>'required|string','responsable_nueva'=>'required|string|min:8|max:128|different:responsable_actual','responsable_validacion'=>'required|same:responsable_nueva']);
        $usuario=User::findOrFail($id);
        if(!Hash::check($datos['responsable_actual'],$usuario->password))throw ValidationException::withMessages(['responsable_actual'=>'La contraseña actual no es correcta.']);
        $usuario->password=Hash::make($datos['responsable_nueva']);$usuario->saveOrFail();
        return ['estado'=>1,'success'=>true,'msj'=>'Contraseña actualizada.'];
    }
}
