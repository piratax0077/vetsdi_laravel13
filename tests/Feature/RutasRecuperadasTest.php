<?php

namespace Tests\Feature;

use App\Http\Controllers\{ReporteFallaController, RevocacionVeterinariaController};
use App\Models\{ConConsentimientosPcte, User};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RutasRecuperadasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:']);
        DB::purge('sqlite');
        (require database_path('migrations/2026_10_13_235000_create_solicitudes_soporte_table.php'))->up();
        foreach ([
            'profesionales'=>['id_usuario'], 'pacientes'=>['id_usuario'],
            'mascotas'=>['id_responsable'],
            'fichas_atenciones'=>['id_profesional','id_paciente','id_mascota'],
            'horas_medicas'=>['id_profesional','id_paciente','id_mascota'],
            'con_consentimientos_pcte'=>['id_fc','id_paciente','id_profesional','id_log_user_devices_revocacion','revocacion','fecha_revocacion','observaciones_rev'],
            'log_users_devices'=>['id_user_recept','tipo','estado','token','fecha_exp'],
        ] as $tabla=>$campos) Schema::create($tabla, function(Blueprint $t) use ($campos) {
            $t->id(); foreach ($campos as $c) $t->string($c)->nullable(); $t->timestamps();
        });
        DB::table('profesionales')->insert(['id'=>5,'id_usuario'=>1]);
        DB::table('pacientes')->insert(['id'=>9,'id_usuario'=>2]);
        DB::table('mascotas')->insert(['id'=>3,'id_responsable'=>9]);
        DB::table('fichas_atenciones')->insert(['id'=>7,'id_profesional'=>5,'id_paciente'=>9,'id_mascota'=>3]);
        DB::table('con_consentimientos_pcte')->insert(['id'=>4,'id_fc'=>7,'id_profesional'=>5,'id_paciente'=>9,'id_log_user_devices_revocacion'=>8,'revocacion'=>0]);
        DB::table('log_users_devices')->insert(['id'=>8,'id_user_recept'=>2,'tipo'=>5,'estado'=>0,'token'=>'token-valido','fecha_exp'=>now()->addHour()]);
        $this->usuario(1, true);
    }

    private function usuario(int $id, bool $profesional): void
    {
        $u=\Mockery::mock(User::class)->makePartial(); $u->id=$id;
        $u->shouldReceive('hasRole')->andReturn($profesional); Auth::setUser($u);
    }

    public function test_clinical_lists_reject_tutor_writes_and_foreign_professionals(): void
    {
        $middleware = app(\App\Http\Middleware\AutorizarFichaVeterinaria::class);
        foreach ([[2,false],[88,true]] as [$id,$profesional]) {
            $this->usuario($id,$profesional);
            try {
                $middleware->handle(Request::create('/','POST',['id_ficha_atencion'=>7]),fn()=>true);
                $this->fail('No debe autorizar cambios sanitarios.');
            } catch (HttpException $e) {$this->assertSame(403,$e->getStatusCode());}
        }
    }

    public function test_clinical_lists_bind_identity_and_allow_current_tutor_reading_only(): void
    {
        $middleware = app(\App\Http\Middleware\AutorizarFichaVeterinaria::class);
        $request = Request::create('/','POST',['id_ficha_atencion'=>7]);
        $this->assertTrue($middleware->handle($request,fn()=>true));
        $this->assertEquals(9,$request->id_paciente);
        $this->assertEquals(5,$request->id_profesional);
        $this->usuario(2,false);
        $this->assertTrue($middleware->handle(Request::create('/','GET',['id_ficha'=>7]),fn()=>true));
        DB::table('mascotas')->where('id',3)->update(['id_responsable'=>99]);
        $this->expectException(HttpException::class);
        $middleware->handle(Request::create('/','GET',['id_ficha'=>7]),fn()=>true);
    }

    public function test_invalid_batch_does_not_delete_existing_clinical_data_and_failed_save_rolls_back(): void
    {
        $service = app(\App\Services\ColeccionClinica::class);
        foreach (['invalid-json', '[{"nombre":""}]', '[{"nombre":"Control"}]'] as $lista) {
            try {
                $service->reemplazar(new Request(['items'=>$lista]),'items',['nombre'=>'required|string'],[],function () {
                    DB::table('fichas_atenciones')->where('id',7)->delete();
                    return ['falla'=>1];
                });
                $this->fail('Debe rechazar la lista incompleta.');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertDatabaseHas('fichas_atenciones',['id'=>7]);
            }
        }
    }

    private function confirmar(array $datos=[])
    {
        return app(RevocacionVeterinariaController::class)->confirmar(new Request($datos+[
            'id_consentimiento'=>4,'token'=>'token-valido','acepta_consentimiento'=>1,
        ]));
    }

    public function test_all_registered_routes_have_real_public_actions(): void
    {
        $this->artisan('sistema:auditar')->assertExitCode(0);
    }

    public function test_reporting_a_failure_persists_the_authenticated_author(): void
    {
        $r=app(ReporteFallaController::class)->guardar(new Request(['usuario_id'=>99,'asunto_falla'=>'Error de pantalla','descripcion_falla'=>'No permite abrir el resumen.']));
        $this->assertSame(1,$r->getData(true)['estado']);
        $this->assertDatabaseHas('solicitudes_soporte',['usuario_id'=>1,'asunto'=>'Error de pantalla','estado'=>'abierta']);
        $this->assertNotEmpty($r->getData(true)['ticket']);
    }

    public function test_failure_report_requires_authentication_and_valid_content(): void
    {
        $this->postJson(route('ficha_medica.registrar_falla'),[])->assertUnprocessable();
        Auth::forgetGuards();
        $this->postJson(route('ficha_medica.registrar_falla'),[])->assertUnauthorized();
        $this->assertDatabaseCount('solicitudes_soporte',0);
    }

    public function test_revocation_consumes_the_specific_token_and_cannot_be_replayed(): void
    {
        $this->confirmar();
        $this->assertDatabaseHas('con_consentimientos_pcte',['id'=>4,'revocacion'=>1]);
        $this->assertDatabaseHas('log_users_devices',['id'=>8,'estado'=>1]);
        $this->expectException(HttpException::class);
        $this->confirmar();
    }

    public function test_invalid_expired_or_wrong_recipient_tokens_cannot_revoke(): void
    {
        foreach ([['token'=>'ajeno'],['fecha_exp'=>now()->subMinute()],['tipo'=>4],['id_user_recept'=>99]] as $cambio) {
            DB::table('log_users_devices')->where('id',8)->update(['token'=>'token-valido','fecha_exp'=>now()->addHour(),'tipo'=>5,'id_user_recept'=>2]);
            DB::table('log_users_devices')->where('id',8)->update($cambio);
            try {$this->confirmar(); $this->fail('Debe rechazar la autorización.');}
            catch (HttpException $e) {$this->assertSame(403,$e->getStatusCode());}
            $this->assertDatabaseHas('con_consentimientos_pcte',['id'=>4,'revocacion'=>0]);
        }
    }

    public function test_professional_cannot_finalize_without_tutor_approval(): void
    {
        $this->expectException(HttpException::class);
        app(RevocacionVeterinariaController::class)->guardar(new Request(['id_consentimiento_pcte'=>4,'observaciones_rev'=>'Motivo']));
    }

    public function test_revocation_form_only_accepts_the_token_bound_to_its_consent(): void
    {
        $this->get(route('consentimiento.revocar.form', ['id_consentimiento'=>4,'token'=>'token-valido']))
            ->assertOk()->assertSee('Autorizar Revocación');
        DB::table('con_consentimientos_pcte')->insert(['id'=>6,'id_fc'=>7,'id_profesional'=>5,'id_paciente'=>9,'id_log_user_devices_revocacion'=>99,'revocacion'=>0]);
        $this->get(route('consentimiento.revocar.form', ['id_consentimiento'=>6,'token'=>'token-valido']))->assertForbidden();
        $this->assertDatabaseHas('con_consentimientos_pcte',['id'=>6,'revocacion'=>0]);
    }

    public function test_another_professional_cannot_request_revocation(): void
    {
        DB::table('profesionales')->insert(['id'=>15,'id_usuario'=>3]);
        DB::table('horas_medicas')->insert(['id_profesional'=>15,'id_paciente'=>9,'id_mascota'=>3]);
        $this->usuario(3,true);
        $this->expectException(HttpException::class);
        app(RevocacionVeterinariaController::class)->autorizarProfesional(ConConsentimientosPcte::find(4));
    }

    public function test_professional_can_save_approved_revocation_but_tutor_cannot_edit_it(): void
    {
        $this->confirmar();
        $request=new Request(['id_consentimiento_pcte'=>4,'observaciones_rev'=>'Decisión del tutor']);
        $r=app(RevocacionVeterinariaController::class)->guardar($request);
        $this->assertSame(1,$r->getData(true)['estado']);
        $this->assertSame('Decisión del tutor',ConConsentimientosPcte::find(4)->observaciones_rev);
        $this->usuario(2,false);
        $this->expectException(HttpException::class);
        app(RevocacionVeterinariaController::class)->guardar($request);
    }
}
