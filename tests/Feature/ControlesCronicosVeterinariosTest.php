<?php

namespace Tests\Feature;

use App\Http\Controllers\ControlCronicoVeterinarioController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class ControlesCronicosVeterinariosTest extends TestCase
{
    public function test_historial_separa_mascotas_del_mismo_tutor_y_nombres_de_otras_condiciones(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('fichas_atenciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mascota');
            $table->unsignedBigInteger('id_paciente');
        });
        (require database_path('migrations/2026_11_07_000003_create_vet_controles_cronicos.php'))->up();
        DB::table('fichas_atenciones')->insert([
            ['id' => 1, 'id_mascota' => 9, 'id_paciente' => 1],
            ['id' => 2, 'id_mascota' => 9, 'id_paciente' => 1],
            ['id' => 3, 'id_mascota' => 10, 'id_paciente' => 1],
        ]);

        $control = app(ControlCronicoVeterinarioController::class);
        $control->guardar(Request::create('/', 'POST', [
            'id_ficha_atencion' => 1, 'condicion' => 'epilepsia', 'mediciones' => ['crisis' => 0],
        ]));
        $registros = fn (int $ficha) => $control->historial(Request::create('/', 'GET', [
            'id_ficha_atencion' => $ficha, 'condicion' => 'epilepsia',
        ]))['registros'];
        $this->assertEquals(['crisis' => 0], $registros(2)->sole()['mediciones']);
        $this->assertTrue($registros(3)->isEmpty());

        foreach (['Patología A', 'Patología B'] as $nombre) {
            $control->guardar(Request::create('/', 'POST', [
                'id_ficha_atencion' => 1, 'condicion' => 'otra', 'otra' => $nombre, 'observaciones' => 'Control '.$nombre,
            ]));
        }
        $respuesta = $control->historial(Request::create('/', 'GET', [
            'id_ficha_atencion' => 2, 'condicion' => 'otra', 'otra' => 'Patología B',
        ]));
        $this->assertSame('Control Patología B', $respuesta['registros']->sole()['observaciones']);
    }
}
