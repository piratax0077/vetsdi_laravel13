<?php
namespace App\Http\Controllers;
use App\Models\Peluqueria;
use App\Services\CuentasService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB};
use Illuminate\Validation\ValidationException;
class PeluqueriaController extends Controller
{
    public const COMPORTAMIENTOS = ['Tranquilo/a','Nervioso/a','Enojado/a','Ansioso/a','Sociable','Miedoso/a','Se deja manipular','Requiere bozal'];
    public const HALLAZGOS = ['Pulgas','Garrapatas','Verrugas','Secreciones','Irritaciones en piel','Heridas','Problemas en ojos','Problemas en oídos','Problemas dentales','Problemas en uñas','Alteraciones en pelo/piel'];

    private function nombreEspecie($valor): string {
        $valor=trim((string)$valor);
        if($valor!==''&&ctype_digit($valor)){
            if(\Illuminate\Support\Facades\Schema::hasTable('especies_mascotas'))return DB::table('especies_mascotas')->where('id',(int)$valor)->value('nombre')??'Sin información';
            return 'Sin información';
        }
        return $valor;
    }
    public function ficha(int $cita) {
        $perfil = $this->perfil();
        $atencion = DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->first();
        abort_unless($atencion,404);
        $acceso=app(\App\Services\PeluqueriaAcceso::class);$acceso->exigirCita($perfil,$atencion);
        $permisos=$acceso->permisos($perfil);abort_unless(($permisos['atender']??false)||(($permisos['ver_historial']??false)&&$atencion->estado==='finalizada'),403);
        $cliente = DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->where('id',$atencion->cliente_id)->first();
        abort_unless($cliente,404);
        $guardada = DB::table('peluqueria_fichas')->where('cita_id',$cita)->where('peluqueria_id',$perfil->id)->first();
        $esterilizada = 'desconocido';
        if (!$guardada && !empty($cliente->mascota_id)) {
            $mascota = \App\Models\Mascota::find($cliente->mascota_id);
            if ($mascota && $mascota->esterilizado !== null) {
                $esterilizada = $mascota->esterilizado ? 'si' : 'no';
            }
        }
        $datos = $guardada ? json_decode($guardada->datos,true) : [
            'mascota'=>$cliente->mascota,'especie'=>$cliente->especie,'raza'=>$cliente->raza,'esterilizada'=>$esterilizada,
            'observaciones'=>$atencion->registro ?: $cliente->cuidados,
            'servicios'=>$atencion->procedimientos_reservados?json_decode($atencion->procedimientos_reservados,true):[['nombre'=>$atencion->servicio,'precio'=>(int)$atencion->valor]],
        ];
        $datos['especie']=$this->nombreEspecie($datos['especie']??$cliente->especie);
        if (!$guardada && !$atencion->procedimientos_reservados && $atencion->procedimiento_id) $datos['servicios'][0]['procedimiento_id'] = $atencion->procedimiento_id;
        $procedimientos=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->where('activo',true)->orderBy('nombre')->get();
        $anteriores=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('cliente_id',$cliente->id)
            ->when(!($permisos['ver_historial']??false),fn($q)=>$q->whereRaw('1=0'))
            ->where('estado','finalizada')
            ->when(app(\App\Services\PeluqueriaAcceso::class)->miembro($perfil)?->sucursal_id,fn($q,$id)=>$q->where('sucursal_id',$id))
            ->orderByDesc('finalizada_en')->orderByDesc('inicio')->orderByDesc('id')->paginate(15,['*'],'historial_pagina');
        return view('app.peluqueria.ficha', compact('perfil','atencion','cliente','datos','procedimientos','anteriores'));
    }

