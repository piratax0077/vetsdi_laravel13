<?php

namespace Tests\Feature;

use App\Http\Controllers\{ficha_atencionController, InformeVeterinarioController};
use App\Mail\CorreoGenerico;
use App\Models\{InformeMedico, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Mail, Schema};
use Tests\TestCase;

class InformeVeterinarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'profesionales' => ['id_usuario'], 'pacientes' => ['email'], 'mascotas' => ['id_responsable'],
            'horas_medicas' => ['id_paciente', 'id_profesional', 'id_mascota', 'id_ficha_atencion', 'id_lugar_atencion'],
            'fichas_atenciones' => ['id_paciente', 'id_profesional', 'id_mascota', 'id_lugar_atencion'],
            'informes_medicos' => ['id_tipo_informe', 'informe_medico', 'fecha_informe_medico', 'id_paciente', 'id_ficha_atencion', 'id_profesional', 'id_lugar_atencion', 'cod_auto'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $t) use ($campos) {
                $t->id(); foreach ($campos as $campo) $t->text($campo)->nullable(); $t->timestamps();
            });
        }
        DB::table('profesionales')->insert(['id' => 3, 'id_usuario' => 10]);
        DB::table('pacientes')->insert(['id' => 2, 'email' => 'tutor@example.test']);
        DB::table('mascotas')->insert(['id' => 4, 'id_responsable' => 2]);
        DB::table('fichas_atenciones')->insert(['id' => 1, 'id_paciente' => 2, 'id_profesional' => 3, 'id_mascota' => 4, 'id_lugar_atencion' => 5]);
        DB::table('horas_medicas')->insert(['id' => 7, 'id_paciente' => 2, 'id_profesional' => 3, 'id_mascota' => 4, 'id_ficha_atencion' => 1, 'id_lugar_atencion' => 5]);
        $user = new User(); $user->id = 10; Auth::setUser($user);
        Mail::fake();
    }

    private function request(): Request
    {
        return new Request(['hora_medica' => 7, 'tipo_informe' => 1, 'comentarios_informe_medico' => 'Informe veterinario', 'id_lugar_atencion' => 999]);
    }

    public function test_el_boton_guarda_el_informe_y_envia_su_pdf_al_tutor_registrado(): void
    {
        $pdf = \Mockery::mock(ficha_atencionController::class);
        $pdf->shouldReceive('pdf_informe_medico')->once()->withArgs(fn ($request) => (int) $request->id_informe_medico === 1)->andReturn(response('%PDF-1.4 prueba'));
        $this->app->instance(ficha_atencionController::class, $pdf);
        $respuesta = (new ficha_atencionController())->registrar_informe_medico($this->request())->getData(true);
        $this->assertSame(1, $respuesta['estado']);
        $this->assertSame(1, $respuesta['envio']['estado']);
        $this->assertSame(1, $respuesta['id_last']);
        $this->assertStringContainsString('id_informe_medico=1', $respuesta['pdf_url']);
        $informe = InformeMedico::sole();
        $this->assertSame('Informe veterinario', $informe->informe_medico);
        $this->assertEquals(5, $informe->id_lugar_atencion);
        Mail::assertSent(CorreoGenerico::class, fn ($correo) => $correo->hasTo('tutor@example.test') && count($correo->rawAttachments) === 1);
    }

    public function test_si_falla_el_pdf_conserva_el_informe_para_reintentar_envio(): void
    {
        $pdf = \Mockery::mock(ficha_atencionController::class);
        $pdf->shouldReceive('pdf_informe_medico')->once()->andReturn(response('Error de PDF'));
        $this->app->instance(ficha_atencionController::class, $pdf);
        $respuesta = app(InformeVeterinarioController::class)->registrar($this->request())->getData(true);
        $this->assertSame(1, $respuesta['estado']);
        $this->assertSame(0, $respuesta['envio']['estado']);
        $this->assertSame(1, InformeMedico::count());
        Mail::assertNothingSent();
    }

    public function test_no_guarda_ni_envia_un_informe_de_otro_profesional(): void
    {
        DB::table('horas_medicas')->where('id', 7)->update(['id_profesional' => 99]);
        try {
            app(InformeVeterinarioController::class)->registrar($this->request());
            $this->fail('Debe rechazar la hora ajena.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertSame(0, InformeMedico::count());
        }
        Mail::assertNothingSent();
    }
}
