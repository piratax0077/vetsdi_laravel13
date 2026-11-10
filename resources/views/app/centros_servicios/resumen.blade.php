@php
    $servicio = \App\Services\CentrosServiciosMascotas::SERVICIOS[$centro->tipo][$solicitud->servicio];
    $estado = \App\Services\CentrosServiciosMascotas::ESTADOS[$solicitud->estado];
@endphp
<div class="card">
    <div class="card-header"><h5>{{ $servicio }} · Solicitud {{ $solicitud->id }}</h5><span class="badge badge-light-info">{{ $estado }}</span></div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Centro</dt><dd class="col-sm-9">{{ $centro->nombre }}</dd>
            <dt class="col-sm-3">Mascota</dt><dd class="col-sm-9">{{ $mascota?->nombre ?? 'Sin registro' }}</dd>
            <dt class="col-sm-3">Tutor</dt><dd class="col-sm-9">{{ $tutor?->nombres }} {{ $tutor?->apellido_uno }}</dd>
            <dt class="col-sm-3">Inicio</dt><dd class="col-sm-9">{{ \Carbon\Carbon::parse($solicitud->inicio)->format('d/m/Y') }}</dd>
            @if(in_array($solicitud->servicio, ['guarderia', 'hotel'], true))
                <dt class="col-sm-3">Término del período</dt><dd class="col-sm-9">{{ \Carbon\Carbon::parse($solicitud->fin)->format('d/m/Y') }}</dd>
            @endif
            @if($solicitud->periodo)
                <dt class="col-sm-3">Plan diurno</dt><dd class="col-sm-9">{{ $solicitud->cantidad }} {{ $solicitud->periodo }}{{ $solicitud->cantidad == 1 ? '' : ($solicitud->periodo === 'mes' ? 'es' : 's') }}</dd>
            @endif
            @if($solicitud->retiro)<dt class="col-sm-3">Dirección de retiro</dt><dd class="col-sm-9">{{ $solicitud->direccion_retiro }}</dd>@endif
            @if($solicitud->peluqueria)<dt class="col-sm-3">Peluquería</dt><dd class="col-sm-9">Incluye acompañamiento e informe.</dd>@endif
            @if($lugar)<dt class="col-sm-3">Lugar</dt><dd class="col-sm-9">{{ $lugar->codigo }} · {{ $lugar->sector }}</dd>@endif
            <dt class="col-sm-3">Valor (CLP)</dt><dd class="col-sm-9">{{ $solicitud->precio === null ? 'Por confirmar' : '$'.number_format($solicitud->precio, 0, ',', '.') }}</dd>
            @if($solicitud->indicaciones)<dt class="col-sm-3">Indicaciones</dt><dd class="col-sm-9">{{ $solicitud->indicaciones }}</dd>@endif
        </dl>
    </div>
</div>
<div class="card">
    <div class="card-header"><h5>Seguimiento e informes</h5></div>
    <div class="card-body">
        @forelse($bitacora as $evento)
            <article class="border-bottom py-3">
                <strong>{{ \App\Services\CentrosServiciosMascotas::EVENTOS[$evento->tipo] ?? 'Cambio de estado' }}</strong>
                <small class="text-muted ml-2">{{ \Carbon\Carbon::parse($evento->created_at)->setTimezone('America/Santiago')->format('d/m/Y H:i') }}</small>
                <p class="mb-0">{!! nl2br(e($evento->detalle)) !!}</p>
            </article>
        @empty<p class="text-muted mb-0">Aún no se han registrado gestiones.</p>
        @endforelse
    </div>
</div>
