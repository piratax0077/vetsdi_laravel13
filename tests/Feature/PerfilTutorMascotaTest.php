<?php

namespace Tests\Feature;

use App\Http\Controllers\{EscritorioPaciente, MascotasController, MascotaGenealogiaController};
use App\Models\{Mascota, Paciente, User};
use App\Services\PerfilTutor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Hash, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PerfilTutorMascotaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        foreach([
            'users'=>['name','nombres','apellido_uno','apellido_dos','telefono','email','password'],
            'pacientes'=>['id_usuario','rut','nombres','apellido_uno','apellido_dos','sexo','fecha_nac','email','telefono_uno','id_direccion','foto_perfil','id_prevision'],
            'profesionales'=>['id_usuario','id_direccion','foto_perfil'],
            'asistentes'=>['id_usuario','id_direccion'],'asistente_contacto_emergencia'=>['id_asistente','id_contacto'],'pacientes_dependientes'=>['id_responsable','id_paciente'],
            'direcciones'=>['id_ciudad','direccion','numero_dir'],'ciudades'=>['id_region','nombre'],'regiones'=>['nombre'],'previsiones'=>['nombre'],
            'especies_mascotas'=>['nombre','requiere_detalle'],'tamanos_mascotas'=>['nombre','slug'],'razas_mascotas'=>['nombre','slug','especie_id'],'especie_tamano_mascota'=>['especie_id','tamano_id'],
            'mascotas'=>['id_responsable','tiene_chip','chip','nombre','especie_id','especie','raza_id','otra_especie','tamano_id','tamano','fecha_nacimiento','sexo','foto_perfil','galeria','observaciones_fotos','esterilizado','fecha_esterilizacion','enfermedad_cronica','dieta','ultima_desparasitacion','producto_desparasitacion','cirugias','vacunas','viajes','vive_con_animales','vacunas_registro','desparasitaciones_registro','suscripciones_servicios_registro','reservas_servicios_registro','id_user','estado','ficha_incompleta_veterfarma','fallecida'],
            'mascotas_lugares_atencion'=>['id_mascota','id_lugar_atencion','id_institucion','origen'],'lugares_atencion'=>['nombre'],'sucursal'=>['id_lugar_atencion','id_institucion'],
            'contactos_emergencia'=>['rut','nombre','apellido_uno','apellido_dos','email','telefono','id_direccion','parentezco','prioridad','fecha_nac'],'pacientes_contacto_emergencia'=>['id_paciente','id_contacto','relacion'],
        ]as $tabla=>$campos)Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
        Schema::table('contactos_emergencia',fn(Blueprint $t)=>$t->unique('email','contactos_emergencia_email_unique'));
        (require database_path('migrations/2026_10_13_230000_allow_independent_tutor_emergency_contacts.php'))->up();
        DB::table('users')->insert([['id'=>1,'name'=>'Tutor Uno','email'=>'uno@example.test','password'=>Hash::make('Anterior123')],['id'=>2,'name'=>'Otro','email'=>'otro@example.test','password'=>Hash::make('Anterior123')],['id'=>3,'name'=>'Sin tutor','email'=>'sin@example.test','password'=>Hash::make('Anterior123')]]);
        DB::table('pacientes')->insert([['id'=>9,'id_usuario'=>1,'nombres'=>'Tutor','apellido_uno'=>'Uno','rut'=>'11111111-1','email'=>'uno@example.test','foto_perfil'=>'fotos_perfil/tutor.png'],['id'=>20,'id_usuario'=>2,'nombres'=>'Otro','apellido_uno'=>'Tutor','rut'=>'22222222-2','email'=>'otro@example.test','foto_perfil'=>null]]);
        DB::table('regiones')->insert([['id'=>1,'nombre'=>'Región Uno'],['id'=>2,'nombre'=>'Región Dos']]);DB::table('ciudades')->insert(['id'=>1,'id_region'=>1,'nombre'=>'Comuna']);
        DB::table('especies_mascotas')->insert([['id'=>1,'nombre'=>'Canina','requiere_detalle'=>0],['id'=>2,'nombre'=>'Felina','requiere_detalle'=>0]]);
        DB::table('tamanos_mascotas')->insert(['id'=>1,'nombre'=>'Pequeña','slug'=>'pequena']);DB::table('razas_mascotas')->insert(['id'=>1,'especie_id'=>1,'nombre'=>'Mestizo','slug'=>'mestizo']);DB::table('especie_tamano_mascota')->insert([['especie_id'=>1,'tamano_id'=>1],['especie_id'=>2,'tamano_id'=>1]]);
        $base=['nombre'=>'Mascota','especie_id'=>1,'especie'=>1,'raza_id'=>1,'tamano_id'=>1,'tamano'=>'pequena','tiene_chip'=>0,'esterilizado'=>1,'fecha_esterilizacion'=>'2021-01-01','fecha_nacimiento'=>'2020-01-01','sexo'=>'F','foto_perfil'=>'mascotas/foto.png','galeria'=>json_encode(['ven_pre'=>[['imagenes/temp/foto.png']]]),'estado'=>1,'dieta'=>'Dieta registrada','enfermedad_cronica'=>'Antecedente','vacunas'=>'Vacunada','cirugias'=>'Cirugía previa'];
        DB::table('mascotas')->insert([['id'=>1,'id_responsable'=>9]+$base,['id'=>2,'id_responsable'=>20]+$base]);
        $this->usuario(1);
    }

    private function usuario(int $id, bool $profesional = false): void
    {
        $user=\Mockery::mock(User::class)->makePartial();$user->setRawAttributes(User::find($id)->getAttributes());$user->shouldReceive('hasRole')->andReturn($profesional);$user->shouldReceive('hasAnyRole')->andReturn($profesional);Auth::setUser($user);
    }
    private function req(array $data=[]): Request{return Request::create('/perfil','POST',$data);}
    private function tutor(): PerfilTutor{return app(PerfilTutor::class);}
    private function actualizar(array $data=[],int $id=1){return app(MascotasController::class)->update($this->req($data),Mascota::find($id));}

    public function test_tutor_form_renders_without_birthdate_or_address_and_without_duplicate_editor_ids(): void
    {
        $fuente=file_get_contents(resource_path('views/app/paciente/perfil_paciente.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        $html=Blade::render($fuente,['paciente'=>Paciente::find(9),'direccion_paciente'=>null,'direccion_id_ciudad_paciente'=>'','direccion_txt_ciudad_paciente'=>'','direccion_id_region_paciente'=>'','direccion_txt_region_paciente'=>'','previsiones'=>collect(),'regiones'=>DB::table('regiones')->get(),'ciudades'=>DB::table('ciudades')->get(),'contacto'=>collect()]);
        $this->assertStringContainsString('perfil-tutor.js',$html);$this->assertStringContainsString('Sin registro',$html);
        foreach(['Perfil_email','Perfil_fono','perfil_dire','perfil_region','perfil_ciudad','perfil_numero_dir'] as $id)$this->assertSame(1,substr_count($html,'id="'.$id.'"'),$id);
        $this->assertSame(0,substr_count($html,'id="contacto_email"'));
        $this->assertStringNotContainsString('value="direccion }}',$html);
    }

    public function test_profile_syncs_account_identity_and_ignores_human_insurance(): void
    {
        $this->tutor()->guardar($this->req(['perfil_nombre'=>'Nuevo','perfil_apellido_uno'=>'Tutor','perfil_fono'=>'12345','perfil_email'=>' NUEVO@example.test ','perfil_prevision'=>999]));
        $user=User::find(1);
        $this->assertSame('Nuevo',$user->nombres);
        $this->assertSame('Tutor',$user->apellido_uno);
        $this->assertSame('12345',$user->telefono);
        $this->assertSame('nuevo@example.test',$user->email);
        $this->assertNull(Paciente::find(9)->id_prevision);
    }

    public function test_contact_shared_with_assistant_keeps_private_address_even_when_initial_edit_omits_it(): void
    {
        DB::table('direcciones')->insert(['id'=>1,'id_ciudad'=>1,'direccion'=>'Original']);
        DB::table('contactos_emergencia')->insert(['id'=>1,'rut'=>'333','nombre'=>'Original','apellido_uno'=>'Apellido','id_direccion'=>1,'telefono'=>'123']);
        DB::table('pacientes_contacto_emergencia')->insert(['id_paciente'=>9,'id_contacto'=>1]);
        DB::table('asistente_contacto_emergencia')->insert(['id_asistente'=>5,'id_contacto'=>1]);
        $controller=app(\App\Http\Controllers\PerfilTutorContactoController::class);
        $data=['id_contacto'=>1,'rut'=>'333','nombres'=>'Editado','apellido_uno'=>'Apellido'];
        $edited=json_decode($controller->guardar($this->req($data)),true);
        $this->assertNotEquals(1,$edited['id_direccion']);
        $this->assertSame('123',$edited['telefono']);
        $data['id_contacto']=$edited['id'];$data['direccion']='Nueva';$data['id_ciudad']=1;
        $controller->guardar($this->req($data));
        $this->assertSame('Original',DB::table('direcciones')->where('id',1)->value('direccion'));
        $this->assertSame('Original',DB::table('contactos_emergencia')->where('id',1)->value('nombre'));
    }

    public function test_tutor_cannot_read_an_unrelated_dependent(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(EscritorioPaciente::class)->getPacienteUser($this->req(['id_dependiente_activo'=>20]));
    }

    public function test_registering_pet_assigns_current_tutor_and_uses_uploaded_gallery_photo(): void
    {
        $r=app(MascotasController::class)->store($this->req(['id_responsable'=>20,'tiene_chip'=>0,'nombre'=>'Nueva mascota','especie_id'=>2,'tamano_id'=>1,'sexo'=>'M','esterilizado'=>0,'galeria'=>['ven_pre'=>[['/storage/imagenes/temp/nueva.png']]]]));
        $this->assertSame(200,$r->getStatusCode());$m=Mascota::find($r->getData(true)['id']);$this->assertEquals(9,$m->id_responsable);$this->assertSame('imagenes/temp/nueva.png',$m->foto_perfil);
    }

    public function test_removing_tutor_photo_keeps_shared_professional_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $ruta='fotos_perfil/compartida.png';\Illuminate\Support\Facades\Storage::disk('public')->put($ruta,'foto');
        DB::table('pacientes')->where('id',9)->update(['foto_perfil'=>$ruta]);DB::table('profesionales')->insert(['id_usuario'=>1,'foto_perfil'=>$ruta]);
        app(\App\Services\Fotografias::class)->quitarPerfil(Paciente::find(9));
        $this->assertNull(Paciente::find(9)->foto_perfil);\Illuminate\Support\Facades\Storage::disk('public')->assertExists($ruta);
    }

    public function test_emergency_contact_edit_is_isolated_from_another_tutors_contact(): void
    {
        DB::table('contactos_emergencia')->insert(['id'=>1,'rut'=>'33333333-3','nombre'=>'Contacto original','apellido_uno'=>'Apellido','email'=>'contacto@example.test']);DB::table('pacientes_contacto_emergencia')->insert([['id_paciente'=>9,'id_contacto'=>1],['id_paciente'=>20,'id_contacto'=>1]]);
        $controller=app(\App\Http\Controllers\PerfilTutorContactoController::class);
        $datos=$controller->datos($this->req(['id_contacto'=>1]))->getData(true);$this->assertSame('',$datos['direccion']['direccion']);
        $controller->guardar($this->req(['id_contacto'=>1,'rut'=>'33333333-3','nombres'=>'Contacto editado','apellido_uno'=>'Apellido','email'=>'contacto@example.test']));
        $this->assertSame('Contacto original',DB::table('contactos_emergencia')->where('id',1)->value('nombre'));
        $this->assertSame('Contacto editado',Paciente::find(9)->ContactosEmergencia()->first()->nombre);$this->assertSame('Contacto original',Paciente::find(20)->ContactosEmergencia()->first()->nombre);
        $lista=$controller->listar($this->req())->getData(true);$this->assertCount(1,$lista['registros']);$this->assertSame('Contacto editado',$lista['registros'][0]['nombre']);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);$controller->datos($this->req(['id_contacto'=>1]));
    }

    public function test_tutor_profile_opens_without_human_clinical_tables(): void
    {
        $datos=app(EscritorioPaciente::class)->perfil()->getData();
        $this->assertEquals(9,$datos['paciente']->id);
        $this->assertNull($datos['direccion_paciente']);
        $this->assertCount(0,$datos['contacto']);
        $this->assertCount(2,$datos['regiones']);
    }

    public function test_editing_contact_address_keeps_shared_tutor_address_and_omitted_fields(): void
    {
        DB::table('direcciones')->insert(['id'=>1,'id_ciudad'=>1,'direccion'=>'Original','numero_dir'=>'12']);
        DB::table('pacientes')->where('id',20)->update(['id_direccion'=>1]);
        DB::table('contactos_emergencia')->insert(['id'=>1,'rut'=>'333','nombre'=>'Contacto','apellido_uno'=>'Apellido','id_direccion'=>1,'parentezco'=>'Hermano']);
        DB::table('pacientes_contacto_emergencia')->insert(['id_paciente'=>9,'id_contacto'=>1,'relacion'=>'Hermano']);
        app(\App\Http\Controllers\PerfilTutorContactoController::class)->guardar($this->req([
            'id_contacto'=>1,'rut'=>'333','nombres'=>'Contacto','apellido_uno'=>'Apellido','direccion'=>'Nueva calle',
        ]));
        $contacto=Paciente::find(9)->ContactosEmergencia()->first();
        $direccion=DB::table('direcciones')->where('id',$contacto->id_direccion)->first();
        $this->assertNotEquals(1,$contacto->id_direccion);
        $this->assertSame('Original',DB::table('direcciones')->where('id',1)->value('direccion'));
        $this->assertSame('Nueva calle',$direccion->direccion);
        $this->assertEquals(1,$direccion->id_ciudad);
        $this->assertSame('12',$direccion->numero_dir);
        $this->assertSame('Hermano',$contacto->pivot->relacion);
    }

    public function test_tutor_saves_optional_birthdate_and_syncs_name_and_email(): void
    {
        $resultado=$this->tutor()->guardar($this->req(['perfil_nombre'=>'Tutor editado','perfil_apellido_uno'=>'Apellido','perfil_nac'=>null,'perfil_email'=>'nuevo@example.test']));
        $this->assertTrue($resultado['success']);$this->assertNull(Paciente::find(9)->fecha_nac);$this->assertSame('nuevo@example.test',User::find(1)->email);$this->assertSame('Tutor editado Apellido',User::find(1)->name);
        $this->assertSame('fotos_perfil/tutor.png',Paciente::find(9)->foto_perfil);$this->assertSame('Mascota',Mascota::find(1)->nombre);
    }

    public function test_duplicate_email_and_future_birthdate_are_rejected_without_changes(): void
    {
        foreach([['perfil_email'=>'otro@example.test'],['perfil_nac'=>'2999-01-01']] as $data){try{$this->tutor()->guardar($this->req($data));$this->fail();}catch(ValidationException $e){$this->assertNotEmpty($e->errors());}}
        $this->assertSame('uno@example.test',User::find(1)->email);
    }

    public function test_tutor_address_is_created_with_number_and_copied_when_shared_with_professional(): void
    {
        $data=['perfil_dire'=>'Calle','perfil_numero_dir'=>'12 B','perfil_region'=>1,'perfil_ciudad'=>1];$this->tutor()->guardar($this->req($data));$id=Paciente::find(9)->id_direccion;
        $this->assertSame('12 B',DB::table('direcciones')->where('id',$id)->value('numero_dir'));
        DB::table('profesionales')->insert(['id_usuario'=>1,'id_direccion'=>$id]);$data['perfil_dire']='Nueva calle';$this->tutor()->guardar($this->req($data));
        $this->assertNotEquals($id,Paciente::find(9)->id_direccion);$this->assertSame('Calle',DB::table('direcciones')->where('id',$id)->value('direccion'));
    }

    public function test_tutor_profile_transaction_rolls_back_address_and_email_on_failure(): void
    {
        DB::statement("CREATE TRIGGER fallo BEFORE UPDATE ON pacientes BEGIN SELECT RAISE(FAIL,'Fallo simulado'); END");
        try{$this->tutor()->guardar($this->req(['perfil_email'=>'nuevo@example.test','perfil_dire'=>'Calle','perfil_region'=>1,'perfil_ciudad'=>1]));$this->fail();}catch(\Illuminate\Database\QueryException $e){$this->assertStringContainsString('Fallo simulado',$e->getMessage());}
        $this->assertSame(0,DB::table('direcciones')->count());$this->assertSame('uno@example.test',User::find(1)->email);
    }

    public function test_contact_only_update_does_not_clear_personal_information(): void
    {
        $r=app(EscritorioPaciente::class)->editcontacto($this->req(['perfil_email'=>'contacto@example.test','perfil_fono'=>'+56 9 1234']));$this->assertTrue($r->getData(true)['success']);$this->assertSame('Tutor',Paciente::find(9)->nombres);
    }

    public function test_pet_partial_update_preserves_history_photos_dates_and_tutor(): void
    {
        $respuesta=$this->actualizar(['nombre'=>'Mascota editada','id_responsable'=>20]);$this->assertSame(200,$respuesta->getStatusCode());
        $m=Mascota::find(1);$this->assertSame('Mascota editada',$m->nombre);$this->assertEquals(9,$m->id_responsable);$this->assertSame('Dieta registrada',$m->dieta);$this->assertSame('Antecedente',$m->enfermedad_cronica);$this->assertSame('Vacunada',$m->vacunas);$this->assertSame('mascotas/foto.png',$m->foto_perfil);$this->assertNotEmpty($m->galeria);$this->assertSame('2021-01-01',$m->fecha_esterilizacion->format('Y-m-d'));
    }

    public function test_pet_profile_uses_photo_from_second_gallery_section_when_first_is_empty(): void
    {
        $mascota = new Mascota([
            'foto_perfil' => null,
            'galeria' => ['ven_pre' => [], 'ven_post' => [['imagenes/temp/segunda.png']]],
        ]);
        $this->assertSame(storage_public_url('imagenes/temp/segunda.png'), $mascota->foto_url);
        $mascota->foto_perfil = 'mascotas/principal.png';
        $this->assertSame(storage_public_url('mascotas/principal.png'), $mascota->foto_url);
        $mascota->foto_perfil = null;
        $mascota->galeria = null;
        $this->assertNull($mascota->foto_url);
    }

    public function test_updated_pet_photo_is_shared_by_menu_booking_carnet_and_json(): void
    {
        $this->assertSame(200, $this->actualizar(['foto_perfil' => 'mascotas/nueva.png'])->getStatusCode());
        $mascota = Mascota::find(1);
        $url = storage_public_url('mascotas/nueva.png');
        $this->assertSame($url, $mascota->toArray()['foto_url']);
        foreach ([
            'template/paciente_dependiente/menu' => 'fotoMascota',
            'app/general/buscador_profesionales/buscador' => 'foto_mascota_reserva',
            'app/paciente/carnet_sanitario_mascota' => 'imgMascota',
        ] as $vista => $variable) {
            preg_match('/@php\b.*?@endphp/s', file_get_contents(resource_path('views/'.$vista.'.blade.php')), $bloque);
            $html = Blade::render($bloque[0]."\n".'{{ $'.$variable.' }}', [
                'mascota' => $mascota, 'paciente' => Paciente::find(9),
                'responsable' => Paciente::find(9),
            ]);
            $this->assertSame(e($url), trim($html), $vista);
        }
        $this->actualizar(['nombre' => 'Nombre nuevo']);
        $this->assertSame($url, Mascota::find(1)->foto_url);
    }

    public function test_pet_optional_fields_can_be_cleared_explicitly(): void
    {
        $this->assertSame(200,$this->actualizar(['dieta'=>null,'fecha_nacimiento'=>null])->getStatusCode());
        $m=Mascota::find(1);$this->assertNull($m->dieta);$this->assertNull($m->fecha_nacimiento);$this->assertSame('2021-01-01',$m->fecha_esterilizacion->format('Y-m-d'));$this->assertTrue($m->esterilizado);
    }

    public function test_pet_cannot_be_edited_by_another_tutor(): void
    {
        $this->assertSame(404,$this->actualizar(['nombre'=>'Ajena'],2)->getStatusCode());$this->assertSame('Mascota',Mascota::find(2)->nombre);
    }

    public function test_invalid_gallery_does_not_erase_saved_photos(): void
    {
        $respuesta=$this->actualizar(['galeria'=>'JSON incorrecto']);$this->assertSame(422,$respuesta->getStatusCode());$this->assertNotEmpty(Mascota::find(1)->galeria);
    }

    public function test_pet_update_does_not_reactivate_an_inactive_record(): void
    {
        DB::table('mascotas')->where('id',1)->update(['estado'=>0]);$this->assertSame(200,$this->actualizar(['nombre'=>'Nombre corregido'])->getStatusCode());$this->assertEquals(0,Mascota::find(1)->estado);
    }

    public function test_failed_clinic_link_rolls_back_pet_update(): void
    {
        DB::table('lugares_atencion')->insert(['id'=>1,'nombre'=>'Clínica']);DB::table('sucursal')->insert(['id_lugar_atencion'=>1,'id_institucion'=>1]);
        DB::statement("CREATE TRIGGER fallo_vinculo BEFORE INSERT ON mascotas_lugares_atencion BEGIN SELECT RAISE(FAIL,'Fallo vínculo'); END");
        try{$this->actualizar(['nombre'=>'No guardar','id_lugar_atencion'=>1]);$this->fail();}catch(\Illuminate\Database\QueryException $e){$this->assertStringContainsString('Fallo vínculo',$e->getMessage());}
        $this->assertSame('Mascota',Mascota::find(1)->nombre);
    }

    public function test_user_without_tutor_profile_cannot_access_pet_genealogy(): void
    {
        $this->usuario(3);$this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);app(MascotaGenealogiaController::class)->show(Mascota::find(1));
    }

    public function test_tutor_cannot_target_another_tutors_profile(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->tutor()->guardar($this->req(['id_paciente'=>20,'perfil_nombre'=>'Ajeno']));
    }

    public function test_legacy_contact_endpoint_cannot_bypass_tutor_ownership(): void
    {
        DB::table('contactos_emergencia')->insert(['id'=>1,'nombre'=>'Ajeno']);DB::table('pacientes_contacto_emergencia')->insert(['id_paciente'=>20,'id_contacto'=>1]);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(\App\Http\Controllers\EscritorioProfesional::class)->cargar_datos_contacto($this->req(['id_contacto'=>1]));
    }

    public function test_sanitary_records_cannot_be_read_or_changed_by_another_tutor(): void
    {
        $controller=app(MascotasController::class);
        $this->assertSame(404,$controller->obtenerRegistrosSanitarios(2)->status());
        $this->assertSame(404,$controller->guardarVacunaDesdeModal($this->req(['fecha_dosis'=>'2026-01-01','vacuna'=>'Ajena']),2)->status());
        $this->assertEmpty(Mascota::find(2)->vacunas_registro);
    }

    public function test_vaccinations_persist_and_adding_older_deworming_does_not_replace_latest_summary(): void
    {
        Schema::create('horas_medicas',function(Blueprint $t){$t->id();$t->integer('id_profesional');$t->integer('id_mascota');$t->integer('id_paciente');});
        DB::table('profesionales')->insert(['id'=>5,'id_usuario'=>1]);
        DB::table('horas_medicas')->insert(['id_profesional'=>5,'id_mascota'=>1,'id_paciente'=>9]);
        $this->usuario(1,true);
        $controller=app(MascotasController::class);
        foreach(['Vacuna A','Vacuna B'] as $vacuna) $this->assertSame(1,$controller->guardarVacunaDesdeModal($this->req(['fecha_dosis'=>'2026-01-01','vacuna'=>$vacuna]),1)['estado']);
        foreach(['2026-06-01'=>'Reciente','2025-01-01'=>'Antigua'] as $fecha=>$producto) $this->assertSame(1,$controller->guardarDesparasitacionDesdeModal($this->req(['fecha_dosis'=>$fecha,'antiparasitario'=>$producto,'tipo'=>'Interno']),1)['estado']);
        $this->assertSame('Reciente',Mascota::find(1)->producto_desparasitacion);
        $this->assertSame('2026-06-01',Mascota::find(1)->ultima_desparasitacion->format('Y-m-d'));
        $datos=$controller->obtenerRegistrosSanitarios(1);
        $this->assertCount(2,$datos['vacunas']);$this->assertCount(2,$datos['desparasitaciones']);
        $this->assertSame(['Vacuna A','Vacuna B'],array_column($datos['vacunas'],'vacuna'));
    }

    public function test_previous_tutor_relationship_does_not_authorize_sanitary_changes(): void
    {
        foreach (['horas_medicas', 'fichas_atenciones'] as $tabla) {
            Schema::create($tabla, function(Blueprint $t) {
                $t->id(); $t->integer('id_profesional'); $t->integer('id_mascota'); $t->integer('id_paciente');
            });
            DB::table($tabla)->insert(['id_profesional'=>5,'id_mascota'=>1,'id_paciente'=>20]);
        }
        DB::table('profesionales')->insert(['id'=>5,'id_usuario'=>1]);
        $this->usuario(1,true);
        $acceso=app(\App\Services\AccesoSanitarioMascota::class);
        $this->assertFalse($acceso->puedeEscribir(Mascota::find(1)));
        DB::table('fichas_atenciones')->update(['id_paciente'=>9]);
        $this->assertTrue($acceso->puedeEscribir(Mascota::find(1)));
    }

    public function test_tutor_can_read_but_cannot_modify_own_sanitary_records(): void
    {
        $controller=app(MascotasController::class);
        $this->assertSame(1,$controller->obtenerRegistrosSanitarios(1)['estado']);
        foreach([
            fn()=> $controller->guardarVacunaDesdeModal($this->req(['fecha_dosis'=>'2026-01-01','vacuna'=>'No permitida']),1),
            fn()=> $this->actualizar(['vacunas'=>'Cambio no autorizado']),
            fn()=> $this->actualizar(['enfermedad_cronica'=>null]),
            fn()=> $this->actualizar(['esterilizado'=>0]),
            fn()=> $this->actualizar(['fecha_esterilizacion_desconocida'=>true]),
        ]as $operacion){
            try{$operacion();$this->fail('El tutor no debe editar registros sanitarios');}
            catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        }
        $this->assertSame('Vacunada',Mascota::find(1)->vacunas);
        $this->assertSame('Antecedente',Mascota::find(1)->enfermedad_cronica);
    }

    public function test_tutor_registers_deworming_indicating_who_did_it(): void
    {
        $controller=app(MascotasController::class);
        $datos=['fecha_dosis'=>'2026-01-01','antiparasitario'=>'Antiparasitario','tipo'=>'Interno'];
        $this->assertSame(422,$controller->guardarDesparasitacionDesdeModal($this->req($datos),1)->status());

        $respuesta=$controller->guardarDesparasitacionDesdeModal($this->req($datos+['realizado_por'=>'tutor']),1);
        $this->assertSame('Tutor: Tutor Uno',$respuesta['desparasitaciones'][0]['realizado_por']);
        $respuesta=$controller->guardarDesparasitacionDesdeModal($this->req($datos+['realizado_por'=>'profesional_externo']),1);
        $this->assertSame('Profesional externo',$respuesta['desparasitaciones'][1]['realizado_por']);
        $this->assertCount(2,Mascota::find(1)->desparasitaciones_registro);

        // La mascota de otro tutor no se encuentra para este usuario
        $this->assertSame(404,$controller->guardarDesparasitacionDesdeModal($this->req($datos+['realizado_por'=>'tutor']),2)->status());
    }
}
