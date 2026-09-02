@extends('template.profesional.template')
@section('content')
    <style>
        .configuracion-grid > [class*="col-"] { display: flex; margin-bottom: 24px; }
        .configuracion-card { width: 100%; min-height: 188px; margin-bottom: 0; overflow: hidden; }
        .configuracion-card > a,
        .configuracion-card .card-body { width: 100%; height: 100%; }
        .configuracion-card .card-body {
            min-height: 188px;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .configuracion-card-icon {
            width: 78px !important;
            height: 78px !important;
            margin: 0 0 16px !important;
            object-fit: contain;
            flex: 0 0 78px;
        }
        .configuracion-card h5 { margin: 0; line-height: 1.35; text-align: center; }
        .configuracion-grid { display: flex; flex-wrap: wrap !important; }
    </style>
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
<ul class="breadcrumb mt-2">
                                <li class="breadcrumb-item"><a href="{{ route('profesional.home') }}" data-toggle="tooltip"
                                        data-placement="top" title="Volver a mi escritorio"><i
                                            class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item">
                                    <a href="#">Panel de configuración</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--Cierre: Header-->
            <!--Botones-->

            <div class="row m-b-30">
                <div class="col-md-12">
                    @php
                        $esOdontologo = (int) $profesional->id_especialidad === 2
                            || (int) $profesional->id_tipo_especialidad === 18;
                    @endphp
                    <div class="row configuracion-grid">
                        <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                            <div class="card subir configuracion-card">
                                <a href="{{ route('sitio.configurar') }}">
                                    <div class="card-body text-center" style="cursor:pointer">
                                        <img class="configuracion-card-icon" src="{{ asset('images/iconos/docs.svg') }}"
                                            alt="Configurar mi página web">
                                        <h5>Configurar mi página web</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                            <div class="card subir configuracion-card">
                                <a href="{{ ROUTE('profesional.lugares_atencion') }}">
                                    <div class="card-body text-center" style="cursor:pointer">
                                        <img class="configuracion-card-icon" src="{{ asset('images/iconos/lugar.svg') }}"
                                            alt="Mis Lugares de Atención">
                                        <h5>
                                            Mis lugares de atención
                                        </h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                            <div class="card subir configuracion-card">
                                <a href="{{ route('profesional.mis_asistentes') }}">
                                    <div class="card-body text-center" style="cursor:pointer">
                                        <img class="configuracion-card-icon"
                                            src="{{ asset('images/iconos/mis_asistentes.svg') }}" alt="Mis Asistentes">
                                        <h5>
                                            Mis asistentes
                                        </h5>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                            <div class="card subir configuracion-card">
                                <a href="{{ route('profesional.aranceles') }}">
                                    <div class="card-body text-center" style="cursor:pointer">
                                        <span class="configuracion-card-icon d-inline-flex align-items-center justify-content-center position-relative"
                                              style="width:78px;height:78px;border-radius:22px;background:#e5fbf8;box-shadow:0 8px 20px rgba(16,170,168,.16)">
                                            <img src="{{ asset($esOdontologo ? 'images/iconos/dental.png' : 'images/iconos/gastos-pagos.png') }}" alt="Configurar aranceles" style="width:58px;height:58px;object-fit:contain">
                                            <span class="position-absolute d-flex align-items-center justify-content-center"
                                                  style="right:-4px;bottom:-3px;width:27px;height:27px;border-radius:50%;background:#7b35a5;color:#fff;font-size:15px;font-weight:700;border:3px solid #fff">$</span>
                                        </span>
                                        <h5 class="f-16">Configurar mis aranceles</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3">
                            <div class="card subir configuracion-card">
                                <a href="{{ ROUTE('profesional.mantencion_equipo') }}">
                                    <div class="card-body text-center" style="cursor:pointer">
                                        <img class="configuracion-card-icon"
                                        src="{{ asset('images/iconos/equipoqx.png') }}"

                                        alt="Profesional">
                                        <h5 class="f-16">
                                        Mis equipos quirúrgicos
                                        </h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--CIERRE:Botones-->
            <!--Botones-->
            <div class="row m-b-10">
                <div class="col-md-12">
                    <div class="card-deck">

                        {{--  <div class="card subir py-4">
                            <a href="{{ route('profesional.diagnosticos_frecuentes') }}">
                                <div class="card-body text-center" style="cursor:pointer">
                                    <img class="wid-90 text-center mb-3"
                                        src="{{ asset('images/iconos/diagnosticos_frecuentes.svg') }}"
                                        alt="Diagnósticos Frecuentes">
                                    <h5>
                                        Diagnósticos Frecuentes
                                    </h5>
                                </div>
                            </a>
                        </div>  --}}
                        {{--  <div class="card subir py-4">
                            <a href="{{ route('profesional.examenes_frecuentes') }}">
                                <div class="card-body text-center" style="cursor:pointer">
                                    <img class="wid-90 text-center mb-3"
                                        src="{{ asset('images/iconos/examenes_frecuentes.svg') }}"
                                        alt="Examenes Frecuentes">
                                    <h5>
                                        Exámenes Frecuentes
                                    </h5>
                                </div>
                            </a>
                        </div>  --}}
                        {{--  <div class="card subir py-4">
                            <a href="https://www.redmedichile.cl/admin/login.php">
                                <div class="card-body text-center" style="cursor:pointer">
                                    <img class="wid-90 text-center mb-3"
                                        src="{{ asset('images/iconos/panel_administracion.svg') }}"
                                        alt="Panel de Administración">
                                    <h5>
                                        Panel de Administración
                                    </h5>
                                </div>
                            </a>
                        </div>  --}}


                    </div>
                </div>
            </div>
            <!--CIERRE:Botones-->
        </div>
    </div>
    <!--Cierre: Container Completo-->
@endsection
