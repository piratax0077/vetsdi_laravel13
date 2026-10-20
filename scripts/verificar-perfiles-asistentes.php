<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\{Asistente, User};
use Illuminate\Support\Facades\{Auth, DB, Mail, Notification, Queue};
config(['session.driver' => 'array', 'app.debug' => false]); Mail::fake(); Notification::fake(); Queue::fake();
DB::connection()->beforeExecuting(function ($sql) {
    if (!preg_match('/^\s*(select|show|describe|explain|savepoint|release\s+savepoint)\b/i', $sql)) throw new RuntimeException('Revisión de perfiles: escritura bloqueada.');
});
$resultados = [];
foreach (config('asistentes.roles') as $rol) {
    if (!DB::table('roles')->where('name', $rol)->where('guard_name', 'web')->exists()) { $resultados[] = ['rol' => $rol, 'rol_en_base' => false]; continue; }
    $usuario = User::role($rol)->whereIn('id', Asistente::select('id_usuario'))->orderBy('id')->first();
    if (!$usuario) { $resultados[] = ['rol' => $rol, 'datos_locales' => false]; continue; }
    foreach (['asistentes.home', 'asistentes.perfil', 'asistentes.perfil.contactos'] as $ruta) {
        Auth::forgetGuards(); Auth::guard('web')->setUser($usuario);
        $request = Illuminate\Http\Request::create(route($ruta), 'GET');
        $response = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle($request);
        $resultado = ['rol' => $rol, 'ruta' => $ruta, 'estado' => $response->getStatusCode()];
        if (isset($response->exception)) $resultado['error'] = substr(explode('(SQL:', $response->exception->getMessage())[0], 0, 300);
        $resultados[] = $resultado;
    }
}
$indices = collect(DB::select('SHOW INDEX FROM contactos_emergencia'))->where('Non_unique', 0)->pluck('Column_name')->all();
echo json_encode(['pantallas' => $resultados, 'contactos_columnas_unicas' => $indices], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
exit(collect($resultados)->contains(fn ($r) => isset($r['estado']) && $r['estado'] !== 200) ? 1 : 0);
