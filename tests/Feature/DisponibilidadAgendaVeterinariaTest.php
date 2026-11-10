<?php

namespace Tests\Feature;

use App\Models\HoraMedica;
use App\Services\AccesoAgendaVeterinaria;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DisponibilidadAgendaVeterinariaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        foreach ([
            'profesionales' => [],
            'profesional_horarios' => ['id_profesional', 'id_lugar_atencion', 'tipo_agenda', 'dia', 'duracion_consulta', 'hora_inicio', 'hora_termino'],
            'profesional_horarios_bloqueo' => ['id_profesional', 'id_lugar_atencion', 'fecha_inicio', 'fecha_termino', 'hora_inicio', 'hora_termino', 'todo_dia'],
            'horas_medicas' => ['id_profesional', 'id_lugar_atencion', 'fecha_consulta', 'hora_inicio', 'hora_termino', 'tipo_hora_medica', 'id_estado', 'fecha_realizacion_consulta'],
        ] as $tabla => $campos) {
            Schema::create($tabla, function (Blueprint $t) use ($campos) {
                $t->id();
                foreach ($campos as $campo) $t->string($campo)->nullable();
                $t->timestamps();
            });
        }
        DB::table('profesionales')->insert(['id' => 1]);
        $this->travelTo(\Carbon\Carbon::parse('2026-11-07 08:00:00'));
    }

    private function hora(string $inicio, string $fin, string $fecha = '2026-11-08'): HoraMedica
    {
        return (new HoraMedica())->forceFill([
            'id_profesional' => 1, 'id_lugar_atencion' => 2, 'fecha_consulta' => $fecha,
            'hora_inicio' => $inicio, 'hora_termino' => $fin, 'tipo_hora_medica' => 'C', 'id_estado' => 1,
        ]);
    }

    public function test_videoconsulta_sigue_disponible_durante_la_atencion(): void
    {
        $hora = $this->hora('10:00:00', '10:30:00')->forceFill([
            'id_mascota' => 1, 'tipo_hora_medica' => 'T',
        ]);

        foreach ([2, 4, 5, 8] as $estado) {
            $hora->id_estado = $estado;
            $this->assertTrue($hora->permiteVideoconsulta());
        }

        foreach ([1, 3, 6, 14, 15] as $estado) {
            $hora->id_estado = $estado;
            $this->assertFalse($hora->permiteVideoconsulta());
        }
    }

    public function test_reserva_rechaza_tramo_fuera_de_turno_y_bloqueo_y_admite_cita_contigua(): void
    {
        DB::table('profesional_horarios')->insert([
            'id_profesional' => 1, 'id_lugar_atencion' => 2, 'tipo_agenda' => 1,
            'dia' => '0,1', 'duracion_consulta' => '00:30:00', 'hora_inicio' => '10:00:00', 'hora_termino' => '12:00:00',
        ]);
        DB::table('profesional_horarios_bloqueo')->insert([
            'id_profesional' => 1, 'id_lugar_atencion' => 2, 'fecha_inicio' => '2026-11-08', 'fecha_termino' => '2026-11-08',
            'hora_inicio' => '10:30:00', 'hora_termino' => '11:00:00', 'todo_dia' => 0,
        ]);
        $agenda = app(AccesoAgendaVeterinaria::class);
        foreach ([['09:30:00','10:00:00'], ['10:30:00','11:00:00'], ['11:45:00','12:15:00'], ['11:15:00','11:45:00']] as [$inicio,$fin]) {
            try {
                $agenda->guardarReserva($this->hora($inicio, $fin), 30);
                $this->fail('La cita no debió guardarse.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('fecha_consulta', $error->errors());
            }
        }
        $this->assertTrue($agenda->guardarReserva($this->hora('11:00:00','11:30:00'), 30));
        $this->assertTrue($agenda->guardarReserva($this->hora('11:30:00','12:00:00'), 30));
        $this->assertSame(2, HoraMedica::count());
    }

    public function test_realizada_permanece_el_dia_real_de_atencion_y_sale_al_dia_siguiente(): void
    {
        $realizada = $this->hora('10:00:00', '10:30:00', '2026-11-16');
        $realizada->id_estado = 6;
        $realizada->fecha_realizacion_consulta = '2026-11-07 09:15:00';
        $realizada->saveOrFail();
        $futura = $this->hora('11:00:00', '11:30:00');
        $futura->saveOrFail();
        $this->assertSame([$realizada->id, $futura->id], HoraMedica::proximasCitas()->orderBy('id')->pluck('id')->all());
        $this->travel(1)->day();
        $this->assertSame([$futura->id], HoraMedica::proximasCitas()->pluck('id')->all());
        $this->assertTrue(HoraMedica::whereKey($realizada->id)->exists());
    }
}
