<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{Auth,DB,Http,Mail,Notification,Queue};
config(['session.driver'=>'array','app.debug'=>false]);
Mail::fake(); Notification::fake(); Queue::fake(); Http::preventStrayRequests();
DB::connection()->beforeExecuting(function($sql) {
    if (!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql)) throw new RuntimeException('Escritura bloqueada durante la revision.');
});
try {
    $ids=App\Models\Profesional::where('id_especialidad',2)->pluck('id_usuario');
    $user=App\Models\User::role('Profesional')->whereIn('id',$ids)->first();
    if (!$user) { echo "Sin cuenta local de odontologia veterinaria\n"; exit; }
    $profesional=App\Models\Profesional::where('id_usuario',$user->id)->firstOrFail();
    $hora=App\Models\HoraMedica::where('id_profesional',$profesional->id)->whereNotNull('id_mascota')->whereExists(function($q){$q->selectRaw('1')->from('mascotas')->whereColumn('mascotas.id','horas_medicas.id_mascota')->whereColumn('mascotas.id_responsable','horas_medicas.id_paciente');})->orderByDesc('id')->first();
    $cookies=[];
    foreach (['profesional.home','profesional.mi_perfil','profesional.pacientes','profesional.lugares_atencion','profesional.configuracion','profesional.mis_asistentes','profesional.hospitalizacion','profesional.realizar_consulta'] as $name) {
        if ($name==='profesional.realizar_consulta' && !$hora) {echo "Ficha: sin hora propia para comprobar
";continue;}
        $params=$name==='profesional.realizar_consulta'?['id_hora_realizar'=>$hora->id]:[];
        Auth::forgetGuards(); Auth::guard('web')->setUser($user);
        $response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create(route($name,$params),'GET',[],$cookies));
        foreach($response->headers->getCookies() as $cookie) $cookies[$cookie->getName()]=$cookie->getValue();
        echo $name,': ',$response->getStatusCode();
        if (isset($response->exception)) echo ' ',get_class($response->exception),' ',substr(explode('(SQL:',$response->exception->getMessage())[0],0,220);
        if ($response->isRedirection()) echo ' -> ',parse_url($response->headers->get('Location'),PHP_URL_PATH);
        echo "\n";
        if ($name==='profesional.realizar_consulta' && $response->getStatusCode()===200) {
            foreach (['ficha-odontologia-vet','vet-odontograma','vet-periodonto','vet-presupuestos','vet-pago-form'] as $id) {
                $count=substr_count($response->getContent(),'id="'.$id.'"');
                echo 'Elemento ',$id,': ',$count===1?'OK':'ERROR ('.$count.')',"\n";
            }
        }
    }
} catch (Throwable $e) { echo get_class($e),': ',substr(explode('(SQL:',$e->getMessage())[0],0,220),"\n"; exit(1); }
