@if (isset($fichaAtencion) && !empty($fichaAtencion))

    <nav class="pcoded-navbar menu-light">
        <div class="navbar-wrapper">
            <div class="navbar-content scroll-div">
                <div class="">
                    <div class="main-menu-header">
                        <img class="img-radius" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="Profesional">
                        <div class="user-details">
                            <div id="more-details">{{ @Auth::user()->name }} <i class="fa fa-caret-down"></i></div>
                        </div>
                    </div>
                    <div id="nav-user-link">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <a href="#" data-toggle="tooltip" title="Mi perfil">
                                    <i class="feather icon-user"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <form action="{{ route('logout') }}" method="post" id="closeSession">
                                    @csrf
                                    <a data-toggle="tooltip" title="Cerrar sesión" class="text-danger" href="javascript:{}" onclick="document.getElementById('closeSession').submit();">
                                        <i class="feather icon-power"></i>
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <ul class="nav pcoded-inner-navbar ">
                    <li class="nav-item pcoded-menu-caption text-center">
                        <!--<label>Menú</label>-->
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-home"></i></span><span class="pcoded-mtext text-center">Mi Escritorio</span></a>
                        <ul class="pcoded-submenu">
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.home') }}', 'Escritorio medichile ');">Escritorio medichile </a></li>
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.mi_perfil') }}', 'Mi Perfil');">Mi Perfil</a></li>
                        </ul>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-activity"></i></span><span class="pcoded-mtext text-center">Mis Herramientas</span></a>
                        <ul class="pcoded-submenu">
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.index_receta_online') }}', 'Receta online' );" >Receta online</a></li>
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.mi_agenda') }}', 'Mi Agenda' );" >Mi Agenda</a></li>
                        </ul>
                    </li>
                    <!--<li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-search"></i></span><span class="pcoded-mtext text-center">Buscadores</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="buscar_paciente.php">Buscar Pacientes</a></li>
                            <li><a href="buscar_profesional.php">Buscar Profesionales</a></li>
                            <li><a href="buscar_servicio.php">Buscar Servicios</a></li>
                            <li><a href="buscar_institucion.php">Buscar Instituciones</a></li>
                        </ul>
                    </li>-->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-menu"></i></span><span class="pcoded-mtext text-center">Otras Opciones</span></a>
                        <ul class="pcoded-submenu">
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.lugares_atencion') }}', 'Configuración de <br>mis lugares de atención');" >Configuración de <br>mis lugares de atención</a></li>
                            <li><a onclick="menuValidarSalidaFicha('suscripcionmedichile.php', 'Renovar suscripción');" >Renovar suscripción</a></li>
                        </ul>
                    </li>
                </ul>
                <div id="info_cliente" class="ficha-resumen">
                    @if(isset($mascota) && $mascota)
                        <div class="ficha-resumen-cabecera">
                            <div class="ficha-resumen-titulo">
                                <span class="ficha-resumen-etiqueta">Información de la mascota</span>
                                <strong class="ficha-resumen-nombre">{{ $mascota->nombre ?: '-' }}</strong>
                            </div>
                        </div>
                        <dl class="ficha-resumen-datos">
                            <div class="ficha-resumen-dato">
                                <dt>Especie</dt>
                                <dd>{{ ($mascota->especieMascota->nombre ?? $mascota->especie) ?: '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Raza</dt>
                                <dd>{{ optional($mascota->razaMascota)->nombre ?: '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Sexo</dt>
                                <dd>{{ $mascota->sexo ?: '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Edad</dt>
                                <dd>{{ isset($mascota_edad) && $mascota_edad !== '' ? $mascota_edad . ' años' : '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Esterilización</dt>
                                <dd>{{ is_null($mascota->esterilizado) ? '-' : ($mascota->esterilizado ? 'Sí' : 'No') }}</dd>
                            </div>
                        </dl>
                    @else
                        <div class="ficha-resumen-cabecera">
                            <span class="ficha-resumen-icono"><i class="feather icon-user"></i></span>
                            <div class="ficha-resumen-titulo">
                                <span class="ficha-resumen-etiqueta">Información del paciente</span>
                                <strong class="ficha-resumen-nombre">{{ $paciente->nombres }} {{ $paciente->apellido_uno }} {{ $paciente->apellido_dos }}</strong>
                            </div>
                        </div>
                        <dl class="ficha-resumen-datos">
                            <div class="ficha-resumen-dato">
                                <dt>Edad</dt>
                                <dd>{{ $paciente->edad }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>RUT</dt>
                                <dd>{{ $paciente->rut }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Previsión</dt>
                                <dd>{{ $paciente->prevision->nombre }}</dd>
                            </div>
                        </dl>
                    @endif

                    <div class="ficha-resumen-seccion">Patologías crónicas</div>
                    @php
                        $patologiasCronicas = collect($antecedentes)->filter(function ($a) {
                            return $a->estado == 1 && $a->id_tipo_antecedente == 1;
                        });
                    @endphp
                    {{-- En una sola línea a propósito: si no hay patologías la lista queda vacía y el CSS muestra un guion --}}
                    <ul id="listado_patologias_paciente" class="ficha-resumen-lista">@foreach ($patologiasCronicas as $a)<li>{{ $a->antecedente_data->nombre }}</li>@endforeach</ul>
                </div>

            </div>
        </div>
    </nav>

@else
    <nav class="pcoded-navbar menu-light">
        <div class="navbar-wrapper">
            <div class="navbar-content scroll-div">
                <div class="">
                    <div class="main-menu-header">
                        <img class="img-radius" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="Profesional">
                        <div class="user-details">
                            <div id="more-details">{{ @Auth::user()->name }} <i class="fa fa-caret-down"></i></div>
                        </div>
                    </div>
                    <div id="nav-user-link">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <a href="#" data-toggle="tooltip" title="Mi perfil">
                                    <i class="feather icon-user"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <form action="{{ route('logout') }}" method="post" id="closeSession">
                                    @csrf
                                    <a data-toggle="tooltip" title="Cerrar sesión" class="text-danger" href="javascript:{}" onclick="document.getElementById('closeSession').submit();">
                                        <i class="feather icon-power"></i>
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <ul class="nav pcoded-inner-navbar ">
                    <li class="nav-item pcoded-menu-caption text-center">
                        <!--<label>Menú</label>-->
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-home"></i></span><span class="pcoded-mtext text-center">Mi Escritorio</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="{{ route('profesional.home') }}">Escritorio medichile </a></li>
                            <li><a href="{{ route('profesional.mi_perfil') }}">Mi Perfil</a></li>
                        </ul>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-activity"></i></span><span class="pcoded-mtext text-center">Mis Herramientas</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="recetaonline_profesional.php">Receta online</a></li>
                            <li><a href="{{ route('profesional.mi_agenda') }}">Mi Agenda</a></li>
                        </ul>
                    </li>
                    <!--<li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-search"></i></span><span class="pcoded-mtext text-center">Buscadores</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="buscar_paciente.php">Buscar Pacientes</a></li>
                            <li><a href="buscar_profesional.php">Buscar Profesionales</a></li>
                            <li><a href="buscar_servicio.php">Buscar Servicios</a></li>
                            <li><a href="buscar_institucion.php">Buscar Instituciones</a></li>
                        </ul>
                    </li>-->
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-menu"></i></span><span class="pcoded-mtext text-center">Otras Opciones</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="{{ route('profesional.lugares_atencion') }}">Configuración de <br>mis lugares de atención</a></li>
                            <li><a href="suscripcionmedichile.php">Renovar suscripción</a></li>
                        </ul>
                    </li>
                </ul>
                <div id="info_cliente" class="ficha-resumen">
                    @if(isset($mascota) && $mascota)
                        <div class="ficha-resumen-cabecera">
                            <div class="ficha-resumen-titulo">
                                <span class="ficha-resumen-etiqueta">Información de la mascota</span>
                                <strong class="ficha-resumen-nombre">{{ $mascota->nombre ?: '-' }}</strong>
                            </div>
                        </div>
                        <dl class="ficha-resumen-datos">
                            <div class="ficha-resumen-dato">
                                <dt>Especie</dt>
                                <dd>{{ ($mascota->especieMascota->nombre ?? $mascota->otra_especie) ?: '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Raza</dt>
                                <dd>{{ optional($mascota->razaMascota)->nombre ?: '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Sexo</dt>
                                <dd>{{ $mascota->sexo ?: '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Edad</dt>
                                <dd>{{ isset($mascota_edad) && $mascota_edad !== '' ? $mascota_edad : '-' }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Esterilización</dt>
                                <dd>{{ is_null($mascota->esterilizado) ? '-' : ($mascota->esterilizado ? 'Sí' : 'No') }}</dd>
                            </div>
                        </dl>
                    @else
                        <div class="ficha-resumen-cabecera">
                            <span class="ficha-resumen-icono"><i class="feather icon-user"></i></span>
                            <div class="ficha-resumen-titulo">
                                <span class="ficha-resumen-etiqueta">Información del paciente</span>
                                <strong class="ficha-resumen-nombre">{{ $paciente->nombres }} {{ $paciente->apellido_uno }} {{ $paciente->apellido_dos }}</strong>
                            </div>
                        </div>
                        <dl class="ficha-resumen-datos">
                            <div class="ficha-resumen-dato">
                                <dt>Edad</dt>
                                <dd>{{ $paciente->edad }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>RUT</dt>
                                <dd>{{ $paciente->rut }}</dd>
                            </div>
                            <div class="ficha-resumen-dato">
                                <dt>Previsión</dt>
                                <dd>{{ $paciente->prevision->nombre }}</dd>
                            </div>
                        </dl>
                    @endif

                    <div class="ficha-resumen-seccion">Patologías crónicas</div>
                    @php
                        $patologiasCronicas = collect($antecedentes)->filter(function ($a) {
                            return $a->estado == 1 && $a->id_tipo_antecedente == 1;
                        });
                    @endphp
                    {{-- En una sola línea a propósito: si no hay patologías la lista queda vacía y el CSS muestra un guion --}}
                    <ul id="listado_patologias_paciente" class="ficha-resumen-lista">@foreach ($patologiasCronicas as $a)<li>{{ $a->antecedente_data->nombre }}</li>@endforeach</ul>
                </div>
            </div>
        </div>
    </nav>
@endif



<div class="modal fade" id="confirmLogoutModal" tabindex="-1" role="dialog" aria-labelledby="confirmLogoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmLogoutModalLabel">Confirmar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="menu_url_destino" id="menu_url_destino" value="">
                <input type="hidden" name="menu_nombre_destino" id="menu_nombre_destino" value="">
                <p>Esta por salir de la Ficha de Atención, los datos serán eliminados.</p>
                <p>¿Esta seguro que desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="menuContinuar();">Continuar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function menuValidarSalidaFicha(url, nombre)
    {
        $('#confirmLogoutModal').modal('show');
        $('#menu_url_destino').val(url);
        $('#menu_nombre_destino').val(nombre);
    }

    function menuContinuar()
    {
        var temp = $('#menu_url_destino').val();
        $('#menu_url_destino').val('');
        $('#menu_nombre_destino').val('');
        window.location.href = temp;
    }
</script>
