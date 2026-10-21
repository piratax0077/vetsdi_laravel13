@extends('template.adm_cm.template')

@section('content')

@php
    $nombreAdministrador = trim(Auth::user()->name ?? Auth::user()->nombre ?? 'Administrador');
    $primerNombreAdministrador = collect(preg_split('/\s+/', $nombreAdministrador))
        ->filter()
        ->first() ?? 'Administrador';

    // Sin foto cargada en Configuración se muestra el mismo icono que usa esa pantalla
    $fotoCentro = trim($institucion->foto_perfil ?? '');
    if ($fotoCentro === '') {
        $fotoCentro = asset('images/iconos/cm-perfiles.png');
    } elseif (!\Illuminate\Support\Str::startsWith($fotoCentro, ['http://', 'https://', '/'])) {
        $fotoCentro = asset('storage/' . $fotoCentro);
    }
@endphp

 <!--Container Completo-->

    <div class="pcoded-main-container">

        <div class="pcoded-content">

            <!--Header-->

            <div class="page-header encabezado-escritorio">

                <div class="page-block">

                    <div class="row align-items-center">

                        <div class="col-md-12">

                            <div class="page-header-title saludo-con-foto">
                                <a href="{{ route('adm_cm.configuracion') }}" class="saludo-foto" title="Cambiar la foto en Configuración">
                                    {{-- Si el archivo de la foto no está, queda el icono base igual que en Configuración --}}
                                    <img src="{{ $fotoCentro }}" alt="Foto de {{ $institucion->nombre }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/iconos/cm-perfiles.png') }}';">
                                </a>
                                <div class="saludo-texto">
                                <h3>Hola, <span class="text-purple">{{ $primerNombreAdministrador }}</span></h3>
                                <p class="text-dark">Bienvenido/a a tu escritorio de Administrador General de <strong>{{ mb_strtoupper($institucion->nombre) }}</strong></p>
                                @if (false && !empty($contextosCentro) && !empty($contextoActivo))
                                    <form method="GET" action="{{ route('adm_cm.home') }}" class="mt-3">
                                        <div class="form-row align-items-end">
                                            <div class="col-md-5">
                                                <label class="text-white mb-1">Institución / Sucursal activa</label>
                                                <select name="contexto" class="form-control form-control-sm">
                                                    @foreach ($contextosCentro as $contexto)
                                                        <option value="{{ $contexto['key'] }}" @selected(($contextoActivo['key'] ?? '') === $contexto['key'])>
                                                            {{ $contexto['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mt-2 mt-md-0">
                                                <button type="submit" class="btn btn-light btn-sm btn-block">Cambiar</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!--Cierre: Header-->

            <!--Indicadores: por ahora solo diseño, los valores se conectan más adelante-->

            <div class="row escritorio-indicadores">
                <div class="col-6 col-xl-3">
                    <div class="tarjeta-indicador tarjeta-indicador--morado">
                        <span class="tarjeta-indicador-icono"><i class="feather icon-credit-card"></i></span>
                        <strong class="tarjeta-indicador-valor">$0</strong>
                        <span class="tarjeta-indicador-etiqueta">Recaudado hoy</span>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tarjeta-indicador tarjeta-indicador--morado">
                        <span class="tarjeta-indicador-icono"><i class="feather icon-trending-up"></i></span>
                        <strong class="tarjeta-indicador-valor">$0</strong>
                        <span class="tarjeta-indicador-etiqueta">Recaudado en el mes</span>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tarjeta-indicador tarjeta-indicador--morado">
                        <span class="tarjeta-indicador-icono"><i class="feather icon-calendar"></i></span>
                        <strong class="tarjeta-indicador-valor">0</strong>
                        <span class="tarjeta-indicador-etiqueta">Citas hoy</span>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tarjeta-indicador tarjeta-indicador--morado">
                        <span class="tarjeta-indicador-icono"><i class="feather icon-user-plus"></i></span>
                        <strong class="tarjeta-indicador-valor">0</strong>
                        <span class="tarjeta-indicador-etiqueta">Pacientes nuevos</span>
                    </div>
                </div>
            </div>

            <!--Accesos principales y áreas del centro-->

            <div class="row">

                <div class="col-lg-6">
                    <div class="card escritorio-grupo escritorio-grupo--accesos">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-grid"></i></span>Accesos principales</h5>
                        </div>
                        <div class="card-body">
                            <div class="escritorio-grupo-accesos">
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.configuracion') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/panel_configuracion.svg') }}" alt="">
                                            <h6>Configuración</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.usuarios') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/profesionales.svg') }}" alt="">
                                            <h6>Usuarios</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.pacientes') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/mascotas.svg') }}" alt="">
                                            <h6>Mascotas y tutores</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.area_comercial') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/adm_comercial.svg') }}" alt="">
                                            <h6>Gestión comercial</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.adm_medico') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/adm_medica.png') }}" alt="">
                                            <h6>Gestión médica</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.profesionales', ['tab' => 'contratos']) }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/cotizacion.svg') }}" alt="">
                                            <h6>Contratos</h6>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card escritorio-grupo escritorio-grupo--areas">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-layers"></i></span>Áreas del centro</h5>
                        </div>
                        <div class="card-body">
                            <div class="escritorio-grupo-accesos">
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.laboratorio') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/laboratorio.svg') }}" alt="">
                                            <h6>Laboratorio</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.dental') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/dental.png') }}" alt="">
                                            <h6>Dental</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.examenes') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/imagenologia.svg') }}" alt="">
                                            <h6>Imagenología</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="{{ route('adm_cm.vacunatorio') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/vacunatorio.svg') }}" alt="">
                                            <h6>Vacunatorio</h6>
                                        </div>
                                    </a>
                                </div>
                                {{-- Estas dos áreas todavía no tienen pantalla de administración --}}
                                <div class="card modulo-en-construccion" aria-disabled="true" title="Módulo en construcción">
                                    <div class="card-body text-center">
                                        <img src="{{ asset('images/iconos/hospitalizacion.svg') }}" alt="">
                                        <h6>Hospitalización</h6>
                                        <span class="modulo-en-construccion-etiqueta">En construcción</span>
                                    </div>
                                </div>
                                <div class="card modulo-en-construccion" aria-disabled="true" title="Módulo en construcción">
                                    <div class="card-body text-center">
                                        <img src="{{ asset('images/iconos/huella.png') }}" alt="">
                                        <h6>Peluquería y belleza</h6>
                                        <span class="modulo-en-construccion-etiqueta">En construcción</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!--Utilidades y notificaciones-->

            <div class="row">

                <div class="col-lg-6">
                    <div class="card escritorio-grupo">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-sliders"></i></span>Utilidades</h5>
                        </div>
                        <div class="card-body">
                            <div class="escritorio-grupo-accesos">
                                <div class="card subir">
                                    <a href="{{ route('sitio.configurar') }}">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/docs.svg') }}" alt="">
                                            <h6>Configuración sitio web</h6>
                                        </div>
                                    </a>
                                </div>
                                <div class="card subir">
                                    <a href="#" onclick="en_construccion(); return false;">
                                        <div class="card-body text-center">
                                            <img src="{{ asset('images/iconos/mis_asistentes.svg') }}" alt="">
                                            <h6>Busca a tu asistente</h6>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card escritorio-grupo">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-bell"></i></span>Notificaciones<span class="badge badge-pill badge-danger ml-2">5 nuevas</span></h5>
                        </div>
                        <div class="card-body">
                            @php
                                // Avisos de ejemplo: por ahora solo diseño, se conectan más adelante
                                $notificaciones = [
                                    ['color' => 'rojo', 'icono' => 'package', 'titulo' => 'Stock bajo', 'texto' => 'Vacuna óctuple: quedan 3 unidades, hay que reponer.', 'cuando' => 'Hoy'],
                                    ['color' => 'morado', 'icono' => 'message-square', 'titulo' => 'Mensaje nuevo', 'texto' => 'Dra. Camila Soto te escribió por el turno del viernes.', 'cuando' => 'Hace 1 h'],
                                    ['color' => 'naranja', 'icono' => 'calendar', 'titulo' => 'Citas sin confirmar', 'texto' => '6 citas de mañana todavía no se confirman.', 'cuando' => 'Mañana'],
                                    ['color' => '', 'icono' => 'trending-up', 'titulo' => 'Recaudación del mes', 'texto' => 'Este mes llevas $2.450.000, un 12% más que el mes pasado.', 'cuando' => 'Octubre'],
                                    ['color' => 'azul', 'icono' => 'file-text', 'titulo' => 'Contrato por vencer', 'texto' => 'El contrato de un asistente vence en 7 días.', 'cuando' => '7 días'],
                                ];
                            @endphp
                            <ul class="lista-notificaciones">
                                @foreach ($notificaciones as $notificacion)
                                    <li class="notificacion-item {{ $notificacion['color'] ? 'tarjeta-indicador--' . $notificacion['color'] : '' }}">
                                        <span class="tarjeta-indicador-icono"><i class="feather icon-{{ $notificacion['icono'] }}"></i></span>
                                        <div class="notificacion-contenido">
                                            <strong>{{ $notificacion['titulo'] }}</strong>
                                            <span>{{ $notificacion['texto'] }}</span>
                                        </div>
                                        <small class="notificacion-cuando">{{ $notificacion['cuando'] }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!--Cierre: Container Completo-->

    @include('app.adm_cm.modales.en_construccion')

@endsection
