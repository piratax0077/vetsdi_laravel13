<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CatalogoLaboratorioVeterinario
{
    public const EXAMENES = [
        'Sangre · Hemograma completo',
        'Sangre · Perfil bioquímico',
        'Sangre · Perfil renal',
        'Sangre · Perfil hepático',
        'Sangre · Glicemia',
        'Sangre · Electrolitos',
        'Sangre · Pruebas de coagulación',
        'Sangre · Frotis sanguíneo',
        'Orina · Urianálisis completo',
        'Orina · Sedimento urinario',
        'Orina · Urocultivo y antibiograma',
        'Orina · Relación proteína/creatinina urinaria (UPC)',
        'Secreciones · Citología ótica',
        'Secreciones · Citología vaginal',
        'Secreciones · Citología de secreciones',
        'Secreciones · Cultivo y antibiograma de secreción ótica',
        'Secreciones · Cultivo y antibiograma de secreción ocular',
        'Secreciones · Cultivo y antibiograma de secreción nasal',
        'Secreciones · Cultivo y antibiograma de heridas y abscesos',
    ];

    public function cargar(int $institucion): void
    {
        $existentes = DB::table('vet_catalogo_servicios')
            ->where('id_institucion', $institucion)->where('modulo', 'laboratorio')
            ->pluck('nombre')->all();
        $faltantes = array_diff(self::EXAMENES, $existentes);
        if (!$faltantes) return;

        $ahora = now();
        // La clave única evita duplicados incluso ante aperturas simultáneas.
        DB::table('vet_catalogo_servicios')->insertOrIgnore(array_map(fn($nombre) => [
            'id_institucion' => $institucion,
            'modulo' => 'laboratorio',
            'nombre' => $nombre,
            'duracion_minutos' => 30,
            'precio' => 0,
            'activo' => true,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ], array_values($faltantes)));
    }
}
