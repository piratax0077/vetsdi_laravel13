<?php

namespace Tests\Feature;

use App\Support\EscritorioVeterinario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Schema};
use Tests\TestCase;

class EscritorioVeterinarioHeaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        Schema::create('vet_establecimientos',function(Blueprint $t){$t->id();$t->integer('id_institucion');$t->string('tipo');});
        DB::table('vet_establecimientos')->insert([['id_institucion'=>10,'tipo'=>'centro'],['id_institucion'=>20,'tipo'=>'hospital']]);
    }

    private function request(string $ruta,array $query=[]): Request
    {
        $r=Request::create('/prueba','GET',$query);$r->setLaravelSession($this->app['session.store']);
        $route=\Illuminate\Support\Facades\Route::getRoutes()->getByName($ruta);
        $this->assertNotNull($route);$r->setRouteResolver(fn()=>$route);$this->app->instance('request',$r);
        return $r;
    }

    public function test_house_is_rendered_on_all_center_routes_including_configuration_and_home(): void
    {
        foreach(['adm_cm.home','adm_cm.configuracion','adm_cm.pacientes','adm_cm.area_comercial','vet.operacion.index'] as $ruta){
            $this->request($ruta);
            $html=view('template.partials.escritorio_veterinario_header',['forzarEscritorioVeterinario'=>true,'institucion'=>(object)['id'=>10]])->render();
            $this->assertStringContainsString('aria-label="Volver al escritorio"',$html);
            $this->assertStringContainsString('feather icon-home',$html);
            $this->assertStringContainsString(route('adm_cm.home',['id_institucion'=>10]),$html);
        }
    }

    public function test_destination_respects_hospital_type_and_active_branch(): void
    {
        $r=$this->request('vet.operacion.index');$r->session()->put('admin_center_context','admin:20:200');
        $url=EscritorioVeterinario::url($r);
        $this->assertSame(route('adm_hospital.home',['id_institucion'=>20,'contexto'=>'admin:20:200']),$url);
        $r=$this->request('adm_hospital.configuracion',['id_institucion'=>10]);
        $this->assertStringStartsWith(route('adm_hospital.home'),EscritorioVeterinario::url($r));
    }

    public function test_shared_accounting_header_uses_clinic_session_but_not_other_active_roles(): void
    {
        $r=$this->request('contabilidad.secciones_contabilidad.ingresos');$r->session()->put('rol_activo','clinica');
        $this->assertStringContainsString('class="sdi-home-header"',view('template.partials.escritorio_veterinario_header')->render());
        $r->session()->put('rol_activo','profesional');
        $this->assertStringNotContainsString('class="sdi-home-header"',view('template.partials.escritorio_veterinario_header')->render());
    }

    public function test_house_is_outside_collapsible_header_and_shared_by_supporting_layouts(): void
    {
        foreach(['adm_cm','contabilidad','enfermera_tens','administracion','hospitales','asistente','profesional'] as $plantilla){
            $source=file_get_contents(resource_path('views/template/'.$plantilla.'/header.blade.php'));
            $this->assertLessThan(strpos($source,'collapse navbar-collapse'),strpos($source,"@include('template.partials.escritorio_veterinario_header'"));
        }
        $this->assertStringNotContainsString('boton_escritorio_encabezado',file_get_contents(resource_path('views/template/adm_cm/header.blade.php')));
    }
}
