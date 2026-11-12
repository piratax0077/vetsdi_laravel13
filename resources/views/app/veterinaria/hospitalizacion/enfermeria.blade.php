@extends('template.hospitalizacion_veterinaria')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
<h4>Hospitalización veterinaria · Enfermería</h4><p>{{ $institucion->nombre }}</p>
<form method="get" class="form-row mb-3"><div class="form-group col-md-6"><label class="floating-label-activo-sm" for="enfermeria-centro">Centro veterinario</label><select id="enfermeria-centro" class="form-control" name="id_institucion">@foreach($centros as $centro)<option value="{{ $centro->id }}" @selected($centro->id === $institucion->id)>{{ $centro->nombre }}</option>@endforeach</select></div><div class="col-md-3 align-self-end form-group"><button class="btn btn-info">Ver pacientes</button></div></form>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><div class="card-header"><h5>Mascotas hospitalizadas</h5></div><div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Mascota</th><th>Especie</th><th>Ingreso</th><th>Atención</th></tr></thead><tbody>
@forelse($pacientes as $p)<tr><td>{{ $mascotas[$p->id_mascota]->nombre ?? '#'.$p->id_mascota }}</td><td>{{ $mascotas[$p->id_mascota]->especie ?? '—' }}</td><td>{{ $p->ingreso }}</td><td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.hospital.servicio',['id_institucion'=>$institucion->id,'internacion'=>$p->id]) }}">Controles y cuidados</a></td></tr>@empty<tr><td colspan="4">No hay registros.</td></tr>@endforelse
</tbody></table></div>{{ $pacientes->links('pagination::bootstrap-4') }}</div></div>
@if($internacion)
<div class="card"><div class="card-header"><h5>{{ $mascotas[$internacion->id_mascota]->nombre ?? '#'.$internacion->id_mascota }} · Indicaciones veterinarias</h5></div><div class="card-body"><p><strong>Motivo de ingreso:</strong> {{ $internacion->motivo }}</p><p style="white-space:pre-wrap">{{ $internacion->indicaciones ?: 'Sin indicaciones registradas' }}</p></div></div>
@if($internacion->estado === 'hospitalizada')
<div class="card"><div class="card-header"><h5>Control de enfermería veterinaria</h5></div><div class="card-body">
<form method="post" action="{{ route('vet.hospital.enfermeria.guardar') }}">@csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="internacion_id" value="{{ $internacion->id }}">
<div class="form-row">
<div class="form-group col-md-4"><label class="floating-label-activo-sm" for="enf-fecha">Fecha y hora</label><input class="form-control" id="enf-fecha" type="datetime-local" name="fecha" value="{{ old('fecha',now()->format('Y-m-d\TH:i')) }}" required></div>
@foreach(['peso'=>['Peso (kg)','0.001','99999','0.001'],'temperatura'=>['Temperatura (°C)','0','99','0.1'],'frecuencia_cardiaca'=>['Frecuencia cardíaca (lat/min)','1','600','1'],'frecuencia_respiratoria'=>['Frecuencia respiratoria (resp/min)','1','300','1'],'dolor'=>['Valoración del dolor (0–10)','0','10','1']] as $campo=>$config)
<div class="form-group col-md-4"><label class="floating-label-activo-sm" for="enf-{{ $campo }}">{{ $config[0] }} (opcional)</label><input class="form-control" id="enf-{{ $campo }}" type="number" name="{{ $campo }}" min="{{ $config[1] }}" max="{{ $config[2] }}" step="{{ $config[3] }}" value="{{ old($campo) }}"></div>
@endforeach
@include('app.veterinaria.hospitalizacion.presion_arterial', ['prefijoPresion' => 'enf'])
<div class="form-group col-12"><label class="floating-label-activo-sm" for="enf-observaciones">Cuidados realizados, alimentación, hidratación, eliminación y respuesta a las indicaciones veterinarias</label><textarea id="enf-observaciones" class="form-control" name="observaciones" rows="5" maxlength="20000" required>{{ old('observaciones') }}</textarea></div>
</div><button type="submit" class="btn btn-info">Guardar control de enfermería</button></form></div></div>
@endif
<div class="card"><div class="card-header"><h5>Historia de atención hospitalaria</h5></div><div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Fecha</th><th>Tipo</th><th>Autor</th><th>Registro</th><th>Controles</th></tr></thead><tbody>
@forelse($evoluciones as $e)<tr><td>{{ $e->fecha }}</td><td>{{ ['enfermeria'=>'Enfermería','veterinaria'=>'Atención veterinaria','cirugia'=>'Cirugía','control'=>'Control','evolucion'=>'Evolución'][$e->tipo_registro] ?? 'Atención veterinaria' }}</td><td>{{ $e->autor }}</td><td style="white-space:pre-wrap">{{ $e->observaciones }}</td><td>Peso: {{ $e->peso ?? '—' }} kg<br>Temperatura: {{ $e->temperatura ?? '—' }} °C<br>FC: {{ $e->frecuencia_cardiaca ?? '—' }}<br>FR: {{ $e->frecuencia_respiratoria ?? '—' }}<br>Dolor: {{ $e->dolor ?? '—' }}@include('app.veterinaria.hospitalizacion.presion_registrada', ['registroPresion' => $e])</td></tr>@empty<tr><td colspan="5">Sin controles registrados.</td></tr>@endforelse
</tbody></table></div>{{ $evoluciones->links('pagination::bootstrap-4') }}</div></div>
@endif
</div></div>
@endsection
