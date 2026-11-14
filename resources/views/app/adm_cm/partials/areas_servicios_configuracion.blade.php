{{-- Una pestaña por cada área del centro (peluquería, laboratorio, imagenología, vacunatorio y dental):
     estado del área y accesos a su catálogo, sus registros y su gestión. --}}
@php
    $detalleAreas = [
        'peluqueria' => ['icono' => 'icon-scissors', 'texto' => 'Configure el catálogo de servicios, precios y duración de las atenciones de peluquería y belleza del centro.'],
        'laboratorio' => ['icono' => 'icon-droplet', 'texto' => 'Configure los exámenes que realiza el laboratorio del centro, sus precios y duración.'],
        'imagenologia' => ['icono' => 'icon-image', 'texto' => 'Configure el catálogo de estudios de imagenología del centro, sus precios y duración.'],
        'vacunatorio' => ['icono' => 'icon-shield', 'texto' => 'Configure el catálogo de vacunas y servicios del vacunatorio del centro, sus precios y duración.'],
        'dental' => ['icono' => 'icon-smile', 'texto' => 'Configure el catálogo de servicios, precios y duración de las atenciones dentales del centro.'],
    ];
    $ctxArea = ['id_institucion' => $institucion->id];
@endphp
@foreach(\App\Services\OperacionVeterinaria::SERVICIOS as $claveArea => $nombreArea)
<div class="tab-pane fade" id="{{ $claveArea }}-config" role="tabpanel" aria-labelledby="{{ $claveArea }}-config-tab">
    <div class="card">
        <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather {{ $detalleAreas[$claveArea]['icono'] ?? 'icon-layers' }}"></i></span>{{ $nombreArea }}</h5></div>
        <div class="card-body">
            <p>{{ $detalleAreas[$claveArea]['texto'] ?? 'Configure el catálogo de servicios, precios y duración de las atenciones de esta área.' }}</p>
            <p>Estado: <strong>{{ in_array($claveArea,$configuracion_establecimiento->servicios_habilitados ?? []) ? 'Habilitada' : 'Deshabilitada' }}</strong>.</p>
            <ul class="nav pill-moderno pill-moderno-white" aria-label="Configuración de {{ $nombreArea }}">
                <li class="nav-item"><a class="nav-link" href="{{ route('vet.areas.index',$ctxArea+['area'=>$claveArea,'pestana'=>'catalogo']) }}">{{ $claveArea === 'laboratorio' ? 'Exámenes del laboratorio' : 'Catálogo de servicios' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('vet.areas.index',$ctxArea+['area'=>$claveArea]) }}">{{ $claveArea === 'laboratorio' ? 'Estadísticas del laboratorio' : 'Reservas y atenciones' }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('vet.areas.index',$ctxArea+['area'=>$claveArea,'vista'=>'gestion']) }}">Gestión del área</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('adm_cm.configuracion',$ctxArea) }}#configuracion-establecimiento">Habilitar o deshabilitar área</a></li>
            </ul>
        </div>
    </div>
</div>
@endforeach
