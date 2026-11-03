<div class="card">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Exámenes de laboratorio realizados</h5></div>
    <div class="card-body">
        <form method="get" class="form-row mb-3">
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
            <div class="col-md-4 form-group"><label class="floating-label-activo-sm" for="laboratorio-desde">Desde</label><input id="laboratorio-desde" name="desde" type="date" value="{{ $estadisticasLaboratorio['desde'] }}" class="form-control form-control-sm" required></div>
            <div class="col-md-4 form-group"><label class="floating-label-activo-sm" for="laboratorio-hasta">Hasta</label><input id="laboratorio-hasta" name="hasta" type="date" value="{{ $estadisticasLaboratorio['hasta'] }}" class="form-control form-control-sm" required></div>
            <div class="col-md-4 form-group align-self-end"><button class="btn btn-info btn-sm" type="submit">Filtrar</button> <a href="{{ $urlSeccion() }}" class="btn btn-light btn-sm">Mes actual</a></div>
        </form>
        <div class="table-responsive"><table class="table table-striped table-sm">
            <thead><tr><th>Fecha</th><th>Mascota</th><th>Examen</th><th>Estado</th></tr></thead>
            <tbody>
                @forelse($estadisticasLaboratorio['examenes'] as $examen)
                <tr><td>{{ $examen->fecha_consulta }}</td><td>{{ $examen->mascota ?: 'Sin mascota asociada' }}</td><td>{{ $examen->alias_examen ?: 'Sin nombre en agenda' }}</td><td><span class="badge badge-light-success">Realizado</span></td></tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">{{ $estadisticasLaboratorio['disponible'] ? 'No hay exámenes realizados en el período seleccionado.' : 'La información de la agenda de laboratorio aún no está disponible.' }}</td></tr>
                @endforelse
            </tbody>
        </table></div>
        @if($estadisticasLaboratorio['disponible'])
            {{ $estadisticasLaboratorio['examenes']->links('pagination::bootstrap-4') }}
        @endif
    </div>
</div>
