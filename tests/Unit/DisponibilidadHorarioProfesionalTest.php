<?php

namespace Tests\Unit;

use App\Services\DisponibilidadHorarioProfesional;
use PHPUnit\Framework\TestCase;

class DisponibilidadHorarioProfesionalTest extends TestCase
{
    public function test_semana_sin_agendas_tiene_siete_dias_libres(): void
    {
        $libres = (new DisponibilidadHorarioProfesional())->calcular([]);
        $this->assertCount(7, $libres);
        $this->assertSame(['dia' => 0, 'hora_inicio' => '08:00', 'hora_termino' => '20:00'], $libres[6]);
    }

    public function test_combina_lugares_cruces_y_dias_multiples_sin_falsos_huecos(): void
    {
        $horarios = [
            (object) ['dia' => '1,0', 'hora_inicio' => '08:00:01', 'hora_termino' => '10:00:00', 'id_lugar_atencion' => 1],
            (object) ['dia' => '1', 'hora_inicio' => '09:00:01', 'hora_termino' => '11:00:00', 'id_lugar_atencion' => 2],
            (object) ['dia' => '1', 'hora_inicio' => '11:00:01', 'hora_termino' => '12:00:00', 'id_lugar_atencion' => 2],
            (object) ['dia' => '7', 'hora_inicio' => '14:00:01', 'hora_termino' => '21:00:00'],
            (object) ['dia' => '2', 'hora_inicio' => '06:00:01', 'hora_termino' => '22:00:00'],
        ];
        $libres = (new DisponibilidadHorarioProfesional())->calcular($horarios);
        $this->assertSame([['dia' => 1, 'hora_inicio' => '12:00', 'hora_termino' => '20:00']], array_values(array_filter($libres, fn ($b) => $b['dia'] === 1)));
        $this->assertSame([['dia' => 0, 'hora_inicio' => '10:00', 'hora_termino' => '14:00']], array_values(array_filter($libres, fn ($b) => $b['dia'] === 0)));
        $this->assertSame([], array_values(array_filter($libres, fn ($b) => $b['dia'] === 2)));
    }
}
