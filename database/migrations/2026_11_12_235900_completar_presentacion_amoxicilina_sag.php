<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PRESENTACION = 'Suspensión inyectable de larga duración';

    public function up(): void
    {
        $this->articuloSag()->whereNull('present')->update(['present' => self::PRESENTACION]);
    }

    public function down(): void
    {
        $this->articuloSag()->where('present', self::PRESENTACION)->update(['present' => null]);
    }

    private function articuloSag()
    {
        // Registro SAG 0025-B. El catálogo local no guarda el número de registro.
        return DB::table('articulos')
            ->where('nombre', 'Amoxicilina 15%')
            ->where('grupo', 'Bovino | Perro | Gato | Ovino | Cerdo')
            ->where('temperatura', 'Carne: Bovinos: 55 días; Ovinos y Cerdos: 30 días. Leche: 4 días.');
    }
};
