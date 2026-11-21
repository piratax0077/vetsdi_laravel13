<?php

namespace Tests\Feature;

use App\Http\Controllers\JitsiController;
use App\Models\{JitsiVideo, User};
use Firebase\JWT\{JWT, Key};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Tests\TestCase;

class VideoconsultaAccesoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
        DB::purge('sqlite');
        foreach (['jitsi_video_consulta'=>['id_profesional','id_paciente','nombre_grupo','token_invitado','token_moderator'],
            'horas_medicas'=>['id_profesional','id_paciente','id_jitsi_video_consulta'],
            'pacientes'=>['id_usuario'],'profesionales'=>['id_usuario']] as $tabla=>$campos) {
            Schema::create($tabla,function(Blueprint $t) use($campos){$t->id();foreach($campos as $campo)$t->text($campo)->nullable();});
        }
        DB::table('pacientes')->insert(['id'=>9,'id_usuario'=>1]);
        DB::table('profesionales')->insert(['id'=>3,'id_usuario'=>7]);
        DB::table('jitsi_video_consulta')->insert(['id'=>2,'id_profesional'=>3,'id_paciente'=>9,'nombre_grupo'=>'Consulta.prueba','token_invitado'=>'invitado','token_moderator'=>'moderador']);
        DB::table('horas_medicas')->insert(['id'=>4,'id_profesional'=>3,'id_paciente'=>9,'id_jitsi_video_consulta'=>2]);
        config(['services.jitsi.app_id'=>'app-prueba']);
    }

    private function usuario(int $id): void
    {
        $user=\Mockery::mock(User::class)->makePartial();
        $user->id=$id;
        $user->shouldReceive('hasRole','hasAnyRole')->andReturn(false);
        Auth::setUser($user);
    }

    public function test_invitado_no_recibe_token_de_moderador(): void
    {
        $this->usuario(1);
        $respuesta=app(JitsiController::class)->buscarLlamadaJitsi(new Request(['id'=>2]));
        $this->assertArrayNotHasKey('token_moderator',$respuesta['registro']->toArray());
        $this->assertSame('invitado',$respuesta['registro']->token_invitado);
        $vista=app(JitsiController::class)->index('app-prueba','Consulta.prueba');
        $this->assertSame('invitado',$vista->getData()['token_invitado']);
    }

    public function test_otro_tutor_no_puede_obtener_los_tokens(): void
    {
        $this->usuario(99);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(JitsiController::class)->buscarLlamadaJitsi(new Request(['id'=>2]));
    }

    public function test_rutas_de_llamada_exigen_autenticacion(): void
    {
        foreach (['jitsi.buscar.meet','videollamada'] as $nombre) {
            $this->assertContains('auth',app('router')->getRoutes()->getByName($nombre)->gatherMiddleware());
        }
    }

    public function test_token_firmado_tiene_vigencia_de_la_consulta_y_no_fecha_fija(): void
    {
        $opciones=['private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA,'config'=>base_path('tests/Fixtures/openssl.cnf')];
        $clave=openssl_pkey_new($opciones);
        openssl_pkey_export($clave,$privada,null,$opciones);
        config(['services.jitsi.api_key'=>'id-clave','services.jitsi.private_key'=>$privada]);
        $inicio=now()->subMinute();
        $respuesta=JitsiController::generarJWT(5,1,'Tutor','','tutor@example.test',false,'Veterinario','Tutor',$inicio->format('Y-m-d H:i:s'));
        $this->assertSame(1,$respuesta->estado);
        $jwt=JWT::decode($respuesta->jwt,new Key(openssl_pkey_get_details($clave)['key'],'RS256'));
        $this->assertEquals($inicio->timestamp,$jwt->nbf);
        $this->assertEquals($inicio->timestamp+5400,$jwt->exp);
        $this->assertFalse($jwt->context->user->moderator);
    }

    public function test_sin_credenciales_se_devuelve_error_controlado(): void
    {
        config(['services.jitsi.api_key'=>null,'services.jitsi.private_key'=>null]);
        $resultado=JitsiController::generarJWT(5,1,'Tutor','','tutor@example.test',false,'Veterinario','Tutor',now()->toDateTimeString());
        $this->assertSame(0,$resultado->estado);
        $this->assertArrayHasKey('configuracion',$resultado->error);
    }
}
