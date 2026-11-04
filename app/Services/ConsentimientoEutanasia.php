<?php

namespace App\Services;

use App\Models\ConConsentimientos;
use Illuminate\Support\Facades\DB;

class ConsentimientoEutanasia
{
    public function plantilla(): ConConsentimientos
    {
        return DB::transaction(function () {
            $plantilla = ConConsentimientos::where('estado', 1)->where('nombre', 'like', '%eutanasia%')->orderBy('id')->first();
            if (!$plantilla) {
                $ultima = ConConsentimientos::orderByDesc('id_consent')->lockForUpdate()->first();
                $plantilla = new ConConsentimientos();
                $plantilla->id_consent = ((int) ($ultima?->id_consent ?? 0)) + 1;
                $plantilla->nombre = 'Consentimiento informado veterinario para eutanasia de mascotas';
                $plantilla->texto = view('general.modal.partials.texto_eutanasia')->render();
                $plantilla->estado = 1;
                $plantilla->saveOrFail();
            }
            return $plantilla;
        });
    }
}
