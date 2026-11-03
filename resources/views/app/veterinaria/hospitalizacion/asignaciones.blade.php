@extends('template.adm_cm.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
<h5 class="f-26 mb-3">Asignación de pacientes hospitalizados</h5><p>{{ $institucion->nombre }}</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">Revise la asignación seleccionada.</div>@endif
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Mascota</th><th>Ingreso</th><th>Profesionales asignados</th></tr></thead><tbody>
@forelse($pacientes as $paciente)<tr><td>{{ $mascotas[$paciente->id_mascota] ?? '#'.$paciente->id_mascota }}</td><td>{{ $paciente->ingreso }}</td><td>
<form method="post" action="{{ route('vet.hospital.asignar') }}">@csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="internacion_id" value="{{ $paciente->id }}">
@foreach($profesionales as $prof)<label class="floating-label-activo-sm mr-3"><input type="checkbox" name="profesionales[]" value="{{ $prof->id_usuario }}" @checked(collect($asignaciones[$paciente->id] ?? [])->contains('id_usuario',$prof->id_usuario))> {{ $prof->nombre }} {{ $prof->apellido }}</label>@endforeach
@if($profesionales->isEmpty())<p>No hay profesionales vinculados al centro.</p>@else<button class="btn btn-info btn-sm" type="submit">Guardar asignación</button>@endif
</form></td></tr>@empty<tr><td colspan="3">No hay pacientes hospitalizados.</td></tr>@endforelse
</tbody></table></div>{{ $pacientes->links('pagination::bootstrap-4') }}</div></div>
</div></div>
@endsection
