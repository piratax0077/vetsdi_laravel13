<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La fecha de nacimiento del contacto de emergencia ya no se pide en el
 * formulario del asistente, así que la columna pasa a ser opcional.
 * Los contactos que ya la tienen la conservan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `contactos_emergencia` MODIFY `fecha_nac` DATE NULL');
    }

    public function down(): void
    {
        // Se vuelve a NOT NULL solo si no quedaron contactos sin fecha.
        if (DB::table('contactos_emergencia')->whereNull('fecha_nac')->exists()) {
            return;
        }

        DB::statement('ALTER TABLE `contactos_emergencia` MODIFY `fecha_nac` DATE NOT NULL');
    }
};
