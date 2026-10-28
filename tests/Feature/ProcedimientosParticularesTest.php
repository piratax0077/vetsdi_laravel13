<?php

namespace Tests\Feature;

use App\Http\Controllers\ProcedimientosProfesionalController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProcedimientosParticularesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'profesionales' => ['id_usuario'],
            'lugares_atencion' => ['nombre'],
            'profesionales_lugares_atencion' => ['id_profesional', 'id_lugar_atencion', 'id_institucion', 'estado'],
            'sucursal' => ['id_lugar_atencion', 'id_institucion'],
            'asistentes_lugar_atencion' => ['id_lugar_atencion', 'id_institucion'],
            'administrativos_lugar_atencion' => ['id_lugar_atencion', 'id_institucion'],
            'especialidades_cm' => ['id_lugar_atencion', 'id_institucion'],
            'procedimientos_centro' => ['id_lugar_atencion', 'nombre', 'descripcion', 'minutos_bloque', 'cantidad_bloques', 'tipo_ficha_atencion', 'estado'],
            'procedimientos_lugar_atencion_profesional' => ['id_procedimiento_centro', 'id_lugar_atencion', 'id_profesional', 'nombre', 'descripcion', 'minutos_bloque', 'cantidad_bloques', 'otros', 'estado'],
        ] as $table => $columns) {
            Schema::create($table, function (Blueprint $t) use ($columns) {
                $t->id();
                foreach ($columns as $column) $t->string($column)->nullable();
                $t->timestamps();
            });
        }
        $user = new User();
        $user->id = 5;
        $this->actingAs($user);
        DB::table('profesionales')->insert(['id' => 1, 'id_usuario' => 5]);
        DB::table('lugares_atencion')->insert(['id' => 10, 'nombre' => 'Consulta particular']);
        DB::table('profesionales_lugares_atencion')->insert(['id_profesional' => 1, 'id_lugar_atencion' => 10, 'estado' => 1]);
    }

    private function guardar(array $datos = [])
    {
        return app(ProcedimientosProfesionalController::class)->store(new Request(array_merge([
            'id_lugar_atencion' => 10, 'nombre' => 'Ecografía', 'minutos_bloque' => 15, 'cantidad_bloques' => 2,
        ], $datos)));
    }

    public function test_particular_guarda_nombre_sin_exigir_catalogo_y_evita_duplicados(): void
    {
        $this->assertSame(1, $this->guardar()->getData()->estado);
        $this->assertDatabaseHas('procedimientos_lugar_atencion_profesional', ['nombre' => 'Ecografía', 'id_lugar_atencion' => 10]);
        $this->assertSame(422, $this->guardar()->getStatusCode());
        $this->assertSame(1, DB::table('procedimientos_centro')->count());
    }

    public function test_centro_exige_procedimiento_del_catalogo(): void
    {
        DB::table('sucursal')->insert(['id_lugar_atencion' => 10, 'id_institucion' => 3]);
        $this->expectException(ValidationException::class);
        $this->guardar();
    }

    public function test_centro_guarda_el_nombre_del_catalogo(): void
    {
        DB::table('sucursal')->insert(['id_lugar_atencion' => 10, 'id_institucion' => 3]);
        DB::table('procedimientos_centro')->insert(['id' => 7, 'id_lugar_atencion' => 10, 'nombre' => 'Radiografía', 'estado' => 1]);
        $this->assertSame('Radiografía', $this->guardar(['id_procedimiento_centro' => 7])->getData()->procedimiento->nombre);
    }
}
