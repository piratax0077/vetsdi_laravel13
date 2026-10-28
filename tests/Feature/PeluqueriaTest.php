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
        (require database_path('migrations/2026_10_25_000001_configuracion_operativa_peluqueria.php'))->up();
        (require database_path('migrations/2026_10_25_000002_rut_clientes_peluqueria.php'))->up();
        (require database_path('migrations/2026_10_25_000003_flujo_reservas_peluqueria.php'))->up();
        (require database_path('migrations/2026_10_25_000004_detalle_procedimientos_reserva.php'))->up();
        (require database_path('migrations/2026_10_25_000005_centro_estetico.php'))->up();
        Schema::create('pacientes',function(Blueprint $t){$t->id();$t->string('rut');$t->string('nombres');$t->string('apellido_uno');$t->string('apellido_dos');$t->string('telefono_uno')->nullable();$t->string('email')->nullable();});
        DB::table('users')->insert([['id'=>1,'name'=>'Pelu Uno','email'=>'uno@example.test'],['id'=>2,'name'=>'Pelu Dos','email'=>'dos@example.test']]);
        foreach([1,2] as $id){DB::table('peluquerias')->insert(['id'=>$id,'id_usuario'=>$id,'nombre'=>'Local '.$id,'telefono'=>'123','direccion'=>'Calle']);app(CuentasService::class)->asignarRol(User::find($id),'peluqueria');\App\Models\ProfesionalEstetica::create(['id_usuario'=>$id,'nombre'=>'Estilista '.$id]);}
        Auth::setUser(User::find(1));
        view()->share('errors',new \Illuminate\Support\ViewErrorBag());
    }
    public function test_historial_muestra_sucursal_en_cada_fila_y_respeta_el_acceso_del_personal(): void {
        $a=$this->sucursal();$b=$this->sucursal();
        DB::table('peluqueria_sucursales')->where('id',$a)->update(['nombre'=>'Sucursal Norte','direccion'=>'Calle Norte']);
        DB::table('peluqueria_sucursales')->where('id',$b)->update(['nombre'=>'Sucursal Sur','direccion'=>'Calle Sur']);
        $cliente=$this->cliente();
        foreach([$a,$b] as $id)DB::table('peluqueria_citas')->insert(['peluqueria_id'=>1,'cliente_id'=>$cliente,'sucursal_id'=>$id,'inicio'=>now()->subDay(),'fin'=>now()->subDay()->addHour(),'servicio'=>'Baño','estado'=>'finalizada','valor'=>15000,'finalizada_en'=>now()]);
        $this->trabajador(['ver_historial'],$a);
        app(CuentasService::class)->marcarPerfilCompleto(User::find(1),'peluqueria');
        $this->actingAs(User::find(1))->withSession(['rol_activo'=>'peluqueria'])->get(route('peluqueria.historial'))
            ->assertOk()->assertSee('Sucursal Norte')->assertSee('Sucursal Sur')->assertSee('Calle Norte')->assertDontSee('id="agenda-sucursal"',false);
        $this->actingAs(User::find(3))->withSession(['rol_activo'=>'centro_estetico'])->get(route('centro_estetico.historial'))
            ->assertOk()->assertSee('Sucursal Norte')->assertDontSee('Sucursal Sur');
    }
    protected function tearDown(): void {\Carbon\Carbon::setTestNow();parent::tearDown();}
    private function configController(): \App\Http\Controllers\PeluqueriaConfiguracionController {return app(\App\Http\Controllers\PeluqueriaConfiguracionController::class);}
    private function sucursal(int $negocio=1,int $capacidad=1): int {
        return DB::table('peluqueria_sucursales')->insertGetId(['peluqueria_id'=>$negocio,'nombre'=>'Sucursal '.$negocio,'direccion'=>'Calle','activo'=>true,'capacidad'=>$capacidad]);
    }
    private function trabajador(array $permisos,?int $sucursal=null): int {
        DB::table('users')->insertOrIgnore(['id'=>3,'name'=>'Recepción','email'=>'personal@example.test']);
        $this->configController()->personal($this->req(['email'=>'personal@example.test','cargo'=>'Recepción','sucursal_id'=>$sucursal,'activo'=>1,'permisos'=>$permisos]));
        return DB::table('peluqueria_personal')->where('usuario_id',3)->value('id');
    }
    private function horario(int $sucursal): void {
        $this->configController()->agenda($this->req(['sucursal_id'=>$sucursal,'dias'=>[1,2,3,4,5],'apertura'=>'09:00','cierre'=>'18:00','pausa_inicio'=>'13:00','pausa_fin'=>'14:00','intervalo'=>30,'anticipacion_horas'=>0,'anticipacion_dias'=>30]));
    }
    public function test_configuracion_muestra_personal_agenda_sucursales_y_negocio(): void {
        $id=$this->sucursal();$this->trabajador(['ver_agenda'],$id);
        foreach(['procedimientos','personal','agenda','sucursales','negocio'] as $seccion){
            $html=$this->controller()->configuracion($this->req(compact('seccion')))->render();
            foreach(['Personal y permisos','Configurar agenda','Sucursales','Datos del negocio'] as $texto)$this->assertStringContainsString($texto,$html);
            $this->assertStringNotContainsString('@endif',$html);
        }
    }
    public function test_personal_entra_con_su_cuenta_y_solo_los_permisos_asignados(): void {
        $this->trabajador(['ver_agenda']);Auth::setUser(User::find(3));
        $cuentas=app(CuentasService::class);$this->assertTrue($cuentas->tieneRol(User::find(3),'centro_estetico'));$this->assertTrue($cuentas->perfilCompleto(User::find(3),'centro_estetico'));
        $this->assertSame('centro_estetico',$cuentas->rolesActivos(User::find(3))->first()->tipo);
        $this->actingAs(User::find(3))->get(route('cuenta.entrar',['tipo'=>'centro_estetico']))->assertRedirect(route('centro_estetico.home'));
        $html=$this->controller()->index($this->req())->render();$this->assertStringContainsString('Agendas del centro',$html);$this->assertStringNotContainsString('Panel de configuración',$html);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->controller()->procedimientoGuardar($this->req(['nombre'=>'No autorizado','duracion'=>30,'precio'=>1000,'activo'=>1]));
    }
    public function test_desactivar_personal_revoca_su_acceso_sin_cerrar_sesion(): void {
        $id=$this->trabajador(['ver_agenda']);Auth::setUser(User::find(3));$this->controller()->index($this->req());
        DB::table('peluqueria_personal')->where('id',$id)->update(['activo'=>false]);
        $this->assertFalse(app(CuentasService::class)->tieneRol(User::find(3),'peluqueria'));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->index($this->req());
    }
    public function test_personal_sin_permiso_no_puede_reservar_ni_cambiar_permisos(): void {
        $this->trabajador(['ver_agenda']);Auth::setUser(User::find(3));
        try{$this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->configController()->personal($this->req(['email'=>'personal@example.test','cargo'=>'Admin','activo'=>1,'permisos'=>['negocio']]));
    }
    public function test_sucursales_tienen_agendas_independientes_y_no_aceptan_ajenas(): void {
        $a=$this->sucursal();$b=$this->sucursal();$cliente=$this->cliente();
        foreach([$a,$b] as $id){$datos=$this->reserva($cliente);$datos['sucursal_id']=$id;$this->controller()->citaGuardar($this->req($datos));}
        $this->assertSame(2,DB::table('peluqueria_citas')->count());
        $ajena=$this->sucursal(2);$datos=$this->reserva($cliente);$datos['sucursal_id']=$ajena;
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->citaGuardar($this->req($datos));
    }
    public function test_personal_asignado_solo_ve_y_reserva_su_sucursal(): void {
        $a=$this->sucursal();$b=$this->sucursal();$cliente=$this->cliente();
        foreach([$a,$b] as $id){$datos=$this->reserva($cliente);$datos['sucursal_id']=$id;$this->controller()->citaGuardar($this->req($datos));}
        $this->trabajador(['reservar'],$a);Auth::setUser(User::find(3));
        $r=$this->controller()->eventos(Request::create('/','GET',['start'=>now()->addDay()->startOfDay()->format('Y-m-d'),'end'=>now()->addDays(2)->startOfDay()->format('Y-m-d'),'sucursal_id'=>$a]));$this->assertCount(1,$r->getData(true));
        $datos=$this->reserva($cliente,'12:00');$datos['sucursal_id']=$b;
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->citaGuardar($this->req($datos));
    }
    public function test_horario_rechaza_cerrado_descanso_y_dia_cerrado(): void {
        \Carbon\Carbon::setTestNow('2026-10-26 08:00:00');$id=$this->sucursal();$this->horario($id);$cliente=$this->cliente();
        foreach(['08:00','13:00','17:30'] as $hora){$datos=$this->reserva($cliente,$hora);$datos['sucursal_id']=$id;
            try{$this->controller()->citaGuardar($this->req($datos));$this->fail($hora);}catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}}
        $datos=$this->reserva($cliente);$datos['sucursal_id']=$id;$datos['inicio']='2026-11-01T10:00';
        try{$this->controller()->citaGuardar($this->req($datos));$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}
        $datos['inicio']='2026-10-27T10:00';$this->controller()->citaGuardar($this->req($datos));$this->assertSame(1,DB::table('peluqueria_citas')->count());
    }
    public function test_bloqueo_impide_reservas_y_retirarlo_libera_el_horario(): void {
        $sucursal=$this->sucursal();$cliente=$this->cliente();$inicio=now()->addDay()->format('Y-m-d').'T10:00';$fin=now()->addDay()->format('Y-m-d').'T12:00';
        $this->configController()->bloqueo($this->req(['sucursal_id'=>$sucursal,'inicio'=>$inicio,'fin'=>$fin,'motivo'=>'Mantenimiento']));
        $datos=$this->reserva($cliente);$datos['sucursal_id']=$sucursal;
        try{$this->controller()->citaGuardar($this->req($datos));$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}
        $this->configController()->quitarBloqueo(DB::table('peluqueria_bloqueos')->value('id'));$this->controller()->citaGuardar($this->req($datos));$this->assertSame(1,DB::table('peluqueria_citas')->count());
    }
    public function test_capacidad_y_reservas_consecutivas_se_validan_sin_falsos_solapamientos(): void {
        $id=$this->sucursal(1,2);$cliente=$this->cliente();
        foreach(['10:00','11:00'] as $hora){$datos=$this->reserva($cliente,$hora);$datos['sucursal_id']=$id;$this->controller()->citaGuardar($this->req($datos));}
        $datos=$this->reserva($cliente);$datos['sucursal_id']=$id;$datos['duracion']=120;$this->controller()->citaGuardar($this->req($datos));
        $this->assertSame(3,DB::table('peluqueria_citas')->count());
        $this->expectException(ValidationException::class);$this->controller()->citaGuardar($this->req($datos));
    }
    public function test_no_se_puede_reservar_sucursal_inactiva_ni_personal_ocupado(): void {
        $id=$this->sucursal(1,2);$personal=$this->trabajador(['atender'],$id);$datos=$this->reserva($this->cliente());$datos['sucursal_id']=$id;$datos['personal_id']=$personal;$this->controller()->citaGuardar($this->req($datos));
        try{$this->controller()->citaGuardar($this->req($datos));$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}
        DB::table('peluqueria_sucursales')->where('id',$id)->update(['activo'=>false]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->citaGuardar($this->req($datos));
    }
    public function test_no_se_pueden_editar_sucursales_ni_bloqueos_de_otro_negocio(): void {
        $id=$this->sucursal(2);$this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->configController()->agenda($this->req(['sucursal_id'=>$id,'dias'=>[1],'apertura'=>'09:00','cierre'=>'18:00','intervalo'=>15,'anticipacion_horas'=>0,'anticipacion_dias'=>30]));
    }

    public function test_solo_lectura_no_abre_fichas_y_no_registra_clientes_al_reservar(): void {
        $sucursal=$this->sucursal();$cliente=$this->cliente();$datos=$this->reserva($cliente);$datos['sucursal_id']=$sucursal;$this->controller()->citaGuardar($this->req($datos));$cita=DB::table('peluqueria_citas')->value('id');
        $this->trabajador(['reservar'],$sucursal);Auth::setUser(User::find(3));
        try{$this->controller()->ficha($cita);$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        $datos['nuevo_cliente']=1;$datos['rut']='12345678-5';
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->citaGuardar($this->req($datos));
    }
    public function test_horario_anticipacion_y_plazo_maximo_se_aplican_en_chile(): void {
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::parse('2026-10-26 09:00:00','America/Santiago'));
        $id=$this->sucursal();$this->configController()->agenda($this->req(['sucursal_id'=>$id,'dias'=>[1,2,3,4,5,6,7],'apertura'=>'09:00','cierre'=>'20:00','intervalo'=>15,'anticipacion_horas'=>4,'anticipacion_dias'=>5]));$cliente=$this->cliente();
        foreach(['2026-10-26T10:00','2026-11-10T10:00'] as $inicio){$datos=$this->reserva($cliente);$datos['inicio']=$inicio;$datos['sucursal_id']=$id;
            try{$this->controller()->citaGuardar($this->req($datos));$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}}
        $datos['inicio']='2026-10-26T13:00';$this->controller()->citaGuardar($this->req($datos));$this->assertSame(1,DB::table('peluqueria_citas')->count());
    }
    public function test_contexto_revocado_no_se_reemplaza_silenciosamente_por_otro_negocio(): void {
        $this->sucursal();session(['peluqueria_contexto'=>2]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->index($this->req());
    }

    public function test_duracion_flexible_permite_reservas_consecutivas_fuera_de_intervalos_fijos(): void {
        \Carbon\Carbon::setTestNow('2026-10-26 08:00:00');$sucursal=$this->sucursal();$this->horario($sucursal);$cliente=$this->cliente();
        $primero=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>'Servicio de 17 minutos','duracion'=>17,'precio'=>5000,'activo'=>1]);
        $segundo=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>'Servicio de 42 minutos','duracion'=>42,'precio'=>15000,'activo'=>1]);
        $datos=$this->reserva($cliente,'10:07');$datos['sucursal_id']=$sucursal;$datos['procedimiento_id']=$primero;$datos['duracion']=300;
        $this->controller()->citaGuardar($this->req($datos));
        $cita=DB::table('peluqueria_citas')->first();$this->assertSame('10:24',\Carbon\Carbon::parse($cita->fin)->format('H:i'));
        $datos['inicio']='2026-10-27T10:24';$datos['procedimiento_id']=$segundo;$this->controller()->citaGuardar($this->req($datos));
        $ultima=DB::table('peluqueria_citas')->orderByDesc('id')->first();$this->assertSame('11:06',\Carbon\Carbon::parse($ultima->fin)->format('H:i'));
        $datos['inicio']='2026-10-27T10:23';$this->expectException(ValidationException::class);$this->controller()->citaGuardar($this->req($datos));
    }
    public function test_procedimientos_pueden_durar_menos_de_quince_minutos(): void {
        $this->controller()->procedimientoGuardar($this->req(['nombre'=>'Procedimiento breve','duracion'=>7,'precio'=>1000,'activo'=>1]));
        $this->assertEquals(7,DB::table('peluqueria_procedimientos')->value('duracion'));
        $html=$this->controller()->configuracion($this->req(['seccion'=>'agenda']))->render();$this->assertStringNotContainsString('Intervalo entre inicios de reserva',$html);
    }

    public function test_horario_profesional_admite_tramos_distintos_por_dia_sin_perder_duracion(): void {
        \Carbon\Carbon::setTestNow('2026-10-26 08:00:00');$sucursal=$this->sucursal();
        $tramos=[['dia'=>2,'inicio'=>'09:00','fin'=>'12:00'],['dia'=>2,'inicio'=>'15:00','fin'=>'19:00'],['dia'=>3,'inicio'=>'10:00','fin'=>'14:00']];
        $this->configController()->agenda($this->req(['sucursal_id'=>$sucursal,'tramos'=>$tramos,'anticipacion_horas'=>0,'anticipacion_dias'=>30]));
        $datos=$this->reserva($this->cliente(),'11:30');$datos['sucursal_id']=$sucursal;
        try{$this->controller()->citaGuardar($this->req($datos));$this->fail();}catch(ValidationException $e){$this->assertArrayHasKey('inicio',$e->errors());}
        $datos['inicio']='2026-10-27T15:07';$datos['duracion']=42;$this->controller()->citaGuardar($this->req($datos));
        $this->assertSame('15:49',\Carbon\Carbon::parse(DB::table('peluqueria_citas')->value('fin'))->format('H:i'));
        $html=$this->controller()->configuracion($this->req(['seccion'=>'agenda']))->render();$this->assertStringContainsString('agenda-profesional.css',$html);$this->assertStringContainsString('Agregar horario',$html);
    }
    public function test_tramos_superpuestos_no_reemplazan_horario_valido(): void {
        $sucursal=$this->sucursal();$this->horario($sucursal);$antes=DB::table('peluqueria_sucursales')->where('id',$sucursal)->value('agenda');
        try{$this->configController()->agenda($this->req(['sucursal_id'=>$sucursal,'tramos'=>[['dia'=>1,'inicio'=>'09:00','fin'=>'13:00'],['dia'=>1,'inicio'=>'12:00','fin'=>'14:00']],'anticipacion_horas'=>0,'anticipacion_dias'=>30]));$this->fail();}catch(ValidationException $e){$this->assertSame($antes,DB::table('peluqueria_sucursales')->where('id',$sucursal)->value('agenda'));}
    }

    public function test_agenda_se_elige_desde_escritorio_y_no_tiene_selector_de_sucursal_dentro(): void {
        $id=$this->sucursal();
        DB::table('peluqueria_procedimientos')->insert(['peluqueria_id'=>1,'nombre'=>'Baño','duracion'=>30,'precio'=>5000,'activo'=>1]);
        $html=$this->controller()->index($this->req())->render();$this->assertStringContainsString('seleccionar-agenda-peluqueria',$html);
        $this->actingAs(User::find(1))->get(route('peluqueria.agenda.ver'))->assertRedirect(route('peluqueria.home',['elegir_agenda'=>1]));
        $r=$this->actingAs(User::find(1))->get(route('peluqueria.agenda.ver',['sucursal_id'=>$id,'agenda_personal'=>'titular']));
        $r->assertOk()->assertSee('Pelu Uno')->assertDontSee('id="agenda-sucursal"',false)->assertSee('name="agenda_personal"',false);
        $this->actingAs(User::find(1))->get(route('peluqueria.agenda.ver'))->assertOk()->assertDontSee('id="agenda-sucursal"',false);
    }
    public function test_calendario_filtra_por_profesional_y_lugar_y_reserva_con_esa_asignacion(): void {
        $sucursal=$this->sucursal(1,2);$personal=$this->trabajador(['atender'],$sucursal);$cliente=$this->cliente();
        $datos=$this->reserva($cliente);$datos['sucursal_id']=$sucursal;$datos['agenda_personal']='titular';$datos['personal_id']=$personal;
        $this->controller()->citaGuardar($this->req($datos));$this->assertNull(DB::table('peluqueria_citas')->first()->personal_id);
        $datos['agenda_personal']=(string)$personal;unset($datos['personal_id']);$this->controller()->citaGuardar($this->req($datos));
        foreach(['titular',(string)$personal] as $agenda){
            $r=$this->controller()->eventos(Request::create('/','GET',['start'=>now()->addDay()->startOfDay()->format('Y-m-d'),'end'=>now()->addDays(2)->startOfDay()->format('Y-m-d'),'sucursal_id'=>$sucursal,'agenda_personal'=>$agenda]));$this->assertCount(1,$r->getData(true));
        }
    }

    public function test_busqueda_rut_trae_mascotas_del_tutor_sin_exponer_otro_negocio(): void {
        Schema::create('mascotas',function(Blueprint $t){$t->id();$t->integer('id_responsable');$t->string('nombre');});
        DB::table('pacientes')->insert(['id'=>1,'rut'=>'12.345.678-5','nombres'=>'Tutor','apellido_uno'=>'Prueba','apellido_dos'=>'']);
        DB::table('mascotas')->insert([['id'=>1,'id_responsable'=>1,'nombre'=>'Luna'],['id'=>2,'id_responsable'=>2,'nombre'=>'Ajena']]);
        $ajeno=$this->cliente(2);DB::table('peluqueria_clientes')->where('id',$ajeno)->update(['rut'=>'123456785']);
        $datos=$this->controller()->buscarTutor($this->req(['rut'=>'12.345.678-5']))->getData(true);
        $this->assertSame('Tutor Prueba',$datos['tutor']);
        $this->assertSame([['id'=>'sistema:1','nombre'=>'Luna']],$datos['mascotas']);
    }
    public function test_busqueda_rechaza_rut_invalido(): void {
        $this->expectException(ValidationException::class);
        $this->controller()->buscarTutor($this->req(['rut'=>'12345678-0']));
    }
    public function test_no_se_pueden_saltar_los_pasos_de_la_agenda_y_el_pago_se_registra_una_sola_vez(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$id=DB::table('peluqueria_citas')->value('id');
        foreach(['pagada','en_espera','en_atencion'] as $estado){
            try{$this->controller()->estado($this->req(compact('estado')),$id);$this->fail('Permitió saltar pasos.');}
            catch(ValidationException $e){$this->assertArrayHasKey('estado',$e->errors());}
        }
        $this->controller()->estado($this->req(['estado'=>'confirmada','via_confirmacion'=>'WhatsApp']),$id);
        try{$this->controller()->estado($this->req(['estado'=>'pagada','medio_pago'=>'Efectivo','monto_pagado'=>1]),$id);$this->fail();}
        catch(ValidationException $e){$this->assertArrayHasKey('monto_pagado',$e->errors());}
        $this->controller()->estado($this->req(['estado'=>'pagada','medio_pago'=>'Transferencia','monto_pagado'=>15000,'referencia_pago'=>'PRUEBA']),$id);
        $cita=DB::table('peluqueria_citas')->find($id);$this->assertNotNull($cita->pagada_en);$this->assertSame(1,$cita->pago_usuario_id);$this->assertEquals(15000,$cita->monto_pagado);
        try{$this->controller()->estado($this->req(['estado'=>'pagada','medio_pago'=>'Efectivo','monto_pagado'=>15000]),$id);$this->fail();}
        catch(ValidationException $e){$this->assertArrayHasKey('estado',$e->errors());}
        $this->controller()->estado($this->req(['estado'=>'en_espera']),$id);$this->assertNotNull(DB::table('peluqueria_citas')->find($id)->espera_en);
    }
    public function test_personal_sin_permiso_de_pago_no_puede_recepcionar(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$id=DB::table('peluqueria_citas')->value('id');
        $this->controller()->estado($this->req(['estado'=>'confirmada','via_confirmacion'=>'Teléfono']),$id);
        $this->trabajador(['ver_agenda','confirmar']);Auth::setUser(User::find(3));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->controller()->estado($this->req(['estado'=>'pagada','medio_pago'=>'Efectivo','monto_pagado'=>15000]),$id);
    }
    public function test_tutor_existente_puede_inscribir_otra_mascota_y_encontrarla_por_su_rut(): void {
        DB::table('pacientes')->insert(['id'=>1,'rut'=>'12.345.678-5','nombres'=>'Tutor','apellido_uno'=>'Existente','apellido_dos'=>'','telefono_uno'=>'912345678','email'=>'tutor@example.test']);
        Schema::create('mascotas',function(Blueprint $t){$t->id();$t->integer('id_responsable');$t->string('nombre');});
        $datos=$this->reserva(0);$datos+=['nuevo_cliente'=>1,'rut'=>'12.345.678-5','tutor'=>'Dato alterado','telefono'=>'123','mascota'=>'Nueva mascota','especie'=>'Felina'];
        $this->controller()->citaGuardar($this->req($datos));
        $cliente=DB::table('peluqueria_clientes')->first();$this->assertSame('Tutor Existente',$cliente->tutor);$this->assertSame('123456785',$cliente->rut);
        $resultado=$this->controller()->buscarTutor($this->req(['rut'=>'12345678-5']))->getData(true);
        $this->assertSame('Nueva mascota',$resultado['mascotas'][0]['nombre']);
        $this->assertSame('local:'.$cliente->id,$resultado['mascotas'][0]['id']);
    }
    public function test_una_mascota_del_sistema_solo_se_reserva_con_el_rut_de_su_tutor(): void {
        DB::table('pacientes')->insert(['id'=>1,'rut'=>'12345678-5','nombres'=>'Tutor','apellido_uno'=>'Existente','apellido_dos'=>'']);
        Schema::create('mascotas',function(Blueprint $t){$t->id();$t->integer('id_responsable');$t->string('nombre');});
        DB::table('mascotas')->insert(['id'=>1,'id_responsable'=>2,'nombre'=>'Otra mascota']);
        $datos=$this->reserva(0)+['rut'=>'12345678-5','mascota_sistema_id'=>1];
        try{$this->controller()->citaGuardar($this->req($datos));$this->fail('Reservó la mascota de otro tutor.');}
        catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(404,$e->getStatusCode());}
        $this->assertSame(0,DB::table('peluqueria_clientes')->count());$this->assertSame(0,DB::table('peluqueria_citas')->count());
    }
    public function test_reserva_varios_procedimientos_suma_aranceles_y_duracion_y_conserva_detalle_para_pago(): void {
        $ids=[];foreach([['Baño',5000,25],['Corte',12000,40]] as [$nombre,$precio,$duracion])$ids[]=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>$nombre,'precio'=>$precio,'duracion'=>$duracion,'activo'=>true]);
        $datos=$this->reserva($this->cliente());$datos['procedimiento_ids']=$ids;$datos['valor']=1;$datos['duracion']=1;
        $this->controller()->citaGuardar($this->req($datos));$cita=DB::table('peluqueria_citas')->first();
        $this->assertEquals(17000,$cita->valor);$this->assertEquals(65,\Carbon\Carbon::parse($cita->inicio)->diffInMinutes(\Carbon\Carbon::parse($cita->fin)));
        $this->assertSame([5000,12000],array_column(json_decode($cita->procedimientos_reservados,true),'precio'));
        DB::table('peluqueria_procedimientos')->whereIn('id',$ids)->update(['precio'=>999]);
        $ficha=$this->controller()->ficha($cita->id)->getData();$this->assertSame([5000,12000],array_column($ficha['datos']['servicios'],'precio'));
        $this->prepararAtencion($cita->id,false);$this->assertEquals(17000,DB::table('peluqueria_citas')->find($cita->id)->monto_pagado);
    }
    public function test_reserva_pagada_permanece_en_agenda_y_puede_pasar_a_espera_y_atencion(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$id=DB::table('peluqueria_citas')->value('id');
        $this->controller()->estado($this->req(['estado'=>'confirmada','via_confirmacion'=>'Presencial']),$id);
        $request=$this->req(['estado'=>'pagada','medio_pago'=>'Efectivo','monto_pagado'=>15000]);$request->headers->set('Accept','application/json');
        $respuesta=$this->controller()->estado($request,$id)->getData(true);
        $this->assertSame('pagada',$respuesta['reserva']['estado']);$this->assertSame('#7043b5',$respuesta['reserva']['color']);$this->assertContains('en_atencion',$respuesta['reserva']['acciones']);$this->assertNull($respuesta['ficha']);
        $eventos=$this->controller()->eventos($this->req(['start'=>now()->addDay()->startOfDay()->toIso8601String(),'end'=>now()->addDays(2)->startOfDay()->toIso8601String()]))->getData(true);
        $this->assertCount(1,$eventos);$this->assertSame('pagada',$eventos[0]['extendedProps']['reserva']['estado']);
        $this->controller()->estado($this->req(['estado'=>'en_espera']),$id);
        $this->controller()->estado($this->req(['estado'=>'en_atencion']),$id);
        $this->assertSame('en_atencion',DB::table('peluqueria_citas')->find($id)->estado);
    }
    public function test_pago_permite_atender_directamente_al_profesional_y_bloquea_a_recepcion(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$id=DB::table('peluqueria_citas')->value('id');
        $this->controller()->estado($this->req(['estado'=>'confirmada','via_confirmacion'=>'Presencial']),$id);
        $this->controller()->estado($this->req(['estado'=>'pagada','medio_pago'=>'Efectivo','monto_pagado'=>15000]),$id);
        $this->trabajador(['ver_agenda','reservar','confirmar','pagar','espera','anular']);Auth::setUser(User::find(3));
        $eventos=$this->controller()->eventos($this->req(['start'=>now()->addDay()->startOfDay()->toIso8601String(),'end'=>now()->addDays(2)->startOfDay()->toIso8601String()]))->getData(true);
        $this->assertTrue($eventos[0]['extendedProps']['reserva']['bloqueada']);$this->assertSame([],$eventos[0]['extendedProps']['reserva']['acciones']);
        try{$this->controller()->estado($this->req(['estado'=>'en_espera']),$id);$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        Auth::setUser(User::find(1));$respuesta=$this->controller()->estado($this->req(['estado'=>'en_atencion']),$id);
        $this->assertSame(route('peluqueria.ficha',$id),$respuesta->getTargetUrl());
        $this->assertSame('en_atencion',DB::table('peluqueria_citas')->find($id)->estado);
    }
    public function test_ficha_muestra_nombre_de_especie_y_no_su_identificador(): void {
        Schema::create('especies_mascotas',function(Blueprint $t){$t->id();$t->string('nombre');});
        DB::table('especies_mascotas')->insert(['id'=>2,'nombre'=>'Felina']);
        $cliente=$this->cliente();DB::table('peluqueria_clientes')->where('id',$cliente)->update(['especie'=>'2']);
        $this->controller()->citaGuardar($this->req($this->reserva($cliente)));$id=DB::table('peluqueria_citas')->value('id');
        $vista=$this->controller()->ficha($id);$this->assertSame('Felina',$vista->getData()['datos']['especie']);
        $this->assertStringContainsString('name="especie" value="Felina"',$vista->render());
        $this->assertStringNotContainsString('name="especie" value="2"',$vista->render());
    }
    public function test_centro_estetico_es_rol_institucional_distinto_del_profesional(): void {
        app(CuentasService::class)->asignarRol(User::find(1),'centro_estetico');
        $this->assertTrue(CuentasService::esTipoValido('centro_estetico'));
        $this->assertTrue(User::find(1)->hasRole('CentroEstetico'));
        $this->assertFalse(User::find(1)->hasRole('Profesional'));
        $this->assertSame('centro_estetico.home',app(CuentasService::class)->rutaEscritorio('centro_estetico'));
        session(['rol_activo'=>'centro_estetico']);
        $permisos=app(\App\Services\PeluqueriaAcceso::class)->permisos(\App\Models\Peluqueria::find(1));
        $this->assertTrue($permisos['personal']);$this->assertArrayNotHasKey('atender',$permisos);
        $html=$this->controller()->index($this->req())->render();$this->assertStringContainsString('Centro estético',$html);$this->assertStringContainsString('/CentroEstetico',$html);
        session(['rol_activo'=>'peluqueria']);
        $permisos=app(\App\Services\PeluqueriaAcceso::class)->permisos(\App\Models\Peluqueria::find(1));
        $this->assertTrue($permisos['atender']);$this->assertArrayNotHasKey('personal',$permisos);
    }
    public function test_personal_de_estetica_tiene_perfil_propio_sin_ser_veterinario(): void {
        $this->trabajador(['ver_agenda','atender']);$usuario=User::find(3);
        $this->assertTrue(app(CuentasService::class)->tieneRol($usuario,'peluqueria'));
        $this->assertTrue(app(CuentasService::class)->tieneRol($usuario,'centro_estetico'));
        $this->assertFalse($usuario->hasRole('Profesional'));
    }
    public function test_el_centro_no_puede_iniciar_la_atencion_del_profesional(): void {
        app(CuentasService::class)->asignarRol(User::find(1),'centro_estetico');
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));$id=DB::table('peluqueria_citas')->value('id');
        $this->prepararAtencion($id,false);session(['rol_activo'=>'centro_estetico']);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->controller()->estado($this->req(['estado'=>'en_atencion']),$id);
    }
    public function test_registrar_centro_no_crea_un_profesional_veterinario_ni_de_estetica(): void {
        DB::table('users')->insert(['id'=>4,'name'=>'Administradora','email'=>'centro@example.test']);$usuario=User::find(4);
        app(CuentasService::class)->asignarRol($usuario,'centro_estetico');Auth::setUser($usuario);
        $this->actingAs($usuario)->get(route('perfil.completar',['tipo'=>'centro_estetico']))->assertOk()->assertSee('Datos de la institución');
        $this->actingAs($usuario)->post(route('perfil.completar.guardar',['tipo'=>'centro_estetico']),['nombre'=>'Centro Nuevo','rut_empresa'=>'12345678-5','razon_social'=>'Empresa Nueva','giro'=>'Estética de mascotas','email_contacto'=>'empresa@example.test','telefono'=>'123456789','direccion'=>'Dirección principal'])->assertRedirect(route('centro_estetico.home'));
        $centro=\App\Models\CentroEstetico::where('id_usuario',4)->firstOrFail();
        $this->assertSame('Empresa Nueva',$centro->razon_social);
        $this->assertFalse(\App\Models\ProfesionalEstetica::where('id_usuario',4)->exists());
        $this->assertFalse(app(CuentasService::class)->tieneRol($usuario,'peluqueria'));
        $this->actingAs($usuario)->get(route('centro_estetico.home'))->assertOk()->assertSee('Centro estético')->assertSee('Sucursales');
    }
    public function test_centro_y_profesional_tienen_identidades_y_rutas_separadas(): void {
        app(CuentasService::class)->asignarRol(User::find(1),'centro_estetico');app(CuentasService::class)->marcarPerfilCompleto(User::find(1),'centro_estetico');
        $this->actingAs(User::find(1))->get(route('cuenta.entrar',['tipo'=>'centro_estetico']))->assertRedirect(route('centro_estetico.home'));
        $this->assertSame('centro_estetico',session('rol_activo'));
        $this->actingAs(User::find(1))->get(route('centro_estetico.configuracion',['seccion'=>'personal']))->assertOk()->assertSee('Personal y permisos');
        app(CuentasService::class)->marcarPerfilCompleto(User::find(1),'peluqueria');
        $this->actingAs(User::find(1))->get(route('cuenta.entrar',['tipo'=>'peluqueria']))->assertRedirect(route('peluqueria.home'));
        $this->actingAs(User::find(1))->get(route('peluqueria.configuracion',['seccion'=>'personal']))->assertForbidden();
        $this->assertNotSame(app(CuentasService::class)->perfil(User::find(1),'peluqueria')->getTable(),app(CuentasService::class)->perfil(User::find(1),'centro_estetico')->getTable());
    }
    public function test_ficha_precarga_esterilizacion_y_conserva_el_dato_de_la_atencion_guardada(): void {
        Schema::create('mascotas',function(Blueprint $t){$t->id();$t->boolean('esterilizado')->nullable();});
        $cliente=$this->cliente();
        DB::table('mascotas')->insert(['id'=>1,'esterilizado'=>true]);
        DB::table('peluqueria_clientes')->where('id',$cliente)->update(['mascota_id'=>1]);
        $this->controller()->agendar($this->req($this->reserva($cliente)));
        $id=DB::table('peluqueria_citas')->value('id');
        foreach ([1=>'si',0=>'no'] as $estado=>$esperado) {
            DB::table('mascotas')->where('id',1)->update(['esterilizado'=>$estado]);
            $this->assertSame($esperado,$this->controller()->ficha($id)->getData()['datos']['esterilizada']);
        }
        DB::table('mascotas')->where('id',1)->update(['esterilizado'=>null]);
        $this->assertSame('desconocido',$this->controller()->ficha($id)->getData()['datos']['esterilizada']);
        DB::table('peluqueria_fichas')->insert(['peluqueria_id'=>1,'cita_id'=>$id,'datos'=>json_encode(['esterilizada'=>'no'])]);
        DB::table('mascotas')->where('id',1)->update(['esterilizado'=>true]);
        $this->assertSame('no',$this->controller()->ficha($id)->getData()['datos']['esterilizada']);
    }
    private function prepararAtencion(int $id,bool $iniciar=true): void {
        $cita=DB::table('peluqueria_citas')->find($id);
        if($cita->estado==='reservada')$this->controller()->estado($this->req(['estado'=>'confirmada','via_confirmacion'=>'Teléfono']),$id);
        if(DB::table('peluqueria_citas')->find($id)->estado==='confirmada')$this->controller()->estado($this->req(['estado'=>'pagada','medio_pago'=>'Efectivo','monto_pagado'=>$cita->valor]),$id);
        if(DB::table('peluqueria_citas')->find($id)->estado==='pagada')$this->controller()->estado($this->req(['estado'=>'en_espera']),$id);
        if($iniciar&&DB::table('peluqueria_citas')->find($id)->estado==='en_espera')$this->controller()->estado($this->req(['estado'=>'en_atencion']),$id);
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
        $this->prepararAtencion($id);$this->controller()->fichaGuardar($this->req($datos),$id);
        $this->assertEquals(10000,DB::table('peluqueria_citas')->find($id)->valor);
        DB::table('peluqueria_procedimientos')->where('id',$procedimiento)->update(['activo'=>0]);
        $datos['accion']='finalizar';$this->prepararAtencion($id);$this->controller()->fichaGuardar($this->req($datos),$id);
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
        $this->prepararAtencion($id);$this->controller()->fichaGuardar($this->req($datos),$id);
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
        $this->assertSame('reservada',$resultado[0]['extendedProps']['reserva']['estado']);
        $this->assertContains('confirmada',$resultado[0]['extendedProps']['reserva']['acciones']);
        app(CuentasService::class)->marcarPerfilCompleto(User::find(1),'peluqueria');
        $this->actingAs(User::find(1))->withSession(['rol_activo'=>'peluqueria'])->get(route('peluqueria.agenda.ver'))->assertOk()->assertSee('peluqueria-calendario');
    }
    public function test_iniciar_desde_la_agenda_abre_ficha_con_mascota_y_servicio_cargados(): void {
        $this->controller()->citaGuardar($this->req($this->reserva($this->cliente())));
        $id=DB::table('peluqueria_citas')->value('id');
        $this->prepararAtencion($id,false);
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
        $this->prepararAtencion($id);
        $this->controller()->fichaGuardar($this->req(['mascota'=>'Mascota 1','especie'=>'Canina','esterilizada'=>'desconocido',
            'servicios'=>[['nombre'=>'Baño','precio'=>15000]],'observaciones'=>'Atención cerrada de prueba','accion'=>'finalizar']),$id);
        $vista=$this->controller()->ficha($id);
        $this->assertSame([$id],$vista->getData()['anteriores']->pluck('id')->all());
        $this->assertStringContainsString('Atención cerrada de prueba',$vista->render());
        $this->assertSame(1,$this->controller()->index($this->req())->getData()['historial']->total());
    }
    public function test_agendamiento_modal_crea_tutor_mascota_y_reserva_en_una_operacion(): void {
        $procedimiento=DB::table('peluqueria_procedimientos')->insertGetId(['peluqueria_id'=>1,'nombre'=>'Corte de uñas','precio'=>5000,'duracion'=>30,'activo'=>1]);
        $datos=['nuevo_cliente'=>1,'rut'=>'12345678-5','tutor'=>'Tutor prueba','telefono'=>'912345678','mascota'=>'Luna','especie'=>'Felina','procedimiento_id'=>$procedimiento,'inicio'=>now()->addDay()->format('Y-m-d').'T12:00'];
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
        $this->prepararAtencion($id);
        $html=$this->controller()->ficha($id)->render();
        foreach(['Alergias a productos de belleza','Comportamiento durante la atención','Análisis visual','Pulgas','Corte de uñas','Guardar y finalizar atención'] as $texto) $this->assertStringContainsString($texto,$html);
        $datos=['mascota'=>'Luna','especie'=>'Felina','edad'=>'2 años','esterilizada'=>'si','alergias'=>'Alergia informada','alergias_productos'=>'Perfumes',
            'comportamientos'=>['Ansioso/a','Requiere bozal'],'hallazgos'=>['Pulgas','Irritaciones en piel'],'hallazgos_otro'=>'Observar zona dorsal',
            'servicios'=>[['procedimiento_id'=>$bano,'nombre'=>'Baño','precio'=>1],['procedimiento_id'=>$unas,'nombre'=>'Corte de uñas','precio'=>1]],'observaciones_servicios'=>'Baño suave','productos_utilizados'=>'Champú indicado por tutor','observaciones'=>'Sin incidentes','accion'=>'guardar','total'=>1];
        $this->prepararAtencion($id);$this->controller()->fichaGuardar($this->req($datos),$id);
        $guardada=json_decode(DB::table('peluqueria_fichas')->where('cita_id',$id)->value('datos'),true);
        $this->assertSame(15000,$guardada['total']);
        $this->assertSame(['Pulgas','Irritaciones en piel'],$guardada['hallazgos']);
        $this->assertSame('Perfumes',$guardada['alergias_productos']);
        $this->assertSame('en_atencion',DB::table('peluqueria_citas')->find($id)->estado);
        unset($datos['comportamientos'],$datos['hallazgos']);$datos['accion']='finalizar';
        $this->prepararAtencion($id);$this->controller()->fichaGuardar($this->req($datos),$id);
        $this->assertSame(1,DB::table('peluqueria_fichas')->count());
        $guardada=json_decode(DB::table('peluqueria_fichas')->value('datos'),true);
        $this->assertSame([],$guardada['hallazgos']);$this->assertSame([],$guardada['comportamientos']);
        $cita=DB::table('peluqueria_citas')->find($id);$this->assertSame('finalizada',$cita->estado);$this->assertNotNull($cita->finalizada_en);
        $this->assertStringContainsString('Corte de uñas',$this->controller()->index($this->req())->render());
        $this->assertStringNotContainsString('Guardar y finalizar atención',$this->controller()->ficha($id)->render());
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->prepararAtencion($id);$this->controller()->fichaGuardar($this->req($datos),$id);
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
        $this->assertInstanceOf(\App\Models\ProfesionalEstetica::class,app(CuentasService::class)->perfil(User::find(1),'peluqueria'));
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
        $this->controller()->clienteGuardar($this->req(['rut'=>'12345678-5','tutor'=>'Ana','telefono'=>'555','mascota'=>'Luna','especie'=>'Felina']));$this->cliente(2);
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
        $this->prepararAtencion($id);
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
