<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User,Peluqueria};
use App\Services\CuentasService;
use App\Http\Controllers\PeluqueriaController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Schema,Auth};
use Illuminate\Validation\ValidationException;
class PeluqueriaTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','cache.default'=>'array']);DB::purge('sqlite');
        Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('email');$t->timestamps();});
        Schema::create('usuario_roles',function(Blueprint $t){$t->id();$t->unsignedBigInteger('id_usuario');$t->string('tipo');$t->boolean('estado')->default(true);$t->boolean('perfil_completo')->default(false);$t->timestamp('fecha_activacion')->nullable();$t->timestamp('fecha_perfil_completo')->nullable();$t->timestamps();});
        Schema::create('roles',function(Blueprint $t){$t->id();$t->string('name');$t->string('guard_name');$t->timestamps();});
        Schema::create('model_has_roles',function(Blueprint $t){$t->unsignedBigInteger('role_id');$t->string('model_type');$t->unsignedBigInteger('model_id');});
        (require database_path('migrations/2026_10_19_000003_create_peluqueria_module.php'))->up();
        (require database_path('migrations/2026_10_21_000001_create_peluqueria_fichas.php'))->up();
        (require database_path('migrations/2026_10_21_000002_create_peluqueria_procedimientos.php'))->up();
        DB::table('users')->insert([['id'=>1,'name'=>'Pelu Uno','email'=>'uno@example.test'],['id'=>2,'name'=>'Pelu Dos','email'=>'dos@example.test']]);
        foreach([1,2] as $id){DB::table('peluquerias')->insert(['id'=>$id,'id_usuario'=>$id,'nombre'=>'Local '.$id,'telefono'=>'123','direccion'=>'Calle']);app(CuentasService::class)->asignarRol(User::find($id),'peluqueria');}
        Auth::setUser(User::find(1));
        view()->share('errors',new \Illuminate\Support\ViewErrorBag());
    }
    private function req(array $data=[]): Request{return Request::create('/Peluqueria','POST',$data);}
    private function controller(): PeluqueriaController{return app(PeluqueriaController::class);}
    private function cliente(int $owner=1): int{return DB::table('peluqueria_clientes')->insertGetId(['peluqueria_id'=>$owner,'tutor'=>'Tutor '.$owner,'telefono'=>'123','mascota'=>'Mascota '.$owner,'especie'=>'Canina']);}
    private function reserva(int $id,string $time='10:00'): array {return ['cliente_id'=>$id,'inicio'=>now()->addDay()->format('Y-m-d').'T'.$time,'duracion'=>60,'servicio'=>'Baño','valor'=>15000];}
    public function test_escritorio_usa_css_profesional_y_abre_agenda_y_configuracion(): void {
        $html=$this->controller()->index($this->req())->render();
        foreach(['escritorio_profesional.css','pcoded-navbar','Panel de configuración','Mi agenda',route('peluqueria.agenda.ver'),route('peluqueria.configuracion')] as $texto)$this->assertStringContainsString($texto,$html);
        $this->assertStringNotContainsString('datetime-local',$html);
    }
    public function test_procedimiento_configurado_determina_duracion_y_precio_de_reserva(): void {
        $this->controller()->procedimientoGuardar($this->req(['nombre'=>'Corte de uñas','duracion'=>30,'precio'=>5000,'activo'=>1,'indicaciones'=>'Manipulación cuidadosa']));
        $id=DB::table('peluqueria_procedimientos')->value('id');
        $datos=$this->reserva($this->cliente());$datos['procedimiento_id']=$id;$datos['valor']=1;$datos['duracion']=300;
        $this->controller()->citaGuardar($this->req($datos));
        $cita=DB::table('peluqueria_citas')->first();
        $this->assertSame('Corte de uñas',$cita->servicio);$this->assertEquals(5000,$cita->valor);
        $this->assertEquals(30,\Carbon\Carbon::parse($cita->inicio)->diffInMinutes(\Carbon\Carbon::parse($cita->fin)));
        $this->assertStringContainsString('Corte de uñas',$this->controller()->configuracion()->render());
    }
    public function test_ficha_conserva_precio_reservado_si_el_servicio_cambia_o_se_desactiva(): void {
        $procedimiento=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>'Belleza','duracion'=>30,'precio'=>10000,'activo'=>1]);
        $reserva=$this->reserva($this->cliente());$reserva['procedimiento_id']=$procedimiento;
        $this->controller()->citaGuardar($this->req($reserva));
        $id=DB::table('peluqueria_citas')->value('id');
        DB::table('peluqueria_procedimientos')->where('id',$procedimiento)->update(['precio'=>20000]);
        $datos=['mascota'=>'Mascota 1','especie'=>'Canina','esterilizada'=>'desconocido',
            'servicios'=>[['procedimiento_id'=>$procedimiento,'nombre'=>'Belleza','precio'=>10000]],'accion'=>'guardar'];
        $this->controller()->fichaGuardar($this->req($datos),$id);
        $this->assertEquals(10000,DB::table('peluqueria_citas')->find($id)->valor);
        DB::table('peluqueria_procedimientos')->where('id',$procedimiento)->update(['activo'=>0]);
        $datos['accion']='finalizar';$this->controller()->fichaGuardar($this->req($datos),$id);
        $guardada=json_decode(DB::table('peluqueria_fichas')->value('datos'),true);
        $this->assertSame(10000,$guardada['total']);
        $this->assertSame('finalizada',DB::table('peluqueria_citas')->find($id)->estado);
    }

    public function test_no_se_puede_duplicar_un_cobro_antiguo_sin_procedimiento_configurado(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));
        $id=DB::table('peluqueria_citas')->value('id');
        $datos=['mascota'=>'Mascota 1','especie'=>'Canina','esterilizada'=>'desconocido','accion'=>'guardar'];
        $datos['servicios']=[['nombre'=>'Baño','precio'=>15000],['nombre'=>'Baño','precio'=>15000]];
        $this->expectException(ValidationException::class);
        $this->controller()->fichaGuardar($this->req($datos),$id);
    }

    public function test_configuracion_no_permite_editar_procedimiento_de_otro_local(): void {
        $id=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>2,'nombre'=>'Ajeno','duracion'=>30,'precio'=>5000]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->controller()->procedimientoGuardar($this->req(['id'=>$id,'nombre'=>'Cambio','duracion'=>30,'precio'=>1,'activo'=>1]));
    }
    public function test_calendario_muestra_solo_atenciones_del_local_y_enlaza_su_ficha(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));
        $otro=$this->cliente(2);DB::table('peluqueria_citas')->insert(['peluqueria_id'=>2,'cliente_id'=>$otro,'inicio'=>now()->addDay()->setTime(10,0),'fin'=>now()->addDay()->setTime(11,0),'servicio'=>'Ajeno']);
        $resultado=$this->controller()->eventos($this->req(['start'=>now()->toDateString(),'end'=>now()->addDays(7)->toDateString()]))->getData(true);
        $this->assertCount(1,$resultado);$this->assertStringContainsString('Mascota 1',$resultado[0]['title']);
        $this->assertStringContainsString('/ficha',$resultado[0]['url']);
        app(CuentasService::class)->marcarPerfilCompleto(User::find(1),'peluqueria');
        $this->actingAs(User::find(1))->withSession(['rol_activo'=>'peluqueria'])->get(route('peluqueria.agenda.ver'))->assertOk()->assertSee('peluqueria-calendario');
    }
    public function test_iniciar_desde_la_agenda_abre_ficha_con_mascota_y_servicio_cargados(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));
        $id=DB::table('peluqueria_citas')->value('id');
        $respuesta=$this->controller()->estado($this->req(['estado'=>'en_atencion']),$id);
        $this->assertSame(route('peluqueria.ficha',$id),$respuesta->getTargetUrl());
        $datos=$this->controller()->ficha($id)->getData();
        $this->assertSame('Mascota 1',$datos['datos']['mascota']);
        $this->assertSame([['nombre'=>'Baño','precio'=>15000]],$datos['datos']['servicios']);
        $html=$this->controller()->ficha($id)->render();
        foreach(['Peluquería canina y felina','Comportamiento durante la atención','Servicios realizados','Análisis visual','Observaciones generales'] as $texto)$this->assertStringContainsString($texto,$html);
    }
    public function test_pills_muestran_solo_atenciones_previas_finalizadas_de_la_misma_mascota_y_local(): void {
        $cliente=$this->cliente();$otro=$this->cliente();
        $this->controller()->citaGuardar($this->req($this->reserva($cliente)));$actual=DB::table('peluqueria_citas')->value('id');
        $base=['peluqueria_id'=>1,'cliente_id'=>$cliente,'inicio'=>now()->subDays(2),'fin'=>now()->subDays(2)->addHour(),'servicio'=>'Baño previo','registro'=>'Cuidados anteriores de esta mascota','estado'=>'finalizada','valor'=>15000,'finalizada_en'=>now()->subDays(2)];
        $previa=DB::table('peluqueria_citas')->insertGetId($base);
        DB::table('peluqueria_citas')->insert(array_replace($base,['cliente_id'=>$otro,'registro'=>'Otra mascota']));
        DB::table('peluqueria_citas')->insert(array_replace($base,['peluqueria_id'=>2,'registro'=>'Otro negocio']));
        DB::table('peluqueria_citas')->insert(array_replace($base,['estado'=>'cancelada','registro'=>'Cancelada']));
        $vista=$this->controller()->ficha($actual);
        $this->assertSame([$previa],$vista->getData()['anteriores']->pluck('id')->all());
        $html=$vista->render();
        foreach(['Atención actual','Atenciones anteriores','Cuidados anteriores de esta mascota',route('peluqueria.ficha',$previa)] as $texto)$this->assertStringContainsString($texto,$html);
        $this->assertStringNotContainsString('Otro negocio',$html);$this->assertStringNotContainsString('Otra mascota',$html);
    }
    public function test_al_cerrar_atencion_aparece_en_su_propia_pestana_de_historial(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));
        $id=DB::table('peluqueria_citas')->value('id');
        $this->assertSame(0,$this->controller()->ficha($id)->getData()['anteriores']->total());
        $this->controller()->fichaGuardar($this->req(['mascota'=>'Mascota 1','especie'=>'Canina','esterilizada'=>'desconocido',
            'servicios'=>[['nombre'=>'Baño','precio'=>15000]],'observaciones'=>'Atención cerrada de prueba','accion'=>'finalizar']),$id);
        $vista=$this->controller()->ficha($id);
        $this->assertSame([$id],$vista->getData()['anteriores']->pluck('id')->all());
        $this->assertStringContainsString('Atención cerrada de prueba',$vista->render());
        $this->assertSame(1,$this->controller()->index($this->req())->getData()['historial']->total());
    }
    public function test_agendamiento_modal_crea_tutor_mascota_y_reserva_en_una_operacion(): void {
        $procedimiento=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>'Corte de uñas','precio'=>5000,'duracion'=>30,'activo'=>1]);
        $datos=['nuevo_cliente'=>1,'tutor'=>'Tutor prueba','telefono'=>'912345678','mascota'=>'Luna','especie'=>'Felina','procedimiento_id'=>$procedimiento,'inicio'=>now()->addDay()->format('Y-m-d').'T12:00'];
        app(CuentasService::class)->marcarPerfilCompleto(User::find(1),'peluqueria');
        $this->actingAs(User::find(1))->withSession(['rol_activo'=>'peluqueria'])->postJson(route('peluqueria.agenda'),$datos)
            ->assertOk()->assertJsonPath('estado',1)->assertJsonStructure(['cita_id','ficha_url']);
        $this->assertSame(1,DB::table('peluqueria_clientes')->count());
        $cita=DB::table('peluqueria_citas')->first();$this->assertSame('Corte de uñas',$cita->servicio);$this->assertEquals(5000,$cita->valor);
        $this->actingAs(User::find(1))->get(route('peluqueria.ficha',$cita->id))->assertOk()->assertSee('Luna')->assertSee('Peluquería canina y felina');
        $datos['mascota']='Otra';$this->actingAs(User::find(1))->postJson(route('peluqueria.agenda'),$datos)->assertUnprocessable()->assertJsonValidationErrors('inicio');
        $this->assertSame(1,DB::table('peluqueria_clientes')->count());$this->assertSame(1,DB::table('peluqueria_citas')->count());
    }
    public function test_ficha_guarda_detalle_servicios_total_y_switches_y_finaliza_en_historial(): void {
        $bano=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>'Baño','duracion'=>30,'precio'=>10000,'activo'=>1]);
        $unas=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>'Corte de uñas','duracion'=>30,'precio'=>5000,'activo'=>1]);
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));
        $id=DB::table('peluqueria_citas')->value('id');
        $html=$this->controller()->ficha($id)->render();
        foreach(['Alergias a productos de belleza','Comportamiento durante la atención','Análisis visual','Pulgas','Corte de uñas','Guardar y finalizar atención'] as $texto) $this->assertStringContainsString($texto,$html);
        $datos=['mascota'=>'Luna','especie'=>'Felina','edad'=>'2 años','esterilizada'=>'si','alergias'=>'Alergia informada','alergias_productos'=>'Perfumes',
            'comportamientos'=>['Ansioso/a','Requiere bozal'],'hallazgos'=>['Pulgas','Irritaciones en piel'],'hallazgos_otro'=>'Observar zona dorsal',
            'servicios'=>[['procedimiento_id'=>$bano,'nombre'=>'Baño','precio'=>1],['procedimiento_id'=>$unas,'nombre'=>'Corte de uñas','precio'=>1]],'observaciones_servicios'=>'Baño suave','productos_utilizados'=>'Champú indicado por tutor','observaciones'=>'Sin incidentes','accion'=>'guardar','total'=>1];
        $this->controller()->fichaGuardar($this->req($datos),$id);
        $guardada=json_decode(DB::table('peluqueria_fichas')->where('cita_id',$id)->value('datos'),true);
        $this->assertSame(15000,$guardada['total']);
        $this->assertSame(['Pulgas','Irritaciones en piel'],$guardada['hallazgos']);
        $this->assertSame('Perfumes',$guardada['alergias_productos']);
        $this->assertSame('en_atencion',DB::table('peluqueria_citas')->find($id)->estado);
        unset($datos['comportamientos'],$datos['hallazgos']);$datos['accion']='finalizar';
        $this->controller()->fichaGuardar($this->req($datos),$id);
        $this->assertSame(1,DB::table('peluqueria_fichas')->count());
        $guardada=json_decode(DB::table('peluqueria_fichas')->value('datos'),true);
        $this->assertSame([],$guardada['hallazgos']);$this->assertSame([],$guardada['comportamientos']);
        $cita=DB::table('peluqueria_citas')->find($id);$this->assertSame('finalizada',$cita->estado);$this->assertNotNull($cita->finalizada_en);
        $this->assertStringContainsString('Corte de uñas',$this->controller()->index($this->req())->render());
        $this->assertStringNotContainsString('Guardar y finalizar atención',$this->controller()->ficha($id)->render());
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->controller()->fichaGuardar($this->req($datos),$id);
    }
    public function test_ficha_rechaza_precios_negativos_y_no_crea_un_registro_parcial(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$id=DB::table('peluqueria_citas')->value('id');
        try {$this->controller()->fichaGuardar($this->req(['mascota'=>'Luna','especie'=>'Felina','esterilizada'=>'no','servicios'=>[['nombre'=>'Baño','precio'=>-1]],'accion'=>'finalizar']),$id);$this->fail();}
        catch(ValidationException $e){$this->assertArrayHasKey('servicios.0.precio',$e->errors());}
        $this->assertSame(0,DB::table('peluqueria_fichas')->count());$this->assertSame('reservada',DB::table('peluqueria_citas')->find($id)->estado);
    }
    public function test_ficha_de_otro_negocio_no_es_accesible(): void {
        $cliente=$this->cliente(2);$id=DB::table('peluqueria_citas')->insertGetId(['peluqueria_id'=>2,'cliente_id'=>$cliente,'inicio'=>now(),'fin'=>now()->addHour(),'servicio'=>'Baño']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->ficha($id);
    }
    public function test_account_role_and_initial_profile_completion(): void {
        $this->assertTrue(CuentasService::esTipoValido('peluqueria'));
        $this->assertTrue(User::find(1)->hasRole('Peluqueria'));
        $this->assertSame('peluqueria.home',app(CuentasService::class)->rutaEscritorio('peluqueria'));
        $this->assertInstanceOf(Peluqueria::class,app(CuentasService::class)->perfil(User::find(1),'peluqueria'));
        $this->actingAs(User::find(1))->get(route('cuenta.entrar',['tipo'=>'peluqueria']))->assertRedirect(route('perfil.completar',['tipo'=>'peluqueria']));
        $this->actingAs(User::find(1))->get(route('perfil.completar',['tipo'=>'peluqueria']))->assertOk()->assertSee('peluquería');
        $this->actingAs(User::find(1))->post(route('perfil.completar.guardar',['tipo'=>'peluqueria']),['nombre'=>'Mi pelu','telefono'=>'555','direccion'=>'Centro'])->assertRedirect(route('peluqueria.home'));
        $this->assertTrue(app(CuentasService::class)->perfilCompleto(User::find(1),'peluqueria'));
    }
    public function test_completed_profile_opens_agenda_through_web_routes(): void {
        app(CuentasService::class)->marcarPerfilCompleto(User::find(1),'peluqueria');
        $this->actingAs(User::find(1))->withSession(['rol_activo'=>'peluqueria'])->get(route('peluqueria.home'))
            ->assertOk()->assertSee('Agenda del día')->assertSee('Registrar tutor y mascota')->assertSee('Servicios realizados');
        $request=new \App\Http\Requests\RegistroCuentaRequest();
        $rules=$request->rules();
        $this->assertFalse(validator(['tipo_cuenta'=>'peluqueria'],['tipo_cuenta'=>$rules['tipo_cuenta']])->fails());
    }

    public function test_registration_and_agenda_render_only_own_records(): void {
        $this->controller()->clienteGuardar($this->req(['tutor'=>'Ana','telefono'=>'555','mascota'=>'Luna','especie'=>'Felina']));$this->cliente(2);
        $this->controller()->citaGuardar($this->req($this->reserva(DB::table('peluqueria_clientes')->where('peluqueria_id',1)->value('id'))));
        $html=$this->controller()->index($this->req(['fecha'=>now()->addDay()->format('Y-m-d')]))->render();
        $this->assertStringContainsString('Luna',$html);$this->assertStringNotContainsString('Mascota 2',$html);$this->assertStringContainsString('Baño',$html);
    }
    public function test_all_three_service_options_can_be_booked_and_other_values_are_rejected(): void {
        $id=$this->cliente();
        foreach(['Baño','Belleza','Peluquería'] as $i=>$servicio){
            $datos=$this->reserva($id,(10+$i).':00');$datos['servicio']=$servicio;
            $this->controller()->citaGuardar($this->req($datos));
        }
        $this->assertSame(['Baño','Belleza','Peluquería'],DB::table('peluqueria_citas')->orderBy('id')->pluck('servicio')->all());
        $this->expectException(ValidationException::class);
        $datos=$this->reserva($id,'15:00');$datos['servicio']='Otro';$this->controller()->citaGuardar($this->req($datos));
    }

    public function test_overlapping_reservation_is_rejected_but_adjacent_one_allowed(): void {
        $id=$this->cliente();$this->controller()->citaGuardar($this->req($this->reserva($id)));$this->controller()->citaGuardar($this->req($this->reserva($id,'11:00')));
        try{$this->controller()->citaGuardar($this->req($this->reserva($id,'10:30')));$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}
        $this->assertSame(2,DB::table('peluqueria_citas')->count());
    }
    public function test_full_attention_flow_requires_record_and_keeps_finalized_history(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$id=DB::table('peluqueria_citas')->value('id');
        foreach(['confirmada','en_atencion'] as $estado)$this->controller()->estado($this->req(compact('estado')),$id);
        try{$this->controller()->estado($this->req(['estado'=>'finalizada']),$id);$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('registro',$e->errors());}
        $this->controller()->estado($this->req(['estado'=>'finalizada','registro'=>'Baño y secado, sin incidentes.']),$id);
        $cita=DB::table('peluqueria_citas')->find($id);$this->assertNotNull($cita->finalizada_en);$this->assertSame('finalizada',$cita->estado);
        $this->assertStringContainsString('Baño y secado, sin incidentes.',$this->controller()->index($this->req())->render());
        $this->expectException(ValidationException::class);$this->controller()->estado($this->req(['estado'=>'cancelada']),$id);
    }
    public function test_cannot_book_another_business_client(): void {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->citaGuardar($this->req($this->reserva($this->cliente(2))));
    }
    public function test_cannot_update_another_business_reservation(): void {
        $client=$this->cliente(2);$id=DB::table('peluqueria_citas')->insertGetId(['peluqueria_id'=>2,'cliente_id'=>$client,'inicio'=>now(),'fin'=>now()->addHour(),'servicio'=>'Baño']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->estado($this->req(['estado'=>'cancelada']),$id);
    }
    public function test_user_without_active_role_is_denied(): void {
        DB::table('usuario_roles')->where('id_usuario',1)->update(['estado'=>0]);$this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->index($this->req());
    }
    public function test_cancellation_releases_schedule_and_router_matches_module(): void {
        $id=$this->cliente();$this->controller()->citaGuardar($this->req($this->reserva($id)));$cita=DB::table('peluqueria_citas')->value('id');$this->controller()->estado($this->req(['estado'=>'cancelada']),$cita);$this->controller()->citaGuardar($this->req($this->reserva($id)));
        $this->assertSame(2,DB::table('peluqueria_citas')->count());
        $this->assertSame('peluqueria.home',app('router')->getRoutes()->match(Request::create('/Peluqueria'))->getName());
    }
}
