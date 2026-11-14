<?php

namespace App\Support;

use App\Models\Mascota;
use App\Models\Profesional;
use Illuminate\Support\Facades\DB;

// Notas internas del tutor de la mascota (las que se guardan en Mascotas y tutores del profesional).
// Una nota la ve quien la escribió y todos los profesionales que pertenecen al lugar de atención
// donde se dejó (centro médico, peluquería, etc.). Las notas sin lugar solo las ve su autor.
class NotasInternasMascota
{
    public static function para(?Mascota $mascota, ?Profesional $profesional): array
    {
        if (!$mascota || !$profesional || empty($mascota->id_responsable)) {
            return [];
        }

        $lugaresDelProfesional = DB::table('profesionales_lugares_atencion')
            ->where('id_profesional', $profesional->id)
            ->where('estado', 1)
            ->pluck('id_lugar_atencion')
            ->all();

        return DB::table('tutor_notas_internas as n')
            ->leftJoin('lugares_atencion as la', 'la.id', '=', 'n.id_lugar_atencion')
            ->leftJoin('profesionales as p', 'p.id', '=', 'n.id_profesional')
            ->where('n.id_paciente', $mascota->id_responsable)
            ->where(function ($consulta) use ($profesional, $lugaresDelProfesional) {
                $consulta->where('n.id_profesional', $profesional->id)
                    ->orWhereIn('n.id_lugar_atencion', $lugaresDelProfesional ?: [0]);
            })
            ->orderByDesc('n.id')
            ->get([
                'n.nota', 'n.conflictivo', 'n.created_at', 'la.nombre as lugar',
                'p.nombre as nombre_profesional', 'p.apellido_uno as apellido_profesional',
            ])
            ->map(fn ($nota) => [
                'nota' => trim((string) $nota->nota),
                'conflictivo' => (bool) $nota->conflictivo,
                'fecha' => $nota->created_at ? date('d-m-Y H:i', strtotime($nota->created_at)) : '',
                'lugar' => $nota->lugar ?: '',
                'profesional' => trim($nota->nombre_profesional . ' ' . $nota->apellido_profesional),
            ])
            ->all();
    }
}
