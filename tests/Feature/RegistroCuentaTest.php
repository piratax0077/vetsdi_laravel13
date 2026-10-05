<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificacionCorreo;
use App\Services\CuentasService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Registro, unicidad de RUT y correo, y confirmación del correo.
 *
 * Usa transacciones: users, usuario_roles y verificaciones_correo son InnoDB,
 * así que la base queda igual que antes de correr las pruebas.
 */
class RegistroCuentaTest extends TestCase
{
    use DatabaseTransactions;

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'tipo_cuenta' => 'tutor',
            'nombres' => 'Camila',
            'apellido_uno' => 'Soto',
            'apellido_dos' => 'Pérez',
            'rut' => '20.145.378-K',
            'email' => 'Camila.Soto@Ejemplo.cl',
            'telefono' => '+56 9 6543 2109',
            'password' => 'Veterchile1',
            'password_confirmation' => 'Veterchile1',
        ], $cambios);
    }

    public function test_crea_la_cuenta_con_su_primer_rol_y_el_perfil_por_completar(): void
    {
        $respuesta = $this->post(route('registro.cuenta'), $this->datosValidos());

        $respuesta->assertRedirect(route('registro.enviado'));

        // El correo se guarda normalizado y el RUT sin puntos.
        $usuario = User::where('email', 'camila.soto@ejemplo.cl')->first();

        $this->assertNotNull($usuario);
        $this->assertSame('20145378-K', $usuario->rut);
        $this->assertNull($usuario->email_verified_at);
        $this->assertTrue(Hash::check('Veterchile1', $usuario->password));

        $rol = $usuario->rolesCuenta()->first();

        $this->assertSame('tutor', $rol->tipo);
        $this->assertFalse($rol->perfil_completo);
        $this->assertTrue($usuario->hasRole(CuentasService::ROLES_SPATIE['tutor']));
    }

    public function test_no_crea_una_segunda_cuenta_si_el_correo_ya_existe_aunque_cambien_las_mayusculas(): void
    {
        $this->post(route('registro.cuenta'), $this->datosValidos());

        $respuesta = $this->post(route('registro.cuenta'), $this->datosValidos([
            'rut' => '9.735.129-5',
            'email' => 'CAMILA.SOTO@ejemplo.cl',
            'tipo_cuenta' => 'profesional',
        ]));

        $respuesta->assertSessionHas('cuenta_existente');
        $this->assertSame(1, User::whereRaw('LOWER(email) = ?', ['camila.soto@ejemplo.cl'])->count());
    }

    public function test_no_crea_una_segunda_cuenta_si_el_rut_ya_existe(): void
    {
        $this->post(route('registro.cuenta'), $this->datosValidos());

        $respuesta = $this->post(route('registro.cuenta'), $this->datosValidos([
            'email' => 'otra.persona@ejemplo.cl',
        ]));

        $respuesta->assertSessionHas('cuenta_existente');
        $this->assertSame(1, User::where('rut', '20145378-K')->count());
    }

    public function test_rechaza_rut_invalido_contrasenas_distintas_y_telefono_mal_escrito(): void
    {
        $respuesta = $this->post(route('registro.cuenta'), $this->datosValidos([
            'rut' => '16.489.235-9',
            'password_confirmation' => 'OtraClave1',
            'telefono' => '12345',
        ]));

        $respuesta->assertSessionHasErrors(['rut', 'password', 'telefono']);
        $this->assertSame(0, User::where('email', 'camila.soto@ejemplo.cl')->count());
    }

    public function test_con_un_error_el_registro_se_reabre_en_el_paso_que_hay_que_corregir(): void
    {
        // RUT inválido: los datos personales son el paso 2.
        $this->from(route('home.ingreso'))
            ->post(route('registro.cuenta'), $this->datosValidos(['rut' => '16.489.235-9']));

        $this->get(route('home.ingreso'))->assertSee('data-paso-inicial="2"', false);

        // Contraseñas distintas: la contraseña es el paso 3.
        $this->from(route('home.ingreso'))
            ->post(route('registro.cuenta'), $this->datosValidos(['password_confirmation' => 'OtraClave1']));

        $this->get(route('home.ingreso'))->assertSee('data-paso-inicial="3"', false);
    }

    public function test_sin_confirmar_el_correo_no_se_puede_ingresar(): void
    {
        $this->post(route('registro.cuenta'), $this->datosValidos());

        $respuesta = $this->post(route('login'), [
            'email' => 'camila.soto@ejemplo.cl',
            'password' => 'Veterchile1',
        ]);

        $respuesta->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_al_abrir_el_enlace_queda_verificado_y_puede_ingresar_con_rut_o_correo(): void
    {
        $this->post(route('registro.cuenta'), $this->datosValidos());

        $usuario = User::where('email', 'camila.soto@ejemplo.cl')->firstOrFail();
        $token = VerificacionCorreo::where('id_usuario', $usuario->id)->value('token');

        $this->get(route('registro.verificar', ['token' => $token]))
            ->assertRedirect(route('home.ingreso'));

        $this->assertNotNull($usuario->fresh()->email_verified_at);

        $this->post(route('login'), [
            'email' => '20.145.378-K',
            'password' => 'Veterchile1',
        ]);
        $this->assertAuthenticatedAs($usuario);

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => 'camila.soto@ejemplo.cl',
            'password' => 'Veterchile1',
        ]);
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_un_enlace_vencido_no_verifica_la_cuenta(): void
    {
        $this->post(route('registro.cuenta'), $this->datosValidos());

        $usuario = User::where('email', 'camila.soto@ejemplo.cl')->firstOrFail();
        $verificacion = VerificacionCorreo::where('id_usuario', $usuario->id)->firstOrFail();
        $verificacion->update(['expira_en' => now()->subHour()]);

        $this->withSession(['registro_pendiente' => $usuario->id])
            ->get(route('registro.verificar', ['token' => $verificacion->token]))
            ->assertRedirect(route('registro.enviado'));

        $this->assertNull($usuario->fresh()->email_verified_at);
    }
}