    public function fichaGuardar(Request $request, int $cita) {
        $perfil = $this->perfil('atender');
        $datos = $request->validate([
            'mascota'=>'required|string|max:100','especie'=>'required|string|max:60','raza'=>'nullable|string|max:100',
            'edad'=>'nullable|string|max:60','esterilizada'=>'required|in:si,no,desconocido',
            'alergias'=>'nullable|string|max:2000','alergias_productos'=>'nullable|string|max:2000',
            'comportamientos'=>'sometimes|array|max:8','comportamientos.*'=>['string',\Illuminate\Validation\Rule::in(self::COMPORTAMIENTOS)],
            'comportamiento_otro'=>'nullable|string|max:1000','hallazgos'=>'sometimes|array|max:11',
            'hallazgos.*'=>['string',\Illuminate\Validation\Rule::in(self::HALLAZGOS)],
            'hallazgos_otro'=>'nullable|string|max:2000','servicios'=>'required|array|min:1|max:30',
            'servicios.*.nombre'=>'required|string|max:150','servicios.*.precio'=>'required|integer|min:0|max:999999999',
            'servicios.*.procedimiento_id'=>'nullable|integer',
            'observaciones_servicios'=>'nullable|string|max:3000','productos_utilizados'=>'nullable|string|max:2000',
            'observaciones'=>'nullable|string|max:5000','accion'=>'required|in:guardar,finalizar',
        ]);
        $datos['comportamientos'] = array_values(array_unique($datos['comportamientos'] ?? []));
        $datos['hallazgos'] = array_values(array_unique($datos['hallazgos'] ?? []));
        $datos['servicios'] = array_values($datos['servicios']);
        $datos['especie']=$this->nombreEspecie($datos['especie']);
        $citaActual=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->first();
        abort_unless($citaActual,404);
        app(\App\Services\PeluqueriaAcceso::class)->exigirCita($perfil,$citaActual);
        $fichaAnterior=DB::table('peluqueria_fichas')->where('cita_id',$cita)->where('peluqueria_id',$perfil->id)->first();
        $anteriores=$fichaAnterior ? (json_decode($fichaAnterior->datos,true)['servicios']??[]) : ($citaActual->procedimientos_reservados?json_decode($citaActual->procedimientos_reservados,true):[['nombre'=>$citaActual->servicio,'precio'=>(int)$citaActual->valor]]);
        if (!$fichaAnterior && $citaActual->procedimiento_id) $anteriores[0]['procedimiento_id']=$citaActual->procedimiento_id;
        foreach($datos['servicios'] as &$servicio) {
            // Conservar el valor ya registrado, aunque el catálogo cambie después.
            $indiceAnterior=null;
            foreach ($anteriores as $indice=>$anterior) {
                if ((int)($anterior['procedimiento_id']??0)===(int)($servicio['procedimiento_id']??0)
                    && $anterior['nombre']===$servicio['nombre'] && (int)$anterior['precio']===(int)$servicio['precio']) {
                    $indiceAnterior=$indice; break;
                }
            }
            if ($indiceAnterior!==null) {
                $servicio=$anteriores[$indiceAnterior];unset($anteriores[$indiceAnterior]);continue;
            }
            if (!empty($servicio['procedimiento_id'])) {
                $configurado=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->where('id',$servicio['procedimiento_id'])->where('activo',true)->first();
                if(!$configurado) throw ValidationException::withMessages(['servicios'=>'Seleccione servicios activos del panel de configuración.']);
                $servicio=['procedimiento_id'=>$configurado->id,'nombre'=>$configurado->nombre,'precio'=>(int)$configurado->precio];
            } else {
                throw ValidationException::withMessages(['servicios'=>'Agregue los servicios desde el selector de procedimientos configurados.']);
            }
        }
        unset($servicio);
        $total = array_sum(array_column($datos['servicios'],'precio'));
        if ($total > 999999999) throw ValidationException::withMessages(['servicios'=>'El total supera el valor permitido.']);
        DB::transaction(function () use ($perfil,$cita,$datos,$total) {
            $actual = DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->lockForUpdate()->first();
            abort_unless($actual,404);
            abort_unless(in_array($actual->estado,['en_atencion'],true),422,'Primero confirma la reserva, recepciona el pago y registra la espera antes de atender.');
            $finalizar = $datos['accion'] === 'finalizar'; unset($datos['accion']);
            $datos['total'] = $total;
            $datos['monto_pagado'] = max(0, (int) ($actual->monto_pagado ?? 0));
            $datos['saldo_pendiente'] = max(0, $total - $datos['monto_pagado']);
            $datos['tutor'] = DB::table('peluqueria_clientes')->where('id',$actual->cliente_id)->where('peluqueria_id',$perfil->id)->value('tutor');
            $existente = DB::table('peluqueria_fichas')->where('cita_id',$cita)->first();
            $valores = ['peluqueria_id'=>$perfil->id,'datos'=>json_encode($datos,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),'updated_at'=>now()];
            if (!$existente) $valores['created_at'] = now();
            DB::table('peluqueria_fichas')->updateOrInsert(['cita_id'=>$cita],$valores);
            $registro = implode('; ',array_map(fn ($s)=>$s['nombre'].' ($'.number_format($s['precio'],0,',','.').')',$datos['servicios']));
            DB::table('peluqueria_citas')->where('id',$cita)->update([
                'valor'=>$total,'registro'=>$registro.(!empty($datos['observaciones']) ? "\n".$datos['observaciones'] : ''),
                'estado'=>$finalizar?'finalizada':'en_atencion','finalizada_en'=>$finalizar?now():null,'updated_at'=>now(),
            ]);
        });
        return redirect()->route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.ficha',$cita)->with('mensaje','Ficha guardada correctamente.');
    }

