<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema,DB};
use Spatie\Permission\Models\Role;
return new class extends Migration {
 public function up(): void {
  Schema::table('usuario_roles',fn(Blueprint $t)=>$t->enum('tipo',['tutor','profesional','asistente','clinica','peluqueria','centro_estetico'])->change());
  Schema::table('peluquerias',function(Blueprint $t){$t->string('rut_empresa',12)->nullable();$t->string('razon_social')->nullable();$t->string('giro')->nullable();$t->string('email_contacto')->nullable();});
  Schema::create('profesionales_estetica',function(Blueprint $t){$t->id();$t->unsignedBigInteger('id_usuario')->unique();$t->string('nombre');$t->string('telefono',30)->nullable();$t->string('direccion')->nullable();$t->timestamps();});
  Role::findOrCreate('CentroEstetico','web');
  foreach(DB::table('usuario_roles')->where('tipo','peluqueria')->whereIn('id_usuario',DB::table('peluquerias')->select('id_usuario'))->get() as $rol){
   if(!DB::table('usuario_roles')->where('id_usuario',$rol->id_usuario)->where('tipo','centro_estetico')->exists())DB::table('usuario_roles')->insert(['id_usuario'=>$rol->id_usuario,'tipo'=>'centro_estetico','estado'=>$rol->estado,'perfil_completo'=>$rol->perfil_completo,'fecha_activacion'=>now(),'created_at'=>now(),'updated_at'=>now()]);
   $usuario=App\Models\User::find($rol->id_usuario);if($usuario){
    $usuario->assignRole('CentroEstetico');
    $local=DB::table('peluquerias')->where('id_usuario',$usuario->id)->first();
    DB::table('profesionales_estetica')->insertOrIgnore(['id_usuario'=>$usuario->id,'nombre'=>$usuario->name?:$local->nombre,'telefono'=>$local->telefono,'direccion'=>$local->direccion,'created_at'=>now(),'updated_at'=>now()]);
   }
  }
  app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
 }
 public function down(): void {
  // Los perfiles y permisos creados se conservan para no perder accesos ni datos.
  Schema::table('peluquerias',fn(Blueprint $t)=>$t->dropColumn(['rut_empresa','razon_social','giro','email_contacto']));
 }
};
