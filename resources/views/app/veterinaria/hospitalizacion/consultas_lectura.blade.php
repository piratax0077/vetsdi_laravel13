<div class="tab-pane fade {{ $pestanaHospital === 'enfermeria' ? 'show active' : '' }}" id="hospital-enfermeria" role="tabpanel" aria-labelledby="hospital-enfermeria-tab">
    <p class="text-muted small">Consulta de los controles y cuidados registrados por enfermería durante esta hospitalización. Solo lectura.</p>
    <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Fecha</th><th>Registrado por</th><th>Evolución y cuidados</th><th>Peso (kg)</th><th>Temperatura (°C)</th><th>FC (lat/min)</th><th>FR (resp/min)</th><th>Dolor (0–10)</th></tr></thead><tbody>
    @forelse($evolucionesEnfermeria as $notaEnfermeria)
    <tr><td>{{ $notaEnfermeria->fecha }}</td><td>{{ $notaEnfermeria->autor ?? '#'.$notaEnfermeria->id_usuario }}</td><td style="white-space:pre-wrap">{{ $notaEnfermeria->observaciones }}@include('app.veterinaria.hospitalizacion.presion_registrada', ['registroPresion' => $notaEnfermeria])</td><td>{{ $notaEnfermeria->peso ?? '—' }}</td><td>{{ $notaEnfermeria->temperatura ?? '—' }}</td><td>{{ $notaEnfermeria->frecuencia_cardiaca ?? '—' }}</td><td>{{ $notaEnfermeria->frecuencia_respiratoria ?? '—' }}</td><td>{{ $notaEnfermeria->dolor ?? '—' }}</td></tr>
    @empty<tr><td colspan="8" class="text-center text-muted">No hay evoluciones de enfermería registradas para esta hospitalización.</td></tr>@endforelse
    </tbody></table></div>
    {{ $evolucionesEnfermeria->links('pagination::bootstrap-4') }}
</div>
<div class="tab-pane fade {{ $pestanaHospital === 'examenes' ? 'show active' : '' }}" id="hospital-examenes" role="tabpanel" aria-labelledby="hospital-examenes-tab">
    <a class="btn btn-outline-info btn-sm mb-3" href="{{ route('vet.pdf.index',['id_mascota'=>$internacion->id_mascota]) }}" target="_blank" rel="noopener">Documentos PDF de la mascota</a>
    <p class="text-muted small">Resultados de exámenes adjuntos a la ficha de esta mascota, incluidos antecedentes anteriores al ingreso. Solo lectura.</p>
    <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Fecha de carga</th><th>Examen / documento</th><th>Tipo</th><th>Observaciones</th><th>Revisión</th><th>Resultado</th></tr></thead><tbody>
    @forelse($examenesHospitalarios ?? [] as $examen)
    <tr><td>{{ $examen->created_at }}</td><td>{{ $examen->nombre_original }}</td><td>{{ ['sangre'=>'Sangre','radiologico'=>'Radiológico','especialidad'=>'Especialidad','otro'=>'Otro'][$examen->tipo_examen] ?? 'Sin clasificar' }}</td><td style="white-space:pre-wrap">{{ $examen->observacion ?: '—' }}</td><td>{{ $examen->estado === 'clasificado' ? 'Revisado' : 'Pendiente de revisión' }}</td><td><a class="btn btn-outline-info btn-sm" target="_blank" rel="noopener noreferrer" href="{{ route('vet.hospital.examen.ver',['documento'=>$examen->id,'id_institucion'=>$institucion->id,'internacion'=>$internacion->id]) }}">Ver examen</a></td></tr>
    @empty<tr><td colspan="6" class="text-center text-muted">No hay resultados de exámenes adjuntos para esta mascota.</td></tr>@endforelse
    </tbody></table></div>
    @if($examenesHospitalarios){{ $examenesHospitalarios->links('pagination::bootstrap-4') }}@endif
</div>