    private function perfil(string $permiso=''): Peluqueria {
        $acceso=app(\App\Services\PeluqueriaAcceso::class);$perfil=$acceso->perfil();
        if($permiso)$acceso->exigir($perfil,$permiso);
        view()->share(['peluqueriaRutaBase'=>$acceso->rutaBase(),'esCentroEstetico'=>$acceso->rutaBase()==='centro_estetico','peluqueriaPermisos'=>$acceso->permisos($perfil),'peluqueriaSucursales'=>$acceso->sucursales($perfil),'peluqueriaContextos'=>$acceso->opciones()]);
        return $perfil;
    }
    private function profesionalAgenda(Peluqueria $perfil,$sucursal,string $valor): array {
        $acceso=app(\App\Services\PeluqueriaAcceso::class);
        if(session('rol_activo')==='peluqueria'){
            $miembro=$acceso->miembro($perfil);abort_unless($acceso->propietario($perfil)?$valor==='titular':($miembro&&(string)$miembro->id===$valor),403,'El profesional de estética solo puede abrir su propia agenda.');
        }
        if($valor==='titular')abort_unless(DB::table('usuario_roles')->where('id_usuario',$perfil->id_usuario)->where('tipo','peluqueria')->where('estado',1)->exists(),403,'El administrador del centro no tiene un perfil profesional de estética activo.');
        if($valor==='titular')return ['id'=>null,'nombre'=>\App\Models\User::find($perfil->id_usuario)?->name??$perfil->nombre];
        abort_unless(ctype_digit($valor),422);
        $persona=DB::table('peluqueria_personal as p')->join('users as u','u.id','=','p.usuario_id')->where('p.peluqueria_id',$perfil->id)->where('p.id',(int)$valor)->where('p.activo',true)->select('p.*','u.name')->first();
        abort_unless($persona&&(!$persona->sucursal_id||(int)$persona->sucursal_id===(int)$sucursal?->id)&&in_array('atender',json_decode($persona->permisos,true)??[],true),403,'Este profesional no atiende en el lugar seleccionado.');
        return ['id'=>$persona->id,'nombre'=>$persona->name];
    }

