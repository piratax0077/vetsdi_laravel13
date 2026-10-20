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
        DB::table('users')->insert([['id'=>1,'name'=>'Pelu Uno','email'=>'uno@example.test'],['id'=>2,'name'=>'Pelu Dos','email'=>'dos@example.test']]);
        foreach([1,2] as $id){DB::table('peluquerias')->insert(['id'=>$id,'id_usuario'=>$id,'nombre'=>'Local '.$id,'telefono'=>'123','direccion'=>'Calle']);app(CuentasService::class)->asignarRol(User::find($id),'peluqueria');}
        Auth::setUser(User::find(1));
        view()->share('errors',new \Illuminate\Support\ViewErrorBag());
    }
    private function req(array $data=[]): Request{return Request::create('/Peluqueria','POST',$data);}
    private function controller(): PeluqueriaController{return app(PeluqueriaController::class);}
    private function cliente(int $owner=1): int{return DB::table('peluqueria_clientes')->insertGetId(['peluqueria_id'=>$owner,'tutor'=>'Tutor '.$owner,'telefono'=>'123','mascota'=>'Mascota '.$owner,'especie'=>'Canina']);}
    private function reserva(int $id,string $time='10:00'): array {return ['cliente_id'=>$id,'inicio'=>now()->addDay()->format('Y-m-d').'T'.$time,'duracion'=>60,'servicio'=>'Baño','valor'=>15000];}
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
