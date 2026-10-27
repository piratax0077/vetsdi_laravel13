<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth,DB,Mail,Notification,Queue};
config(['session.driver'=>'array','app.debug'=>false]);Mail::fake();Notification::fake();Queue::fake();
DB::connection()->beforeExecuting(function($sql){if(!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql))throw new RuntimeException('Revisión: escritura bloqueada.');});
$p=App\Models\Peluqueria::whereIn('id_usuario',DB::table('usuario_roles')->where('tipo','peluqueria')->where('estado',1)->select('id_usuario'))->firstOrFail();
Auth::guard('web')->setUser(App\Models\User::findOrFail($p->id_usuario));
$s=DB::table('peluqueria_sucursales')->where('peluqueria_id',$p->id)->where('activo',1)->first();
$fecha=DB::table('peluqueria_citas')->where('peluqueria_id',$p->id)->value('inicio');
$request=Illuminate\Http\Request::create(route('peluqueria.agenda.ver',['sucursal_id'=>$s?->id,'agenda_personal'=>'titular','fecha'=>$fecha?substr($fecha,0,10):date('Y-m-d')]),'GET');
$response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
$html=$response->getContent();
$resultado=['estado'=>$response->getStatusCode(),'busqueda_rut'=>str_contains($html,'reserva-buscar-tutor'),'pasos_reserva'=>str_contains($html,'peluqueria-estado-modal'),'sin_directivas_pendientes'=>!str_contains($html,'@endif')];
if(isset($response->exception))$resultado['error']=substr(explode('(SQL:',$response->exception->getMessage())[0],0,180);
echo json_encode($resultado,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($resultado['estado']===200&&$resultado['pasos_reserva']?0:1);
