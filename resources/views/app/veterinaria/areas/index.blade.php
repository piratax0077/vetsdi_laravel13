@extends('template.adm_cm.template')
@section('content')
@php
    // Las fechas con hora se muestran como HH:MM, sin segundos
    $sinSegundos = fn ($valor) => preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $valor);
    $urlSeccion = fn (array $extra = []) => route('vet.areas.index', $ctx + ['area' => $areaCentro] + ($seccion === 'plazas' ? ['vista' => 'plazas'] : []) + $extra);
    // Si un formulario volvió con errores se abre su pestaña, aunque la dirección apunte a la otra
    $formularioPrevio = (string) old('_formulario');
    // Hospitalización reúne en pestañas lo que antes eran páginas aparte: plazas, gestión del área y equipo del servicio
    $pestanaActiva = match (true) {
        str_starts_with($formularioPrevio,'plaza-') => 'plazas',
        str_starts_with($formularioPrevio,'gestion-') => 'gestion',
        str_starts_with($formularioPrevio,'equipo-') => 'equipo',
        $formularioPrevio !== '' => 'hospitalizacion',
        in_array(request('vista'),['plazas','gestion','equipo'],true) => request('vista'),
        default => 'hospitalizacion',
    };
@endphp
<div class="pcoded-main-container"><div class="pcoded-content configuracion-administrador vista-area-veterinaria">
    <div class="row">
        <div class="col-md-12 mb-3 encabezado-pagina">
            <div>
                <h5 class="f-26 d-block mb-0">{{ $tituloArea }}</h5>
            </div>
            {{-- Gestión del área (y Equipo del servicio en Hospitalización) son pestañas de esta misma vista --}}
        </div>
    </div>
    @if($areaCentro === 'hospitalizacion')
    <ul class="nav pill-moderno pill-moderno-white" role="tablist" aria-label="Secciones de hospitalización">
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'hospitalizacion' ? 'active' : '' }}" data-toggle="pill" href="#pestana_hospitalizacion" role="tab">Hospitalización</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'plazas' ? 'active' : '' }}" data-toggle="pill" href="#pestana_plazas" role="tab">Plazas de hospitalización</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'gestion' ? 'active' : '' }}" data-toggle="pill" href="#pestana_gestion" role="tab">Gestión del área</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'equipo' ? 'active' : '' }}" data-toggle="pill" href="#pestana_equipo" role="tab">Equipo del servicio</a></li>
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
        <div class="tab-pane fade {{ $pestanaActiva === 'hospitalizacion' ? 'show active' : '' }}" id="pestana_hospitalizacion" role="tabpanel">
            @unless($configuracion->hospitalizacion_habilitada)
            <div class="alert alert-warning">Hospitalización deshabilitada. Puede consultar su historia y finalizar ingresos existentes.</div>
            @endunless
            @if($seccionesHospitalizacion['hospitalizacion']['filas']->isEmpty())
            <div class="alert alert-info">No hay registros.</div>
            @endif
            @include('app.veterinaria.operacion.registros',$seccionesHospitalizacion['hospitalizacion']+['urlSeccion'=>fn (array $extra = []) => route('vet.areas.index',$ctx+['area'=>'hospitalizacion']+$extra)])
        </div>
        <div class="tab-pane fade {{ $pestanaActiva === 'plazas' ? 'show active' : '' }}" id="pestana_plazas" role="tabpanel">
            @include('app.veterinaria.operacion.registros',$seccionesHospitalizacion['plazas']+['urlSeccion'=>fn (array $extra = []) => route('vet.areas.index',$ctx+['area'=>'hospitalizacion','vista'=>'plazas']+$extra)])
        </div>
        <div class="tab-pane fade {{ $pestanaActiva === 'gestion' ? 'show active' : '' }}" id="pestana_gestion" role="tabpanel">
            @include('app.veterinaria.areas.partials.gestion_contenido',$gestionDelArea+['enArea'=>true])
        </div>
        <div class="tab-pane fade {{ $pestanaActiva === 'equipo' ? 'show active' : '' }}" id="pestana_equipo" role="tabpanel">
            @include('app.veterinaria.hospitalizacion.partials.equipo_contenido',$equipoHospitalizacion+['enArea'=>true])
        </div>
    </div>
    @else
    @if(in_array($seccion,['imagenologia','vacunatorio','peluqueria']) && $filas->isEmpty())
    <div class="alert alert-info">No hay registros.</div>
    @elseif(!in_array($seccion,['laboratorio','imagenologia','vacunatorio','peluqueria']) && isset($mascotas) && !$mascotas)
    <div class="alert alert-info">No hay mascotas vinculadas a este establecimiento. <a href="{{ route('vet.operacion.index',$ctx+['seccion'=>'pacientes']) }}">Vincular una mascota</a> para reservar o registrar su ingreso.</div>
    @endif
    @if($seccion !== 'laboratorio' && isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]) && !in_array($seccion,$configuracion->servicios_habilitados))
    <div class="alert alert-warning">Esta área está deshabilitada para nuevas reservas. Puede consultar y finalizar las reservas existentes.</div>
    @endif
    @include('app.veterinaria.operacion.registros')
    @endif
</div></div>
@endsection
@section('page-script')
<script src="{{ asset('js/operacion-veterinaria.js') }}?t={{ time() }}"></script>
@endsection
