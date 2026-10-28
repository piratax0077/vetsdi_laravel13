<?php

namespace Tests\Feature;

use App\Http\Controllers\ConsentimientosController;
use App\Models\{ConConsentimientos, ConConsentimientosPcte, User};
use App\Services\NotificarConsentimientoTutor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Tests\TestCase;

class ConsentimientoEutanasiaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'profesionales' => ['id_usuario'],
            'mascotas' => ['id_responsable'],
            'fichas_atenciones' => ['id_profesional', 'id_paciente', 'id_mascota', 'id_lugar_atencion'],
            'con_consentimientos' => ['nombre', 'texto', 'estado'],
            'con_consentimientos_pcte' => ['id_consent', 'id_fc', 'id_paciente', 'id_profesional', 'diagnostico_cons', 'cirugia_cons', 'num_consentimiento', 'fecha_cons', 'observaciones_con', 'revocacion', 'otro', 'confirmacion'],
            'documento_fc_paciente' => ['id_ficha_atencion', 'id_paciente', 'otro', 'documento', 'id_tipo_documento', 'id_profesional', 'id_lugar_atencion', 'url', 'observacion', 'cuerpo', 'fecha_envio', 'estado_envio', 'estado'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $t) use ($campos) {
                $t->id(); foreach ($campos as $campo) $t->text($campo)->nullable(); $t->timestamps();
            });
        }
        DB::table('profesionales')->insert(['id' => 3, 'id_usuario' => 7]);
        DB::table('mascotas')->insert(['id' => 4, 'id_responsable' => 2]);
        DB::table('fichas_atenciones')->insert(['id' => 1, 'id_profesional' => 3, 'id_paciente' => 2, 'id_mascota' => 4, 'id_lugar_atencion' => 5]);
        $user = \Mockery::mock(User::class)->makePartial();
        $user->id = 7;
        $user->shouldReceive('hasRole')->with('Profesional')->andReturnTrue();
        Auth::setUser($user);
        $notificador = \Mockery::mock(NotificarConsentimientoTutor::class);
        $notificador->shouldReceive('enviar')->andReturn(['resumen' => 'Disponible para firma.']);
        $this->app->instance(NotificarConsentimientoTutor::class, $notificador);
    }

    private function controller(): ConsentimientosController
    {
        return new class extends ConsentimientosController {
            public static function generarPdfFirmable(ConConsentimientosPcte $registro, ?array $firmaTutor, string $rutaSalida): void {}
        };
    }

    public function test_genera_documento_para_la_mascota_sin_depender_de_otro_modal(): void
    {
        $resultado = $this->controller()->registrar(new Request([
            'tipo_consentimiento' => 'eutanasia', 'id_ficha_atencion' => 1,
            'diagnostico_cons' => 'CA', 'cirugia_cons' => 'Tumor terminal',
            'observaciones_con' => 'Observaciones', 'num_consentimiento' => 0,
        ]));
        $this->assertSame(1, $resultado['estado']);
        $registro = ConConsentimientosPcte::sole();
        $this->assertSame('CA', $registro->diagnostico_cons);
        $this->assertSame('Tumor terminal', $registro->cirugia_cons);
        $this->assertEquals(2, $registro->id_paciente);
        $this->assertEquals(3, $registro->id_profesional);
        $documento = DB::table('documento_fc_paciente')->sole();
        $this->assertSame('pendiente', json_decode($documento->cuerpo, true)['estado_firma']);
        $this->assertEquals(4, json_decode($documento->cuerpo, true)['id_mascota']);
        $this->assertStringContainsString('eutanasia', $documento->observacion);
    }

    public function test_reutiliza_la_plantilla_existente_y_no_una_de_tratamiento(): void
    {
        DB::table('con_consentimientos')->insert([
            ['id' => 8, 'nombre' => 'Tratamiento', 'texto' => 'Otro', 'estado' => 1],
            ['id' => 9, 'nombre' => 'Consentimiento eutanasia', 'texto' => 'Texto registrado', 'estado' => 1],
        ]);
        $plantilla = app(\App\Services\ConsentimientoEutanasia::class)->plantilla();
        $this->assertEquals(9, $plantilla->id);
        $this->assertSame('Texto registrado', $plantilla->texto);
        $this->assertSame(2, ConConsentimientos::count());
    }

    public function test_sin_motivo_no_guarda_plantilla_ni_documento(): void
    {
        try {
            $this->controller()->registrar(new Request(['tipo_consentimiento' => 'eutanasia', 'id_ficha_atencion' => 1, 'diagnostico_cons' => 'CA']));
            $this->fail('Debe requerir motivo.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('cirugia_cons', $e->errors());
        }
        $this->assertSame(0, ConConsentimientos::count());
        $this->assertSame(0, ConConsentimientosPcte::count());
        $this->assertSame(0, DB::table('documento_fc_paciente')->count());
    }

    public function test_modal_carga_identificacion_y_muestra_la_accion_fuera_del_contenido(): void
    {
        $mascota = new \App\Models\Mascota();
        $mascota->forceFill(['nombre' => 'Luna', 'chip' => '123456']);
        $mascota->setRelation('razaMascota', (object) ['nombre' => 'Mestiza']);
        $mascota->setRelation('especieMascota', (object) ['nombre' => 'Canina']);
        $profesional = (object) ['nombre' => 'Jaime', 'apellido_uno' => 'Kriman', 'apellido_dos' => 'Astorga'];
        $html = view('general.modal.m_consentimiento_eutanasia', compact('mascota', 'profesional'))->render();
        foreach (['Luna', 'Mestiza', 'Canina', '123456', 'Generar y solicitar firma al tutor', 'modal-footer'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }
        $this->assertStringNotContainsString('{nombre_mascota}', $html);
        $this->assertStringNotContainsString('id="cirugia_cons"', $html);
        $this->assertStringNotContainsString('solicitud_autorizacion', $html);
    }
}
