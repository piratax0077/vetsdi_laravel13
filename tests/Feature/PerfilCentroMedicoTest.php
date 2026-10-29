<?php

namespace Tests\Feature;

use App\Http\Controllers\AdministradorCmController;
use App\Models\{AdminInstServ, Instituciones, Profesional, User};
use App\Services\PerfilCentroMedico;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, Blade, DB, File, Hash, Schema, Storage};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PerfilCentroMedicoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        foreach([
            'users'=>['name','nombres','apellido_uno','apellido_dos','telefono','email','password'],
            'instituciones'=>['id_usuario','id_responsable','id_direccion','id_lugar_atencion','nombre','razon_social','rut','email','telefono','whatsapp','horario_atencion','sitio_web','certificado_supersalud','sucursales','foto_perfil','id_director_medico','id_subdirector_medico','id_director_comercial','id_director_tecnico','id_director_gestion_cuidado'],
            'servicios'=>['id_usuario','id_responsable','id_direccion','nombre','rut','email'],
            'admin_inst_serv'=>['id_admin','id_direccion','rut','nombres','apellido_uno','apellido_dos','sexo','fecha_nac','email','telefono_uno','telefono_dos'],
            'profesionales'=>['id_usuario','id_direccion','rut','nombre','apellido_uno','apellido_dos','sexo','fecha_nacimiento','email','telefono_uno','telefono_dos'],
            'contrato_dependiente'=>['id_empleado','tipo_empleado','estado','id_institucion'],
            'areas_cm'=>['id_institucion','id_responsable'],
            'direcciones'=>['id_ciudad','direccion','numero_dir'],'ciudades'=>['id_region','nombre'],'regiones'=>['nombre'],
        ] as $tabla=>$campos)Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
        foreach([1,2,3,4] as $id)DB::table('users')->insert(['id'=>$id,'name'=>'Usuario '.$id,'email'=>'user'.$id.'@example.test','password'=>Hash::make('Anterior123')]);
        DB::table('admin_inst_serv')->insert([['id'=>30,'id_admin'=>1,'nombres'=>'Administración','apellido_uno'=>'Uno','email'=>'user1@example.test'],['id'=>40,'id_admin'=>2,'nombres'=>'Ajeno','apellido_uno'=>'Dos','email'=>'user2@example.test']]);
        DB::table('instituciones')->insert([['id'=>10,'id_usuario'=>1,'id_responsable'=>30,'nombre'=>'Centro Uno','telefono'=>'123','rut'=>'11111111-1','id_director_medico'=>3],['id'=>20,'id_usuario'=>2,'id_responsable'=>40,'nombre'=>'Centro Dos','telefono'=>'456','rut'=>'22222222-2','id_director_medico'=>4]]);
        DB::table('profesionales')->insert([['id'=>50,'id_usuario'=>3,'nombre'=>'Directora','apellido_uno'=>'Una','email'=>'user3@example.test'],['id'=>60,'id_usuario'=>4,'nombre'=>'Ajeno','apellido_uno'=>'Dos','email'=>'user4@example.test']]);
        DB::table('regiones')->insert([['id'=>1,'nombre'=>'Región Uno'],['id'=>2,'nombre'=>'Región Dos']]);DB::table('ciudades')->insert(['id'=>1,'id_region'=>1,'nombre'=>'Comuna']);
        Auth::setUser(User::find(1));
    }

    private function req(array $datos=[]): Request{return Request::create('/perfil','POST',$datos+['id_institucion'=>10,'tipo_institucion'=>'institucion']);}
    private function servicio(): PerfilCentroMedico{return app(PerfilCentroMedico::class);}

    private function prepararCuentas(): void
    {
        Schema::create('bancos', function(Blueprint $t){$t->id();$t->string('nombre');});
        Schema::create('tipo_cuenta_bancaria', function(Blueprint $t){$t->id();$t->string('descripcion');});
        Schema::create('cuenta_bancaria_inst', function(Blueprint $t){
            $t->id();
            foreach(['id_institucion','id_banco','id_tipo_cuenta','id_responsable','rut_titular','nombre_titular','numero_cuenta','rut_representante','nombre_representante','email','telefono','estado','observaciones'] as $campo)$t->string($campo)->nullable();
            $t->timestamps();
        });
        DB::table('bancos')->insert(['id'=>1,'nombre'=>'Banco de prueba']);
        DB::table('tipo_cuenta_bancaria')->insert(['id'=>1,'descripcion'=>'Cuenta corriente']);
    }

    private function datosCuenta(): array
    {
        return ['rut_titular'=>'11111111-1','nombre_titular'=>'Centro Uno','numero_cuenta'=>'001234567890123456','banco_titular'=>1,'tipo_cuenta'=>1,'email_titular'=>'cuenta@example.test'];
    }

    public function test_commercial_accounts_preserve_number_allow_multiple_accounts_and_edit_delete(): void
    {
        $this->prepararCuentas();$controller=app(AdministradorCmController::class);
        $datos=$this->datosCuenta();
        $this->assertSame(1,$controller->agregar_cuenta_bancaria_institucion($this->req($datos))['estado']);
        $this->assertSame($datos['numero_cuenta'],DB::table('cuenta_bancaria_inst')->value('numero_cuenta'));
        $datos['numero_cuenta']='0002';$controller->agregar_cuenta_bancaria_institucion($this->req($datos));
        $this->assertSame(2,DB::table('cuenta_bancaria_inst')->count());
        $datos['id']=1;$datos['nombre_titular']='Titular editado';$datos['numero_cuenta']='0003';
        $controller->editar_cuenta_bancaria_institucion($this->req($datos));
        $this->assertSame('Titular editado',DB::table('cuenta_bancaria_inst')->where('id',1)->value('nombre_titular'));
        $controller->eliminar_cuenta_bancaria_institucion($this->req(['id'=>1]));
        $this->assertSame(1,DB::table('cuenta_bancaria_inst')->count());
    }

    public function test_commercial_accounts_reject_duplicates_and_invalid_bank_without_writing(): void
    {
        $this->prepararCuentas();$controller=app(AdministradorCmController::class);$datos=$this->datosCuenta();
        $controller->agregar_cuenta_bancaria_institucion($this->req($datos));
        foreach([$datos,array_merge($datos,['banco_titular'=>999])] as $invalido){
            try{$controller->agregar_cuenta_bancaria_institucion($this->req($invalido));$this->fail('Debe rechazar la cuenta.');}catch(ValidationException $e){$this->assertNotEmpty($e->errors());}
        }
        $this->assertSame(1,DB::table('cuenta_bancaria_inst')->count());
    }

    public function test_commercial_accounts_cannot_edit_or_delete_another_centers_account(): void
    {
        $this->prepararCuentas();DB::table('cuenta_bancaria_inst')->insert(['id'=>1,'id_institucion'=>20,'numero_cuenta'=>'999']);
        $controller=app(AdministradorCmController::class);
        foreach(['editar_cuenta_bancaria_institucion','eliminar_cuenta_bancaria_institucion'] as $metodo){
            try{$controller->{$metodo}($this->req($this->datosCuenta()+['id'=>1]));$this->fail('No debe acceder a cuentas ajenas.');}catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){$this->assertSame('999',DB::table('cuenta_bancaria_inst')->value('numero_cuenta'));}
        }
    }

    public function test_commercial_configuration_renders_real_forms_without_director_or_address(): void
    {
        $this->prepararCuentas();$controller=app(AdministradorCmController::class);
        $vista=$controller->configuracion_comercial($this->req());
        $fuente=file_get_contents(resource_path('views/app/adm_cm/configuracion_esc_comercial.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        $html=Blade::render($fuente,$vista->getData());
        $this->assertStringContainsString('Centro Uno',$html);
        $this->assertStringContainsString('data-centro-form="cuenta"',$html);
        $this->assertStringContainsString('No hay un responsable comercial asignado.',$html);
        $this->assertStringNotContainsString('Centro Dos',$html);
        $this->assertSame(10,$controller->areaComercial($this->req())->getData()['institucion']->id);
    }

    public function test_configuration_and_commercial_cards_have_registered_routes_and_actions(): void
    {
        $pendientes=['app.adm_cm.configuracion','app.adm_cm.configuracion_esc_comercial','app.adm_cm.escritorio_adm_comercial'];$leidos=[];
        while($nombre=array_pop($pendientes)){
            if(isset($leidos[$nombre]))continue;$leidos[$nombre]=true;
            $fuente=file_get_contents(resource_path('views/'.str_replace('.','/',$nombre).'.blade.php'));
            $fuente=preg_replace('/\{\{--[\s\S]*?--\}\}/','',$fuente);
            preg_match_all('/\broute\s*\(\s*[\'\"]([^\'\"]+)[\'\"]/i',$fuente,$rutas);
            foreach($rutas[1] as $ruta){
                $registro=\Illuminate\Support\Facades\Route::getRoutes()->getByName($ruta);
                $this->assertNotNull($registro,"Ruta faltante: $ruta en $nombre");
                $accion=$registro->getActionName();
                if(str_contains($accion,'@')){[$clase,$metodo]=explode('@',$accion);$this->assertTrue(method_exists($clase,$metodo),"Acción faltante: $accion");}
            }
            preg_match_all('/@include\s*\(\s*[\'\"]([^\'\"]+)[\'\"]/',$fuente,$includes);
            foreach($includes[1] as $include)$pendientes[]=$include;
        }
    }

    public function test_area_creation_and_edit_preserve_institution_and_selected_type(): void
    {
        Schema::table('areas_cm',function(Blueprint $t){foreach(['id_lugar_atencion','id_tipo_area_cm','nombre','apellido_uno','apellido_dos','email','telefono','numero_personas'] as $campo)$t->string($campo)->nullable();});
        Schema::create('tipos_areas_cm',function(Blueprint $t){$t->id();$t->string('nombre');});
        DB::table('tipos_areas_cm')->insert([['id'=>1,'nombre'=>'Primera'],['id'=>2,'nombre'=>'Segunda']]);
        DB::table('instituciones')->where('id',10)->update(['id_lugar_atencion'=>100]);
        $controller=new class extends AdministradorCmController {
            public function dame_areas_cm($ids){return collect();}
        };
        $resultado=$controller->agregar_area_cm($this->req(['tipo_area'=>1,'responsable_cargo'=>50,'e_cont'=>'area@example.test','tel_c'=>'555','n_pers'=>2]));
        $this->assertSame(1,$resultado->getData(true)['estado']);
        $id=DB::table('areas_cm')->value('id');
        $resultado=$controller->editar_area_cm($this->req(['id_area'=>$id,'tipo_area'=>2,'responsable'=>50,'email'=>'editada@example.test','telefono'=>'666','n_persons'=>4]));
        $this->assertSame(1,$resultado['estado']);
        $area=DB::table('areas_cm')->find($id);$this->assertEquals(2,$area->id_tipo_area_cm);$this->assertEquals(10,$area->id_institucion);$this->assertSame('editada@example.test',$area->email);
    }

    public function test_real_profile_renders_with_no_addresses_or_optional_data(): void
    {
        $vista=app(AdministradorCmController::class)->perfil($this->req());
        $this->assertSame('Centro Uno',$vista->getData()['institucion']->nombre);
        $fuente=file_get_contents(resource_path('views/app/adm_cm/perfil_cm.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        $html=Blade::render($fuente,$vista->getData());
        $this->assertStringContainsString('Centro Uno',$html);
        $this->assertStringNotContainsString('Centro Dos',$html);
        $this->assertStringContainsString('perfil-centro-medico.js',$html);
        $limpio=preg_replace('/<script[^>]*>[\s\S]*?<\/script>/','',$html);preg_match_all('/<\/?form\b[^>]*>/i',$limpio,$forms);
        $nivel=0;foreach($forms[0] as $f){$nivel+=str_starts_with($f,'</')?-1:1;$this->assertLessThanOrEqual(1,$nivel);$this->assertGreaterThanOrEqual(0,$nivel);}$this->assertSame(0,$nivel);
    }

    public function test_partial_edits_preserve_omitted_fields_and_allow_clearing_optional_fields(): void
    {
        $this->servicio()->guardarCentro($this->req(['nombre'=>'Nombre editado','whatsapp'=>null]));
        $centro=Instituciones::find(10);
        $this->assertSame('Nombre editado',$centro->nombre);$this->assertSame('123',$centro->telefono);$this->assertNull($centro->whatsapp);
    }

    public function test_center_address_edit_does_not_change_another_profile(): void
    {
        DB::table('direcciones')->insert(['id'=>1,'id_ciudad'=>1,'direccion'=>'Original','numero_dir'=>'10']);
        DB::table('instituciones')->where('id',10)->update(['id_direccion'=>1]);
        DB::table('profesionales')->where('id',50)->update(['id_direccion'=>1]);
        $this->servicio()->guardarCentro($this->req(['ciudad'=>1,'region'=>1,'direccion'=>'Nueva','numero_dir'=>'20']));
        $this->assertNotEquals(1,Instituciones::find(10)->id_direccion);
        $this->assertEquals(1,Profesional::find(50)->id_direccion);
        $this->assertSame('Original',DB::table('direcciones')->where('id',1)->value('direccion'));
    }

    public function test_responsible_identity_and_phone_are_synced_with_account(): void
    {
        $this->servicio()->guardarResponsable($this->req(['nombres'=>'Nombre nuevo','apellido_uno'=>'Apellido','telefono_uno'=>'555','email'=>' NUEVO@example.test ']));
        $user=User::find(1);
        $this->assertSame('Nombre nuevo',$user->nombres);
        $this->assertSame('Apellido',$user->apellido_uno);
        $this->assertSame('555',$user->telefono);
        $this->assertSame('nuevo@example.test',$user->email);
    }

    public function test_first_address_is_linked_and_reused(): void
    {
        $data=['ciudad'=>1,'region'=>1,'direccion'=>'Calle','numero_dir'=>'12 B'];
        $this->servicio()->guardarCentro($this->req($data));$id=Instituciones::find(10)->id_direccion;
        $this->assertNotNull($id);$data['direccion']='Calle editada';$this->servicio()->guardarCentro($this->req($data));
        $this->assertEquals($id,Instituciones::find(10)->id_direccion);$this->assertSame(1,DB::table('direcciones')->count());
    }

    public function test_address_and_center_rollback_together(): void
    {
        DB::statement("CREATE TRIGGER fallo BEFORE UPDATE ON instituciones BEGIN SELECT RAISE(FAIL,'Fallo simulado'); END");
        try{$this->servicio()->guardarCentro($this->req(['nombre'=>'Nuevo','ciudad'=>1,'direccion'=>'Calle']));$this->fail();}catch(\Illuminate\Database\QueryException $e){$this->assertStringContainsString('Fallo simulado',$e->getMessage());}
        $this->assertSame('Centro Uno',Instituciones::find(10)->nombre);$this->assertSame(0,DB::table('direcciones')->count());
    }

    public function test_other_centers_cannot_be_edited(): void
    {
        $this->expectException(HttpException::class);$this->servicio()->guardarCentro($this->req(['id_institucion'=>20,'nombre'=>'Ajeno']));
    }

    public function test_active_administrator_contract_grants_access_and_revocation_removes_it(): void
    {
        DB::table('contrato_dependiente')->insert(['id_empleado'=>30,'tipo_empleado'=>'ADMINISTRADOR','estado'=>2,'id_institucion'=>20]);
        $this->assertEquals(20,$this->servicio()->centro($this->req(['id_institucion'=>20]))->id);
        DB::table('contrato_dependiente')->update(['estado'=>0]);
        $this->expectException(HttpException::class);$this->servicio()->centro($this->req(['id_institucion'=>20]));
    }

    public function test_responsible_email_and_address_save_with_login_in_one_transaction(): void
    {
        $this->servicio()->guardarResponsable($this->req(['id_responsable'=>30,'email'=>'nuevo@example.test','ciudad'=>1,'direccion'=>'Dirección responsable']));
        $this->assertSame('nuevo@example.test',User::find(1)->email);$this->assertSame('nuevo@example.test',AdminInstServ::find(30)->email);$this->assertNotNull(AdminInstServ::find(30)->id_direccion);
    }

    public function test_unrelated_responsible_cannot_be_modified(): void
    {
        $this->expectException(HttpException::class);$this->servicio()->guardarResponsable($this->req(['id_responsable'=>40,'nombres'=>'Intruso']));
    }

    public function test_linked_director_gets_first_address_and_account_email_is_synced(): void
    {
        $this->servicio()->guardarResponsableMedico($this->req(['id_responsable'=>50,'nombres'=>'Directora editada','email'=>'directora@example.test','ciudad'=>1,'direccion'=>'Calle médica']));
        $this->assertSame('Directora editada',Profesional::find(50)->nombre);$this->assertNotNull(Profesional::find(50)->id_direccion);$this->assertSame('directora@example.test',User::find(3)->email);
        $this->expectException(HttpException::class);$this->servicio()->guardarResponsableMedico($this->req(['id_responsable'=>60,'nombres'=>'Ajeno']));
    }

    public function test_password_requires_current_password_and_rejects_unrelated_account(): void
    {
        $datos=['responsable_id'=>1,'responsable_actual'=>'incorrecta','responsable_nueva'=>'NuevaClave123','responsable_validacion'=>'NuevaClave123'];
        try{$this->servicio()->contrasenaResponsable($this->req($datos));$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('responsable_actual',$e->errors());}
        $datos['responsable_actual']='Anterior123';$this->servicio()->contrasenaResponsable($this->req($datos));$this->assertTrue(Hash::check('NuevaClave123',User::find(1)->password));
        $datos['responsable_id']=2;$this->expectException(HttpException::class);$this->servicio()->contrasenaResponsable($this->req($datos));
    }

    public function test_center_photo_upload_is_public_and_cannot_target_another_center(): void
    {
        Storage::fake('public');$anterior=public_path();$temp=storage_path('framework/testing/centro-'.uniqid());$this->app->usePublicPath($temp);
        try{
            $foto=UploadedFile::fake()->createWithContent('foto.png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
            $request=$this->req();$request->files->set('foto_perfil',$foto);
            $respuesta=app(AdministradorCmController::class)->actualizarFoto($request)->getData(true);$this->assertTrue($respuesta['success']);
            $this->assertFileExists(public_path('storage/'.Instituciones::find(10)->foto_perfil));
            $request=$this->req(['id_institucion'=>20]);$request->files->set('foto_perfil',$foto);
            $this->expectException(HttpException::class);app(AdministradorCmController::class)->actualizarFoto($request);
        }finally{$this->app->usePublicPath($anterior);File::deleteDirectory($temp);}
    }
}
