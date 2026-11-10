@extends('template.usuario.template')
@section('content')
<div class="pcoded-main-container"><main class="pcoded-content">
    @include('app.centros_servicios.mensajes')
    <a class="btn btn-outline-secondary mb-3" href="{{ route('paciente.centros_servicios.catalogo') }}">Volver a servicios</a>
    @include('app.centros_servicios.resumen')
    @if(in_array($solicitud->estado, ['pendiente', 'confirmada'], true))
        <form method="post" action="{{ route('paciente.centros_servicios.cancelar', $solicitud->id) }}">
            @csrf<button class="btn btn-outline-danger" onclick="return confirm('¿Cancelar esta solicitud?')">Cancelar solicitud</button>
        </form>
    @endif
</main></div>
@endsection
