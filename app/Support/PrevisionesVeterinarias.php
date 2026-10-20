<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PrevisionesVeterinarias
{
    public static function paraSelect($opciones): Collection
    {
        return collect($opciones)->filter(function ($opcion) {
            $nombre = Str::lower(trim((string) data_get($opcion, 'nombre')));
            return in_array($nombre, ['particular', 'sin convenio'], true);
        })->values();
    }
}
