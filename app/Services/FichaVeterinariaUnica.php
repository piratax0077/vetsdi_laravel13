<?php

namespace App\Services;

use App\Models\{ConConsentimientosPcte, DocumentoLaboratorioMascota, FichaAtencion, FichaVeterinariaGeneral, Mascota};
use App\Support\DenticionVeterinaria;
use Illuminate\Support\Facades\DB;

class FichaVeterinariaUnica
{
    public function datos(Mascota $mascota): array
    {
        $fichas = FichaAtencion::with(['Profesional', 'LugarAtencion', 'PresupuestosMascota'])
            ->where('id_mascota', $mascota->id)->orderByDesc('created_at')->orderByDesc('id')->get();
        // Pet identity stays stable when its tutor changes.
        $detalles = FichaVeterinariaGeneral::whereIn('id_fichas_atenciones', $fichas->pluck('id'))
            ->orderByDesc('id')->get()->unique('id_fichas_atenciones')
            ->mapWithKeys(fn ($detalle) => [$detalle->id_fichas_atenciones => json_decode($detalle->datos ?? '{}', true) ?: []]);
        $peso = $fichas->first(fn ($ficha) => $ficha->peso !== null && $ficha->peso !== '')->peso ?? null;
        $odontograma = collect();
        try {
            $especie = DenticionVeterinaria::especie($mascota);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $especie = null;
        }
        if ($especie) {
            $odontograma = DB::table('odontogramas_mascotas')->where('mascota_id', $mascota->id)
                ->where('especie', $especie)->where('estado', 1)->orderByDesc('id')->get()->unique('pieza')->values();
        }
        $documentos = $fichas->flatMap(fn ($ficha) => $ficha->PresupuestosMascota->map(fn ($presupuesto) => [
            'tipo' => 'Presupuesto', 'nombre' => 'Presupuesto veterinario',
            'fecha' => $presupuesto->fecha?->format('Y-m-d') ?? $presupuesto->created_at?->format('Y-m-d'), 'estado' => $presupuesto->estado,
        ]));
        $documentos = $documentos->concat(ConConsentimientosPcte::with('Consentimiento')->whereIn('id_fc', $fichas->pluck('id'))->get()->map(fn ($documento) => [
            'tipo' => 'Consentimiento informado', 'nombre' => $documento->Consentimiento?->nombre ?? 'Consentimiento informado',
            'fecha' => $documento->fecha_cons ?? $documento->created_at?->format('Y-m-d'), 'estado' => $documento->confirmacion,
        ]))->concat(DocumentoLaboratorioMascota::where('id_mascota', $mascota->id)->get()->map(fn ($documento) => [
            'tipo' => 'Examen de laboratorio', 'nombre' => $documento->nombre_original,
            'fecha' => $documento->created_at?->format('Y-m-d'), 'estado' => $documento->estado,
        ]))->sortByDesc('fecha')->values();
        return ['fichas' => $fichas, 'detalles' => $detalles, 'peso' => $peso, 'odontograma' => $odontograma, 'documentos' => $documentos];
    }
}
