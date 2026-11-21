<?php
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth,DB,Http,Mail};
config(['session.driver'=>'array','cache.default'=>'array']);
Mail::fake(); Http::fake();
$salida=['entorno'=>[
 'debug'=>config('app.debug'),'timezone'=>config('app.timezone'),'mail_driver'=>config('mail.default'),
 'mail_redirigido'=>(bool)config('mail.redirect_to'),'jitsi_configurado'=>(bool)(config('services.jitsi.app_id')&&config('services.jitsi.api_key')&&config('services.jitsi.private_key')),
 'sesion_segura'=>(bool)config('session.secure'),'cola'=>config('queue.default'),
], 'perfiles'=>[], 'pantallas'=>[]];
$perfiles=[
 'tutor'=>['paciente.home','paciente.perfil','paciente.mascotas.index'],
 'profesional'=>['profesional.home','profesional.mi_perfil','profesional.configuracion','vet.inventario.index'],
 'clinica'=>['adm_cm.home','adm_cm.configuracion','adm_cm.perfil_cm','vet.inventario.index'],
 'asistente'=>['asistentes.perfil'],
 'guarderia_hotel'=>['guarderia_hotel.home','guarderia_hotel.configuracion'],
 'cementerio_mascotas'=>['cementerio_mascotas.home','cementerio_mascotas.configuracion','cementerio_mascotas.inventario'],
 'peluqueria'=>['peluqueria.home','peluqueria.configuracion'],
 'centro_estetico'=>['centro_estetico.home','centro_estetico.configuracion'],
];
$escrituras=0;
DB::connection()->beforeExecuting(function($sql) use (&$escrituras){
 if(!preg_match('/^\s*(select|show|pragma|explain)\b/i',$sql)) { $escrituras++; throw new RuntimeException('AUDITORIA_ESCRITURA_BLOQUEADA'); }
});
foreach($perfiles as $tipo=>$rutas){
 $cuenta=DB::table('usuario_roles')->where('tipo',$tipo)->where('estado',1)->orderByDesc('perfil_completo')->first();
 $salida['perfiles'][$tipo]=['cuenta_disponible'=>(bool)$cuenta,'perfil_completo'=>(bool)($cuenta->perfil_completo??false)];
 if(!$cuenta)continue;
 foreach($rutas as $nombre){
  $parametros=[];
  if($tipo==='clinica'){
   $id=DB::table('instituciones')->where('id_usuario',$cuenta->id_usuario)->value('id');
   if($id)$parametros['id_institucion']=$id;
  }
  $antes=$escrituras;
  Auth::forgetGuards(); Auth::guard()->setUser(App\Models\User::findOrFail($cuenta->id_usuario));
  try{
   $request=Illuminate\Http\Request::create(route($nombre,$parametros),'GET');
   $response=$kernel->handle($request);
   $salida['pantallas'][]=['perfil'=>$tipo,'ruta'=>$nombre,'http'=>$response->getStatusCode(),'escrituras_bloqueadas'=>$escrituras-$antes,
    'redireccion'=>$response->isRedirection()?parse_url($response->headers->get('Location'),PHP_URL_PATH):null];
  }catch(Throwable $e){$salida['pantallas'][]=['perfil'=>$tipo,'ruta'=>$nombre,'error'=>get_class($e),'escrituras_bloqueadas'=>$escrituras-$antes];}
 }
}
file_put_contents(__DIR__.'/smoke.json',json_encode($salida,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($salida,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
