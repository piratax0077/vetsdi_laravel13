@extends('app.centros_servicios.layout')
@section('content')
<a class="btn btn-outline-secondary mb-3" href="{{ route($centro->tipo.'.home') }}">Volver a solicitudes</a>
@include('app.centros_servicios.resumen')
@php
    $acciones = match($solicitud->estado) {
        'pendiente' => ['confirmar' => $solicitud->servicio === 'lugar' ? 'Registrar venta' : 'Confirmar solicitud', 'cancelar' => 'Cancelar solicitud'],
        'confirmada' => ['iniciar' => 'Registrar ingreso', 'cancelar' => 'Cancelar solicitud'],
        'en_servicio' => ['finalizar' => 'Finalizar servicio'],
        default => [],
    };
@endphp
@if($acciones)
<div class="card">
    <div class="card-header"><h5>Registrar gestión</h5></div>
    <div class="card-body">
        <form method="post" action="{{ route($centro->tipo.'.operar', $solicitud->id) }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="accion">Acción</label>
                    <select id="accion" class="form-control" name="accion" required>
                        <option value="">Selecciona</option>
                        @foreach($acciones as $clave => $texto)<option value="{{ $clave }}" @selected(old('accion') === $clave)>{{ $texto }}</option>@endforeach
                        @if($solicitud->estado !== 'pendiente')
                            @foreach($eventos as $evento)
                                <option value="{{ $evento }}" @selected(old('accion') === $evento)>{{ \App\Services\CentrosServiciosMascotas::EVENTOS[$evento] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @if($solicitud->estado === 'pendiente')
                    <div class="form-group col-md-6">
                        <label for="precio">Valor acordado (CLP)</label>
                        <input class="form-control" id="precio" name="precio" type="number" min="0" value="{{ old('precio', $solicitud->precio) }}">
                    </div>
                @endif
            </div>
            <div class="form-group">
                <label for="detalle">Detalle para el tutor</label>
                <textarea class="form-control" id="detalle" name="detalle" rows="4" maxlength="3000" placeholder="Responsable del retiro, lugar de la visita o resultado del servicio">{{ old('detalle') }}</textarea>
            </div>
            <button class="btn btn-info">Guardar gestión</button>
        </form>
    </div>
</div>
@endif
@endsection
