{{-- Contenido del modal "Autorizaciones" de un asistente (se carga por ajax desde la tabla de asistentes) --}}
<form id="form_autorizaciones_asistente" onsubmit="return false;">
    <input type="hidden" name="id_asistente" value="{{ $asistente->id }}">
    <input type="hidden" name="id_lugar_atencion" value="{{ $seleccion['id_lugar_atencion'] }}">
    <input type="hidden" name="id_profesional" value="{{ $seleccion['id_profesional'] }}">

    <p class="text-muted mb-3">Defina qué funciones podrá utilizar {{ $asistente->nombres }} {{ $asistente->apellido_uno }} en su lugar de atención. Al desmarcar una función y guardar, se revoca su acceso.</p>

    @if($ambitos->count() > 1)
        <div class="form-group">
            <label for="ambito_autorizaciones_asistente">Ámbito de las autorizaciones</label>
            <select id="ambito_autorizaciones_asistente" class="form-control form-control-sm" onchange="cargar_autorizaciones_asistente({{ $asistente->id }}, this.value);">
                @foreach($ambitos as $ambito)
                    <option value="{{ $ambito['id_lugar_atencion'] }}|{{ $ambito['id_profesional'] }}" @selected($ambito === $seleccion)>{{ $lugares[$ambito['id_lugar_atencion']]->nombre }} — {{ $ambito['id_profesional'] ? 'Mi consulta profesional' : 'Todo el centro en este lugar' }}</option>
                @endforeach
            </select>
        </div>
    @else
        <p class="mb-3"><strong>{{ $lugares[$seleccion['id_lugar_atencion']]->nombre }}</strong> — {{ $seleccion['id_profesional'] ? 'Mi consulta profesional' : 'Todo el centro en este lugar' }}</p>
    @endif

    @if($heredaCentro)
        <div class="alert alert-info">Este asistente tiene una política general del centro. Sus autorizaciones particulares quedarán guardadas, pero se aplicarán cuando el centro retire esa política.</div>
    @endif

    <div class="row">
        @foreach(config('asistentes.permisos') as $clave => $etiqueta)
            <div class="col-md-6 mb-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="permiso_asistente_{{ $clave }}" name="permisos[{{ $clave }}]" value="1" @checked($registro?->permisos[$clave] ?? false)>
                    <label class="custom-control-label" for="permiso_asistente_{{ $clave }}">{{ $etiqueta }}</label>
                </div>
            </div>
        @endforeach
    </div>

    <small class="text-muted d-block">{{ $registro ? 'Última modificación: '.$registro->updated_at->format('d-m-Y H:i') : 'Sin autorizaciones asignadas' }}</small>

    @if(!$seleccion['id_profesional'] && $registro)
        <button type="button" class="btn btn-light btn-sm mt-3" onclick="quitar_politica_autorizaciones_asistente({{ $asistente->id }}, {{ $seleccion['id_lugar_atencion'] }});">Usar autorizaciones particulares de cada profesional</button>
    @endif
</form>
