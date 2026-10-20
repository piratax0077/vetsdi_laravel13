<?php
require __DIR__.'/../vendor/autoload.php';$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
DB::connection()->beforeExecuting(function($sql){if(!preg_match('/^\s*(select|show|describe|explain)\b/i',$sql))throw new RuntimeException('Escritura bloqueada');});
foreach(App\Models\ProfesionalMiEquipo::orderByDesc('id')->limit(15)->get() as $e){
    $prof=App\Models\Profesional::find($e->id_profesional);$usuario=$prof?App\Models\User::find($prof->id_usuario):null;
    if(!$usuario)continue;
    Illuminate\Support\Facades\Auth::guard('web')->setUser($usuario);
    $respuesta=app(App\Http\Controllers\ProfesionalMiEquipoController::class)->verDetalleEquipoProfesional(Illuminate\Http\Request::create('/','GET',['id_profesional_mi_equipo'=>$e->id]));
    echo json_encode(['equipo'=>$e->id,'estado'=>$respuesta['estado'],'integrantes'=>$respuesta['registros']->count()]).PHP_EOL;
}
