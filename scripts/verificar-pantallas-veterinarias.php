<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth,DB,Mail,Notification,Queue};
use App\Models\{User,Profesional,Asistente,Paciente,Instituciones,HoraMedica};
config(['session.driver'=>'array','app.debug'=>false]); Mail::fake(); Notification::fake(); Queue::fake();
if(DB::getDriverName() !== 'mysql') throw new RuntimeException('Esta comprobación exige MySQL transaccional.');
DB::connection()->beforeExecuting(function ($sql) {
    if (!preg_match('/^\\s*(select|show|describe|explain|savepoint|release\\s+savepoint)\\b/i', $sql)) {
        throw new RuntimeException('La revisión bloqueó una escritura: '.strtok(trim($sql), ' '));
    }
});
$casos=[];
foreach([['asistente',Asistente::class,'asistentes.perfil'],['profesional',Profesional::class,'profesional.mi_perfil'],['tutor',Paciente::class,'paciente.perfil'],['mascotas',Paciente::class,'paciente.mascotas.index']] as [$nombre,$modelo,$ruta]) {
    $perfil=$modelo::whereIn('id_usuario',User::select('id'))->orderBy('id')->first();
    if($perfil)$casos[]=[$nombre,$perfil->id_usuario,$ruta,[]];
}
foreach(User::role(['Admin','Institucion','Adm_Institucion','AsistenteAdm','Profesional'])->orderBy('id')->cursor() as $usuarioCentro) {
    Auth::guard('web')->setUser($usuarioCentro);
    $centro=app(\App\Services\PerfilCentroMedico::class)->permitidos()->first();
    if($centro){$casos[]=['centro',$usuarioCentro->id,'adm_cm.perfil_cm',['id_institucion'=>$centro->id]];break;}
}
Auth::forgetGuards();
$vistos=[];
foreach(HoraMedica::whereNotNull('id_mascota')->whereNotNull('id_ficha_atencion')->whereIn('id_estado',[5,6])->orderByDesc('id')->limit(300)->get() as $hora){
    $prof=Profesional::find($hora->id_profesional);if(!$prof||!User::find($prof->id_usuario)?->hasRole('Profesional'))continue;
    $mascota=\App\Models\Mascota::find($hora->id_mascota);if(!$mascota||(int)$mascota->id_responsable!==(int)$hora->id_paciente)continue;
    $tipo=\App\Support\FichaVeterinariaVista::para($prof);if(isset($vistos[$tipo]))continue;$vistos[$tipo]=true;
    $casos[]=[$tipo,$prof->id_usuario,'profesional.realizar_consulta',['id_hora_realizar'=>$hora->id,'lugar_atencion_id'=>$hora->id_lugar_atencion,'id_mascota'=>$hora->id_mascota]];
}
$resultados=[];
foreach($casos as [$nombre,$usuario,$ruta,$parametros]){
    DB::beginTransaction();
    try{
        Auth::forgetGuards();Auth::guard('web')->setUser(User::findOrFail($usuario));
        $request=\Illuminate\Http\Request::create(route($ruta,$parametros),'GET');
        $response=$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
        $registro=['pantalla'=>$nombre,'estado'=>$response->getStatusCode(),'bytes'=>strlen($response->getContent())];
        if(isset($response->exception))$registro['error']=substr(preg_replace('/\s+/',' ',explode('(SQL:', $response->exception->getMessage())[0]),0,450);
        if($response->isRedirection())$registro['destino']=parse_url($response->headers->get('Location'),PHP_URL_PATH);
        $resultados[]=$registro;
    }catch(Throwable $e){$resultados[]=['pantalla'=>$nombre,'estado'=>500,'error'=>substr(preg_replace('/\s+/',' ',explode('(SQL:', $e->getMessage())[0]),0,450)];}
    finally{while(DB::transactionLevel()>0)DB::rollBack();Auth::forgetGuards();}
}
echo json_encode($resultados,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains(fn($r)=>$r['estado']!==200)?1:0);
