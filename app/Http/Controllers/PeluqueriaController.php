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

    public function ficha(int $cita) {
        $perfil = $this->perfil();
        $atencion = DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->first();
        abort_unless($atencion,404);
        $cliente = DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->where('id',$atencion->cliente_id)->first();
        abort_unless($cliente,404);
        $guardada = DB::table('peluqueria_fichas')->where('cita_id',$cita)->where('peluqueria_id',$perfil->id)->first();
        $datos = $guardada ? json_decode($guardada->datos,true) : [
            'mascota'=>$cliente->mascota,'especie'=>$cliente->especie,'raza'=>$cliente->raza,'esterilizada'=>'desconocido',
            'observaciones'=>$atencion->registro ?: $cliente->cuidados,
            'servicios'=>[['nombre'=>$atencion->servicio,'precio'=>(int)$atencion->valor]],
        ];
        if (!$guardada && $atencion->procedimiento_id) $datos['servicios'][0]['procedimiento_id'] = $atencion->procedimiento_id;
        $procedimientos=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->where('activo',true)->orderBy('nombre')->get();
        $anteriores=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('cliente_id',$cliente->id)
            ->where('estado','finalizada')
            ->orderByDesc('finalizada_en')->orderByDesc('inicio')->orderByDesc('id')->paginate(15,['*'],'historial_pagina');
        return view('app.peluqueria.ficha', compact('perfil','atencion','cliente','datos','procedimientos','anteriores'));
    }

    public function fichaGuardar(Request $request, int $cita) {
        $perfil = $this->perfil();
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
        $citaActual=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->first();
        abort_unless($citaActual,404);
        $fichaAnterior=DB::table('peluqueria_fichas')->where('cita_id',$cita)->where('peluqueria_id',$perfil->id)->first();
        $anteriores=$fichaAnterior ? (json_decode($fichaAnterior->datos,true)['servicios']??[]) : [['nombre'=>$citaActual->servicio,'precio'=>(int)$citaActual->valor]];
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
            abort_unless(in_array($actual->estado,['reservada','confirmada','en_atencion'],true),422,'La atención ya está finalizada o cancelada.');
            $finalizar = $datos['accion'] === 'finalizar'; unset($datos['accion']);
            $datos['total'] = $total;
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
        return redirect()->route('peluqueria.ficha',$cita)->with('mensaje','Ficha guardada correctamente.');
    }

    private function perfil(): Peluqueria {
        abort_unless(app(CuentasService::class)->tieneRol(Auth::user(),'peluqueria'),403);
        return Peluqueria::where('id_usuario',Auth::id())->firstOrFail();
    }
    public function index(Request $request) {
        $perfil=$this->perfil();
        $datos=$request->validate(['fecha'=>'nullable|date_format:Y-m-d']);
        $fecha=$datos['fecha']??now()->toDateString();
        $clientes=DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->orderBy('mascota')->get();
        $citas=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')
            ->where('c.peluqueria_id',$perfil->id)->whereDate('c.inicio',$fecha)->orderBy('c.inicio')
            ->select('c.*','m.mascota','m.tutor','m.telefono','m.cuidados')->get();
        $historial=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')
            ->where('c.peluqueria_id',$perfil->id)->where('c.estado','finalizada')->orderByDesc('c.finalizada_en')
            ->select('c.*','m.mascota','m.tutor')->paginate(15);
        $procedimientos=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->where('activo',true)->orderBy('nombre')->get();
        $seccion = match($request->route()?->getName()) {'peluqueria.mascotas'=>'clientes','peluqueria.historial'=>'registro',default=>'agenda'};
        $vista = $request->routeIs('peluqueria.home') || !$request->route() ? 'escritorio' : 'agenda';
        return view('app.peluqueria.'.$vista,compact('perfil','fecha','clientes','citas','historial','procedimientos','seccion'));
    }
    public function configuracion() {
        $perfil=$this->perfil();
        $procedimientos=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->orderBy('nombre')->get();
        return view('app.peluqueria.configuracion',compact('perfil','procedimientos'));
    }
    public function eventos(Request $request) {
        $perfil=$this->perfil();
        $datos=$request->validate(['start'=>'required|date','end'=>'required|date|after:start']);
        $inicio=Carbon::parse($datos['start']);$fin=Carbon::parse($datos['end']);
        abort_if($inicio->diffInDays($fin)>93,422);
        $citas=DB::table('peluqueria_citas as c')->join('peluqueria_clientes as m','m.id','=','c.cliente_id')
            ->where('c.peluqueria_id',$perfil->id)->where('c.estado','<>','cancelada')->where('c.inicio','<',$fin)->where('c.fin','>',$inicio)
            ->select('c.*','m.mascota')->get();
        return response()->json($citas->map(fn($c)=>[
            'id'=>$c->id,'title'=>$c->mascota.' · '.$c->servicio,'start'=>Carbon::parse($c->inicio)->format('Y-m-d\TH:i:s'),
            'end'=>Carbon::parse($c->fin)->format('Y-m-d\TH:i:s'),'url'=>route('peluqueria.ficha',$c->id),
            'color'=>match($c->estado){'finalizada'=>'#31a69c','en_atencion'=>'#7043b5',default=>'#df9b25'},
        ]));
    }
    public function procedimientoGuardar(Request $request) {
        $perfil=$this->perfil();
        $datos=$request->validate(['id'=>'nullable|integer','nombre'=>'required|string|max:150','duracion'=>'required|integer|min:15|max:480','precio'=>'required|integer|min:0|max:999999999','activo'=>'required|boolean','indicaciones'=>'nullable|string|max:3000']);
        $id=$datos['id']??null;unset($datos['id']);
        if($id){abort_unless(DB::table('peluqueria_procedimientos')->where('id',$id)->where('peluqueria_id',$perfil->id)->exists(),404);DB::table('peluqueria_procedimientos')->where('id',$id)->where('peluqueria_id',$perfil->id)->update($datos+['updated_at'=>now()]);}
        else DB::table('peluqueria_procedimientos')->insert($datos+['peluqueria_id'=>$perfil->id,'created_at'=>now(),'updated_at'=>now()]);
        return redirect()->route('peluqueria.configuracion')->with('mensaje','Procedimiento guardado.');
    }
    public function perfilGuardar(Request $request) {
        $perfil=$this->perfil();$datos=$request->validate(['nombre'=>'required|string|max:255','telefono'=>'required|string|max:30','direccion'=>'required|string|max:255']);
        $perfil->fill($datos)->saveOrFail();return back()->with('mensaje','Perfil actualizado.');
    }
    public function clienteGuardar(Request $request) {
        $perfil=$this->perfil();
        $datos=$request->validate(['tutor'=>'required|string|max:255','telefono'=>'required|string|max:30','email'=>'nullable|email|max:255','mascota'=>'required|string|max:100','especie'=>'required|string|max:60','raza'=>'nullable|string|max:100','cuidados'=>'nullable|string|max:3000']);
        DB::table('peluqueria_clientes')->insert($datos+['peluqueria_id'=>$perfil->id,'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('mensaje','Tutor y mascota registrados.');
    }
    public function citaGuardar(Request $request) {
        $perfil=$this->perfil();
        $nuevoCliente=null;
        if($request->boolean('nuevo_cliente')) {
            $nuevoCliente=$request->validate(['tutor'=>'required|string|max:255','telefono'=>'required|string|max:30','email'=>'nullable|email|max:255','mascota'=>'required|string|max:100','especie'=>'required|string|max:60','raza'=>'nullable|string|max:100','cuidados'=>'nullable|string|max:3000']);
            $request->merge(['cliente_id'=>0]);
        }
        $request->validate(['procedimiento_id'=>'nullable|integer']);
        $procedimiento=null;
        if($request->filled('procedimiento_id')) {
            $procedimiento=DB::table('peluqueria_procedimientos')->where('peluqueria_id',$perfil->id)->where('id',$request->procedimiento_id)->where('activo',true)->first();
            abort_unless($procedimiento,404);
            $request->merge(['servicio'=>'Peluquería','valor'=>$procedimiento->precio,'duracion'=>$procedimiento->duracion]);
        }
        $datos=$request->validate(['cliente_id'=>'required|integer','inicio'=>'required|date_format:Y-m-d\TH:i|after_or_equal:now','duracion'=>'required|integer|min:15|max:480','servicio'=>'required|in:Baño,Belleza,Peluquería','valor'=>'required|integer|min:0|max:999999999','observaciones'=>'nullable|string|max:3000']);
        if($procedimiento){$datos['servicio']=$procedimiento->nombre;$datos['procedimiento_id']=$procedimiento->id;}
        $citaId=DB::transaction(function()use($perfil,$datos,$nuevoCliente){
            Peluqueria::whereKey($perfil->id)->lockForUpdate()->firstOrFail();
            if(!$nuevoCliente) abort_unless(DB::table('peluqueria_clientes')->where('peluqueria_id',$perfil->id)->where('id',$datos['cliente_id'])->exists(),404);
            $inicio=Carbon::parse($datos['inicio']);$fin=$inicio->copy()->addMinutes((int)$datos['duracion']);
            if(DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('estado','<>','cancelada')->where('inicio','<',$fin)->where('fin','>',$inicio)->exists())
                throw ValidationException::withMessages(['inicio'=>'Ya existe una reserva en ese horario.']);
            unset($datos['duracion']);$datos['inicio']=$inicio;$datos['fin']=$fin;
            if($nuevoCliente) $datos['cliente_id']=DB::table('peluqueria_clientes')->insertGetId($nuevoCliente+['peluqueria_id'=>$perfil->id,'created_at'=>now(),'updated_at'=>now()]);
            return DB::table('peluqueria_citas')->insertGetId($datos+['peluqueria_id'=>$perfil->id,'estado'=>'reservada','created_at'=>now(),'updated_at'=>now()]);
        });
        if($request->expectsJson()) {
            $cliente=DB::table('peluqueria_clientes as m')->join('peluqueria_citas as c','c.cliente_id','=','m.id')->where('c.id',$citaId)->where('c.peluqueria_id',$perfil->id)->select('m.id','m.mascota','m.tutor')->first();
            return response()->json(['estado'=>1,'mensaje'=>'Hora reservada correctamente.','cita_id'=>$citaId,'ficha_url'=>route('peluqueria.ficha',$citaId),'cliente'=>$cliente]);
        }
        return back()->with('mensaje','Hora reservada.');
    }
    public function estado(Request $request,int $cita) {
        $perfil=$this->perfil();$datos=$request->validate(['estado'=>'required|in:confirmada,en_atencion,finalizada,cancelada','registro'=>'nullable|string|max:5000']);
        DB::transaction(function()use($perfil,$cita,$datos){
            $actual=DB::table('peluqueria_citas')->where('peluqueria_id',$perfil->id)->where('id',$cita)->lockForUpdate()->first();abort_unless($actual,404);
            $permitidos=['reservada'=>['confirmada','en_atencion','cancelada'],'confirmada'=>['en_atencion','cancelada'],'en_atencion'=>['finalizada','cancelada']];
            if(!in_array($datos['estado'],$permitidos[$actual->estado]??[],true))throw ValidationException::withMessages(['estado'=>'Ese cambio de estado no está permitido.']);
            if($datos['estado']==='finalizada' && trim($datos['registro']??'')==='')throw ValidationException::withMessages(['registro'=>'Describe el servicio realizado antes de finalizar.']);
            DB::table('peluqueria_citas')->where('id',$cita)->update(['estado'=>$datos['estado'],'registro'=>$datos['registro']??$actual->registro,'finalizada_en'=>$datos['estado']==='finalizada'?now():null,'updated_at'=>now()]);
        });
        if ($datos['estado'] === 'en_atencion') {
            return redirect()->route('peluqueria.ficha',$cita)->with('mensaje','Atención iniciada. Completa la ficha de peluquería.');
        }
        return back()->with('mensaje','Atención actualizada.');
    }
}
