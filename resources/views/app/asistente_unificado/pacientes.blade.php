@extends('template.asistente_unificado.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
    <div class="page-header"><h4>Pacientes vinculados</h4><p>Pacientes atendidos por el profesional en el lugar seleccionado.</p></div>
    <div class="card"><div class="card-body">
        <form method="GET" class="form-row mb-3"><div class="col-md-9"><label class="sr-only" for="buscar_paciente">Nombre o RUT</label><input class="form-control" id="buscar_paciente" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre, apellido o RUT"></div><div class="col-md-3"><button class="btn btn-info btn-block">Buscar</button></div></form>
        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>RUT</th><th>Nombre</th><th>Teléfono</th><th>Email</th></tr></thead><tbody>
            @forelse($pacientes as $paciente)<tr><td>{{ $paciente->rut }}</td><td>{{ $paciente->nombres }} {{ $paciente->apellido_uno }} {{ $paciente->apellido_dos }}</td><td>{{ $paciente->telefono_uno }}</td><td>{{ $paciente->email }}</td></tr>
            @empty<tr><td colspan="4" class="text-muted">No se encontraron pacientes en este ámbito.</td></tr>@endforelse
        </tbody></table></div>{{ $pacientes->links() }}
    </div></div>
</div></div>
@endsection
