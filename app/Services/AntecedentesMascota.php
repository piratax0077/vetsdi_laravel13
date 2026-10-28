<?php

namespace App\Services;

use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AntecedentesMascota
{
    private const CAMPOS = [
        'dieta' => 'dieta',
        'enfermedades_previas' => 'enfermedad_cronica',
        'cirugias_previas' => 'cirugias',
        'esquema_inmunizaciones' => 'vacunas',
        'viajes_recientes' => 'viajes',
        'ultima_desparasitacion' => 'ultima_desparasitacion',
        'producto_desparasitacion' => 'producto_desparasitacion',
        'esterilizado' => 'esterilizado',
        'vive_con_animales' => 'vive_con_animales',
    ];

    public function datos(?Mascota $mascota): array
    {
        $datos = [];
        foreach (self::CAMPOS as $campo => $atributo) {
            $valor = $mascota?->{$atributo};
            if (in_array($campo, ['esterilizado', 'vive_con_animales'], true)) {
                $valor = $valor === null ? '' : ($valor ? 'Si' : 'No');
            } elseif ($valor instanceof \DateTimeInterface) {
                $valor = $valor->format('Y-m-d');
            }
            $datos[$campo] = $valor ?? '';
        }
        return $datos;
    }

    public function guardar(Mascota $mascota, Request $request): void
    {
        $reglas = array_fill_keys(array_keys(self::CAMPOS), 'nullable|string');
        $reglas['ultima_desparasitacion'] = 'nullable|date_format:Y-m-d|before_or_equal:today';
        $reglas['producto_desparasitacion'] = 'nullable|string|max:255';
        foreach (['esterilizado', 'vive_con_animales'] as $campo) {
            $reglas[$campo] = 'nullable|in:Si,No,1,0';
        }
        $datos = Validator::make($request->only(array_keys(self::CAMPOS)), $reglas)->validate();
        foreach ($datos as $campo => $valor) {
            // An empty control must not erase an existing clinical antecedent.
            if ($valor === null || $valor === '') continue;
            if (in_array($campo, ['esterilizado', 'vive_con_animales'], true)) {
                $valor = in_array((string) $valor, ['Si', '1'], true);
            }
            $mascota->{self::CAMPOS[$campo]} = $valor;
        }
        if ($mascota->isDirty()) $mascota->saveOrFail();
    }
}
