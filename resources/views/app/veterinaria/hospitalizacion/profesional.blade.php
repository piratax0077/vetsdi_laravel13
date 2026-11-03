@extends(session('rol_activo') === 'hospitalizacion' ? 'template.hospitalizacion_veterinaria' : 'template.profesional.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
    <h5 class="f-26 mb-3">{{ $internacion ? 'Ficha hospitalaria · '.($mascotas[$internacion->id_mascota] ?? '#'.$internacion->id_mascota) : 'Atención de pacientes hospitalizados' }}</h5>
    <p>{{ $institucion->nombre }}</p>
    @if($centros->count() > 1)<form method="get" class="form-inline mb-3"><label for="hospital-centro" class="floating-label-activo-sm mr-2">Centro</label><select id="hospital-centro" class="form-control mr-2" name="id_institucion">@foreach($centros as $centro)<option value="{{ $centro['id_institucion'] }}" @selected($centro['id_institucion'] == $institucion->id)>{{ $centro['label'] }}</option>@endforeach</select><button class="btn btn-info btn-sm">Ver pacientes</button></form>@endif
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(!$internacion)
    @include('app.veterinaria.hospitalizacion.ingreso')
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Mis pacientes asignados e historia</h5></div><div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Mascota</th><th>Ingreso</th><th>Estado</th><th>Atención</th></tr></thead><tbody>
        @forelse($pacientes as $paciente)<tr><td>{{ $mascotas[$paciente->id_mascota] ?? '#'.$paciente->id_mascota }}</td><td>{{ $paciente->ingreso }}</td><td>{{ $paciente->estado }}</td><td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.hospital.profesional',['id_institucion'=>$institucion->id,'internacion'=>$paciente->id]) }}">Abrir atención</a></td></tr>@empty<tr><td colspan="4">No hay hospitalizaciones registradas.</td></tr>@endforelse
        </tbody></table></div>{{ $pacientes->links('pagination::bootstrap-4') }}
    </div></div>
    @endif
    @if($internacion)
    <a class="btn btn-outline-info mb-3" href="{{ route('vet.hospital.profesional',['id_institucion'=>$institucion->id]) }}">Pacientes hospitalizados</a>
    <div class="card" id="atencion-hospitalaria"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">{{ $mascotas[$internacion->id_mascota] ?? '#'.$internacion->id_mascota }} · Atención hospitalaria</h5>
    </div><div class="card-body">
        <p><strong>Motivo del ingreso:</strong> {{ $internacion->motivo }}</p><p><strong>Indicaciones:</strong> {{ $internacion->indicaciones ?: 'Sin indicaciones registradas' }}</p>
        @php($pestanaHospital = request('pestana_hospital','clinica'))
        <ul class="nav pill-moderno pill-moderno-white mb-3" role="tablist" aria-label="Atención hospitalaria">
            @foreach(['clinica'=>'Evolución veterinaria','enfermeria'=>'Evolución de enfermería','examenes'=>'Exámenes realizados'] as $clave=>$etiqueta)
            <li class="nav-item"><a class="nav-link {{ $pestanaHospital === $clave ? 'active' : '' }}" id="hospital-{{ $clave }}-tab" data-toggle="pill" href="#hospital-{{ $clave }}" role="tab" aria-controls="hospital-{{ $clave }}" aria-selected="{{ $pestanaHospital === $clave ? 'true' : 'false' }}">{{ $etiqueta }}</a></li>
            @endforeach
        </ul>
        <div class="tab-content">
        <div class="tab-pane fade {{ $pestanaHospital === 'clinica' ? 'show active' : '' }}" id="hospital-clinica" role="tabpanel" aria-labelledby="hospital-clinica-tab">
        @if($internacion->estado === 'hospitalizada')<button class="btn btn-info mb-3" data-toggle="modal" data-target="#registro_hospitalario">Agregar registro</button>@endif
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Fecha</th><th>Tipo</th><th>Profesional</th><th>Registro</th><th>Peso (kg)</th><th>Temperatura (°C)</th></tr></thead><tbody>
        @forelse($evoluciones as $nota)<tr><td>{{ $nota->fecha }}</td><td>{{ ['enfermeria'=>'Enfermería','veterinaria'=>'Atención veterinaria','cirugia'=>'Cirugía','control'=>'Control','evolucion'=>'Evolución'][$nota->tipo_registro] ?? 'Atención veterinaria' }}</td><td>{{ $nota->autor ?? '#'.$nota->id_usuario }}</td><td style="white-space:pre-wrap">@if($nota->procedimiento)<strong>{{ $nota->procedimiento }}</strong><br>@endif{{ $nota->observaciones }}@if(isset($nota->frecuencia_cardiaca) || isset($nota->frecuencia_respiratoria) || isset($nota->dolor))<small class="d-block text-muted">FC: {{ $nota->frecuencia_cardiaca ?? '—' }} lat/min · FR: {{ $nota->frecuencia_respiratoria ?? '—' }} resp/min · Dolor: {{ $nota->dolor ?? '—' }}/10</small>@endif</td><td>{{ $nota->peso ?? '—' }}</td><td>{{ $nota->temperatura ?? '—' }}</td></tr>@empty<tr><td colspan="6">Sin registros de atención.</td></tr>@endforelse
        </tbody></table></div>{{ $evoluciones->links('pagination::bootstrap-4') }}
        </div>
        @include('app.veterinaria.hospitalizacion.consultas_lectura')
        </div>
    </div></div>
    @include('app.veterinaria.hospitalizacion.alta_controles')
    @if($internacion->estado === 'hospitalizada')
    <div class="modal fade" id="registro_hospitalario" tabindex="-1" aria-labelledby="registro_hospitalario_titulo" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header modal-header-purple"><h5 class="modal-title" id="registro_hospitalario_titulo">Registrar atención hospitalaria</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">×</button></div>
        <form method="post" action="{{ route('vet.hospital.profesional.guardar') }}">@csrf<input type="hidden" name="_hospital_form" value="registro">
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="internacion_id" value="{{ $internacion->id }}">
            <div class="modal-body"><div class="form-row">
                <div class="col-md-6 form-group"><label class="floating-label-activo-sm" for="hospital-tipo">Tipo de registro</label><select class="form-control" id="hospital-tipo" name="tipo_registro" required><option value="veterinaria" @selected(old('tipo_registro') === 'veterinaria')>Atención veterinaria</option><option value="enfermeria" @selected(old('tipo_registro') === 'enfermeria')>Registro de enfermería</option><option value="cirugia" @selected(old('tipo_registro') === 'cirugia')>Registro de cirugía</option><option value="control" @selected(old('tipo_registro') === 'control')>Control</option><option value="evolucion" @selected(old('tipo_registro') === 'evolucion')>Evolución</option></select></div>
                <div class="col-md-6 form-group"><label class="floating-label-activo-sm" for="hospital-fecha">Fecha y hora</label><input class="form-control" id="hospital-fecha" type="datetime-local" name="fecha" value="{{ old('fecha',now()->format('Y-m-d\TH:i')) }}" required></div>
                <div class="col-md-6 form-group"><label class="floating-label-activo-sm" for="hospital-peso">Peso en kg (opcional)</label><input class="form-control" id="hospital-peso" type="number" name="peso" min="0.001" step="0.001" value="{{ old('peso') }}"></div>
                <div class="col-md-6 form-group"><label class="floating-label-activo-sm" for="hospital-temperatura">Temperatura en °C (opcional)</label><input class="form-control" id="hospital-temperatura" type="number" name="temperatura" min="0" max="99" step="0.1" value="{{ old('temperatura') }}"></div>
                <div class="col-12 form-group"><label class="floating-label-activo-sm" for="hospital-procedimiento">Procedimiento quirúrgico (obligatorio para cirugía)</label><input id="hospital-procedimiento" class="form-control" name="procedimiento" maxlength="150" value="{{ old('procedimiento') }}"></div>
                <div class="col-12 form-group">
                    <label class="floating-label-activo-sm" for="hospital-observaciones">Registro de atención</label>
                    <textarea class="form-control" id="hospital-observaciones" name="observaciones" rows="6" maxlength="20000" aria-describedby="hospital-observaciones-ayuda" style="min-height:150px;padding:12px;color:#343a40;caret-color:#343a40;" required>{{ old('observaciones') }}</textarea>
                    <small id="hospital-observaciones-ayuda" class="form-text text-muted">Describa la evaluación, los cuidados y los procedimientos realizados. Para cirugía, incluya diagnóstico, anestesia, técnica, incidencias e indicaciones posoperatorias.</small>
                </div>
            </div></div><div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button><button class="btn btn-info" type="submit">Guardar registro</button></div>
        </form>
    </div></div></div>
    @endif
    @endif
</div></div>
@endsection
@section('page-script')
@if($errors->any() && in_array(old('_hospital_form'),['registro','ingreso']))<script>$(function(){ $('#{{ old('_hospital_form') === 'ingreso' ? 'ingreso_hospitalario' : 'registro_hospitalario' }}').modal('show'); });</script>@endif
@endsection
