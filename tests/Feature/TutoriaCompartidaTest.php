<?php

namespace Tests\Feature;

use App\Http\Controllers\TutoriaCompartidaController;
use App\Mail\CorreoGenerico;
use App\Models\{Mascota, MascotaTutoria, Paciente, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Mail, Schema};
use Tests\TestCase;

class TutoriaCompartidaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'users' => ['email'], 'pacientes' => ['id_usuario', 'nombres', 'apellido_uno', 'rut'],
            'mascotas' => ['id_responsable', 'nombre', 'estado', 'fallecida', 'foto_perfil', 'galeria'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $t) use ($campos) {
                $t->id();
                foreach ($campos as $campo) $t->string($campo)->nullable();
                $t->timestamps();
            });
        }
        (require database_path('migrations/2026_11_07_000001_create_mascotas_tutorias.php'))->up();

        foreach ([1, 2, 3] as $id) {
            DB::table('users')->insert(['id' => $id, 'email' => "tutor$id@example.test"]);
            DB::table('pacientes')->insert(['id' => $id, 'id_usuario' => $id, 'nombres' => "Tutor $id", 'rut' => "11.111.11$id-1"]);
        }
        DB::table('mascotas')->insert(['id' => 1, 'id_responsable' => 1, 'nombre' => 'Michi', 'estado' => 1]);
        Mail::fake();
    }

    private function invitacion(string $rol): string
    {
        $this->actingAs(User::findOrFail(1));
        $respuesta = app(TutoriaCompartidaController::class)->invitar(Request::create('/', 'POST', [
            'rut' => '11.111.112-1', 'rol' => $rol,
        ]), 1);
        $this->assertSame('pendiente', $respuesta['invitacion']['estado']);
        $this->assertSame($rol, $respuesta['invitacion']['rol']);

        $enlace = Mail::sent(CorreoGenerico::class)->last()->detalle['body']['enlace'];
        $this->assertSame($rol, Mail::sent(CorreoGenerico::class)->last()->detalle['body']['rol']);
        $this->assertTrue(Mail::sent(CorreoGenerico::class)->last()->hasTo('tutor2@example.test'));

        return basename(parse_url($enlace, PHP_URL_PATH));
    }

    public function test_activa_solo_para_el_destinatario_y_revoca_acceso_al_quitarlo(): void
    {
        $token = $this->invitacion('cuidador');
        $this->assertStringContainsString('Cuidador', Mail::sent(CorreoGenerico::class)->last()->render());
        $this->get(route('tutoria.invitacion', $token))->assertOk()->assertSee('Te invitaron a cuidar de Michi');
        $tutor = Paciente::findOrFail(2);
        $this->assertFalse(Mascota::accesiblesPor($tutor)->exists());
        $this->actingAs(User::findOrFail(3))
            ->post(route('tutoria.responder', [$token, 'aceptar']))->assertForbidden();
        $this->actingAs(User::findOrFail(2))
            ->post(route('tutoria.responder', [$token, 'aceptar']))->assertRedirect(route('tutoria.invitacion', $token));
        $this->assertTrue(Mascota::accesiblesPor($tutor)->exists());
        $this->assertFalse(Mascota::accesiblesPor($tutor, true)->exists());
        $this->assertSame('cuidador', Mascota::findOrFail(1)->rolTutoria($tutor));

        $this->actingAs(User::findOrFail(1));
        app(TutoriaCompartidaController::class)->gestionar(1, MascotaTutoria::sole()->id, 'quitar');
        $this->assertFalse(Mascota::accesiblesPor($tutor)->exists());
    }

    public function test_expira_a_los_siete_dias_y_reenvio_invalida_el_enlace_anterior(): void
    {
        $token = $this->invitacion('co_tutor');
        $this->travel(8)->days();
        $this->actingAs(User::findOrFail(2))->post(route('tutoria.responder', [$token, 'aceptar']))->assertRedirect();
        $this->assertSame('expirada', MascotaTutoria::sole()->estadoActual());
        $this->assertFalse(Mascota::accesiblesPor(Paciente::findOrFail(2))->exists());

        $this->actingAs(User::findOrFail(1));
        app(TutoriaCompartidaController::class)->gestionar(1, MascotaTutoria::sole()->id, 'reenviar');
        $this->get(route('tutoria.invitacion', $token))->assertNotFound();
        $nuevo = basename(parse_url(Mail::sent(CorreoGenerico::class)->last()->detalle['body']['enlace'], PHP_URL_PATH));
        $this->actingAs(User::findOrFail(2))->post(route('tutoria.responder', [$nuevo, 'aceptar']))->assertRedirect();
        $this->assertTrue(Mascota::accesiblesPor(Paciente::findOrFail(2), true)->exists());
    }
}
