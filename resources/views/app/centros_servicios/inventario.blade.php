@extends('app.centros_servicios.layout')
@section('content')
<div class="page-header"><h4>Lugares y ventas</h4><p>Administra el inventario del cementerio. Las ventas confirmadas quedan en Solicitudes y agenda.</p></div>
<div class="card">
    <div class="card-header"><h5>Agregar lugar</h5></div>
    <div class="card-body">@include('app.centros_servicios.lugar_form', ['lugar' => null])</div>
</div>
@forelse($lugares as $lugar)
    <div class="card">
        <div class="card-header">
            <h5>{{ $lugar->codigo }} · {{ $lugar->sector }}</h5>
            <span class="badge {{ $lugar->disponible ? 'badge-success' : 'badge-secondary' }}">{{ $lugar->disponible ? 'En catálogo' : 'Fuera de catálogo' }}</span>
        </div>
        <div class="card-body">@include('app.centros_servicios.lugar_form')</div>
    </div>
@empty
    <p class="text-muted">Todavía no hay lugares registrados.</p>
@endforelse
{{ $lugares->links('pagination::bootstrap-4') }}
@endsection
