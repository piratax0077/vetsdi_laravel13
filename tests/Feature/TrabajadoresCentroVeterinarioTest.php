<?php

namespace Tests\Feature;

use App\Mail\MensajeTrabajador;
use App\Models\{Instituciones,User};
use App\Services\TrabajadoresCentroVeterinario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Mail,Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TrabajadoresCentroVeterinarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','mail.default'=>'array','mail.mailers.array.transport'=>'array']);DB::purge('sqlite');
        foreach(['regiones'=>['nombre'],'ciudades'=>['nombre','id_region'],'users'=>['name','email'],'instituciones'=>['nombre','id_usuario','id_responsable','id_lugar_atencion'],'admin_inst_serv'=>['id_admin'],'contrato_dependiente'=>['id_empleado','tipo_empleado','estado','id_institucion'],'lugares_atencion'=>['nombre'],'sucursal'=>['id_institucion','id_lugar_atencion','estado'],'vet_operacion_auditoria'=>['id_institucion','id_usuario','tabla','registro_id','antes','despues']] as $tabla=>$campos)
            Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
        DB::table('users')->insert(['id'=>1,'name'=>'Admin','email'=>'admin@example.test']);Auth::setUser(User::find(1));
        DB::table('instituciones')->insert([['id'=>10,'nombre'=>'Centro Uno','id_usuario'=>1,'id_lugar_atencion'=>100],['id'=>20,'nombre'=>'Hospital ajeno','id_usuario'=>2,'id_lugar_atencion'=>200]]);
        DB::table('lugares_atencion')->insert([['id'=>100,'nombre'=>'Matriz'],['id'=>101,'nombre'=>'Sucursal'],['id'=>200,'nombre'=>'Ajena']]);
        DB::table('sucursal')->insert(['id_institucion'=>10,'id_lugar_atencion'=>101,'estado'=>1]);
        $this->migracion()->up();
        (require database_path('migrations/2026_11_03_000002_add_ubicacion_to_vet_trabajadores.php'))->up();
        DB::table('regiones')->insert([['id'=>1,'nombre'=>'Región Uno'],['id'=>2,'nombre'=>'Región Dos']]);
        DB::table('ciudades')->insert([['id'=>1,'nombre'=>'Comuna Uno','id_region'=>1],['id'=>2,'nombre'=>'Comuna Dos','id_region'=>2]]);
    }
    private function migracion(){return require database_path('migrations/2026_10_31_000001_otros_trabajadores_veterinarios.php');}
    private function servicio(): TrabajadoresCentroVeterinario{return app(TrabajadoresCentroVeterinario::class);}
    private function centro(): Instituciones{return Instituciones::findOrFail(10);}
    private function req(array $d=[]): Request{return Request::create('/','POST',$d+['id_institucion'=>10]);}
    private function datos(array $d=[]): array{return $d+['rut'=>'12.345.678-5','nombres'=>'Trabajadora','apellido_uno'=>'Prueba','categoria_id'=>DB::table('vet_categorias_trabajadores')->where('id_institucion',10)->where('clave','limpieza')->value('id'),'email'=>'trabajadora@example.test','activo'=>1,'lugares'=>[100,101],'numero_cuenta'=>'00123456789012345'];}
    private function crear(array $d=[]): int{return $this->servicio()->guardar($this->req($this->datos($d)),$this->centro());}
    private function invalido(callable $fn,string $campo): void
    {try{$fn();$this->fail('Debe rechazar la operación.');}catch(ValidationException $e){$this->assertArrayHasKey($campo,$e->errors());}}

    public function test_rut_de_captura_rechaza_digito_incorrecto_y_acepta_formato_y_k_minuscula(): void
    {
        $this->invalido(fn()=>$this->crear(['rut'=>'18.564.235-K']),'rut');
        $id=$this->crear(['rut'=>'18.564.235-6']);
        $this->assertSame('18564235-6',DB::table('vet_trabajadores')->where('id',$id)->value('rut'));
        $id=$this->crear(['rut'=>'12.345.670-k']);
        $this->assertSame('12345670-K',DB::table('vet_trabajadores')->where('id',$id)->value('rut'));
    }

    public function test_region_y_comuna_se_guardan_cargan_y_validan_al_editar(): void
    {
        $id=$this->crear(['region_id'=>1,'comuna_id'=>1,'direccion'=>'Calle 123']);
        $detalle=$this->servicio()->detalle($this->centro(),$id)['trabajador'];
        $this->assertEquals(1,$detalle->region_id);$this->assertEquals(1,$detalle->comuna_id);
        $this->servicio()->guardar($this->req($this->datos(['id'=>$id,'region_id'=>2,'comuna_id'=>2])),$this->centro());
        $this->assertEquals(2,DB::table('vet_trabajadores')->where('id',$id)->value('comuna_id'));
        $this->invalido(fn()=>$this->servicio()->guardar($this->req($this->datos(['id'=>$id,'region_id'=>1,'comuna_id'=>2])),$this->centro()),'comuna_id');
        $this->assertEquals(2,DB::table('vet_trabajadores')->where('id',$id)->value('region_id'));
        $this->servicio()->guardar($this->req($this->datos(['id'=>$id,'region_id'=>''])),$this->centro());
        $this->assertNull(DB::table('vet_trabajadores')->where('id',$id)->value('comuna_id'));
    }

    public function test_migration_creates_default_categories_and_can_be_reversed_without_touching_existing_data(): void
    {
        $this->assertSame(12,DB::table('vet_categorias_trabajadores')->count());$this->migracion()->down();
        $this->assertFalse(Schema::hasTable('vet_trabajadores'));$this->assertSame(2,DB::table('instituciones')->count());
        $this->migracion()->up();$this->assertSame(12,DB::table('vet_categorias_trabajadores')->count());
    }

    public function test_worker_create_edit_categories_branches_and_bank_account_are_persisted(): void
    {
        $id=$this->crear();$worker=DB::table('vet_trabajadores')->find($id);
        $this->assertSame('12345678-5',$worker->rut);$this->assertSame('00123456789012345',$worker->numero_cuenta);
        $this->assertSame(2,DB::table('vet_trabajador_lugares')->where('trabajador_id',$id)->count());
        $this->servicio()->guardar($this->req($this->datos(['id'=>$id,'nombres'=>'Nombre editado','lugares'=>[101]])),$this->centro());
        $lista=$this->servicio()->datos($this->centro());$this->assertSame('Nombre editado Prueba',$lista['trabajadores']->first()->nombre_completo);
        $this->assertSame(['Sucursal'],$lista['trabajadores']->first()->sucursales);$this->assertSame(1,DB::table('vet_trabajadores')->count());
        $this->assertCount(0,$this->servicio()->datos($this->centro(),[100])['trabajadores']);
        $this->assertCount(2,DB::table('vet_operacion_auditoria')->get());
    }

    public function test_duplicates_invalid_rut_and_foreign_branches_are_rejected(): void
    {
        $this->crear();$this->invalido(fn()=>$this->crear(),'rut');
        $this->invalido(fn()=>$this->crear(['rut'=>'11111111-2']),'rut');
        $this->invalido(fn()=>$this->crear(['rut'=>'11111111-1','lugares'=>[200]]),'lugares');
        $this->assertSame(1,DB::table('vet_trabajadores')->count());
    }

    public function test_categories_can_be_created_renamed_and_disabled_but_clinical_tabs_stay_separate(): void
    {
        $s=$this->servicio();$i=$this->centro();
        $id=$s->categoria($this->req(['nombre'=>' Bodega general ','activa'=>1]),$i);
        $this->invalido(fn()=>$s->categoria($this->req(['nombre'=>'Bodega general','activa'=>1]),$i),'nombre');
        $this->invalido(fn()=>$s->categoria($this->req(['nombre'=>'Asistentes','activa'=>1]),$i),'nombre');
        $s->categoria($this->req(['id'=>$id,'nombre'=>'Apoyo de bodega','activa'=>1]),$i);$this->crear(['categoria_id'=>$id]);
        $this->invalido(fn()=>$s->categoria($this->req(['id'=>$id,'nombre'=>'Apoyo de bodega','activa'=>0]),$i),'activa');
        $this->assertSame('Apoyo de bodega',DB::table('vet_categorias_trabajadores')->find($id)->nombre);
    }

    public function test_contract_create_edit_finalize_and_worker_unlink_preserve_history(): void
    {
        $id=$this->crear();$s=$this->servicio();$i=$this->centro();
        $d=['trabajador_id'=>$id,'tipo'=>'indefinido','fecha_inicio'=>today()->subMonth()->toDateString(),'remuneracion'=>600000,'horas_semanales'=>40];
        $contrato=$s->contrato($this->req($d),$i);
        $this->invalido(fn()=>$s->contrato($this->req($d),$i),'trabajador_id');
        $this->invalido(fn()=>$s->estado($this->req(['activo'=>0]),$i,$id),'activo');
        $s->contrato($this->req(['id'=>$contrato,'remuneracion'=>650000]+$d),$i);
        $this->assertEquals(650000,DB::table('vet_contratos_trabajadores')->find($contrato)->remuneracion);
        $s->terminar($this->req(['fecha_termino'=>today()->toDateString()]),$i,$contrato);
        $s->estado($this->req(['activo'=>0]),$i,$id);
        $this->assertEquals(0,DB::table('vet_trabajadores')->find($id)->activo);$this->assertSame('terminado',DB::table('vet_contratos_trabajadores')->find($contrato)->estado);
        $s->estado($this->req(['activo'=>1]),$i,$id);$this->assertSame(1,DB::table('vet_trabajadores')->count());
        $this->invalido(fn()=>$s->contrato($this->req($d),$i),'fecha_inicio');
    }

    public function test_fixed_term_contract_requires_end_and_closed_contract_cannot_be_edited(): void
    {
        $id=$this->crear();$s=$this->servicio();$i=$this->centro();$d=['trabajador_id'=>$id,'tipo'=>'plazo_fijo','fecha_inicio'=>today()->subMonth()->toDateString(),'remuneracion'=>100000];
        $this->invalido(fn()=>$s->contrato($this->req($d),$i),'fecha_fin');
        $contrato=$s->contrato($this->req($d+['fecha_fin'=>today()->toDateString()]),$i);
        $this->invalido(fn()=>$s->terminar($this->req(['fecha_termino'=>today()->subYear()->toDateString()]),$i,$contrato),'fecha_termino');
        $s->terminar($this->req(['fecha_termino'=>today()->toDateString()]),$i,$contrato);
        $this->invalido(fn()=>$s->contrato($this->req(['id'=>$contrato]+$d+['fecha_fin'=>today()->toDateString()]),$i),'id');
    }

    public function test_messages_have_history_and_repeated_submits_do_not_send_twice(): void
    {
        Mail::fake();$id=$this->crear();$r=$this->req(['token'=>(string)Str::uuid(),'asunto'=>'Turno de prueba','mensaje'=>'Texto de prueba']);
        $m=$this->servicio()->mensaje($r,$this->centro(),$id);$repetido=$this->servicio()->mensaje($r,$this->centro(),$id);
        $this->assertSame($m->id,$repetido->id);$this->assertSame('registrado',$m->estado);
        Mail::assertSent(MensajeTrabajador::class,1);Mail::assertSent(MensajeTrabajador::class,fn($m)=>$m->hasTo('trabajadora@example.test'));
        $this->assertCount(1,$this->servicio()->detalle($this->centro(),$id)['mensajes']);
    }

    public function test_message_without_email_is_rejected_and_mail_transport_failure_is_recorded(): void
    {
        $id=$this->crear(['email'=>null]);$d=['token'=>(string)Str::uuid(),'asunto'=>'Asunto','mensaje'=>'Contenido'];
        $this->invalido(fn()=>$this->servicio()->mensaje($this->req($d),$this->centro(),$id),'mensaje');
        $this->servicio()->guardar($this->req($this->datos(['id'=>$id])),$this->centro());
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Fallo simulado'));
        $this->assertSame('fallido',$this->servicio()->mensaje($this->req($d),$this->centro(),$id)->estado);
    }

    public function test_foreign_worker_category_and_contract_cannot_be_accessed(): void
    {
        $id=$this->crear();$foreign=Instituciones::find(20);$s=$this->servicio();
        foreach([fn()=>$s->detalle($foreign,$id),fn()=>$s->estado($this->req(['activo'=>0]),$foreign,$id)] as $fn){
            try{$fn();$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(404,$e->getStatusCode());}
        }
        $cat=DB::table('vet_categorias_trabajadores')->where('id_institucion',20)->value('id');
        try{$this->crear(['rut'=>'11111111-1','categoria_id'=>$cat]);$this->fail();}catch(\Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(404,$e->getStatusCode());}
        $this->assertSame(1,DB::table('vet_trabajadores')->count());
    }

    public function test_worker_and_branch_assignments_roll_back_if_audit_fails(): void
    {
        DB::statement("CREATE TRIGGER fallo BEFORE INSERT ON vet_operacion_auditoria BEGIN SELECT RAISE(FAIL,'Fallo simulado'); END");
        try{$this->crear();$this->fail();}catch(\Illuminate\Database\QueryException $e){$this->assertStringContainsString('Fallo simulado',$e->getMessage());}
        $this->assertSame(0,DB::table('vet_trabajadores')->count());$this->assertSame(0,DB::table('vet_trabajador_lugares')->count());
    }

    public function test_worker_and_contract_tables_and_modals_render_saved_data_escaped(): void
    {
        $this->crear(['nombres'=>'Nombre <script>prueba</script>']);$datos=['otrosTrabajadores'=>$this->servicio()->datos($this->centro()),'institucion'=>$this->centro()];
        $html=view('app.adm_cm.partials.otros_trabajadores_tabla',$datos)->render();
        $this->assertStringContainsString('Nombre &lt;script&gt;prueba&lt;/script&gt;',$html);$this->assertStringNotContainsString('Nombre <script>prueba</script>',$html);
        $this->assertStringContainsString('Matriz',$html);$this->assertStringContainsString('Sucursal',$html);
        $modales=view('app.adm_cm.partials.otros_trabajadores_modales',$datos)->render();
        $this->assertStringContainsString('data-otro-form="guardar"',$modales);$this->assertStringContainsString('data-otro-form="mensaje"',$modales);
        preg_match_all('/<\/?form\b[^>]*>/',$modales,$forms);$nivel=0;
        foreach($forms[0] as $f){$nivel+=str_starts_with($f,'</')?-1:1;$this->assertLessThanOrEqual(1,$nivel);$this->assertGreaterThanOrEqual(0,$nivel);}$this->assertSame(0,$nivel);
    }

    public function test_http_routes_require_role_and_establishment_authorization(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\PerfilPendiente::class);
        $user=\Mockery::mock(User::class)->makePartial();$user->setRawAttributes(['id'=>1,'name'=>'Admin','email'=>'admin@example.test']);$user->shouldReceive('hasAnyRole')->andReturn(false);
        $this->actingAs($user)->postJson(route('vet.trabajadores.guardar'),$this->datos()+['id_institucion'=>10])->assertForbidden();
        $permitido=\Mockery::mock(User::class)->makePartial();$permitido->setRawAttributes(['id'=>1,'name'=>'Admin','email'=>'admin@example.test']);$permitido->shouldReceive('hasAnyRole')->andReturn(true);
        $this->actingAs($permitido)->postJson(route('vet.trabajadores.guardar'),$this->datos()+['id_institucion'=>10])->assertOk()->assertJsonPath('estado',1);
        $this->actingAs($permitido)->postJson(route('vet.trabajadores.guardar'),$this->datos()+['id_institucion'=>20])->assertForbidden();
    }

    public function test_a_future_contract_can_be_cancelled_without_trapping_the_worker(): void
    {
        $id=$this->crear();$s=$this->servicio();$i=$this->centro();
        $d=['trabajador_id'=>$id,'tipo'=>'indefinido','fecha_inicio'=>today()->addMonth()->toDateString(),'remuneracion'=>100000];
        $contrato=$s->contrato($this->req($d),$i);$s->terminar($this->req(['fecha_termino'=>today()->toDateString()]),$i,$contrato);
        $this->assertSame('anulado',DB::table('vet_contratos_trabajadores')->find($contrato)->estado);
        $s->estado($this->req(['activo'=>0]),$i,$id);$this->assertEquals(0,DB::table('vet_trabajadores')->find($id)->activo);
    }
}
