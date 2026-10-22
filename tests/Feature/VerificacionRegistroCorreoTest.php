<?php

namespace Tests\Feature;

use App\Mail\CorreoGenerico;
use App\Models\{User, VerificacionCorreo};
use App\Services\VerificacionCorreoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Mail, Schema};
use Tests\TestCase;

class VerificacionRegistroCorreoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite', 'database.connections.sqlite.database'=>':memory:', 'mail.default'=>'smtp', 'mail.redirect_to'=>'pruebas@example.test']);
        DB::purge('sqlite');
        foreach ([
            'users'=>['name','nombres','apellido_uno','apellido_dos','email','email_verified_at'],
            'verificaciones_correo'=>['id_usuario','email','token','expira_en','verificado_en','enviado_en'],
            'registro_correo'=>['id_user','data','fecha_envio','estado'],
        ] as $tabla=>$campos) Schema::create($tabla, function (Blueprint $t) use ($campos) { $t->id(); foreach ($campos as $c) $t->text($c)->nullable(); $t->timestamps(); });
        DB::table('users')->insert(['id'=>1,'nombres'=>'Camila','apellido_uno'=>'Soto','email'=>'camila@example.test']);
        Mail::fake();
    }

    private function anterior(): VerificacionCorreo
    {
        return VerificacionCorreo::create(['id_usuario'=>1, 'email'=>'camila@example.test', 'token'=>str_repeat('a',64), 'expira_en'=>now()->addHour(), 'enviado_en'=>now()->subMinutes(5)]);
    }

    public function test_envia_enlace_real_a_la_cuenta_sin_desviarlo_a_la_casilla_de_pruebas(): void
    {
        $previo = $this->anterior();
        $servicio = app(VerificacionCorreoService::class);
        $this->assertTrue($servicio->enviar(User::find(1)));
        $nuevo = VerificacionCorreo::latest('id')->first();
        $this->assertNotNull($nuevo->enviado_en);
        $this->assertFalse($previo->fresh()->estaVigente());
        Mail::assertSent(CorreoGenerico::class, fn ($mail) => $mail->hasTo('camila@example.test') && !$mail->hasTo('pruebas@example.test') && $mail->detalle['body']['enlace'] === route('registro.verificar', ['token'=>$nuevo->token]));
        $this->assertNotNull($servicio->confirmar($nuevo->token));
        $this->assertNotNull(User::find(1)->email_verified_at);
        $this->assertNull($servicio->confirmar($nuevo->token));
    }

    public function test_fallo_smtp_conserva_el_enlace_anterior_y_permite_reintentar(): void
    {
        $previo = $this->anterior();
        Mail::shouldReceive('to->cc->bcc->send')->once()->andThrow(new \RuntimeException('SMTP no disponible'));
        $servicio = app(VerificacionCorreoService::class);
        $this->assertFalse($servicio->enviar(User::find(1)));
        $this->assertTrue($previo->fresh()->estaVigente());
        $this->assertSame(1, VerificacionCorreo::count());
        $this->assertTrue($servicio->puedeReenviar(User::find(1)));
    }

    public function test_recargar_confirmacion_no_cambia_un_fallo_por_un_mensaje_de_exito(): void
    {
        $this->withSession(['registro_pendiente'=>1, 'correo_enviado'=>false]);
        $this->app['session']->flash('correo_enviado', false);
        $this->get(route('registro.enviado'))->assertViewHas('correoEnviado', false);
        $this->get(route('registro.enviado'))->assertViewHas('correoEnviado', false);
        $this->get(route('registro.enviado'))->assertViewHas('correoEnviado', false);
    }
}
