<?php

namespace App\Services;

use App\Models\{Bono, ConvenioVeterinario, HoraMedica};
use Illuminate\Validation\ValidationException;

class DescuentoConvenioVeterinario
{
    public function aplicar(Bono $bono, HoraMedica $hora, ?int $idConvenio, int $valor): void
    {
        if ($valor < 0) throw ValidationException::withMessages(['valor_atencion' => 'El valor no puede ser negativo.']);
        $bono->valor_atencion = $valor;
        $bono->a_pagar = $valor;
        if (!$idConvenio) {
            // Si se edita un pago, quitar el convenio también quita su descuento.
            if ($bono->id_convenio_veterinario) {
                $bono->id_convenio_veterinario = null;
                $bono->nombre_convenio_veterinario = null;
                $bono->porcentaje_convenio_veterinario = null;
                $bono->descuento_convenio_veterinario = null;
            }
            return;
        }
        $convenio = ConvenioVeterinario::whereKey($idConvenio)
            ->where('id_profesional', $hora->id_profesional)
            ->where('activo', true)
            ->where(fn ($q) => $q->whereNull('id_lugar_atencion')->orWhere('id_lugar_atencion', $hora->id_lugar_atencion))
            ->first();
        if (!$convenio) throw ValidationException::withMessages(['convenio_veterinario_id' => 'El convenio no está disponible para esta atención.']);
        $descuento = (int) round($valor * (float) $convenio->porcentaje / 100, 0, PHP_ROUND_HALF_UP);
        $bono->id_convenio_veterinario = $convenio->id;
        $bono->nombre_convenio_veterinario = $convenio->nombre;
        $bono->porcentaje_convenio_veterinario = $convenio->porcentaje;
        $bono->descuento_convenio_veterinario = $descuento;
        $bono->a_pagar = $valor - $descuento;
    }
}
