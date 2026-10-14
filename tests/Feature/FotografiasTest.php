<?php

namespace Tests\Feature;

use App\Http\Controllers\MascotaGenealogiaController;
use App\Models\{Mascota, MascotaGenealogia, User};
use App\Services\Fotografias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, DB, File, Schema, Storage};
use Tests\TestCase;

class FotografiasTest extends TestCase
{
    private string $publicOriginal;
    private string $publicPrueba;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:']);
        DB::purge('sqlite');
        Storage::fake('public');
        $this->publicOriginal = public_path();
        $this->publicPrueba = storage_path('framework/testing/fotos-'.uniqid());
        $this->app->usePublicPath($this->publicPrueba);
        foreach (['asistentes'=>['id_usuario','foto_perfil'], 'pacientes'=>['id_usuario','foto_perfil'], 'mascotas'=>['id_responsable','especie_id','especie','foto_perfil'], 'especies_mascotas'=>['nombre'], 'mascota_genealogias'=>['mascota_id','padre_foto','madre_foto','abuelo_paterno_foto','abuela_paterna_foto','abuelo_materno_foto','abuela_materna_foto','observaciones']] as $tabla=>$campos) {
            Schema::create($tabla, function (Blueprint $t) use ($campos) {
                $t->id(); foreach ($campos as $campo) $t->text($campo)->nullable(); $t->timestamps();
            });
        }
        DB::table('pacientes')->insert(['id'=>9,'id_usuario'=>7]);
        DB::table('mascotas')->insert(['id'=>1,'id_responsable'=>9,'especie_id'=>2,'foto_perfil'=>'mascotas/anterior.png']);
        DB::table('especies_mascotas')->insert(['id'=>2,'nombre'=>'Canina']);
        $user=new User(); $user->id=7; Auth::setUser($user);
    }

    protected function tearDown(): void
    {
        $this->app->usePublicPath($this->publicOriginal);
        File::deleteDirectory($this->publicPrueba);
        parent::tearDown();
    }

    private function foto(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('foto.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
    }

    public function test_assistant_upload_reaches_database_and_returns_public_url(): void
    {
        DB::table('asistentes')->insert(['id'=>1,'id_usuario'=>7]);
        $respuesta=app(\App\Http\Controllers\EscritorioAsistente::class)->actualizarFoto(Request::create('/foto','POST',[],[],['foto_perfil'=>$this->foto()]));
        $datos=$respuesta->getData(true);
        $this->assertTrue($datos['success']);
        $ruta=DB::table('asistentes')->value('foto_perfil');
        $this->assertStringEndsWith('/storage/'.$ruta,$datos['foto_url']);
        $this->assertFileExists(public_path('storage/'.$ruta));
    }

    public function test_user_and_pet_gallery_uploads_are_public_and_persistent(): void
    {
        $respuesta=app(\App\Http\Controllers\EscritorioPaciente::class)->actualizarFoto(Request::create('/foto','POST',[],[],['foto_perfil'=>$this->foto()]));
        $this->assertSame(1,$respuesta->getData(true)['estado']);
        $ruta=DB::table('pacientes')->value('foto_perfil');
        $this->assertFileExists(public_path('storage/'.$ruta));
        $resultado=app(\App\Http\Controllers\CargaImagenController::class)->cargaImagenTemp(Request::create('/foto','POST',[],[],['file'=>$this->foto()]));
        $this->assertSame(1,$resultado['estado']);
        $this->assertFileExists(public_path(ltrim($resultado['img']['url'],'/')));
        $this->assertSame('imagenes/temp/'.$resultado['img']['nombre_img'],resolver_foto_perfil_mascota(null,['ven_pre'=>[[$resultado['img']['url']]]]));
    }

    public function test_profile_is_saved_and_public_copy_matches_storage(): void
    {
        Storage::disk('public')->put('mascotas/anterior.png', 'foto anterior');
        $ruta=app(Fotografias::class)->perfil(Mascota::find(1), $this->foto());
        $this->assertSame($ruta, Mascota::find(1)->foto_perfil);
        $this->assertSame(Storage::disk('public')->get($ruta), file_get_contents(public_path('storage/'.$ruta)));
        Storage::disk('public')->assertExists('mascotas/anterior.png');
    }

    public function test_removing_profile_clears_database_and_both_file_copies(): void
    {
        $mascota=Mascota::find(1);
        $ruta=app(Fotografias::class)->perfil($mascota,$this->foto());
        app(Fotografias::class)->quitarPerfil($mascota);
        $this->assertNull(Mascota::find(1)->foto_perfil);
        Storage::disk('public')->assertMissing($ruta);
        $this->assertFileDoesNotExist(public_path('storage/'.$ruta));
    }

    public function test_failed_database_save_keeps_previous_photo_and_discards_new_upload(): void
    {
        $perfil=new class extends Model {
            public function save(array $options=[]){throw new \RuntimeException('Fallo simulado');}
        };
        try { app(Fotografias::class)->perfil($perfil, $this->foto()); $this->fail('Expected failure'); }
        catch (\RuntimeException $e) { $this->assertSame('Fallo simulado',$e->getMessage()); }
        $this->assertSame([], Storage::disk('public')->allFiles('fotos_perfil'));
        $this->assertSame([], File::files(public_path('storage/fotos_perfil')));
        $this->assertSame('mascotas/anterior.png',Mascota::find(1)->foto_perfil);
    }

    public function test_genealogy_saves_all_seven_photos_and_preserves_them_on_text_edit(): void
    {
        $campos=['foto_mascota','padre_foto','madre_foto','abuelo_paterno_foto','abuela_paterna_foto','abuelo_materno_foto','abuela_materna_foto'];
        $request=Request::create('/genealogia','POST',['especie_id'=>2],[],array_fill_keys($campos,$this->foto()));
        app(MascotaGenealogiaController::class)->store($request,Mascota::find(1));
        $g=MascotaGenealogia::first();
        $rutas=[$mascotaFoto=Mascota::find(1)->foto_perfil];
        foreach (array_slice($campos,1) as $campo) $rutas[]=$g->{$campo};
        $this->assertCount(7,array_unique($rutas));
        foreach ($rutas as $ruta) {Storage::disk('public')->assertExists($ruta); $this->assertFileExists(public_path('storage/'.$ruta));}
        app(MascotaGenealogiaController::class)->store(Request::create('/genealogia','POST',['especie_id'=>2,'observaciones'=>'Editado']),Mascota::find(1));
        $this->assertSame($mascotaFoto,Mascota::find(1)->foto_perfil);
        $this->assertSame($g->padre_foto,MascotaGenealogia::first()->padre_foto);
        $this->assertSame('Editado',MascotaGenealogia::first()->observaciones);
    }

    public function test_genealogy_rollback_removes_new_files_without_deleting_old_photos(): void
    {
        Storage::disk('public')->put('mascotas/anterior.png','original');
        DB::statement("CREATE TRIGGER fallo_foto BEFORE UPDATE ON mascotas BEGIN SELECT RAISE(FAIL, 'Fallo simulado'); END");
        try {
            app(MascotaGenealogiaController::class)->store(Request::create('/genealogia','POST',['especie_id'=>2],[],['foto_mascota'=>$this->foto(),'padre_foto'=>$this->foto()]),Mascota::find(1));
            $this->fail('Expected failure');
        } catch (\Illuminate\Database\QueryException $e) { $this->assertStringContainsString('Fallo simulado',$e->getMessage()); }
        Storage::disk('public')->assertExists('mascotas/anterior.png');
        $this->assertSame([],Storage::disk('public')->allFiles('mascotas/genealogia'));
        $this->assertSame(0,MascotaGenealogia::count());
    }
}
