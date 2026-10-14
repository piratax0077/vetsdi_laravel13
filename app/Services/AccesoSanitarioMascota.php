<?php

namespace App\Services;

use App\Models\{FichaAtencion, HoraMedica, Mascota, Profesional};
use Illuminate\Support\Facades\Auth;

class AccesoSanitarioMascota
{
    public function puedeEscribir(Mascota $mascota): bool
    {
        $usuario = Auth::user();
        if (!$usuario || !$usuario->hasRole('Profesional')) return false;
        $profesional = Profesional::where('id_usuario', $usuario->id)->first();
        if (!$profesional) return false;
        return HoraMedica::where('id_profesional', $profesional->id)
            ->where('id_mascota', $mascota->id)->where('id_paciente', $mascota->id_responsable)->exists()
            || FichaAtencion::where('id_profesional', $profesional->id)
                ->where('id_mascota', $mascota->id)->where('id_paciente', $mascota->id_responsable)->exists();
    }

    public function exigirEscritura(Mascota $mascota): void
    {
        abort_unless($this->puedeEscribir($mascota), 403, 'Solo el profesional autorizado puede modificar los registros sanitarios.');
    }
}
