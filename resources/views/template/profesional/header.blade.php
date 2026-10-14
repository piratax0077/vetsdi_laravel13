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

                    @include('template.partials.mensajes_encabezado')

                    <li>
                        @include('template.partials.perfil_encabezado', ['tipoPerfil' => 'profesional'])
                    </li>

                @endif
            </ul>
        </div>
</header>
