<?php

namespace Tests\Feature;

use App\Services\UltimaConsultaAgenda;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class UltimaConsultaAgendaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('fichas_atenciones', function (Blueprint $t) {
            $t->id();
            $t->boolean('finalizada');
        });
        Schema::create('horas_medicas', function (Blueprint $t) {
            $t->id();
            foreach (['id_paciente', 'id_profesional', 'id_lugar_atencion', 'id_ficha_atencion'] as $column) {
                $t->unsignedBigInteger($column)->nullable();
            }
            $t->date('fecha_consulta');
        });
        DB::table('fichas_atenciones')->insert([['id' => 1, 'finalizada' => 1], ['id' => 2, 'finalizada' => 0]]);
    }

    public function test_elige_la_fecha_mas_reciente_del_paciente_profesional_y_lugar_con_ficha_finalizada(): void
    {
        foreach ([
            [9, 4, 3, 1, '2026-10-20'],
            [9, 4, 3, 1, '2026-10-01'], // ID posterior con una fecha anterior.
            [8, 4, 3, 1, '2026-10-27'], // Otro paciente.
            [9, 5, 3, 1, '2026-10-27'], // Otro profesional.
            [9, 4, 2, 1, '2026-10-27'], // Otro lugar.
            [9, 4, 3, 2, '2026-10-27'], // Atención sin finalizar.
            [9, 4, 3, null, '2026-10-28'], // Reserva sin atención.
        ] as [$paciente, $profesional, $lugar, $ficha, $fecha]) {
            DB::table('horas_medicas')->insert([
                'id_paciente' => $paciente, 'id_profesional' => $profesional,
                'id_lugar_atencion' => $lugar, 'id_ficha_atencion' => $ficha, 'fecha_consulta' => $fecha,
            ]);
        }
        $this->assertSame('2026-10-20', app(UltimaConsultaAgenda::class)->fecha(9, 4, 3));
    }

    public function test_sin_historial_devuelve_null_en_vez_de_la_fecha_actual(): void
    {
        $this->assertNull(app(UltimaConsultaAgenda::class)->fecha(9, 4, 3));
    }
}
