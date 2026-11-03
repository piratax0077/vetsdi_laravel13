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
        (require database_path('migrations/2026_11_01_000001_tipo_evolucion_hospitalaria.php'))->up();
        (require database_path('migrations/2026_11_01_000002_asignacion_y_cirugia_hospitalaria.php'))->up();
        (require database_path('migrations/2026_11_01_000003_controles_hospitalarios.php'))->up();
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

    public function test_laboratorio_precarga_examenes_sin_duplicar_ni_sobrescribir_configuracion(): void
    {
        $controller = app(OperacionVeterinariaController::class);
        $vista = $controller->index($this->req(), $this->servicio(), 'laboratorio')->getData();
        $this->assertCount(19, $vista['catalogo']);
        $this->assertCount(19, $vista['campos'][1]['opciones']);
        $examen = $vista['catalogo']->firstWhere('nombre', 'Sangre · Hemograma completo');
        DB::table('vet_catalogo_servicios')->where('id', $examen->id)
            ->update(['precio'=>18000, 'duracion_minutos'=>45, 'activo'=>false]);
        $this->servicioCatalogo('laboratorio');

        $controller->index($this->req(), $this->servicio(), 'laboratorio');
        $this->assertSame(20, DB::table('vet_catalogo_servicios')->count());
        $guardado = DB::table('vet_catalogo_servicios')->find($examen->id);
        $this->assertEquals(18000, $guardado->precio);
        $this->assertEquals(45, $guardado->duracion_minutos);
        $this->assertFalse((bool) $guardado->activo);
        $this->assertSame(0, DB::table('vet_catalogo_servicios')->where('id_institucion',20)->count());
        $this->assertSame(0, DB::table('vet_catalogo_servicios')->where('modulo','!=','laboratorio')->count());
    }

    public function test_estadisticas_laboratorio_usan_agenda_del_centro_y_periodo(): void
    {
        Schema::table('horas_medicas',function(Blueprint $t){
            $t->date('fecha_consulta')->nullable(); $t->integer('id_estado')->nullable();
            $t->string('tipo_hora_medica')->nullable(); $t->string('alias_examen')->nullable();
            $t->string('id_procedimiento')->nullable();
        });
        $base = ['id_mascota'=>1,'id_lugar_atencion'=>100,'fecha_consulta'=>'2026-11-01','id_estado'=>6,'tipo_hora_medica'=>'E','alias_examen'=>'Laboratorio','id_procedimiento'=>null];
        DB::table('horas_medicas')->insert([
            $base, array_replace($base,['id_estado'=>1]),
            array_replace($base,['id_lugar_atencion'=>200]),
            array_replace($base,['alias_examen'=>'Radiografía']),
            array_replace($base,['fecha_consulta'=>'2026-10-01']),
            array_replace($base,['id_estado'=>14]),
        ]);
        $vista = app(OperacionVeterinariaController::class)->index($this->req(['desde'=>'2026-11-01','hasta'=>'2026-11-30']),$this->servicio(),'laboratorio')->getData();
        $this->assertNull($vista['accion']);
        $this->assertSame([], $vista['columnas']);
        $this->assertTrue($vista['estadisticasLaboratorio']['disponible']);
        $this->assertEquals(1,$vista['estadisticasLaboratorio']['estados']->sum());
        $this->assertEquals(1,$vista['estadisticasLaboratorio']['estados']->get(6));
        $this->assertEquals(6,$vista['estadisticasLaboratorio']['examenes']->first()->id_estado);
        $this->assertArrayNotHasKey('mascotas',$vista);
    }

    public function test_gestion_area_guarda_responsable_y_gastos_solo_del_centro(): void
    {
        (require database_path('migrations/2026_11_01_000004_gestion_areas_veterinarias.php'))->up();
        $c = app(\App\Http\Controllers\GestionAreaVeterinariaController::class);
        $c->guardar($this->req(['accion'=>'responsable','nombre'=>'Responsable de radiología','contacto'=>'Anexo 20']),$this->servicio(),'imagenologia');
        $c->guardar($this->req(['accion'=>'gasto','tipo'=>'insumos','fecha'=>'2026-11-01','concepto'=>'Insumos radiológicos','monto'=>25000]),$this->servicio(),'imagenologia');
        $c->guardar($this->req(['accion'=>'gasto','tipo'=>'otros','fecha'=>'2026-11-01','concepto'=>'Mantención','monto'=>10000]),$this->servicio(),'imagenologia');
        DB::table('vet_gastos_area')->insert(['id_institucion'=>20,'area'=>'imagenologia','tipo'=>'otros','fecha'=>'2026-11-01','concepto'=>'Centro ajeno','monto'=>99999,'id_usuario'=>2]);
        $d = $c->index($this->req(),$this->servicio(),'imagenologia')->getData();
        $this->assertSame('Responsable de radiología',$d['responsable']->nombre);
        $this->assertEquals(25000,$d['totales']->get('insumos'));
        $this->assertEquals(10000,$d['totales']->get('otros'));
        $this->assertSame(2,$d['gastos']->total());
        $otro = $c->index($this->req(),$this->servicio(),'laboratorio')->getData();
        $this->assertNull($otro['responsable']);
        $this->assertSame(0,$otro['gastos']->total());
        $c->guardar($this->req(['accion'=>'responsable','nombre'=>'Responsable de hospitalización']),$this->servicio(),'hospitalizacion');
        $c->guardar($this->req(['accion'=>'gasto','tipo'=>'insumos','fecha'=>'2026-11-01','concepto'=>'Insumos de hospitalización','monto'=>5000]),$this->servicio(),'hospitalizacion');
        $hospital = $c->index($this->req(),$this->servicio(),'hospitalizacion')->getData();
        $this->assertSame('Hospitalización',$hospital['titulo']);
        $this->assertSame('Responsable de hospitalización',$hospital['responsable']->nombre);
        $this->assertEquals(5000,$hospital['totales']->get('insumos'));
        $c->guardar($this->req(['accion'=>'responsable','nombre'=>'Responsable de peluquería']),$this->servicio(),'peluqueria');
        $c->guardar($this->req(['accion'=>'gasto','tipo'=>'insumos','fecha'=>'2026-11-01','concepto'=>'Champú','monto'=>8000]),$this->servicio(),'peluqueria');
        $peluqueria = $c->index($this->req(),$this->servicio(),'peluqueria')->getData();
        $this->assertSame('Responsable de peluquería',$peluqueria['responsable']->nombre);
        $this->assertEquals(8000,$peluqueria['totales']->get('insumos'));
        $this->invalido(fn()=>$c->guardar($this->req(['accion'=>'gasto','tipo'=>'otros','fecha'=>'2026-11-01','concepto'=>'Inválido','monto'=>-1]),$this->servicio(),'imagenologia'),'monto');
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
            if ($modulo === 'laboratorio') {
                $this->invalido(fn()=>$this->guardar('reserva',$d),'servicio_id');
                continue;
            }
            $id=$this->guardar('reserva',$d);
            $this->invalido(fn()=>$this->guardar('reserva',array_merge($d,['id'=>$id,'estado'=>'completada'])),'resultado');
            $this->guardar('reserva',array_merge($d,['id'=>$id,'estado'=>'completada','resultado'=>'Atención registrada']));
        }
        $this->assertSame(4,DB::table('vet_reservas_servicios')->where('estado','completada')->count());
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
                if ($seccion === 'configuracion') {
                    $this->assertSame(route('adm_cm.configuracion',['id_institucion'=>10]).'#configuracion-establecimiento',$vista->getTargetUrl());
                    continue;
                }
                $html=Blade::render($fuente,$vista->getData()+['errors'=>new ViewErrorBag]);
                $this->assertStringContainsString('Centro veterinario Uno',$html);
                $this->assertStringNotContainsString('Hospital ajeno',$html);
                $this->assertStringContainsString($tipo === 'hospital' ? 'Hospital veterinario' : 'Centro veterinario',$html);
            }
        }
    }

    public function test_hospital_configuration_exposes_existing_hospitalization_modules(): void
    {
        foreach ([['hospital',false,true],['centro',true,true],['centro',false,false]] as [$tipo,$habilitada,$visible]) {
            $html=view('app.adm_cm.partials.hospitalizacion_configuracion',[
                'tipo_establecimiento'=>$tipo,
                'configuracion_establecimiento'=>(object)['hospitalizacion_habilitada'=>$habilitada],
                'institucion'=>Instituciones::find(10),
            ])->render();
            $this->assertSame($visible,str_contains($html,'Información del área hospitalaria'));
            if ($visible) {
                $this->assertStringContainsString('href="#hospital-info-hospitalizacion"',$html);
                $this->assertStringNotContainsString('Agregar registro',$html);
                $this->assertStringNotContainsString('Asignar pacientes',$html);
                $this->assertStringContainsString('Plazas de hospitalización',$html);
            }
        }
    }

    public function test_attention_statistics_use_filtered_establishment_records_and_snapshot_prices(): void
    {
        $servicio=$this->servicioCatalogo();
        $base=['id_institucion'=>10,'id_mascota'=>1,'servicio_id'=>$servicio,'modulo'=>'peluqueria','servicio'=>'Baño','precio'=>15000,'responsable'=>'Responsable','inicio'=>'2026-10-15 10:00:00','fin'=>'2026-10-15 11:00:00','id_usuario'=>1];
        foreach (['completada','reservada','cancelada','no_asiste'] as $estado) DB::table('vet_reservas_servicios')->insert(['estado'=>$estado]+$base);
        DB::table('vet_reservas_servicios')->insert(['estado'=>'completada','id_institucion'=>20,'precio'=>999999]+$base);
        DB::table('vet_reservas_servicios')->insert(['estado'=>'completada','inicio'=>'2026-09-01 10:00:00']+$base);
        DB::table('vet_catalogo_servicios')->where('id',$servicio)->update(['precio'=>50000]);
        $controller=app(OperacionVeterinariaController::class);
        $request=$this->req(['desde'=>'2026-10-01','hasta'=>'2026-10-31']);
        $data=$controller->index($request,$this->servicio(),'peluqueria')->getData();
        $stats=$data['estadisticaAtenciones'];
        $this->assertSame(4,(int)$stats->total);
        $this->assertSame(1,(int)$stats->completadas);
        $this->assertSame(1,(int)$stats->pendientes);
        $this->assertSame(1,(int)$stats->canceladas);
        $this->assertSame(1,(int)$stats->ausencias);
        $this->assertSame(15000,(int)$stats->valor_completadas);
        $data=$controller->index($this->req(['estado'=>'cancelada','desde'=>'2026-10-01']),$this->servicio(),'peluqueria')->getData();
        $this->assertSame(1,(int)$data['estadisticaAtenciones']->total);
        $this->assertSame(0,(int)$data['estadisticaAtenciones']->valor_completadas);
    }

    private function prepararProfesionalHospitalario(): array
    {
        Schema::create('profesionales',function(Blueprint $t){$t->id();$t->integer('id_usuario');$t->string('nombre');$t->string('apellido');});
        Schema::create('lugares_atencion',function(Blueprint $t){$t->id();$t->string('nombre');});
        Schema::create('profesionales_lugares_atencion',function(Blueprint $t){$t->id();$t->integer('id_profesional');$t->integer('id_institucion');$t->integer('id_lugar_atencion');$t->integer('estado');});
        DB::table('profesionales')->insert(['id'=>1,'id_usuario'=>1,'nombre'=>'Vet','apellido'=>'Uno']);
        DB::table('lugares_atencion')->insert(['id'=>100,'nombre'=>'Centro']);
        DB::table('profesionales_lugares_atencion')->insert(['id_profesional'=>1,'id_institucion'=>10,'id_lugar_atencion'=>100,'estado'=>1]);
        $id=$this->guardar('internacion',$this->datosIngreso($this->plaza()));
        DB::table('vet_internacion_profesionales')->insert(['internacion_id'=>$id,'id_usuario'=>1,'asignado_por'=>1]);
        return [$id,app(\App\Http\Controllers\AtencionHospitalariaVeterinariaController::class)];
    }

    private function reqClinica(array $datos=[]): Request
    {
        $r=$this->req($datos);$r->setLaravelSession(app('session.store'));return $r;
    }

    public function test_servicio_hospitalario_separa_enfermeria_y_profesional_y_revoca_el_acceso(): void
    {
        [$id,$profesional]=$this->prepararProfesionalHospitalario();
        (require database_path('migrations/2026_11_01_000005_servicio_hospitalizacion_veterinaria.php'))->up();
        Schema::create('asistentes',function(Blueprint $t){$t->id();$t->integer('id_usuario');});
        DB::table('users')->insert(['id'=>2,'name'=>'Enfermería','email'=>'enfermeria@example.test']);
        DB::table('asistentes')->insert(['id'=>1,'id_usuario'=>2]);
        DB::table('contrato_dependiente')->insert(['id_empleado'=>1,'tipo_empleado'=>'ASISTENTE ENFERMERIA','estado'=>2,'id_institucion'=>10]);
        $c=app(\App\Http\Controllers\ServicioHospitalizacionVeterinariaController::class);
        $acceso=app(\App\Services\HospitalizacionAcceso::class);
        $c->guardarEquipo($this->reqClinica(['id_usuario'=>2,'perfil'=>'enfermeria','activo'=>1]),$this->servicio(),$acceso);
        Auth::setUser(User::find(2));
        $this->assertTrue($acceso->tieneAcceso(Auth::user()));
        $this->assertSame('enfermeria',$acceso->perfil(Auth::user(),10));
        $d=$c->index($this->reqClinica(['internacion'=>$id]),$acceso,$this->servicio())->getData();
        $this->assertSame($id,$d['internacion']->id);
        $html=view('app.veterinaria.hospitalizacion.enfermeria',$d+['errors'=>new ViewErrorBag])->render();
        $this->assertStringContainsString('Control de enfermería veterinaria',$html);
        $this->assertStringNotContainsString('Registrar alta',$html);
        $c->guardar($this->reqClinica(['internacion_id'=>$id,'tipo_registro'=>'cirugia','fecha'=>now()->subMinute()->toDateTimeString(),'observaciones'=>'Cuidados realizados','peso'=>12,'temperatura'=>38,'frecuencia_cardiaca'=>90,'frecuencia_respiratoria'=>24,'dolor'=>2]),$acceso,$this->servicio());
        $nota=DB::table('vet_evoluciones')->first();
        $this->assertSame('enfermeria',$nota->tipo_registro);
        $this->assertEquals(90,$nota->frecuencia_cardiaca);
        $this->assertEquals(2,$nota->id_usuario);
        try{$profesional->alta($this->reqClinica(['internacion_id'=>$id]),$this->servicio());$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        try{$acceso->perfil(Auth::user(),20);$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        DB::table('contrato_dependiente')->update(['estado'=>0]);
        $this->assertTrue($acceso->centros(Auth::user())->isEmpty());
    }

    public function test_hospitalizacion_aparece_en_selector_y_abre_el_servicio_para_el_equipo(): void
    {
        $this->prepararProfesionalHospitalario();
        Schema::create('usuario_roles',function(Blueprint $t){$t->id();$t->integer('id_usuario');$t->string('tipo');$t->boolean('estado');$t->boolean('perfil_completo');});
        $cuentas=app(\App\Services\CuentasService::class);
        $this->assertTrue($cuentas->rolesActivos(Auth::user())->contains('tipo','hospitalizacion'));
        $this->assertTrue($cuentas->perfilCompleto(Auth::user(),'hospitalizacion'));
        $c=app(\App\Http\Controllers\SeleccionCuentaController::class);
        $r=$this->reqClinica();
        $respuesta=$c->entrar($r,'hospitalizacion');
        $this->assertSame('hospitalizacion',$r->session()->get('rol_activo'));
        $this->assertSame(route('vet.hospital.servicio'),$respuesta->getTargetUrl());
    }

    public function test_ingreso_hospitalario_informa_sin_plazas_y_solo_abre_con_disponibilidad(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        $r=$this->reqClinica();
        $d=$c->index($r,$this->servicio())->getData();
        $html=view('app.veterinaria.hospitalizacion.ingreso',$d)->render();
        $this->assertStringContainsString('No hay plazas disponibles',$html);
        $this->assertStringNotContainsString('id="ingreso_hospitalario"',$html);
        $this->plaza('B1');
        $d=$c->index($r,$this->servicio())->getData();
        $html=view('app.veterinaria.hospitalizacion.ingreso',$d)->render();
        $this->assertStringContainsString('id="ingreso_hospitalario"',$html);
        $this->assertStringContainsString('B1',$html);
        $this->assertStringNotContainsString('No hay plazas disponibles',$html);
        DB::table('vet_internacion_profesionales')->delete();
        DB::table('vet_internaciones')->delete();
        DB::table('vet_plazas')->delete();
        $d=$c->index($r,$this->servicio())->getData();
        $html=view('app.veterinaria.hospitalizacion.ingreso',$d)->render();
        $this->assertStringContainsString('No hay plazas de hospitalización configuradas',$html);
        $this->assertStringContainsString('Configurar plazas de hospitalización',$html);
    }

    public function test_ficha_hospitalaria_separa_enfermeria_y_examenes_del_paciente_en_solo_lectura(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        (require database_path('migrations/2026_07_31_120000_create_documentos_laboratorio_mascota_table.php'))->up();
        foreach(['veterinaria','enfermeria'] as $tipo) $c->guardar($this->reqClinica(['internacion_id'=>$id,'tipo_registro'=>$tipo,'fecha'=>now()->subMinute()->toDateTimeString(),'observaciones'=>'Nota '.$tipo]),$this->servicio());
        DB::table('documentos_laboratorio_mascota')->insert([
            ['id_mascota'=>1,'nombre_original'=>'Hemograma.pdf','ruta'=>'resultado.pdf','tipo_examen'=>'sangre','estado'=>'clasificado','created_at'=>now()],
            ['id_mascota'=>2,'nombre_original'=>'Examen ajeno.pdf','ruta'=>'otro.pdf','tipo_examen'=>'sangre','estado'=>'clasificado','created_at'=>now()],
        ]);
        $d=$c->index($this->reqClinica(['internacion'=>$id]),$this->servicio())->getData();
        $this->assertSame(1,$d['evoluciones']->total());
        $this->assertSame(1,$d['evolucionesEnfermeria']->total());
        $this->assertSame(1,$d['examenesHospitalarios']->total());
        $html=view('app.veterinaria.hospitalizacion.consultas_lectura',$d+['pestanaHospital'=>'enfermeria'])->render();
        $this->assertStringContainsString('Nota enfermeria',$html);
        $this->assertStringContainsString('Hemograma.pdf',$html);
        $this->assertStringNotContainsString('Examen ajeno.pdf',$html);
        $this->assertStringNotContainsString('<form',$html);
        $this->assertStringNotContainsString('<textarea',$html);
        try{$c->examen($this->reqClinica(['internacion'=>$id]),$this->servicio(),2);$this->fail();}catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){$this->assertTrue(true);}
    }

    public function test_professional_can_save_control_and_evolution_in_hospital_history(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        foreach(['control','evolucion'] as $tipo) {
            $c->guardar($this->reqClinica(['internacion_id'=>$id,'tipo_registro'=>$tipo,'fecha'=>now()->subMinute()->toDateTimeString(),'observaciones'=>'Registro de '.$tipo]),$this->servicio());
        }
        $this->assertSame(['control','evolucion'],DB::table('vet_evoluciones')->orderBy('id')->pluck('tipo_registro')->all());
        $d=$c->index($this->reqClinica(['internacion'=>$id]),$this->servicio())->getData();
        $fuente=file_get_contents(resource_path('views/app/veterinaria/hospitalizacion/profesional.blade.php'));
        $fuente=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$fuente));
        $html=Blade::render($fuente,$d+['errors'=>new ViewErrorBag]);
        $this->assertStringContainsString('<td>Control</td>',$html);
        $this->assertStringContainsString('<td>Evolución</td>',$html);
    }

    public function test_professional_records_surgery_nursing_discharge_and_followup_for_assigned_patient(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        foreach(['enfermeria','cirugia'] as $tipo) $c->guardar($this->reqClinica(['internacion_id'=>$id,'tipo_registro'=>$tipo,'procedimiento'=>$tipo==='cirugia'?'Sutura':null,'fecha'=>now()->subMinute()->format('Y-m-d H:i:s'),'observaciones'=>'Registro clínico']),$this->servicio());
        $this->assertSame(2,DB::table('vet_evoluciones')->count());
        $data=$c->index($this->reqClinica(['internacion'=>$id]),$this->servicio())->getData();
        $source=file_get_contents(resource_path('views/app/veterinaria/hospitalizacion/profesional.blade.php'));
        $source=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$source));
        $html=Blade::render($source,$data+['errors'=>new ViewErrorBag]);
        $this->assertStringContainsString('Registro de cirugía',$html);
        $this->assertStringContainsString('Citaciones a control',$html);
        $this->assertStringContainsString('Registro de enfermería',$html);
        $this->assertStringContainsString('Ficha hospitalaria',$html);
        $this->assertStringContainsString('id="atencion-hospitalaria"',$html);
        $this->assertStringNotContainsString('Mis pacientes asignados e historia',$html);

        $portal=app(\App\Http\Controllers\ServicioHospitalizacionVeterinariaController::class)->index($this->reqClinica(['internacion'=>$id]),app(\App\Services\HospitalizacionAcceso::class),$this->servicio());
        $this->assertSame(route('vet.hospital.profesional',['id_institucion'=>10,'internacion'=>$id]),$portal->getTargetUrl());

        $c->control($this->reqClinica(['internacion_id'=>$id,'fecha'=>now()->addDay()->format('Y-m-d H:i:s'),'motivo'=>'Revisión de herida']),$this->servicio());
        $this->assertSame(1,DB::table('vet_controles_hospitalarios')->count());
        $c->alta($this->reqClinica(['internacion_id'=>$id,'egreso'=>now()->format('Y-m-d H:i:s'),'observaciones_alta'=>'Evolución favorable','indicaciones'=>'Cuidados de herida y control']),$this->servicio());
        $this->assertSame('alta',DB::table('vet_internaciones')->find($id)->estado);
        $carne=$c->carne($this->reqClinica(['internacion'=>$id]),$this->servicio())->render();
        $this->assertStringContainsString('Sutura',$carne);
        $this->assertStringContainsString('Revisión de herida',$carne);
        $this->assertStringContainsString('Cuidados de herida',$carne);
        $this->invalido(fn()=>$c->guardar($this->reqClinica(['internacion_id'=>$id,'tipo_registro'=>'veterinaria','fecha'=>now()->format('Y-m-d H:i:s'),'observaciones'=>'No permitido']),$this->servicio()),'internacion_id');
    }

    public function test_unassigned_professional_cannot_read_or_write_patient_and_invalid_surgery_is_rejected(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        $this->invalido(fn()=>$c->guardar($this->reqClinica(['internacion_id'=>$id,'tipo_registro'=>'cirugia','fecha'=>now()->format('Y-m-d H:i:s'),'observaciones'=>'Sin procedimiento']),$this->servicio()),'procedimiento');
        DB::table('vet_internacion_profesionales')->delete();
        $view=$c->index($this->reqClinica(),$this->servicio());
        $this->assertSame(0,$view->getData()['pacientes']->total());
        foreach(['index','guardar'] as $metodo){
            try {
                $c->{$metodo}($this->reqClinica(['internacion'=>$id,'internacion_id'=>$id,'tipo_registro'=>'enfermeria','fecha'=>now()->format('Y-m-d H:i:s'),'observaciones'=>'Ajena']),$this->servicio());
                $this->fail('Debe denegar paciente no asignado.');
            } catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        }
        $this->assertSame(0,DB::table('vet_evoluciones')->count());
    }

    public function test_control_cannot_be_completed_before_appointment_or_completed_twice(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        $c->control($this->reqClinica(['internacion_id'=>$id,'fecha'=>now()->addDay()->format('Y-m-d H:i:s'),'motivo'=>'Control']),$this->servicio());
        $control=DB::table('vet_controles_hospitalarios')->first();
        try{$c->control($this->reqClinica(['internacion_id'=>$id,'control_id'=>$control->id,'estado'=>'realizado','resultado'=>'Normal']),$this->servicio());$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(422,$e->getStatusCode());}
        $this->assertSame('programado',DB::table('vet_controles_hospitalarios')->find($control->id)->estado);
        DB::table('vet_controles_hospitalarios')->where('id',$control->id)->update(['fecha'=>now()->subMinute()]);
        $c->control($this->reqClinica(['internacion_id'=>$id,'control_id'=>$control->id,'estado'=>'realizado','resultado'=>'Herida cicatrizando']),$this->servicio());
        $this->assertSame('realizado',DB::table('vet_controles_hospitalarios')->find($control->id)->estado);
        try{$c->control($this->reqClinica(['internacion_id'=>$id,'control_id'=>$control->id,'estado'=>'cancelado']),$this->servicio());$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(422,$e->getStatusCode());}
    }

    public function test_professional_admission_assigns_patient_and_rejects_foreign_assignment(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        $plaza=$this->plaza('B2');
        $c->ingreso($this->reqClinica(['id_mascota'=>2,'plaza_id'=>$plaza,'ingreso'=>now()->subMinute()->format('Y-m-d H:i:s'),'motivo'=>'Observación']),$this->servicio());
        $nuevo=DB::table('vet_internaciones')->where('id_mascota',2)->first();
        $this->assertTrue(DB::table('vet_internacion_profesionales')->where('internacion_id',$nuevo->id)->where('id_usuario',1)->exists());
        try{$c->asignar($this->reqClinica(['internacion_id'=>$id,'profesionales'=>[999]]),$this->servicio());$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $c->asignar($this->reqClinica(['internacion_id'=>$id,'profesionales'=>[]]),$this->servicio());
        $this->assertFalse(DB::table('vet_internacion_profesionales')->where('internacion_id',$id)->exists());
    }

    public function test_hospital_information_tables_are_scoped_to_the_establishment(): void
    {
        $id=$this->guardar('internacion',$this->datosIngreso($this->plaza()));
        DB::table('vet_plazas')->insert(['id'=>999,'id_institucion'=>20,'codigo'=>'AJENA','tipo'=>'sala','activa'=>1]);
        $info=$this->servicio()->informacionHospitalaria(Instituciones::find(10));
        $this->assertSame(2,$info['pacientes']->total());
        $this->assertSame(1,$info['hospitalizaciones']->total());
        $this->assertSame($id,$info['hospitalizaciones']->first()->id);
        $this->assertSame(1,$info['plazas']->total());
        $this->assertSame(1,(int)$info['plazas']->first()->ocupacion);
        $html=view('app.adm_cm.partials.hospitalizacion_configuracion',['tipo_establecimiento'=>'hospital','configuracion_establecimiento'=>$this->servicio()->configuracion(10),'institucion'=>Instituciones::find(10),'informacionHospitalaria'=>$info])->render();
        $this->assertStringContainsString('Luna',$html);
        $this->assertStringNotContainsString('AJENA',$html);
        $this->assertStringContainsString('Ocupada',$html);
        $this->assertStringNotContainsString('vet-operacion-form',$html);
    }

    public function test_exportacion_de_centro_sin_datos_informa_en_lugar_de_generar_zip_vacio(): void
    {
        $exportacion = app(\App\Services\ExportacionPacientesCentro::class);
        $centro = new Instituciones;
        $centro->id = 999;
        $centro->id_lugar_atencion = 999;
        $centro->nombre = 'Centro sin atenciones';
        $this->invalido(fn()=>$exportacion->crear($centro),'exportacion');
    }

    public function test_export_zip_contains_only_center_patients_and_attributions_and_never_credentials(): void
    {
        Schema::table('pacientes',function(Blueprint $t){$t->string('password')->nullable();});
        DB::table('pacientes')->insert(['id'=>1,'rut'=>'11111111-1','password'=>'SECRETO_NO_EXPORTAR']);
        DB::table('mascotas')->where('id',1)->update(['id_responsable'=>1,'nombre'=>'=HYPERLINK("x")']);
        Schema::create('fichas_atenciones',function(Blueprint $t){$t->id();$t->integer('id_mascota');$t->integer('id_lugar_atencion');$t->integer('id_institucion')->nullable();$t->string('diagnostico');});
        DB::table('fichas_atenciones')->insert([
            ['id_mascota'=>1,'id_lugar_atencion'=>100,'id_institucion'=>10,'diagnostico'=>'ATENCION_PROPIA'],
            ['id_mascota'=>1,'id_lugar_atencion'=>200,'id_institucion'=>20,'diagnostico'=>'HISTORIA_AJENA_MISMA_MASCOTA'],
            ['id_mascota'=>3,'id_lugar_atencion'=>100,'id_institucion'=>20,'diagnostico'=>'OTRO_CENTRO_EXPLICITO'],
        ]);
        $export=app(\App\Services\ExportacionPacientesCentro::class);
        $ruta=$export->crear(Instituciones::find(10));
        try {
            $zip=new \ZipArchive;$this->assertTrue($zip->open($ruta));
            $csv=$zip->getFromName('pacientes_mascotas.csv');
            $this->assertStringContainsString('HYPERLINK',$csv);
            $this->assertStringContainsString("'=HYPERLINK",$csv);
            $this->assertStringNotContainsString('Mascota ajena',$csv);
            $fichas=$zip->getFromName('fichas_atenciones.csv');
            $this->assertStringContainsString('ATENCION_PROPIA',$fichas);
            $this->assertStringNotContainsString('HISTORIA_AJENA',$fichas);
            $this->assertStringNotContainsString('OTRO_CENTRO',$fichas);
            $tutores=$zip->getFromName('tutores.csv');
            $this->assertStringNotContainsString('SECRETO_NO_EXPORTAR',$tutores);
            $this->assertFalse($zip->getFromName('users.csv'));
            $this->assertNotFalse($zip->getFromName('LEEME.txt'));
            $zip->close();
        } finally {unlink($ruta);}
    }

    public function test_export_rejects_other_center_and_authorized_download_is_private_and_audited(): void
    {
        $c=app(\App\Http\Controllers\ExportacionPacientesCentroController::class);
        $export=app(\App\Services\ExportacionPacientesCentro::class);
        try{$c->descargar($this->req(['id_institucion'=>20]),$export);$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $this->assertSame(0,DB::table('vet_operacion_auditoria')->count());
        $response=$c->descargar($this->req(),$export);
        try {
            $this->assertStringContainsString('private',$response->headers->get('Cache-Control'));
            $this->assertStringContainsString('no-store',$response->headers->get('Cache-Control'));
            $this->assertSame(1,DB::table('vet_operacion_auditoria')->where('tabla','exportacion_pacientes_atenciones')->where('id_institucion',10)->count());
        } finally {ob_start();($response->getCallback())();$contenido=ob_get_clean();}
        $this->assertStringStartsWith('PK',$contenido);
    }

    public function test_professional_export_excludes_other_professionals_even_for_the_same_patient(): void
    {
        [$id,$c]=$this->prepararProfesionalHospitalario();
        Schema::table('horas_medicas',function(Blueprint $t){$t->integer('id_profesional')->nullable();});
        Schema::create('fichas_atenciones',function(Blueprint $t){$t->id();$t->integer('id_profesional');$t->integer('id_mascota');$t->string('diagnostico');});
        DB::table('horas_medicas')->where('id_mascota',1)->update(['id_profesional'=>1]);
        DB::table('fichas_atenciones')->insert([
            ['id_profesional'=>1,'id_mascota'=>1,'diagnostico'=>'MIA_PROFESIONAL'],
            ['id_profesional'=>99,'id_mascota'=>1,'diagnostico'=>'OTRO_PROFESIONAL_MISMA_MASCOTA'],
            ['id_profesional'=>99,'id_mascota'=>3,'diagnostico'=>'OTRO_PACIENTE'],
        ]);
        foreach([1,99] as $usuario) DB::table('vet_evoluciones')->insert(['internacion_id'=>$id,'fecha'=>now(),'observaciones'=>$usuario===1?'NOTA_PROPIA':'NOTA_AJENA','id_usuario'=>$usuario]);
        $service=app(\App\Services\ExportacionPacientesProfesional::class);
        $profesional=\App\Models\Profesional::find(1);
        $ruta=$service->crear($profesional);
        try {
            $zip=new \ZipArchive;$this->assertTrue($zip->open($ruta));
            $fichas=$zip->getFromName('fichas_atenciones.csv');
            $this->assertStringContainsString('MIA_PROFESIONAL',$fichas);
            $this->assertStringNotContainsString('OTRO_PROFESIONAL',$fichas);
            $this->assertStringNotContainsString('OTRO_PACIENTE',$fichas);
            $notas=$zip->getFromName('vet_evoluciones.csv');
            $this->assertStringContainsString('NOTA_PROPIA',$notas);
            $this->assertStringNotContainsString('NOTA_AJENA',$notas);
            $this->assertStringNotContainsString('Mascota ajena',$zip->getFromName('pacientes_mascotas.csv'));
            $zip->close();
        } finally {unlink($ruta);}
        DB::table('profesionales_lugares_atencion')->update(['estado'=>0]);
        $this->assertSame(0,$service->consultas($profesional)['vet_internaciones']->count());
    }

    public function test_professional_download_uses_authenticated_profile_and_disallows_impersonation(): void
    {
        $this->prepararProfesionalHospitalario();
        $c=app(\App\Http\Controllers\ExportacionPacientesProfesionalController::class);
        $s=app(\App\Services\ExportacionPacientesProfesional::class);
        $this->invalido(fn()=>$c->descargar(Request::create('/exportar','POST',['id_profesional'=>99]),$s),'id_profesional');
        $response=$c->descargar(Request::create('/exportar','POST'),$s);
        $this->assertStringContainsString('private',$response->headers->get('Cache-Control'));
        ob_start();($response->getCallback())();$zip=ob_get_clean();
        $this->assertStringStartsWith('PK',$zip);
        DB::table('users')->insert(['id'=>2,'name'=>'Sin perfil','email'=>'otro@example.test']);
        Auth::setUser(User::find(2));
        try{$c->descargar(Request::create('/exportar','POST'),$s);$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
    }

    public function test_configuration_saves_and_returns_to_institution_profile(): void
    {
        $response=app(OperacionVeterinariaController::class)->guardar($this->req(['tipo'=>'hospital','hospitalizacion_habilitada'=>1,'servicios_habilitados'=>['dental'],'_seccion'=>'configuracion']),$this->servicio(),'configuracion');
        $this->assertSame(route('adm_cm.configuracion',['id_institucion'=>10]).'#configuracion-establecimiento',$response->getTargetUrl());
        $config=$this->servicio()->configuracion(10);
        $this->assertSame('hospital',$config->tipo);
        $this->assertSame(['dental'],$config->servicios_habilitados);
        $html=view('app.adm_cm.partials.configuracion_establecimiento',['configuracion_establecimiento'=>$config,'institucion'=>Instituciones::find(10),'errors'=>new ViewErrorBag])->render();
        $this->assertStringContainsString('id="configuracion-establecimiento"',$html);
        $this->assertStringContainsString('name="hospitalizacion_habilitada"',$html);
        $this->assertStringContainsString('Guardar cambios',$html);
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
            if ($area === 'laboratorio') {
                $this->assertStringNotContainsString('Guardar registro',$html);
                $this->assertStringNotContainsString('Vincular una mascota',$html);
                $this->assertStringContainsString('Agregar examen',$html);
                $this->assertStringContainsString('Guardar examen',$html);
                $this->assertSame(2,substr_count($html,'<table '));
            }
            $enPestanas=in_array($area,OperacionVeterinaria::AREAS_EN_PESTANAS);
            $this->assertSame($enPestanas || $area === 'hospitalizacion',str_contains($html,'pill-moderno-white'));
            // Las áreas abren sus formularios en modales; los formularios siguen siendo los mismos.
            $this->assertSame($area !== 'laboratorio',str_contains($html,'id="modal_registro_area"'));
            $this->assertSame(!in_array($area,['peluqueria','laboratorio','imagenologia']),str_contains($html,'Agregar registro'));
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
