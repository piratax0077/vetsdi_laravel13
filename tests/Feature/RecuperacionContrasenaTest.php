<?php

namespace Tests\Feature;

use App\Mail\CorreoGenerico;
use App\Models\User;
use App\Services\Mensajeria\MensajeriaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Recuperación de contraseña: enlace al correo y código al celular.
 *
 * Ojo: password_resets es MyISAM y no entra en la transacción, por eso al
 * terminar cada prueba se borran a mano los tokens del correo de prueba.
 */
class RecuperacionContrasenaTest extends TestCase
{
    use DatabaseTransactions;

    private const CORREO = 'camila.recupera@ejemplo.cl';

    private const TELEFONO = '+56 9 0102 0304';

    protected function setUp(): void
    {
        parent::setUp();

        // Los correos de prueba no se desvían a otra casilla.
        config(['mail.redirect_to' => null]);
    }

    protected function tearDown(): void
    {
        DB::table('password_resets')->where('email', self::CORREO)->delete();

        parent::tearDown();
    }

    private function crearUsuario(): User
    {
        $usuario = new User;

        $usuario->forceFill([
            'name' => 'Camila Soto',
            'nombres' => 'Camila',
            'apellido_uno' => 'Soto',
            'email' => self::CORREO,
            'rut' => '20145378-K',
            'telefono' => self::TELEFONO,
            'password' => Hash::make('ClaveAntigua1'),
            'email_verified_at' => now(),
        ])->save();

        return $usuario;
    }

    /** Pide el enlace por correo y devuelve la dirección que venía en el mensaje. */
    private function pedirEnlace(): string
    {
        Mail::fake();

        $this->post(route('home.recuperar_contrasena'), ['correo_recuperacion' => 'Camila.Recupera@Ejemplo.cl'])
            ->assertRedirect(route('home.ingreso'))
            ->assertSessionHas('recuperar', 'correo')
            ->assertSessionHas('mensaje');

        $enlace = null;

        Mail::assertSent(CorreoGenerico::class, function (CorreoGenerico $correo) use (&$enlace) {
            $enlace = $correo->detalle['body']['enlace'];

            return $correo->detalle['blade'] === 'restablecer_contrasena' && $correo->hasTo(self::CORREO);
        });

        return $enlace;
    }

    /** Pide el código por celular y devuelve los seis dígitos que se enviaron. */
    private function pedirCodigo(string $telefono = '9 0102 0304'): string
    {
        $mensaje = '';

        $this->mock(MensajeriaService::class, function ($mensajeria) use (&$mensaje) {
            $mensajeria->shouldReceive('enviarWhatsapp')->once()->andReturnUsing(
                function (string $destino, string $texto) use (&$mensaje) {
                    $mensaje = $texto;

                    return ['estado' => 1];
                }
            );
        });

        $this->post(route('recuperar.codigo'), ['telefono_recuperacion' => $telefono])
            ->assertRedirect(route('home.ingreso'))
            ->assertSessionHas('recuperar', 'codigo');

        $this->assertSame(1, preg_match('/\b(\d{6})\b/', $mensaje, $partes));

        return $partes[1];
    }

    private function tokenDe(string $enlace): string
    {
        return basename((string) parse_url($enlace, PHP_URL_PATH));
    }

    public function test_por_correo_llega_un_enlace_y_la_contrasena_solo_cambia_al_guardar_la_nueva(): void
    {
        $usuario = $this->crearUsuario();

        $enlace = $this->pedirEnlace();

        // Pedir el enlace no toca la contraseña actual.
        $this->assertTrue(Hash::check('ClaveAntigua1', $usuario->fresh()->password));

        $this->get($enlace)->assertOk()->assertSee('Crea tu contraseña nueva');

        $this->post(route('password.update'), [
            'token' => $this->tokenDe($enlace),
            'email' => self::CORREO,
            'password' => 'ClaveNueva2',
            'password_confirmation' => 'ClaveNueva2',
        ])->assertRedirect(route('home.ingreso'))->assertSessionHas('mensaje');

        $this->assertTrue(Hash::check('ClaveNueva2', $usuario->fresh()->password));
    }

    public function test_el_enlace_sirve_una_sola_vez(): void
    {
        $usuario = $this->crearUsuario();
        $enlace = $this->pedirEnlace();

        $datos = [
            'token' => $this->tokenDe($enlace),
            'email' => self::CORREO,
            'password' => 'ClaveNueva2',
            'password_confirmation' => 'ClaveNueva2',
        ];

        $this->post(route('password.update'), $datos);

        $this->post(route('password.update'), array_merge($datos, [
            'password' => 'OtraClave3',
            'password_confirmation' => 'OtraClave3',
        ]))->assertRedirect(route('home.ingreso'))->assertSessionHas('mensaje_error');

        $this->assertTrue(Hash::check('ClaveNueva2', $usuario->fresh()->password));
    }

