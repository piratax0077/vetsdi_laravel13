@extends('template.usuario.template')
@section('content')
<div class="pcoded-main-container" >
    <div class="pcoded-content">
        <!--Header-->
  
        <div class="page-header encabezado-escritorio">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        @include('template.include.saludo_escritorio', ['nombre_saludo' => $paciente->nombres, 'texto_saludo' => 'Bienvenido/a a tu escritorio de cuidado de mascotas'])
                        <!--<ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ ROUTE('paciente.home') }}">Bienvenido/a a tu escritorio de cuidado de mascotas</a>
                            </li>
                        </ul>-->
                    </div>
                </div>
            </div>
        </div>
        <!--Cierre: Header-->

        <div class="form-row m-b-30 mt-n2">
            <div class="col-md-12">
                <div class="card-deck">
                    <div class="card subir">
                        <a href="{{ route('paciente.mascotas.index') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/mascotas.svg') }}">
                                <h5 class="mt-2"> Mis <br>mascotas</h5>
                            </div>
                        </a>
                    </div>
                    <div class="card subir">
                          <a href="{{ ROUTE('paciente.mascotas.suscripcion_servicios') }}"> 
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/alimento.png') }}">
                                <h5 class="mt-2"> Servicios cercanos </h5>
                            </div>
                          </a>
                    </div>
                    <div class="card subir">
                        <a href="{{ route('paciente.integraciones.alimentos') }}" target="_blank" rel="noopener">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/alimento.png') }}" alt="Alimentos">
                                <h5 class="mt-2">Alimentos</h5>
                                <small class="text-muted">Acceso con tu cuenta VET SDI</small>
                            </div>
                        </a>
                    </div>
                    <div class="card subir">
                        <a href="{{ route('paciente.integraciones.farmacia') }}" target="_blank" rel="noopener">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/i-medic.svg') }}" alt="Farmacia">
                                <h5 class="mt-2">Farmacia</h5>
                                <small class="text-muted">Farmacia Central</small>
                            </div>
                        </a>
                    </div>
                    <!--<div class="card subir">
                          <a href="{{ ROUTE('paciente.mascotas.pagos_suscripcion') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/alimento.png') }}">
                                <h5 class="mt-2"> Suscripciones y facturación </h5>
                            </div>
                        </a>
                    </div>-->
                    <!--<div class="card subir">
                        <a href="{{ ROUTE('paciente.mascotas.inscripcion_medicamentos') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/i-medic.svg') }}">
                                <h5 class="mt-2"> Registro de <br>mis medicamentos</h5>
                            </div>
                        </a>
                    </div>-->
                    <!--<div class="card subir">
                        <a href="{{ ROUTE('paciente.convenios') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/convenios.png') }}">
                                <h5 class="mt-2"> Mis convenios <br>de atención</h5>
                            </div>
                        </a>
                    </div>-->
                    {{--  <div class="card subir">
                        <a href="https://www.cronicos.cl/" class="btn" type="button">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/profesionales.svg') }}">
                                <h5 class="mt-2"> Controles de Crónicos</h5>
                            </div>
                        </a>
                        {{--  <a href="https://www.cronicos.cl/" class="btn" type="button">
                            <div class="card-body">
                                <div class="row">
                                    <h5 class="card-body text-center">Controles de Crónicos </h5>
                                    <img class="wid-70 ml-auto" src="{{ asset('images/iconos/cronicos.svg') }}">
                                </div>
                            </div>
                        </a>  --}}
                        {{--  <a href="{{ ROUTE('check_sdi') }}?urla=Inicio&urln=Mi_Ficha_Medica">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/fmu.svg') }}">
                                <h5 class="mt-1"> Controles de Crónicos </h5>
                            </div>
                        </a>  --}}
                    {{--  </div>  --}}

                </div>
            </div>
        </div>
        @if(($mascotasIncompletas ?? collect())->isNotEmpty())
        <div class="alert alert-warning d-flex justify-content-between align-items-center"><div><strong>Completa la ficha de {{ $mascotasIncompletas->pluck('nombre')->join(', ') }}</strong><br><small>La inscripción rápida desde Veterfarma guardó los datos esenciales. Faltan antecedentes para completar su ficha.</small></div><a href="{{ route('paciente.mis_mascotas') }}" class="btn btn-warning btn-sm">Completar ahora</a></div>
        @endif

        <!--PROX CITAS DE MASCOTAS-->
        <div class="row m-b-30" >
            <div class="col-md-8 columna-citas">
                <div class="card h-100 pb-0" >
                    <div class="card-header encabezado-citas">
                        <h5><i class="feather icon-calendar mr-2"></i> Mis próximas citas</h5>
                        <button type="button" class="btn btn-info btn-sm" onclick="abrirModalAgendaCita();">
                            <i class="feather icon-calendar mr-1"></i> Agendar hora
                        </button>
                    </div>
                    <div class="card-body pt-4 pb-0" style="height:400px;">
                        <div class="dt-responsive table-responsive" style="height:350px;back">
                            <table id="simpletable" class="table table-striped table-bordered nowrap table-xs">
                                <thead>
                                    <tr>
                                        <th>Acción</th>
                                        <th>Mascota</th>
                                        <th>Profesional</th>
                                        <th>Información de Atención</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($hora_medica as $hora)
                                        <tr>
                                            <td class="align-middle">
                                                @if (in_array((int) ($hora->id_estado_visual ?? $hora->id_estado), [1, 16], true))
                                                    <button class="btn btn-info btn-icon btn-confirmar-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Confirmar hora"
                                                        onclick="confirmar({{ $hora->id }});">
                                                        <i class="feather icon-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-icon btn-anular-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Anular hora"
                                                        onclick="anular({{ $hora->id }});">
                                                        <i class="feather icon-x"></i>
                                                    </button>
                                                @elseif (($hora->id_estado_visual ?? $hora->id_estado) == 2)
                                                    <button class="btn btn-info btn-icon btn-confirmar-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Hora confirmada"
                                                        disabled="disabled">
                                                        <i class="feather icon-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-icon btn-anular-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Anular hora"
                                                        onclick="anular({{ $hora->id }});">
                                                        <i class="feather icon-x"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-info btn-icon btn-confirmar-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Confirmar hora"
                                                        disabled="disabled">
                                                        <i class="feather icon-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-icon btn-anular-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Anular hora"
                                                        disabled="disabled">
                                                        <i class="feather icon-x"></i>
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <strong>{{ $hora->nombre_mascota ?: 'Sin mascota' }}</strong>
                                                @if ($hora->Mascota && (int) $hora->Mascota->estado === 0)
                                                    <br><small class="text-muted">Mascota eliminada</small>
                                                @endif
                                                @if (!empty($hora->nombre_especie_mascota))
                                                    <br>{{ $hora->nombre_especie_mascota }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $hora->nombre_profesional_completo }}<br>
                                                {{ $hora->nombre_especialidad_resumen }}
                                            </td>
                                            <td>
                                                {{ $hora->nombre_lugar_atencion }}<br>
                                                {{ $hora->direccion_lugar_atencion }}<br>
                                                <span style="font-weight:bold;">
                                                    {{ \Carbon\Carbon::parse($hora->fecha_consulta . ' ' . $hora->hora_inicio)->format('d-m-Y H:i') }}
                                                    hrs
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                @php $estadoVisualHora = (int) ($hora->id_estado_visual ?? $hora->id_estado); @endphp
                                                <span class="estado-hora-chip {{ in_array($estadoVisualHora, [1, 16], true) ? 'pendiente' : ($estadoVisualHora == 2 ? 'confirmada' : ($estadoVisualHora == 3 ? 'cancelada' : (in_array($estadoVisualHora, [4, 5, 8], true) ? 'en-proceso' : ($estadoVisualHora == 6 ? 'realizada' : ($estadoVisualHora == 7 ? 'inasistida' : 'desconocida'))))) }}">
                                                    {{ $hora->texto_estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No existen registros</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 columna-beneficios">
                <!-- Vouchers de Veterfarma y Alimentos, se cargan por ajax -->
                <section class="card beneficios-mascota" id="vouchers-veterfarma-card">
                    <div class="card-header beneficios-mascota-encabezado">
                        <span class="beneficios-mascota-icono"><i class="feather icon-tag"></i></span>
                        <div class="beneficios-mascota-titulos">
                            <h5>Beneficios para tu mascota</h5>
                            <small>Veterfarma y Alimentos</small>
                        </div>
                        <span class="beneficios-mascota-contador d-none" id="vouchers-veterfarma-contador"></span>
                        <button type="button" class="beneficios-mascota-recargar" id="vouchers-veterfarma-recargar" title="Actualizar beneficios" aria-label="Actualizar beneficios">
                            <i class="feather icon-refresh-cw"></i>
                        </button>
                    </div>
                    <div class="beneficios-mascota-cuerpo" id="vouchers-veterfarma-list" aria-live="polite">
                        <div class="beneficios-mascota-cargando" aria-label="Consultando beneficios"><span></span><span></span></div>
                    </div>
                </section>

                <!-- Banner de ofertas: el producto lleva a promociones especiales y el botón a las generales -->
                <div class="card espacio-promocional">
                    <a href="{{ ROUTE('paciente.mascotas.promociones_especiales') }}" class="espacio-promocional-imagen" aria-label="Ver promociones especiales">
                        <img src="{{ asset('images/iconos/publicidad.png') }}" alt="Alimentos en promoción">
                    </a>
                    <div class="espacio-promocional-contenido">
                        <span class="espacio-promocional-etiqueta">Espacios promocionales</span>
                        <h5 class="espacio-promocional-titulo">Ofertas que tu mascota amará</h5>
                        <a href="{{ ROUTE('paciente.mascotas.promociones_generales') }}" class="espacio-promocional-boton">
                            Ver ofertas <i class="feather icon-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- AGREGAR GEOLOCALIZACION -->
        </div>

        <!--Accesos rápidos: tarjetas celestes compactas-->
        <div class="accesos-rapidos-paciente mb-3">
            <a href="{{ route('paciente.acceso_pni') }}" class="acceso-rapido-tarjeta">
                <span class="acceso-rapido-icono"><i class="feather icon-user-plus"></i></span>
                <span class="acceso-rapido-texto">
                    <strong>Atención por profesional no registrado</strong>
                    <small>Ingresa el RUT y correo del profesional</small>
                </span>
            </a>
            <a href="{{ route('paciente.mascotas_perdidas') }}" class="acceso-rapido-tarjeta">
                <span class="acceso-rapido-icono"><i class="feather icon-search"></i></span>
                <span class="acceso-rapido-texto">
                    <strong>Mascotas perdidas</strong>
                    <small>Reporta o busca mascotas extraviadas</small>
                </span>
            </a>
            <a href="{{ route('paciente.referidos.index') }}" class="acceso-rapido-tarjeta">
                <span class="acceso-rapido-icono"><img src="{{ asset('images/iconos/referidos.svg') }}" alt=""></span>
                <span class="acceso-rapido-texto">
                    <strong>Invita y gana con Veterchile</strong>
                    <small>Comparte tu código con otros tutores</small>
                </span>
            </a>
            <a href="{{ route('app.descarga') }}" class="acceso-rapido-tarjeta" target="_blank">
                <span class="acceso-rapido-icono"><img src="{{ asset('images/iconos/descargar-app.svg') }}" alt=""></span>
                <span class="acceso-rapido-texto">
                    <strong>Descarga VetPass</strong>
                    <small>Lleva la ficha de tu mascota en el celular</small>
                </span>
            </a>
        </div>
        <!--Cierre: Accesos rápidos-->
    </div>
</div>
@endsection

@section('modales')
    @php
        // Datos que necesitan los pasos de la reserva de hora veterinaria dentro del modal
        $datosAgendaCita = [
            'es_veterinaria_busqueda' => true,
            'con_paso_mascota' => true,
            'mascotas' => $mascotasReservaCita ?? collect(),
            'regiones' => $regionesReservaCita ?? collect(),
            'ciudades' => $ciudadesReservaCita ?? collect(),
            'catalogo_veterinario_busqueda' => $catalogoReservaCita ?? [],
            'catalogo_videoconsulta_busqueda' => $catalogoVideoconsultaCita ?? [],
            'filtros' => ['id_profesion' => 0, 'id_especialidad' => 0, 'id_subespecialidad' => 0],
        ];
    @endphp

    <!--Agendar hora: los mismos pasos de "Reservar hora veterinaria", partiendo por la mascota-->
    <div class="modal fade" id="modal_agenda_cita_tutor" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modal_agenda_cita_tutor_titulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header modal-header-reserva">
                    <h5 class="modal-title" id="modal_agenda_cita_tutor_titulo"><span class="modal-header-reserva-icono"><i class="feather icon-calendar"></i></span>Reservar hora</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="vista_reserva_veterinaria">
                        @include('app.general.buscador_profesionales.partials.pasos_reserva', $datosAgendaCita)
                    </div>
                </div>
                <!--Los botones del paso que se está viendo se traen acá, así siempre quedan en el pie-->
                <div class="modal-footer reserva-vet-pie" id="reserva_hora_pie"></div>
            </div>
        </div>
    </div>

    @include('app.general.buscador_profesionales.modals.ficha_profesional')
@endsection

@section('page-styles')
    <style>
        /* Accesos rápidos: cuatro tarjetas celestes en una fila, dos en tablet y una en celular */
        .accesos-rapidos-paciente {
            display: -ms-grid;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .acceso-rapido-tarjeta {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            gap: 12px;
            min-height: 84px;
            padding: 14px 16px;
            border-radius: 10px;
            background-color: #0fb9b9;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            -webkit-transition: opacity .2s ease, -webkit-transform .2s ease;
            transition: opacity .2s ease, transform .2s ease;
        }

        .acceso-rapido-tarjeta:hover,
        .acceso-rapido-tarjeta:focus {
            color: #fff;
            text-decoration: none;
            opacity: .9;
            -webkit-transform: translateY(-2px);
            transform: translateY(-2px);
        }

        .acceso-rapido-icono {
            -ms-flex-negative: 0;
            flex-shrink: 0;
            display: -webkit-inline-box;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.18);
            font-size: 1.3rem;
        }

        .acceso-rapido-icono img {
            width: 26px;
            height: 26px;
        }

        .acceso-rapido-texto {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -ms-flex-direction: column;
            flex-direction: column;
            min-width: 0;
            line-height: 1.25;
        }

        .acceso-rapido-texto strong {
            font-family: 'Nunito', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .acceso-rapido-texto small {
            margin-top: 2px;
            font-size: 0.75rem;
            opacity: .9;
        }

        @media (max-width: 1199px) {
            .accesos-rapidos-paciente {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575px) {
            .accesos-rapidos-paciente {
                grid-template-columns: minmax(0, 1fr);
                gap: 12px;
            }
        }

        .estado-hora-chip {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 600;
            line-height: 1.1;
            text-align: center;
            white-space: normal;
        }

        .estado-hora-chip.cancelada {
            background-color: #ff3d4f;
        }

        .estado-hora-chip.pendiente {
            background-color: #ffb640;
        }

        .estado-hora-chip.confirmada {
            background-color: #6ac000;
        }

        .estado-hora-chip.en-proceso {
            background-color: #7d4bc4;
        }

        .estado-hora-chip.realizada {
            background-color: #17c1c1;
        }

        .estado-hora-chip.inasistida {
            background-color: #6c757d;
        }

        .estado-hora-chip.desconocida {
            background-color: #4a5568;
        }

        /* Mis próximas citas: el botón "Agendar hora" a la derecha del título */
        .card .card-header.encabezado-citas {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
            gap: 10px;
        }

        .card .card-header.encabezado-citas .btn {
            margin: 0;
        }

        /* Modal "Agendar hora": mismo cuerpo que el modal de reserva veterinaria */
        #modal_agenda_cita_tutor .modal-body {
            padding: 20px 24px 24px;
            background: #fcfcfe;
        }

        /* Todo el modal con las esquinas redondeadas, también abajo */
        #modal_agenda_cita_tutor .modal-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
        }

        /* Pie: volver a la izquierda, avanzar a la derecha */
        #modal_agenda_cita_tutor .modal-footer.reserva-vet-pie {
            display: block;
            padding: 14px 24px;
            background: #fff;
            border-top: 1px solid #edf0f5;
        }

        #modal_agenda_cita_tutor .reserva-vet-pie .reserva-vet-acciones {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: end;
            -ms-flex-pack: end;
            justify-content: flex-end;
            gap: 10px;
            margin: 0;
            padding: 0;
            border: 0;
        }

        /* Elección de mascota: el select y, al lado, la tarjeta para una mascota nueva */
        #modal_agenda_cita_tutor .reserva-vet-mascota-eleccion {
            display: -ms-grid;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            gap: 14px;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        #modal_agenda_cita_tutor .reserva-vet-mascota-select.d-none ~ .reserva-vet-mascota-nueva-boton {
            grid-column: 1 / -1;
        }

        #modal_agenda_cita_tutor .reserva-vet-mascota-o {
            color: #9aa1b2;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        #modal_agenda_cita_tutor .reserva-vet-mascota-nueva-boton {
            border-style: dashed;
            border-color: #cdbdf5;
            background: #fbf9ff;
        }

        #modal_agenda_cita_tutor .reserva-vet-mascota-nueva-boton.active {
            border-style: solid;
        }

        #modal_agenda_cita_tutor .reserva-vet-mascota-nueva {
            margin-top: 18px;
            padding: 18px 18px 4px;
            border: 1px solid #edf0f5;
            border-radius: 14px;
            background: #fff;
        }

        /* Título "Nueva mascota": el .titulo-item de siempre, con la franja más fina y más aire abajo */
        #modal_agenda_cita_tutor .reserva-vet-mascota-nueva .titulo-item {
            margin-bottom: 22px;
            border-bottom-width: 1px;
        }

        @include('app.general.buscador_profesionales.partials.reserva_tutor_estilos', ['ambito' => '#modal_agenda_cita_tutor'])

        @media (max-width: 767.98px) {
            #modal_agenda_cita_tutor .reserva-vet-mascota-eleccion {
                grid-template-columns: minmax(0, 1fr);
                gap: 10px;
            }

            #modal_agenda_cita_tutor .reserva-vet-mascota-o {
                text-align: center;
            }
        }

        /* Cabecera blanca con el título grande, como el encabezado general de card */
        #modal_agenda_cita_tutor .modal-header-reserva {
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            padding: 18px 24px;
            background: #fff;
            filter: none;
            border-bottom: 1px solid #edf0f5;
        }

        #modal_agenda_cita_tutor .modal-header-reserva .modal-title,
        #modal_agenda_cita_tutor .modal-header-reserva h5 {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            margin: 0;
            color: #272727 !important;
            font-family: 'Nunito', 'Nunito Sans', sans-serif;
            font-size: 24px !important;
            font-weight: 800;
            line-height: 1.2;
        }

        #modal_agenda_cita_tutor .modal-header-reserva-icono {
            display: -webkit-inline-box;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            -ms-flex-negative: 0;
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            margin-right: 10px;
            color: #fff;
            background-color: #6f42c1;
            border-radius: 10px;
            font-size: 19px;
        }

        #modal_agenda_cita_tutor .modal-header-reserva .close {
            margin: 0 -8px 0 auto !important;
            background-color: #f3f4f8 !important;
            color: #6c757d !important;
        }

        #modal_agenda_cita_tutor .modal-header-reserva .close:hover {
            background-color: #e7e9f0 !important;
            color: #272727 !important;
        }

        @media (max-width: 575.98px) {
            #modal_agenda_cita_tutor .modal-body {
                padding: 16px 12px;
            }

            #modal_agenda_cita_tutor .modal-footer.reserva-vet-pie {
                padding: 12px;
            }
        }
    </style>
