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
    $user=App\Models\User::whereIn('id',App\Models\Peluqueria::select('id_usuario'))->first();
    if (!$user) {echo "Sin cuenta local de peluqueria\n";exit;}
    $cookies=[];
    foreach (['peluqueria.home','peluqueria.agenda.ver','peluqueria.mascotas','peluqueria.historial','peluqueria.configuracion'] as $name) {
        Auth::forgetGuards(); Auth::guard('web')->setUser($user);
        $response=$app->make(Illuminate\Contracts\Http\Kernel::class)->handle(Illuminate\Http\Request::create(route($name),'GET',[],$cookies));
        foreach($response->headers->getCookies() as $cookie) $cookies[$cookie->getName()]=$cookie->getValue();
        echo $name,': ',$response->getStatusCode();
        if (isset($response->exception)) echo ' ',get_class($response->exception),' ',substr(explode('(SQL:',$response->exception->getMessage())[0],0,220);
        if ($response->isRedirection()) echo ' -> ',parse_url($response->headers->get('Location'),PHP_URL_PATH);
        echo "\n";
    }
} catch (Throwable $e) { echo get_class($e),': ',substr(explode('(SQL:',$e->getMessage())[0],0,220),"\n"; exit(1); }
