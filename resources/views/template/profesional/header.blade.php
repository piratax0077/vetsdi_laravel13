<header class="navbar pcoded-header navbar-expand-lg navbar-light header-blue">
    <div class="m-header">
            <a class="mobile-menu" id="mobile-collapse"><span></span></a>
            <a href="#!" class="b-brand">
                <img src="{{ asset('/images/logo_pais.png') }}" alt="" class="logo" height="45px">
            </a>
            <a href="#!" class="mob-toggler">
                <i class="feather icon-more-vertical icono-header"></i>
            </a>
        </div>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">

                @if(isset($mensajes))

                        <li>
                            <div class="dropdown drp-user">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Mensajes" data-placement="button">
                                    <i class="feather icon-mail icono-header"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right profile-notification">
                                    <div class="pro-head font-weight-bold f-16 py-2">
                                        Mensajes  <span class="badge badge-danger">{{ count($mensajes) }}</span>
                                    </div>
                                    <ul></ul>
                                    <ul class="pro-body">
                                        @foreach ($mensajes as $mensaje)
                                            @if (isset($mensaje->datos_mensaje))

                                                <li>
                                                    <a href="{{ route('profesional.mensaje', ['id' => $mensaje->id]) }}" class="dropdown-item">
                                                        <div class="media">
                                                            <img class="img-radius img-40" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="Foto de perfil" style="width: 50px;">
                                                            <div class="media-body ml-3">
                                                                @if (array_key_exists('titulo', $mensaje->datos_mensaje))
                                                                    <h6 class="pro-title">{{ $mensaje->datos_mensaje['titulo'] }}</h6>
                                                                @else
                                                                    <h6 class="pro-title">Sin titulo</h6>
                                                                @endif
                                                                <p class="pro-date">{{ $mensaje->created_at->diffForHumans() }}</p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </li>

                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </li>

                @endif
                @if (Auth::user())

                    @php
                        // El botón lleva al escritorio que el profesional tiene activo en la sesión
                        $rolEscritorioActivo = session(\App\Http\Controllers\SeleccionCuentaController::SESION_ROL_ACTIVO);
                        $rutaEscritorioActivo = \App\Services\CuentasService::esTipoValido($rolEscritorioActivo)
                            ? \App\Services\CuentasService::RUTAS_ESCRITORIO[$rolEscritorioActivo]
                            : 'profesional.home';

                        if (! \Route::has($rutaEscritorioActivo)) {
                            $rutaEscritorioActivo = 'profesional.home';
                        }
                    @endphp

                    <li class="d-flex align-items-center mr-2">
                        <a href="{{ route($rutaEscritorioActivo) }}" class="btn btn-outline-header btn-xxs d-inline-flex align-items-center" style="white-space:nowrap;" data-toggle="tooltip" data-placement="bottom" title="Volver a mi escritorio">
                            <i class="feather icon-home mr-1"></i>Mi escritorio
                        </a>
                    </li>

                    @include('template.partials.workspace_switcher')

                    <li>
                        @include('template.partials.perfil_encabezado', ['tipoPerfil' => 'profesional'])
                    </li>

                @endif
            </ul>
        </div>
</header>
