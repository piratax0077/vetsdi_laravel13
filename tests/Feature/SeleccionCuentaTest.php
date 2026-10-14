<?php

namespace Tests\Feature;

use App\Http\Controllers\SeleccionCuentaController;
use App\Models\Asistente;
use App\Models\Instituciones;
use App\Models\Mascota;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\User;
use App\Services\CuentasService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Selección de cuenta, guard de perfil y cambio de escritorio.
 *
 * Ojo: mascotas e instituciones son MyISAM y no entran en la transacción, por
 * eso las pruebas que las tocan borran a mano lo que crearon.
 */
class SeleccionCuentaTest extends TestCase
{
    use DatabaseTransactions;

    private CuentasService $cuentas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cuentas = app(CuentasService::class);
    }

    private function crearUsuario(array $tipos, string $rut = '18777349-6'): User
    {
        $usuario = User::create([
            'name' => 'Camila Soto',
            'nombres' => 'Camila',
            'apellido_uno' => 'Soto',
            'apellido_dos' => 'Pérez',
            'email' => 'prueba.seleccion@ejemplo.cl',
            'rut' => $rut,
            'telefono' => '+56 9 6543 2109',
            'password' => Hash::make('Veterchile1'),
            'email_verified_at' => now(),
        ]);

        foreach ($tipos as $tipo) {
            $this->cuentas->asignarRol($usuario, $tipo);
        }

        return $usuario;
    }

    public function test_con_un_solo_rol_entra_directo_sin_mostrar_la_pantalla(): void
    {
        $usuario = $this->crearUsuario(['asistente']);

        // Con el perfil sin completar la entrada directa va al formulario.
        $this->actingAs($usuario)
            ->get(route('cuenta.seleccion'))
            ->assertRedirect(route('cuenta.entrar', ['tipo' => 'asistente']));

        $this->actingAs($usuario)
            ->get(route('cuenta.entrar', ['tipo' => 'asistente']))
            ->assertRedirect(route('perfil.completar', ['tipo' => 'asistente']));
    }

    public function test_con_varios_roles_muestra_solo_los_que_tiene(): void
    {
        $usuario = $this->crearUsuario(['tutor', 'profesional']);

        $respuesta = $this->actingAs($usuario)->get(route('cuenta.seleccion'));

        $respuesta->assertOk();
        $respuesta->assertSee('Elige el perfil con el que quieres trabajar');
        $respuesta->assertSee('Camila Soto');
        $respuesta->assertSee('Tutor');
        $respuesta->assertSee('Profesional');
        $respuesta->assertDontSee('Clínica veterinaria');
        $respuesta->assertSee('Completar perfil');
    }

    public function test_no_se_puede_entrar_a_un_rol_que_no_se_tiene(): void
    {
        $usuario = $this->crearUsuario(['tutor', 'asistente']);

        $this->actingAs($usuario)
            ->get(route('cuenta.entrar', ['tipo' => 'clinica']))
            ->assertRedirect(route('cuenta.seleccion'));

        $this->actingAs($usuario)
            ->get(route('perfil.completar', ['tipo' => 'clinica']))
            ->assertRedirect(route('cuenta.seleccion'));
    }

    public function test_con_el_perfil_incompleto_la_url_del_escritorio_no_deja_entrar(): void
    {
        $usuario = $this->crearUsuario(['asistente']);

        $this->actingAs($usuario)
            ->withSession([SeleccionCuentaController::SESION_ROL_ACTIVO => 'asistente'])
            ->get(route('asistente.home'))
            ->assertRedirect(route('perfil.completar', ['tipo' => 'asistente']));
    }

    public function test_completar_un_rol_no_habilita_los_otros(): void
    {
        $usuario = $this->crearUsuario(['asistente', 'profesional']);

        $this->actingAs($usuario)->post(route('perfil.completar.guardar', ['tipo' => 'asistente']), [
            'id_modalidad' => 2,
        ])->assertRedirect(route('asistente.home'));

        $this->assertTrue($this->cuentas->perfilCompleto($usuario, 'asistente'));
        $this->assertFalse($this->cuentas->perfilCompleto($usuario, 'profesional'));
        $this->assertSame(2, (int) Asistente::where('id_usuario', $usuario->id)->value('id_modalidad'));

        // El escritorio del profesional sigue cerrado hasta completar ese perfil.
        $this->actingAs($usuario)
            ->get(route('cuenta.entrar', ['tipo' => 'profesional']))
            ->assertRedirect(route('perfil.completar', ['tipo' => 'profesional']));
    }

    public function test_el_profesional_guarda_su_profesion_y_queda_completo(): void
    {
        $usuario = $this->crearUsuario(['profesional']);

        $this->actingAs($usuario)->post(route('perfil.completar.guardar', ['tipo' => 'profesional']), [
            'id_especialidad' => 1,
            'id_tipo_especialidad' => '',
        ])->assertRedirect(route('profesional.home'));

        $profesional = Profesional::where('id_usuario', $usuario->id)->first();

        $this->assertNotNull($profesional);
        $this->assertSame(1, (int) $profesional->id_especialidad);
        $this->assertSame('18777349-6', $profesional->rut);
        $this->assertTrue($this->cuentas->perfilCompleto($usuario, 'profesional'));
    }

    public function test_el_tutor_guarda_su_direccion_y_su_primera_mascota(): void
    {
        $usuario = $this->crearUsuario(['tutor']);
        $ciudad = \DB::table('ciudades')->first();

        $this->actingAs($usuario)->post(route('perfil.completar.guardar', ['tipo' => 'tutor']), [
            'id_region' => $ciudad->id_region,
            'id_ciudad' => $ciudad->id,
            'direccion' => 'Av. Siempre Viva 742',
            'depto' => 'Depto 3B',
            'mascota_nombre' => 'Lúa',
            'mascota_especie' => 1,
            'mascota_sexo' => 'H',
            'mascota_edad_aproximada' => 3,
        ])->assertRedirect(route('paciente.home'));

        $paciente = Paciente::where('id_usuario', $usuario->id)->first();
        $mascota = Mascota::where('id_user', $usuario->id)->first();

        $this->assertNotNull($paciente);
        $this->assertNotNull($mascota);
        $this->assertSame('Lúa', $mascota->nombre);
        $this->assertSame($paciente->id, (int) $mascota->id_responsable);
        $this->assertTrue($this->cuentas->perfilCompleto($usuario, 'tutor'));

        // mascotas es MyISAM: la transacción no la borra.
        Mascota::where('id_user', $usuario->id)->delete();
    }

    public function test_la_clinica_guarda_los_datos_de_la_empresa(): void
    {
        $usuario = $this->crearUsuario(['clinica']);
        $ciudad = \DB::table('ciudades')->first();

        $this->actingAs($usuario)->post(route('perfil.completar.guardar', ['tipo' => 'clinica']), [
            'rut_empresa' => '76.156.756-K',
            'razon_social' => 'Veterinaria Las Araucarias SpA',
            'nombre_fantasia' => 'Vet Araucarias',
            'giro' => 'Servicios veterinarios',
            'id_region' => $ciudad->id_region,
            'id_ciudad' => $ciudad->id,
            'direccion' => 'Los Alerces 120',
            'telefono' => '+56 2 2345 6789',
            'email_contacto' => 'contacto@vetaraucarias.cl',
        ])->assertRedirect(route('adm_cm.home'));

        $institucion = Instituciones::where('id_usuario', $usuario->id)->first();

        $this->assertNotNull($institucion);
        $this->assertSame('76156756-K', $institucion->rut);
        $this->assertSame('Servicios veterinarios', $institucion->giro);
        $this->assertTrue($this->cuentas->perfilCompleto($usuario, 'clinica'));

        // instituciones y admin_inst_serv son MyISAM: se limpian a mano.
        Instituciones::where('id_usuario', $usuario->id)->delete();
        \DB::table('admin_inst_serv')->where('email', $usuario->email)->delete();
    }

    public function test_al_agregar_un_segundo_rol_aparece_su_card_y_pide_completar_el_perfil(): void
    {
        $usuario = $this->crearUsuario(['tutor']);

        // Mismo usuario, mismo RUT y misma contraseña: solo se suma el rol.
        $this->cuentas->asignarRol($usuario, 'profesional');

        $this->assertSame(1, User::where('rut', '18777349-6')->count());
        $this->assertCount(2, $this->cuentas->rolesActivos($usuario));

        $respuesta = $this->actingAs($usuario)->get(route('cuenta.seleccion'));

        $respuesta->assertOk();
        $respuesta->assertSee('Tutor');
        $respuesta->assertSee('Profesional');

        $this->actingAs($usuario)
            ->get(route('cuenta.entrar', ['tipo' => 'profesional']))
            ->assertRedirect(route('perfil.completar', ['tipo' => 'profesional']));
    }
}
