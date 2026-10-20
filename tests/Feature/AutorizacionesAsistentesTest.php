<?php

namespace Tests\Feature;

use App\Http\Middleware\AutorizarAsistente;
use App\Models\{Asistente, AsistenteLugarAtencion, AutorizacionAsistente, HoraMedica, User};
use App\Services\AutorizacionesAsistentes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Session\Store;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AutorizacionesAsistentesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'asistentes' => ['id_usuario', 'nombres', 'apellido_uno', 'apellido_dos'],
            'asistentes_lugar_atencion' => ['id_asistente', 'id_lugar_atencion', 'id_profesional', 'id_institucion', 'estado'],
            'contrato_dependiente' => ['id_empleado', 'id_lugar_atencion', 'tipo_empleado', 'estado'],
            'profesionales' => ['id_usuario', 'nombre', 'apellido_uno', 'apellido_dos'],
            'profesionales_lugares_atencion' => ['id_profesional', 'id_lugar_atencion', 'id_institucion', 'estado'],
            'lugares_atencion' => ['nombre'],
            'instituciones' => ['id_usuario', 'id_responsable', 'id_lugar_atencion'],
            'admin_inst_serv' => ['id_admin'],
            'sucursal' => ['id_institucion', 'id_lugar_atencion', 'estado'],
            'horas_medicas' => ['id_profesional', 'id_lugar_atencion'],
            'bonos' => ['id_profesional', 'id_asistente', 'id_referencia'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $table) use ($campos) {
                $table->id();
                foreach ($campos as $campo) $table->text($campo)->nullable();
                $table->timestamps();
            });
        }
        (require database_path('migrations/2026_10_13_180000_create_autorizaciones_asistentes_table.php'))->up();
        DB::table('asistentes')->insert(['id' => 20, 'id_usuario' => 2, 'nombres' => 'Asistente', 'apellido_uno' => 'Prueba']);
        DB::table('profesionales')->insert([
            ['id' => 10, 'id_usuario' => 1, 'nombre' => 'Profesional', 'apellido_uno' => 'Uno'],
            ['id' => 11, 'id_usuario' => 3, 'nombre' => 'Profesional', 'apellido_uno' => 'Dos'],
        ]);
        DB::table('lugares_atencion')->insert([['id' => 100, 'nombre' => 'Centro Uno'], ['id' => 101, 'nombre' => 'Centro Dos']]);
        DB::table('profesionales_lugares_atencion')->insert([
            ['id_profesional' => 10, 'id_lugar_atencion' => 100, 'id_institucion' => 50, 'estado' => 1],
            ['id_profesional' => 11, 'id_lugar_atencion' => 100, 'id_institucion' => 50, 'estado' => 1],
            ['id_profesional' => 11, 'id_lugar_atencion' => 101, 'id_institucion' => 51, 'estado' => 1],
        ]);
        DB::table('asistentes_lugar_atencion')->insert(['id_asistente' => 20, 'id_lugar_atencion' => 100, 'id_profesional' => 10, 'estado' => 1]);
        DB::table('instituciones')->insert(['id' => 50, 'id_usuario' => 4, 'id_lugar_atencion' => 100]);
    }

    private function usuario(int $id, ?string $rol = null): User
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->id = $id;
        $user->shouldReceive('hasAnyRole')->andReturnUsing(fn (...$roles) => in_array($rol, is_array($roles[0] ?? null) ? $roles[0] : $roles, true));
        $user->shouldReceive('hasRole')->andReturnUsing(fn ($role) => $role === $rol);
        return $user;
    }

    private function servicio(): AutorizacionesAsistentes { return app(AutorizacionesAsistentes::class); }

    public function test_new_assistants_have_no_implicit_permissions_regardless_of_type(): void
    {
        foreach (config('asistentes.roles') as $rol) {
            $this->assertTrue($this->servicio()->esAsistente($this->usuario(2, $rol)));
            $this->assertNotContains(true, $this->servicio()->permisos(20, 100, 10));
        }
    }

    public function test_professional_can_grant_and_revoke_only_their_own_assistant_permissions(): void
    {
        $asistente = Asistente::find(20);
        $registro = $this->servicio()->guardar($this->usuario(1), $asistente, 100, 10, ['permiso_ver_agenda' => true]);
        $this->assertEquals(1, $registro->id_usuario_autoriza);
        $this->servicio()->exigir($asistente, 100, 10, 'permiso_ver_agenda');
        $this->servicio()->guardar($this->usuario(1), $asistente, 100, 10, []);
        $this->assertFalse($this->servicio()->permisos(20, 100, 10)['permiso_ver_agenda']);
        $this->assertSame(1, AutorizacionAsistente::count());
        $this->expectException(HttpException::class);
        $this->servicio()->guardar($this->usuario(3), $asistente, 100, 10, ['permiso_ver_agenda' => true]);
    }

    public function test_assistant_cannot_authorize_themself(): void
    {
        $this->expectException(HttpException::class);
        $this->servicio()->guardar($this->usuario(2, 'AsistenteAdm'), Asistente::find(20), 100, 0, ['permiso_administracion' => true]);
    }

    public function test_centre_policy_overrides_professional_grants_including_denial(): void
    {
        $this->servicio()->guardar($this->usuario(1), Asistente::find(20), 100, 10, ['permiso_ver_agenda' => true]);
        $this->servicio()->guardar($this->usuario(4), Asistente::find(20), 100, 0, []);
        $this->assertFalse($this->servicio()->permisos(20, 100, 10)['permiso_ver_agenda']);
        $this->expectException(HttpException::class);
        $this->servicio()->guardar($this->usuario(4), Asistente::find(20), 101, 0, ['permiso_ver_agenda' => true]);
    }

    public function test_inactive_relationship_blocks_previously_granted_permissions(): void
    {
        $this->servicio()->guardar($this->usuario(1), Asistente::find(20), 100, 10, ['permiso_ver_agenda' => true]);
        AsistenteLugarAtencion::query()->update(['estado' => 0]);
        $this->expectException(HttpException::class);
        $this->servicio()->exigir(Asistente::find(20), 100, 10, 'permiso_ver_agenda');
    }

    public function test_contexts_do_not_include_unrelated_professionals(): void
    {
        $this->assertSame([10], $this->servicio()->contextos(Asistente::find(20))->pluck('id_profesional')->all());
        DB::table('contrato_dependiente')->insert(['id_empleado' => 999, 'id_lugar_atencion' => 101, 'tipo_empleado' => 'ASISTENTE CONSULTA', 'estado' => 2]);
        $this->assertFalse($this->servicio()->vinculado(20, 101, 11));
    }

    private function solicitud(string $nombre, array $datos = []): Request
    {
        $request = Request::create('/', 'GET', $datos);
        $request->setUserResolver(fn () => $this->usuario(2, 'Asistente'));
        $route = (new Route('GET', '/', fn () => null))->name($nombre);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);
        $session = new Store('test', new ArraySessionHandler(120));
        $session->put('asistente_contexto', ['id_lugar_atencion' => 100, 'id_profesional' => 10]);
        $request->setLaravelSession($session);
        return $request;
    }

    public function test_direct_url_is_forbidden_without_permission(): void
    {
        $this->expectException(HttpException::class);
        app(AutorizarAsistente::class)->handle($this->solicitud('agenda.buscar_hora_medica'), fn () => response('Acceso indebido'));
    }

    public function test_agenda_permission_cannot_be_used_to_reserve_or_access_another_professional(): void
    {
        $this->servicio()->guardar($this->usuario(1), Asistente::find(20), 100, 10, ['permiso_ver_agenda' => true]);
        foreach ([['agenda.agendar_hora', []], ['agenda.buscar_hora_medica', ['id_profesional' => 11]]] as [$nombre, $datos]) {
            try {
                app(AutorizarAsistente::class)->handle($this->solicitud($nombre, $datos), fn () => response('Acceso indebido'));
                $this->fail('La autorización debe ser específica.');
            } catch (HttpException $error) { $this->assertSame(403, $error->getStatusCode()); }
        }
    }

    public function test_legacy_eloquent_queries_are_scoped_and_do_not_leak_to_next_request(): void
    {
        $this->servicio()->guardar($this->usuario(1), Asistente::find(20), 100, 10, ['permiso_ver_agenda' => true]);
        DB::table('horas_medicas')->insert([['id_profesional' => 10, 'id_lugar_atencion' => 100], ['id_profesional' => 11, 'id_lugar_atencion' => 101]]);
        app(AutorizarAsistente::class)->handle($this->solicitud('agenda.buscar_hora_medica'), function () {
            $this->assertSame(1, HoraMedica::count());
            return response('ok');
        });
        $this->assertSame(2, HoraMedica::count());
    }

    public function test_cash_queries_only_include_the_authorized_location_and_professional(): void
    {
        $this->servicio()->guardar($this->usuario(1), Asistente::find(20), 100, 10, ['permiso_entrega_caja' => true]);
        DB::table('horas_medicas')->insert([
            ['id' => 1, 'id_profesional' => 10, 'id_lugar_atencion' => 100],
            ['id' => 2, 'id_profesional' => 10, 'id_lugar_atencion' => 101],
        ]);
        DB::table('bonos')->insert([
            ['id_profesional' => 10, 'id_asistente' => 20, 'id_referencia' => 1],
            ['id_profesional' => 10, 'id_asistente' => 20, 'id_referencia' => 2],
        ]);
        app(AutorizarAsistente::class)->handle($this->solicitud('flujo_caja.data_flujo_caja'), function () {
            $this->assertSame(1, \App\Models\Bono::count());
            return response('ok');
        });
        $this->assertSame(2, \App\Models\Bono::count());
    }
    public function test_escritorio_asistente_no_es_capturado_por_el_sitio_publico(): void
    {
        $ruta = app('router')->getRoutes()->match(Request::create('/Asistentes', 'GET'));
        $this->assertSame('asistentes.home', $ruta->getName());
        $sitio = app('router')->getRoutes()->match(Request::create('/mi-clinica-publica', 'GET'));
        $this->assertSame('sitio.ficha', $sitio->getName());
    }

}

