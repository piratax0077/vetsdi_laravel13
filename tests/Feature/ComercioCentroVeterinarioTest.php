<?php

namespace Tests\Feature;

use App\Services\ComercioCentroVeterinario;
use Tests\TestCase;

class ComercioCentroVeterinarioTest extends TestCase
{
    public function test_destinos_comerciales_se_configuran_por_separado(): void
    {
        config(['services.comercio_centro.alimentos_url'=>'https://alimentos.example.test/ventas',
            'services.comercio_centro.farmacia_url'=>'https://farmacia.example.test/centros',
            'services.comercio_centro.cuidados_url'=>'https://accesorios.example.test/ventas']);
        $enlaces = app(ComercioCentroVeterinario::class)->enlaces();
        $this->assertSame('https://alimentos.example.test/ventas',$enlaces[0]['url']);
        $this->assertSame('https://farmacia.example.test/centros',$enlaces[1]['url']);
        $this->assertSame('Accesorios',$enlaces[2]['nombre']);
    }

    public function test_destinos_ausentes_o_invalidos_no_generan_enlaces_rotos(): void
    {
        config(['services.comercio_centro.alimentos_url'=>'',
            'services.comercio_centro.farmacia_url'=>'javascript:alert(1)',
            'services.comercio_centro.cuidados_url'=>'https://user:password@example.test/']);
        foreach (app(ComercioCentroVeterinario::class)->enlaces() as $enlace) $this->assertNull($enlace['url']);
    }
}
