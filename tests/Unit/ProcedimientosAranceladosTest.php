<?php

namespace Tests\Unit;

use App\Services\ProcedimientosArancelados;
use PHPUnit\Framework\TestCase;

class ProcedimientosAranceladosTest extends TestCase
{
    public function test_solo_ofrece_activos_con_precio_e_incluye_iva_y_tamano(): void
    {
        $base = ['id' => 'proc', 'nombre' => 'Cirugía', 'activo' => true, 'valorNeto' => 10000, 'afectoIva' => true, 'duracionMin' => 45];
        $estado = ['config' => ['parametros' => ['iva' => 19], 'tamanos' => [['id' => 'mini', 'nombre' => 'Mini']]], 'procedimientos' => [
            $base,
            array_replace($base, ['activo' => false]),
            array_replace($base, ['valorNeto' => 0]),
            array_replace($base, ['id' => 'variable', 'variaPorTamano' => true, 'preciosTamano' => [
                ['tamanoId' => 'mini', 'activo' => true, 'valorNeto' => 20000, 'afectoIva' => false],
                ['tamanoId' => 'grande', 'activo' => false, 'valorNeto' => 30000],
            ]]),
        ]];
        $opciones = (new ProcedimientosArancelados())->opciones($estado);
        $this->assertCount(2, $opciones);
        $this->assertSame(11900, $opciones[0]['valor']);
        $this->assertSame(45, $opciones[0]['duracion']);
        $this->assertSame('variable:mini', $opciones[1]['id']);
        $this->assertSame('Cirugía — Mini', $opciones[1]['nombre']);
        $this->assertSame(20000, $opciones[1]['valor']);
    }
}
