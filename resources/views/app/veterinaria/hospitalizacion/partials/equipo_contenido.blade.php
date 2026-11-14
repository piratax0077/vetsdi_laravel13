{{-- Equipo del servicio de hospitalización: asignar accesos y ver los ya configurados.
     Lo usan la página del equipo y la pestaña "Equipo del servicio" de Hospitalización ($enArea). --}}
@php($enArea = $enArea ?? false)
<div class="card">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Asignar o actualizar acceso al servicio</h5></div>
    <div class="card-body">
        <p class="text-muted mb-4">El personal vinculado accede desde «Cambiar perfil» → «Hospitalización veterinaria». Enfermería registra controles y cuidados; el profesional veterinario gestiona ingresos, evolución, cirugía, alta y controles posteriores.</p>
        <form method="post" action="{{ route('vet.hospital.equipo.guardar') }}">
            @csrf
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
            @if($enArea)<input type="hidden" name="_volver" value="area"><input type="hidden" name="_formulario" value="equipo-acceso">@endif
            <div class="form-row pt-2">
                <div class="col-md-6"><div class="form-group"><label class="floating-label-activo-sm" for="equipo-usuario">Integrante vinculado al centro</label><select class="form-control form-control-sm" id="equipo-usuario" name="id_usuario" required><option value="">Seleccione</option>@foreach($candidatos as $c)<option value="{{ $c->id }}" @selected(old('id_usuario') == $c->id)>{{ $c->name }}</option>@endforeach</select></div></div>
                <div class="col-md-3 col-sm-6"><div class="form-group"><label class="floating-label-activo-sm" for="equipo-perfil">Rol en el servicio</label><select class="form-control form-control-sm" id="equipo-perfil" name="perfil" required><option value="profesional">Profesional veterinario</option><option value="enfermeria" @selected(old('perfil') === 'enfermeria')>Enfermería veterinaria</option></select></div></div>
                <div class="col-md-3 col-sm-6"><div class="form-group"><label class="floating-label-activo-sm" for="equipo-activo">Acceso</label><select class="form-control form-control-sm" id="equipo-activo" name="activo" required><option value="1">Activo</option><option value="0" @selected(old('activo') === '0')>Desactivado</option></select></div></div>
            </div>
            <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save mr-1"></i>Guardar acceso</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Accesos configurados</h5></div>
    <div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Integrante</th><th>Rol</th><th>Estado</th></tr></thead><tbody>
            @forelse($equipo as $e)<tr><td>{{ $e->name }}</td><td>{{ $e->perfil === 'enfermeria' ? 'Enfermería veterinaria' : 'Profesional veterinario' }}</td><td><span class="badge {{ $e->activo ? 'badge-success' : 'badge-secondary' }}">{{ $e->activo ? 'Activo' : 'Desactivado' }}</span></td></tr>
            @empty<tr><td colspan="3" class="text-center text-muted text-wrap">No hay accesos adicionales configurados. Los profesionales vinculados al centro ya pueden ingresar al servicio.</td></tr>@endforelse
        </tbody></table></div>
    </div>
</div>
