<?php

namespace Tests\Feature;

use App\Http\Controllers\CentroMedicoController;
use App\Models\{Instituciones, Prevision, ProcedimientosCentro, Sucursal};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Route, Schema};
use Tests\TestCase;

class AuditoriaSistemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([Prevision::class => ['nombre'], ProcedimientosCentro::class => ['id_lugar_atencion'], Instituciones::class => ['id_lugar_atencion'], Sucursal::class => ['id_institucion']] as $modelo => $campos) {
            Schema::create((new $modelo)->getTable(), function (Blueprint $t) use ($campos) {
                $t->id();
                foreach ($campos as $campo) $t->string($campo);
            });
        }
    }

    public function test_recovered_catalogs_return_only_the_selected_center_and_sorted_previsions(): void
    {
        DB::table((new Prevision)->getTable())->insert([['nombre' => 'Zeta'], ['nombre' => 'Alfa']]);
        DB::table((new ProcedimientosCentro)->getTable())->insert([['id' => 1, 'id_lugar_atencion' => 7], ['id' => 2, 'id_lugar_atencion' => 8]]);
        DB::table((new Instituciones)->getTable())->insert(['id' => 3, 'id_lugar_atencion' => 7]);
        DB::table((new Sucursal)->getTable())->insert([['id' => 4, 'id_institucion' => 3], ['id' => 5, 'id_institucion' => 9]]);
        $controlador = app(CentroMedicoController::class);
        $this->assertSame(['Alfa', 'Zeta'], $controlador->damePrevisiones()->pluck('nombre')->all());
        $this->assertSame([1], $controlador->buscarExamenes(new Request(['id_centro_medico' => 7]))->pluck('id')->all());
        $this->assertSame([4], $controlador->buscarSucursalesLaboratorio(new Request(['id_laboratorio' => 7]))->pluck('id')->all());
    }

    public function test_unknown_laboratory_returns_not_found_instead_of_null_dereference(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(CentroMedicoController::class)->buscarSucursalesLaboratorio(new Request(['id_laboratorio' => 100]));
    }

    public function test_audit_reports_an_unimplemented_route_as_failure(): void
    {
        Route::get('/audit-only-missing', [CentroMedicoController::class, 'metodoInexistente'])->name('audit.missing');
        $this->artisan('sistema:auditar')->expectsOutputToContain('audit.missing')->assertExitCode(1);
    }

    public function test_private_profiles_and_pet_pages_reject_guests(): void
    {
        foreach (['asistentes.perfil', 'paciente.perfil', 'adm_cm.perfil_cm', 'paciente.mascotas.index'] as $nombre) {
            $respuesta = $this->getJson(route($nombre));
            $this->assertContains($respuesta->status(), [401, 403], $nombre);
        }
    }

    public function test_clinical_view_selection_stays_within_the_three_veterinary_forms(): void
    {
        foreach ([[1, 0, 'atencion_veterinaria.atencion_veterinaria_general'], [1, 119, 'atencion_veterinaria.atencion_medica_cirugia_general'], [2, 0, 'atencion_odontologica.atencion_odonto_gral'], [3, 0, 'atencion_veterinaria.atencion_veterinaria_general']] as [$especialidad, $sub, $vista]) {
            $profesional = new \App\Models\Profesional(['id_especialidad' => $especialidad, 'id_sub_tipo_especialidad' => $sub]);
            $this->assertSame($vista, \App\Support\FichaVeterinariaVista::para($profesional));
            $this->assertTrue(view()->exists($vista));
        }
    }
}
