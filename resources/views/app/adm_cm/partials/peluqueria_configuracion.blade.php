<div class="tab-pane fade" id="peluqueria-config" role="tabpanel" aria-labelledby="peluqueria-config-tab">
    <div class="card">
        <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-scissors"></i></span>Peluquería y belleza</h5></div>
        <div class="card-body">
            <p>Configure el catálogo de servicios, precios y duración de las atenciones de peluquería y belleza del centro.</p>
            <p>Estado: <strong>{{ in_array('peluqueria',$configuracion_establecimiento->servicios_habilitados ?? []) ? 'Habilitada' : 'Deshabilitada' }}</strong>.</p>
            <ul class="nav pill-moderno pill-moderno-white" aria-label="Configuración de peluquería y belleza">
                <li class="nav-item"><a class="nav-link" href="{{ route('vet.areas.index',['area'=>'peluqueria','id_institucion'=>$institucion->id,'pestana'=>'catalogo']) }}">Catálogo de servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('vet.areas.index',['area'=>'peluqueria','id_institucion'=>$institucion->id]) }}">Reservas y atenciones</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('adm_cm.configuracion',['id_institucion'=>$institucion->id]) }}#configuracion-establecimiento">Habilitar o deshabilitar área</a></li>
            </ul>
        </div>
    </div>
</div>
