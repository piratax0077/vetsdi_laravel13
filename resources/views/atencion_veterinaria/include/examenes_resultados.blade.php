@php
    $resultadosVet = collect();
    foreach ($examenes_especialidad_realizados ?? [] as $examen) {
        if ((int) ($examen->HoraMedica->id_estado ?? 0) !== 6) continue;
        $resultadosVet->push([
            'fecha' => $examen->HoraMedica->fecha_realizacion_consulta,
            'nombre' => $examen->nombre,
            'tipo' => 'Especialidad',
            'detalle' => $examen->SubTipoEspecialidad?->nombre,
            'origen' => 'especialidad', 'id' => $examen->id, 'revisar' => !$examen->revisado,
        ]);
    }
    foreach ($resultado_examen ?? [] as $examen) {
        if ($examen->ResultadoExamenArchivo->isEmpty()) continue;
        $resultadosVet->push([
            'fecha' => $examen->fecha_registro,
            'nombre' => $examen->obj_tipo_examen?->nombre_examen ?? 'Resultado #'.$examen->id,
            'tipo' => (int) $examen->tipo_examen === 354 ? 'Radiológico / imagenología' : 'General / laboratorio',
            'origen' => 'laboratorio', 'id' => $examen->id, 'revisar' => !$examen->revisado,
        ]);
    }
    foreach ($reg_exam_rayo ?? [] as $examen) {
        $resultadosVet->push([
            'fecha' => $examen->created_at, 'nombre' => $examen->nombre_procedimientos,
            'tipo' => 'Radiológico / imagenología', 'origen' => 'radiologia', 'id' => $examen->id,
        ]);
    }
    foreach ($reg_octavo_par ?? [] as $examen) {
        if (!$examen->id_otros_profesionales) continue;
        $resultadosVet->push([
            'fecha' => $examen->fecha_ex, 'nombre' => 'Octavo par',
            'tipo' => 'Especialidad', 'origen' => 'octavo', 'id' => $examen->id_otros_profesionales,
        ]);
    }
@endphp
<div class="card mb-3">
    <div class="card-top"><h6 class="mb-0">Exámenes con resultados</h6></div>
    <div class="card-body pt-2">
        <div class="table-responsive">
            <table class="table table-sm align-middle" id="examenes-resultados-vet">
                <thead><tr><th>Fecha</th><th>Examen o archivo</th><th>Tipo / clasificación</th><th>Observación</th><th>Acción</th></tr></thead>
                <tbody>
                    @foreach($resultadosVet->sortByDesc(fn ($fila) => strtotime($fila['fecha'] ?? '') ?: 0) as $fila)
                        <tr>
                            <td>{{ $fila['fecha'] ? \Illuminate\Support\Carbon::parse($fila['fecha'])->format('d-m-Y') : '—' }}</td>
                            <td>{{ $fila['nombre'] ?: '—' }}</td>
                            <td>{{ $fila['tipo'] }}</td>
                            <td>{{ $fila['detalle'] ?? '—' }}</td>
                            <td class="text-nowrap">
                                @if($fila['origen'] === 'octavo')
                                    <form method="post" action="{{ route('ficha.otro.prof.generar_pdf_octavo_par') }}" target="_blank">
                                        @csrf
                                        <input type="hidden" name="id_fc" value="{{ $fila['id'] }}">
                                        <button type="submit" class="btn btn-xxs btn-success-light-c"><i class="feather icon-activity"></i> Ver</button>
                                    </form>
                                @else
                                    @php($accion = ['especialidad' => 'verExamenEspecialidad', 'laboratorio' => 'verResultadoExamen', 'radiologia' => 'carga_detalle_rayo'][$fila['origen']])
                                    <button type="button" class="btn btn-xxs btn-success-light-c"
                                        onclick="{{ $accion }}({{ (int) $fila['id'] }}, {{ (int) ($fila['revisar'] ?? false) }})">
                                        <i class="feather icon-activity"></i> Ver
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tbody id="lab-vet-resultados-unificados"></tbody>
                <tbody id="examenes-resultados-vet-vacio" @if($resultadosVet->isNotEmpty()) hidden @endif>
                    <tr><td colspan="5" class="text-center text-muted">No hay exámenes con resultados.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