    public function test_la_contrasena_nueva_debe_cumplir_la_misma_politica_del_registro(): void
    {
        $usuario = $this->crearUsuario();
        $enlace = $this->pedirEnlace();

        $this->post(route('password.update'), [
            'token' => $this->tokenDe($enlace),
            'email' => self::CORREO,
            'password' => 'sinmayusculas1',
            'password_confirmation' => 'sinmayusculas1',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('ClaveAntigua1', $usuario->fresh()->password));
    }

    public function test_un_correo_que_no_existe_recibe_la_misma_respuesta_y_no_se_envia_nada(): void
    {
        Mail::fake();

        $this->post(route('home.recuperar_contrasena'), ['correo_recuperacion' => 'nadie.con.este.correo@ejemplo.cl'])
            ->assertRedirect(route('home.ingreso'))
            ->assertSessionHas('recuperar', 'correo')
            ->assertSessionHas('mensaje');

        Mail::assertNothingSent();
    }

    public function test_no_se_puede_pedir_otro_enlace_antes_de_un_minuto(): void
    {
        $this->crearUsuario();
        $this->pedirEnlace();

        $this->post(route('home.recuperar_contrasena'), ['correo_recuperacion' => self::CORREO])
            ->assertSessionHas('mensaje_error');

        Mail::assertSentCount(1);
    }

    public function test_por_celular_el_codigo_correcto_lleva_directo_a_cambiar_la_contrasena(): void
    {
        $usuario = $this->crearUsuario();

        $codigo = $this->pedirCodigo();

        $respuesta = $this->post(route('recuperar.verificar'), ['codigo_recuperacion' => $codigo]);

        $destino = $respuesta->headers->get('Location');

        $this->assertStringStartsWith(url('reset-password').'/', $destino);

        $this->get($destino)->assertOk()->assertSee(self::CORREO);

        $this->post(route('password.update'), [
            'token' => $this->tokenDe($destino),
            'email' => self::CORREO,
            'password' => 'ClaveNueva2',
            'password_confirmation' => 'ClaveNueva2',
        ])->assertRedirect(route('home.ingreso'));

        $this->assertTrue(Hash::check('ClaveNueva2', $usuario->fresh()->password));
    }

    public function test_un_codigo_usado_no_sirve_por_segunda_vez(): void
    {
        $this->crearUsuario();

        $codigo = $this->pedirCodigo();

        $this->post(route('recuperar.verificar'), ['codigo_recuperacion' => $codigo]);

        $this->from(route('home.ingreso'))
            ->post(route('recuperar.verificar'), ['codigo_recuperacion' => $codigo])
            ->assertRedirect(route('home.ingreso'));

        $this->assertSame(1, DB::table('password_resets')->where('email', self::CORREO)->count());
    }

    public function test_un_codigo_equivocado_no_deja_pasar_y_se_anula_al_quinto_intento(): void
    {
        $this->crearUsuario();

        $codigo = $this->pedirCodigo();
        $equivocado = str_pad((string) (((int) $codigo + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->from(route('home.ingreso'))
                ->post(route('recuperar.verificar'), ['codigo_recuperacion' => $equivocado])
                ->assertSessionHasErrors('codigo_recuperacion');
        }

        // Agotados los intentos, ni el código correcto sirve: hay que pedir otro.
        $this->from(route('home.ingreso'))
            ->post(route('recuperar.verificar'), ['codigo_recuperacion' => $codigo])
            ->assertSessionHasErrors('codigo_recuperacion');

        $this->assertSame(0, DB::table('password_resets')->where('email', self::CORREO)->count());
    }

    public function test_un_celular_que_no_esta_registrado_no_recibe_codigo_pero_la_respuesta_es_la_misma(): void
    {
        $this->mock(MensajeriaService::class, function ($mensajeria) {
            $mensajeria->shouldNotReceive('enviarWhatsapp');
        });

        $this->post(route('recuperar.codigo'), ['telefono_recuperacion' => self::TELEFONO])
            ->assertRedirect(route('home.ingreso'))
            ->assertSessionHas('recuperar', 'codigo');

        $this->from(route('home.ingreso'))
            ->post(route('recuperar.verificar'), ['codigo_recuperacion' => '123456'])
            ->assertSessionHasErrors('codigo_recuperacion');
    }

    public function test_el_celular_se_reconoce_aunque_este_guardado_con_otro_formato(): void
    {
        $usuario = $this->crearUsuario();
        $usuario->forceFill(['telefono' => '901020304'])->save();

        $this->pedirCodigo('+56 9 0102 0304');
    }

    public function test_rechaza_un_celular_mal_escrito(): void
    {
        $this->from(route('home.ingreso'))
            ->post(route('recuperar.codigo'), ['telefono_recuperacion' => '12345'])
            ->assertSessionHasErrors('telefono_recuperacion');
    }

    public function test_la_tarjeta_vuelve_abierta_en_el_paso_del_codigo(): void
    {
        $this->crearUsuario();
        $this->pedirCodigo();

        $this->get(route('home.ingreso'))
            ->assertOk()
            ->assertSee('id="codigo_recuperacion"', false)
            ->assertSee(self::TELEFONO);
    }
}
