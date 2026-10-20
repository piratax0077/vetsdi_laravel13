<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Http\Controllers\ArancelesVeterinariosController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth,DB,Schema,Blade};
use Illuminate\Validation\ValidationException;
class ArancelesVeterinariosTest extends TestCase
{
    protected function setUp(): void {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('email');$t->timestamps();});
        Schema::create('profesionales',function(Blueprint $t){$t->id();$t->unsignedBigInteger('id_usuario');$t->timestamps();});
        (require database_path('migrations/2026_10_19_000004_create_aranceles_profesionales_table.php'))->up();
        DB::table('users')->insert([['id'=>1,'name'=>'Uno','email'=>'uno@example.test'],['id'=>2,'name'=>'Dos','email'=>'dos@example.test']]);
        DB::table('profesionales')->insert([['id'=>10,'id_usuario'=>1],['id'=>20,'id_usuario'=>2]]);$this->usuario(1);
    }
    private function usuario(int $id,bool $permitido=true): void {
        $user=\Mockery::mock(User::class)->makePartial();$user->setRawAttributes(User::find($id)->getAttributes());$user->shouldReceive('hasRole')->with('Profesional')->andReturn($permitido);Auth::setUser($user);
    }
    private function controller(): ArancelesVeterinariosController{return app(ArancelesVeterinariosController::class);}
    private function guardar(array $data): array{return $this->controller()->guardar(Request::create('/','POST',$data))->getData(true);}
    private function item(array $overrides=[]): array{return array_replace(['codigo'=>'CIR-001','nombre'=>'Ovariectomía','activo'=>true,'variaPorTamano'=>false,'valorNeto'=>70000,'afectoIva'=>false,'costoInterno'=>null,'preciosTamano'=>[],'tipo'=>'Cirugía','duracionMin'=>60,'requiereAnestesia'=>true,'insumos'=>''],$overrides);}
    private function crear(array $item=[],string $catalogo='procedimientos'): array{return $this->guardar(['accion'=>'crear','catalogo'=>$catalogo,'datos'=>$this->item($item)]);}
    public function test_new_account_has_no_imposed_prices_and_catalog_has_unique_codes(): void {
        $estado=$this->controller()->listar()->getData(true);$this->assertSame([],$estado['procedimientos']);$this->assertSame(0,DB::table('aranceles_profesionales')->count());
        $catalogo=config('catalogo_prestaciones_veterinarias');$this->assertGreaterThan(90,count($catalogo));$this->assertSame(count($catalogo),count(array_unique(array_column($catalogo,'codigo'))));
        foreach($catalogo as $item){$this->assertArrayNotHasKey('valorNeto',$item);$this->assertContains($item['catalogo'],['procedimientos','examenes']);}
    }
    public function test_prices_are_saved_per_professional_and_editable_after_reload(): void {
        $uno=$this->crear();$this->usuario(2);$this->assertSame([],$this->controller()->listar()->getData(true)['procedimientos']);$this->crear(['valorNeto'=>90000]);
        $this->usuario(1);$this->guardar(['accion'=>'editar','catalogo'=>'procedimientos','id'=>$uno['id'],'datos'=>$this->item(['valorNeto'=>80000])]);
        $this->assertSame(80000,$this->controller()->listar()->getData(true)['procedimientos'][0]['valorNeto']);
        $this->usuario(2);$this->assertSame(90000,$this->controller()->listar()->getData(true)['procedimientos'][0]['valorNeto']);
    }
    public function test_duplicate_code_across_catalogs_and_negative_prices_are_rejected(): void {
        $this->crear();
        foreach([['catalogo'=>'examenes','datos'=>$this->item()],['catalogo'=>'procedimientos','datos'=>$this->item(['codigo'=>'OTRO','valorNeto'=>-1])]] as $data){
            try{$this->guardar(['accion'=>'crear']+$data);$this->fail();}catch(ValidationException $e){$this->assertNotEmpty($e->errors());}
        }
        $this->assertCount(1,$this->controller()->listar()->getData(true)['procedimientos']);
    }
    public function test_another_professionals_item_cannot_be_changed(): void {
        $item=$this->crear();$this->usuario(2);$this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->guardar(['accion'=>'estado','catalogo'=>'procedimientos','id'=>$item['id'],'activo'=>false]);
    }
    public function test_prices_by_size_and_active_state_survive_reload(): void {
        $item=$this->crear(['variaPorTamano'=>true,'valorNeto'=>0,'preciosTamano'=>[['tamanoId'=>'mini','valorNeto'=>50000,'afectoIva'=>false,'activo'=>true,'costoInterno'=>null]]]);
        $this->guardar(['accion'=>'estado','catalogo'=>'procedimientos','id'=>$item['id'],'activo'=>false]);
        $saved=$this->controller()->listar()->getData(true)['procedimientos'][0];$this->assertFalse($saved['activo']);$this->assertSame(50000,$saved['preciosTamano'][0]['valorNeto']);
    }
    public function test_browser_import_is_atomic_and_never_replaces_saved_prices(): void {
        $estado=ArancelesVeterinariosController::inicial();$estado['procedimientos']=[$this->item(['id'=>'viejo'])];
        $this->guardar(['accion'=>'importar','datos'=>$estado]);
        $this->assertCount(1,$this->controller()->listar()->getData(true)['procedimientos']);
        $this->expectException(ValidationException::class);$this->guardar(['accion'=>'importar','datos'=>$estado]);
    }
    public function test_invalid_import_leaves_no_partial_data(): void {
        $estado=ArancelesVeterinariosController::inicial();$estado['procedimientos']=[$this->item(),$this->item(['codigo'=>'OTRO','valorNeto'=>-1])];
        try{$this->guardar(['accion'=>'importar','datos'=>$estado]);$this->fail();}catch(ValidationException $e){$this->assertSame(0,DB::table('aranceles_profesionales')->count());}
    }
    public function test_account_without_professional_role_cannot_read_prices(): void {
        $this->usuario(1,false);$this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);$this->controller()->listar();
    }
    public function test_configuration_round_trip_and_catalog_screen_render(): void {
        $config=ArancelesVeterinariosController::inicial()['config'];$config['parametros']['iva']=0;$this->guardar(['accion'=>'config','datos'=>$config]);
        $this->assertSame(0,$this->controller()->listar()->getData(true)['config']['parametros']['iva']);
        $source=file_get_contents(resource_path('views/app/profesional/aranceles_veterinaria_general.blade.php'));$source=str_replace('@endsection','',preg_replace('/@(?:extends|section)\([^\n]*\)/','',$source));
        $html=Blade::render($source);$this->assertStringContainsString('arvModalCatalogo',$html);$this->assertStringContainsString('arancelesVeterinariosConfig',$html);$this->assertStringContainsString('Ovariectom',$html);
    }
}
