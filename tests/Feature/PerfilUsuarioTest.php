<?php

namespace Tests\Feature;

use App\Http\Controllers\{EscritorioAsistente, EscritorioProfesional};
use App\Http\Middleware\AutorizarAsistente;
use App\Models\{Asistente, Profesional, User};
use App\Services\PerfilUsuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\{Auth, DB, Hash, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PerfilUsuarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        DB::purge('sqlite');
        foreach ([
            'users'=>['name','nombres','apellido_uno','apellido_dos','rut','telefono','email','password'],
            'asistentes'=>['id_usuario','id_direccion','rut','nombres','apellido_uno','apellido_dos','sexo','fecha_nac','email','telefono_uno','curriculum','buscador','id_modalidad'],
            'profesionales'=>['id_usuario','id_direccion','rut','nombre','apellido_uno','apellido_dos','sexo','email','telefono_uno','num_colegio','id_especialidad','id_tipo_especialidad','id_sub_tipo_especialidad','id_tipo_atencion'],
            'direcciones'=>['id_ciudad','direccion','numero_dir'],
            'ciudades'=>['nombre','id_region'], 'regiones'=>['nombre'],
            'especialidades'=>['nombre'], 'tipos_especialidad'=>['nombre','id_especialidad'], 'sub_tipo_especialidad'=>['nombre','id_tipo_especialidad'],
            'contactos_emergencia'=>['rut','nombre','apellido_uno','apellido_dos','email','telefono','id_direccion','parentezco','prioridad','fecha_nac'],
            'asistente_contacto_emergencia'=>['id_asistente','id_contacto','relacion'],
            'bancos'=>['nombre','estado'],
            'liquidacion_recibo'=>['id_seccion','serie','autor','casa','numero_control','email','principal','otro','estado'],
            'profesional_antecedente_academico'=>['id_profesional','nombre'],
            'tipo_antecedente_academico'=>['nombre'],
        ] as $tabla=>$campos) Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
        DB::table('users')->insert([['id'=>1,'name'=>'Uno','email'=>'uno@example.test','password'=>Hash::make('ClaveAnterior123')],['id'=>2,'name'=>'Dos','email'=>'dos@example.test','password'=>Hash::make('OtraClave123')]]);
        foreach(['asistentes','profesionales'] as $tabla) DB::table($tabla)->insert([['id'=>10,'id_usuario'=>1,'rut'=>'11111111-1','email'=>'uno@example.test'],['id'=>20,'id_usuario'=>2,'rut'=>'22222222-2','email'=>'dos@example.test']]);
        DB::table('regiones')->insert([['id'=>1,'nombre'=>'Región Uno'],['id'=>2,'nombre'=>'Región Dos']]);
        DB::table('ciudades')->insert(['id'=>1,'nombre'=>'Ciudad','id_region'=>1]);
        DB::table('especialidades')->insert([['id'=>1,'nombre'=>'Veterinaria'],['id'=>2,'nombre'=>'Otra']]);
        DB::table('tipos_especialidad')->insert(['id'=>1,'nombre'=>'Cirugía','id_especialidad'=>1]);
        DB::table('sub_tipo_especialidad')->insert(['id'=>1,'nombre'=>'General','id_tipo_especialidad'=>1]);
        Auth::setUser(User::find(1));
    }

    private function req(array $data): Request {return Request::create('/perfil','POST',$data);}
    private function servicio(): PerfilUsuario {return app(PerfilUsuario::class);}

    public function test_cv_upload_can_be_saved_and_reopened_from_its_public_path(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $anterior=public_path();
        $temporal=storage_path('framework/testing/cv-'.uniqid());
        $this->app->usePublicPath($temporal);
        try {
            $request=Request::create('/perfil/archivo','POST',[],[],['file'=>\Illuminate\Http\UploadedFile::fake()->create('cv.pdf',20,'application/pdf')]);
            $resultado=app(\App\Http\Controllers\PerfilUsuarioController::class)->archivo($request,$this->servicio())->getData(true);
            $archivo=$resultado['archivo'];
            $guardado=$this->servicio()->busqueda($this->req(['buscador'=>1,'modalidad'=>2,'archivo'=>[$archivo['url'],$archivo['original_file_name'],$archivo['nombre_archivo'],$archivo['file_extension']]]));
            $this->assertSame(1,$guardado['estado']);
            $ruta=Asistente::find(10)->curriculum;
            $this->assertStringStartsWith('asistente/curriculum/10/',$ruta);
            $this->assertFileExists(public_path('storage/'.$ruta));
            $this->assertSame($archivo['url'],$guardado['archivo']['proceso']['url']);
        } finally {
            $this->app->usePublicPath($anterior);
            \Illuminate\Support\Facades\File::deleteDirectory($temporal);
        }
    }

    public function test_search_preferences_preserve_existing_cv_and_reject_another_assistants_file(): void
    {
        DB::table('asistentes')->where('id',10)->update(['curriculum'=>'asistente/curriculum/10/anterior.pdf']);
        $resultado=$this->servicio()->busqueda($this->req(['buscador'=>2]));
        $this->assertSame(1,$resultado['estado']);
        $this->assertSame('asistente/curriculum/10/anterior.pdf',Asistente::find(10)->curriculum);
        try{$this->servicio()->busqueda($this->req(['buscador'=>1]));$this->fail();}
        catch(ValidationException $e){$this->assertArrayHasKey('modalidad',$e->errors());}
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->servicio()->busqueda($this->req(['buscador'=>2,'archivo'=>['/storage/asistente/curriculum/20/ajeno.pdf']]));
    }

    public function test_both_profile_pages_load_without_an_address_or_specialty(): void
    {
        $asistente=app(EscritorioAsistente::class)->perfil();
        $this->assertSame('app.asistente.perfil_asistente',$asistente->name());
        $this->assertSame('',$asistente->getData()['direccion_region_nombre']);
        $profesional=app(EscritorioProfesional::class)->mi_perfil();
        $this->assertSame('app.profesional.perfil_profesional',$profesional->name());
        $this->assertSame('',$profesional->getData()['direccion_id_region_profesional']);
        foreach([$asistente,$profesional] as $vista) {
            $fuente=file_get_contents(resource_path('views/'.str_replace('.','/',$vista->name()).'.blade.php'));
            $fuente=preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente);
            $fuente=str_replace('@endsection','',$fuente);
            $html=\Illuminate\Support\Facades\Blade::render($fuente,$vista->getData());
            $this->assertStringContainsString('perfil-usuario.js',$html);
            $this->assertStringContainsString('/Mi-perfil/contrasena',$html);
        }
    }

    public function test_general_veterinarian_profile_does_not_offer_unrelated_subspecialties(): void
    {
        DB::table('profesionales')->where('id',10)->update(['id_especialidad'=>1]);
        $datos=app(EscritorioProfesional::class)->mi_perfil()->getData();
        $this->assertSame([1],$datos['tipo_especialidades']->pluck('id')->all());
        $this->assertCount(0,$datos['sub_tipo_especialidades']);
        $this->assertSame('',$datos['txt_sub_tipo_especialidades']);
    }

    public function test_professional_specialty_options_submit_their_own_ids(): void
    {
        DB::table('profesionales')->where('id',10)->update(['id_especialidad'=>1]);
        DB::table('tipos_especialidad')->insert(['id'=>7,'nombre'=>'Medicina veterinaria general','id_especialidad'=>1]);
        $vista=app(EscritorioProfesional::class)->mi_perfil();
        $fuente=file_get_contents(resource_path('views/app/profesional/perfil_profesional.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        $html=\Illuminate\Support\Facades\Blade::render($fuente,$vista->getData());
        preg_match('/<select[^>]+id="id_tipo_especialidad"[^>]*>(.*?)<\/select>/s',$html,$select);
        $this->assertMatchesRegularExpression('/<option value="7">\s*Medicina veterinaria general\s*<\/option>/',$select[1]);
        $this->servicio()->personales('profesional',$this->req(['nombre'=>'Veterinario','apellido_uno'=>'General','id_tipo_especialidad'=>7]));
        $this->assertEquals(7,Profesional::find(10)->id_tipo_especialidad);
        $this->assertNull(Profesional::find(10)->id_sub_tipo_especialidad);
    }

    public function test_contact_without_address_can_be_edited_only_by_its_owner(): void
    {
        DB::table('contactos_emergencia')->insert(['id'=>1,'rut'=>'33333333-3','nombre'=>'Contacto']);
        DB::table('asistente_contacto_emergencia')->insert(['id_asistente'=>10,'id_contacto'=>1]);
        $controller=app(EscritorioAsistente::class);
        $contacto=$controller->cargar_datos_contacto($this->req(['id_contacto'=>1]));
        $this->assertNull($contacto->ciudad);
        $controller->editar_contacto_emergencia($this->req(['id_contacto'=>1,'rut'=>'33333333-3','nombres'=>'Contacto editado','apellido_uno'=>'Apellido','id_ciudad'=>1,'direccion'=>'Calle']));
        $this->assertSame('Contacto editado',DB::table('contactos_emergencia')->value('nombre'));
        $this->assertNotNull(DB::table('contactos_emergencia')->value('id_direccion'));
        Auth::setUser(User::find(2));
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $controller->cargar_datos_contacto($this->req(['id_contacto'=>1]));
    }

    public function test_bank_records_and_academic_history_cannot_be_deleted_by_another_user(): void
    {
        DB::table('profesional_antecedente_academico')->insert(['id'=>1,'id_profesional'=>20,'nombre'=>'Ajeno']);
        $resultado=app(EscritorioProfesional::class)->eliminarAntecedenteAcademico($this->req(['id'=>1]));
        $this->assertSame(0,$resultado['estado']);
        $this->assertSame(1,DB::table('profesional_antecedente_academico')->count());
        DB::table('liquidacion_recibo')->insert(['id'=>1,'id_seccion'=>2]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(\App\Http\Controllers\LiquidacionReciboController::class)->eliminarLiquidacion($this->req(['id'=>1]));
    }

    public function test_invalid_encrypted_bank_data_does_not_break_profile_loading(): void
    {
        DB::table('liquidacion_recibo')->insert(['id_seccion'=>1,'casa'=>'dato antiguo inválido','autor'=>encrypt('Nombre')]);
        $registros=$this->servicio()->liquidaciones(1);
        $this->assertCount(1,$registros);
        $this->assertSame('Nombre',$registros->first()->autor);
        $this->assertNull($registros->first()->banco);
    }

    public function test_both_profiles_create_and_reuse_their_first_address(): void
    {
        foreach(['asistente','profesional'] as $tipo){
            $campo=$tipo==='asistente'?'id_ciudad':'ciudad';
            $resultado=$this->servicio()->direccion($tipo,$this->req([$campo=>1,'region'=>1,'direccion'=>'Calle nueva','numero_dir'=>'12 B']));
            $modelo=$tipo==='asistente'?Asistente::class:Profesional::class;
            $id=$modelo::find(10)->id_direccion;
            $this->assertEquals($resultado['direccion']->id,$id);
            $this->servicio()->direccion($tipo,$this->req([$campo=>1,'direccion'=>'Calle editada','numero_dir'=>'20']));
            $this->assertEquals($id,$modelo::find(10)->id_direccion);
            $this->assertSame('Calle editada',$modelo::find(10)->Direccion()->first()->direccion);
        }
        $this->assertSame(2,DB::table('direcciones')->count());
    }

    public function test_professional_initial_profile_rejects_specialty_from_another_profession(): void
    {
        $controller=app(\App\Http\Controllers\Perfil\CompletarPerfilController::class);
        $method=new \ReflectionMethod($controller,'guardarProfesional');
        $this->expectException(ValidationException::class);
        $method->invoke($controller,$this->req(['id_especialidad'=>2,'id_tipo_especialidad'=>1]),User::find(1));
    }

    public function test_professional_initial_profile_allows_omitted_optional_specialty(): void
    {
        Schema::table('profesionales',fn(Blueprint $t)=>$t->integer('estado')->nullable());
        $controller=app(\App\Http\Controllers\Perfil\CompletarPerfilController::class);
        (new \ReflectionMethod($controller,'guardarProfesional'))->invoke($controller,$this->req(['id_especialidad'=>1]),User::find(1));
        $this->assertNull(Profesional::find(10)->id_tipo_especialidad);
        $this->assertEquals(1,Profesional::find(10)->id_especialidad);
    }

    public function test_initial_profile_rejects_city_from_another_region(): void
    {
        $controller=app(\App\Http\Controllers\Perfil\CompletarPerfilController::class);
        $this->expectException(ValidationException::class);
        (new \ReflectionMethod($controller,'validarCiudadRegion'))->invoke($controller,['id_ciudad'=>1,'id_region'=>2]);
    }

    public function test_professional_address_edit_keeps_shared_tutor_and_assistant_address(): void
    {
        Schema::create('pacientes',function(Blueprint $t){$t->id();$t->integer('id_direccion')->nullable();});
        DB::table('direcciones')->insert(['id'=>1,'id_ciudad'=>1,'direccion'=>'Original','numero_dir'=>'10']);
        DB::table('profesionales')->where('id',10)->update(['id_direccion'=>1]);
        DB::table('asistentes')->where('id',10)->update(['id_direccion'=>1]);
        DB::table('pacientes')->insert(['id_direccion'=>1]);
        $this->servicio()->direccion('profesional',$this->req(['ciudad'=>1,'direccion'=>'Nueva','numero_dir'=>'20']));
        $this->assertNotEquals(1,Profesional::find(10)->id_direccion);
        $this->assertEquals(1,Asistente::find(10)->id_direccion);
        $this->assertSame('Original',DB::table('direcciones')->where('id',1)->value('direccion'));
        $this->assertEquals(1,DB::table('pacientes')->value('id_direccion'));
    }

    public function test_professional_accepts_not_applicable_specialties(): void
    {
        DB::table('profesionales')->where('id',10)->update(['id_especialidad'=>1,'id_tipo_especialidad'=>1,'id_sub_tipo_especialidad'=>1]);
        $this->servicio()->personales('profesional',$this->req(['nombre'=>'Vet','apellido_uno'=>'Apellido','id_tipo_especialidad'=>0,'id_sub_tipo_especialidad'=>0]));
        $this->assertNull(Profesional::find(10)->id_tipo_especialidad);
        $this->assertNull(Profesional::find(10)->id_sub_tipo_especialidad);
        $this->assertEquals(1,Profesional::find(10)->id_especialidad);
    }

    public function test_professional_cannot_edit_another_profile_through_any_profile_endpoint(): void
    {
        foreach (['editar_datos_personales_perfil','editar_datos_contacto_perfil','editar_datos_residencia_perfil'] as $metodo) {
            try {app(EscritorioProfesional::class)->{$metodo}($this->req(['id_profesional'=>20]));$this->fail('Debe rechazar un perfil ajeno.');}
            catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {$this->assertSame(403,$error->getStatusCode());}
        }
    }

    public function test_wrong_city_region_does_not_create_an_address(): void
    {
        try{$this->servicio()->direccion('profesional',$this->req(['ciudad'=>1,'region'=>2,'direccion'=>'Calle']));$this->fail();}
        catch(ValidationException $e){$this->assertArrayHasKey('ciudad',$e->errors());}
        $this->assertSame(0,DB::table('direcciones')->count());
    }

    public function test_contact_email_is_synced_with_login_for_both_profiles(): void
    {
        foreach(['asistente','profesional'] as $tipo){
            $email=$tipo.'@example.test';
            $this->servicio()->contacto($tipo,$this->req(['email'=>$email,'telefono_uno'=>'+56 9 1234 5678']));
            $this->assertSame($email,User::find(1)->email);
            $this->assertSame($email,DB::table($tipo==='asistente'?'asistentes':'profesionales')->where('id',10)->value('email'));
        }
    }

    public function test_duplicate_email_is_rejected_without_partial_changes(): void
    {
        try{$this->servicio()->contacto('asistente',$this->req(['email'=>'dos@example.test']));$this->fail();}
        catch(ValidationException $e){$this->assertArrayHasKey('email',$e->errors());}
        $this->assertSame('uno@example.test',User::find(1)->email);
        $this->assertSame('uno@example.test',Asistente::find(10)->email);
    }

    public function test_contact_transaction_rolls_back_login_when_profile_save_fails(): void
    {
        DB::statement("CREATE TRIGGER fallo BEFORE UPDATE ON asistentes BEGIN SELECT RAISE(FAIL,'Fallo simulado'); END");
        try{$this->servicio()->contacto('asistente',$this->req(['email'=>'nuevo@example.test']));$this->fail();}
        catch(\Illuminate\Database\QueryException $e){$this->assertStringContainsString('Fallo simulado',$e->getMessage());}
        $this->assertSame('uno@example.test',User::find(1)->email);
    }

    public function test_assistant_cannot_edit_another_profile_even_without_clinical_context(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(EscritorioAsistente::class)->editar_datos_contacto_perfil($this->req(['id_asistente'=>20,'email'=>'robo@example.test']));
    }

    public function test_professional_optional_values_are_preserved_and_college_number_saves(): void
    {
        DB::table('profesionales')->where('id',10)->update(['num_colegio'=>'Anterior','id_especialidad'=>1,'id_tipo_especialidad'=>1,'id_sub_tipo_especialidad'=>1]);
        $respuesta=app(EscritorioProfesional::class)->editar_datos_personales_perfil($this->req(['nombre'=>'Nombre','apellido_uno'=>'Apellido','sexo'=>'F']));
        $this->assertTrue($respuesta->getData(true)['success']);
        $this->assertSame('Anterior',Profesional::find(10)->num_colegio);
        $this->assertEquals(1,Profesional::find(10)->id_tipo_especialidad);
        $this->servicio()->personales('profesional',$this->req(['nombre'=>'Nombre','apellido_uno'=>'Apellido','num_colegio'=>'Colegio 42']));
        $this->assertSame('Colegio 42',Profesional::find(10)->num_colegio);
        $this->assertSame('Nombre Apellido',User::find(1)->name);
    }

    public function test_professional_rejects_mismatched_specialties(): void
    {
        $this->expectException(ValidationException::class);
        $this->servicio()->personales('profesional',$this->req(['nombre'=>'Nombre','apellido_uno'=>'Apellido','id_especialidad'=>2,'id_tipo_especialidad'=>1]));
    }

    public function test_assistant_birthdate_remains_optional_and_persists_when_provided(): void
    {
        $data=['nombres'=>'Nombre','apellido_uno'=>'Apellido','nacimiento'=>'1990-05-10'];
        $this->servicio()->personales('asistente',$this->req($data));
        $this->assertSame('1990-05-10',Asistente::find(10)->fecha_nac);
        unset($data['nacimiento']);$this->servicio()->personales('asistente',$this->req($data));
        $this->assertSame('1990-05-10',Asistente::find(10)->fecha_nac);
    }

    public function test_password_changes_only_with_correct_current_password(): void
    {
        $data=['contrasena_actual'=>'incorrecta','password_registro'=>'NuevaClave123','password_confirmacion_registro'=>'NuevaClave123'];
        try{$this->servicio()->contrasena($this->req($data));$this->fail();}
        catch(ValidationException $e){$this->assertArrayHasKey('contrasena_actual',$e->errors());}
        $this->assertTrue(Hash::check('ClaveAnterior123',User::find(1)->password));
        $data['contrasena_actual']='ClaveAnterior123';$this->servicio()->contrasena($this->req($data));
        $this->assertTrue(Hash::check('NuevaClave123',User::find(1)->password));
        $this->assertTrue(Hash::check('OtraClave123',User::find(2)->password));
    }

    public function test_all_assistant_types_can_access_own_profile_and_photos_without_workspace(): void
    {
        foreach(config('asistentes.roles') as $rol){
            $usuario=\Mockery::mock(User::class)->makePartial();$usuario->id=1;
            $usuario->shouldReceive('hasAnyRole')->andReturn(true);$usuario->shouldReceive('hasRole')->andReturn(false);
            foreach(['asistentes.perfil','asistentes.perfil.contacto','asistentes.foto.guardar','perfil.usuario.contrasena'] as $nombre){
                $request=$this->req([]);$request->setUserResolver(fn()=>$usuario);
                $route=new Route('POST','/perfil',fn()=>null);$route->name($nombre);$request->setRouteResolver(fn()=>$route);
                $respuesta=app(AutorizarAsistente::class)->handle($request,fn()=>response('ok'));
                $this->assertSame('ok',$respuesta->getContent(),$rol.' '.$nombre);
            }
        }
    }
    public function test_crea_contacto_nuevo_y_repetirlo_no_duplica_su_vinculacion(): void
    {
        $controller = app(EscritorioAsistente::class);
        $datos = ['rut' => '33333333-3', 'nombres' => 'Contacto', 'apellido_uno' => 'Nuevo', 'id_ciudad' => 1, 'telefono' => '912345678', 'parentezco' => 'Hermano'];
        $primero = json_decode($controller->registrar_contacto_emergencia($this->req($datos)), true);
        $segundo = json_decode($controller->registrar_contacto_emergencia($this->req($datos)), true);
        $this->assertSame($primero['id'], $segundo['id']);
        $this->assertSame(1, DB::table('asistente_contacto_emergencia')->count());
        $this->assertSame(1, DB::table('contactos_emergencia')->count());
        $this->assertNotNull($primero['id_direccion']);
    }

    public function test_registrar_un_rut_ajeno_no_modifica_el_contacto_de_otro_asistente(): void
    {
        DB::table('contactos_emergencia')->insert(['id' => 1, 'rut' => '33333333-3', 'nombre' => 'Original']);
        DB::table('asistente_contacto_emergencia')->insert(['id_asistente' => 20, 'id_contacto' => 1]);
        $nuevo = json_decode(app(EscritorioAsistente::class)->registrar_contacto_emergencia($this->req([
            'rut' => '33333333-3', 'nombres' => 'Mi contacto', 'apellido_uno' => 'Apellido', 'id_ciudad' => 1,
        ])), true);
        $this->assertNotSame(1, $nuevo['id']);
        $this->assertSame('Original', DB::table('contactos_emergencia')->where('id', 1)->value('nombre'));
        $this->assertEquals(1, DB::table('asistente_contacto_emergencia')->where('id_asistente', 20)->value('id_contacto'));
    }

    public function test_editar_un_contacto_compartido_conserva_los_datos_del_otro_perfil(): void
    {
        DB::table('direcciones')->insert(['id' => 1, 'id_ciudad' => 1, 'direccion' => 'Dirección original']);
        DB::table('contactos_emergencia')->insert(['id' => 1, 'rut' => '33333333-3', 'nombre' => 'Original', 'id_direccion' => 1]);
        DB::table('asistente_contacto_emergencia')->insert([['id_asistente' => 10, 'id_contacto' => 1], ['id_asistente' => 20, 'id_contacto' => 1]]);
        $editado = json_decode(app(EscritorioAsistente::class)->editar_contacto_emergencia($this->req([
            'id_contacto' => 1, 'rut' => '33333333-3', 'nombres' => 'Modificado', 'apellido_uno' => 'Apellido', 'id_ciudad' => 1, 'direccion' => 'Dirección nueva',
        ])), true);
        $this->assertNotSame(1, $editado['id']);
        $this->assertSame('Original', DB::table('contactos_emergencia')->where('id', 1)->value('nombre'));
        $this->assertSame('Dirección original', DB::table('direcciones')->where('id', 1)->value('direccion'));
        $this->assertEquals($editado['id'], DB::table('asistente_contacto_emergencia')->where('id_asistente', 10)->value('id_contacto'));
    }

    public function test_rutas_antiguas_aplican_propiedad_validacion_y_sincronizan_la_cuenta(): void
    {
        foreach ([EscritorioAsistente::class, \App\Http\Controllers\EscritorioAsistenteCmPublico::class, \App\Http\Controllers\EscritorioAsistenteCmJefe::class] as $clase) {
            foreach (['editar_datos_personales_perfil', 'editar_datos_contacto_perfil', 'editar_datos_direccion_perfil'] as $metodo) {
                try {
                    app($clase)->{$metodo}($this->req(['id_asistente' => 20]));
                    $this->fail('No debe editar perfiles ajenos.');
                } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                    $this->assertSame(403, $e->getStatusCode());
                }
            }
            $respuesta = app($clase)->editar_datos_personales_perfil($this->req(['id_asistente' => 10, 'nombres' => 'Nombre Actual', 'apellido_uno' => 'Apellido', 'apellido_dos' => 'Segundo']));
            $this->assertTrue($respuesta->getData(true)['success']);
            $this->assertSame('Nombre Actual Apellido', User::find(1)->nombreParaMostrar());
            $this->assertSame('Nombre Actual', User::find(1)->nombres);
            $this->assertSame('Segundo', User::find(1)->apellido_dos);
        }
    }

    public function test_cambiar_rut_rechaza_el_de_otra_cuenta_y_contacto_sincroniza_telefono(): void
    {
        DB::table('users')->where('id', 2)->update(['rut' => '33333333-3']);
        try {
            $this->servicio()->personales('asistente', $this->req(['nombres' => 'Nombre', 'apellido_uno' => 'Apellido', 'rut' => '33333333-3']));
            $this->fail('No debe usar el RUT de otra identidad.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('rut', $e->errors());
        }
        $this->servicio()->contacto('asistente', $this->req(['email' => 'NUEVO@EXAMPLE.TEST', 'telefono_uno' => '+56 9 1234 5678']));
        $this->assertSame('nuevo@example.test', User::find(1)->email);
        $this->assertSame('+56 9 1234 5678', User::find(1)->telefono);
        $this->assertSame('nuevo@example.test', Asistente::find(10)->email);
    }

}
