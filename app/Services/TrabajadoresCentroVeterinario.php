<?php

namespace App\Services;

use App\Mail\MensajeTrabajador;
use App\Models\{Instituciones,LugarAtencion};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Mail,Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\{Rule,ValidationException};

class TrabajadoresCentroVeterinario
{
    public const TIPOS_CONTRATO=['indefinido'=>'Indefinido','plazo_fijo'=>'Plazo fijo','honorarios'=>'Honorarios','externo'=>'Servicio externo'];

    public function lugares(Instituciones $i)
    {
        $ids=collect(preg_split('/[,;]/',(string)$i->id_lugar_atencion))->filter()->map(fn($id)=>(int)$id);
        if(Schema::hasTable('sucursal'))$ids=$ids->merge(DB::table('sucursal')->where('id_institucion',$i->id)->where('estado',1)->pluck('id_lugar_atencion'));
        return LugarAtencion::whereIn('id',$ids->filter()->unique())->orderBy('nombre')->get(['id','nombre']);
    }

    public function registro(string $tabla,Instituciones $i,int $id): object
    {
        $registro=DB::table($tabla)->where('id_institucion',$i->id)->find($id);abort_unless($registro,404);return $registro;
    }

    public function datos(Instituciones $i,array $scope=[]): array
    {
        $lugares=$this->lugares($i);
        if(!Schema::hasTable('vet_trabajadores'))return ['habilitado'=>false,'trabajadores'=>collect(),'categorias'=>collect(),'contratos'=>collect(),'lugares'=>$lugares];
        $categorias=DB::table('vet_categorias_trabajadores')->where('id_institucion',$i->id)->orderBy('nombre')->get();
        $trabajadores=DB::table('vet_trabajadores')->where('id_institucion',$i->id)
            ->when($scope,fn($q)=>$q->whereIn('id',DB::table('vet_trabajador_lugares')->whereIn('id_lugar_atencion',$scope)->select('trabajador_id')))->orderBy('nombres')->orderBy('apellido_uno')->get();
        $asignaciones=DB::table('vet_trabajador_lugares')->whereIn('trabajador_id',$trabajadores->pluck('id'))->get()->groupBy('trabajador_id');
        $nombres=LugarAtencion::whereIn('id',$asignaciones->flatten()->pluck('id_lugar_atencion'))->pluck('nombre','id');
        foreach($trabajadores as $t){$t->nombre_completo=trim($t->nombres.' '.$t->apellido_uno.' '.$t->apellido_dos);$t->categoria=$categorias->firstWhere('id',$t->categoria_id)?->nombre ?? 'Sin categoría';$t->sucursales=($asignaciones[$t->id] ?? collect())->map(fn($a)=>$nombres[$a->id_lugar_atencion] ?? 'Lugar #'.$a->id_lugar_atencion)->all();}
        $contratos=DB::table('vet_contratos_trabajadores')->where('id_institucion',$i->id)->whereIn('trabajador_id',$trabajadores->pluck('id'))->orderByDesc('fecha_inicio')->get();
        return ['habilitado'=>true,
            'regiones'=>DB::table('regiones')->orderBy('nombre')->get(['id','nombre']),
            'comunas'=>DB::table('ciudades')->orderBy('nombre')->get(['id','id_region','nombre'])
        ]+compact('trabajadores','categorias','contratos','lugares');
    }

    public function detalle(Instituciones $i,int $id): array
    {
        $trabajador=$this->registro('vet_trabajadores',$i,$id);
        $trabajador->lugares=DB::table('vet_trabajador_lugares')->where('trabajador_id',$id)->pluck('id_lugar_atencion')->map(fn($id)=>(int)$id)->all();
        return ['trabajador'=>$trabajador,'contratos'=>DB::table('vet_contratos_trabajadores')->where('id_institucion',$i->id)->where('trabajador_id',$id)->orderByDesc('fecha_inicio')->get(),
            'mensajes'=>DB::table('vet_mensajes_trabajadores')->where('id_institucion',$i->id)->where('trabajador_id',$id)->orderByDesc('id')->limit(25)->get(['id','asunto','mensaje','destinatario','estado','created_at','enviado_en'])];
    }

    private function error(bool $condicion,string $campo,string $texto): void
    {
        if(!$condicion)throw ValidationException::withMessages([$campo=>$texto]);
    }

