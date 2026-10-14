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
            'users'=>['name','email','password'],
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
}
