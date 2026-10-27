from pathlib import Path
import re,json
root=Path('D:/laragon/www/centro-estetica')
def put(name,text):
 p=root/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(text,encoding='utf-8')
put('bootstrap/app.php',r"""<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Env;
Env::disablePutenv();
return Application::configure(basePath: dirname(__DIR__))
 ->withRouting(web: __DIR__.'/../routes/web.php',commands: __DIR__.'/../routes/console.php',health:'/up')
 ->withMiddleware(function(Middleware $middleware){
  $middleware->appendToGroup('web',[App\Http\Middleware\PerfilPendiente::class]);
  $middleware->alias(['role'=>Spatie\Permission\Middleware\RoleMiddleware::class,'permission'=>Spatie\Permission\Middleware\PermissionMiddleware::class,'role_or_permission'=>Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,'rol.cuenta'=>App\Http\Middleware\RolCuenta::class]);
  $middleware->redirectGuestsTo('/Ingreso');
  $middleware->redirectUsersTo('/Elegir_cuenta');
 })
 ->withProviders([App\Providers\FortifyServiceProvider::class])->create();
""")
p=root/'config/app.php';s=p.read_text(encoding='utf-8');s=s.replace('App\\Providers\\RouteServiceProvider::class,','').replace('App\\Providers\\AuthServiceProvider::class,','').replace('App\\Providers\\EventServiceProvider::class,','');p.write_text(s,encoding='utf-8')
put('app/Providers/AppServiceProvider.php',r"""<?php
namespace App\Providers;
class AppServiceProvider extends \Illuminate\Support\ServiceProvider {
 public function register():void { \Laravel\Jetstream\Jetstream::ignoreRoutes(); }
 public function boot():void {
  \Illuminate\Support\Facades\Blade::component('components.modal', 'modal');
 }
}
""")
p=root/'app/Providers/FortifyServiceProvider.php';s=p.read_text(encoding='utf-8');s=s.replace('// El ingreso acepta RUT o correo, y exige el correo ya confirmado.',"Fortify::loginView(fn () => view('auth.centro_ingreso'));\n        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request'=>$request]));\n        // El ingreso acepta RUT o correo, y exige el correo ya confirmado.")
s=s.replace('return $usuario;',"if (app(\\App\\Services\\CuentasService::class)->rolesActivos($usuario)->isEmpty()) { return null; }\n            return $usuario;",1);p.write_text(s,encoding='utf-8')
put('config/fortify.php',r"""<?php
return ['guard'=>'web','passwords'=>'users','username'=>'email','email'=>'email','lowercase_usernames'=>true,'home'=>'/Elegir_cuenta','prefix'=>'','domain'=>null,'middleware'=>['web'],'limiters'=>['login'=>'login'],'views'=>true,'features'=>[Laravel\Fortify\Features::resetPasswords()]];
""")
p=root/'app/Services/CuentasService.php';s=p.read_text(encoding='utf-8');s=s.replace("public const TIPOS = ['tutor', 'profesional', 'asistente', 'clinica', 'peluqueria', 'centro_estetico'];","public const TIPOS = ['centro_estetico', 'peluqueria'];");s=s.replace("->activos()\n            ->get()","->activos()\n            ->whereIn('tipo', self::TIPOS)\n            ->get()",1);p.write_text(s,encoding='utf-8')
put('routes/web.php',r"""<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegistroCuentaController;
use App\Http\Controllers\Auth\VerificacionCorreoController;
use App\Http\Controllers\Auth\RecuperacionContrasenaController;
use App\Http\Controllers\SeleccionCuentaController;
use App\Http\Controllers\Perfil\CompletarPerfilController;
Route::redirect('/','/Ingreso')->name('home');
Route::get('/Ingreso',fn()=>view('auth.centro_ingreso'))->name('home.ingreso');
Route::redirect('/Acceso','/Ingreso');
Route::redirect('/home','/Elegir_cuenta');
Route::post('/Registro_cuenta',[RegistroCuentaController::class,'store'])->middleware('throttle:6,1')->name('registro.cuenta');
Route::get('/Registro_confirmacion',[VerificacionCorreoController::class,'enviado'])->name('registro.enviado');
Route::post('/Registro_reenviar',[VerificacionCorreoController::class,'reenviar'])->middleware('throttle:3,1')->name('registro.reenviar');
Route::get('/Verificar_correo/{token}',[VerificacionCorreoController::class,'verificar'])->name('registro.verificar');
Route::post('/recuperar_contrasena',[RecuperacionContrasenaController::class,'enlace'])->name('home.recuperar_contrasena');
Route::middleware('auth')->group(function(){
 Route::get('/Elegir_cuenta',[SeleccionCuentaController::class,'index'])->name('cuenta.seleccion');
 Route::get('/Entrar_cuenta/{tipo}',[SeleccionCuentaController::class,'entrar'])->name('cuenta.entrar');
 Route::get('/Completar_perfil/{tipo}',[CompletarPerfilController::class,'formulario'])->name('perfil.completar');
 Route::post('/Completar_perfil/{tipo}',[CompletarPerfilController::class,'guardar'])->name('perfil.completar.guardar');
});
require __DIR__.'/peluqueria.php';
""")
put('routes/console.php','<?php\n// Comandos exclusivos del centro estético.\n')
put('database/migrations/2026_10_25_000000_create_centro_estetica_schema.php',r"""<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up():void {
  $sql=file_get_contents(database_path('schema/centro-estetica.sql'));
  foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql) as $statement){if(trim($statement)!=='')DB::unprepared($statement);}
 }
 public function down():void { throw new RuntimeException('El esquema inicial conserva los datos. Utilice una base vacía para reinstalar.'); }
};
""")
composer=json.loads((root/'composer.json').read_text(encoding='utf-8'));composer['name']='veterchile/centro-estetica';composer['description']='Centro estético independiente: administración y profesional de estética';put('composer.json',json.dumps(composer,indent=4,ensure_ascii=False)+'\n')
# Archivo de ejemplo sin credenciales, claves ni datos de la instalación.
env=(root/'.env').read_text(encoding='utf-8');env=re.sub(r'(?m)^APP_KEY=.*$','APP_KEY=',env);env=re.sub(r'(?m)^DB_PASSWORD=.*$','DB_PASSWORD=',env);put('.env.example',env)
put('README.md',"""# Centro estético independiente

Proyecto: `D:/laragon/www/centro-estetica`.
Acceso local con Apache de Laragon: http://localhost/centro-estetica/public/Ingreso

Base exclusiva: `centro_estetica`. Usuario MySQL exclusivo: `centro_estetica_app`.
Las credenciales están únicamente en `.env`. No tiene acceso a la base veterinaria.

Conserva el centro, los perfiles de estética, sucursales, servicios, reservas, ficha y mascotas de sus tutores. Las contraseñas de las cuentas copiadas se mantienen. Las sesiones y claves son nuevas e independientes. No se importan sesiones ni correos históricos.

Roles: Centro estético administra el negocio; Profesional de estética atiende su agenda. El personal conserva sus permisos por centro y sucursal.

Se conservan los CSS y recursos visuales del sistema. No se publican rutas de atención clínica veterinaria.

Para otra instalación: instalar dependencias con Composer, configurar una base MySQL vacía y su usuario en `.env`, ejecutar `php artisan key:generate` y `php artisan migrate`. El esquema inicial no incluye datos personales ni contraseñas.

Pendiente externo: configurar SMTP y mensajería. El correo usa `MAIL_MAILER=log`; no envía mensajes reales.
""")
# Copiar verificación de pantallas para ejecutarla sobre la base nueva.
put('scripts/verificar-centro-estetico.php',Path('scripts/verificar-centro-estetico.php').read_text(encoding='utf-8'))
print('Configuración, rutas y esquema independientes preparados.')