    private function auditar(Instituciones $i,string $tabla,int $id,$antes,$despues): void
    {
        DB::table('vet_operacion_auditoria')->insert(['id_institucion'=>$i->id,'id_usuario'=>Auth::id(),'tabla'=>$tabla,'registro_id'=>$id,'antes'=>$antes?json_encode($antes):null,'despues'=>json_encode($despues),'created_at'=>now()]);
    }

    public function categoria(Request $r,Instituciones $i): int
    {
        $r->merge(['nombre'=>trim(preg_replace('/\s+/u',' ',(string)$r->nombre))]);
        $d=$r->validate(['id'=>'nullable|integer|min:1','nombre'=>'required|string|max:100','activa'=>'required|boolean']);
        $clave=Str::slug($d['nombre']);$this->error($clave!=='' && !in_array($clave,['veterinario','veterinarios','profesional-veterinario','profesionales-veterinarios','asistente','asistentes'],true),'nombre','Los profesionales veterinarios y asistentes se gestionan en sus propias pestañas.');
        return DB::transaction(function()use($i,$d,$clave){
            Instituciones::whereKey($i->id)->lockForUpdate()->firstOrFail();
            $id=!empty($d['id'])?(int)$d['id']:null;$antes=$id?$this->registro('vet_categorias_trabajadores',$i,$id):null;
            $duplicada=DB::table('vet_categorias_trabajadores')->where('id_institucion',$i->id)->where('clave',$clave)->when($id,fn($q)=>$q->where('id','!=',$id))->exists();
            $this->error(!$duplicada,'nombre','La categoría ya existe en el establecimiento.');
            if($id && !$d['activa'])$this->error(!DB::table('vet_trabajadores')->where('categoria_id',$id)->where('activo',1)->exists(),'activa','Reasigne los trabajadores activos antes de deshabilitar esta categoría.');
            $datos=['nombre'=>$d['nombre'],'clave'=>$clave,'activa'=>$d['activa'],'updated_at'=>now()];
            if($id)DB::table('vet_categorias_trabajadores')->where('id',$id)->update($datos);
            else $id=DB::table('vet_categorias_trabajadores')->insertGetId($datos+['id_institucion'=>$i->id,'created_at'=>now()]);
            $this->auditar($i,'vet_categorias_trabajadores',$id,$antes,DB::table('vet_categorias_trabajadores')->find($id));return $id;
        });
    }

    public function guardar(Request $r,Instituciones $i): int
    {
        foreach (['region_id','comuna_id'] as $campo) if ($r->exists($campo) && !$r->filled($campo)) $r->merge([$campo=>null]);
        if ($r->exists('region_id') && !$r->filled('region_id')) $r->merge(['comuna_id'=>null]);
        $r->merge(['rut'=>rut_normalizar($r->rut),'email'=>$r->filled('email')?correo_normalizar($r->email):null]);
        $d=$r->validate(['id'=>'nullable|integer|min:1','rut'=>['required','string','max:20',Rule::unique('vet_trabajadores')->where('id_institucion',$i->id)->ignore($r->id)],
            'nombres'=>'required|string|max:100','apellido_uno'=>'required|string|max:80','apellido_dos'=>'nullable|string|max:80','categoria_id'=>'required|integer',
            'email'=>'nullable|email|max:255','telefono'=>'nullable|string|max:30','direccion'=>'nullable|string|max:255','banco'=>'nullable|string|max:100','tipo_cuenta'=>'nullable|string|max:60','numero_cuenta'=>'nullable|string|max:50',
            'region_id'=>'nullable|integer|exists:regiones,id',
            'comuna_id'=>'nullable|required_with:region_id|integer|exists:ciudades,id',
            'observaciones'=>'nullable|string|max:10000','activo'=>'required|boolean','lugares'=>'required|array|min:1','lugares.*'=>'required|integer|distinct']);
        $this->error(rut_es_valido($d['rut']),'rut','El dígito verificador del RUT no coincide. Revise el número y el dígito final; puede escribirlo con o sin puntos.');
        if (!empty($d['comuna_id'])) {
            $this->error(!empty($d['region_id']) && DB::table('ciudades')->where('id',$d['comuna_id'])->where('id_region',$d['region_id'])->exists(),'comuna_id','Seleccione una comuna de la región indicada.');
        }
        return DB::transaction(function()use($d,$i){
            Instituciones::whereKey($i->id)->lockForUpdate()->firstOrFail();
            $id=!empty($d['id'])?(int)$d['id']:null;$antes=$id?DB::table('vet_trabajadores')->where('id_institucion',$i->id)->where('id',$id)->lockForUpdate()->first():null;
            abort_if($id && !$antes,404);
            if($antes)$antes->lugares=DB::table('vet_trabajador_lugares')->where('trabajador_id',$id)->pluck('id_lugar_atencion')->all();
            $categoria=$this->registro('vet_categorias_trabajadores',$i,(int)$d['categoria_id']);
            $this->error((bool)$categoria->activa,'categoria_id','Seleccione una categoría activa.');
            $permitidos=$this->lugares($i)->pluck('id')->map(fn($id)=>(int)$id)->all();
            $this->error(!array_diff($d['lugares'],$permitidos),'lugares','Todas las sucursales deben pertenecer al establecimiento.');
            if($id && !$d['activo'])$this->exigirSinContrato($id);
            $lugares=$d['lugares'];$datos=$d;unset($datos['id'],$datos['lugares']);$datos['updated_at']=now();
            if($id)DB::table('vet_trabajadores')->where('id',$id)->update($datos);
            else $id=DB::table('vet_trabajadores')->insertGetId($datos+['id_institucion'=>$i->id,'creado_por'=>Auth::id(),'created_at'=>now()]);
            DB::table('vet_trabajador_lugares')->where('trabajador_id',$id)->delete();
            foreach($lugares as $lugar)DB::table('vet_trabajador_lugares')->insert(['trabajador_id'=>$id,'id_lugar_atencion'=>$lugar]);
            $despues=DB::table('vet_trabajadores')->find($id);$despues->lugares=$lugares;
            $this->auditar($i,'vet_trabajadores',$id,$antes,$despues);
            return $id;
        });
    }

