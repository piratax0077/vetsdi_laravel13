@extends(auth()->user()->hasRole('Profesional') ? 'template.profesional.template' : 'template.adm_cm.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
    <div class="page-header"><h4>Autorizaciones de asistentes</h4><p>Defina qué funciones podrá utilizar cada asistente en su lugar de atención.</p></div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card"><div class="card-body">
        <label for="ambito_autorizaciones">Ámbito de las autorizaciones</label>
        <select id="ambito_autorizaciones" class="form-control" onchange="window.location.href=this.value">
            @foreach($ambitos as $ambito)
                <option value="{{ route('asistentes.autorizaciones', $ambito) }}" @selected($ambito === $seleccion)>{{ $lugares[$ambito['id_lugar_atencion']]->nombre }} — {{ $ambito['id_profesional'] ? 'Mi consulta profesional' : 'Todo el centro en este lugar' }}</option>
            @endforeach
        </select>
        <small class="form-text text-muted">Las autorizaciones generales del centro tienen prioridad. Al desmarcar una función y guardar, se revoca su acceso.</small>
    </div></div>
    @forelse($asistentes as $asistente)
        @php($registro = $registros->get($asistente->id))
        @php($heredaCentro = $seleccion['id_profesional'] && $generales->contains($asistente->id))
        <div class="card"><div class="card-body">
            <h5>{{ $asistente->nombres }} {{ $asistente->apellido_uno }} {{ $asistente->apellido_dos }}</h5>
            <p class="text-muted">{{ $asistente->rut }} · {{ $asistente->email }}</p>
            @if($heredaCentro)<div class="alert alert-info">Este asistente tiene una política general del centro. Sus autorizaciones particulares quedarán guardadas, pero se aplicarán cuando el centro retire esa política.</div>@endif
            <form method="POST" action="{{ route('asistentes.autorizaciones.guardar') }}">
                @csrf
                <input type="hidden" name="id_asistente" value="{{ $asistente->id }}">
                <input type="hidden" name="id_lugar_atencion" value="{{ $seleccion['id_lugar_atencion'] }}">
                <input type="hidden" name="id_profesional" value="{{ $seleccion['id_profesional'] }}">
                <div class="row">
                    @foreach(config('asistentes.permisos') as $clave => $etiqueta)
                        <div class="col-md-6 col-lg-4 mb-3"><div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="permiso_{{ $asistente->id }}_{{ $clave }}" name="permisos[{{ $clave }}]" value="1" @checked($registro?->permisos[$clave] ?? false)>
                            <label class="custom-control-label" for="permiso_{{ $asistente->id }}_{{ $clave }}">{{ $etiqueta }}</label>
                        </div></div>
                    @endforeach
                </div>
                <button class="btn btn-info">Guardar autorizaciones</button>
                <span class="text-muted ml-2">{{ $registro ? 'Última modificación: '.$registro->updated_at->format('d-m-Y H:i') : 'Sin autorizaciones asignadas' }}</span>
            </form>
            @if(!$seleccion['id_profesional'] && $registro)
                <form method="POST" class="mt-3" action="{{ route('asistentes.autorizaciones.quitar_politica') }}">
                    @csrf @method('DELETE')
                    <input type="hidden" name="id_asistente" value="{{ $asistente->id }}">
                    <input type="hidden" name="id_lugar_atencion" value="{{ $seleccion['id_lugar_atencion'] }}">
                    <button class="btn btn-outline-secondary btn-sm">Usar autorizaciones particulares de cada profesional</button>
                </form>
            @endif
        </div></div>
    @empty
        <div class="alert alert-info">No hay asistentes con una vinculación o contrato activo en este ámbito. Vincúlelos desde la sección de asistentes o personal.</div>
    @endforelse
</div></div>
@endsection
