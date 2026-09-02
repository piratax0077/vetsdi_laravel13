@extends('template.profesional.template')
@section('page-styles')
    <link href='{{ asset('js/fullcalendar-5.10.1/lib/main.css') }}' rel='stylesheet' />
    <style>
        :root {
            --agenda-primary: #2fa8a4;
            --agenda-primary-dark: #1f8b88;
            --agenda-primary-light: #e6f6f5;
            --agenda-accent: #3b6ef6;
            --agenda-ok: #1fb28a;
            --agenda-ok-bg: #e4f9f1;
            --agenda-warn: #f5a524;
            --agenda-warn-bg: #fff4e0;
            --agenda-danger: #f1493f;
            --agenda-danger-bg: #fdeceb;
            --agenda-ink: #1e2b3a;
            --agenda-muted: #7c8a9a;
            --agenda-border: #e6eaf0;
            --agenda-surface: #ffffff;
            --agenda-bg: #f4f7fb;
        }

        .status-circle .circle {
            width: 14px;
            height: 14px;
            background-color: var(--agenda-danger);
            border-radius: 50%;
            display: inline-block;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px rgba(30, 43, 58, 0.08);
        }

        .status-circle .circle.bg-success { background-color: var(--agenda-ok) !important; }
        .status-circle .circle.bg-warning { background-color: var(--agenda-warn) !important; }
        .status-circle .circle.bg-danger  { background-color: var(--agenda-danger) !important; }
    </style>

    <style>
        #loading {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        /* ================= HEADER ================= */
        .agenda-header-bar {
            background: linear-gradient(120deg, var(--agenda-primary-dark), var(--agenda-primary));
            border-radius: 12px;
            padding: 14px 22px;
            box-shadow: 0 6px 16px rgba(31, 139, 136, 0.25);
        }

        .agenda-header-bar .titulo-agenda {
            letter-spacing: 0.2px;
        }

        .page-header .breadcrumb-item,
        .page-header .breadcrumb-item.active,
        .page-header .breadcrumb-item span,
        .page-header .breadcrumb-item a,
        .page-header .breadcrumb-item i {
            color: #fff !important;
            background: transparent !important;
            opacity: 1 !important;
        }

        .agenda-header-bar a {
            color: #fff;
            opacity: 0.9;
            transition: opacity .15s ease;
        }

        .agenda-header-bar a:hover {
            opacity: 1;
            text-decoration: none;
        }

        /* ================= MAIN PANEL ================= */
        .agenda-panel {
            background-color: var(--agenda-bg) !important;
            border-radius: 14px;
            margin-top: 16px;
        }

        .agenda-panel .titulo-agenda#titulo_tipo_agenda {
            color: var(--agenda-ink);
            font-weight: 600;
        }

        .agenda-box-info {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--agenda-surface);
            border: 1px solid var(--agenda-border);
            border-radius: 10px;
            padding: 8px 14px;
            box-shadow: 0 2px 6px rgba(30, 43, 58, 0.05);
        }

        .agenda-box-info .agenda-box-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--agenda-muted);
            font-weight: 700;
            margin: 0;
        }

        .agenda-box-info #profesional_box {
            color: var(--agenda-ink) !important;
            font-weight: 700 !important;
        }

        /* ================= CALENDAR CARD ================= */
        .agenda-calendar-card {
            background: var(--agenda-surface);
            border-radius: 16px;
            border: 1px solid var(--agenda-border);
            box-shadow: 0 8px 24px rgba(30, 43, 58, 0.06);
            padding: 18px !important;
        }

        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }

        /* kill the scrollbars and allow natural height */
        .fc-scroller,
        .fc-day-grid-container,
        .fc-time-grid-container {
            overflow-x: hidden;
            overflow-y: auto !important;
            height: auto !important;
        }

        .fc {
            font-family: inherit;
            --fc-border-color: var(--agenda-border);
            --fc-today-bg-color: var(--agenda-primary-light);
            --fc-neutral-bg-color: #f7f9fb;
            --fc-page-bg-color: var(--agenda-surface);
        }

        .fc-timegrid-event .fc-event-time{
            font-size: 1rem!important;
        }

        .fc-v-event .fc-event-title{
            font-size: 0.9rem!important;
        }

        .fc-event, .fc-v-event, .fc-h-event {
            border: none !important;
            border-radius: 8px !important;
            border-left: 4px solid rgba(0,0,0,0.15) !important;
            box-shadow: 0 2px 6px rgba(30, 43, 58, 0.12);
            padding: 2px 4px;
        }

        .fc-daygrid-day.fc-day-today,
        .fc-timegrid-col.fc-day-today {
            background: var(--agenda-primary-light) !important;
        }

        .fc-col-header-cell {
            background: #f7f9fb;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
            color: var(--agenda-muted);
            font-weight: 700;
        }

        /* kill the horizontal border/padding used to compensate for scrollbars */
        .fc-row {
            border: 0 !important;
            margin: 0 !important;
        }

        .fc .fc-timegrid-slot {
            height: 5em!important;
        }

        .fc-timegrid-event-harness > .fc-timegrid-event {
          /* height:6em; */
        }

        .fc .fc-toolbar-title {
            font-size: 1.4em;
            margin: 0;
            color: var(--agenda-ink);
            font-weight: 700;
        }

        .fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 14px!important;
        }

        /* ================= BUTTONS / TOOLBAR ================= */
        .btn-group>.btn,
        .btn-group-vertical>.btn {
            position: relative;
            flex: 1 1 auto;
            padding: 6px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: var(--agenda-primary);
            border-color: var(--agenda-primary);
            border-radius: 8px !important;
            transition: background-color .15s ease, transform .1s ease;
        }

        .btn-group>.btn:hover,
        .btn-group-vertical>.btn:hover {
            background-color: var(--agenda-primary-dark);
            border-color: var(--agenda-primary-dark);
        }

        .fc .fc-toolbar-chunk:first-child {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .fc .fc-toolbar-chunk:first-child > .fc-button,
        .fc .fc-toolbar-chunk:first-child > .fc-button-group {
            margin: 0 !important;
        }

        .fc .fc-toolbar-chunk:last-child {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fc .fc-toolbar-chunk:last-child > .fc-button,
        .fc .fc-toolbar-chunk:last-child > .fc-button-group {
            margin: 0 !important;
        }

        .fc .fc-toolbar-chunk:last-child .fc-button {
            border-radius: 8px !important;
        }

        .fc .fc-prev-button,
        .fc .fc-next-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            margin: 0 !important;
            padding: 0 !important;
            color: #fff !important;
            background: var(--agenda-primary) !important;
            border: 1px solid var(--agenda-primary) !important;
            border-radius: 9px !important;
            box-shadow: 0 3px 8px rgba(31, 139, 136, .2);
        }

        .fc .fc-prev-button:hover,
        .fc .fc-next-button:hover {
            background: var(--agenda-primary-dark) !important;
            border-color: var(--agenda-primary-dark) !important;
            transform: translateY(-1px);
        }

        .fc .fc-today-button {
            min-width: 68px;
            height: 38px;
            margin: 0 !important;
            padding: 6px 16px !important;
            color: #fff !important;
            font-size: .86rem;
            font-weight: 800;
            letter-spacing: .2px;
            background: #f26b4a !important;
            border: 1px solid #f26b4a !important;
            border-radius: 9px !important;
            box-shadow: 0 4px 10px rgba(242, 107, 74, .28);
            opacity: 1 !important;
        }

        .fc .fc-today-button:hover:not(:disabled) {
            background: #dc5738 !important;
            border-color: #dc5738 !important;
            transform: translateY(-1px);
        }

        .fc .fc-today-button:disabled {
            color: #fff !important;
            background: #d65c40 !important;
            border-color: #d65c40 !important;
            opacity: 1 !important;
        }

        .btn.btn-agenda {
            width: 38px !important;
            height: 38px !important;
            font-size: 22px !important;
            padding: 0px;
            border-radius: 50%!important;
            box-shadow: 0 4px 10px rgba(30, 43, 58, 0.18);
            transition: transform .12s ease;
        }

        .btn.btn-agenda:hover {
            transform: translateY(-2px);
        }

        /* ================= MODALS ================= */
        .modal-content {
            border: none;
            border-radius: 14px;
            overflow: hidden;
        }

        .modal-header.bg-info {
            background: linear-gradient(120deg, var(--agenda-primary-dark), var(--agenda-primary)) !important;
            border: none;
        }

        .form-control-sm {
            border-radius: 8px;
        }

        .floating-label-activo-sm {
            color: var(--agenda-muted);
            font-weight: 600;
            font-size: 0.78rem;
        }
        .form-group h5 {
            text-align: center;
            margin-bottom: 10px;
        }
        @media (max-width: 767.98px) {

            .titulo-agenda {
                font-size: 1.2rem;
            }

            .t-lugar-aten {
                font-size:0.9rem!important;
            }

            .t-tipo-agenda {
                font-size:0.94rem!important;
            }

             .btn.btn-agenda {
                width: 30px !important;
                height: 30px !important;
                font-size: 15px !important;
                padding: 0px;
                border-radius: 50%!important;
            }

            .fc .fc-toolbar-title {
            font-size: 1rem!important;
            margin: 0;
            }

             .btn-group>.btn,
            .btn-group-vertical>.btn {
                position: relative;
                flex: 1 1 auto;
                padding: 1px 5px;
                font-size: 0.8rem;
            }

         }

         @media (min-width: 768px) { 
               .titulo-agenda {
                font-size: 1.2rem;
            }
          }

        @media (min-width: 992px) { 

        .titulo-agenda {
                font-size: 1.2rem;
            }
        }

        @media (min-width: 1200px) { 
            .titulo-agenda {
                font-size: 1.3rem;
            }

        }

        @media (min-width: 1400px) { 
        .titulo-agenda {
                font-size: 1.6rem;
            } }

    </style>

    <link href='{{ asset('css/estilos_boton_agen_examenes.css') }}' rel='stylesheet' />
@endsection

@section('content')

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--HEADER-->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">Mi agenda</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('profesional.home') }}" data-toggle="tooltip"
                                        data-placement="top" title="Volver a mi escritorio">
                                        <i class="feather icon-home"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span>{{ $lugar_atencion_nombre }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--CIERRE: HEADER-->


            <div class="row user-profile user-card  align-items-center py-1 pb-3 px-4" style="background-color:#ecf0f5;">
                <div class="col-md-12 d-inline pt-3">
                    <h5 class="titulo-agenda d-inline mt-2 t-tipo-agenda" id="titulo_tipo_agenda"></h5>
                    @include('general.info_simbologia.simbologia_agenda')
                    @include('general.anular_hora.anular_hora')
                    @include('general.bloqueo_hora.bloque_hora')


                    @if ($boxes->count() > 0)
                        <div class="align-middle m-b-25">
                            <div class="d-inline-block f-11">

                                @if ($lug_prof_box)
                                    <input type="hidden" name="id_box" id="id_box" value="{{ $lug_prof_box->id_box }}">
                                    <span><strong>BOX</strong></span> <button type="button" class="btn btn-warning-light-c btn-xxxs" id="btn_ver_modificar_box_prof"  onclick="abrir_editar_box_prof('{{ $lug_prof_box->id }}')"><i class="feather icon-edit"></i></button><br>
                                    <span><strong id="profesional_box" style="font-size: 16px;">{{ $lug_prof_box->box->tipo_box.' - '.$lug_prof_box->box->numero_box }}</strong></span>
                                @else
                                    <input type="hidden" name="id_box" id="id_box" value="">
                                    <span><strong>BOX</strong></span> <button type="button" class="btn btn-warning-light-c btn-xxxs" id="btn_ver_modificar_box_prof"  onclick="abrir_agregar_box_prof('{{ $profesional->id }}')"><i class="feather icon-edit"></i></button><br>
                                    <span><strong id="profesional_box" style="font-size: 16px;">-</strong></span>
                                @endif

                            </div>
                        </div>
                    @endif

                </div>
                <div class="col-md-12 mr-5 px-4 card">
                    <div id='agenda'></div>
                </div>
            </div>
        </div>
    </div>

    @include('app.profesional.modales.modal_consulta_agenda')

    <style>
        #agenda_agregar_paciente .modal-header {
            align-items: center;
            min-height: 60px;
            padding: 14px 20px;
        }
        #agenda_agregar_paciente .modal-title {
            margin: 0;
            line-height: 1.25;
        }
        #agenda_agregar_paciente .modal-header .close {
            position: static;
            flex: 0 0 auto;
            margin: 0 0 0 auto;
            padding: 4px 8px;
        }
        #agenda_agregar_paciente .form-group {
            position: relative;
            margin-bottom: 1.15rem;
        }
        #agenda_agregar_paciente .floating-label-activo-sm {
            position: absolute !important;
            z-index: 2;
            top: -8px;
            left: 10px;
            display: inline-block;
            width: auto;
            margin: 0;
            padding: 0 4px;
            color: #748297;
            font-size: 12px;
            font-weight: 600;
            line-height: 16px;
            background: #fff;
            transform: none !important;
        }
        #agenda_agregar_paciente .form-control {
            min-height: 38px;
            border-radius: 7px;
        }
        #agenda_agregar_paciente .modal-body,
        #agenda_agregar_paciente .modal-content {
            overflow-x: hidden;
        }
        #agenda_agregar_paciente .reserva-seccion-titulo {
            margin: .3rem 0 1.4rem;
            text-align: center;
            color: #4d5b70;
            font-size: 15px;
            font-weight: 700;
        }
        #agenda_agregar_paciente .reserva-fecha-desconocida {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
        }
        #agenda_agregar_paciente .modal-footer {
            margin-left: 0;
            margin-right: 0;
            padding-left: 0;
            padding-right: 0;
        }
        @media (max-width: 575.98px) {
            #agenda_agregar_paciente .modal-dialog {
                margin: .5rem;
            }
            #agenda_agregar_paciente .modal-body {
                padding: 1rem;
            }
        }
    </style>

    <div id="agenda_agregar_paciente" class="modal fade" tabindex="-1" role="dialog"
        aria-labelledby="agregar_paciente_asistente" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info pt-3 pb-2">
                    <h5 class="modal-title text-white text-center">Tomar horas</h5>
                    <button id="cerrar_tomar_hora" type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    {{--  BUSCADOR DE RUT  --}}
                    <div class="form-row div_rut_buscar">
                         <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group">
                                <h6 class="text-c-blue f-14">Ingrese el RUT del Tutor</h6>
                            </div>
                        </div>
                        <div class="col-sm-9 col-md-9">
                            <form id="validacion_rut_form" onsubmit="event.preventDefault(); buscar_paciente();">
                                <div class="form-group" id="validacion_rut_div">
                                    <input type="text" id="rut_paciente_reserva" name="rut_paciente_reserva"
                                        class="form-control form-control-sm" placeholder="RUT del tutor"
                                        aria-label="RUT del tutor" aria-describedby="button-addon2" required
                                        oninput="formatoRut(this)">
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-3 col-md-3 mb-3">
                            <button class="btn btn-sm btn-info btn-block" onclick="buscar_paciente();"
                                type="button"id="button-addon2">
                                <i class="feather icon-search"></i> Buscar
                            </button>
                        </div>
                    </div>

                    <form id="form_reseva_de_horas">
                        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
                        <input type="hidden" id="fecha_consulta" name="fecha_consulta" value="">
                        <input type="hidden" id="reserva_hora_id_paciente" name="reserva_hora_id_paciente" value="">
                        <input type="hidden" name="id_lugar_atencion" id="id_lugar_atencion" value="{{ $lugar_atencion }}">
                        <input type="hidden" name="fecha" id="fecha">

                        @if ($profesional->id_especialidad == 4 && $profesional->id_tipo_especialidad == 55)
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12" id="div_procedimiento" name="div_procedimiento" style="display: none;">

                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Procedimiento</label>
                                    <select class="form-control form-control-sm" name="form_reseva_de_horas_id_procedimiento" id="form_reseva_de_horas_id_procedimiento">
                                        <option value="">Seleccione</option>
                                        @if (isset($procedimientos) && !empty($procedimientos))
                                            @foreach ($procedimientos as $proced )
                                                <option value="{{ $proced->id }}" data-cant_bloque="{{ (empty($proced->cantidad_bloques_prof)?$proced->cantidad_bloques:$proced->cantidad_bloques_prof) }}">{{ $proced->nombre }} {{ (empty($proced->cantidad_bloques_prof)?$proced->cantidad_bloques:$proced->cantidad_bloques_prof) }}Blq.</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                            </div>
                        @endif

                        <div id="examenes" class="d-none">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Examenes</label>
                                    <select class="form-control form-control-sm" name="form_reseva_de_horas_id_examen" id="form_reseva_de_horas_id_examen">

                                    </select>
                                </div>
                            </div>
                        </div>
                        @if($profesional->id_especialidad == 2 )
                            <div class="col-sm-12" id="div_procedimiento" name="div_procedimiento" style="display: none;">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Nº de presupuesto</label>
                                    <select class="form-control form-control-sm" name="presupuesto_numero" id="presupuesto_numero" onchange="updateTotalValue()">
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>
                                <div id="contenedor_tratamientos_presupuesto">
                                    Se utilizan <span id="cantidad_bloques_atencion">1</span> bloque de atención.
                                </div>
                            </div>

                        @endif

                        <div id="reserva_datos_paciente" class="row mx-3">

                            
                            <table class="table table-borderless table-xs">
                                <tbody>
                                    <tr>
                                        <td class="align-top">
                                            <h6 class="text-c-blue f-16 mb-3">Seleccione mascota para agendar cita</h6>
                                            <div class="form-row">
                                                <div class="form-group col-12">
                                                    <label class="floating-label-activo-sm">Mascota</label>
                                                    <select class="form-control form-control-sm" id="reserva_hora_mascota_id" name="reserva_hora_mascota_id">
                                                        <option value="">Seleccione mascota</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-12">
                                                    <label class="floating-label-activo-sm">Motivo de la consulta</label>
                                                    <select class="form-control form-control-sm">
                                                        <option value="">Seleccione</option>
                                                        <option value="urgencia">Urgencia</option>
                                                        <option value="consulta-general">Consulta general</option>
                                                        <option value="control">Control</option>
                                                        <option value="vacunacion">Vacunación</option>
                                                        <option value="desparacation-externa">Desparacitación Externa</option>
                                                        <option value="desparacation-interna">Desparacitación Interna</option>
                                                        <option value="plan-geriatrico">Plan geriátrico</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                              <div class="col-md-7">
                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-2">
                                                <h6 class="text-c-blue f-16 d-inline">Información del cliente</h6>
                                                <button type="button" onclick="editar_info_paciente();" class="btn btn-sm btn-info-light-c float-right d-inline paciente_view">
                                                    <i class="feather icon-edit"></i> Editar
                                                </button>
                                                <input type="hidden" name="modificando_paciente" id="modificando_paciente" value="0">
                                                </div>

                                                <table class="table table-borderless table-xs mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                              <div><b>Rut</b></div>
                                                              <span id="reserva_rut_paciente"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Nombre</b></div>
                                                                <div class="paciente_view">
                                                                    <span id="reserva_hora_nombre"></span>
                                                                </div>

                                                                <div class="paciente_edit" style="display:none">
                                                                    <div class="form-row">
                                                                        <div class="col-sm-12 col-md-4">
                                                                            <input type="text" class="form-control form-control-sm" id="input_reserva_hora_nombre" value="">
                                                                        </div>
                                                                        <div class="col-sm-12 col-md-4">
                                                                            <input type="text" class="form-control form-control-sm" id="input_reserva_hora_apellido_uno" value="">
                                                                        </div>
                                                                        <div class="col-sm-12 col-md-4">
                                                                            <input type="text" class="form-control form-control-sm" id="input_reserva_hora_apellido_dos" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Dirección</b></div>
                                                                <div class="paciente_view">
                                                                    <span id="reserva_direccion"></span>
                                                                </div>
                                                                <div class="paciente_edit" style="display:none">
                                                                    <div class="form-row">
                                                                        <div class="col-sm-12 col-md-9">
                                                                            <div class="form-group">
                                                                                <input type="address" class="form-control form-control-sm" name="input_reserva_direccion_direccion" id="input_reserva_direccion_direccion" value="">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-12 col-md-3">
                                                                            <div class="form-group">
                                                                                <input type="address" class="form-control form-control-sm" name="input_reserva_direccion_numero_dir" id="input_reserva_direccion_numero_dir" value="">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-row">
                                                                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                                                            <div class="form-group">
                                                                                <label class="floating-label-activo-sm">Región</label>
                                                                                <select id="input_reserva_direccion_region" onchange="buscar_ciudad_general('input_reserva_direccion_region', 'input_reserva_direccion_ciudad', 0);" name="input_reserva_direccion_region" class="form-control form-control-sm">
                                                                                    <option value="0">Seleccione</option>
                                                                                    @if (isset($region))
                                                                                        @foreach ($region as $reg)
                                                                                            <option value="{{ $reg->id }}">{{ $reg->nombre }} </option>
                                                                                        @endforeach
                                                                                    @endif
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                                                            <div class="form-group">
                                                                                <label class="floating-label-activo-sm">Ciudad</label>
                                                                                <select id="input_reserva_direccion_ciudad" name="input_reserva_direccion_ciudad" class="form-control form-control-sm">
                                                                                    <option value="0">Seleccione</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Correo Electrónico</b></div>
                                                                <div class="paciente_view">
                                                                    <span id="reserva_hora_email"></span>
                                                                </div>
                                                                <div class="paciente_edit" style="display:none">
                                                                    <div class="form-row">
                                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                                            <input type="text" class="form-control form-control-sm" id="input_reserva_hora_email" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Teléfono</b></div>
                                                                <div class="paciente_view">
                                                                    <span id="reserva_hora_telefono"></span>
                                                                </div>
                                                                <div class="paciente_edit" style="display:none">
                                                                    <div class="form-row">
                                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                                                            <input type="text" class="form-control form-control-sm" id="input_reserva_hora_telefono" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div><b>Convenio</b></div>
                                                                <div class="paciente_view">
                                                                    <span id="reserva_convenio"></span>
                                                                </div>
                                                                <div class="paciente_edit" style="display:none">
                                                                    <select id="input_reserva_convenio" name="input_reserva_convenio" class="form-control form-control-sm">
                                                                        <option value="0">Seleccione</option>
                                                                        @if (isset($prevision))
                                                                            @foreach ($prevision as $p)
                                                                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </td>
                                                        </tr>

                                                        <br>
                                                        <tr class="paciente_edit">
                                                          <td>
                                                            <table>
                                                              <tr>
                                                                <td>
                                                                    <button type="button" id="cancelar_modifcar_paciente" onclick="cancelar_modificacion_paciente();" class="btn btn-sm btn-danger">
                                                                        <i class="feather icon-x"></i> Cancelar actualización
                                                                    </button>
                                                                </td>
                                                                <td>
                                                                    <button type="button" id="actualizar_modificar_paciente" onclick="actualizar_paciente();" class="btn btn-sm btn-info">
                                                                        <i class="feather icon-check"></i> Actualizar paciente
                                                                    </button>
                                                                </td>
                                                              </tr>
                                                            </table>
                                                          </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                              </div>
                                              <div class="col-md-5">
                                                <h6 class="text-c-blue f-16 d-inline mb-3">Información de la mascota</h6>
                                                <table class="table table-borderless table-xs mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                              <div><b>Nombre</b></div>
                                                              <span id="reserva_mascota_nombre"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Especie</b></div>
                                                              <span id="reserva_mascota_especie"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Tamaño</b></div>
                                                              <span id="reserva_mascota_tamano"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Sexo</b></div>
                                                              <span id="reserva_mascota_sexo"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Fecha nacimiento</b></div>
                                                              <span id="reserva_mascota_fecha_nacimiento"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Chip</b></div>
                                                              <span id="reserva_mascota_chip"></span></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                              <div><b>Esterilizado</b></div>
                                                              <span id="reserva_mascota_esterilizado"></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                              </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="modal-footer mb-0 pt-1 pb-0 paciente_view">
                                <button type="button" class="btn btn-warning" id="btn_registrar_mascota_desde_tomar_hora" style="display:none;">
                                    <i class="feather icon-plus"></i> Agregar mascota no registrada
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                                        class="feather icon-x"></i> Cancelar</button>
                                <button type="button" onclick="agendar_hora();" class="btn btn-info"><i
                                        class="feather icon-check"></i> Agendar hora</button>
                            </div>
                        </div>

                        <div id="reserva_agregar_paciente_hora">

                            <div class="form-row">
                                <div class="col-sm-12 col-md-12">
                                    <div class="alert alert-danger py-1" role="alert">
                                        Responsable o primera mascota no registrados. Ingrese los datos para agendar una hora.
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <div class="reserva-seccion-titulo">
                                            Identificación del Tutor
                                    </div>
                                </div>
                            </div>

                            {{-- INFORMACION DEL PACIENTE --}}
                            <div class="form-row seccion_reserva_paciente_nuevo">
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Nombres</label>
                                        <input type="text" required class="form-control form-control-sm"
                                            name="reserva_hora_nombres_paciente" id="reserva_hora_nombres_paciente">
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Primer Apellido</label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="reserva_hora_apellido_uno" id="reserva_hora_apellido_uno">
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Segundo Apellido</label>
                                        <input type="text" class="form-control form-control-sm"
                                            name="reserva_hora_apellido_dos" id="reserva_hora_apellido_dos">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row seccion_reserva_paciente_nuevo">
                                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Direcci&oacute;n</label>
                                        <input type="address" class="form-control form-control-sm"
                                            name="reserva_hora_direccion" id="reserva_hora_direccion">
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Región</label>
                                        <select id="region_agregar" onchange="buscar_ciudad();" name="region_agregar"
                                            class="form-control form-control-sm" required>
                                            <option value="0">Seleccione</option>
                                            @if (isset($region))
                                                @foreach ($region as $reg)
                                                    <option value="{{ $reg->id }}">{{ $reg->nombre }} </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Ciudad</label>
                                        <select id="ciudad_agregar" name="ciudad_agregar"
                                            class="form-control form-control-sm" required>
                                            <option value="0">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Correo Electr&oacute;nico </label>

                                        <input type="text" class="form-control form-control-sm"
                                            onblur="validar_email_agenda();validar_campo_telefono();" onchange="validar_email_agenda();validar_campo_telefono();"name="reserva_hora_correo"
                                            id="reserva_hora_correo">
                                        <span id="mensaje_email_reserva" style="width: 100%; font-size: 10px; color: #f00; font-weight: bold; display:none"></span>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-6 col-xl-6">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Tel&eacute;fono</label>
                                        <input type="tel" class="form-control form-control-sm"
                                            name="reserva_hora_telefono_uno" id="reserva_hora_telefono_uno" onblur="validar_campo_telefono();" onchange="validar_campo_telefono();">
                                    </div>
                                </div>
                            </div>

                          
                            <div class="row mt-2">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <div class="reserva-seccion-titulo">
                                            Identificación de la mascota
                                    </div>
                                </div>
                            
                            </div>
                           

                            {{-- INFORMACION DEL PACIENTE --}}
                            <div class="form-row seccion_reserva_paciente_nuevo">
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Nombre Mascota</label>
                                        <input type="text" class="form-control form-control-sm" id="reserva_mascota_nueva_nombre">
                                    </div>
                                </div>
                              
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                      <label class="floating-label-activo-sm">Especie</label>
                                        <select class="form-control form-control-sm" id="reserva_mascota_nueva_especie"></select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                       <label class="floating-label-activo-sm">Raza</label>
                                        <select class="form-control form-control-sm" id="reserva_mascota_nueva_raza">
                                            <option value="">Seleccione</option>
                                            <option value="sin">Sin raza</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Tamaño</label>
                                        <select class="form-control form-control-sm" id="reserva_mascota_nueva_tamano">
                                            <option value="">Seleccione</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                         <label class="floating-label-activo-sm">¿Tiene chip?</label>
                                        <select class="form-control form-control-sm" id="reserva_mascota_nueva_tiene_chip">
                                            <option value="0">No</option>
                                            <option value="1">Sí</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4" id="reserva_mascota_nueva_contenedor_chip" style="display:none;">
                                    <div class="form-group">
                                       <label class="floating-label-activo-sm">N° chip</label>
                                        <input type="text" class="form-control form-control-sm" id="reserva_mascota_nueva_chip">
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <div class="form-group">
                                       <label class="floating-label-activo-sm">¿Esterilizado?</label>
                                        <select class="form-control form-control-sm" id="reserva_mascota_nueva_esterilizado">
                                            <option value="">Seleccione</option>
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row align-items-center seccion_reserva_paciente_nuevo">
                                <!-- Campo Fecha de Nacimiento -->
                                <div class="col-sm-12 col-md-5">
                                    <div class="form-group">
                                        <label for="f_nacimiento" class="floating-label-activo-sm">F. Nacimiento</label>
                                        <input type="date" id="reserva_mascota_nueva_fecha_nacimiento" class="form-control form-control-sm" max="{{ date('Y-m-d') }}" />
                                    </div>
                                </div>

                                <!-- Checkbox Desconocida -->
                                <div class="col-sm-12 col-md-3">
                                    <div class="form-check form-check-inline reserva-fecha-desconocida">
                                        <input type="checkbox" class="form-check-input" id="reserva_mascota_nueva_fecha_nacimiento_desconocida" />
                                        <label class="form-check-label" for="reserva_mascota_nueva_fecha_nacimiento_desconocida">desconocida</label>
                                    </div>
                                </div>

                                <!-- Selector de Sexo -->
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-group">
                                      <label for="sexo" class="floating-label-activo-sm">Sexo</label>
                                      <select id="reserva_mascota_nueva_sexo" class="form-control form-control-sm">
                                        <option value="0">Seleccione una opción</option>
                                        <option value="M">Macho</option>
                                        <option value="F">Hembra</option>
                                      </select>
                                    </div>
                                </div>
                            </div>
                            
                           
                            <div class="modal-footer">
                                <div class="w-100 mb-2">
                                    <div class="form-group mb-0">
                                        <label class="floating-label-activo-sm">Enfermedad crónica o frecuente</label>
                                        <textarea class="form-control form-control-sm" id="reserva_mascota_nueva_enfermedad_cronica" rows="2"></textarea>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-danger" id="cerrar_registro_paciente_hora"
                                    data-dismiss="modal"><i class="feather icon-x"></i> Cancelar</button>
                                <button type="button" id="guardar_reserva_paciente"
                                    onclick="agendar_hora_paciente_nuevo();" class="btn btn-info" disabled="disabled">
                                    <i class="feather icon-check"></i> Tomar Hora
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--Evento Reserva-->
    @if($profesional->id_especialidad == 2)
        <!-- Modal -->
        <div class="modal fade" id="modal_recepcion_bonos_api" tabindex="-1" aria-labelledby="modal_recepcion_bonos_apiLabel" aria-hidden="true">
            <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="modal_pago_consulta_title">Recepción de pago atención</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <div class="form-row mb-2">
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="alert alert-danger">
                                <h6 class="text-danger p-16">Recuerde que siempre se debe validar datos del paciente, profesional y convenio con los datos del bono físico</h6>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <input type="hidden" name="bono_hora_medica" id="bono_hora_medica">
                        <input type="hidden" name="bono_id_profesional" id="bono_id_profesional">
                        <input type="hidden" name="bono_id_paciente" id="bono_id_paciente">
                        <input type="hidden" name="bono_id_tipo_bono" id="bono_id_tipo_bono" value="1">
                        <div class="col-sm-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Rut del Paciente</label>
                                <input type="person" class="form-control form-control-sm" name="bono_paciente_rut"
                                    id="bono_paciente_rut" disabled="disabled">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Nombre del Paciente</label>
                                <input type="text" class="form-control form-control-sm" name="bono_paciente_nombre"
                                    id="bono_paciente_nombre" disabled="disabled">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm"> Nombre Profesional</label>
                                <input type="text" class="form-control form-control-sm" name="bono_profesional_nombre"
                                    id="bono_profesional_nombre" disabled="disabled">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm"> Rut Profesional</label>
                                <input type="text" class="form-control form-control-sm" name="bono_profesional_rut"
                                    id="bono_profesional_rut" disabled="disabled">
                            </div>
                        </div>
                        <div class="col-sm-6 bono_valor">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Clase Pago</label>
                                <select id="bono_id_clase_bono" name="bono_id_clase_bono" class="form-control form-control-sm" onchange="validar_prevision(this)">
                                    <option value="0">Seleccione</option>
                                    <option value="1">Efectivo</option>
                                    <option value="2">Transferencia</option>
                                    <option value="3">Tarjeta de débito</option>
                                    <option value="4">Tarjeta de crédito</option>
                                    <option value="5">Pago por aplicación móvil (Ej: Mercado Pago, PayPal, etc.)</option>
                                    <option value="6">Cheque</option>
                                    <option value="7">Crédito del paciente (acuerdo interno)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="input-group">
                                <label class="floating-label-activo-sm">Convenio</label>
                                <input type="text" class="form-control form-control-sm" name="bono_prevision_txt" id="bono_prevision_txt" disabled="disabled" value="">
                                <select id="bono_prevision" name="bono_prevision" class="form-control form-control-sm"  style="display: none;" onchange="$('#bono_prevision_txt').val( $('#bono_prevision option:selected').text() );$('#bono_prevision_txt').show();$('#bono_prevision').hide();actualizar_prevision_paciente('bono_id_paciente', 'bono_prevision');">
                                    <option value="0">Selecione una opción</option>
                                    @foreach ($prevision as $prev)
                                        <option value="{{ $prev->id }}">{{ $prev->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 bono_valor">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Valor total</label>
                                <input name="bono_valor_consulta" id="bono_valor_consulta" type="number"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-sm-6 bono_valor">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Abono</label>
                                <input name="bono_valor_abono_consulta" id="bono_valor_abono_consulta" type="number"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-sm-6 d-none">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Abono</label>
                                <input name="bono_valor_bonificacion" id="bono_valor_bonificacion" type="number"
                                    class="form-control form-control-sm" value="0">
                            </div>
                        </div>
                        <div class="col-sm-6 d-none">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Abono</label>
                                <input name="valor_bonificacion" id="valor_bonificacion" type="number"
                                    class="form-control form-control-sm" value="0">
                            </div>
                        </div>
                        <div class="col-sm-6 bono_valor">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Saldo</label>
                                <input name="bono_valor_saldo_consulta" id="bono_valor_saldo_consulta" type="number"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="card border-info mb-3">
                                <div class="card-body">
                                    <h6 class="text-info mb-2"><i class="fas fa-qrcode"></i> Recepción mediante QR</h6>
                                    <p class="text-muted small mb-2">Escanee el QR emitido en Bonos Veterinaria o ingrese su código.</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-sm bono_qr_recepcion"
                                            placeholder="Código u orden del bono" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-info btn-sm" onclick="iniciarRecepcionQr(this)">
                                                <i class="fas fa-camera"></i> Escanear QR
                                            </button>
                                        </div>
                                    </div>
                                    <div class="bono_qr_estado small mt-2" aria-live="polite"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <div class="switch switch-success d-inline m-r-10">
                                    <input type="checkbox" id="recepcion_programa">
                                    <label for="recepcion_programa" class="cr"></label>
                                </div>
                                <label>Recepción de programa</label>
                            </div>

                            <div class="row" id="sesiones_programa" style="display: none;">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Tipo Bono</label>
                                        <select  id="bono_id_tipo_bono" name="bono_id_tipo_bono"class="form-control form-control-sm">
                                            @foreach ($tipo_bonos as $t_bono)
                                                <option value="{{ $t_bono->id }}">{{ $t_bono->nombre }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Nº de Sesiones</label>
                                        <input name="bono_sn_sesiones" id="bono_sn_sesiones" type="number"
                                            class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group text-center my-2 pb-2">
                                <div onclick="recepcion_pago();" class="btn btn-info"><i class="feather icon-check"></i> Recepcionar</div>
                                <button class="btn btn-primary"><i class="fas fa-check"></i>Generar Boleta</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                </div>
            </div>
            </div>
        </div>
    @else
        <!-- INICIO RECEPCION BONO  -->
        <!--Modal Recepción de Bonos y programas-->
        <div id="modal_recepcion_bonos_api" class="modal fade" tabindex="-1" role="dialog"
            aria-labelledby="Recepcion de bonos" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="modal_pago_consulta_title">Recepción de pago de atención</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            onclick="$('#modal_recepcion_bonos_api').modal('hide');"><span
                                aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body pb-0">
                        <div class="form-row mb-2">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <div class="alert alert-danger">
                                    <h6 class="text-danger p-16">Recuerde que siempre se debe validar datos del paciente, profesional y convenio con los datos del bono físico</h6>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <input type="hidden" name="bono_hora_medica" id="bono_hora_medica">
                            <input type="hidden" name="bono_id_profesional" id="bono_id_profesional">
                            <input type="hidden" name="bono_id_paciente" id="bono_id_paciente">
                            <input type="hidden" name="bono_id_tipo_bono" id="bono_id_tipo_bono" value="1">
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Rut del Tutor</label>
                                    <input type="person" class="form-control form-control-sm" name="bono_paciente_rut"
                                        id="bono_paciente_rut" disabled="disabled">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Nombre del Tutor</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_paciente_nombre"
                                        id="bono_paciente_nombre" disabled="disabled">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm"> Nombre Profesional</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_profesional_nombre"
                                        id="bono_profesional_nombre" disabled="disabled">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm"> Rut Profesional</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_profesional_rut"
                                        id="bono_profesional_rut" disabled="disabled">
                                </div>
                            </div>
                            {{--  <div class="col-sm-6">
                                <div class="input-group">
                                    <label class="floating-label-activo-sm">Convenio</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_prevision_txt" id="bono_prevision_txt" disabled="disabled" value="">
                                    <select id="bono_prevision" name="bono_prevision" class="form-control form-control-sm"  style="display: none;" onchange="$('#bono_prevision_txt').val( $('#bono_prevision option:selected').text() );$('#bono_prevision_txt').show();$('#bono_prevision').hide();actualizar_prevision_paciente('bono_id_paciente', 'bono_prevision');">
                                        <option value="0">Selecione una opción</option>
                                        @foreach ($prevision as $prev)
                                            <option value="{{ $prev->id }}">{{ $prev->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="$('#bono_prevision_txt').hide();$('#bono_prevision').show();"><i class="feather icon-edit"></i></button>
                                    </div>
                                </div>
                            </div>  --}}
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Servicio de atención</label>
                                    <select id="bono_id_clase_bono" name="bono_id_clase_bono" class="form-control form-control-sm" onchange="validar_prevision(this)">
                                        <option value="0" data-valor="">Seleccione un servicio</option>
                                        <option value="2" data-valor="0" data-servicio="Control sin costo">Control sin costo — $0</option>
                                        @foreach ($tarifas_atencion as $tarifa)
                                            <option value="6"
                                                data-valor="{{ (int) $tarifa->valor }}"
                                                data-servicio="{{ $tarifa->tipo_atencion }}">
                                                {{ $tarifa->tipo_atencion }} — ${{ number_format($tarifa->valor, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($tarifas_atencion->isEmpty())
                                        <small class="form-text text-muted">Configure servicios con valor en Mis lugares de atención.</small>
                                    @endif
                                    <input type="hidden" id="bono_tipo_atencion" name="bono_tipo_atencion" value="">
                                </div>
                            </div>
                            {{--  <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Nº de bono o programa</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_numero"
                                        id="bono_numero">
                                </div>
                            </div>  --}}

                            {{--  <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Valor Bonificación</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_bonificacion" id="valor_bonificacion" value="">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Aporte Seguro</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_seguro" id="valor_seguro" value="0">
                                </div>
                            </div>  --}}
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Valor a pagar</label>
                                    <input name="bono_valor_consulta" id="bono_valor_consulta" type="number"
                                        class="form-control form-control-sm" value="" readonly>
                                    <small class="form-text text-muted">Se carga automáticamente desde la configuración del lugar.</small>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="card border-info mb-3">
                                    <div class="card-body">
                                        <h6 class="text-info mb-2"><i class="fas fa-qrcode"></i> Recepción mediante QR</h6>
                                        <p class="text-muted small mb-2">Escanee el QR emitido en Bonos Veterinaria o ingrese su código.</p>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm bono_qr_recepcion"
                                                placeholder="Código u orden del bono" autocomplete="off">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-info btn-sm" onclick="iniciarRecepcionQr(this)">
                                                    <i class="fas fa-camera"></i> Escanear QR
                                                </button>
                                            </div>
                                        </div>
                                        <div class="bono_qr_estado small mt-2" aria-live="polite"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="card border-primary mb-3">
                                    <div class="card-body">
                                        <h6 class="text-primary mb-2"><i class="fas fa-file-invoice-dollar"></i> Presupuesto N.º</h6>
                                        <p class="text-muted small mb-2">Ingrese el número del presupuesto asociado a la atención.</p>
                                        <input type="text" class="form-control form-control-sm bono_numero_presupuesto"
                                            placeholder="Número de presupuesto" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            {{--  <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" id="recepcion_programa">
                                        <label for="recepcion_programa" class="cr"></label>
                                    </div>
                                    <label>Recepción de programa</label>
                                </div>

                                <div class="row" id="sesiones_programa" style="display: none;">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm">Tipo Bono</label>
                                            <select  id="bono_id_tipo_bono" name="bono_id_tipo_bono"class="form-control form-control-sm">
                                                @foreach ($tipo_bonos as $t_bono)
                                                    <option value="{{ $t_bono->id }}">{{ $t_bono->nombre }}</option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm">Nº de Sesiones</label>
                                            <input name="bono_sn_sesiones" id="bono_sn_sesiones" type="number"
                                                class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>  --}}
                            <div class="col-sm-12">
                                <div class="form-group text-center my-2 pb-2">
                                    <button type="button" onclick="recepcion_pago();" class="btn btn-info">
                                        <i class="feather icon-check"></i> Recepcionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- FIN RECEPCION BONO  -->
    @endif

    <div id="modal_lector_qr_bono" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white"><i class="fas fa-qrcode"></i> Recibir QR de pago</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"
                        onclick="detenerRecepcionQr()"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <video id="video_lector_qr_bono" class="w-100 rounded border" playsinline muted
                        style="max-height:360px;background:#111"></video>
                    <div id="mensaje_lector_qr_bono" class="small text-muted mt-2">Apunte la cámara al QR del bono.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="detenerRecepcionQr()">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        var streamRecepcionQrBono = null;
        var cicloRecepcionQrBono = null;
        var inputRecepcionQrBono = null;

        function normalizarRecepcionQrBono(valor) {
            var texto = String(valor || '').trim();
            if (!texto) return '';

            try {
                var payload = JSON.parse(texto);
                return String(payload.o || payload.orden_id || payload.numero_bono || payload.codigo || texto);
            } catch (e) {
                try {
                    var url = new URL(texto, window.location.origin);
                    return url.searchParams.get('orden_id') ||
                        url.searchParams.get('numero_bono') ||
                        url.searchParams.get('codigo') || texto;
                } catch (ignore) {
                    return texto;
                }
            }
        }

        function aplicarRecepcionQrBono(valor) {
            var codigo = normalizarRecepcionQrBono(valor);
            if (!codigo || !inputRecepcionQrBono) return;

            inputRecepcionQrBono.value = codigo;
            var numeroBono = document.getElementById('bono_numero');
            if (numeroBono) numeroBono.value = codigo;

            $(inputRecepcionQrBono).closest('.card-body').find('.bono_qr_estado')
                .removeClass('text-danger').addClass('text-success')
                .html('<i class="fas fa-check-circle"></i> QR recibido: ' + $('<div>').text(codigo).html());

            detenerRecepcionQr();
            $('#modal_lector_qr_bono').modal('hide');
        }

        async function iniciarRecepcionQr(boton) {
            inputRecepcionQrBono = $(boton).closest('.input-group').find('.bono_qr_recepcion').get(0);
            var mensaje = document.getElementById('mensaje_lector_qr_bono');

            if (!('BarcodeDetector' in window)) {
                $(inputRecepcionQrBono).focus();
                $(inputRecepcionQrBono).closest('.card-body').find('.bono_qr_estado')
                    .removeClass('text-success').addClass('text-danger')
                    .text('Este navegador no permite cámara QR. Ingrese o pegue el código manualmente.');
                return;
            }

            try {
                var detector = new BarcodeDetector({ formats: ['qr_code'] });
                streamRecepcionQrBono = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false
                });
                var video = document.getElementById('video_lector_qr_bono');
                video.srcObject = streamRecepcionQrBono;
                await video.play();
                $('#modal_lector_qr_bono').modal('show');

                cicloRecepcionQrBono = window.setInterval(async function () {
                    if (!video.videoWidth) return;
                    try {
                        var codigos = await detector.detect(video);
                        if (codigos.length) aplicarRecepcionQrBono(codigos[0].rawValue);
                    } catch (ignore) {}
                }, 350);
            } catch (error) {
                if (mensaje) mensaje.textContent = 'No se pudo abrir la cámara. Autorice su uso o ingrese el código manualmente.';
                $(inputRecepcionQrBono).focus();
            }
        }

        function detenerRecepcionQr() {
            if (cicloRecepcionQrBono) {
                window.clearInterval(cicloRecepcionQrBono);
                cicloRecepcionQrBono = null;
            }
            if (streamRecepcionQrBono) {
                streamRecepcionQrBono.getTracks().forEach(function (track) { track.stop(); });
                streamRecepcionQrBono = null;
            }
        }

        $(document).on('change blur', '.bono_qr_recepcion', function () {
            inputRecepcionQrBono = this;
            aplicarRecepcionQrBono(this.value);
        });
        $('#modal_lector_qr_bono').on('hidden.bs.modal', detenerRecepcionQr);
    </script>

    @include('app.profesional.modales.boton_flotante_agenda_exa_ciru')

    {{-- lugar atencion box profesional --}}
    @include('general.asignacion_box_prof.asignacion_box_prof')

@endsection

@section('page-script')
    <script src="{{ asset('js/jQuery-Mask-Plugin-master/jquery.mask.js') }}"></script>
    <script>
        $(document).ready(function () {
            function calcularSaldoConsulta() {
                let valorConsulta = parseFloat($('#bono_valor_consulta').val()) || 0;
                let valorAbono = parseFloat($('#bono_valor_abono_consulta').val()) || 0;
                let saldo = valorConsulta - valorAbono;

                $('#bono_valor_saldo_consulta').val(saldo);
            }

            $('#bono_valor_consulta, #bono_valor_abono_consulta').on('input', calcularSaldoConsulta);
            $('.mask_date').mask("dd/mm/0000", {
                'translation': {
                    0: {pattern: /[0-9]/},
                    d: {pattern: /[0-9]/},
                    m: {pattern: /[0-9]/},
                    Y: {
                        pattern: function (value) {
                            value = value.replace(/\D/g, '');

                            if (value.length === 4) {
                                var year = parseInt(value, 10);
                                return year >= {{ date('Y')-110 }} && year <= {{ date('Y') }};
                            }
                            return true;
                        }
                    }
                },
                onKeyPress: function(value, e, field, options) {
                    $('#mensaje_reserva_hora_fecha_nac').html('');
                    $('#mensaje_reserva_hora_fecha_nac').hide();
                    var year = value.split('/')[2];
                    if (year)
                    {
                        var year_txt = year.toString();
                        if(year_txt.length >= 4)
                        {
                            $('#mensaje_reserva_hora_fecha_nac').html('');
                            $('#mensaje_reserva_hora_fecha_nac').hide();
                            if (year && (year < {{ date('Y')-110 }} || year > {{ date('Y') }} ))
                            {

                                $('#mensaje_reserva_hora_fecha_nac').html('');
                                $('#mensaje_reserva_hora_fecha_nac').show();
                                $('#mensaje_reserva_hora_fecha_nac').html('La fecha cargada no es valida');

                                console.log('validacion:');
                                console.log(year);
                            }

                            if (validarEdad(value)) {
                                console.log("La edad es válida.");
                                $('#mensaje_reserva_hora_fecha_nac').html('');
                                $('#mensaje_reserva_hora_fecha_nac').hide();
                                validar_email_agenda();
                                validar_campo_telefono();
                            } else {
                                console.log("La edad no es válida.");
                                $("#guardar_reserva_paciente").prop('disabled', true);
                                $('#mensaje_reserva_hora_fecha_nac').html('');
                                $('#mensaje_reserva_hora_fecha_nac').show();
                                $('#mensaje_reserva_hora_fecha_nac').html('La fecha cargada no es valida');
                            }


                        }
                    }
                }
            });
        });

        function validarEdad(fechaNacimiento) {

            console.log('validarEdad - resources\views\app\profesional\agenda.blade.php');
            var partes = fechaNacimiento.split('/');
            var dia = parseInt(partes[0], 10);
            var mes = parseInt(partes[1], 10) - 1;
            var anio = parseInt(partes[2], 10);

            var fechaNac = new Date(anio, mes, dia);

            var hoy = new Date();

            var edad = hoy.getFullYear() - fechaNac.getFullYear();
            var mes = hoy.getMonth() - fechaNac.getMonth();
            var dia = hoy.getDate() - fechaNac.getDate();

            if (mes < 0 || (mes === 0 && dia < 0)) {
                edad--;
            }

            return edad >= 0 && edad <= 120;
        }

        function evaluar_edad() {
            let fechaNacimiento = new Date($('#reserva_hora_fecha_nac').val());
            let hoy = new Date();
            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();

            if (hoy.getMonth() < fechaNacimiento.getMonth() || (hoy.getMonth() === fechaNacimiento.getMonth() && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--;
            }

            if (edad < 18) {
                $('#paciente_dependiente').prop('checked', 'checked');
                activar_paciente_dependientes();
                $('#reserva_hora_correo').attr('onblur', "");
                $('#reserva_hora_telefono_uno').attr('onchange', "");
                $('#btn_reserva_hora_telefono_uno_validar').hide();

                $('#reserva_hora_telefono_uno_codigo_validador').val('');
                $('#div_codigo_validador_mensaje').html('');
                $('#result_codigo_validacion').val('0');

                $('#reserva_hora_representante_telefono_uno_codigo_validador').val('');
                $('#div_representante_codigo_validador_mensaje').html('');
                $('#result_representante_codigo_validacion').val('0');
            } else {
                $('#paciente_dependiente').prop('checked', '')
                activar_paciente_dependientes();
                $('#reserva_hora_correo').attr('onblur', "validar_email_agenda();");
                $('#reserva_hora_telefono_uno').attr('onchange', "validar_campo_telefono();");
                $('#btn_reserva_hora_telefono_uno_validar').show();

                $('#reserva_hora_telefono_uno_codigo_validador').val('');
                $('#div_codigo_validador_mensaje').html('');
                $('#result_codigo_validacion').val('0');

                $('#reserva_hora_representante_telefono_uno_codigo_validador').val('');
                $('#div_representante_codigo_validador_mensaje').html('');
                $('#result_representante_codigo_validacion').val('0');
            }
        }

        function buscar_rut_representente() {

            let rut = $('#reserva_hora_representante_rut').val();
            let url = "{{ route('profesional.buscar_rut_paciente') }}";

            $('.div_representante_nuevo').hide();
            $('.div_representante_existente').hide();

            $.ajax({

                    url: url,
                    type: "get",
                    data: {
                        rut: rut,
                    },
                })
                .done(function(data) {

                    if (data !== 'null') {
                        data = JSON.parse(data);

                        if (data.tipo_paciente == 'SI') {
                            $('#reserva_representante_nuevo_exitente').val(1);

                            $('#reserva_representante_nombre').text(data.nombres + ' ' + data.apellido_uno + ' ' + data
                                .apellido_dos);
                            $('#reserva_representante_fecha_nacimiento').text(data.fecha_nac);
                            if (data.sexo == 'M') {
                                $('#reserva_representante_sexo').text('Masculino');
                            } else {
                                $('#reserva_representante_sexo').text('Femenino');
                            }
                            $('#reserva_representante_direccion').text(data.direccion.direccion + ' ' + data.direccion
                                .numero_dir + ', ' + data.direccion.ciudad.nombre);
                            $('#reserva_representante_email').text(data.email);
                            $('#reserva_representante_telefono').text(data.telefono_uno);

                            $('#reserva_representante_id').val(data.id);
                            $('#reserva_representante_id_usuario').val(data.id_usuario);

                            $("#guardar_reserva_paciente").prop('disabled', false);

                            $('.div_representante_nuevo').hide();
                            $('.div_representante_existente').show();
                        } else {
                            $('#reserva_representante_nuevo_exitente').val(0);
                            $('#reserva_representante_id').val('');
                            $('#reserva_representante_id_usuario').val('');
                            $('.div_representante_nuevo').show();
                            $('.div_representante_existente').hide();

                            $('#reserva_hora_representante_nombres_paciente').val('');
                            $('#reserva_hora_representante_apellido_uno').val('');
                            $('#reserva_hora_representante_apellido_dos').val('');
                            $('#reserva_hora_representante_fecha_nac').val('');
                            $('#reserva_hora_representante_sexo').val('');
                            $('#reserva_hora_representante_direccion').val('');
                            $('#reserva_hora_representante_numero_dir').val('');
                            $('#reserva_hora_representante_region_agregar').val('');
                            buscar_ciudad_repesentante();
                            $('#reserva_hora_representante_correo').val('');
                            $('#reserva_hora_representante_telefono_uno').val('');
                        }
                    } else {
                        $('#reserva_representante_id').val('');
                        $('#reserva_representante_id_usuario').val('');
                        $('.div_representante_nuevo').show();
                        $('.div_representante_existente').hide();

                        $('#reserva_hora_representante_nombres_paciente').val('');
                        $('#reserva_hora_representante_apellido_uno').val('');
                        $('#reserva_hora_representante_apellido_dos').val('');
                        $('#reserva_hora_representante_fecha_nac').val('');
                        $('#reserva_hora_representante_sexo').val('');
                        $('#reserva_hora_representante_direccion').val('');
                        $('#reserva_hora_representante_numero_dir').val('');
                        $('#reserva_hora_representante_region_agregar').val('');
                        buscar_ciudad_repesentante();
                        $('#reserva_hora_representante_correo').val('');
                        $('#reserva_hora_representante_telefono_uno').val('');
                    }

                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
        }

        function activar_paciente_dependientes()
        {
            if ($('#paciente_dependiente').prop('checked'))
            {
                $('.seccion_reserva_paciente_nuevo_representante').show();
                $('#reserva_hora_correo').attr('onblur', "");
                $('#reserva_hora_telefono_uno').attr('onchange', "");
                $('#btn_reserva_hora_telefono_uno_validar').hide();
            }
            else
            {
                $('.seccion_reserva_paciente_nuevo_representante').hide();
                $('#reserva_hora_correo').attr('onblur', "validar_email_agenda();");
                $('#reserva_hora_telefono_uno').attr('onchange', "validar_campo_telefono();");
                $('#btn_reserva_hora_telefono_uno_validar').show();
                if($('#reserva_hora_fecha_nac').val() !=='')
                    evaluar_edad();
            }
        }

        function validar_campo_telefono()
        {
            var telefono = $('#reserva_hora_telefono_uno').val();
            var email = $('#reserva_hora_correo').val();

            console.log('telefono:*'+telefono+'*');
            console.log('email:*'+email+'*');

            if(email == '')
            {
                {
                    var re = new RegExp(/^\d{9}$/);
                    if( re.test(telefono) )
                    {

                        if (validarEdad($('#reserva_hora_fecha_nac').val())) {
                            console.log("La edad es válida.");
                            $('#btn_reserva_hora_telefono_uno_validar').attr('disabled',false);
                        } else {
                            console.log("La edad no es válida.");
                            $('#btn_reserva_hora_telefono_uno_validar').attr('disabled',true);
                            $("#guardar_reserva_paciente").prop('disabled', true);
                        }

                    }
                    else
                        $('#btn_reserva_hora_telefono_uno_validar').attr('disabled',true);
                }
            }
        }

        function enviar_validacion_telefono()
        {
            $('#btn_reserva_hora_telefono_uno_validar').hide();
            $('#div_codigo_validador').show();
            $('#reserva_hora_telefono_uno_codigo_validador').val('');
            $('#div_codigo_validador_mensaje').html('');
            $('#result_codigo_validacion').val('0');
        }

        function validar_codigo_telefono()
        {
            var codigo = $('#reserva_hora_telefono_uno_codigo_validador').val();
            if(codigo.length >= 4)
            {
                console.log(codigo);
                if(codigo == 1234)
                {
                    $('#div_codigo_validador').hide();
                    $('#div_codigo_validador_mensaje').show();
                    $('#div_codigo_validador_mensaje').html('<span style="color:green;">Valido</span>');
                    $('#result_codigo_validacion').val('1');
                    $("#guardar_reserva_paciente").prop('disabled', false);
                }
                else
                {
                    $('#div_codigo_validador').show();
                    $('#div_codigo_validador_mensaje').show();
                    $('#div_codigo_validador_mensaje').html('<span style="color:red;">No Valido</span>');
                    $('#result_codigo_validacion').val('0');
                    $("#guardar_reserva_paciente").prop('disabled', true);
                }
            }
        }

        function validar_campo_telefono_representante()
        {
            var telefono = $('#reserva_hora_representante_telefono_uno').val();
            var email = $('#reserva_hora_representante_correo').val();

            if(email === '')
            {
                {
                    var re = new RegExp(/^\x2b56[6-9][0-9]{8}$/i);
                    if( re.test(telefono) )
                        $('#btn_reserva_hora_representante_telefono_uno_validar').attr('disabled',false);
                    else
                        $('#btn_reserva_hora_representante_telefono_uno_validar').attr('disabled',true);
                }
            }
            $('#reserva_hora_representante_telefono_uno_codigo_validador').val('');
            $('#div_representante_codigo_validador_mensaje').html('');
            $('#result_representante_codigo_validacion').val('0');
        }

        function enviar_validacion_telefono_representante()
        {
            $('#btn_reserva_hora_representante_telefono_uno_validar').hide();
            $('#div_representante_codigo_validador').show();
            $('#reserva_hora_representante_telefono_uno_codigo_validador').val('');
            $('#div_representante_codigo_validador_mensaje').html('');
            $('#result_representante_codigo_validacion').val('0');
        }

        function validar_codigo_telefono_representante()
        {
            var codigo = $('#reserva_hora_representante_telefono_uno_codigo_validador').val();
            if(codigo.length >= 4)
            {
                console.log(codigo);
                if(codigo == 1234)
                {
                    $('#div_representante_codigo_validador').hide();
                    $('#div_representante_codigo_validador_mensaje').show();
                    $('#div_representante_codigo_validador_mensaje').html('<span style="color:green;">Valido</span>');
                    $('#result_representante_codigo_validacion').val('1');
                    $("#guardar_reserva_paciente").prop('disabled', false);
                }
                else
                {
                    $('#div_representante_codigo_validador').show();
                    $('#div_representante_codigo_validador_mensaje').show();
                    $('#div_representante_codigo_validador_mensaje').html('<span style="color:red;">No Valido</span>');
                    $('#result_representante_codigo_validacion').val('0');
                    $("#guardar_reserva_paciente").prop('disabled', true);
                }
            }
        }

        function editar_info_paciente()
    {
        $('.paciente_view').hide();
        $('.paciente_edit').show();
        $('#modificando_paciente').val(1);
    }

    function editar_info_paciente_asistente()
    {
        $('.paciente_view_asistente').hide();
        $('.paciente_edit_asistente').show();
        $('#modificando_paciente_asistente').val(1);
    }

    function cancelar_modificacion_paciente()
    {
        $('.paciente_view').show();
        $('.paciente_edit').hide();
        $('#modificando_paciente').val(0);
    }


    function cancelar_modificacion_paciente_asistente()
    {
        $('.paciente_view_asistente').show();
        $('.paciente_edit_asistente').hide();
        $('#modificando_paciente_asistente').val(0);
    }

    function actualizar_paciente()
    {
        var modificando = $('#modificando_paciente').val();
        var id_paciente = $('#reserva_hora_id_paciente').val();
        var nombre_paciente = $('#input_reserva_hora_nombre').val();
        var apellido_uno_paciente = $('#input_reserva_hora_apellido_uno').val();
        var apellido_dos_paciente = $('#input_reserva_hora_apellido_dos').val();
        var fecha_nacimiento = $('#input_reserva_fecha_nacimiento').val();
        var sexo_paciente = $('#input_reserva_sexo').val();
        var convenio_paciente = $('#input_reserva_convenio').val();
        var convenio_txt_paciente = $('#input_reserva_convenio option:selected').text()
        var direccion_paciente = $('#input_reserva_direccion_direccion').val();
        var numero_direccion_paciente = $('#input_reserva_direccion_numero_dir').val();
        var region_paciente = $('#input_reserva_direccion_region').val();
        var ciudad_paciente = $('#input_reserva_direccion_ciudad').val();
        var ciudad_txt_paciente = $('#input_reserva_direccion_ciudad option:selected').text();
        var email_paciente = $('#input_reserva_hora_email').val();
        var telefono_paciente = $('#input_reserva_hora_telefono').val();
        var valido = 1;
        var mensaje = '';

        if( id_paciente == '' )
        {
            valido = 0;
            mensaje += 'Paciente Requerido\n';
        }
        if( nombre_paciente == '' )
        {
            valido = 0;
            mensaje += 'Nombre Paciente requerido\n';
        }
        if( apellido_uno_paciente == '' )
        {
            valido = 0;
            mensaje += 'Apellido Paterno de paciente requerido\n';
        }
        if( apellido_dos_paciente == '' )
        {
            valido = 0;
            mensaje += 'Apellido Materno de paciente requerido\n';
        }
        if( fecha_nacimiento == '' )
        {
            valido = 0;
            mensaje += 'Fecha de Nacimiento del paciente requerido\n';
        }
        else
        {
            fecha_nacimiento = formatDateDB(fecha_nacimiento);
        }
        if( sexo_paciente == '' )
        {
            valido = 0;
            mensaje += 'Sexo del paciente requerido\n';
        }
        if( convenio_paciente == '' )
        {
            valido = 0;
            mensaje += 'Convenio del paciente requerido\n';
        }
        if( direccion_paciente == '' )
        {
            valido = 0;
            mensaje += 'Dirección del paciente requerido\n';
        }
        if( numero_direccion_paciente == '' )
        {
            valido = 0;
            mensaje += 'Número de Dirección del paciente requerido\n';
        }
        if( region_paciente == '' )
        {
            valido = 0;
            mensaje += 'Región de Dirección del paciente requerido\n';
        }
        if( ciudad_paciente == '' )
        {
            valido = 0;
            mensaje += 'Ciudad de Dirección del paciente requerido\n';
        }
        if( email_paciente == '' )
        {
            valido = 0;
            mensaje += 'Email del paciente requerido\n';
        }
        if( telefono_paciente == '' )
        {
            valido = 0;
            mensaje += 'Teléfono del paciente requerido\n';
        }

        if(valido == 1)
        {
            if(modificando == 1)
            {
                let url = "{{ route('asistente.paciente.modificar') }}";

                $.ajax({

                    url: url,
                    type: "get",
                    data: {
                        id: id_paciente,
                        nombre: nombre_paciente,
                        apellido_uno: apellido_uno_paciente,
                        apellido_dos: apellido_dos_paciente,
                        fecha_nacimiento: fecha_nacimiento,
                        sexo: sexo_paciente,
                        convenio: convenio_paciente,
                        direccion: direccion_paciente,
                        numero_direccion: numero_direccion_paciente,
                        region: region_paciente,
                        ciudad: ciudad_paciente,
                        email: email_paciente,
                        telefono: telefono_paciente,
                    },
                })
                .done(function(data) {
                    if (data.estado == 1)
                    {
                        if (data.estado == 1)
                        {
                            $('#reserva_hora_nombre').text(nombre_paciente + ' ' + apellido_uno_paciente + ' ' + apellido_dos_paciente);
                            $('#reserva_fecha_nacimiento').text(fecha_nacimiento);
                            if (sexo_paciente == 'M') {
                                $('#reserva_sexo').text('Masculino');
                            } else {
                                $('#reserva_sexo').text('Femenino');
                            }
                            $('#reserva_hora_email').text(email_paciente);
                            $('#reserva_hora_telefono').text(telefono_paciente);
                            $('#reserva_convenio').text(convenio_txt_paciente);
                            $('#reserva_direccion').text(direccion_paciente+' '+numero_direccion_paciente+', '+ciudad_txt_paciente);

                            $('.paciente_view').show();
                            $('.paciente_edit').hide();
                            $('#modificando_paciente').val(0);

                            swal({
                                title: "Actualización de Paciente",
                                text: "Actualización Exitosa",
                                icon: "success",
                            });
                        }
                        else
                        {
                            swal({
                                title: "Actualización de Paciente",
                                text: "Falla en Actualización.\nIntente de nuevo.",
                                icon: "error",
                            });
                        }
                    }
                    else
                    {
                        swal({
                            title: "Actualización de Paciente",
                            text: "Falla en Actualización.\nIntente de nuevo.",
                            icon: "error",
                        });
                    }
                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
            }
            else
            {
                swal({
                    title: "Actualización de Paciente",
                    text: "Esta actualizando al paciente sin haber inicado proceso de edición",
                    icon: "error",
                });
            }
        }
        else
        {
            swal({
                title: "Actualización de Paciente",
                text: "Campos requeridos.\n"+mensaje,
                icon: "error",
                buttons: "Aceptar"
            });
        }


    }

    function actualizar_paciente_asistente(){
        var modificando = $('#modificando_paciente_asistente').val();
        var id_paciente = $('#estado_id_paciente').val();
        var nombre_paciente = $('#input_reserva_hora_nombre_asistente').val();
        var apellido_uno_paciente = $('#input_reserva_hora_apellido_uno_asistente').val();
        var apellido_dos_paciente = $('#input_reserva_hora_apellido_dos_asistente').val();
        var fecha_nacimiento = $('#input_reserva_fecha_nacimiento_asistente').val();
        var sexo_paciente = $('#input_reserva_sexo_asistente').val();
        var email_paciente = $('#input_reserva_hora_email_asistente').val();
        var telefono_paciente = $('#input_reserva_hora_telefono_asistente').val();
        var direccion_paciente = $('#input_reserva_hora_direccion_asistente').val();
        var numero_direccion_paciente = $('#input_reserva_hora_numero_asistente').val();
        var region_paciente = $('#input_reserva_hora_region_asistente').val();
        var ciudad_paciente = $('#input_reserva_hora_ciudad_asistente').val();
        var valido = 1;
        var mensaje = '';

        var data = {
            id: id_paciente,
            nombre: nombre_paciente,
            apellido_uno: apellido_uno_paciente,
            apellido_dos: apellido_dos_paciente,
            fecha_nacimiento: fecha_nacimiento,
            sexo: sexo_paciente,
            email: email_paciente,
            telefono: telefono_paciente,
            direccion: direccion_paciente,
            numero_direccion: numero_direccion_paciente,
            region: region_paciente,
            ciudad: ciudad_paciente,
        }

        if( id_paciente == '' )
        {
            valido = 0;
            mensaje += 'Paciente Requerido\n';
        }
        if( nombre_paciente == '' )
        {
            valido = 0;
            mensaje += 'Nombre Paciente requerido\n';
        }
        if( apellido_uno_paciente == '' )
        {
            valido = 0;
            mensaje += 'Apellido Paterno de paciente requerido\n';
        }
        if( apellido_dos_paciente == '' )
        {
            valido = 0;
            mensaje += 'Apellido Materno de paciente requerido\n';
        }
        if( fecha_nacimiento == '' )
        {
            valido = 0;
            mensaje += 'Fecha de Nacimiento del paciente requerido\n';
        }
        else
        {
        }
        if( sexo_paciente == '' )
        {
            valido = 0;
            mensaje += 'Sexo del paciente requerido\n';
        }

        if(valido == 1)
        {
            if(modificando == 1)
            {
                let url = "{{ route('asistente.paciente.modificar') }}";

                $.ajax({

                    url: url,
                    type: "get",
                    data: data,
                })
                .done(function(data) {
                    console.log(data);
                    if (data.estado == 1)
                    {
                        if (data.estado == 1)
                        {
                            $('#datos_consulta_nombre').text(nombre_paciente + ' ' + apellido_uno_paciente + ' ' + apellido_dos_paciente);
                            $('#datos_consulta_edad').text(fecha_nacimiento);
                            if (sexo_paciente == 'M') {
                                $('#datos_consulta_sexo').text('Masculino');
                            } else {
                                $('#datos_consulta_sexo').text('Femenino');
                            }
                            $('#datos_consulta_email').text(email_paciente);
                            $('#datos_consulta_telefono').text(telefono_paciente);
                            $('#datos_consulta_direcion').text(direccion_paciente);
                            $('#datos_consulta_numero').text(numero_direccion_paciente);

                            $('.paciente_view_asistente').show();
                            $('.paciente_edit_asistente').hide();
                            $('#modificando_paciente_asistente').val(0);

                            swal({
                                title: "Actualización de Paciente",
                                text: "Actualización Exitosa",
                                icon: "success",
                            });
                        }
                        else
                        {
                            swal({
                                title: "Actualización de Paciente",
                                text: "Falla en Actualización.\nIntente de nuevo.",
                                icon: "error",
                            });
                        }
                    }
                    else
                    {
                        swal({
                            title: "Actualización de Paciente",
                            text: "Falla en Actualización.\nIntente de nuevo.",
                            icon: "error",
                        });
                    }
                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
            }
            else
            {
                swal({
                    title: "Actualización de Paciente",
                    text: "Esta actualizando al paciente sin haber inicado proceso de edición",
                    icon: "error",
                });
            }
        }
        else
        {
            swal({
                title: "Actualización de Paciente",
                text: "Campos requeridos.\n"+mensaje,
                icon: "error",
                buttons: "Aceptar"
            });
        }
    }

        function buscar_ciudad_general(input_region, input_ciudad, id_ciudad=0)
        {
            var region = $('#'+input_region).val();
            console.log(region);
            let url = "{{ route('home.buscar_ciudad_region') }}";
            $.ajax({
                url: url,
                type: "get",
                data: {
                    region: region,
                },
            })
            .done(function(data) {
                if (data != null) {
                    data = JSON.parse(data);

                    let ciudades = $('#'+input_ciudad);

                    ciudades.find('option').remove();
                    ciudades.append('<option value="0">seleccione</option>');
                    $(data).each(function(i, v) {
                        ciudades.append('<option value="' + v.id + '">' + v.nombre + '</option>');
                    })

                    if(id_ciudad != 0)
                    {
                        ciudades.val(id_ciudad);
                    }
                }
                else
                {
                    swal({
                        title: "Error",
                        text: "Error al cargar las ciudades",
                        icon: "error",
                        buttons: "Aceptar",
                        DangerMode: true,
                    });
                }
            })
            .fail(function(jqXHR, ajaxOptions, thrownError) {
                console.log(jqXHR, ajaxOptions, thrownError)
            });
        };

        function actualizar_prevision_paciente(input_paciente, input_prevision)
        {
            var paciente = $('#'+input_paciente).val();
            var prevision = $('#'+input_prevision).val();

            let url = "{{ route('paciente.prevision.actualizar') }}";
            $.ajax({
                url: url,
                type: "post",
                data: {
                    _token: CSRF_TOKEN,
                    id_paciente: paciente,
                    id_prevision: prevision,
                },
            })
            .done(function(data) {
                if (data != null) {
                    if(data.estado == 1)
                    {
                        swal({
                            title: "Actualizacion de Convenio del Paciente.",
                            text: "Convenio del Paciente actualizado.",
                            icon: "success",
                        });
                    }
                    else
                    {
                        swal({
                            title: "Actualizacion de Convenio del Paciente.",
                            text: "Se presento una falla al intentar actualizar el Convenio del Paciente.",
                            icon: "error",
                        });
                    }
                }
                else
                {
                    swal({
                        title: "Actualizacion de Convenio del Paciente.",
                        text: "Se presento una falla al intentar actualizar el Convenio del Paciente.",
                        icon: "error",
                    });
                }
            })
            .fail(function(jqXHR, ajaxOptions, thrownError) {
                console.log(jqXHR, ajaxOptions, thrownError)
            });
        }

        function validar_prevision(value){
            let id_clase_bono = value.value;
            const opcionSeleccionada = value.options[value.selectedIndex];
            const valorConfigurado = opcionSeleccionada ? opcionSeleccionada.dataset.valor : undefined;
            const servicioConfigurado = opcionSeleccionada ? (opcionSeleccionada.dataset.servicio || '') : '';

            if (valorConfigurado !== undefined) {
                $('#bono_tipo_atencion').val(servicioConfigurado);
                $('#valor_bonificacion').val(0).attr('disabled', false);
                $('#valor_seguro').val(0).attr('disabled', false);
                $('#bono_valor_consulta').val(id_clase_bono == 0 ? '' : (parseInt(valorConfigurado, 10) || 0));
                return;
            }

            if(id_clase_bono != 2 && id_clase_bono != 0){
                let url = "{{ ROUTE('profesional.dame_valor_consulta') }}";
                let data = {
                    id_clase_bono: id_clase_bono,
                    id_lugar_atencion: $('#id_lugar_atencion').val(),
                    _token: CSRF_TOKEN
                }

                $.ajax({
                    url: url,
                    type: "post",
                    data: data,
                    success: function(resp) {
                        console.log(resp);
                        if(id_clase_bono == 6){
                            var valor = resp.valor;
                            var valor_bon = 0;
                        }else if(id_clase_bono == 8){
                            var valor = resp.valor_garantia;
                            var valor_bon = 0;
                        }else{
                            var valor = resp.valor_copago_fonasa;
                            var valor_bon = resp.valor_bon_fonasa;
                        }

                        $('#bono_valor_consulta').val(valor);
                        $('#valor_bonificacion').val(valor_bon);

                    }
                })

            }else{
                $('#valor_bonificacion').val(0);
                $('#valor_bonificacion').attr('disabled', false);
                $('#valor_seguro').val(0);
                $('#valor_seguro').attr('disabled', false);
                $('#bono_valor_consulta').val(0);
            }

        }

    </script>
@endsection