put('resources/views/auth/centro_ingreso.blade.php',r"""<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Centro estético · Ingreso</title><link rel="stylesheet" href="{{ asset('css/style.css') }}"><link rel="stylesheet" href="{{ asset('css/login.css') }}"></head>
<body><main class="auth-wrapper"><div class="auth-content" style="max-width:600px;width:100%;padding:20px"><div class="card"><div class="card-body p-4">
<h3 class="text-center text-primary">Centro estético</h3><p class="text-center">Administración y profesionales de estética</p>
@if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@foreach(['mensaje'=>'info','mensaje_error'=>'danger'] as $key=>$color)@if(session($key))<div class="alert alert-{{ $color }}">{{ session($key) }}</div>@endif @endforeach
@if(session('cuenta_existente'))<div class="alert alert-info">La cuenta ya existe. Ingresa con tu contraseña.</div>@endif
<form method="post" action="{{ route('login') }}">@csrf
<div class="form-group"><label for="email">RUT o correo electrónico</label><input class="form-control" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required></div>
<div class="form-group"><label for="password">Contraseña</label><input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required></div>
<button class="btn btn-primary btn-block">Ingresar</button></form>
<details class="mt-4"><summary>Crear cuenta</summary><form class="mt-3" method="post" action="{{ route('registro.cuenta') }}">@csrf
<label for="tipo_cuenta">Perfil</label><select id="tipo_cuenta" name="tipo_cuenta" class="form-control mb-3">@foreach(\App\Services\CuentasService::TIPOS as $tipo)<option value="{{ $tipo }}" @selected(old('tipo_cuenta')===$tipo)>{{ \App\Services\CuentasService::ETIQUETAS[$tipo] }}</option>@endforeach</select>
<div class="row">@foreach(['nombres'=>'Nombres','apellido_uno'=>'Apellido paterno','apellido_dos'=>'Apellido materno','rut'=>'RUT','telefono'=>'Teléfono (+56 9 1234 5678)','email'=>'Correo electrónico'] as $name=>$label)<div class="col-md-6 form-group"><label for="registro_{{ $name }}">{{ $label }}</label><input id="registro_{{ $name }}" name="{{ $name }}" class="form-control" value="{{ old($name) }}" @if($name!=='apellido_dos') required @endif @if($name==='email')type="email"@endif></div>@endforeach</div>
<p class="small">Contraseña: mínimo 8 caracteres, una mayúscula, una minúscula y un número.</p>
<div class="form-group"><label for="registro_password">Contraseña</label><input id="registro_password" name="password" type="password" class="form-control" autocomplete="new-password" required></div>
<div class="form-group"><label for="password_confirmation">Repetir contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required></div>
<button class="btn btn-info btn-block">Crear cuenta</button></form></details>
<details class="mt-3"><summary>Recuperar contraseña</summary><form class="mt-3" method="post" action="{{ route('home.recuperar_contrasena') }}">@csrf<label for="correo_recuperacion">Correo electrónico</label><input class="form-control mb-3" type="email" name="correo_recuperacion" id="correo_recuperacion" required><button class="btn btn-outline-primary">Solicitar enlace</button></form></details>
</div></div></div></main></body></html>
""")
