<?php

namespace App\Services;

class ComercioCentroVeterinario
{
    public function enlaces(): array
    {
        $base = rtrim((string) config('services.veterfood.url'), '/');
        $enlaces = [];
        foreach (['alimentos'=>'Alimentos','farmacia'=>'Farmacia','cuidados'=>'Accesorios'] as $rubro=>$nombre) {
            $url = trim((string) config('services.comercio_centro.'.$rubro.'_url', $base.'/central-ventas/rubros/'.$rubro));
            $partes = parse_url($url);
            // Un destino aún no configurado no debe generar un enlace local roto.
            $valido = $partes && in_array(strtolower($partes['scheme'] ?? ''), ['http','https'],true)
                && !empty($partes['host']) && !isset($partes['user']) && !isset($partes['pass']);
            $enlaces[] = ['nombre'=>$nombre,'url'=>$valido ? $url : null];
        }
        return $enlaces;
    }
}
