<?php

namespace App\Services;

class DisponibilidadHorarioProfesional
{
    public function calcular(iterable $horarios): array
    {
        $ocupados = array_fill(0, 7, []);
        foreach ($horarios as $horario) {
            $inicio = substr($horario->hora_inicio, 0, 5);
            $fin = substr($horario->hora_termino, 0, 5);
            foreach (explode(',', (string) $horario->dia) as $dia) {
                $dia = trim($dia);
                if ($dia === '' || !in_array($dia, ['0', '1', '2', '3', '4', '5', '6', '7'], true)) continue;
                $ocupados[(int) $dia % 7][] = [$inicio, $fin];
            }
        }

        $disponibles = [];
        foreach ([1, 2, 3, 4, 5, 6, 0] as $dia) {
            sort($ocupados[$dia]);
            $cursor = '08:00';
            foreach ($ocupados[$dia] as [$inicio, $fin]) {
                $inicio = max('08:00', $inicio);
                $fin = min('20:00', $fin);
                if ($inicio >= $fin) continue;
                if ($cursor < $inicio) {
                    $disponibles[] = ['dia' => $dia, 'hora_inicio' => $cursor, 'hora_termino' => $inicio];
                }
                $cursor = max($cursor, $fin);
            }
            if ($cursor < '20:00') {
                $disponibles[] = ['dia' => $dia, 'hora_inicio' => $cursor, 'hora_termino' => '20:00'];
            }
        }
        return $disponibles;
    }
}
