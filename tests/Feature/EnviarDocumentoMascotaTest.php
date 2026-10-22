<?php

namespace Tests\Feature;

use App\Http\Controllers\{RecomendacionController, ficha_atencionController};
use App\Mail\CorreoGenerico;
use App\Models\{FichaAtencion, Paciente};
use App\Services\EnviarDocumentoMascota;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Mail, Schema};
use Tests\TestCase;

class EnviarDocumentoMascotaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:', 'mail.default'=>'smtp']);
        DB::purge('sqlite');
        Schema::create('pacientes', function (Blueprint $t) { $t->id(); $t->string('email'); });
        DB::table('pacientes')->insert(['id'=>2, 'email'=>'tutor@example.test']);
        Mail::fake();
    }

    public function test_receta_y_examen_se_adjuntan_y_se_envian_al_tutor_de_la_ficha(): void
    {
        $receta = \Mockery::mock(RecomendacionController::class);
        $receta->shouldReceive('verPDF')->once()->withArgs(fn ($r) => $r->id_ficha_atencion === 10)->andReturn(response('%PDF-1.4 receta'));
        $examen = \Mockery::mock(ficha_atencionController::class);
        $examen->shouldReceive('pdf_orden_examenes')->once()->withArgs(fn ($r) => $r->id_ficha_atencion === 10)->andReturn(response('%PDF-1.4 examen'));
        $this->app->instance(RecomendacionController::class, $receta);
        $this->app->instance(ficha_atencionController::class, $examen);
        $ficha = (new FichaAtencion())->forceFill(['id'=>10, 'id_paciente'=>2, 'id_mascota'=>4]);
        foreach (['receta','examenes'] as $tipo) {
            $resultado = app(EnviarDocumentoMascota::class)->enviar($ficha, $tipo);
            $this->assertTrue($resultado['correo_enviado']);
        }
        Mail::assertSent(CorreoGenerico::class, 2);
        Mail::assertSent(CorreoGenerico::class, fn ($mail) => $mail->hasTo('tutor@example.test') && count($mail->rawAttachments) === 1);
    }

    public function test_pdf_invalido_no_envia_correo_ni_informa_exito(): void
    {
        $receta = \Mockery::mock(RecomendacionController::class);
        $receta->shouldReceive('verPDF')->once()->andReturn(response('Error'));
        $this->app->instance(RecomendacionController::class, $receta);
        $resultado = app(EnviarDocumentoMascota::class)->enviar((new FichaAtencion())->forceFill(['id'=>10, 'id_paciente'=>2]), 'receta');
        $this->assertFalse($resultado['correo_enviado']);
        Mail::assertNothingSent();
    }

    public function test_tutor_sin_correo_conserva_documento_y_advierte_fallo(): void
    {
        DB::table('pacientes')->where('id',2)->update(['email'=>'']);
        $resultado = app(EnviarDocumentoMascota::class)->enviar((new FichaAtencion())->forceFill(['id'=>10, 'id_paciente'=>2]), 'examenes');
        $this->assertFalse($resultado['correo_enviado']);
        $this->assertStringContainsString('Documento guardado', $resultado['mensaje_correo']);
        Mail::assertNothingSent();
    }

    public function test_transporte_de_desarrollo_no_afirma_haber_enviado_correo(): void
    {
        config(['mail.default'=>'log']);
        $resultado = app(EnviarDocumentoMascota::class)->enviar((new FichaAtencion())->forceFill(['id'=>10, 'id_paciente'=>2]), 'receta');
        $this->assertFalse($resultado['correo_enviado']);
        $this->assertStringContainsString('no está configurado', $resultado['mensaje_correo']);
        Mail::assertNothingSent();
    }
}
