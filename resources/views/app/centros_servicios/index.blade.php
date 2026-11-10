@extends('app.centros_servicios.layout')
@section('content')
<div class="page-header">
    <h4>{{ $centro->nombre }}</h4>
    <p>Gestiona las solicitudes y registra el avance de los servicios.</p>
</div>
@if(!$centro->publicado)
    <div class="alert alert-info">Completa la configuración y publica el centro para recibir solicitudes.
        <a href="{{ route($centro->tipo.'.configuracion') }}">Ir a configuración</a>
    </div>
@endif
<div class="card">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Solicitudes y agenda</h5></div>
    <div class="card-body">
        <form class="form-inline mb-3" method="get">
            <label class="mr-2" for="estado">Estado</label>
            <select id="estado" name="estado" class="form-control mr-2">
                <option value="">Todos</option>
                @foreach(\App\Services\CentrosServiciosMascotas::ESTADOS as $clave => $estado)
                    <option value="{{ $clave }}" @selected(request('estado') === $clave)>{{ $estado }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-info">Filtrar</button>
        </form>
        <div class="table-responsive"><table class="table table-sm">
            <thead><tr><th>Fecha</th><th>Mascota</th><th>Tutor</th><th>Servicio</th><th>Estado</th><th>Acción</th></tr></thead>
            <tbody>
            @forelse($solicitudes as $solicitud)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($solicitud->inicio)->format('d/m/Y') }}</td>
                    <td>{{ $solicitud->mascota ?? 'Sin registro' }}</td>
                    <td>{{ $solicitud->tutor }} {{ $solicitud->apellido_uno }}</td>
                    <td>{{ \App\Services\CentrosServiciosMascotas::SERVICIOS[$centro->tipo][$solicitud->servicio] }}</td>
                    <td>{{ \App\Services\CentrosServiciosMascotas::ESTADOS[$solicitud->estado] }}</td>
                    <td><a class="btn btn-outline-info btn-sm" href="{{ route($centro->tipo.'.detalle', $solicitud->id) }}">Ver solicitud</a></td>
                </tr>
            @empty<tr><td colspan="6">No hay solicitudes para este filtro.</td></tr>
            @endforelse
            </tbody>
        </table></div>
        {{ $solicitudes->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
