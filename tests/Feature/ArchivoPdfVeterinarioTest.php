<?php
namespace Tests\Feature;

use App\Http\Controllers\ArchivoPdfVeterinarioController;
use App\Http\Middleware\ArchivarPdfVeterinario;
use App\Models\{DocumentoPdfVeterinario,User};
use App\Services\ArchivoPdfVeterinario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\{Auth,DB,Mail,Schema,Storage};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ArchivoPdfVeterinarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        foreach(['users'=>['name','email'],'pacientes'=>['id_usuario','nombres','email','telefono_uno','telefono_dos'],
            'mascotas'=>['id_responsable','nombre'],'profesionales'=>['id_usuario'],
            'instituciones'=>['id_usuario','id_responsable','id_lugar_atencion','nombre'],
            'admin_inst_serv'=>['id_admin'],'contrato_dependiente'=>['id_empleado','tipo_empleado','estado','id_institucion'],
            'fichas_atenciones'=>['id_mascota','id_paciente','id_profesional','id_institucion','id_lugar_atencion']] as $tabla=>$campos)Schema::create($tabla,function(Blueprint $t)use($campos){$t->id();foreach($campos as $c)$t->text($c)->nullable();$t->timestamps();});
        (require database_path('migrations/2026_11_01_000006_archivo_pdf_veterinario.php'))->up();
        DB::table('users')->insert([['id'=>1,'name'=>'Tutora','email'=>'tutora@example.test'],['id'=>2,'name'=>'Ajeno','email'=>'otro@example.test'],['id'=>3,'name'=>'Administración','email'=>'admin@example.test']]);
        DB::table('pacientes')->insert([['id'=>9,'id_usuario'=>1,'nombres'=>'Tutora','email'=>'tutora@example.test','telefono_uno'=>'912345678'],['id'=>20,'id_usuario'=>2,'nombres'=>'Otro','email'=>'otro@example.test','telefono_uno'=>null]]);
        DB::table('mascotas')->insert([['id'=>4,'id_responsable'=>9,'nombre'=>'Luna'],['id'=>5,'id_responsable'=>20,'nombre'=>'Otra']]);
        DB::table('instituciones')->insert(['id'=>10,'id_usuario'=>3,'id_lugar_atencion'=>100,'nombre'=>'Centro']);
        DB::table('fichas_atenciones')->insert(['id'=>10,'id_mascota'=>4,'id_paciente'=>9,'id_institucion'=>10,'id_lugar_atencion'=>100]);
        Auth::setUser(User::find(1));Storage::fake('local');Mail::fake();
    }
    private function servicio(){return app(ArchivoPdfVeterinario::class);}
    private function controller(){return app(ArchivoPdfVeterinarioController::class);}
    private function req(array $datos=[]): Request
    {
        $r=Request::create('http://localhost/pdf','GET',$datos+['id_ficha_atencion'=>10]);
        $route=new Route('GET','pdf',fn()=>null);$route->name('pdf.informe_medico');$route->bind($r);$r->setRouteResolver(fn()=>$route);return $r;
    }
    private function documento(): DocumentoPdfVeterinario
    {
        return $this->servicio()->registrar($this->req(),response('%PDF-1.4 prueba',200,['Content-Type'=>'application/pdf','Content-Disposition'=>'inline; filename="informe.pdf"']));
    }
    public function test_pdf_se_archiva_en_privado_y_se_vincula_a_mascota_y_centro_sin_duplicar(): void
    {
        $doc=$this->documento();$otra=$this->documento();
        $this->assertSame($doc->id,$otra->id);$this->assertSame(1,DocumentoPdfVeterinario::count());
        $this->assertEquals(4,$doc->id_mascota);$this->assertEquals(10,$doc->id_institucion);
        Storage::disk('local')->assertExists($doc->ruta);
        $this->assertSame('%PDF-1.4 prueba',Storage::disk('local')->get($doc->ruta));
        $this->assertSame(200,$this->controller()->ver($doc,$this->servicio())->getStatusCode());
        $html=$this->controller()->detalle($doc,$this->servicio())->with('errors',new \Illuminate\Support\ViewErrorBag())->render();
        $this->assertStringContainsString('Preparar WhatsApp',$html);
        $this->assertStringContainsString('informe.pdf',$html);
    }
    public function test_historial_aislado_y_tutor_ajeno_no_puede_ver_preparar_ni_enviar(): void
    {
        $doc=$this->documento();
        $vista=$this->controller()->index(Request::create('/','GET',['id_mascota'=>4]),$this->servicio());
        $this->assertSame(1,$vista->getData()['documentos']->total());
        Auth::setUser(User::find(2));
        foreach(['ver','preparar','enviar'] as $metodo){try{$this->controller()->{$metodo}($doc,$this->servicio());$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}}
        Mail::assertNothingSent();
    }
    public function test_whatsapp_se_prepara_sin_enviar_y_el_enlace_expira_y_se_invalida_al_traspasar(): void
    {
        $doc=$this->documento();$datos=$this->controller()->preparar($doc,$this->servicio())->getData(true);
        $this->assertStringStartsWith('https://wa.me/56912345678?',$datos['whatsapp']);
        $this->assertSame(48,$datos['vence_en_horas']);Mail::assertNothingSent();
        $this->assertSame(200,$this->controller()->compartido(Request::create($datos['url']),$doc)->getStatusCode());
        DB::table('mascotas')->where('id',4)->update(['id_responsable'=>20]);
        try{$this->controller()->compartido(Request::create($datos['url']),$doc);$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}
        DB::table('mascotas')->where('id',4)->update(['id_responsable'=>9]);$this->travel(49)->hours();
        try{$this->controller()->compartido(Request::create($datos['url']),$doc);$this->fail();}catch(HttpException $e){$this->assertSame(403,$e->getStatusCode());}
    }
    public function test_correo_adjunta_pdf_guardado_solo_al_tutor_actual(): void
    {
        $doc=$this->documento();$this->controller()->enviar($doc,$this->servicio());
        Mail::assertSent(\App\Mail\CorreoGenerico::class,fn($mail)=>$mail->hasTo('tutora@example.test') && $mail->hasAttachedData('%PDF-1.4 prueba','informe.pdf',['mime'=>'application/pdf']));
    }
    public function test_reporte_interno_se_ve_en_centro_y_no_se_comparte_con_pacientes(): void
    {
        Auth::setUser(User::find(3));
        $doc=$this->servicio()->registrar($this->req(),response('%PDF-1.4 interno'),['id_institucion'=>10]);
        $this->assertNull($doc->id_mascota);
        $this->assertSame(1,$this->controller()->index(Request::create('/','GET',['id_institucion'=>10]),$this->servicio())->getData()['documentos']->total());
        try{$this->controller()->preparar($doc,$this->servicio());$this->fail();}catch(HttpException $e){$this->assertSame(422,$e->getStatusCode());}
        Mail::assertNothingSent();
    }
    public function test_middleware_archiva_descarga_pdf_y_agrega_identificador(): void
    {
        $r=$this->req();$respuesta=app(ArchivarPdfVeterinario::class)->handle($r,fn()=>response('%PDF-1.4 archivo'));
        $this->assertNotNull($respuesta->headers->get('X-Documento-Veterinario'));
        $this->assertSame(1,DocumentoPdfVeterinario::count());
    }
    public function test_enlace_compartido_adulterado_es_rechazado(): void
    {
        $doc=$this->documento();$datos=$this->controller()->preparar($doc,$this->servicio())->getData(true);
        $this->expectException(HttpException::class);
        $this->controller()->compartido(Request::create($datos['url'].'&tutor=20'),$doc);
    }
    public function test_pdf_generado_como_url_json_tambien_queda_archivado(): void
    {
        $anterior=public_path();$temporal=storage_path('framework/testing/pdf-json-'.uniqid());
        $this->app->usePublicPath($temporal);
        try{
            \Illuminate\Support\Facades\File::ensureDirectoryExists($temporal.'/reportes');
            file_put_contents($temporal.'/reportes/informe.pdf','%PDF-1.4 informe');
            $r=$this->req();
            $respuesta=app(ArchivarPdfVeterinario::class)->handle($r,fn()=>response()->json(['ruta'=>'http://localhost/reportes/informe.pdf','success'=>true]));
            $this->assertSame(1,DocumentoPdfVeterinario::count());
            $this->assertStringContainsString('/Veterinaria/PDF/',$respuesta->getData(true)['archivo_pdf_url']);
        }finally{$this->app->usePublicPath($anterior);\Illuminate\Support\Facades\File::deleteDirectory($temporal);}
    }
    public function test_url_externa_y_ficha_inexistente_no_crean_documentos(): void
    {
        $r=$this->req();
        app(ArchivarPdfVeterinario::class)->handle($r,fn()=>response()->json(['ruta'=>'https://otro.example.test/reportes/privado.pdf']));
        $this->assertSame(0,DocumentoPdfVeterinario::count());
        $this->assertNull($this->servicio()->registrar($this->req(['id_ficha_atencion'=>999,'id_mascota'=>4]),response('%PDF-1.4 inválido')));
    }

    public function test_centro_sin_vinculo_no_puede_inyectarse_en_documento_de_mascota(): void
    {
        $r=Request::create('/pdf','GET',['id_mascota'=>4,'id_institucion'=>999]);
        $doc=$this->servicio()->registrar($r,response('%PDF-1.4 mascota'));
        $this->assertEquals(4,$doc->id_mascota);
        $this->assertNull($doc->id_institucion);
    }

    public function test_historial_incluye_solo_pdf_de_laboratorio_de_la_mascota(): void
    {
        Schema::create('documentos_laboratorio_mascota',function(Blueprint $t){$t->id();$t->integer('id_mascota');$t->string('mime');$t->string('nombre_original');$t->timestamps();});
        DB::table('documentos_laboratorio_mascota')->insert([
            ['id_mascota'=>4,'mime'=>'application/pdf','nombre_original'=>'Hemograma.pdf'],
            ['id_mascota'=>5,'mime'=>'application/pdf','nombre_original'=>'Ajeno.pdf'],
            ['id_mascota'=>4,'mime'=>'image/png','nombre_original'=>'Imagen.png'],
        ]);
        $vista=$this->controller()->index(Request::create('/','GET',['id_mascota'=>4]),$this->servicio());
        $this->assertSame(['Hemograma.pdf'],$vista->getData()['historicos']->pluck('titulo')->all());
        $this->assertStringContainsString('Hemograma.pdf',$vista->with('errors',new \Illuminate\Support\ViewErrorBag())->render());
    }

    public function test_json_con_varios_pdf_archiva_todos_y_conserva_la_lista(): void
    {
        $anterior=public_path();$temporal=storage_path('framework/testing/pdf-json-'.uniqid());
        $this->app->usePublicPath($temporal);
        try {
            \Illuminate\Support\Facades\File::ensureDirectoryExists($temporal.'/reportes');
            file_put_contents($temporal.'/reportes/uno.pdf','%PDF-1.4 uno');
            file_put_contents($temporal.'/reportes/dos.pdf','%PDF-1.4 dos');
            $datos=[['ruta'=>'/reportes/uno.pdf'],['ruta'=>'/reportes/dos.pdf']];
            $respuesta=app(ArchivarPdfVeterinario::class)->handle($this->req(),fn()=>response()->json($datos));
            $this->assertSame(2,DocumentoPdfVeterinario::count());
            $this->assertSame($datos,$respuesta->getData(true));
        } finally {$this->app->usePublicPath($anterior);\Illuminate\Support\Facades\File::deleteDirectory($temporal);}
    }
}
