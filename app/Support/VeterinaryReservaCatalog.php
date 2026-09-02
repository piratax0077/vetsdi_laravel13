<?php

namespace App\Support;

class VeterinaryReservaCatalog
{
    public static function areas(): array
    {
        return config('vet_reserva_areas', []);
    }
}

