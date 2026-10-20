<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB,Auth};
DB::connection()->beforeExecuting(function($sql){if(!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql))throw new RuntimeException('Escritura bloqueada');});
$informes=App\Models\InformeMedico::whereDate('created_at','2025-07-07')->limit(10)->get();
if($informes->isEmpty())$informes=App\Models\InformeMedico::orderByDesc('id')->limit(3)->get();
$fallos=0;
foreach($informes as $informe){
$ficha=App\Models\FichaAtencion::find($informe->id_ficha_atencion);if(!$ficha)continue;
$prof=App\Models\Profesional::find($ficha->id_profesional);Auth::guard('web')->setUser(App\Models\User::findOrFail($prof->id_usuario));
try{$response=app(App\Http\Controllers\ficha_atencionController::class)->pdf_informe_medico(Illuminate\Http\Request::create('/','GET',['id_informe_medico'=>$informe->id]));$valido=$response instanceof Symfony\Component\HttpFoundation\Response && str_starts_with($response->getContent(),'%PDF-');if(!$valido)$fallos++;echo json_encode(['informe'=>$informe->id,'ficha'=>$ficha->id,'tipo'=>$informe->id_tipo_informe,'pdf'=>$response instanceof Symfony\Component\HttpFoundation\Response ? str_starts_with($response->getContent(),'%PDF-') : false,'resultado'=>is_array($response)?$response:null]).PHP_EOL;}
catch(Throwable $e){$fallos++;echo json_encode(['informe'=>$informe->id,'ficha'=>$ficha->id,'error'=>$e->getMessage(),'archivo'=>basename($e->getFile()),'linea'=>$e->getLine()]).PHP_EOL;}
}

exit($fallos ? 1 : 0);
