<?php

namespace Tests\Feature;

use App\Http\Controllers\ConsentimientosController;
use App\Models\{ConConsentimientosPcte, DocumentoFcPaciente};
use App\Services\NotificarConsentimientoTutor;
use App\Services\Mensajeria\MensajeriaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema, Mail};
use Tests\TestCase;

class ConsentimientoEnvioTutorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','mail.default'=>'log','mensajeria.driver'=>'log']);
        DB::purge('sqlite');
        foreach ([
            'pacientes'=>['email','telefono_uno','telefono'],
            'fichas_atenciones'=>['id_mascota','id_lugar_atencion'],
            'con_consentimientos'=>['nombre'],
            'documento_fc_paciente'=>['id_ficha_atencion','id_paciente','otro','documento','id_tipo_documento','id_profesional','id_lugar_atencion','url','observacion','cuerpo','fecha_envio','estado_envio','estado'],
        ] as $tabla=>$campos) Schema::create($tabla,function(Blueprint $t) use($campos) {
            $t->id(); foreach($campos as $c) $t->text($c)->nullable(); $t->timestamps();
        });
        DB::table('pacientes')->insert(['id'=>2,'email'=>'tutor@example.test','telefono_uno'=>'912345678']);
        DB::table('fichas_atenciones')->insert(['id'=>3,'id_mascota'=>4,'id_lugar_atencion'=>5]);
        DB::table('con_consentimientos')->insert(['id'=>6,'nombre'=>'Consentimiento de prueba']);
    }

    private function documento(): DocumentoFcPaciente
    {
        $controller = new class extends ConsentimientosController {
            public static function generarPdfFirmable(ConConsentimientosPcte $registro, ?array $firmaTutor, string $rutaSalida): void {}
        };
        $registro = new ConConsentimientosPcte();
        $registro->forceFill(['id'=>7,'id_fc'=>3,'id_consent'=>6,'id_paciente'=>2,'id_profesional'=>8]);
        return $controller::registrarDocumentoParaFirmaTutor($registro);
    }

    public function test_document_is_persisted_for_the_tutor_without_mass_assignment_error(): void
    {
        $doc=$this->documento();
        $this->assertEquals(2,$doc->fresh()->id_paciente);
        $this->assertSame('pendiente',json_decode($doc->cuerpo,true)['estado_firma']);
        $this->assertSame('consentimiento_informado_veterinario',$doc->otro);
    }

    public function test_development_transports_do_not_claim_delivery_and_offer_whatsapp_link(): void
    {
        Mail::fake();
        $resultado=app(NotificarConsentimientoTutor::class)->enviar($this->documento());
        $this->assertFalse($resultado['correo']);
        $this->assertFalse($resultado['whatsapp']);
        $this->assertStringStartsWith('https://wa.me/56912345678?text=',$resultado['whatsapp_url']);
        $this->assertStringContainsString(route('paciente.receta.documentos'),urldecode($resultado['whatsapp_url']));
        Mail::assertNothingSent();
    }

    public function test_configured_channels_receive_the_tutors_signature_link(): void
    {
        config(['mail.default'=>'smtp','mensajeria.driver'=>'twilio']);
        Mail::shouldReceive('raw')->once()->withArgs(fn($texto,$callback)=>str_contains($texto,'Ingrese con su cuenta de tutor'))->andReturnNull();
        $mensajeria=\Mockery::mock(MensajeriaService::class);
        $mensajeria->shouldReceive('enviarWhatsapp')->once()->withArgs(fn($telefono,$texto,$meta)=>$telefono==='56912345678' && str_contains($texto,'Documentos') && isset($meta['id_documento']))->andReturn(['estado'=>1]);
        $this->app->instance(MensajeriaService::class,$mensajeria);
        $resultado=app(NotificarConsentimientoTutor::class)->enviar($this->documento());
        $this->assertTrue($resultado['correo']);
        $this->assertTrue($resultado['whatsapp']);
    }
}
