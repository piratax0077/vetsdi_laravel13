<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth,DB,Mail,Notification,Queue};
config(['session.driver'=>'array','app.debug'=>false]);Mail::fake();Notification::fake();Queue::fake();
DB::connection()->beforeExecuting(function($sql){if(!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql))throw new RuntimeException('Revisión: escritura bloqueada.');});
$p=App\Models\Peluqueria::whereIn('id_usuario',DB::table('usuario_roles')->where('tipo','peluqueria')->where('estado',1)->select('id_usuario'))->firstOrFail();
$resultados=[];
foreach(['procedimientos','personal','agenda','sucursales','negocio'] as $seccion){
 Auth::forgetGuards();Auth::guard('web')->setUser(App\Models\User::findOrFail($p->id_usuario));
 $request=Illuminate\Http\Request::create(route('peluqueria.configuracion',['seccion'=>$seccion]),'GET');
 $response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
 $r=['seccion'=>$seccion,'estado'=>$response->getStatusCode(),'bytes'=>strlen($response->getContent())];
 if(isset($response->exception))$r['error']=substr(explode('(SQL:',$response->exception->getMessage())[0],0,200);
 $resultados[]=$r;
}
echo json_encode($resultados,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains(fn($r)=>$r['estado']!==200)?1:0);
