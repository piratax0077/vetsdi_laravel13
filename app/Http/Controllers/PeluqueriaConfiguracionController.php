<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Services\PeluqueriaAcceso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Auth};
use Illuminate\Validation\{Rule,ValidationException};
class PeluqueriaConfiguracionController extends Controller
{
 public function __construct(private PeluqueriaAcceso $acceso){}
 public function contexto(Request $request){$request->validate(['peluqueria_id'=>'required|integer']);$p=$this->acceso->opciones()->firstWhere('id',(int)$request->peluqueria_id);abort_unless($p,403);$request->session()->put('peluqueria_contexto',$p->id);return redirect()->route($this->acceso->rutaBase().'.home');}
 public function personal(Request $request){
  $p=$this->acceso->perfil();$this->acceso->exigir($p,'personal');
  $request->merge(['permisos'=>$request->input('permisos',[])]);
  $datos=$request->validate(['id'=>'nullable|integer','email'=>'required|email|max:255','cargo'=>'required|string|max:100','sucursal_id'=>'nullable|integer','activo'=>'required|boolean','permisos'=>'present|array','permisos.*'=>['string',Rule::in(array_keys(config('peluqueria.permisos')))]]);
  $existente=!empty($datos['id'])?DB::table('peluqueria_personal')->where('peluqueria_id',$p->id)->where('id',$datos['id'])->first():null;
  abort_if(!empty($datos['id'])&&!$existente,404);
  $usuario=$existente?User::findOrFail($existente->usuario_id):User::whereRaw('LOWER(email) = ?',[mb_strtolower(trim($datos['email']))])->first();
  if(!$usuario)throw ValidationException::withMessages(['email'=>'El personal debe tener una cuenta registrada. Ingresa su correo de acceso.']);
  if((int)$usuario->id===(int)$p->id_usuario)throw ValidationException::withMessages(['email'=>'El propietario ya tiene todos los permisos.']);
  if(!empty($datos['sucursal_id']))$this->acceso->sucursal($p,$datos['sucursal_id'],false);
  $permisos=array_values(array_unique($datos['permisos']));
  $dependencias=['reservar'=>['ver_agenda','ver_clientes'],'confirmar'=>['ver_agenda'],'anular'=>['ver_agenda'],'atender'=>['ver_agenda','ver_clientes'],'editar_clientes'=>['ver_clientes']];
  foreach($dependencias as $permiso=>$requeridos)if(in_array($permiso,$permisos,true))$permisos=array_merge($permisos,$requeridos);
  $valores=['cargo'=>$datos['cargo'],'sucursal_id'=>$datos['sucursal_id']??null,'activo'=>$datos['activo'],'permisos'=>json_encode(array_values(array_unique($permisos))),'updated_at'=>now()];
  $previo=$existente??DB::table('peluqueria_personal')->where('peluqueria_id',$p->id)->where('usuario_id',$usuario->id)->first();
  if($previo)DB::table('peluqueria_personal')->where('id',$previo->id)->update($valores);
  else DB::table('peluqueria_personal')->insert($valores+['peluqueria_id'=>$p->id,'usuario_id'=>$usuario->id,'created_at'=>now()]);
  if(in_array('atender',$permisos,true))\App\Models\ProfesionalEstetica::firstOrCreate(['id_usuario'=>$usuario->id],['nombre'=>$usuario->name,'telefono'=>$usuario->telefono]);
  return redirect()->route($this->acceso->rutaBase().'.configuracion',['seccion'=>'personal'])->with('mensaje','Acceso y permisos del personal guardados.');
 }
 public function sucursal(Request $request){
  $p=$this->acceso->perfil();$this->acceso->exigir($p,'sucursales');
  $datos=$request->validate(['id'=>'nullable|integer','nombre'=>'required|string|max:150','direccion'=>'required|string|max:255','telefono'=>'nullable|string|max:30','activo'=>'required|boolean','capacidad'=>'required|integer|min:1|max:20']);
  $id=$datos['id']??null;unset($datos['id']);
  abort_if(!$id && $this->acceso->miembro($p)?->sucursal_id,403,'Solo puedes administrar tu sucursal asignada.');
  if($id){$this->acceso->sucursal($p,$id,false);DB::table('peluqueria_sucursales')->where('id',$id)->where('peluqueria_id',$p->id)->update($datos+['updated_at'=>now()]);}
  else DB::table('peluqueria_sucursales')->insert($datos+['peluqueria_id'=>$p->id,'created_at'=>now(),'updated_at'=>now()]);
  return redirect()->route($this->acceso->rutaBase().'.configuracion',['seccion'=>'sucursales'])->with('mensaje','Sucursal guardada.');
 }
 public function agenda(Request $request){
  $p=$this->acceso->perfil();$this->acceso->exigir($p,'agenda');
  if($request->has('tramos')){
   $datos=$request->validate(['sucursal_id'=>'required|integer','tramos'=>'required|array|min:1|max:35','tramos.*.dia'=>'required|integer|between:1,7','tramos.*.inicio'=>'required|date_format:H:i','tramos.*.fin'=>'required|date_format:H:i','anticipacion_horas'=>'required|integer|min:0|max:720','anticipacion_dias'=>'required|integer|min:1|max:365']);
   $porDia=[];
   foreach($datos['tramos'] as $i=>$tramo){
    if($tramo['fin']<=$tramo['inicio'])throw ValidationException::withMessages(['tramos'=>'Cada tramo debe terminar después de su hora de inicio.']);
    foreach($porDia[(int)$tramo['dia']]??[] as $otro)if($tramo['inicio']<$otro['fin']&&$tramo['fin']>$otro['inicio'])throw ValidationException::withMessages(['tramos'=>'Los tramos de un mismo día no pueden superponerse.']);
    $datos['tramos'][$i]['dia']=(int)$tramo['dia'];$porDia[(int)$tramo['dia']][]=$tramo;
   }
   $datos['tramos']=array_values($datos['tramos']);
  }else{
   $datos=$request->validate(['sucursal_id'=>'required|integer','dias'=>'required|array|min:1|max:7','dias.*'=>'integer|between:1,7|distinct','apertura'=>'required|date_format:H:i','cierre'=>'required|date_format:H:i|after:apertura','pausa_inicio'=>'nullable|required_with:pausa_fin|date_format:H:i|after_or_equal:apertura|before:cierre','pausa_fin'=>'nullable|required_with:pausa_inicio|date_format:H:i|after:pausa_inicio|before_or_equal:cierre','anticipacion_horas'=>'required|integer|min:0|max:720','anticipacion_dias'=>'required|integer|min:1|max:365']);
   $datos['dias']=array_map('intval',$datos['dias']);
  }
  $s=$this->acceso->sucursal($p,$datos['sucursal_id'],false);unset($datos['sucursal_id']);
  foreach(['anticipacion_horas','anticipacion_dias'] as $campo)$datos[$campo]=(int)$datos[$campo];
  if($datos['anticipacion_horas']>24*$datos['anticipacion_dias'])throw ValidationException::withMessages(['anticipacion_dias'=>'El plazo máximo debe superar la anticipación mínima.']);
  DB::table('peluqueria_sucursales')->where('id',$s->id)->update(['agenda'=>json_encode($datos),'updated_at'=>now()]);
  return redirect()->route($this->acceso->rutaBase().'.configuracion',['seccion'=>'agenda'])->with('mensaje','Horario de agenda guardado.');
 }
 public function bloqueo(Request $request){
  $p=$this->acceso->perfil();$this->acceso->exigir($p,'agenda');
  $datos=$request->validate(['sucursal_id'=>'required|integer','inicio'=>'required|date_format:Y-m-d\TH:i','fin'=>'required|date_format:Y-m-d\TH:i|after:inicio','motivo'=>'required|string|max:255']);
  $this->acceso->sucursal($p,$datos['sucursal_id'],false);
  DB::table('peluqueria_bloqueos')->insert(['peluqueria_id'=>$p->id,'sucursal_id'=>$datos['sucursal_id'],'inicio'=>Carbon::parse($datos['inicio'],config('peluqueria.timezone')),'fin'=>Carbon::parse($datos['fin'],config('peluqueria.timezone')),'motivo'=>$datos['motivo'],'created_at'=>now(),'updated_at'=>now()]);
  return redirect()->route($this->acceso->rutaBase().'.configuracion',['seccion'=>'agenda'])->with('mensaje','Bloqueo agregado. Las reservas existentes se conservan.');
 }
 public function quitarBloqueo(int $id){
  $p=$this->acceso->perfil();$this->acceso->exigir($p,'agenda');$bloqueo=DB::table('peluqueria_bloqueos')->where('peluqueria_id',$p->id)->where('id',$id)->first();abort_unless($bloqueo,404);$this->acceso->sucursal($p,$bloqueo->sucursal_id,false);
  DB::table('peluqueria_bloqueos')->where('id',$id)->delete();return redirect()->route($this->acceso->rutaBase().'.configuracion',['seccion'=>'agenda'])->with('mensaje','Bloqueo retirado.');
 }
}