    public function index(Request $request) {
        $perfil=$this->perfil();
        $acceso=app(\App\Services\PeluqueriaAcceso::class);$permisos=$acceso->permisos($perfil);
        $ruta=str_replace('centro_estetico.','peluqueria.',$request->route()?->getName()??'');
        $necesario=match($ruta){'peluqueria.agenda.ver'=>'ver_agenda','peluqueria.mascotas'=>'ver_clientes','peluqueria.historial'=>'ver_historial',default=>null};
        if($necesario)$acceso->exigir($perfil,$necesario);
        $contextoAgenda=null;
        if($ruta==='peluqueria.agenda.ver'){
            $previo=session('peluqueria_agenda_contexto');
            if(!$request->filled('sucursal_id')&&$acceso->sucursales($perfil)->isNotEmpty()){
                if(!$previo||(int)$previo['peluqueria_id']!==(int)$perfil->id)return redirect()->route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.home',['elegir_agenda'=>1]);
                $request->merge(['sucursal_id'=>$previo['sucursal_id'],'agenda_personal'=>$previo['agenda_personal']]);
            }
        }
        $request->validate(['agenda_personal'=>'nullable|string|max:30']);
        $request->validate(['sucursal_id'=>'nullable|integer|min:1']);$sucursal=$acceso->sucursal($perfil,$request->input('sucursal_id'),false);
        if($ruta==='peluqueria.historial')$sucursal=null;
        $agendaConfigurada=$sucursal?->agenda?json_decode($sucursal->agenda,true):null;
        $agendaPersonal=(string)$request->input('agenda_personal',$acceso->miembro($perfil)?->id??'titular');
        if($ruta==='peluqueria.home'&&$acceso->rutaBase()==='centro_estetico'&&$acceso->propietario($perfil)&&!$request->filled('sucursal_id'))$sucursal=null;
        $profesionalAgenda=$ruta==='peluqueria.agenda.ver'?$this->profesionalAgenda($perfil,$sucursal,$agendaPersonal):null;
        if($profesionalAgenda){session(['peluqueria_agenda_contexto'=>['peluqueria_id'=>$perfil->id,'sucursal_id'=>$sucursal?->id,'agenda_personal'=>$agendaPersonal]]);}
        $opcionesAgenda=\Illuminate\Support\Facades\Schema::hasTable('peluqueria_personal')?DB::table('peluqueria_personal as p')->join('users as u','u.id','=','p.usuario_id')->where('p.peluqueria_id',$perfil->id)->where('p.activo',true)->select('p.*','u.name')->get()->filter(fn($m)=>in_array('atender',json_decode($m->permisos,true)??[],true)):collect();
        $puedeVerTitular=DB::table('usuario_roles')->where('id_usuario',$perfil->id_usuario)->where('tipo','peluqueria')->where('estado',1)->exists();
        if(session('rol_activo')==='peluqueria'&&!$acceso->propietario($perfil)){$opcionesAgenda=$opcionesAgenda->where('id',$acceso->miembro($perfil)?->id);$puedeVerTitular=false;}
        $titularAgenda=\App\Models\User::find($perfil->id_usuario)?->name??$perfil->nombre;

        $personal=\Illuminate\Support\Facades\Schema::hasTable('peluqueria_personal')?DB::table('peluqueria_personal as p')->join('users as u','u.id','=','p.usuario_id')->where('p.peluqueria_id',$perfil->id)->where('p.activo',true)->when($sucursal,fn($q)=>$q->where(fn($q)=>$q->whereNull('p.sucursal_id')->orWhere('p.sucursal_id',$sucursal->id)))->select('p.id','u.name')->get():collect();

        $datos=$request->validate(['fecha'=>'nullable|date_format:Y-m-d']);
        $fecha=$datos['fecha']??Carbon::now(config('peluqueria.timezone'))->toDateString();
        $clientes=DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->when(!($permisos['ver_clientes']??false),fn($q)=>$q->whereRaw('1=0'))->orderBy('mascota')->get();
        $citas=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')
            ->where('c.peluqueria_id',$perfil->id)->when($sucursal,fn($q)=>$q->where('c.sucursal_id',$sucursal->id))->when($profesionalAgenda,fn($q)=>$profesionalAgenda['id']?$q->where('c.personal_id',$profesionalAgenda['id']):$q->whereNull('c.personal_id'))->when(!($permisos['ver_agenda']??false),fn($q)=>$q->whereRaw('1=0'))->whereDate('c.inicio',$fecha)->orderBy('c.inicio')
            ->select('c.*','m.mascota','m.especie','m.tutor','m.rut','m.email','m.telefono','m.cuidados')->get();
        $historial=$acceso->filtrarCitas(DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id'),$perfil,'c.sucursal_id')
            ->leftJoin('peluqueria_sucursales as s',function($join){$join->on('s.id','=','c.sucursal_id')->on('s.peluqueria_id','=','c.peluqueria_id');})
            ->where('c.peluqueria_id',$perfil->id)->when($sucursal,fn($q)=>$q->where('c.sucursal_id',$sucursal->id))->when(!($permisos['ver_historial']??false),fn($q)=>$q->whereRaw('1=0'))->where('c.estado','finalizada')->orderByDesc('c.finalizada_en')
            ->select('c.*','m.mascota','m.tutor','s.nombre as sucursal_nombre','s.direccion as sucursal_direccion')->paginate(15);
        $procedimientos=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->where('activo',true)->orderBy('nombre')->get();
        $seccion = match($ruta) {'peluqueria.mascotas'=>'clientes','peluqueria.historial'=>'registro',default=>'agenda'};
        $vista = $ruta==='peluqueria.home' || !$request->route() ? 'escritorio' : 'agenda';
        return view('app.peluqueria.'.$vista,compact('perfil','fecha','clientes','citas','historial','procedimientos','seccion','sucursal','agendaConfigurada','personal','agendaPersonal','profesionalAgenda','opcionesAgenda','titularAgenda','puedeVerTitular'));
    }
    public function configuracion(?Request $request=null) {
        $request=$request??request();$perfil=$this->perfil();$acceso=app(\App\Services\PeluqueriaAcceso::class);$permisos=$acceso->permisos($perfil);
        $secciones=array_filter(['procedimientos','personal','agenda','sucursales','negocio'],fn($p)=>$permisos[$p]??false);abort_unless($secciones,403);
        $seccion=$request->input('seccion',reset($secciones));abort_unless(in_array($seccion,$secciones,true),403);
        $procedimientos=($permisos['procedimientos']??false)?DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->orderBy('nombre')->get():collect();
        $sucursales=$acceso->sucursales($perfil);
        $personal=($permisos['personal']??false)?DB::table('peluqueria_personal as p')->join('users as u','u.id','=','p.usuario_id')->where('p.peluqueria_id',$perfil->id)->select('p.*','u.name','u.email')->orderBy('u.name')->get():collect();
        $bloqueos=($permisos['agenda']??false)?DB::table('peluqueria_bloqueos')->where('peluqueria_id',$perfil->id)->whereIn('sucursal_id',$sucursales->pluck('id'))->where('fin','>=',now())->orderBy('inicio')->get():collect();
        return view('app.peluqueria.configuracion',compact('perfil','procedimientos','seccion','sucursales','personal','bloqueos'));
    }
    public function eventos(Request $request) {
        $perfil=$this->perfil('ver_agenda');
        $datos=$request->validate(['start'=>'required|date','end'=>'required|date|after:start','sucursal_id'=>'nullable|integer|min:1']);
        $sucursal=app(\App\Services\PeluqueriaAcceso::class)->sucursal($perfil,$request->input('sucursal_id'),false);
        $request->validate(['agenda_personal'=>'nullable|string|max:30']);
        $profesionalAgenda=$request->filled('agenda_personal')?$this->profesionalAgenda($perfil,$sucursal,(string)$request->agenda_personal):null;
        $inicio=Carbon::parse($datos['start']);$fin=Carbon::parse($datos['end']);
        abort_if($inicio->diffInDays($fin)>93,422);
        $citas=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')
            ->where('c.peluqueria_id',$perfil->id)->when($sucursal,fn($q)=>$q->where('c.sucursal_id',$sucursal->id))->when($profesionalAgenda,fn($q)=>$profesionalAgenda['id']?$q->where('c.personal_id',$profesionalAgenda['id']):$q->whereNull('c.personal_id'))->where('c.estado','<>','cancelada')->where('c.inicio','<',$fin)->where('c.fin','>',$inicio)
            ->select('c.*','m.mascota','m.especie','m.tutor','m.rut','m.telefono','m.email')->get();
        return response()->json($citas->map(fn($c)=>[
            'id'=>$c->id,'title'=>config('peluqueria.estados.'.$c->estado.'.nombre',$c->estado).' · '.$c->mascota.' · '.$c->servicio,'start'=>Carbon::parse($c->inicio)->format('Y-m-d\TH:i:s'),
            'end'=>Carbon::parse($c->fin)->format('Y-m-d\TH:i:s'),
            'extendedProps'=>['reserva'=>$this->datosReserva($c,$perfil)],
            'color'=>config('peluqueria.estados.'.$c->estado.'.color','#526477'),
        ]));
    }
    public function procedimientoGuardar(Request $request) {
        $perfil=$this->perfil('procedimientos');
        $datos=$request->validate(['id'=>'nullable|integer','nombre'=>'required|string|max:150','duracion'=>'required|integer|min:1|max:480','precio'=>'required|integer|min:0|max:999999999','activo'=>'required|boolean','indicaciones'=>'nullable|string|max:3000']);
        $id=$datos['id']??null;unset($datos['id']);
        if($id){abort_unless(DB::table('peluqueria_procedimientos')->where('id',$id)->where('peluqueria_id',$perfil->id)->exists(),404);DB::table('peluqueria_procedimientos')->where('id',$id)->where('peluqueria_id',$perfil->id)->update($datos+['updated_at'=>now()]);}
        else DB::table('peluqueria_procedimientos')->insert($datos+['peluqueria_id'=>$perfil->id,'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.configuracion')->with('mensaje','Procedimiento guardado.');
    }
    public function perfilGuardar(Request $request) {
        $perfil=$this->perfil('negocio');$datos=$request->validate(['nombre'=>'required|string|max:255','telefono'=>'required|string|max:30','direccion'=>'required|string|max:255']);
        $extra=$request->validate(['rut_empresa'=>['nullable',new \App\Rules\RutChileno],'razon_social'=>'nullable|string|max:255','giro'=>'nullable|string|max:255','email_contacto'=>'nullable|email|max:255']);
        $perfil->fill($datos+$extra)->saveOrFail();return back()->with('mensaje','Perfil actualizado.');
    }
    private function rutTutor(Request $request): string {
        $request->merge(['rut'=>strtoupper(preg_replace('/[.\s-]/','',(string)$request->input('rut')))]);
        $request->validate(['rut'=>['required',new \App\Rules\RutChileno]]);
        return $request->rut;
    }
    private function tutorPorRut(string $rut) {
        return DB::table('pacientes')->whereRaw("UPPER(REPLACE(REPLACE(rut,'.',''),'-','')) = ?",[$rut])->first();
    }
    public function buscarTutor(Request $request) {
        $perfil=$this->perfil('reservar');$rut=$this->rutTutor($request);
        $locales=DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->where('rut',$rut)->get();
        $mascotas=$locales->map(fn($m)=>['id'=>'local:'.$m->id,'nombre'=>$m->mascota]);
        $tutor=$this->tutorPorRut($rut);
        if($tutor){
            foreach(DB::table('mascotas')->where('id_responsable',$tutor->id)->orderBy('nombre')->get() as $m){
                if(!$locales->contains('mascota_id',$m->id))$mascotas->push(['id'=>'sistema:'.$m->id,'nombre'=>$m->nombre]);
            }
        }
        return response()->json(['tutor'=>$tutor?trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos):$locales->first()?->tutor,'telefono'=>$tutor->telefono_uno??$locales->first()?->telefono,'email'=>$tutor->email??$locales->first()?->email,'mascotas'=>$mascotas->values()]);
    }
    public function clienteGuardar(Request $request) {
        $perfil=$this->perfil('editar_clientes');
        $this->rutTutor($request);
        $datos=$request->validate(['rut'=>['required',new \App\Rules\RutChileno],'tutor'=>'required|string|max:255','telefono'=>'required|string|max:30','email'=>'nullable|email|max:255','mascota'=>'required|string|max:100','especie'=>'required|string|max:60','raza'=>'nullable|string|max:100','cuidados'=>'nullable|string|max:3000']);
        DB::table('peluqueria_clientes')->insert($datos+['peluqueria_id'=>$perfil->id,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('mensaje','Tutor y mascota registrados.');
    }
    public function citaGuardar(Request $request) {
        $perfil=$this->perfil('reservar');
        $acceso=app(\App\Services\PeluqueriaAcceso::class);
        $request->validate(['sucursal_id'=>'nullable|integer|min:1','personal_id'=>'nullable|integer|min:1']);
        $sucursal=$acceso->sucursal($perfil,$request->input('sucursal_id'));
        $personalId=$request->filled('personal_id')?(int)$request->personal_id:null;
        $agendaTitular=false;
        if($request->filled('agenda_personal')){
            $request->validate(['agenda_personal'=>'required|string|max:30']);
            $seleccion=$this->profesionalAgenda($perfil,$sucursal,(string)$request->agenda_personal);
            $personalId=$seleccion['id'];$agendaTitular=$request->agenda_personal==='titular';
        }

        if($request->boolean('nuevo_cliente'))$acceso->exigir($perfil,'editar_clientes');
        if($request->boolean('nuevo_cliente')&&((int)$request->input('cliente_id',0)>0||$request->filled('mascota_sistema_id'))){
            throw ValidationException::withMessages(['nuevo_cliente'=>'La reserva es para una sola mascota. Quita la selección actual antes de inscribir otra.']);
        }
        $nuevoCliente=null;
        if($request->filled('mascota_sistema_id')){
            $rut=$this->rutTutor($request);$tutor=$this->tutorPorRut($rut);
            $mascota=$tutor?DB::table('mascotas')->where('id',$request->mascota_sistema_id)->where('id_responsable',$tutor->id)->first():null;
            abort_unless($mascota,404);
            $existente=DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->where('mascota_id',$mascota->id)->first();
            if($existente){$request->merge(['cliente_id'=>$existente->id]);}
            else{$nuevoCliente=['rut'=>$rut,'mascota_id'=>$mascota->id,'tutor'=>trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos),'telefono'=>$tutor->telefono_uno??'','email'=>$tutor->email,'mascota'=>$mascota->nombre,'especie'=>$this->nombreEspecie($mascota->especie_id??$mascota->especie??$mascota->otra_especie??''),'raza'=>null];$request->merge(['cliente_id'=>0]);}
        }
        if($request->boolean('nuevo_cliente')) {
            $acceso->exigir($perfil,'editar_clientes');
            $this->rutTutor($request);
            $nuevoCliente=$request->validate(['rut'=>['required',new \App\Rules\RutChileno],'tutor'=>'required|string|max:255','telefono'=>'required|string|max:30','email'=>'nullable|email|max:255','mascota'=>'required|string|max:100','especie'=>'required|string|max:60','raza'=>'nullable|string|max:100','cuidados'=>'nullable|string|max:3000']);
            $tutor=$this->tutorPorRut($request->rut);
            if($tutor){$nuevoCliente['tutor']=trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos);$nuevoCliente['telefono']=$tutor->telefono_uno??$nuevoCliente['telefono'];$nuevoCliente['email']=$tutor->email??$nuevoCliente['email']??null;}
            $request->merge(['cliente_id'=>0]);
        }
        $request->validate(['procedimiento_id'=>'nullable|integer','procedimiento_ids'=>'sometimes|required|array|min:1|max:20','procedimiento_ids.*'=>'required|integer|distinct']);
        $ids=$request->input('procedimiento_ids',$request->filled('procedimiento_id')?[$request->procedimiento_id]:[]);
        $seleccionados=collect();
        if($ids){
            $catalogo=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->whereIn('id',$ids)->where('activo',true)->get()->keyBy('id');
            abort_unless($catalogo->count()===count($ids),404);
            $seleccionados=collect($ids)->map(fn($id)=>$catalogo[$id]);
            $request->merge(['servicio'=>'Peluquería','valor'=>$seleccionados->sum('precio'),'duracion'=>$seleccionados->sum('duracion')]);
        }
        $datos=$request->validate(['cliente_id'=>'required|integer','inicio'=>'required|date_format:Y-m-d\TH:i','duracion'=>'required|integer|min:1|max:480','servicio'=>'required|in:Baño,Belleza,Peluquería','valor'=>'required|integer|min:0|max:999999999','observaciones'=>'nullable|string|max:3000']);
        if($seleccionados->isNotEmpty()){
            $datos['servicio']=$seleccionados->count()===1?$seleccionados->first()->nombre:'Reserva de '.$seleccionados->count().' procedimientos';
            $datos['procedimiento_id']=$seleccionados->first()->id;
            $datos['procedimientos_reservados']=json_encode($seleccionados->map(fn($p)=>['procedimiento_id'=>$p->id,'nombre'=>$p->nombre,'precio'=>(int)$p->precio,'duracion'=>(int)$p->duracion])->values(),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        }
        $citaId=DB::transaction(function()use($perfil,$datos,$nuevoCliente,$sucursal,$personalId,$agendaTitular){
            Peluqueria::whereKey($perfil->id)->lockForUpdate()->firstOrFail();
            if($sucursal){$sucursal=DB::table('peluqueria_sucursales')->where('peluqueria_id',$perfil->id)->where('id',$sucursal->id)->lockForUpdate()->first();abort_unless($sucursal&&$sucursal->activo,403,'La sucursal ya no está disponible.');}
            if(!$nuevoCliente) abort_unless(DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->where('id',$datos['cliente_id'])->exists(),404);
            $inicio=Carbon::parse($datos['inicio'],config('peluqueria.timezone'));
            if($inicio->lt(Carbon::now(config('peluqueria.timezone'))))throw ValidationException::withMessages(['inicio'=>'La reserva debe ser para una fecha y hora futura.']);
            $fin=$inicio->copy()->addMinutes((int)$datos['duracion']);
            app(\App\Services\PeluqueriaAgenda::class)->validar($perfil,$inicio,$fin,$sucursal,$personalId,$agendaTitular);
            $datos['sucursal_id']=$sucursal?->id;$datos['personal_id']=$personalId;
            unset($datos['duracion']);$datos['inicio']=$inicio;$datos['fin']=$fin;
            if($nuevoCliente) $datos['cliente_id']=DB::table('peluqueria_clientes')->insertGetId($nuevoCliente+['peluqueria_id'=>$perfil->id,'created_at'=>now(),'updated_at'=>now()]);
            return DB::table('peluqueria_citas')->insertGetId($datos+['peluqueria_id'=>$perfil->id,'estado'=>'reservada','created_at'=>now(),'updated_at'=>now()]);
        });
        if($request->expectsJson()) {
            $cliente=DB::table('peluqueria_clientes as m')->join('peluqueria_citas as c','c.cliente_id','=','m.id')->where('c.id',$citaId)->where('c.peluqueria_id',$perfil->id)->select('m.id','m.mascota','m.tutor')->first();
            return response()->json(['estado'=>1,'mensaje'=>'Hora reservada correctamente.','cita_id'=>$citaId,'ficha_url'=>route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.ficha',$citaId),'cliente'=>$cliente]);
        }
        return back()->with('mensaje','Hora reservada.');
    }
    public function datosReserva($cita,Peluqueria $perfil): array {
        $permisos=app(\App\Services\PeluqueriaAcceso::class)->permisos($perfil);
        $siguientes=match($cita->estado){'reservada'=>['confirmada'=>'confirmar'],'confirmada'=>['pagada'=>'pagar'],'pagada'=>['en_atencion'=>'atender'],'en_espera'=>['en_atencion'=>'atender'],default=>[]};
        if(in_array($cita->estado,['reservada','confirmada','pagada','en_espera'],true))$siguientes['cancelada']='anular';
        return ['id'=>$cita->id,'estado'=>$cita->estado,'color'=>config('peluqueria.estados.'.$cita->estado.'.color','#526477'),'tutor'=>$cita->tutor,'rut'=>$cita->rut??'','mascota'=>$cita->mascota,'especie'=>$this->nombreEspecie($cita->especie??''),'telefono'=>$cita->telefono??'','email'=>$cita->email??'',
            'procedimientos'=>$cita->procedimientos_reservados?json_decode($cita->procedimientos_reservados,true):[['nombre'=>$cita->servicio,'precio'=>(int)$cita->valor]],'servicio'=>$cita->servicio,'inicio'=>Carbon::parse($cita->inicio)->format('d-m-Y H:i'),'valor'=>(int)$cita->valor,
            'bloqueada'=>in_array($cita->estado,['pagada','en_espera','en_atencion'],true)&&!($permisos['atender']??false),'medio_pago'=>$cita->medio_pago??null,'monto_pagado'=>$cita->monto_pagado??null,
            'acciones'=>in_array($cita->estado,['pagada','en_espera','en_atencion'],true)&&!($permisos['atender']??false)?[]:array_keys(array_filter($siguientes,fn($permiso)=>$permisos[$permiso]??false)),
            'url'=>route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.estado',$cita->id),'ficha'=>in_array($cita->estado,['en_atencion','finalizada'],true)&&($permisos['atender']??($permisos['ver_historial']??false))?route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.ficha',$cita->id):null];
    }
    public function estado(Request $request,int $cita) {
        $perfil=$this->perfil();$datos=$request->validate(['estado'=>'required|in:confirmada,pagada,en_espera,en_atencion,finalizada,cancelada','registro'=>'nullable|string|max:5000']);
        app(\App\Services\PeluqueriaAcceso::class)->exigir($perfil,match($datos['estado']){'confirmada'=>'confirmar','pagada'=>'pagar','en_espera'=>'espera','cancelada'=>'anular',default=>'atender'});
        DB::transaction(function()use($perfil,$cita,$datos,$request){
            $actual=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->lockForUpdate()->first();abort_unless($actual,404);
            app(\App\Services\PeluqueriaAcceso::class)->exigirCita($perfil,$actual);
            if(in_array($actual->estado,['pagada','en_espera','en_atencion'],true))app(\App\Services\PeluqueriaAcceso::class)->exigir($perfil,'atender');
            $permitidos=['reservada'=>['confirmada','cancelada'],'confirmada'=>['pagada','cancelada'],'pagada'=>['en_atencion','en_espera','cancelada'],'en_espera'=>['en_atencion','cancelada'],'en_atencion'=>['finalizada']];
            if(!in_array($datos['estado'],$permitidos[$actual->estado]??[],true))throw ValidationException::withMessages(['estado'=>'Completa los pasos en orden: reserva, confirmación, pago, espera y atención.']);
            $adicional=[];
            if($datos['estado']==='confirmada'){
                $confirmacion=$request->validate(['via_confirmacion'=>'required|in:Presencial,Teléfono,WhatsApp,Correo electrónico']);
                $adicional=$confirmacion+['confirmada_en'=>now()];
            }
            if($datos['estado']==='pagada'){
                $pago=$request->validate(['medio_pago'=>'required|in:Efectivo,Transferencia,Tarjeta de débito,Tarjeta de crédito','monto_pagado'=>'required|integer|min:0|max:999999999','referencia_pago'=>'nullable|string|max:100']);
                if((int)$pago['monto_pagado']!==(int)$actual->valor)throw ValidationException::withMessages(['monto_pagado'=>'El monto debe coincidir con el arancel de la reserva.']);
                $adicional=$pago+['pagada_en'=>now(),'pago_usuario_id'=>Auth::id()];
            }
            if($datos['estado']==='en_espera')$adicional['espera_en']=now();
            if($datos['estado']==='finalizada' && trim($datos['registro']??'')==='')throw ValidationException::withMessages(['registro'=>'Describe el servicio realizado antes de finalizar.']);
            DB::table('peluqueria_citas')->where('id',$cita)->update($adicional+['estado'=>$datos['estado'],'registro'=>$datos['registro']??$actual->registro,'finalizada_en'=>$datos['estado']==='finalizada'?now():null,'updated_at'=>now()]);
        });
        if($request->expectsJson()){
            $actualizada=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')->where('c.peluqueria_id',$perfil->id)->where('c.id',$cita)->select('c.*','m.mascota','m.especie','m.tutor','m.rut','m.telefono','m.email')->first();
            return response()->json(['mensaje'=>'Reserva actualizada.','reserva'=>$this->datosReserva($actualizada,$perfil),'ficha'=>$datos['estado']==='en_atencion'?route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.ficha',$cita):null]);
        }
        if ($datos['estado'] === 'en_atencion') return redirect()->route(app(\App\Services\PeluqueriaAcceso::class)->rutaBase().'.ficha',$cita)->with('mensaje','Atención iniciada. Completa la ficha de peluquería.');
        return back()->with('mensaje','Atención actualizada.');
    }
}
