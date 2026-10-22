<?php
namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Mail, Schema};

/** Catálogos mínimos para probar cuentas sin escribir en la base del proyecto. */
trait CuentasAisladas
{
    protected function prepararCuentasAisladas(): void
    {
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','cache.default'=>'array','mail.redirect_to'=>null]);
        DB::purge('sqlite');
        foreach ([
            'users'=>['name','nombres','apellido_uno','apellido_dos','rut','telefono','email','password','email_verified_at','remember_token','profile_photo_path','current_team_id'],
            'usuario_roles'=>['id_usuario','tipo','id_plan','fecha_activacion','fecha_perfil_completo'],
            'verificaciones_correo'=>['id_usuario','email','token','expira_en','verificado_en','enviado_en'],
            'registro_correo'=>['id_user','data','fecha_envio','estado'],
            'password_resets'=>['email','token'],
            'roles'=>['name','guard_name'], 'permissions'=>['name','guard_name'],
            'model_has_roles'=>['role_id','model_type','model_id'],
            'model_has_permissions'=>['permission_id','model_type','model_id'],
            'role_has_permissions'=>['role_id','permission_id'],
            'asistentes'=>['id_usuario','rut','nombres','apellido_uno','apellido_dos','email','telefono_uno','id_modalidad','bienvenido','sexo','fecha_nac','id_direccion'],
            'profesionales'=>['id_usuario','rut','nombre','apellido_uno','apellido_dos','email','telefono_uno','id_especialidad','id_tipo_especialidad','estado','sexo','fecha_nacimiento','id_direccion'],
            'pacientes'=>['id_usuario','rut','nombres','apellido_uno','apellido_dos','email','telefono_uno','id_direccion','token','id_prevision','bienvenida','sexo','fecha_nac'],
            'mascotas'=>['nombre','especie_id','raza_id','sexo','fecha_nacimiento','chip','tiene_chip','id_responsable','id_user','estado','origen_registro'],
            'instituciones'=>['id_usuario','nombre','razon_social','nombre_fantasia','giro','rut','email','telefono','id_direccion','id_lugar_atencion','id_tipo_institucion','id_responsable','rut_responsable','nombre_responsable','estado','bienvenido'],
            'admin_inst_serv'=>['rut','nombres','apellido_uno','apellido_dos','telefono_uno','email','estado'],
            'lugares_atencion'=>['nombre','rut','email','telefono','id_direccion','tipo'],
            'direcciones'=>['direccion','id_ciudad','numero_dir'],
            'regiones'=>['nombre'], 'ciudades'=>['nombre','id_region'],
            'especialidades'=>['nombre'], 'tipos_especialidad'=>['nombre','id_especialidad'],
            'especies_mascotas'=>['nombre'], 'previsiones'=>['nombre'],
        ] as $tabla=>$campos) {
            Schema::create($tabla, function (Blueprint $t) use ($tabla,$campos) {
                $t->id(); foreach ($campos as $campo) $t->text($campo)->nullable();
                if ($tabla==='usuario_roles') { $t->boolean('estado')->default(true); $t->boolean('perfil_completo')->default(false); }
                $t->timestamps();
            });
        }
        DB::table('regiones')->insert(['id'=>1,'nombre'=>'Región de prueba']);
        DB::table('ciudades')->insert(['id'=>1,'nombre'=>'Comuna de prueba','id_region'=>1]);
        DB::table('especialidades')->insert(['id'=>1,'nombre'=>'Veterinaria']);
        DB::table('especies_mascotas')->insert(['id'=>1,'nombre'=>'Canina']);
        DB::table('previsiones')->insert(['id'=>1,'nombre'=>'Particular']);
        foreach (\App\Services\CuentasService::ROLES_SPATIE as $rol) DB::table('roles')->insert(['name'=>$rol,'guard_name'=>'web']);
        config(['mail.default'=>'smtp']);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Mail::fake();
    }
}
