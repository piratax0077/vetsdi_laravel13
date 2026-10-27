<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{User,Profesional,Paciente,Asistente};
use Illuminate\Support\Facades\{Auth,DB,Mail,Notification,Queue};
config(['session.driver'=>'array','app.debug'=>false]);Mail::fake();Notification::fake();Queue::fake();
DB::connection()->beforeExecuting(function($sql){if(!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql))throw new RuntimeException('Verificación: escritura bloqueada.');});
$casos=[];
$p=Profesional::whereIn('id_usuario',User::role('Profesional')->select('id'))->whereIn('id',DB::table('profesional_horarios')->select('id_profesional'))->first();
if($p)$casos[]=['rol'=>'profesional','usuario'=>$p->id_usuario,'ruta'=>'profesional.mi_agenda','parametros'=>['lugares_atencion'=>DB::table('profesional_horarios')->where('id_profesional',$p->id)->value('id_lugar_atencion')],'sesion'=>[]];
$t=Paciente::whereIn('id_usuario',User::role('Paciente')->select('id'))->first();
if($t)$casos[]=['rol'=>'tutor','usuario'=>$t->id_usuario,'ruta'=>'paciente.home','parametros'=>[],'sesion'=>[]];
$servicio=app(App\Services\AutorizacionesAsistentes::class);
$rolesAsistente=DB::table('roles')->where('guard_name','web')->whereIn('name',config('asistentes.roles'))->pluck('name')->all();
foreach(Asistente::whereIn('id_usuario',User::role($rolesAsistente)->select('id'))->limit(30)->get() as $a){
 foreach($servicio->contextos($a) as $contexto){
  if(!($servicio->permisos($a->id,$contexto['id_lugar_atencion'],$contexto['id_profesional'])['permiso_ver_agenda']??false))continue;
  $casos[]=['rol'=>'asistente','usuario'=>$a->id_usuario,'ruta'=>'asistentes.agenda','parametros'=>[],'sesion'=>['asistente_contexto'=>$contexto]];break 2;
 }
}
$resultados=[];
foreach($casos as $caso){
 Auth::forgetGuards();Auth::guard('web')->setUser(User::findOrFail($caso['usuario']));
 $session=$app['session']->driver();$session->flush();$session->put($caso['sesion']);$session->save();
 $request=Illuminate\Http\Request::create(route($caso['ruta'],$caso['parametros']),'GET',[],[config('session.cookie')=>$session->getId()]);
 $response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
 $fila=['rol'=>$caso['rol'],'ruta'=>$caso['ruta'],'estado'=>$response->getStatusCode(),'bytes'=>strlen($response->getContent())];
 if(isset($response->exception))$fila['error']=substr(explode('(SQL:',$response->exception->getMessage())[0],0,220);
 $resultados[]=$fila;
}
if(!collect($casos)->contains(fn($c)=>$c['rol']==='asistente'))$resultados[]=['rol'=>'asistente','estado'=>'sin cuenta local con permiso de agenda; permisos comprobados en pruebas SQLite'];
echo json_encode($resultados,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains(fn($r)=>is_int($r['estado'])&&$r['estado']>=400)?1:0);