@endsection

@section('page-script')
    <!--Calendario de la reserva: el modal "Agendar hora" lo usa en línea, como la página de reserva-->
    <script src="{{ asset('js/flatpickr/flatpickr.min.js') }}"></script>
    <script>
        // En este modal las tarjetas de veterinario muestran el valor del servicio elegido
        window.mostrarValoresReserva = true;
    </script>
    @include('template.partials.buscador_reserva_script', $datosAgendaCita)
    <script>
        @include('app.general.buscador_profesionales.partials.pasos_reserva_script', $datosAgendaCita)

        // Especies del registro rápido de mascota, para pedir las razas de canino y felino
        window.especiesMascotaReserva = {!! json_encode(\App\Models\EspecieMascota::whereIn('slug', ['canina', 'felina'])->pluck('id', 'slug')) !!};

        // Botón "Agendar hora" de Mis próximas citas

        // Las alertas de la reserva llevan el botón turquesa; la marca se quita cuando ya no hay
        // modal ni alerta abiertos, así las demás alertas del escritorio quedan como estaban
        function quitarAlertasReservaHora() {
            setTimeout(function () {
                if (!$('#modal_agenda_cita_tutor').hasClass('show') && !$('.swal-overlay--show-modal').length) {
                    $('body').removeClass('reserva-hora-alertas');
                }
            }, 400);
        }
        $(document).on('hidden.bs.modal', '#modal_agenda_cita_tutor', quitarAlertasReservaHora);
        $(document).on('click', '.swal-button, .swal-overlay', quitarAlertasReservaHora);

        function abrirModalAgendaCita() {
            $('body').addClass('reserva-hora-alertas');
            reiniciarReservaHora();
            if (typeof window.reiniciarServiciosReserva === 'function') {
                window.reiniciarServiciosReserva();
            }
            $('#modal_agenda_cita_tutor').modal('show');
        }

        // "Ver ficha": se cierra el modal de la reserva y se abre la ficha en su propio modal.
        // Al cerrar la ficha, la reserva vuelve a abrirse tal como estaba (mismo paso y misma posición).
        (function () {
            const $reserva = $('#modal_agenda_cita_tutor');
            const $ficha = $('#ficha_profesional');
            let volverAReserva = false;
            let posicion = 0;

            function errorFicha() {
                swal({ title: 'Información del profesional', text: 'No pudimos cargar la ficha del profesional.', icon: 'error', buttons: 'OK' });
            }

            window.f_profesional = function (idProfesional) {
                $.get(@json(route('profesional.informacionProfesional')), { id_profesional: idProfesional })
                    .done(function (data) {
                        if (!data || data.estado != 1) {
                            errorFicha();
                            return;
                        }
                        pintarFichaProfesional(data);

                        if (!$reserva.hasClass('show')) {
                            $ficha.modal('show');
                            return;
                        }
                        // Primero se cierra la reserva y, ya cerrada, se abre la ficha (así no quedan dos fondos)
                        volverAReserva = true;
                        posicion = $reserva.scrollTop();
                        $reserva.one('hidden.bs.modal', function () {
                            $ficha.modal('show');
                        }).modal('hide');
                    })
                    .fail(errorFicha);
            };

            $ficha.on('hidden.bs.modal', function () {
                if (!volverAReserva) {
                    return;
                }
                volverAReserva = false;
                $('body').addClass('reserva-hora-alertas');
                $reserva.one('shown.bs.modal', function () {
                    $reserva.scrollTop(posicion);
                }).modal('show');
            });
        })();

        @include('app.general.buscador_profesionales.partials.reserva_tutor_script', ['ambito' => '#modal_agenda_cita_tutor'])

        // La hora quedó agendada: se cierra el modal y la cita aparece en la tabla
        window.reservaHoraAgendada = function () {
            $('#modal_agenda_cita_tutor').modal('hide');
            if (typeof cargar_horas_medicas === 'function') {
                cargar_horas_medicas();
            }
        };
    </script>
    {{-- Peluquería, hotel y cementerio: va después de los pasos porque usa sus funciones --}}
    @include('app.paciente.partials.reserva_servicios_tutor_script')
    <script>
        function obtenerClaseEstadoHora(idEstado)
        {
            switch (parseInt(idEstado, 10))
            {
                case 1:
                case 8:
                case 16:
                    return 'pendiente';

                case 2:
                    return 'confirmada';

                case 3:
                    return 'cancelada';

                case 4:
                case 5:
                    return 'en-proceso';

                case 6:
                    return 'realizada';

                case 7:
                    return 'inasistida';

                default:
                    return 'desconocida';
            }
        }

        function confirmar(id)
        {
            $('.btn-confirmar-hora,.btn-anular-hora').filter(':enabled').attr('data-agenda-reintentar','1');
            $('.btn-confirmar-hora').attr('disabled', true);
            $('.btn-anular-hora').attr('disabled', true);

            let url = "{{ route('paciente.hora.medica.confirmar') }}";

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    id_hora: id,
                    _token: CSRF_TOKEN,
                },
                error: function(xhr){
                    swal({title:'No se pudo completar la acción',text:xhr.responseJSON?.message||'Actualiza la agenda e inténtalo nuevamente.',icon:'error'});
                    cargar_horas_medicas();
                },
                complete: function(){
                     $('[data-agenda-reintentar]').prop('disabled',false).removeAttr('data-agenda-reintentar');
                },
                success: function(data)
                {
                    if (data != null && parseInt(data.estado, 10) === 1)
                    {
                        swal({
                            title: "Exito!",
                            text: "Se ha confirmado su hora médica",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                    else
                    {
                        swal({
                            title: "Error!",
                            text: "Se ha presentado un problema en la confirmación de su hora médica.\n Intente de nuevo.",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                }
            });
        }

        function anular(id)
        {
            $('.btn-confirmar-hora,.btn-anular-hora').filter(':enabled').attr('data-agenda-reintentar','1');
            $('.btn-confirmar-hora').attr('disabled', true);
            $('.btn-anular-hora').attr('disabled', true);

            let url = "{{ route('paciente.hora.medica.cancelar') }}";

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    id_hora: id,
                    _token: CSRF_TOKEN,
                },
                error: function(xhr){
                    swal({title:'No se pudo completar la acción',text:xhr.responseJSON?.message||'Actualiza la agenda e inténtalo nuevamente.',icon:'error'});
                    cargar_horas_medicas();
                },
                complete: function(){
                     $('[data-agenda-reintentar]').prop('disabled',false).removeAttr('data-agenda-reintentar');
                },
                success: function(data)
                {
                    if (data != null && parseInt(data.estado, 10) === 1)
                    {
                        swal({
                            title: "Exito!",
                            text: "Se ha cancelado su hora médica",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                    else
                    {
                        swal({
                            title: "Error!",
                            text: "Se ha presentado un problema en la cancelación de su hora médica.\n Intente de nuevo.",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                }
            });
        }

        let cargandoHorasMedicas = false;

        function cargar_horas_medicas()
        {
            if (cargandoHorasMedicas) return;
            cargandoHorasMedicas = true;
            let url = "{{ route('paciente.hora.medica.ver') }}";
            let tabla = null;

            if ($.fn.DataTable.isDataTable('#simpletable')) {
                tabla = $('#simpletable').DataTable();
            }

            $.ajax({
                url: url,
                type: "GET",
                cache: false,
                timeout: 10000,
                complete: function() { cargandoHorasMedicas = false; },
                data: {},
                success: function(data) {
                    if(data != null)
                    {
                        if (data.estado == 1)
                        {
                            if (tabla) tabla.clear();
                            else $('#simpletable tbody').empty();
                            if(data.registros.length > 0)
                            {
                                $.each(data.registros, function (key, value) {
                                    var html = '';
                                    var acciones = '';
                                    const estadoVisual = parseInt(value.id_estado_visual ?? value.id_estado, 10);
                                    switch(estadoVisual)
                                    {
                                        case 1:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" onclick="confirmar('+value.id+');">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora" onclick="anular('+value.id+');">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                            break

                                        case 16:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" onclick="confirmar('+value.id+');">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora" onclick="anular('+value.id+');">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                            break

                                        case 2:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" disabled="disabled">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora"  onclick="anular('+value.id+');">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                            break

                                        default:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" disabled="disabled">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora" disabled="disabled">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                    }

                                    var mascotaHtml = '<strong>' + (value.nombre_mascota || 'Sin mascota') + '</strong>';
                                    if (value.nombre_especie_mascota) {
                                        mascotaHtml += '<br>' + value.nombre_especie_mascota;
                                    }

                                    var profesionalHtml = (value.nombre_profesional_completo || '') + '<br>' + (value.nombre_especialidad_resumen || '');
                                    var diaHoraFormato = moment(value.fecha_consulta+' '+value.hora_inicio).format('DD-MM-YYYY HH:mm');
                                    var atencionHtml = (value.nombre_lugar_atencion || '') + '<br>' + (value.direccion_lugar_atencion || '') + '<br><span style="font-weight:bold;">'+diaHoraFormato+' hrs</span>';
                                    var estadoHtml = '<span class="estado-hora-chip '+obtenerClaseEstadoHora(estadoVisual)+'">'+(value.texto_estado || '')+'</span>';

                                    if (tabla) {
                                        tabla.row.add([
                                            acciones,
                                            mascotaHtml,
                                            profesionalHtml,
                                            atencionHtml,
                                            estadoHtml
                                        ]);
                                    } else {
                                        html += '<tr>';
                                        html += '    <td class="align-middle">'+acciones+'</td>';
                                        html += '    <td class="align-middle">'+mascotaHtml+'</td>';
                                        html += '    <td>'+profesionalHtml+'</td>';
                                        html += '    <td>'+atencionHtml+'</td>';
                                        html += '    <td class="align-middle">'+estadoHtml+'</td>';
                                        html += '</tr>';
                                        $('#simpletable tbody').append(html);
                                    }
                                });

                            } else if (!tabla) {
                                $('#simpletable tbody').append('<tr><td colspan="5" class="text-center">No existen registros</td></tr>');
                            }
                            if (tabla) tabla.draw(false);
                        }
                    }
                }
            });
        }

        // Nombre visible de cada tienda que responde el endpoint de vouchers
        const nombresTiendasBeneficios = { veterfarma: 'Veterfarma', alimentos: 'Alimentos' };

        function tiendasBeneficiosCaidas(data) {
            return Object.keys(data.fuentes || {})
                .filter(function(fuente) { return !data.fuentes[fuente]; })
                .map(function(fuente) { return nombresTiendasBeneficios[fuente] || fuente; });
        }

        // ["Veterfarma", "Alimentos"] -> "Veterfarma y Alimentos"
        function unirNombresTiendas(nombres) {
            return nombres.length > 1
                ? nombres.slice(0, -1).join(', ') + ' y ' + nombres[nombres.length - 1]
                : nombres.join('');
        }

        // "2026-10-31" -> "31 oct" (con año solo si no es el actual)
        function formatearFechaVoucher(fecha) {
            const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            const partes = String(fecha).slice(0, 10).split('-');
            const mes = meses[parseInt(partes[1], 10) - 1];
            if (partes.length !== 3 || !mes) return String(fecha);

            const anio = parseInt(partes[0], 10) !== new Date().getFullYear() ? ' ' + partes[0] : '';
            return parseInt(partes[2], 10) + ' ' + mes + anio;
        }

        function estadoBeneficios(icono, titulo, detalle, tipo, conReintento) {
            const textos = $('<div>').append(
                $('<strong>', {text: titulo}),
                $('<small>', {text: detalle})
            );

            if (conReintento) {
                textos.append($('<button>', {type: 'button', class: 'beneficios-mascota-reintentar', text: 'Reintentar'}));
            }

            return $('<div>', {class: 'beneficios-mascota-estado ' + (tipo || '')}).append(
                $('<span>', {class: 'beneficios-mascota-estado-icono'}).append($('<i>', {class: 'feather ' + icono})),
                textos
            );
        }

        function cuponVoucher(voucher) {
            const valor = voucher.discount_type === 'percent'
                ? voucher.value + '%'
                : '$' + Number(voucher.value).toLocaleString('es-CL');
            const destino = voucher.scope === 'product' && voucher.product
                ? 'En ' + voucher.product.name
                : voucher.scope === 'client' ? 'Beneficio exclusivo para ti' : 'En toda tu compra';
            const vence = voucher.expires_at ? 'Vence ' + formatearFechaVoucher(voucher.expires_at) : 'Sin vencimiento';

            const acciones = $('<div>', {class: 'cupon-beneficio-fila'});
            if (voucher.code) {
                acciones.append(
                    $('<button>', {type: 'button', class: 'cupon-beneficio-codigo', title: 'Copiar código', 'data-codigo': voucher.code}).append(
                        $('<span>', {text: voucher.code}),
                        $('<i>', {class: 'feather icon-copy'})
                    )
                );
            }
            acciones.append(
                $('<a>', {class: 'cupon-beneficio-usar', href: voucher.store_url || '#', target: '_blank', rel: 'noopener', text: 'Usar'})
                    .append($('<i>', {class: 'feather icon-arrow-right'}))
            );

            return $('<article>', {class: 'cupon-beneficio ' + (voucher.provider === 'alimentos' ? 'alimentos' : 'veterfarma')}).append(
                $('<div>', {class: 'cupon-beneficio-valor'}).append(
                    $('<strong>', {text: valor, class: valor.length > 7 ? 'largo' : ''}),
                    $('<small>', {text: 'dcto.'})
                ),
                $('<div>', {class: 'cupon-beneficio-detalle'}).append(
                    $('<div>', {class: 'cupon-beneficio-fila'}).append(
                        $('<span>', {class: 'cupon-beneficio-tienda', text: voucher.provider_name || 'Beneficio'}),
                        $('<span>', {class: 'cupon-beneficio-vence', text: vence})
                    ),
                    $('<p>', {class: 'cupon-beneficio-destino', text: destino, title: destino}),
                    acciones
                )
            );
        }

        function copiarCodigoVoucher(boton) {
            const codigo = String(boton.attr('data-codigo') || '');
            const avisar = function() {
                boton.addClass('copiado').attr('title', 'Código copiado').find('i').attr('class', 'feather icon-check');
                setTimeout(function() {
                    boton.removeClass('copiado').attr('title', 'Copiar código').find('i').attr('class', 'feather icon-copy');
                }, 1800);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(codigo).then(avisar);
                return;
            }

            // Respaldo para sitios en http o navegadores antiguos
            const campo = $('<textarea>', {readonly: true, css: {position: 'fixed', top: 0, left: '-9999px'}}).val(codigo).appendTo('body');
            campo[0].select();
            try {
                if (document.execCommand('copy')) avisar();
            } catch (e) {}
            campo.remove();
        }

        function cargarVouchersVeterfarma() {
            const contenedor = $('#vouchers-veterfarma-list');
            const contador = $('#vouchers-veterfarma-contador').addClass('d-none');
            const recargar = $('#vouchers-veterfarma-recargar').prop('disabled', true).addClass('girando');

            contenedor.html('<div class="beneficios-mascota-cargando" aria-label="Consultando beneficios"><span></span><span></span></div>');

            $.get(@json(route('paciente.vouchers_veterfarma'))).done(function(data) {
                const vouchers = Array.isArray(data.vouchers) ? data.vouchers : [];
                const caidas = tiendasBeneficiosCaidas(data);
                contenedor.empty();
                Object.values(data.tiendas || {}).forEach(function(tienda) {
                    const estado = !tienda.conectado ? 'Sin conexión' : (tienda.cantidad > 0 ? tienda.cantidad + ' beneficio(s) disponible(s)' : 'Sin beneficios vigentes');
                    contenedor.append($('<div>', {class: 'small mb-2'}).append(
                        $('<strong>', {text: tienda.nombre + ': '}),
                        $('<span>', {class: tienda.conectado ? 'text-success' : 'text-muted', text: estado})
                    ));
                });

                if (!vouchers.length) {
                    contenedor.append(caidas.length
                        ? estadoBeneficios('icon-wifi-off', 'No pudimos consultar ' + unirNombresTiendas(caidas) + '.', 'Intenta de nuevo en unos minutos.', 'aviso', true)
                        : estadoBeneficios('icon-gift', 'Sin beneficios vigentes por ahora.', 'Cuando tengas uno nuevo aparecerá aquí.'));
                    return;
                }

                contador
                    .text(vouchers.length)
                    .attr('title', vouchers.length === 1 ? '1 voucher disponible' : vouchers.length + ' vouchers disponibles')
                    .removeClass('d-none');

                const lista = $('<div>', {class: 'beneficios-mascota-lista'});
                vouchers.forEach(function(voucher) {
                    lista.append(cuponVoucher(voucher));
                });
                contenedor.append(lista);

                if (caidas.length) {
                    contenedor.append($('<small>', {class: 'beneficios-mascota-nota', text: 'No se pudo consultar ' + unirNombresTiendas(caidas) + ' por ahora.'}));
                }
            }).fail(function() {
                contenedor.empty().append(
                    estadoBeneficios('icon-alert-circle', 'No fue posible consultar los beneficios.', 'Revisa tu conexión e intenta otra vez.', 'error', true)
                );
            }).always(function() {
                recargar.prop('disabled', false).removeClass('girando');
            });
        }

        $(document).ready(function() {
            cargar_horas_medicas();
            setInterval(function() {
                if (!document.hidden) cargar_horas_medicas();
            }, 15000);
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) cargar_horas_medicas();
            });
            cargarVouchersVeterfarma();

            $(document).on('click', '#vouchers-veterfarma-recargar, .beneficios-mascota-reintentar', cargarVouchersVeterfarma);
            $(document).on('click', '.cupon-beneficio-codigo', function() {
                copiarCodigoVoucher($(this));
            });
        });
    </script>
@endsection
