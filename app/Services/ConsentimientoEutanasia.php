<?php

namespace App\Services;

use App\Models\ConConsentimientos;

class ConsentimientoEutanasia
{
    public function plantilla(): ConConsentimientos
    {
        $plantilla = ConConsentimientos::where('estado', 1)->where('nombre', 'like', '%eutanasia%')->orderBy('id')->first();
        if (!$plantilla) {
            $plantilla = new ConConsentimientos();
            $plantilla->nombre = 'Consentimiento informado veterinario para eutanasia de mascotas';
            $plantilla->texto = view('general.modal.partials.texto_eutanasia')->render();
            $plantilla->estado = 1;
            $plantilla->saveOrFail();
        }
        return $plantilla;
    }
}
