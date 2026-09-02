@extends('template.profesional.template')
@section('content')
<style>
    .doc-card{border:0;border-radius:16px;box-shadow:0 5px 18px rgba(34,49,68,.10);height:100%;transition:.2s ease;overflow:hidden}
    .doc-card:hover{transform:translateY(-4px);box-shadow:0 10px 25px rgba(34,49,68,.15)}
    .doc-card a{color:inherit}.doc-card-body{display:flex;align-items:center;padding:26px 30px;min-height:165px}
    .doc-icon{width:82px;min-width:82px;height:82px;border-radius:20px;background:#e5f8f7;display:flex;align-items:center;justify-content:center;margin-right:24px}
    .doc-icon img{max-width:54px;max-height:54px}.doc-copy h4{font-size:19px;margin-bottom:7px;color:#34445c}
    .doc-copy p{color:#778398;margin:0;line-height:1.45}.doc-count{margin-left:auto;text-align:center;padding-left:18px}
    .doc-count strong{display:block;font-size:28px;color:#12aaa8}.doc-count small{color:#8792a4;white-space:nowrap}
    .doc-unread{display:inline-block;background:#ff5964;color:#fff;border-radius:20px;padding:3px 9px;font-size:11px;margin-top:5px}
</style>
<div class="pcoded-main-container"><div class="pcoded-content">
    <div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12">
        <div class="page-header-title"><h5 class="m-b-10 font-weight-bold">Mis documentos profesionales</h5></div>
        <ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('profesional.home') }}"><i class="feather icon-home"></i></a></li><li class="breadcrumb-item">Archivo profesional</li></ul>
    </div></div></div></div>

    <div class="row">
        <div class="col-lg-6 mb-4"><div class="card doc-card"><a href="{{ route('profesional.mis_recetas') }}"><div class="doc-card-body">
            <span class="doc-icon"><img src="{{ asset('images/iconos/recetas-ro.svg') }}" alt="Recetas"></span>
            <div class="doc-copy"><h4>Mis recetas</h4><p>Todas las recetas veterinarias que has emitido como profesional.</p></div>
            <div class="doc-count"><strong>{{ $resumenDocumentos['recetas'] }}</strong><small>documentos</small></div>
        </div></a></div></div>
        <div class="col-lg-6 mb-4"><div class="card doc-card"><a href="{{ route('profesional.mis_examenes') }}"><div class="doc-card-body">
            <span class="doc-icon"><img src="{{ asset('images/iconos/examenes-ro.svg') }}" alt="Exámenes"></span>
            <div class="doc-copy"><h4>Mis órdenes de exámenes</h4><p>Órdenes de laboratorio, imagenología y otros exámenes solicitados.</p></div>
            <div class="doc-count"><strong>{{ $resumenDocumentos['examenes'] }}</strong><small>órdenes</small></div>
        </div></a></div></div>
        <div class="col-lg-6 mb-4"><div class="card doc-card"><a href="{{ route('profesional.mis_certificados') }}"><div class="doc-card-body">
            <span class="doc-icon"><img src="{{ asset('images/iconos/certificados-ro.svg') }}" alt="Certificados"></span>
            <div class="doc-copy"><h4>Certificados e informes</h4><p>Certificados veterinarios, interconsultas e informes emitidos.</p></div>
            <div class="doc-count"><strong>{{ $resumenDocumentos['certificados'] }}</strong><small>documentos</small></div>
        </div></a></div></div>
        <div class="col-lg-6 mb-4"><div class="card doc-card"><a href="{{ route('profesional.historial_mensajes') }}"><div class="doc-card-body">
            <span class="doc-icon"><img src="{{ asset('images/iconos/msje.png') }}" alt="Mensajes"></span>
            <div class="doc-copy"><h4>Mis mensajes</h4><p>Comunicaciones de laboratorios clínicos, centros de imagen, farmacias, SAG y otros profesionales.</p>@if($resumenDocumentos['mensajes_no_leidos'] > 0)<span class="doc-unread">{{ $resumenDocumentos['mensajes_no_leidos'] }} sin leer</span>@endif</div>
            <div class="doc-count"><strong>{{ $resumenDocumentos['mensajes'] }}</strong><small>mensajes</small></div>
        </div></a></div></div>
    </div>
</div></div>
@endsection
