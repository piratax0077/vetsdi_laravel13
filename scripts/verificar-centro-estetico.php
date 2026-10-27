<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth,DB,Mail,Notification,Queue};
config(['session.driver'=>'array','app.debug'=>false]);Mail::fake();Notification::fake();Queue::fake();
DB::connection()->beforeExecuting(function($sql){if(!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql))throw new RuntimeException('Verificación: escritura bloqueada.');});
$centro=App\Models\CentroEstetico::whereIn('id_usuario',DB::table('usuario_roles')->where('tipo','centro_estetico')->where('estado',1)->select('id_usuario'))->firstOrFail();
$usuario=App\Models\User::findOrFail($centro->id_usuario);$resultados=[];
foreach([['centro_estetico','centro_estetico.home',[],200],['centro_estetico','centro_estetico.configuracion',['seccion'=>'personal'],200],['centro_estetico','centro_estetico.configuracion',['seccion'=>'negocio'],200],['peluqueria','peluqueria.home',[],200],['peluqueria','peluqueria.configuracion',['seccion'=>'personal'],403]] as [$rol,$ruta,$parametros,$esperado]){
 Auth::forgetGuards();Auth::guard('web')->setUser($usuario);
 $session=$app['session']->driver();$session->flush();$session->put(['rol_activo'=>$rol,'peluqueria_contexto'=>$centro->id]);$session->save();
 $request=Illuminate\Http\Request::create(route($ruta,$parametros),'GET',[],[config('session.cookie')=>$session->getId()]);
 $respuesta=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
 $fila=['perfil'=>$rol,'ruta'=>$ruta,'seccion'=>$parametros['seccion']??null,'estado'=>$respuesta->getStatusCode(),'correcto'=>$respuesta->getStatusCode()===$esperado];
 if($respuesta->getStatusCode()>=500&&isset($respuesta->exception))$fila['error']=substr(explode('(SQL:',$respuesta->exception->getMessage())[0],0,180);
 $resultados[]=$fila;
}
echo json_encode($resultados,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains(fn($r)=>!$r['correcto'])?1:0);
