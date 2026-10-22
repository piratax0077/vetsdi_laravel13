<?php

namespace Tests\Feature;

use App\Services\BeneficiosComercioVeterinario;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BeneficiosComercioVeterinarioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sdi_sso.alimentos_url'=>'https://alimentos.example.test/sso/vet-sdi', 'services.sdi_sso.farmacia_url'=>'https://farmacia.example.test/sso/vet-sdi', 'services.sdi_sso.alimentos_api_url'=>null, 'services.sdi_sso.farmacia_api_url'=>null]);
        Http::preventStrayRequests();
    }

    public function test_consulta_ambas_tiendas_con_correo_del_tutor_y_muestra_beneficios(): void
    {
        Http::fake([
            'alimentos.example.test/api/vouchers/available*'=>Http::response(['data'=>[['id'=>1,'code'=>'ALIMENTO','value'=>10,'discount_type'=>'percent']]]),
            'farmacia.example.test/api/v1/vouchers/available*'=>Http::response(['data'=>[['id'=>2,'code'=>'FARMACIA','value'=>5000,'discount_type'=>'fixed']]]),
        ]);
        $resultado = app(BeneficiosComercioVeterinario::class)->consultar(' Tutor@Example.Test ');
        $this->assertCount(2, $resultado['vouchers']);
        $this->assertTrue($resultado['fuentes']['alimentos']);
        $this->assertTrue($resultado['fuentes']['veterfarma']);
        $this->assertSame('Alimentos-Vet', $resultado['vouchers'][1]['provider_name']);
        Http::assertSent(fn ($r) => $r['email'] === 'tutor@example.test' && str_contains($r->url(), '/api/vouchers/available'));
    }

    public function test_conexion_sin_beneficios_se_distingue_de_servicio_caido(): void
    {
        Http::fake(['alimentos.example.test/*'=>Http::response(['data'=>[]]), 'farmacia.example.test/*'=>Http::response([],503)]);
        $resultado = app(BeneficiosComercioVeterinario::class)->consultar('tutor@example.test');
        $this->assertTrue($resultado['tiendas']['alimentos']['conectado']);
        $this->assertSame(0, $resultado['tiendas']['alimentos']['cantidad']);
        $this->assertFalse($resultado['tiendas']['veterfarma']['conectado']);
    }

    public function test_html_o_respuesta_invalida_no_se_interpreta_como_sin_beneficios(): void
    {
        Http::fake(['alimentos.example.test/*'=>Http::response('<html>Ingreso</html>',200), 'farmacia.example.test/*'=>Http::response(['data'=>[['id'=>1]]])]);
        $resultado = app(BeneficiosComercioVeterinario::class)->consultar('tutor@example.test');
        $this->assertSame(['veterfarma'=>false,'alimentos'=>false], $resultado['fuentes']);
        $this->assertSame([], $resultado['vouchers']);
    }

    public function test_endpoint_independiente_y_fallo_de_una_tienda_conservan_beneficio_de_la_otra(): void
    {
        config(['services.sdi_sso.alimentos_api_url'=>'https://api.example.test/beneficios']);
        Http::fake(['api.example.test/beneficios*'=>Http::response(['data'=>[['code'=>'OFERTA','value'=>10,'discount_type'=>'percent']]]), 'farmacia.example.test/*'=>fn () => throw new \Illuminate\Http\Client\ConnectionException('Sin conexión')]);
        $resultado = app(BeneficiosComercioVeterinario::class)->consultar('tutor@example.test');
        $this->assertCount(1, $resultado['vouchers']);
        $this->assertSame('alimentos', $resultado['vouchers'][0]['provider']);
        $this->assertFalse($resultado['fuentes']['veterfarma']);
    }
}
