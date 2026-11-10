@extends('template.usuario.template')
@section('content')
<div class="pcoded-main-container"><main class="pcoded-content">
    @include('app.centros_servicios.mensajes')
    <a class="btn btn-outline-secondary mb-3" href="{{ route('paciente.home') }}">Volver a mi escritorio</a>
    <div class="page-header"><h4>Guardería, hotel y cementerio de mascotas</h4><p>Encuentra un centro y consulta el estado de tus solicitudes.</p></div>
    <div class="row">
        @forelse($centros as $centro)
            <div class="col-md-6 col-xl-4"><div class="card">
                <div class="card-header"><h5>{{ $centro->nombre }}</h5></div>
                <div class="card-body">
                    <p>{{ \App\Services\CuentasService::ETIQUETAS[$centro->tipo] }}</p>
                    <p>{{ $centro->direccion }}</p>
                    <a class="btn btn-info" href="{{ route('paciente.centros_servicios.formulario', $centro->id) }}">Ver servicios</a>
                </div>
            </div></div>
        @empty<div class="col-12"><p class="text-muted">Todavía no hay centros publicados.</p></div>
        @endforelse
    </div>
    {{ $centros->links('pagination::bootstrap-4') }}
    <div class="card">
        <div class="card-header"><h5>Mis solicitudes</h5></div>
        <div class="card-body">
            <div class="table-responsive"><table class="table table-sm">
                <thead><tr><th>Centro</th><th>Mascota</th><th>Servicio</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody>
                @forelse($solicitudes as $s)
                    <tr><td>{{ $s->centro }}</td><td>{{ $s->mascota }}</td>
                        <td>{{ \App\Services\CentrosServiciosMascotas::SERVICIOS[$s->tipo][$s->servicio] }}</td>
                        <td>{{ \App\Services\CentrosServiciosMascotas::ESTADOS[$s->estado] }}</td>
                        <td><a class="btn btn-outline-info btn-sm" href="{{ route('paciente.centros_servicios.detalle', $s->id) }}">Ver seguimiento</a></td></tr>
                @empty<tr><td colspan="5">No tienes solicitudes.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            {{ $solicitudes->links('pagination::bootstrap-4') }}
        </div>
    </div>
    <a href="{{ route('centros_servicios.perfiles') }}" class="btn btn-outline-info">Administro un centro</a>
</main></div>
@endsection
