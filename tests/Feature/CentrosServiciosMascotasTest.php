<?php

namespace Tests\Feature;

use App\Models\{CentroServiciosMascotas, User};
use App\Services\CuentasService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\{CuentasAisladas, TestCase};

class CentrosServiciosMascotasTest extends TestCase
{
    use CuentasAisladas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepararCuentasAisladas();
        (require database_path('migrations/2026_11_07_000004_create_centros_servicios_mascotas.php'))->up();
        Schema::table('mascotas', fn (Blueprint $table) => $table->boolean('fallecida')->default(false));
        foreach ([1,2,3] as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Usuario '.$id, 'email' => $id.'@example.test', 'email_verified_at' => now()]);
        }
        foreach ([2,3] as $id) {
            $this->rol($id, 'tutor');
            DB::table('pacientes')->insert(['id' => $id, 'id_usuario' => $id, 'nombres' => 'Tutor '.$id]);
            DB::table('mascotas')->insert(['id' => $id, 'id_responsable' => $id, 'nombre' => 'Mascota '.$id, 'estado' => 1]);
        }
        $this->travelTo(\Carbon\Carbon::parse('2026-11-09 12:00:00', 'America/Santiago'));
    }

    private function rol(int $usuario, string $tipo): void
    {
        $cuentas = app(CuentasService::class);
        $cuentas->asignarRol(User::findOrFail($usuario), $tipo);
        $cuentas->marcarPerfilCompleto(User::findOrFail($usuario), $tipo);
    }

    private function centro(string $tipo, array $config): CentroServiciosMascotas
    {
        $this->rol(1, $tipo);

        return CentroServiciosMascotas::create([
            'id_usuario' => 1, 'tipo' => $tipo, 'nombre' => 'Centro de prueba', 'direccion' => 'Calle Uno',
            'telefono' => '999999999', 'email' => 'centro@example.test', 'publicado' => true, 'configuracion' => $config,
        ]);
    }

    public function test_guarderia_confirma_por_cupo_y_registra_retiro_e_informe_antes_de_cerrar(): void
    {
        $centro = $this->centro('guarderia_hotel', [
            'cupos_guarderia' => 1, 'dias' => [1,2,3,4,5], 'ofrece_retiro' => true, 'ofrece_peluqueria' => true,
            'precio_semana' => 10000, 'precio_retiro' => 2000, 'precio_peluqueria' => 3000,
        ]);
        foreach ([2,3] as $id) {
            $this->actingAs(User::findOrFail($id))->post(route('paciente.centros_servicios.solicitar', $centro->id), [
                'servicio' => 'guarderia', 'mascota_id' => $id, 'inicio' => '2026-11-09', 'periodo' => 'semana',
                'cantidad' => 2, 'retiro' => 1, 'direccion_retiro' => 'Calle del tutor', 'peluqueria' => 1,
            ])->assertRedirect();
        }
        $primera = DB::table('servicios_mascotas_solicitudes')->where('tutor_id', 2)->first();
        $segunda = DB::table('servicios_mascotas_solicitudes')->where('tutor_id', 3)->first();
        $this->assertSame('2026-11-23', $primera->fin);
        $this->assertEquals(25000, $primera->precio);

        $this->actingAs(User::findOrFail(3))->get(route('paciente.centros_servicios.detalle', $primera->id))->assertNotFound();
        $this->actingAs(User::findOrFail(1));
        $ruta = route('guarderia_hotel.operar', $primera->id);
        $this->post($ruta, ['accion' => 'confirmar'])->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('guarderia_hotel.operar', $segunda->id), ['accion' => 'confirmar'])->assertSessionHasErrors('inicio');
        $this->post($ruta, ['accion' => 'iniciar'])->assertSessionHasNoErrors();
        $this->post($ruta, ['accion' => 'finalizar'])->assertSessionHasErrors('accion');
        $this->post($ruta, ['accion' => 'retiro', 'detalle' => 'Mascota retirada por recepción.'])->assertSessionHasNoErrors();
        $this->post($ruta, ['accion' => 'peluqueria', 'detalle' => 'Visita de peluquería realizada.'])->assertSessionHasNoErrors();
        $this->post($ruta, ['accion' => 'finalizar'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('servicios_mascotas_solicitudes', ['id' => $primera->id, 'estado' => 'finalizada']);
    }

    public function test_activa_perfil_y_configura_plan_mensual_y_alojamiento(): void
    {
        $this->actingAs(User::findOrFail(1))->post(route('centros_servicios.activar'), [
            'tipo' => 'guarderia_hotel',
        ])->assertRedirect(route('cuenta.entrar', 'guarderia_hotel'));
        $datos = ['nombre' => 'Guardería Uno', 'direccion' => 'Calle Uno', 'telefono' => '123', 'email' => 'centro@example.test'];
        $this->post(route('perfil.completar.guardar', 'guarderia_hotel'), $datos)
            ->assertRedirect(route('guarderia_hotel.home'));
        $this->post(route('guarderia_hotel.configuracion.guardar'), $datos + [
            'publicado' => 1, 'cupos_guarderia' => 2, 'cupos_hotel' => 3, 'dias' => [1,2,3,4,5],
            'precio_mes' => 45000, 'precio_noche' => 18000,
        ])->assertSessionHasNoErrors();
        $centro = CentroServiciosMascotas::sole();
        $this->actingAs(User::findOrFail(2))->post(route('paciente.centros_servicios.solicitar', $centro->id), [
            'mascota_id' => 2, 'servicio' => 'guarderia', 'inicio' => '2026-11-09', 'periodo' => 'mes', 'cantidad' => 1,
        ])->assertSessionHasNoErrors();
        $this->actingAs(User::findOrFail(3))->post(route('paciente.centros_servicios.solicitar', $centro->id), [
            'mascota_id' => 3, 'servicio' => 'hotel', 'inicio' => '2026-11-09', 'fin' => '2026-11-12',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('servicios_mascotas_solicitudes', ['servicio' => 'guarderia', 'fin' => '2026-12-09', 'precio' => 45000]);
        $this->assertDatabaseHas('servicios_mascotas_solicitudes', ['servicio' => 'hotel', 'precio' => 54000]);
    }

    public function test_cementerio_vende_un_lugar_una_vez_y_permite_el_entierro_al_comprador(): void
    {
        $centro = $this->centro('cementerio_mascotas', ['ofrece_entierro' => true, 'precio_entierro' => 30000]);
        $this->actingAs(User::findOrFail(1))->post(route('cementerio_mascotas.inventario.guardar'), [
            'codigo' => 'A-01', 'sector' => 'Jardín', 'precio' => 45000, 'disponible' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $lugar = DB::table('cementerio_lugares')->value('id');
        foreach ([2,3] as $id) {
            $this->actingAs(User::findOrFail($id))->post(route('paciente.centros_servicios.solicitar', $centro->id), [
                'servicio' => 'lugar', 'mascota_id' => $id, 'inicio' => '2026-11-09', 'lugar_id' => $lugar,
            ])->assertRedirect()->assertSessionHasNoErrors();
        }
        $venta = DB::table('servicios_mascotas_solicitudes')->where('tutor_id', 2)->value('id');
        $otraVenta = DB::table('servicios_mascotas_solicitudes')->where('tutor_id', 3)->value('id');
        $this->actingAs(User::findOrFail(1))->post(route('cementerio_mascotas.operar', $venta), ['accion' => 'confirmar'])->assertSessionHasNoErrors();
        $this->post(route('cementerio_mascotas.operar', $otraVenta), ['accion' => 'confirmar'])->assertSessionHasErrors('lugar_id');

        DB::table('mascotas')->where('id', 2)->update(['fallecida' => true]);
        $this->actingAs(User::findOrFail(2))->post(route('paciente.centros_servicios.solicitar', $centro->id), [
            'servicio' => 'entierro', 'mascota_id' => 2, 'inicio' => '2026-11-10', 'lugar_id' => $lugar,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('servicios_mascotas_solicitudes', ['servicio' => 'entierro', 'precio' => 30000, 'lugar_id' => $lugar]);
    }
}
