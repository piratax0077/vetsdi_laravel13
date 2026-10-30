<?php

namespace Tests\Feature;

use App\Http\Controllers\OperacionVeterinariaController;
use App\Models\{Instituciones, User};
use App\Services\OperacionVeterinaria;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Blade, DB, Schema};
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperacionVeterinariaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('email');$t->timestamps();});
        Schema::create('instituciones',function(Blueprint $t){$t->id();$t->string('nombre');$t->integer('id_usuario');$t->integer('id_responsable')->nullable();$t->integer('id_lugar_atencion');$t->timestamps();});
        Schema::create('admin_inst_serv',function(Blueprint $t){$t->id();$t->integer('id_admin');});
        Schema::create('contrato_dependiente',function(Blueprint $t){$t->id();$t->integer('id_empleado');$t->string('tipo_empleado');$t->integer('estado');$t->integer('id_institucion');});
        Schema::create('mascotas',function(Blueprint $t){$t->id();$t->string('nombre');$t->string('especie');$t->integer('id_responsable')->nullable();});
        Schema::create('pacientes',function(Blueprint $t){$t->id();$t->string('rut');});
        Schema::create('horas_medicas',function(Blueprint $t){$t->id();$t->integer('id_lugar_atencion');$t->integer('id_mascota');});
        DB::table('users')->insert(['id'=>1,'name'=>'Admin','email'=>'admin@example.test']);
        DB::table('instituciones')->insert([['id'=>10,'nombre'=>'Centro veterinario Uno','id_usuario'=>1,'id_lugar_atencion'=>100],['id'=>20,'nombre'=>'Hospital ajeno','id_usuario'=>2,'id_lugar_atencion'=>200]]);
        DB::table('mascotas')->insert([['id'=>1,'nombre'=>'Luna','especie'=>'Canina'],['id'=>2,'nombre'=>'Milo','especie'=>'Felina'],['id'=>3,'nombre'=>'Mascota ajena','especie'=>'Canina']]);
        DB::table('horas_medicas')->insert([['id_lugar_atencion'=>100,'id_mascota'=>1],['id_lugar_atencion'=>100,'id_mascota'=>2],['id_lugar_atencion'=>200,'id_mascota'=>3]]);
        $this->migracion()->up();
        $this->migracionPacientes()->up();
        Auth::setUser(User::find(1));
    }

    private function migracion(){return require database_path('migrations/2026_10_28_000001_operacion_establecimientos_veterinarios.php');}
    private function migracionPacientes(){return require database_path('migrations/2026_10_28_000002_pacientes_establecimientos_veterinarios.php');}
    private function servicio(): OperacionVeterinaria{return app(OperacionVeterinaria::class);}
    private function req(array $datos=[]): Request{return Request::create('/Veterinaria/Operacion','POST',$datos+['id_institucion'=>10]);}
    private function guardar(string $accion,array $datos=[]): int{return $this->servicio()->ejecutar($this->req($datos),Instituciones::find(10),$accion);}
    private function plaza(string $codigo='A1'): int{return $this->guardar('plaza',['codigo'=>$codigo,'tipo'=>'jaula','activa'=>1]);}
    private function datosIngreso(int $plaza,int $mascota=1): array{return ['id_mascota'=>$mascota,'plaza_id'=>$plaza,'responsable'=>'Dra. Uno','ingreso'=>now()->subHours(2)->format('Y-m-d H:i:s'),'estado'=>'hospitalizada','motivo'=>'Observación'];}
    private function servicioCatalogo(string $modulo='peluqueria'): int{return $this->guardar('servicio',['modulo'=>$modulo,'nombre'=>'Servicio de '.$modulo,'duracion_minutos'=>60,'precio'=>15000,'activo'=>1]);}
    private function datosReserva(int $servicio,int $mascota=1): array{return ['id_mascota'=>$mascota,'servicio_id'=>$servicio,'responsable'=>'Responsable Uno','inicio'=>now()->addDay()->startOfDay()->addHours(10)->format('Y-m-d H:i:s'),'estado'=>'reservada'];}
    private function invalido(callable $accion,string $campo): void
    {
        try{$accion();$this->fail('Se esperaba error de validación.');}catch(ValidationException $e){$this->assertArrayHasKey($campo,$e->errors());}
    }

    public function test_migration_is_reversible_without_touching_legacy_tables(): void
    {
        $this->migracionPacientes()->down();$this->migracion()->down();$this->assertFalse(Schema::hasTable('vet_establecimientos'));$this->assertTrue(Schema::hasTable('instituciones'));
        $this->migracion()->up();$this->migracionPacientes()->up();$this->assertTrue(Schema::hasTable('vet_reservas_servicios'));
    }

    public function test_establishment_without_row_keeps_all_services_when_type_or_area_changes(): void
    {
        $this->servicio()->cambiarTipo(Instituciones::find(10),'hospital');
        $c=$this->servicio()->configuracion(10);
        $this->assertSame('hospital',$c->tipo);$this->assertTrue($c->hospitalizacion_habilitada);$this->assertSame(array_keys(OperacionVeterinaria::SERVICIOS),$c->servicios_habilitados);
        $this->servicio()->cambiarServicio(Instituciones::find(10),'dental',false);
        $this->assertSame(['peluqueria','laboratorio','imagenologia','vacunatorio'],$this->servicio()->configuracion(10)->servicios_habilitados);
        $this->assertSame(2,DB::table('vet_operacion_auditoria')->where('tabla','vet_establecimientos')->count());
    }

    public function test_hospitalization_area_uses_its_own_field_and_protects_active_stays(): void
    {
        $centro=Instituciones::find(10);
        $this->servicio()->cambiarServicio($centro,'hospitalizacion',false);
        $c=$this->servicio()->configuracion(10);
        $this->assertFalse($c->hospitalizacion_habilitada);$this->assertSame(array_keys(OperacionVeterinaria::SERVICIOS),$c->servicios_habilitados);
        $this->servicio()->cambiarServicio($centro,'hospitalizacion',true);
        $this->assertTrue($this->servicio()->configuracion(10)->hospitalizacion_habilitada);
        $this->guardar('internacion',$this->datosIngreso($this->plaza()));
        $this->invalido(fn()=>$this->servicio()->cambiarServicio($centro,'hospitalizacion',false),'hospitalizacion_habilitada');
        $this->assertTrue($this->servicio()->configuracion(10)->hospitalizacion_habilitada);
    }

    public function test_both_types_can_configure_services_and_keep_existing_records(): void
    {
        $plaza=$this->plaza();
        foreach(['centro','hospital'] as $tipo){
            $this->guardar('configuracion',['tipo'=>$tipo,'hospitalizacion_habilitada'=>1,'servicios_habilitados'=>['peluqueria','dental']]);
            $c=$this->servicio()->configuracion(10);$this->assertSame($tipo,$c->tipo);$this->assertSame(['peluqueria','dental'],$c->servicios_habilitados);
        }
        $this->assertEquals($plaza,DB::table('vet_plazas')->value('id'));
    }

    public function test_admission_prevents_duplicate_occupancy_and_duplicate_pet(): void
    {
        $a=$this->plaza();$b=$this->plaza('A2');$this->guardar('internacion',$this->datosIngreso($a));
        $this->invalido(fn()=>$this->guardar('internacion',$this->datosIngreso($a,2)),'plaza_id');
        $this->invalido(fn()=>$this->guardar('internacion',$this->datosIngreso($b,1)),'plaza_id');
        $this->assertSame(1,DB::table('vet_internaciones')->count());
    }

    public function test_discharge_releases_bed_and_keeps_evolutions_and_audit(): void
    {
        $plaza=$this->plaza();$d=$this->datosIngreso($plaza);$id=$this->guardar('internacion',$d);
        $this->guardar('evolucion',['internacion_id'=>$id,'fecha'=>now()->subHour()->toDateTimeString(),'observaciones'=>'Paciente en observación','peso'=>'4.250','temperatura'=>'38.5']);
        $d=array_merge($d,['id'=>$id,'estado'=>'alta','egreso'=>now()->toDateTimeString(),'observaciones_alta'=>'Egreso registrado por veterinaria']);
        $this->guardar('internacion',$d);
        $this->guardar('internacion',$this->datosIngreso($plaza,2));
        $this->assertSame('alta',DB::table('vet_internaciones')->find($id)->estado);
        $this->assertSame(1,DB::table('vet_evoluciones')->count());
        $this->assertSame(5,DB::table('vet_operacion_auditoria')->count());
        $this->invalido(fn()=>$this->guardar('internacion',$d),'estado');
        $this->invalido(fn()=>$this->guardar('evolucion',['internacion_id'=>$id,'fecha'=>now()->toDateTimeString(),'observaciones'=>'Tardía']),'internacion_id');
    }

    public function test_disabled_or_occupied_bed_cannot_be_used_or_disabled(): void
    {
        $id=$this->plaza();$this->guardar('plaza',['id'=>$id,'codigo'=>'A1','tipo'=>'jaula','activa'=>0]);
        $this->invalido(fn()=>$this->guardar('internacion',$this->datosIngreso($id)),'plaza_id');
        $this->guardar('plaza',['id'=>$id,'codigo'=>'A1','tipo'=>'jaula','activa'=>1]);$this->guardar('internacion',$this->datosIngreso($id));
        $this->invalido(fn()=>$this->guardar('plaza',['id'=>$id,'codigo'=>'A1','tipo'=>'jaula','activa'=>0]),'activa');
        $this->invalido(fn()=>$this->guardar('configuracion',['tipo'=>'centro','hospitalizacion_habilitada'=>0]),'hospitalizacion_habilitada');
    }

    public function test_clinical_dates_and_discharge_fields_are_validated(): void
    {
        $p=$this->plaza();$d=$this->datosIngreso($p);$id=$this->guardar('internacion',$d);
        $this->invalido(fn()=>$this->guardar('internacion',array_merge($d,['id'=>$id,'estado'=>'alta'])),'egreso');
        $this->invalido(fn()=>$this->guardar('evolucion',['internacion_id'=>$id,'fecha'=>now()->subDay()->toDateTimeString(),'observaciones'=>'Anterior al ingreso']),'fecha');
    }

    public function test_foreign_pet_and_foreign_establishment_are_rejected(): void
    {
        $this->invalido(fn()=>$this->guardar('internacion',$this->datosIngreso($this->plaza(),3)),'id_mascota');
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->servicio()->institucion($this->req(['id_institucion'=>20]));
    }

    public function test_reservations_prevent_overlaps_and_keep_price_and_duration_snapshot(): void
    {
        $servicio=$this->servicioCatalogo();$d=$this->datosReserva($servicio);$id=$this->guardar('reserva',$d);
        $this->invalido(fn()=>$this->guardar('reserva',$this->datosReserva($servicio,2)),'inicio');
        $this->invalido(fn()=>$this->guardar('reserva',array_merge($d,['responsable'=>'Otro responsable'])),'inicio');
        $fin=DB::table('vet_reservas_servicios')->find($id)->fin;
        $this->guardar('servicio',['id'=>$servicio,'modulo'=>'peluqueria','nombre'=>'Servicio de peluqueria','duracion_minutos'=>120,'precio'=>30000,'activo'=>1]);
        $this->guardar('reserva',array_merge($d,['id'=>$id,'estado'=>'confirmada']));
        $reserva=DB::table('vet_reservas_servicios')->find($id);$this->assertEquals(15000,$reserva->precio);$this->assertSame($fin,$reserva->fin);
        $this->guardar('reserva',array_merge($d,['id'=>$id,'estado'=>'cancelada']));
        $this->guardar('reserva',$this->datosReserva($servicio,2));
        $this->assertSame(2,DB::table('vet_reservas_servicios')->count());
    }

    public function test_all_veterinary_services_can_complete_and_disabled_services_reject_new_bookings(): void
    {
        foreach(array_keys(OperacionVeterinaria::SERVICIOS) as $n=>$modulo){
            $servicio=$this->servicioCatalogo($modulo);$d=$this->datosReserva($servicio);$d['inicio']=now()->subDays($n+1)->startOfDay()->addHours(10)->toDateTimeString();
            $id=$this->guardar('reserva',$d);
            $this->invalido(fn()=>$this->guardar('reserva',array_merge($d,['id'=>$id,'estado'=>'completada'])),'resultado');
            $this->guardar('reserva',array_merge($d,['id'=>$id,'estado'=>'completada','resultado'=>'Atención registrada']));
        }
        $this->assertSame(5,DB::table('vet_reservas_servicios')->where('estado','completada')->count());
        $this->guardar('configuracion',['tipo'=>'centro','hospitalizacion_habilitada'=>1,'servicios_habilitados'=>[]]);
        $this->invalido(fn()=>$this->guardar('reserva',$this->datosReserva(1)),'servicio_id');
    }

    public function test_vacancies_candidates_and_closed_vacancy_validation(): void
    {
        $d=['cargo'=>'Asistente veterinario','jornada'=>'Completa','descripcion'=>'Apoyo al establecimiento','estado'=>'abierta'];
        $vacante=$this->guardar('convocatoria',$d);
        $c=['convocatoria_id'=>$vacante,'nombre'=>'Candidata','email'=>' candidata@example.test ','estado'=>'recibida'];
        $id=$this->guardar('postulacion',$c);$this->guardar('postulacion',array_merge($c,['id'=>$id,'estado'=>'seleccionada']));
        $this->assertSame('seleccionada',DB::table('vet_postulaciones')->find($id)->estado);
        $this->invalido(fn()=>$this->guardar('postulacion',$c),'email');
        $this->guardar('convocatoria',array_merge($d,['id'=>$vacante,'estado'=>'cerrada']));
        $this->invalido(fn()=>$this->guardar('postulacion',array_merge($c,['email'=>'otra@example.test'])),'convocatoria_id');
    }

    public function test_obligations_require_payment_evidence_and_can_be_edited(): void
    {
        $d=['concepto'=>'Obligación mensual','periodo'=>today()->format('Y-m'),'monto'=>50000,'vencimiento'=>today()->addDays(10)->toDateString(),'estado'=>'pendiente'];
        $id=$this->guardar('obligacion',$d);
        $this->invalido(fn()=>$this->guardar('obligacion',array_merge($d,['id'=>$id,'estado'=>'pagada'])),'fecha_pago');
        $this->guardar('obligacion',array_merge($d,['id'=>$id,'estado'=>'pagada','fecha_pago'=>today()->toDateString(),'referencia'=>'COM-123']));
        $this->assertSame('pagada',DB::table('vet_obligaciones')->find($id)->estado);
    }

    public function test_cross_establishment_updates_fail_and_do_not_write_audit(): void
    {
        DB::table('vet_plazas')->insert(['id'=>999,'id_institucion'=>20,'codigo'=>'AJENA','tipo'=>'sala','activa'=>1]);
        try{$this->guardar('plaza',['id'=>999,'codigo'=>'Modificada','tipo'=>'sala','activa'=>1]);$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(404,$e->getStatusCode());}
        $this->assertSame('AJENA',DB::table('vet_plazas')->find(999)->codigo);$this->assertSame(0,DB::table('vet_operacion_auditoria')->count());
    }

    public function test_failed_audit_rolls_back_the_business_write(): void
    {
        DB::statement("CREATE TRIGGER fallo_auditoria BEFORE INSERT ON vet_operacion_auditoria BEGIN SELECT RAISE(FAIL,'Fallo simulado'); END");
        try{$this->plaza();$this->fail();}catch(\Illuminate\Database\QueryException $e){$this->assertStringContainsString('Fallo simulado',$e->getMessage());}
        $this->assertSame(0,DB::table('vet_plazas')->count());
    }

    public function test_every_section_renders_real_forms_and_valid_routes_for_both_types(): void
    {
        $c=app(OperacionVeterinariaController::class);
        $fuente=file_get_contents(resource_path('views/app/veterinaria/operacion/index.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        foreach(['centro','hospital'] as $tipo){
            $this->guardar('configuracion',['tipo'=>$tipo,'hospitalizacion_habilitada'=>1,'servicios_habilitados'=>array_keys(OperacionVeterinaria::SERVICIOS)]);
            foreach(array_keys(OperacionVeterinaria::SECCIONES + OperacionVeterinaria::SERVICIOS) as $seccion){
                $vista=$c->index($this->req(),$this->servicio(),$seccion);
                $html=Blade::render($fuente,$vista->getData()+['errors'=>new ViewErrorBag]);
                $this->assertStringContainsString('Centro veterinario Uno',$html);
                $this->assertStringNotContainsString('Hospital ajeno',$html);
                $this->assertStringContainsString($tipo === 'hospital' ? 'Hospital veterinario' : 'Centro veterinario',$html);
            }
        }
    }

    public function test_post_redirects_to_the_saved_establishment_and_requires_authentication(): void
    {
        $this->withoutMiddleware([\Spatie\Permission\Middleware\RoleMiddleware::class,\App\Http\Middleware\PerfilPendiente::class]);
        $this->actingAs(User::find(1))->post(route('vet.operacion.guardar',['accion'=>'plaza']),['id_institucion'=>10,'_seccion'=>'plazas','codigo'=>'HTTP-1','tipo'=>'canil','activa'=>1])
            ->assertRedirect(route('vet.operacion.index',['seccion'=>'plazas','id_institucion'=>10]))->assertSessionHas('success');
        $this->assertSame(1,DB::table('vet_plazas')->count());
        Auth::logout();$this->get(route('vet.operacion.index',['seccion'=>'plazas','id_institucion'=>10]))->assertRedirect();
    }

    public function test_each_area_has_its_own_view_and_saving_returns_to_it(): void
    {
        $this->withoutMiddleware([\Spatie\Permission\Middleware\RoleMiddleware::class,\App\Http\Middleware\PerfilPendiente::class]);
        $this->actingAs(User::find(1));
        $c=app(OperacionVeterinariaController::class);
        $fuente=file_get_contents(resource_path('views/app/veterinaria/areas/index.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        foreach(array_keys(OperacionVeterinaria::AREAS) as $area){
            $vista=$c->area($this->req(),$this->servicio(),$area);
            $this->assertSame('app.veterinaria.areas.index',$vista->name());
            $html=Blade::render($fuente,$vista->getData()+['errors'=>new ViewErrorBag]);
            $this->assertStringContainsString(OperacionVeterinaria::AREAS[$area],$html);
            $this->assertStringContainsString('name="_area" value="'.$area.'"',$html);
            $enPestanas=in_array($area,OperacionVeterinaria::AREAS_EN_PESTANAS);
            $this->assertSame($enPestanas || $area === 'hospitalizacion',str_contains($html,'pill-moderno-white'));
            // Las áreas abren sus formularios en modales; los formularios siguen siendo los mismos.
            $this->assertStringContainsString('id="modal_registro_area"',$html);
            $this->assertStringContainsString('Agregar registro',$html);
            $this->assertSame($enPestanas,str_contains($html,'id="modal_servicio_area"'));
        }

        // Hospitalización reúne sus dos secciones en la misma vista.
        $vista=$c->area($this->req(['vista'=>'plazas']),$this->servicio(),'hospitalizacion');
        $this->assertSame('plazas',$vista->getData()['seccion']);
        $htmlPlazas=Blade::render($fuente,$vista->getData()+['errors'=>new ViewErrorBag]);
        $this->assertStringContainsString('Código de plaza',$htmlPlazas);
        $this->assertStringContainsString('id="modal_registro_area"',$htmlPlazas);
        $this->assertStringContainsString('id="modal_registro_plazas"',$htmlPlazas);
        $this->assertStringContainsString('href="#pestana_plazas"',$htmlPlazas);
        $this->assertStringContainsString('id="pestana_hospitalizacion"',$htmlPlazas);

        // La evolución de una internación abierta también se agrega desde un modal, solo en el área.
        $internacion=$this->guardar('internacion',$this->datosIngreso($this->plaza()));
        $vista=$c->area($this->req(['detalle'=>$internacion]),$this->servicio(),'hospitalizacion');
        $htmlDetalle=Blade::render($fuente,$vista->getData()+['errors'=>new ViewErrorBag]);
        $this->assertStringContainsString('id="modal_evolucion_area"',$htmlDetalle);
        $this->assertStringContainsString('name="internacion_id" value="'.$internacion.'"',$htmlDetalle);
        $this->assertStringNotContainsString('<h6 class="mb-3">Agregar evolución</h6>',$htmlDetalle);

        // Sin _area el guardado sigue volviendo a la operación; con _area, a la vista del área.
        $this->post(route('vet.operacion.guardar',['accion'=>'plaza']),['id_institucion'=>10,'_seccion'=>'plazas','_area'=>'hospitalizacion','codigo'=>'AREA-1','tipo'=>'canil','activa'=>1])
            ->assertRedirect(route('vet.areas.index',['area'=>'hospitalizacion','id_institucion'=>10,'vista'=>'plazas']));
        $this->post(route('vet.operacion.guardar',['accion'=>'servicio']),['id_institucion'=>10,'_seccion'=>'dental','_area'=>'dental','modulo'=>'dental','nombre'=>'Limpieza','duracion_minutos'=>30,'precio'=>1000,'activo'=>1])
            ->assertRedirect(route('vet.areas.index',['area'=>'dental','id_institucion'=>10,'pestana'=>'catalogo']))
            ->assertSessionHas('alerta_area')->assertSessionMissing('success');
        $this->post(route('vet.operacion.guardar',['accion'=>'servicio']),['id_institucion'=>10,'_seccion'=>'imagenologia','_area'=>'imagenologia','modulo'=>'imagenologia','nombre'=>'Radiografía','duracion_minutos'=>30,'precio'=>1000,'activo'=>1])
            ->assertRedirect(route('vet.areas.index',['area'=>'imagenologia','id_institucion'=>10,'pestana'=>'catalogo']));
        $this->post(route('vet.operacion.guardar',['accion'=>'servicio']),['id_institucion'=>10,'_seccion'=>'laboratorio','_area'=>'laboratorio','modulo'=>'laboratorio','nombre'=>'Hemograma','duracion_minutos'=>30,'precio'=>1000,'activo'=>1])
            ->assertRedirect(route('vet.areas.index',['area'=>'laboratorio','id_institucion'=>10,'pestana'=>'catalogo']));

        // Al editar un servicio, su modal se abre solo y cerrarlo devuelve a la pestaña del catálogo.
        $servicioId=DB::table('vet_catalogo_servicios')->where('nombre','Radiografía')->value('id');
        $req=$this->req(['catalogo'=>$servicioId]);
        app()->instance('request',$req);
        $html=Blade::render($fuente,$c->area($req,$this->servicio(),'imagenologia')->getData()+['errors'=>new ViewErrorBag]);
        $this->assertMatchesRegularExpression('/id="modal_servicio_area"[^>]*data-abrir="1"[^>]*data-url-cerrar="[^"]*pestana=catalogo/',$html);
        $this->assertMatchesRegularExpression('/id="modal_registro_area"[^>]*data-abrir="0"/',$html);
        $this->assertStringContainsString('name="id" value="'.$servicioId.'"',$html);
        // La edición de cada servicio ya viene en la página, para abrir sin esperar al servidor.
        $this->assertStringContainsString('<template id="modal_servicio_area_'.$servicioId.'">',$html);
        $this->assertStringContainsString('data-plantilla="modal_servicio_area_'.$servicioId.'"',$html);
        $this->assertStringContainsString('<span class="badge badge-light-success">Activo</span>',$html);
        $this->assertMatchesRegularExpression('/class="tab-pane fade show active" id="pestana_catalogo"/',$html);
        $this->post(route('vet.operacion.guardar',['accion'=>'plaza']),['id_institucion'=>10,'_seccion'=>'plazas','_area'=>'no-existe','codigo'=>'AREA-2','tipo'=>'canil','activa'=>1])
            ->assertRedirect(route('vet.operacion.index',['seccion'=>'plazas','id_institucion'=>10]));
        $this->get(route('vet.areas.index',['area'=>'pacientes']))->assertNotFound();
    }

    public function test_populated_detail_and_edit_screens_render_without_nested_forms(): void
    {
        $plaza=$this->plaza();$internacion=$this->guardar('internacion',$this->datosIngreso($plaza));
        $this->guardar('evolucion',['internacion_id'=>$internacion,'fecha'=>now()->toDateTimeString(),'observaciones'=>'Evolución de prueba']);
        $servicio=$this->servicioCatalogo();$reserva=$this->guardar('reserva',$this->datosReserva($servicio));
        $vacante=$this->guardar('convocatoria',['cargo'=>'Asistente de prueba','jornada'=>'Completa','descripcion'=>'Funciones','estado'=>'abierta']);
        $candidata=$this->guardar('postulacion',['convocatoria_id'=>$vacante,'nombre'=>'Candidata de prueba','email'=>'candidata@example.test','estado'=>'recibida']);
        $c=app(OperacionVeterinariaController::class);
        $fuente=file_get_contents(resource_path('views/app/veterinaria/operacion/index.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        foreach([
            ['hospitalizacion',['detalle'=>$internacion]],['hospitalizacion',['editar'=>$internacion]],
            ['peluqueria',['editar'=>$reserva]],['peluqueria',['catalogo'=>$servicio]],
            ['asistentes',['detalle'=>$vacante,'postulacion'=>$candidata]],['asistentes',['editar'=>$vacante]],
        ] as [$seccion,$params]){
            $vista=$c->index($this->req($params),$this->servicio(),$seccion);
            $html=Blade::render($fuente,$vista->getData()+['errors'=>new ViewErrorBag]);
            preg_match_all('/<\/?form\b[^>]*>/i',$html,$forms);$nivel=0;
            foreach($forms[0] as $form){$nivel+=str_starts_with($form,'</')?-1:1;$this->assertLessThanOrEqual(1,$nivel);$this->assertGreaterThanOrEqual(0,$nivel);}
            $this->assertSame(0,$nivel);
        }
        $hospital=app(\App\Http\Controllers\AdministradorHospitalController::class)->index($this->req());
        $this->assertSame('app.adm_hospital.home',$hospital->name());
        $this->assertEquals(10,$hospital->getData()['institucion']->id);
    }

    public function test_no_executable_administration_card_is_left_under_construction(): void
    {
        foreach(['adm_cm','adm_hospital','adm_cm/contabilidad'] as $directorio){
            foreach(glob(resource_path('views/app/'.$directorio.'/*.blade.php')) as $archivo){
                $fuente=preg_replace('/\{\{--[\s\S]*?--\}\}|<!--[\s\S]*?-->/','',file_get_contents($archivo));
                $this->assertDoesNotMatchRegularExpression('/onclick="en_construccion|class="card modulo-en-construccion/',$fuente,$archivo);
            }
        }
    }

    public function test_role_middleware_and_establishment_scope_protect_http_writes(): void
    {
        Schema::create('lugares_atencion',function(Blueprint $t){$t->id();$t->string('nombre');});
        $this->withoutMiddleware(\App\Http\Middleware\PerfilPendiente::class);
        $user=\Mockery::mock(User::class)->makePartial();$user->setRawAttributes(['id'=>1,'name'=>'Admin','email'=>'admin@example.test']);
        $user->shouldReceive('hasAnyRole')->andReturn(false);
        $url=route('vet.operacion.guardar',['accion'=>'plaza']);
        $this->actingAs($user)->post($url,['id_institucion'=>10,'_seccion'=>'plazas','codigo'=>'PRUEBA','tipo'=>'canil','activa'=>1])->assertForbidden();
        $this->assertSame(0,DB::table('vet_plazas')->count());
        $permitido=\Mockery::mock(User::class)->makePartial();$permitido->setRawAttributes(['id'=>1,'name'=>'Admin','email'=>'admin@example.test']);
        $permitido->shouldReceive('hasAnyRole')->andReturn(true);
        $this->actingAs($permitido)->post($url,['id_institucion'=>20,'_seccion'=>'plazas','codigo'=>'AJENA','tipo'=>'canil','activa'=>1])->assertForbidden();
        $this->actingAs($permitido)->post($url,['id_institucion'=>10,'_seccion'=>'plazas','codigo'=>'PROPIA','tipo'=>'canil','activa'=>1])->assertRedirect();
        $this->assertSame(1,DB::table('vet_plazas')->count());
    }

    public function test_active_context_and_notifications_use_only_the_establishments_records(): void
    {
        Schema::create('lugares_atencion',function(Blueprint $t){$t->id();$t->string('nombre');});
        $r=Request::create('/Veterinaria/Operacion');$r->setLaravelSession($this->app['session.store']);
        $this->assertEquals(10,$this->servicio()->institucion($r)->id);
        $this->assertSame([],$this->servicio()->avisos(10));
        $this->guardar('internacion',$this->datosIngreso($this->plaza()));
        $this->assertSame('hospitalizacion',$this->servicio()->avisos(10)[0]['seccion']);
        $this->assertSame([],$this->servicio()->avisos(20));
    }

    public function test_walk_in_pet_can_be_linked_without_creating_a_duplicate_or_a_fake_appointment(): void
    {
        DB::table('pacientes')->insert(['id'=>30,'rut'=>'12.345.678-5']);
        DB::table('mascotas')->insert(['id'=>4,'nombre'=>'Urgencia de prueba','especie'=>'Canina','id_responsable'=>30]);
        $this->assertFalse($this->servicio()->mascotas(Instituciones::find(10))->where('id',4)->exists());
        $this->invalido(fn()=>$this->guardar('vincular',['rut_tutor'=>'11111111-1','id_mascota'=>4]),'id_mascota');
        $this->invalido(fn()=>$this->servicio()->mascotasPorTutor('sin-rut'),'rut_tutor');
        $vinculo=$this->guardar('vincular',['rut_tutor'=>'12345678-5','id_mascota'=>4]);
        $this->assertSame($vinculo,$this->guardar('vincular',['rut_tutor'=>'12.345.678-5','id_mascota'=>4]));
        $this->assertTrue($this->servicio()->mascotas(Instituciones::find(10))->where('id',4)->exists());
        $this->assertFalse($this->servicio()->mascotas(Instituciones::find(20))->where('id',4)->exists());
        $this->guardar('internacion',$this->datosIngreso($this->plaza(),4));
        $this->assertSame(4,DB::table('mascotas')->count());$this->assertSame(3,DB::table('horas_medicas')->count());
        $r=Request::create('/Veterinaria/Operacion/pacientes','GET',['id_institucion'=>10,'rut_tutor'=>'12345678-5']);
        $this->app->instance('request',$r);
        $vista=app(OperacionVeterinariaController::class)->index($r,$this->servicio(),'pacientes');
        $this->assertCount(1,$vista->getData()['resultadosTutor']);
        $fuente=file_get_contents(resource_path('views/app/veterinaria/operacion/index.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        $this->assertStringContainsString('name="rut_tutor"',Blade::render($fuente,$vista->getData()+['errors'=>new ViewErrorBag]));
    }
}
