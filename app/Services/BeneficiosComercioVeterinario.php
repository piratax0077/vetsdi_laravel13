<?php

namespace App\Services;

use Illuminate\Support\Facades\{Http, Log};

class BeneficiosComercioVeterinario
{
    public function consultar(string $correo): array
    {
        $vouchers = []; $fuentes = []; $tiendas = [];
        foreach (['veterfarma'=>['farmacia','Veterfarma','/api/v1/vouchers/available'], 'alimentos'=>['alimentos','Alimentos-Vet','/api/vouchers/available']] as $clave=>[$config,$nombre,$ruta]) {
            $base = preg_replace('#/sso/vet-sdi/?$#', '', rtrim((string) config('services.sdi_sso.'.$config.'_url'), '/'));
            $url = config('services.sdi_sso.'.$config.'_api_url') ?: $base.$ruta;
            $conectado = false; $cantidad = 0;
            try {
                $respuesta = Http::acceptJson()->connectTimeout(2)->timeout(5)->get($url, ['email'=>mb_strtolower(trim($correo))]);
                $lista = $respuesta->json('data');
                $conectado = $respuesta->successful() && is_array($lista) && array_is_list($lista);
                if ($conectado) {
                    foreach ($lista as $voucher) {
                        if (!is_array($voucher) || empty($voucher['code']) || !isset($voucher['value'], $voucher['discount_type'])) {
                            $conectado = false;
                            break;
                        }
                    }
                }
                if ($conectado) {
                    foreach ($lista as $voucher) {
                        $vouchers[] = array_merge($voucher, ['provider'=>$clave, 'provider_name'=>$nombre, 'store_url'=>$base.'/']);
                        $cantidad++;
                    }
                }
            } catch (\Throwable $error) {
                Log::warning('No fue posible consultar beneficios externos.', ['fuente'=>$clave, 'tipo'=>get_class($error)]);
            }
            $fuentes[$clave] = $conectado;
            $tiendas[$clave] = ['nombre'=>$nombre, 'conectado'=>$conectado, 'cantidad'=>$cantidad];
        }
        return ['estado'=>1, 'vouchers'=>$vouchers, 'fuentes'=>$fuentes, 'tiendas'=>$tiendas];
    }
}
