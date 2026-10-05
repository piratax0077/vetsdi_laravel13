<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deja como opcionales los campos que el registro nuevo no pide.
 *
 * Son datos heredados del sistema de salud humana (previsión, certificado de
 * supersalud) o que simplemente no se preguntan al crear la cuenta (sexo,
 * segundo apellido). Siguen estando: se completan después desde el perfil de
 * cada escritorio.
 */
return new class extends Migration
{
    /** Columna => definición nueva, respetando el tipo que ya tenían. */
    private const COLUMNAS = [
        'pacientes' => [
            'sexo' => "CHAR(1) NULL",
            'id_prevision' => 'BIGINT(20) UNSIGNED NULL',
        ],
        'profesionales' => [
            'sexo' => "CHAR(1) NULL",
            'apellido_dos' => 'VARCHAR(50) NULL',
            'certificado' => 'INT(11) NULL',
        ],
        'asistentes' => [
            'apellido_dos' => 'VARCHAR(50) NULL',
        ],
    ];

    public function up(): void
    {
        foreach (self::COLUMNAS as $tabla => $columnas) {
            foreach ($columnas as $columna => $definicion) {
                DB::statement("ALTER TABLE `{$tabla}` MODIFY `{$columna}` {$definicion}");
            }
        }
    }

    public function down(): void
    {
        // Se vuelve a NOT NULL solo si no quedaron filas con el campo vacío.
        foreach (self::COLUMNAS as $tabla => $columnas) {
            foreach ($columnas as $columna => $definicion) {
                if (DB::table($tabla)->whereNull($columna)->exists()) {
                    continue;
                }

                DB::statement("ALTER TABLE `{$tabla}` MODIFY `{$columna}` ".str_replace(' NULL', ' NOT NULL', $definicion));
            }
        }
    }
};
