<dl class="mt-2" style="white-space:normal;min-width:240px">
    @foreach (['motivo' => 'Motivo de consulta', 'antecedentes' => 'Antecedentes', 'examen_fisico' => 'Examen físico', 'hipotesis_diagnostico' => 'Diagnóstico', 'indicaciones' => 'Indicaciones', 'peso' => 'Peso (kg)', 'temperatura' => 'Temperatura', 'pulso' => 'Pulso'] as $campoFvu => $etiquetaFvu)
        @if ($fichaFvu->{$campoFvu} !== null && $fichaFvu->{$campoFvu} !== '')
            <dt>{{ $etiquetaFvu }}</dt><dd style="white-space:pre-wrap">{{ $fichaFvu->{$campoFvu} }}</dd>
        @endif
    @endforeach
    @foreach (['especialidad_veterinaria' => 'Especialidad', 'obs_clinicas' => 'Observaciones clínicas', 'dieta' => 'Dieta', 'vacunas' => 'Vacunas', 'examen_oral' => 'Examen oral', 'medicamentos' => 'Medicamentos', 'examenes' => 'Exámenes solicitados', 'eg_cpq_cg' => 'Control posquirúrgico', 'obs_egp_cpq_cg' => 'Observaciones posquirúrgicas'] as $campoFvu => $etiquetaFvu)
        @php
            $valorFvu = $datosFvu[$campoFvu] ?? null;
            if (is_string($valorFvu)) {
                $jsonFvu = json_decode($valorFvu, true);
                if (is_array($jsonFvu)) $valorFvu = $jsonFvu;
            }
        @endphp
        @if ($valorFvu !== null && $valorFvu !== '' && $valorFvu !== [])
            <dt>{{ $etiquetaFvu }}</dt>
            <dd style="white-space:pre-wrap">@if (is_array($valorFvu)){{ collect(\Illuminate\Support\Arr::flatten($valorFvu))->filter(fn ($valor) => is_scalar($valor) && $valor !== '')->implode(' · ') }}@else{{ $valorFvu }}@endif</dd>
        @endif
    @endforeach
</dl>
