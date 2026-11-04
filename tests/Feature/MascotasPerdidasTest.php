<?php

namespace Tests\Feature;

use App\Http\Controllers\MascotasPerdidasController;
use App\Models\User;
use App\Services\MascotasPerdidas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, DB, Schema, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MascotasPerdidasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']); DB::purge('sqlite');
        foreach (['users' => ['name', 'email'], 'pacientes' => ['id_usuario', 'nombres', 'telefono_uno'],
            'mascotas' => ['id_responsable', 'nombre', 'especie', 'especie_id', 'raza_id', 'fallecida', 'estado', 'foto_perfil', 'galeria'],
            'regiones' => ['nombre'], 'ciudades' => ['nombre', 'id_region'], 'especies_mascotas' => ['nombre'], 'razas_mascotas' => ['nombre']] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $t) use ($campos) { $t->id(); foreach ($campos as $c) $t->text($c)->nullable(); $t->timestamps(); });
        }
        (require database_path('migrations/2026_11_03_000003_create_mascotas_perdidas.php'))->up();
        for ($i = 1; $i <= 3; $i++) {
            DB::table('users')->insert(['id' => $i, 'name' => 'Tutor '.$i, 'email' => $i.'@test.example']);
            DB::table('pacientes')->insert(['id' => $i, 'id_usuario' => $i, 'nombres' => 'Tutor '.$i, 'telefono_uno' => '+5691111111'.$i]);
            DB::table('mascotas')->insert(['id' => $i, 'id_responsable' => $i, 'nombre' => 'Mascota '.$i, 'especie' => 'Perro', 'estado' => 1, 'fallecida' => 0]);
        }
        DB::table('regiones')->insert(['id' => 1, 'nombre' => 'Región Uno']);
        DB::table('ciudades')->insert([['id' => 1, 'nombre' => 'Comuna Uno', 'id_region' => 1], ['id' => 2, 'nombre' => 'Otra región', 'id_region' => 2]]);
        $this->como(1);
    }

    private function como(int $id): void { Auth::setUser(User::findOrFail($id)); }
    private function servicio(): MascotasPerdidas { return app(MascotasPerdidas::class); }
    private function req(array $d): Request { return Request::create('/', 'POST', $d); }
    private function aviso(array $d = []): array
    {
        return $d + ['token' => (string)Str::uuid(), 'estado' => 'perdida', 'mascota_id' => 1, 'region_id' => 1, 'comuna_id' => 1,
            'direccion' => 'Plaza Uno', 'latitud' => -33.4, 'longitud' => -70.6, 'fecha' => now()->toDateString(), 'descripcion' => 'Collar rojo', 'recompensa' => 0, 'fotos' => []];
    }
    private function respuesta(array $d = []): array
    {
        return $d + ['token' => (string)Str::uuid(), 'tipo' => 'la_vio', 'lugar' => 'Calle Dos', 'latitud' => -33.5, 'longitud' => -70.7,
            'fecha' => now()->toDateString(), 'hora' => '12:30', 'mensaje' => 'Está en la plaza', 'compartir_telefono' => false, 'fotos' => []];
    }
    private function invalido(callable $fn, string $campo): void
    {
        try { $fn(); $this->fail('Debió rechazar los datos'); } catch (ValidationException $e) { $this->assertArrayHasKey($campo, $e->errors()); }
    }
    private function prohibido(callable $fn): void
    {
        try { $fn(); $this->fail('Debió rechazar el acceso'); } catch (HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
    }

    public function test_publica_real_marca_mascota_y_no_duplica_un_reintento(): void
    {
        $d = $this->aviso(['recompensa' => 1, 'recompensa_monto' => 50000]);
        $id = $this->servicio()->publicar($this->req($d));
        $this->assertSame($id, $this->servicio()->publicar($this->req($d)));
        $this->assertSame(1, DB::table('vet_perdidas_avisos')->count());
        $this->assertEquals(1, DB::table('mascotas')->where('id', 1)->value('perdida'));
        $this->assertEquals(50000, $this->servicio()->pantalla()['avisos'][0]['recompensa']);
        $this->invalido(fn () => $this->servicio()->publicar($this->req($this->aviso())), 'mascota_id');
    }

    public function test_rechaza_datos_invalidos_y_no_marca_mascota_sin_publicar(): void
    {
        foreach (['comuna_id' => 2, 'latitud' => 999, 'fecha' => now()->addDay()->toDateString(), 'descripcion' => '', 'recompensa_monto' => -1] as $campo => $valor) {
            $this->invalido(fn () => $this->servicio()->publicar($this->req($this->aviso([$campo => $valor, 'recompensa' => $campo === 'recompensa_monto' ? 1 : 0]))), $campo);
        }
        $this->assertSame(0, DB::table('vet_perdidas_avisos')->count());
        $this->assertEquals(0, DB::table('mascotas')->where('id', 1)->value('perdida'));
    }

    public function test_no_publica_mascota_ajena_fallecida_o_eliminada(): void
    {
        foreach ([['id' => 2], ['id' => 1, 'fallecida' => 1], ['id' => 1, 'fallecida' => 0, 'estado' => 0]] as $caso) {
            $id = $caso['id']; unset($caso['id']);
            if ($caso) DB::table('mascotas')->where('id', $id)->update($caso);
            try { $this->servicio()->publicar($this->req($this->aviso(['mascota_id' => $id]))); $this->fail('Mascota no disponible'); }
            catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertSame(0, DB::table('vet_perdidas_avisos')->count()); }
        }
    }

    public function test_responde_notifica_y_telefono_se_comparte_solo_con_consentimiento(): void
    {
        $id = $this->servicio()->publicar($this->req($this->aviso()));
        $this->como(2); $datos = $this->respuesta();
        $r = $this->servicio()->responder($this->req($datos), $id);
        $this->assertSame($r, $this->servicio()->responder($this->req($datos), $id));
        $this->assertNull($this->servicio()->pantalla()['misRespuestas'][0]->telefono);
        $this->como(1); $pantalla = $this->servicio()->pantalla();
        $this->assertCount(1, $pantalla['notificaciones']);
        $this->assertNull($pantalla['misAvisos'][0]['respuestas'][0]['telefono']);
        $this->servicio()->compartir($r); $this->servicio()->compartir($r);
        $this->como(2); $pantalla = $this->servicio()->pantalla();
        $this->assertSame('+56911111111', $pantalla['misRespuestas'][0]->telefono);
        $this->assertCount(1, $pantalla['notificaciones']);
        $this->como(3); $pantalla = $this->servicio()->pantalla();
        $this->assertEmpty($pantalla['misRespuestas']); $this->assertEmpty($pantalla['misAvisos']);
        $this->assertStringNotContainsString('+569', json_encode($pantalla['avisos']));
        $this->prohibido(fn () => $this->servicio()->compartir($r));
        $this->prohibido(fn () => $this->servicio()->cerrar($id));
    }

    public function test_telefono_del_respondedor_solo_se_muestra_si_lo_autoriza(): void
    {
        $id = $this->servicio()->publicar($this->req($this->aviso()));
        $this->como(2); $this->servicio()->responder($this->req($this->respuesta(['compartir_telefono' => true])), $id);
        $this->como(1); $this->assertSame('+56911111112', $this->servicio()->pantalla()['misAvisos'][0]['respuestas'][0]['telefono']);
    }

    public function test_cierra_retira_de_red_desmarca_y_notifica_una_vez_a_cada_tutor(): void
    {
        $id = $this->servicio()->publicar($this->req($this->aviso()));
        $this->como(2);
        $this->servicio()->responder($this->req($this->respuesta()), $id);
        $this->servicio()->responder($this->req($this->respuesta(['tipo' => 'la_tiene'])), $id);
        $this->como(1); $this->servicio()->cerrar($id); $this->servicio()->cerrar($id);
        $this->assertEmpty($this->servicio()->pantalla()['avisos']);
        $this->assertEquals(0, DB::table('mascotas')->where('id', 1)->value('perdida'));
        $this->assertSame(1, DB::table('vet_perdidas_notificaciones')->where('tipo', 'aviso_cerrado')->count());
        $this->como(2); $this->invalido(fn () => $this->servicio()->responder($this->req($this->respuesta()), $id), 'aviso');
    }

    public function test_encontrada_admite_sin_registro_y_exige_sena_para_reclamar(): void
    {
        $id = $this->servicio()->publicar($this->req($this->aviso(['estado' => 'encontrada', 'mascota_id' => null, 'especie' => 'Gato'])));
        $this->assertSame('Sin nombre', DB::table('vet_perdidas_avisos')->where('id', $id)->value('nombre'));
        $this->como(2);
        $d = $this->respuesta(['tipo' => 'es_mia', 'mascota_id' => null]);
        $this->invalido(fn () => $this->servicio()->responder($this->req($d), $id), 'sena');
        $r = $this->servicio()->responder($this->req($d + ['sena' => 'Cicatriz en la oreja']), $id);
        $this->assertSame('es_mia', DB::table('vet_perdidas_respuestas')->where('id', $r)->value('tipo'));
    }

    private function imagen(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('foto.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    public function test_pantalla_renderiza_datos_reales_y_todos_los_scripts_sin_ejemplos(): void
    {
        app('view')->getFinder()->prependLocation(base_path('tests/Fixtures/perdidas-views'));
        $id = $this->servicio()->publicar($this->req($this->aviso()));
        $this->como(2); $r = $this->servicio()->responder($this->req($this->respuesta(['compartir_telefono' => true])), $id);
        $this->como(1); $this->servicio()->compartir($r);
        $html = app(MascotasPerdidasController::class)->index($this->servicio())->render();
        $this->assertStringContainsString('Mascota 1', $html);
        $this->assertStringContainsString('data-region="1" data-comuna="1"', $html);
        $this->assertStringContainsString('+56911111112', $html);
        $this->assertStringNotContainsString('Camila R.', $html);
        $this->assertStringContainsString('Encontré una mascota', $html);
        if (!is_dir(base_path('tmp/tests'))) mkdir(base_path('tmp/tests'), 0777, true);
        file_put_contents(base_path('tmp/tests/mascotas-perdidas.html'), $html);
        $this->como(2);
        $html = app(MascotasPerdidasController::class)->index($this->servicio())->render();
        $this->assertStringContainsString('+56911111111', $html);
        $this->como(3);
        $html = app(MascotasPerdidasController::class)->index($this->servicio())->render();
        $this->assertStringNotContainsString('+569', $html);
    }

    public function test_fotos_privadas_no_se_pueden_robar_y_son_visibles_solo_para_participantes(): void
    {
        Storage::fake('local');
        $controller = app(MascotasPerdidasController::class);
        $req = $this->req([]); $req->files->set('file', $this->imagen());
        $foto = $controller->subir($req, $this->servicio())->getData()->img->nombre_img;
        $this->como(2);
        $this->prohibido(fn () => $controller->foto((int)$foto, $this->servicio()));
        $this->invalido(fn () => $this->servicio()->publicar($this->req($this->aviso(['mascota_id' => 2, 'fotos' => [$foto]]))), 'fotos');
        $this->assertSame(0, DB::table('vet_perdidas_avisos')->count());
        $this->como(1); $id = $this->servicio()->publicar($this->req($this->aviso(['fotos' => [$foto]])));
        $this->como(2); $this->assertSame(200, $controller->foto((int)$foto, $this->servicio())->getStatusCode());
        $req = $this->req([]); $req->files->set('file', $this->imagen());
        $privada = $controller->subir($req, $this->servicio())->getData()->img->nombre_img;
        $this->servicio()->responder($this->req($this->respuesta(['fotos' => [$privada]])), $id);
        $this->como(3); $this->prohibido(fn () => $controller->foto((int)$privada, $this->servicio()));
        $this->como(1); $this->assertSame(200, $controller->foto((int)$privada, $this->servicio())->getStatusCode());
        $this->servicio()->cerrar($id); $this->como(3); $this->prohibido(fn () => $controller->foto((int)$foto, $this->servicio()));
    }

    public function test_telefono_ausente_y_respuesta_con_mascota_ajena_se_rechazan(): void
    {
        $this->invalido(fn () => $this->servicio()->publicar($this->req($this->aviso(['recompensa' => true]))), 'recompensa_monto');
        $id = $this->servicio()->publicar($this->req($this->aviso()));
        $this->como(2); DB::table('pacientes')->where('id', 2)->update(['telefono_uno' => null]);
        $this->invalido(fn () => $this->servicio()->responder($this->req($this->respuesta(['compartir_telefono' => true])), $id), 'compartir_telefono');
        try { $this->servicio()->responder($this->req($this->respuesta(['mascota_id' => 3])), $id); $this->fail('No debe aceptar mascota ajena'); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertSame(0, DB::table('vet_perdidas_respuestas')->count()); }
        $this->servicio()->responder($this->req($this->respuesta()), $id);
        $this->como(1);
        $n = DB::table('vet_perdidas_notificaciones')->value('id');
        app(MascotasPerdidasController::class)->leer($n, $this->servicio());
        $this->assertNotNull(DB::table('vet_perdidas_notificaciones')->where('id', $n)->value('leida_en'));
        $this->como(3);
        try { app(MascotasPerdidasController::class)->leer($n, $this->servicio()); $this->fail('Notificación privada'); }
        catch (HttpException $e) { $this->assertSame(404, $e->getStatusCode()); }
    }

    public function test_no_acepta_autorespuesta_y_exige_sesion(): void
    {
        $id = $this->servicio()->publicar($this->req($this->aviso()));
        $this->prohibido(fn () => $this->servicio()->responder($this->req($this->respuesta()), $id));
        Auth::forgetGuards();
        try { $this->servicio()->pantalla(); $this->fail('Debe exigir sesión'); }
        catch (HttpException $e) { $this->assertSame(401, $e->getStatusCode()); }
    }
}
