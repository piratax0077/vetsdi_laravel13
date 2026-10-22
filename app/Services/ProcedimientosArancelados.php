<?php

namespace App\Services;

class ProcedimientosArancelados
{
    public function opciones(array $estado): array
    {
        $opciones = [];
        $config = $estado['config'] ?? [];
        $tamanos = array_column($config['tamanos'] ?? [], 'nombre', 'id');
        foreach ($estado['procedimientos'] ?? [] as $item) {
            if (empty($item['activo'])) continue;
            $precios = !empty($item['variaPorTamano']) ? ($item['preciosTamano'] ?? []) : [$item];
            foreach ($precios as $precio) {
                if (!empty($item['variaPorTamano']) && empty($precio['activo'])) continue;
                $base = (int) ($precio['valorNeto'] ?? 0);
                if ($base <= 0) continue;
                $tamano = !empty($item['variaPorTamano']) ? ($precio['tamanoId'] ?? '') : '';
                $valor = $base + (!empty($precio['afectoIva']) ? (int) round($base * ($config['parametros']['iva'] ?? 19) / 100) : 0);
                $opciones[] = [
                    'id' => $item['id'].($tamano !== '' ? ':'.$tamano : ''),
                    'nombre' => $item['nombre'].($tamano !== '' ? ' — '.($tamanos[$tamano] ?? $tamano) : ''),
                    'valor' => $valor,
                    'duracion' => $item['duracionMin'] ?? 15,
                ];
            }
        }
        return $opciones;
    }
}
