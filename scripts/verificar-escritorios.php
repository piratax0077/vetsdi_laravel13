<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth,DB,Http,Mail,Notification,Queue};
use App\Models\{User,Paciente,Profesional,Asistente,Peluqueria};
config(['session.driver'=>'array','app.debug'=>false]);
Mail::fake(); Notification::fake(); Queue::fake(); Http::preventStrayRequests();
DB::connection()->beforeExecuting(function ($sql) {
    if (!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql)) throw new RuntimeException('Escritura bloqueada durante la revisión.');
});
$casos=[];
foreach ([
    [Paciente::class,'Paciente',['paciente.home','paciente.perfil','paciente.mascotas.index','paciente.agendar_hora','paciente.mis_profesionales','paciente.acceso_pni','paciente.referidos.index']],
    [Profesional::class,'Profesional',['profesional.home','profesional.mi_perfil','profesional.pacientes','profesional.lugares_atencion','profesional.configuracion','profesional.mis_asistentes','profesional.hospitalizacion','profesional.referidos.index']],
    [Asistente::class,'Asistente',['asistentes.home','asistentes.perfil','asistentes.agenda','asistentes.pacientes','asistentes.permisos']],
    [Peluqueria::class,'Peluqueria',['peluqueria.home','peluqueria.agenda.ver','peluqueria.mascotas','peluqueria.historial','peluqueria.configuracion']],
] as [$modelo,$rol,$rutas]) {
    $usuario=User::role($rol)->whereIn('id',$modelo::select('id_usuario'))->orderBy('id')->first();
    if ($modelo===Asistente::class) {
        foreach (User::role($rol)->whereIn('id',$modelo::select('id_usuario'))->cursor() as $candidato) {
            $asistente=Asistente::where('id_usuario',$candidato->id)->first();
            if (app(\App\Services\AutorizacionesAsistentes::class)->contextos($asistente)->isNotEmpty()) { $usuario=$candidato; break; }
        }
    }
    foreach ($rutas as $ruta) $casos[]=[$rol,$usuario?->id,$ruta,[]];
}
$candidatos=User::role(['Admin','Institucion','Adm_Institucion','AsistenteAdm','Profesional'])->get()->sortByDesc(fn($u)=>\App\Models\UsuarioRol::where('id_usuario',$u->id)->where('tipo','clinica')->where('perfil_completo',true)->exists());
foreach ($candidatos as $usuario) {
    Auth::forgetGuards(); Auth::guard('web')->setUser($usuario);
    $centro=app(\App\Services\PerfilCentroMedico::class)->permitidos()->first();
    if (!$centro) continue;
    foreach (['adm_cm.home','adm_cm.perfil_cm','adm_cm.usuarios','adm_cm.configuracion','adm_cm.profesionales','adm_cm.departamentos_servicios'] as $ruta) $casos[]=['clinica',$usuario->id,$ruta,['id_institucion'=>$centro->id]];
    break;
}
$resultados=[]; $cookies=[];
foreach ($casos as [$perfil,$id,$ruta,$params]) {
    $resultado=['perfil'=>$perfil,'ruta'=>$ruta];
    if (!$id) { $resultado['pendiente']='Sin cuenta local de este perfil'; $resultados[]=$resultado; continue; }
    try {
        Auth::forgetGuards(); Auth::guard('web')->setUser(User::findOrFail($id));
        $response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create(route($ruta,$params),'GET',[], $cookies[$id]??[]));
        foreach ($response->headers->getCookies() as $cookie) $cookies[$id][$cookie->getName()]=$cookie->getValue();
        $resultado['estado']=$response->getStatusCode();
        if (isset($response->exception)) $resultado['error']=substr(explode('(SQL:',$response->exception->getMessage())[0],0,300);
        if ($response->isRedirection()) $resultado['destino']=parse_url($response->headers->get('Location'),PHP_URL_PATH);
    } catch (Throwable $e) { $resultado['estado']=500; $resultado['error']=substr(explode('(SQL:',$e->getMessage())[0],0,300); }
    $resultados[]=$resultado;
}
echo json_encode($resultados,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains(fn($r)=>($r['estado']??0)>=500)?1:0);
