<?php

namespace App\Support;

use App\Models\Mascota;
use Illuminate\Validation\ValidationException;

final class DenticionVeterinaria
{
    public static function especie(Mascota $mascota): string
    {
        $mascota->loadMissing('especieMascota');
        $nombre = mb_strtolower((string) ($mascota->especieMascota?->nombre ?: $mascota->otra_especie ?: $mascota->especie));
        if (str_contains($nombre, 'felin') || str_contains($nombre, 'gato')) return 'felino';
        if (str_contains($nombre, 'canin') || str_contains($nombre, 'perro')) return 'canino';
        throw ValidationException::withMessages(['especie' => 'El odontograma está disponible para mascotas caninas y felinas.']);
    }

    // Permanent dentition, using the modified Triadan system (AVDC nomenclature).
    public static function arcadas(string $especie): array
    {
        if ($especie === 'felino') {
            return [
                'superior' => [109,108,107,106,104,103,102,101,201,202,203,204,206,207,208,209],
                'inferior' => [409,408,407,404,403,402,401,301,302,303,304,307,308,309],
            ];
        }
        if ($especie === 'canino') {
            return [
                'superior' => array_merge(range(110,101), range(201,210)),
                'inferior' => array_merge(range(411,401), range(301,311)),
            ];
        }
        throw ValidationException::withMessages(['especie' => 'Especie dental no soportada.']);
    }

    public static function piezas(string $especie): array
    {
        return array_merge(...array_values(self::arcadas($especie)));
    }
}
