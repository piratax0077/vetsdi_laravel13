<?php

namespace Tests\Feature;

use App\Http\Controllers\{DocumentoVeterinarioCorreoController, ficha_atencionController};
use App\Mail\CorreoGenerico;
use App\Models\{CertificadoReposo, FichaAtencion, InformeMedico, Interconsulta, Paciente, Profesional, UsoPersonal, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Mail, Schema};
use Tests\TestCase;

class DocumentoVeterinarioCorreoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([Profesional::class => ['id_usuario'], Paciente::class => ['email'], FichaAtencion::class => ['id_profesional', 'id_paciente', 'id_mascota'], CertificadoReposo::class => ['id_ficha_atencion'], InformeMedico::class => ['id_ficha_atencion', 'id_tipo_informe'], Interconsulta::class => ['id_ficha_atencion_soli'], UsoPersonal::class => ['id_ficha_atencion']] as $modelo => $campos) {
            Schema::create((new $modelo)->getTable(), function (Blueprint $t) use ($campos) { $t->id(); foreach ($campos as $campo) $t->string($campo)->nullable(); });
        }
        DB::table((new Profesional)->getTable())->insert(['id'=>1,'id_usuario'=>10]);
        DB::table((new Paciente)->getTable())->insert(['id'=>1,'email'=>'tutor@example.test']);
        DB::table((new FichaAtencion)->getTable())->insert(['id'=>1,'id_profesional'=>1,'id_paciente'=>1,'id_mascota'=>4]);
        foreach ([CertificadoReposo::class, InformeMedico::class, UsoPersonal::class] as $modelo) DB::table((new $modelo)->getTable())->insert(['id'=>1,'id_ficha_atencion'=>1]);
        DB::table((new Interconsulta)->getTable())->insert(['id'=>1,'id_ficha_atencion_soli'=>1]);
        $user = new User; $user->id = 10; Auth::setUser($user); Mail::fake();
    }

    public function test_all_four_document_types_attach_pdf_and_send_only_to_registered_tutor(): void
    {
        $pdf = \Mockery::mock(ficha_atencionController::class);
        foreach (['pdf_certificado_reposo','pdf_informe_medico','pdf_interconsulta','pdf_uso_personal'] as $metodo) $pdf->shouldReceive($metodo)->once()->andReturn(response('%PDF-1.4 test'));
        $this->app->instance(ficha_atencionController::class, $pdf);
        foreach (['reposo'=>['id_ficha_atencion'=>1], 'informe'=>['id_informe_medico'=>1], 'interconsulta'=>['id_interconsulta'=>1], 'usoPersonal'=>['id_uso_personal'=>1]] as $metodo=>$datos) {
            $respuesta=app(DocumentoVeterinarioCorreoController::class)->{$metodo}(new Request($datos+['email'=>'otro@example.test']));
            $this->assertSame(1,$respuesta->getData(true)['estado']);
        }
        Mail::assertSent(CorreoGenerico::class, 4);
        Mail::assertSent(CorreoGenerico::class, fn($mail) => $mail->hasTo('tutor@example.test') && count($mail->rawAttachments) === 1);
    }

    public function test_foreign_professional_cannot_send_document(): void
    {
        DB::table((new FichaAtencion)->getTable())->where('id',1)->update(['id_profesional'=>99]);
        try { app(DocumentoVeterinarioCorreoController::class)->reposo(new Request(['id_ficha_atencion'=>1])); $this->fail('Debió rechazar la ficha ajena'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
        Mail::assertNothingSent();
    }

    public function test_failed_pdf_never_reports_success_or_sends_mail(): void
    {
        $pdf=\Mockery::mock(ficha_atencionController::class);
        $pdf->shouldReceive('pdf_certificado_reposo')->once()->andReturn(response('Error de documento'));
        $this->app->instance(ficha_atencionController::class,$pdf);
        $respuesta=app(DocumentoVeterinarioCorreoController::class)->reposo(new Request(['id_ficha_atencion'=>1]));
        $this->assertSame(500,$respuesta->status());$this->assertSame(0,$respuesta->getData(true)['estado']);Mail::assertNothingSent();
    }
}
