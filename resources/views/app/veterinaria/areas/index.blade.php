@extends('template.adm_cm.template')
@section('content')
@php
    // Las fechas con hora se muestran como HH:MM, sin segundos
    $sinSegundos = fn ($valor) => preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $valor);
    $urlSeccion = fn (array $extra = []) => route('vet.areas.index', $ctx + ['area' => $areaCentro] + ($seccion === 'plazas' ? ['vista' => 'plazas'] : []) + $extra);
    // Las áreas que ya tenían pantalla de administración la conservan a un clic
    $gestionArea = ['dental' => 'adm_cm.dental', 'imagenologia' => 'adm_cm.examenes'][$areaCentro] ?? null;
    // Si un formulario volvió con errores se abre su pestaña, aunque la dirección apunte a la otra
    $formularioPrevio = (string) old('_formulario');
    $pestanaPlazas = $formularioPrevio !== '' ? str_starts_with($formularioPrevio,'plaza-') : $seccion === 'plazas';
@endphp
<div class="pcoded-main-container"><div class="pcoded-content configuracion-administrador vista-area-veterinaria">
    <div class="row">
        <div class="col-md-12 mb-3 encabezado-pagina">
            <div>
                <a href="{{ route('adm_cm.home') }}" class="text-muted f-12 d-print-none" onclick="if (document.referrer && history.length > 1) { history.back(); return false; }">
                    <i class="feather icon-arrow-left" aria-hidden="true"></i> Atrás
                </a>
                <h5 class="f-26 d-block mb-0">{{ $tituloArea }}</h5>
            </div>
            @if($gestionArea)
            <div class="ml-auto">
                <a class="btn btn-outline-info btn-sm" href="{{ route($gestionArea) }}"><i class="feather icon-settings"></i> Gestión del área</a>
            </div>
            @endif
        </div>
    </div>
    @if($areaCentro === 'hospitalizacion')
    <ul class="nav pill-moderno pill-moderno-white" role="tablist" aria-label="Secciones de hospitalización">
        <li class="nav-item"><a class="nav-link {{ $pestanaPlazas ? '' : 'active' }}" data-toggle="pill" href="#pestana_hospitalizacion" role="tab">Hospitalización</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaPlazas ? 'active' : '' }}" data-toggle="pill" href="#pestana_plazas" role="tab">Plazas de hospitalización</a></li>
    </ul>
    @endif
    @if(session('alerta_area') || $errors->any())
    <div class="alertas-flotantes">
        @if(session('alerta_area'))
        <div class="alert alert-inverse alert-dismissible fade show alerta-flotante" role="status">
            <i class="feather icon-check-circle alerta-flotante-icono alerta-flotante-exito"></i>
            <div class="alerta-flotante-texto">{{ session('alerta_area') }}</div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-inverse alert-dismissible fade show alerta-flotante" role="alert">
            <i class="feather icon-alert-circle alerta-flotante-icono alerta-flotante-error"></i>
            <div class="alerta-flotante-texto"><strong>No se guardaron los cambios.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
        </div>
        @endif
    </div>
    @endif
    @if($areaCentro === 'hospitalizacion')
    <div class="tab-content">
        <div class="tab-pane fade {{ $pestanaPlazas ? '' : 'show active' }}" id="pestana_hospitalizacion" role="tabpanel">
            @unless($configuracion->hospitalizacion_habilitada)
            <div class="alert alert-warning">Hospitalización deshabilitada. Puede consultar su historia y finalizar ingresos existentes.</div>
            @endunless
            @if(!$seccionesHospitalizacion['hospitalizacion']['mascotas'])
            <div class="alert alert-info">No hay mascotas vinculadas a este establecimiento. <a href="{{ route('vet.operacion.index',$ctx+['seccion'=>'pacientes']) }}">Vincular una mascota</a> para reservar o registrar su ingreso.</div>
            @endif
            @include('app.veterinaria.operacion.registros',$seccionesHospitalizacion['hospitalizacion']+['urlSeccion'=>fn (array $extra = []) => route('vet.areas.index',$ctx+['area'=>'hospitalizacion']+$extra)])
        </div>
        <div class="tab-pane fade {{ $pestanaPlazas ? 'show active' : '' }}" id="pestana_plazas" role="tabpanel">
            @include('app.veterinaria.operacion.registros',$seccionesHospitalizacion['plazas']+['urlSeccion'=>fn (array $extra = []) => route('vet.areas.index',$ctx+['area'=>'hospitalizacion','vista'=>'plazas']+$extra)])
        </div>
    </div>
    @else
    @if(isset($mascotas) && !$mascotas)
    <div class="alert alert-info">No hay mascotas vinculadas a este establecimiento. <a href="{{ route('vet.operacion.index',$ctx+['seccion'=>'pacientes']) }}">Vincular una mascota</a> para reservar o registrar su ingreso.</div>
    @endif
    @if(isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]) && !in_array($seccion,$configuracion->servicios_habilitados))
    <div class="alert alert-warning">Esta área está deshabilitada para nuevas reservas. Puede consultar y finalizar las reservas existentes.</div>
    @endif
    @include('app.veterinaria.operacion.registros')
    @endif
</div></div>
@endsection
@section('page-script')
<script src="{{ asset('js/operacion-veterinaria.js') }}?t={{ time() }}"></script>
@endsection
