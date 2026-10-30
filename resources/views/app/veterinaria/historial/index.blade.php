@extends('template.adm_cm.template')
@section('content')
@php
    // Las fechas con hora se muestran como HH:MM, sin segundos
    $sinSegundos = fn ($valor) => preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $valor);
@endphp
<div class="pcoded-main-container"><div class="pcoded-content configuracion-administrador vista-area-veterinaria">
    <div class="row">
        <div class="col-md-12 mb-3 encabezado-pagina">
            <div>
                <a href="{{ route('adm_cm.home') }}" class="text-muted f-12 d-print-none" onclick="if (document.referrer && history.length > 1) { history.back(); return false; }">
                    <i class="feather icon-arrow-left" aria-hidden="true"></i> Atrás
                </a>
                <h5 class="f-26 d-block mb-0">Historial de cambios</h5>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Historial de cambios</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Fecha</th><th>Sección</th><th>Registro</th><th>Usuario</th></tr></thead>
                    <tbody>
                        @forelse($auditoria as $a)
                        <tr><td>{{ $sinSegundos($a->created_at) }}</td><td>{{ $a->tabla }}</td><td>#{{ $a->registro_id }}</td><td>#{{ $a->id_usuario }}</td></tr>
                        @empty
                        <tr><td colspan="4">Sin cambios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $auditoria->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div></div>
@endsection