    private function exigirSinContrato(int $trabajador): void
    {
        $this->error(!DB::table('vet_contratos_trabajadores')->where('trabajador_id',$trabajador)->where('estado','vigente')->exists(),'activo','Finalice el contrato vigente antes de desvincular al trabajador.');
    }

    public function estado(Request $r,Instituciones $i,int $id): void
    {
        $d=$r->validate(['activo'=>'required|boolean']);
        DB::transaction(function()use($d,$i,$id){
            $antes=DB::table('vet_trabajadores')->where('id_institucion',$i->id)->where('id',$id)->lockForUpdate()->first();abort_unless($antes,404);
            if(!$d['activo'])$this->exigirSinContrato($id);
            DB::table('vet_trabajadores')->where('id',$id)->update(['activo'=>$d['activo'],'updated_at'=>now()]);
            $this->auditar($i,'vet_trabajadores',$id,$antes,DB::table('vet_trabajadores')->find($id));
        });
    }

    public function contrato(Request $r,Instituciones $i): int
    {
        $d=$r->validate(['id'=>'nullable|integer|min:1','trabajador_id'=>'required|integer','tipo'=>['required',Rule::in(array_keys(self::TIPOS_CONTRATO))],
            'fecha_inicio'=>'required|date_format:Y-m-d','fecha_fin'=>'nullable|required_if:tipo,plazo_fijo|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'horas_semanales'=>'nullable|numeric|min:0|max:168','remuneracion'=>'required|integer|min:0|max:1000000000','observaciones'=>'nullable|string|max:10000']);
        return DB::transaction(function()use($d,$i){
            $t=DB::table('vet_trabajadores')->where('id_institucion',$i->id)->where('id',$d['trabajador_id'])->lockForUpdate()->first();abort_unless($t,404);
            $this->error((bool)$t->activo,'trabajador_id','Reactive al trabajador antes de registrar un contrato.');
            $id=!empty($d['id'])?(int)$d['id']:null;$antes=$id?$this->registro('vet_contratos_trabajadores',$i,$id):null;
            $this->error(!$antes || ((int)$antes->trabajador_id===(int)$t->id && $antes->estado==='vigente'),'id','Solo se puede editar el contrato vigente de este trabajador.');
            $this->error(!DB::table('vet_contratos_trabajadores')->where('trabajador_id',$t->id)->where('estado','vigente')->when($id,fn($q)=>$q->where('id','!=',$id))->exists(),'trabajador_id','El trabajador ya tiene un contrato vigente.');
            $fin=$d['tipo']==='indefinido'?'9999-12-31':($d['fecha_fin']??'9999-12-31');
            $cruce=DB::table('vet_contratos_trabajadores')->where('trabajador_id',$t->id)->where('estado','!=','anulado')->when($id,fn($q)=>$q->where('id','!=',$id))
                ->where('fecha_inicio','<=',$fin)->whereRaw("COALESCE(fecha_termino, fecha_fin, '9999-12-31') >= ?",[$d['fecha_inicio']])->exists();
            $this->error(!$cruce,'fecha_inicio','El período se superpone con un contrato anterior de este trabajador.');
            $datos=$d;unset($datos['id']);$datos['fecha_fin']=$d['tipo']==='indefinido'?null:($d['fecha_fin']??null);$datos['updated_at']=now();
            if($id)DB::table('vet_contratos_trabajadores')->where('id',$id)->update($datos);
            else $id=DB::table('vet_contratos_trabajadores')->insertGetId($datos+['id_institucion'=>$i->id,'estado'=>'vigente','creado_por'=>Auth::id(),'created_at'=>now()]);
            $this->auditar($i,'vet_contratos_trabajadores',$id,$antes,DB::table('vet_contratos_trabajadores')->find($id));return $id;
        });
    }

    public function terminar(Request $r,Instituciones $i,int $id): void
    {
        $d=$r->validate(['fecha_termino'=>'required|date_format:Y-m-d|before_or_equal:today']);
        DB::transaction(function()use($d,$i,$id){
            $antes=$this->registro('vet_contratos_trabajadores',$i,$id);DB::table('vet_trabajadores')->where('id',$antes->trabajador_id)->lockForUpdate()->first();
            $this->error($antes->estado==='vigente','id','El contrato ya está terminado.');
            $sinIniciar=substr($antes->fecha_inicio,0,10)>today()->toDateString();
            $this->error($sinIniciar || $d['fecha_termino']>=substr($antes->fecha_inicio,0,10),'fecha_termino','La fecha de término no puede ser anterior al inicio.');
            DB::table('vet_contratos_trabajadores')->where('id',$id)->update(['estado'=>$sinIniciar?'anulado':'terminado','fecha_termino'=>$d['fecha_termino'],'updated_at'=>now()]);
            $this->auditar($i,'vet_contratos_trabajadores',$id,$antes,DB::table('vet_contratos_trabajadores')->find($id));
        });
    }

    public function mensaje(Request $r,Instituciones $i,int $id): object
    {
        $d=$r->validate(['token'=>'required|uuid','asunto'=>'required|string|max:200|not_regex:/[\r\n]/','mensaje'=>'required|string|max:20000']);
        $t=$this->registro('vet_trabajadores',$i,$id);
        $this->error((bool)$t->activo && filter_var($t->email,FILTER_VALIDATE_EMAIL),'mensaje','El trabajador debe estar activo y tener un correo válido.');
        $registro=DB::transaction(function()use($d,$t,$i){
            $actual=DB::table('vet_trabajadores')->where('id',$t->id)->lockForUpdate()->first();
            $this->error((bool)$actual->activo && filter_var($actual->email,FILTER_VALIDATE_EMAIL),'mensaje','El trabajador debe estar activo y tener un correo válido.');
            $existente=DB::table('vet_mensajes_trabajadores')->where('token',$d['token'])->first();
            if($existente){abort_unless((int)$existente->id_institucion===(int)$i->id && (int)$existente->trabajador_id===(int)$t->id,409);return ['nuevo'=>false,'id'=>$existente->id,'destinatario'=>$existente->destinatario];}
            $id=DB::table('vet_mensajes_trabajadores')->insertGetId($d+['id_institucion'=>$i->id,'trabajador_id'=>$t->id,'id_usuario'=>Auth::id(),'destinatario'=>$actual->email,'estado'=>'pendiente','created_at'=>now(),'updated_at'=>now()]);
            return ['nuevo'=>true,'id'=>$id,'destinatario'=>$actual->email];
        });
        if($registro['nuevo']){
            try {
                Mail::to($registro['destinatario'])->send(new MensajeTrabajador($i->nombre,$d['asunto'],$d['mensaje']));
                $registroLocal=in_array(config('mail.mailers.'.config('mail.default').'.transport'),['log','array'],true);
                DB::table('vet_mensajes_trabajadores')->where('id',$registro['id'])->update(['estado'=>$registroLocal?'registrado':'enviado','enviado_en'=>$registroLocal?null:now(),'updated_at'=>now()]);
            }catch(\Throwable $e){DB::table('vet_mensajes_trabajadores')->where('id',$registro['id'])->update(['estado'=>'fallido','updated_at'=>now()]);report($e);}
        }
        return DB::table('vet_mensajes_trabajadores')->find($registro['id']);
    }
}
