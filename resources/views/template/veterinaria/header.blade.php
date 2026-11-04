<header class="navbar pcoded-header navbar-expand-lg navbar-light header-blue">
	<div class="m-header">
        <a class="mobile-menu" id="mobile-collapse"><span></span></a>
        <a href="#!" class="b-brand">
            <img src="{{ asset('/images/logo_pais.png') }}" alt="" class="logo" height="45px">
        </a>
        <a href="#!" class="mob-toggler">
            <i class="feather icon-more-vertical"></i>
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

                    @if (false)
                        <li>
                            <div class="dropdown drp-user">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Cambiar escritorio" data-placement="button" >
                                    <i class="feather icon-refresh-cw" style="font-size: 1.2rem!important;"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right profile-notification">
                                    <div class="pro-head font-weight-bold f-16 py-2">
                                        <span>Cambiar escritorio</span>
                                    </div>
                                    <ul></ul>
                                    <ul class="pro-body">
                                        @if (Auth::user()->hasRole('Paciente') || Auth::user()->hasRole('Admin'))
                                            <li>
                                                <a href="{{ ROUTE('paciente.home') }}" class="dropdown-item">
                                                    <i class="feather icon-user"></i>
                                                    Escritorio paciente
                                                </a>
                                            </li>
                                        @endif

                                        @if (Auth::user()->hasRole('Profesional') || Auth::user()->hasRole('Admin'))
                                            <li>
                                                <a href="{{ ROUTE('profesional.home') }}" class="dropdown-item"><i
                                                        class="feather icon-user"></i>
                                                    Escritorio profesional
                                                </a>
                                            </li>
                                        @endif


                                        @if (Auth::user()->hasRole('Asistente') || Auth::user()->hasRole('Admin'))
                                            <li><a href="{{ ROUTE('asistente.home') }}" class="dropdown-item"><i
                                                        class="feather icon-user"></i>Escritorio
                                                    Asistente</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </li>
                    @endif

                    <li>
                        @include('template.partials.perfil_encabezado', ['tipoPerfil' => 'profesional'])
                    </li>

                @endif
        </ul>
    </div>
</header>
