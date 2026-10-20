<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{Profesional, User};
use Illuminate\Support\Facades\{Auth, DB, Mail, Notification, Queue};
config(['session.driver' => 'array', 'app.debug' => false]);
Mail::fake(); Notification::fake(); Queue::fake();
DB::connection()->beforeExecuting(function ($sql) {
    if (!preg_match('/^\s*(select|show|describe|explain|savepoint|release\s+savepoint)\b/i', $sql)) {
        throw new RuntimeException('Comprobación de solo lectura: escritura bloqueada.');
    }
});
$resultados = [];
$profesional = Profesional::whereHas('Usuario', fn ($q) => $q->role('Profesional'))
    ->whereIn('id', DB::table('profesional_horarios')->select('id_profesional'))->orderBy('id')->firstOrFail();
$lugar = DB::table('profesional_horarios')->where('id_profesional', $profesional->id)->value('id_lugar_atencion');
foreach (['profesional.aranceles', 'profesional.aranceles_vet.listar', 'profesional.mi_perfil', 'profesional.reporte_estadisticas', 'profesional.mis_propios_convenios', 'profesional.mi_agenda', 'profesional.lugares_atencion'] as $ruta) {
    Auth::forgetGuards(); Auth::guard('web')->setUser(User::findOrFail($profesional->id_usuario));
    $parametros = $ruta === 'profesional.mi_agenda' ? ['lugares_atencion' => $lugar] : [];
    $request = Illuminate\Http\Request::create(route($ruta, $parametros), 'GET');
    $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
    $resultado = ['pantalla' => $ruta, 'estado' => $response->getStatusCode(), 'bytes' => strlen($response->getContent())];
    if (isset($response->exception)) $resultado['error'] = substr(explode('(SQL:', $response->exception->getMessage())[0], 0, 450);
    $resultados[] = $resultado;
}
echo json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains(fn ($r) => $r['estado'] !== 200) ? 1 : 0);
