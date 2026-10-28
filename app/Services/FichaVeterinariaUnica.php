<?php

namespace App\Services;

use App\Models\{ConConsentimientosPcte, DocumentoLaboratorioMascota, FichaAtencion, FichaVeterinariaGeneral, Mascota};
use App\Support\DenticionVeterinaria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FichaVeterinariaUnica
{
    public function datos(Mascota $mascota): array
    {
        $fichas = FichaAtencion::with(['Profesional', 'LugarAtencion', 'PresupuestosMascota', 'RecetasEmitidas', 'OrdenesExamenes'])
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
            'fecha' => $this->fecha($presupuesto->getRawOriginal('fecha')) ?? $this->fecha($presupuesto->getRawOriginal('created_at')), 'estado' => $presupuesto->estado,
        ]));
        $documentos = $documentos->concat(ConConsentimientosPcte::with('Consentimiento')->whereIn('id_fc', $fichas->pluck('id'))->get()->map(fn ($documento) => [
            'tipo' => 'Consentimiento informado', 'nombre' => $documento->Consentimiento?->nombre ?? 'Consentimiento informado',
            'fecha' => $this->fecha($documento->fecha_cons) ?? $this->fecha($documento->getRawOriginal('created_at')), 'estado' => $documento->confirmacion,
            'url' => route('consentimiento.pdf', ['id_consentimiento' => $documento->id, 'id_ficha_atencion' => $documento->id_fc]),
        ]))->concat(DocumentoLaboratorioMascota::where('id_mascota', $mascota->id)->get()->map(fn ($documento) => [
            'tipo' => 'Examen de laboratorio', 'nombre' => $documento->nombre_original,
            'fecha' => $this->fecha($documento->getRawOriginal('created_at')), 'estado' => $documento->estado,
        ]))->concat($fichas->flatMap(function ($ficha) {
            $emitidos = collect();
            foreach (['RecetasEmitidas'=>['Receta veterinaria','receta'], 'OrdenesExamenes'=>['Orden de exámenes','examenes']] as $relacion=>[$nombre,$tipo]) {
                if ($ficha->{$relacion}->isNotEmpty()) $emitidos->push([
                    'tipo'=>$nombre, 'nombre'=>$nombre, 'fecha'=>$this->fecha($ficha->{$relacion}->first()->getRawOriginal('created_at')), 'estado'=>'Emitida',
                    'url'=>route('mascota.documento_emitido.ver', ['tipo'=>$tipo, 'id_ficha_atencion'=>$ficha->id]),
                ]);
            }
            return $emitidos;
        }));
        if (Schema::hasTable('informes_medicos')) {
            $documentos = $documentos->concat(\App\Models\InformeMedico::whereIn('id_ficha_atencion', $fichas->pluck('id'))->get()->map(fn ($informe) => [
                'tipo' => 'Informe médico', 'nombre' => 'Informe veterinario',
                'fecha' => $this->fecha($informe->fecha_informe_medico) ?? $this->fecha($informe->getRawOriginal('created_at')),
                'estado' => 'Emitido',
                'url' => route('pdf.informe_medico', ['id_informe_medico' => $informe->id]),
            ]));
        }
        $documentos = $documentos->map(function ($documento) {
            $documento['fecha'] = $this->fecha($documento['fecha'] ?? null);
            return $documento;
        })->sortByDesc('fecha')->values();
        return ['fichas' => $fichas, 'detalles' => $detalles, 'peso' => $peso, 'odontograma' => $odontograma, 'documentos' => $documentos];
    }

    private function fecha($valor): ?string
    {
        if (!is_string($valor) || trim($valor) === '') return null;
        try {
            return \Carbon\Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
