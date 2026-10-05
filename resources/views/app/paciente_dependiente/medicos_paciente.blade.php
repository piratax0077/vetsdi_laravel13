@extends('template.paciente_dependiente.template')
@section('content')
<!--Container Completo-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="row">
            <div class="col-12">
                <h6 class="font-weight-bold f-26">Mis Veterinarios</h6>
                <p>Registro de profesionales que han atendido a {{ \Illuminate\Support\Str::ucfirst(trim($paciente->nombre ?? '')) }}</p>
            </div>
        </div>
        <!--Cierre: Header-->
        <div class="row">
            <div class="col-md-12">
                <!--Pestañas por especialidad: todas muestran el mismo panel y filtran las tarjetas-->
                <ul class="nav nav-general mt-3" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation" onclick="active_e('all')">
                        <a class="nav-link active" id="ver_todos-tab" data-toggle="tab" href="#user2" role="tab" aria-controls="user2" aria-selected="true">Ver todos</a>
                    </li>
                @foreach( $lista_especialidad as $le)
                    <li class="nav-item" role="presentation" onclick="active_e('{{$le}}')">
                        <a class="nav-link" id="especialidad_{{ $loop->index }}-tab" data-toggle="tab" href="#user2" role="tab" aria-controls="user2" aria-selected="false">{{$le}}</a>
                    </li>
                @endforeach
                </ul>
                <!--Cierre: Pestañas por especialidad-->
                <div class="tab-content mt-4" id="myTabContent">
                    <!--Listado de veterinarios-->
                    <div class="tab-pane fade active show" id="user2" role="tabpanel" aria-labelledby="ver_todos-tab">
                        <div class="row mb-n4">
                            @if(isset($profesional))
                                @foreach( $profesional as $p)
                                    @if(in_array($p->id, $desvinculados)==false)
                                    @php
                                        $especialidad_profesional = optional($p->Especialidad()->first())->nombre ?? 'Veterinario';
                                        $nombre_profesional = trim(collect([$p->nombre, $p->apellido_uno, $p->apellido_dos])->filter()->implode(' '));
                                        $rut_profesional = explode('-', (string) $p->rut)[0] ?? null;
                                        $img_profesional = ($rut_profesional && file_exists(public_path('images/img_perfil/'.$rut_profesional.'.png')))
                                            ? asset('images/img_perfil/'.$rut_profesional.'.png')
                                            : asset('images/iconos/usuario_profesional.svg');
                                    @endphp
                                    <!--Card Tomar Hora Perfil Médico -->
                                    <div class="col-md-4 filtro_le le_{{ $especialidad_profesional }}">
                                        <div class="card user-card user-card-1 mt-4">
                                            <div class="card-body pt-0">
                                                <div class="user-about-block text-center">
                                                    <div class="row align-items-end">
                                                        <div class="col"></div>
                                                        <div class="col">
                                                            <div class="position-relative d-inline-block">
                                                                <img class="img-radius img-fluid wid-80" src="{{ $img_profesional }}" alt="Mis médicos">
                                                            </div>
                                                        </div>
                                                        <div class="col text-right pb-3">
                                                            <div class="dropdown" style="cursor:pointer">
                                                                <a class="drp-icon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <i class="feather icon-more-horizontal" data-toggle="tooltip" data-placement="top" title="Opciones"></i>
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <a class="dropdown-item" href='{{ url("Paciente/dependiente/desvincular_profesional/{$paciente->id}/{$paciente->id}/{$p->id}") }}'>Desvincular Veterinario</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <a href="#!" data-toggle="modal" data-target="#modal-report">
                                                        <span class="badge badge-purple mt-2">{{ $especialidad_profesional }}</span>
                                                        <h5 class="mb-1 mt-2">{{ $nombre_profesional }}</h5>
                                                    </a>
                                                    <p class="mb-3 text-muted">
                                                        <!--<i class="feather icon-calendar"></i></p>-->
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-info"
                                                        data-profesional-id="{{ $p->id }}"
                                                        data-profesional-nombre="{{ $nombre_profesional }}"
                                                        data-profesional-especialidad="{{ $especialidad_profesional }}"
                                                        data-profesional-img="{{ $img_profesional }}"
                                                        onclick="abrirModalReservaVeterinaria(this);">
                                                        <i class="feather icon-calendar"></i> Agendar Hora
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-info mt-2"
                                                        onclick="abrirFichaProfesionalMascota({{ $p->id }});">
                                                        <i class="feather icon-user"></i> Ver información profesional
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--CIERRE: Card Tomar Hora Perfil Médico -->
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <!--Cierre: Pills Medicina General-->
                </div>
                <!--Cierre: Pills-->
            </div>
        </div>
    </div>
</div>
<!--Cierre: Container Completo-->

@include('app.general.buscador_profesionales.modals.ficha_profesional')

<div class="modal fade" id="modal_reserva_veterinaria" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modal_reserva_veterinaria_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="modal_reserva_veterinaria_label">Agendar Hora Veterinaria</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="reserva_vet_profesional_id" value="">
                <input type="hidden" id="reserva_vet_lugar_id" value="">
                <input type="hidden" id="reserva_vet_fecha" value="">
                <input type="hidden" id="reserva_vet_hora" value="">

                <!--Veterinario al que se le pide la hora-->
                <div class="reserva-vet-profesional">
                    <img class="reserva-vet-profesional-icono" id="reserva_vet_profesional_img" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="Foto del veterinario">
                    <div class="reserva-vet-profesional-datos">
                        <span class="reserva-vet-profesional-etiqueta">Agendando con</span>
                        <span class="reserva-vet-profesional-nombre" id="reserva_vet_profesional_nombre">-</span>
                    </div>
                    <span class="reserva-vet-profesional-especialidad" id="reserva_vet_profesional_especialidad"></span>
                </div>

                <!--Indicador de pasos-->
                <ol class="reserva-vet-pasos" id="reserva_vet_pasos">
                    <li class="reserva-vet-paso activo" data-paso="1">
                        <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaVeterinaria(1);">
                            <span class="reserva-vet-paso-numero">1</span>
                            <span class="reserva-vet-paso-texto">Lugar</span>
                        </button>
                    </li>
                    <li class="reserva-vet-paso" data-paso="2">
                        <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaVeterinaria(2);" disabled>
                            <span class="reserva-vet-paso-numero">2</span>
                            <span class="reserva-vet-paso-texto">Fecha y hora</span>
                        </button>
                    </li>
                    <li class="reserva-vet-paso" data-paso="3">
                        <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaVeterinaria(3);" disabled>
                            <span class="reserva-vet-paso-numero">3</span>
                            <span class="reserva-vet-paso-texto">Confirmar</span>
                        </button>
                    </li>
                </ol>

                <!--Paso 1: lugar de atención-->
                <div class="reserva-vet-panel activo" data-paso="1">
                    <div class="reserva-vet-panel-encabezado">
                        <h6>¿Dónde quiere la atención?</h6>
                        <p class="small text-muted mb-0">Elija uno de los lugares donde atiende este veterinario.</p>
                    </div>

                    <!--Cada lugar es una tarjeta que se marca con un clic; se llenan al abrir el modal-->
                    <div class="reserva-vet-lugares" id="reserva_vet_lugares" role="radiogroup" aria-label="Lugar de atención"></div>
                    <div class="reserva-vet-aviso d-none" id="reserva_vet_lugar_aviso"></div>

                    <!--Contacto y horario del lugar elegido, solo informativos-->
                    <div class="reserva-vet-lugar-vacio" id="reserva_vet_lugar_vacio">
                        <i class="feather icon-clock"></i>
                        <span>Al elegir un lugar verá aquí sus días y horario de atención.</span>
                    </div>
                    <div class="reserva-vet-lugar-detalle d-none" id="reserva_vet_lugar_info">
                        <div class="reserva-vet-lugar-contacto" id="reserva_vet_lugar_contacto">
                            <a class="reserva-vet-contacto d-none" href="#" id="reserva_vet_telefono">
                                <i class="feather icon-phone" aria-hidden="true"></i>
                                <span id="reserva_vet_telefono_texto">-</span>
                            </a>
                            <a class="reserva-vet-contacto d-none" href="#" id="reserva_vet_email">
                                <i class="feather icon-mail" aria-hidden="true"></i>
                                <span id="reserva_vet_email_texto">-</span>
                            </a>
                        </div>
                        <span class="reserva-vet-etiqueta">Días y horario de atención</span>
                        <div class="reserva-vet-horarios" id="reserva_vet_dias_horario"></div>
                    </div>
                </div>

                <!--Paso 2: fecha y hora juntas-->
                <div class="reserva-vet-panel" data-paso="2">
                    <div class="reserva-vet-panel-encabezado">
                        <h6>¿Qué día y a qué hora?</h6>
                        <p class="small text-muted mb-0">Los días marcados tienen atención en los próximos 60 días.</p>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <!--flatpickr cambia el input a texto, por eso se oculta con d-none y se muestra solo el calendario-->
                            <div class="reserva-vet-calendario">
                                <input type="text" class="d-none" id="reserva_vet_fecha_input" value="" tabindex="-1" aria-hidden="true">
                            </div>
                            <div class="reserva-vet-leyenda" aria-hidden="true">
                                <span><i class="reserva-vet-leyenda-disponible"></i>Disponible</span>
                                <span><i class="reserva-vet-leyenda-elegido"></i>Elegido</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="reserva-vet-horas-caja" id="reserva_vet_horas_caja">
                                <div class="reserva-vet-horas-cabecera">
                                    <div class="reserva-vet-horas-titulo">Horas disponibles</div>
                                    <span class="reserva-vet-horas-total d-none" id="reserva_vet_horas_total"></span>
                                </div>
                                <div class="reserva-vet-horas-fecha" id="reserva_vet_fecha_texto">Primero elija una fecha.</div>
                                <div class="reserva-vet-horas" id="reserva_vet_horas" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Paso 3: resumen antes de confirmar-->
                <div class="reserva-vet-panel" data-paso="3">
                    <div class="reserva-vet-panel-encabezado">
                        <h6>Revise su reserva</h6>
                        <p class="small text-muted mb-0">Si todo está correcto, confirme para agendar la hora.</p>
                    </div>
                    <div class="reserva-vet-resumen">
                        <!--Lo más importante primero: cuándo es la hora-->
                        <div class="reserva-vet-resumen-cita">
                            <span class="reserva-vet-resumen-cita-icono"><i class="feather icon-calendar"></i></span>
                            <div class="reserva-vet-resumen-cita-datos">
                                <span class="reserva-vet-resumen-fecha" id="reserva_vet_resumen_fecha">-</span>
                                <span class="reserva-vet-resumen-hora">
                                    <i class="feather icon-clock"></i>
                                    <span id="reserva_vet_resumen_hora">-</span>
                                </span>
                            </div>
                        </div>
                        <ul class="reserva-vet-detalle">
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="fas fa-paw"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">Mascota</span>
                                    <span class="reserva-vet-detalle-valor">{{ \Illuminate\Support\Str::ucfirst(trim($paciente->nombre ?? '')) ?: '-' }}</span>
                                </div>
                            </li>
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="feather icon-user"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">Veterinario</span>
                                    <span class="reserva-vet-detalle-valor" id="reserva_vet_resumen_profesional">-</span>
                                </div>
                            </li>
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="feather icon-map-pin"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">Lugar de atención</span>
                                    <span class="reserva-vet-detalle-valor" id="reserva_vet_resumen_lugar">-</span>
                                    <span class="reserva-vet-detalle-extra" id="reserva_vet_resumen_direccion"></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer reserva-vet-acciones">
                <button type="button" class="btn btn-outline-dark btn-sm reserva-vet-volver" id="btn_cancelar_reserva_vet" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-outline-secondary reserva-vet-volver d-none" id="btn_anterior_reserva_vet" onclick="anteriorPasoReservaVeterinaria();">
                    <i class="feather icon-chevron-left"></i> Anterior
                </button>
                <button type="button" class="btn btn-sm btn-info" id="btn_siguiente_reserva_vet" onclick="siguientePasoReservaVeterinaria();" disabled>
                    Siguiente <i class="feather icon-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-sm btn-info d-none" id="btn_confirmar_reserva_vet" onclick="confirmarReservaVeterinaria();" disabled>
                    <i class="feather icon-check"></i> Confirmar Reserva
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
    <script>
        function abrirFichaProfesionalMascota(idProfesional) {
            $.ajax({
                url: "{{ route('profesional.informacionProfesional') }}",
                type: 'GET',
                data: {
                    id_profesional: idProfesional,
                },
            }).done(function(data) {
                data = normalizarRespuestaAjax(data);

                if (data.estado !== 1 || !data.profesional) {
                    swal({
                        title: 'Información profesional',
                        text: data.msj || 'No fue posible cargar la información del veterinario.',
                        icon: 'error',
                        buttons: 'Aceptar',
                    });
                    return;
                }

                pintarFichaProfesional(data);
                $('#ficha_profesional').modal('show');
            }).fail(function() {
                swal({
                    title: 'Información profesional',
                    text: 'No fue posible cargar la información del veterinario.',
                    icon: 'error',
                    buttons: 'Aceptar',
                });
            });
        }

        const reservaVeterinariaState = {
            lugares: [],
            flatpickrInstance: null,
            pasoActual: 1,
            diasCargados: false,
            textoFecha: '',
        };

        const TOTAL_PASOS_RESERVA_VET = 3;

        const DIAS_SEMANA_RESERVA_VET = {
            '1': 'Lunes',
            '2': 'Martes',
            '3': 'Miércoles',
            '4': 'Jueves',
            '5': 'Viernes',
            '6': 'Sábado',
            '7': 'Domingo'
        };

        // flatpickr no trae el español cargado en la plantilla, así que se deja acá
        const localeCalendarioReservaVet = {
            weekdays: {
                shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            },
            months: {
                shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            },
            ordinal: function() {
                return 'º';
            },
            firstDayOfWeek: 1,
            rangeSeparator: ' a ',
            time_24hr: true,
            yearAriaLabel: 'Año',
            monthAriaLabel: 'Mes',
        };

        function active_e(tipo_esp){
            if(tipo_esp=='all')
            {
                $('.filtro_le').removeClass('d-none');
            }else{
                $('.filtro_le').addClass('d-none');
                $('.le_'+tipo_esp).removeClass('d-none');
            }
        }

        function normalizarRespuestaAjax(data) {
            if (typeof data === 'string') {
                try {
                    return JSON.parse(data);
                } catch (error) {
                    return {};
                }
            }

            return data || {};
        }

        function abrirModalReservaVeterinaria(button) {
            const profesionalId = $(button).data('profesional-id');
            const profesionalNombre = $(button).data('profesional-nombre');
            const profesionalEspecialidad = $(button).data('profesional-especialidad');
            const profesionalImg = $(button).data('profesional-img');

            $('#reserva_vet_profesional_id').val(profesionalId);
            $('#reserva_vet_profesional_nombre').text(profesionalNombre || '-');
            $('#reserva_vet_profesional_especialidad').text(profesionalEspecialidad || '');
            $('#reserva_vet_profesional_img').attr('src', profesionalImg || "{{ asset('images/iconos/usuario_profesional.svg') }}");

            resetearModalReservaVeterinaria();
            $('#modal_reserva_veterinaria').modal('show');
            cargarLugaresReservaVeterinaria(profesionalId);
        }

        function resetearModalReservaVeterinaria() {
            reservaVeterinariaState.lugares = [];
            reservaVeterinariaState.diasCargados = false;
            reservaVeterinariaState.textoFecha = '';
            destruirCalendarioReservaVeterinaria();

            $('#reserva_vet_lugar_id').val('');
            $('#reserva_vet_fecha').val('');
            $('#reserva_vet_hora').val('');
            $('#reserva_vet_lugares').empty();
            $('#reserva_vet_lugar_aviso').addClass('d-none').text('');
            mostrarInfoLugarReservaVeterinaria(null);
            $('#reserva_vet_fecha_input').val('');
            limpiarHorasReservaVeterinaria();
            $('#btn_anterior_reserva_vet').prop('disabled', false);

            irPasoReservaVeterinaria(1);
        }

        // Muestra el contacto y horario del lugar, o el aviso vacío si no hay lugar elegido
        function mostrarInfoLugarReservaVeterinaria(lugar) {
            $('#reserva_vet_lugar_vacio').toggleClass('d-none', !!lugar);
            $('#reserva_vet_lugar_info').toggleClass('d-none', !lugar);

            if (!lugar) {
                return;
            }

            $('#reserva_vet_lugar_contacto').toggleClass('d-none', !lugar.telefono && !lugar.email);

            $('#reserva_vet_telefono').toggleClass('d-none', !lugar.telefono);
            if (lugar.telefono) {
                $('#reserva_vet_telefono').attr('href', 'tel:' + String(lugar.telefono).replace(/\s+/g, ''));
                $('#reserva_vet_telefono_texto').text(lugar.telefono);
            }

            $('#reserva_vet_email').toggleClass('d-none', !lugar.email);
            if (lugar.email) {
                $('#reserva_vet_email').attr('href', 'mailto:' + lugar.email);
                $('#reserva_vet_email_texto').text(lugar.email);
            }

            $('#reserva_vet_dias_horario').html('<span class="reserva-vet-cargando">Cargando horario...</span>');
        }

        // Tarjetas grises mientras llegan los lugares
        function esqueletoLugaresReservaVeterinaria() {
            const $lista = $('#reserva_vet_lugares').empty();

            for (let i = 0; i < 2; i++) {
                $lista.append(
                    '<div class="reserva-vet-lugar-esqueleto" aria-hidden="true">' +
                        '<span class="reserva-vet-lugar-icono"></span>' +
                        '<span class="reserva-vet-lugar-datos"><span></span><span></span></span>' +
                    '</div>'
                );
            }
        }

        function tarjetaLugarReservaVeterinaria(lugar) {
            const $radio = $('<input type="radio" class="reserva-vet-lugar-radio" name="reserva_vet_lugar">')
                .val(lugar.id)
                .on('change', cambiarLugarReservaVeterinaria);

            const $caja = $('<span class="reserva-vet-lugar-caja">')
                .append('<span class="reserva-vet-lugar-icono"><i class="feather icon-map-pin"></i></span>')
                .append(
                    $('<span class="reserva-vet-lugar-datos">')
                        .append($('<span class="reserva-vet-lugar-nombre">').text(lugar.nombre || 'Sin nombre'))
                        .append($('<span class="reserva-vet-lugar-direccion">').text(formatearDireccionLugar(lugar)))
                )
                .append('<span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>');

            return $('<label class="reserva-vet-lugar">').append($radio, $caja);
        }

        function nombresDiasReservaVeterinaria(dias) {
            const nombres = dias.map(function(dia) {
                return DIAS_SEMANA_RESERVA_VET[dia] || dia;
            });

            if (nombres.length <= 1) {
                return nombres.join('');
            }

            return nombres.slice(0, -1).join(', ') + ' y ' + nombres[nombres.length - 1];
        }

        // Junta los bloques que comparten días: una fila con los días y al lado sus rangos de hora
        function pintarHorarioReservaVeterinaria(horarios) {
            const $horario = $('#reserva_vet_dias_horario').empty();
            const grupos = [];

            $.each(horarios || [], function(_, bloque) {
                if (!bloque.hora_inicio || !bloque.hora_termino) {
                    return;
                }

                const dias = String(bloque.dia || '').split(',').map(function(dia) {
                    return dia.trim();
                }).filter(Boolean).sort();
                const clave = dias.join(',');
                const rango = moment(bloque.hora_inicio, 'HH:mm:ss').format('HH:mm') + ' – ' + moment(bloque.hora_termino, 'HH:mm:ss').format('HH:mm');

                let grupo = grupos.find(function(item) {
                    return item.clave === clave;
                });
                if (!grupo) {
                    grupo = { clave: clave, dias: dias, rangos: [] };
                    grupos.push(grupo);
                }
                if (grupo.rangos.indexOf(rango) === -1) {
                    grupo.rangos.push(rango);
                }
            });

            if (grupos.length === 0) {
                $horario.html('<span class="ficha-profesional__sin-dato">No informado</span>');
                return;
            }

            grupos.sort(function(a, b) {
                return a.clave < b.clave ? -1 : 1;
            });

            $.each(grupos, function(_, grupo) {
                const $rangos = $('<span class="reserva-vet-horario-rangos">');
                $.each(grupo.rangos, function(_, rango) {
                    $rangos.append($('<span class="reserva-vet-horario-rango">').text(rango));
                });

                $horario.append(
                    $('<div class="reserva-vet-horario">')
                        .append($('<span class="reserva-vet-horario-dias">').text(nombresDiasReservaVeterinaria(grupo.dias)))
                        .append($rangos)
                );
            });
        }

        // Deja la caja de horas esperando que se elija una fecha
        function limpiarHorasReservaVeterinaria() {
            reservaVeterinariaState.textoFecha = '';
            $('#reserva_vet_fecha_texto').text('Primero elija una fecha.');
            mensajeHorasReservaVeterinaria('Las horas aparecerán aquí cuando elija un día en el calendario.', false);
        }

        function mensajeHorasReservaVeterinaria(texto, esError) {
            const $mensaje = $('<div class="reserva-vet-horas-vacio">')
                .toggleClass('text-danger', !!esError)
                .append('<i class="feather icon-clock"></i>')
                .append($('<span>').text(texto));

            $('#reserva_vet_horas_total').addClass('d-none').text('');
            $('#reserva_vet_horas').empty().append($mensaje);
        }

        // Separa las horas en mañana y tarde para que se ubiquen de un vistazo
        function pintarHorasReservaVeterinaria(registros) {
            const $horas = $('#reserva_vet_horas').empty();
            const bloques = [
                { titulo: 'Mañana', icono: 'icon-sun', horas: [] },
                { titulo: 'Tarde', icono: 'icon-sunset', horas: [] },
            ];

            $.each(registros, function(_, registro) {
                const hora = moment(registro.hora, 'HH:mm:ss');
                bloques[hora.hour() < 12 ? 0 : 1].horas.push({ valor: registro.hora, texto: hora.format('HH:mm') });
            });

            $.each(bloques, function(_, bloque) {
                if (bloque.horas.length === 0) {
                    return;
                }

                const $grilla = $('<div class="reserva-vet-horas-grilla">');
                $.each(bloque.horas, function(_, hora) {
                    $grilla.append(
                        $('<button type="button" class="reserva-vet-hora" aria-pressed="false">')
                            .attr('data-hora', hora.valor)
                            .text(hora.texto)
                            .on('click', function() {
                                seleccionarHoraReservaVeterinaria(this);
                            })
                    );
                });

                $horas.append(
                    $('<div class="reserva-vet-horas-bloque">')
                        .append(
                            $('<span class="reserva-vet-horas-bloque-titulo">')
                                .append($('<i class="feather">').addClass(bloque.icono))
                                .append(document.createTextNode(bloque.titulo))
                        )
                        .append($grilla)
                );
            });

            $('#reserva_vet_horas_total')
                .text(registros.length === 1 ? '1 hora' : registros.length + ' horas')
                .removeClass('d-none');
        }

        // En pantallas angostas las horas quedan bajo el calendario, así que se acercan a la vista
        function acercarHorasReservaVeterinaria() {
            if (!window.matchMedia || !window.matchMedia('(max-width: 991.98px)').matches) {
                return;
            }

            const caja = document.getElementById('reserva_vet_horas_caja');
            const sinAnimacion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (caja && caja.scrollIntoView) {
                caja.scrollIntoView({ behavior: sinAnimacion ? 'auto' : 'smooth', block: 'nearest' });
            }
        }

        function destruirCalendarioReservaVeterinaria() {
            if (reservaVeterinariaState.flatpickrInstance) {
                reservaVeterinariaState.flatpickrInstance.destroy();
                reservaVeterinariaState.flatpickrInstance = null;
            }
        }

        // Lo mínimo que necesita cada paso para poder avanzar
        function pasoCompletoReservaVeterinaria(paso) {
            switch (paso) {
                case 1:
                    return $('#reserva_vet_lugar_id').val() !== '' && reservaVeterinariaState.diasCargados;
                case 2:
                    return $('#reserva_vet_fecha').val() !== '' && $('#reserva_vet_hora').val() !== '';
                default:
                    return true;
            }
        }

        // A un paso solo se llega si los anteriores ya están listos
        function pasoDisponibleReservaVeterinaria(paso) {
            for (let i = 1; i < paso; i++) {
                if (!pasoCompletoReservaVeterinaria(i)) {
                    return false;
                }
            }

            return true;
        }

        function irPasoReservaVeterinaria(paso) {
            if (paso < 1 || paso > TOTAL_PASOS_RESERVA_VET || !pasoDisponibleReservaVeterinaria(paso)) {
                return;
            }

            reservaVeterinariaState.pasoActual = paso;
            $('#modal_reserva_veterinaria .reserva-vet-panel').removeClass('activo');
            $('#modal_reserva_veterinaria .reserva-vet-panel[data-paso="' + paso + '"]').addClass('activo');

            if (paso === TOTAL_PASOS_RESERVA_VET) {
                llenarResumenReservaVeterinaria();
            }

            actualizarWizardReservaVeterinaria();

            // En celular el modal puede quedar scrolleado, se vuelve arriba al cambiar de paso
            $('#modal_reserva_veterinaria').scrollTop(0);
        }

        function siguientePasoReservaVeterinaria() {
            const paso = reservaVeterinariaState.pasoActual;

            if (!pasoCompletoReservaVeterinaria(paso)) {
                return;
            }

            irPasoReservaVeterinaria(paso + 1);
        }

        function anteriorPasoReservaVeterinaria() {
            irPasoReservaVeterinaria(reservaVeterinariaState.pasoActual - 1);
        }

        // Marca el indicador de pasos y deja los botones del pie según el paso en que se está
        function actualizarWizardReservaVeterinaria() {
            const pasoActual = reservaVeterinariaState.pasoActual;
            const esUltimoPaso = pasoActual === TOTAL_PASOS_RESERVA_VET;

            $('#reserva_vet_pasos .reserva-vet-paso').each(function() {
                const paso = Number($(this).data('paso'));
                const completado = paso !== pasoActual
                    && paso < TOTAL_PASOS_RESERVA_VET
                    && pasoDisponibleReservaVeterinaria(paso)
                    && pasoCompletoReservaVeterinaria(paso);

                $(this).toggleClass('activo', paso === pasoActual).toggleClass('completado', completado);
                $(this).find('.reserva-vet-paso-numero').html(completado ? '<i class="feather icon-check"></i>' : paso);
                $(this).find('.reserva-vet-paso-boton')
                    .prop('disabled', !pasoDisponibleReservaVeterinaria(paso))
                    .attr('aria-current', paso === pasoActual ? 'step' : null);
            });

            $('#btn_cancelar_reserva_vet').toggleClass('d-none', pasoActual > 1);
            $('#btn_anterior_reserva_vet').toggleClass('d-none', pasoActual === 1);
            $('#btn_siguiente_reserva_vet')
                .toggleClass('d-none', esUltimoPaso)
                .prop('disabled', !pasoCompletoReservaVeterinaria(pasoActual));
            $('#btn_confirmar_reserva_vet')
                .toggleClass('d-none', !esUltimoPaso)
                .prop('disabled', !esUltimoPaso || !pasoDisponibleReservaVeterinaria(TOTAL_PASOS_RESERVA_VET));
        }

        function llenarResumenReservaVeterinaria() {
            const lugar = obtenerLugarReservaSeleccionado();
            const hora = $('#reserva_vet_hora').val();

            $('#reserva_vet_resumen_profesional').text($('#reserva_vet_profesional_nombre').text() || '-');
            $('#reserva_vet_resumen_lugar').text(lugar ? lugar.nombre : '-');
            $('#reserva_vet_resumen_direccion').text(lugar ? formatearDireccionLugar(lugar) : '');
            $('#reserva_vet_resumen_fecha').text(reservaVeterinariaState.textoFecha || $('#reserva_vet_fecha').val() || '-');
            $('#reserva_vet_resumen_hora').text(hora ? moment(hora, 'HH:mm:ss').format('HH:mm') + ' hrs.' : '-');
        }

        function mostrarAvisoLugarReservaVeterinaria(mensaje) {
            $('#reserva_vet_lugar_aviso').text(mensaje).removeClass('d-none');
        }

        function cargarLugaresReservaVeterinaria(idProfesional) {
            const $lista = $('#reserva_vet_lugares');
            esqueletoLugaresReservaVeterinaria();

            $.ajax({
                url: "{{ route('profesional.lugaresAtencionProfesionalBuscador') }}",
                type: 'GET',
                data: {
                    id_profesional: idProfesional,
                },
            }).done(function(data) {
                data = normalizarRespuestaAjax(data);
                $lista.empty();

                if (data.estado !== 1 || !Array.isArray(data.registros) || data.registros.length === 0) {
                    mostrarAvisoLugarReservaVeterinaria('Este veterinario no tiene lugares de atención disponibles.');
                    $('#reserva_vet_lugar_vacio').addClass('d-none');
                    return;
                }

                reservaVeterinariaState.lugares = data.registros;

                $.each(data.registros, function(_, lugar) {
                    $lista.append(tarjetaLugarReservaVeterinaria(lugar));
                });

                // Si atiende en un solo lugar se deja elegido para ahorrar un clic
                if (data.registros.length === 1) {
                    $lista.find('.reserva-vet-lugar-radio').prop('checked', true);
                    cambiarLugarReservaVeterinaria();
                }
            }).fail(function() {
                $lista.empty();
                mostrarAvisoLugarReservaVeterinaria('No fue posible cargar los lugares de atención.');
                $('#reserva_vet_lugar_vacio').addClass('d-none');
            });
        }

        function obtenerLugarReservaSeleccionado() {
            const idLugar = $('#reserva_vet_lugares .reserva-vet-lugar-radio:checked').val();
            return reservaVeterinariaState.lugares.find(function(lugar) {
                return String(lugar.id) === String(idLugar);
            }) || null;
        }

        function formatearDireccionLugar(lugar) {
            if (!lugar || !lugar.direccion) {
                return 'Dirección no informada.';
            }

            const partes = [];
            if (lugar.direccion.direccion) {
                let direccion = lugar.direccion.direccion;
                if (lugar.direccion.numero_dir) {
                    direccion += ' ' + lugar.direccion.numero_dir;
                }
                partes.push(direccion);
            }
            if (lugar.direccion.ciudad && lugar.direccion.ciudad.nombre) {
                partes.push(lugar.direccion.ciudad.nombre);
            }

            return partes.length > 0 ? partes.join(', ') : 'Dirección no informada.';
        }

        function cambiarLugarReservaVeterinaria() {
            const lugar = obtenerLugarReservaSeleccionado();

            $('#reserva_vet_lugar_id').val(lugar ? lugar.id : '');
            $('#reserva_vet_fecha').val('');
            $('#reserva_vet_hora').val('');
            $('#reserva_vet_fecha_input').val('');
            limpiarHorasReservaVeterinaria();
            reservaVeterinariaState.diasCargados = false;
            destruirCalendarioReservaVeterinaria();
            mostrarInfoLugarReservaVeterinaria(lugar);
            actualizarWizardReservaVeterinaria();

            if (!lugar) {
                return;
            }

            cargarDiasReservaVeterinaria();
        }

        function cargarDiasReservaVeterinaria() {
            const idProfesional = $('#reserva_vet_profesional_id').val();
            const idLugar = $('#reserva_vet_lugar_id').val();

            $.ajax({
                url: "{{ route('profesional.DiasLaboralesProfesionaLugarAtencionBuscador') }}",
                type: 'GET',
                data: {
                    id_profesional: idProfesional,
                    lugar_atencion: idLugar,
                    tipo_agenda: 1,
                },
            }).done(function(data) {
                data = normalizarRespuestaAjax(data);

                // Si mientras cargaba se cambió de lugar, esta respuesta ya no sirve
                if (String(idLugar) !== String($('#reserva_vet_lugar_id').val())) {
                    return;
                }

                if (data.estado !== 1 || !data.registros || !data.registros.horario_agenda_laboral) {
                    $('#reserva_vet_dias_horario').html('<span class="ficha-profesional__sin-dato">Sin días de atención informados.</span>');
                    return;
                }

                // El "0" no corresponde a ningún día de la semana, se descarta
                const diasActivos = data.registros.horario_agenda_laboral.split(',').filter(function(dia) {
                    return Boolean(dia) && Object.prototype.hasOwnProperty.call(DIAS_SEMANA_RESERVA_VET, dia);
                });

                pintarHorarioReservaVeterinaria(data.registros.horarios);

                destruirCalendarioReservaVeterinaria();

                reservaVeterinariaState.flatpickrInstance = flatpickr('#reserva_vet_fecha_input', {
                    inline: true,
                    dateFormat: 'Y-m-d',
                    minDate: 'today',
                    maxDate: new Date().fp_incr(60),
                    disable: [
                        function(date) {
                            const diaSemana = date.getDay() === 0 ? '7' : String(date.getDay());
                            return !diasActivos.includes(diaSemana);
                        }
                    ],
                    locale: localeCalendarioReservaVet,
                    onChange: function(selectedDates, dateStr, instance) {
                        $('#reserva_vet_fecha').val(dateStr || '');
                        $('#reserva_vet_hora').val('');

                        if (!dateStr) {
                            limpiarHorasReservaVeterinaria();
                            actualizarWizardReservaVeterinaria();
                            return;
                        }

                        // Queda como "Lunes 5 de octubre de 2026"
                        const textoFecha = instance.formatDate(selectedDates[0], 'l j \\d\\e F \\d\\e Y').toLowerCase();
                        reservaVeterinariaState.textoFecha = textoFecha.charAt(0).toUpperCase() + textoFecha.slice(1);

                        actualizarWizardReservaVeterinaria();
                        cargarHorasReservaVeterinaria(dateStr);
                    }
                });

                reservaVeterinariaState.diasCargados = true;
                actualizarWizardReservaVeterinaria();
            }).fail(function() {
                $('#reserva_vet_dias_horario').html('<span class="ficha-profesional__sin-dato text-danger">No fue posible cargar los días de atención.</span>');
            });
        }

        function cargarHorasReservaVeterinaria(fecha) {
            const idProfesional = $('#reserva_vet_profesional_id').val();
            const idLugar = $('#reserva_vet_lugar_id').val();

            $('#reserva_vet_fecha_texto').text(reservaVeterinariaState.textoFecha);
            mensajeHorasReservaVeterinaria('Buscando horas disponibles...', false);

            $.ajax({
                url: "{{ route('profesional.HorasDisponiblesProfesionalLugarAtencionBuscador') }}",
                type: 'GET',
                data: {
                    id_profesional: idProfesional,
                    id_lugar_atencion: idLugar,
                    dia: fecha,
                    tipo_agenda: 1,
                },
            }).done(function(data) {
                data = normalizarRespuestaAjax(data);

                // Si mientras cargaba se eligió otra fecha, esta respuesta ya no sirve
                if (fecha !== $('#reserva_vet_fecha').val()) {
                    return;
                }

                $('#reserva_vet_hora').val('');
                actualizarWizardReservaVeterinaria();

                if (data.estado !== 1 || !Array.isArray(data.registros) || data.registros.length === 0) {
                    mensajeHorasReservaVeterinaria('No quedan horas disponibles este día. Elija otra fecha en el calendario.', false);
                    acercarHorasReservaVeterinaria();
                    return;
                }

                pintarHorasReservaVeterinaria(data.registros);
                acercarHorasReservaVeterinaria();
            }).fail(function() {
                if (fecha !== $('#reserva_vet_fecha').val()) {
                    return;
                }

                mensajeHorasReservaVeterinaria('No fue posible cargar las horas disponibles.', true);
            });
        }

        function seleccionarHoraReservaVeterinaria(button) {
            $('.reserva-vet-hora').removeClass('active').attr('aria-pressed', 'false');
            $(button).addClass('active').attr('aria-pressed', 'true');
            $('#reserva_vet_hora').val($(button).data('hora'));
            actualizarWizardReservaVeterinaria();
        }

        function confirmarReservaVeterinaria() {
            const idProfesional = $('#reserva_vet_profesional_id').val();
            const idLugar = $('#reserva_vet_lugar_id').val();
            const fecha = $('#reserva_vet_fecha').val();
            const hora = $('#reserva_vet_hora').val();

            if (!idProfesional || !idLugar || !fecha || !hora) {
                swal({
                    title: 'Reserva de hora',
                    text: 'Debe seleccionar lugar, fecha y hora.',
                    icon: 'error',
                    buttons: 'Aceptar',
                });
                return;
            }

            // Mientras se guarda no se puede volver atrás ni confirmar de nuevo
            $('#btn_confirmar_reserva_vet, #btn_anterior_reserva_vet').prop('disabled', true);

            $.ajax({
                url: "{{ route('paciente.solicitar.hora') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    reserva_hora_id: "{{ $responsable->id }}",
                    id_profesional: idProfesional,
                    id_lugar_atencion: idLugar,
                    id_asistente: 2,
                    fecha_consulta: fecha + ' ' + hora,
                    tipo_hora_medica: 'C',
                    representante: 0,
                    acompanante: 0,
                    autorizacion_atencion: 1,
                    id_mascota: "{{ $paciente->id }}",
                },
            }).done(function(data) {
                data = normalizarRespuestaAjax(data);

                if (data.estado === 1 || data.estado === 'success' || data.id) {
                    $('#modal_reserva_veterinaria').modal('hide');
                    swal({
                        title: 'Hora reservada',
                        text: 'La hora fue agendada correctamente.',
                        icon: 'success',
                        buttons: 'Aceptar',
                    }).then(function() {
                        window.location.reload();
                    });
                    return;
                }

                $('#btn_confirmar_reserva_vet, #btn_anterior_reserva_vet').prop('disabled', false);
                swal({
                    title: 'No fue posible agendar la hora',
                    text: data.msj || 'Intente nuevamente.',
                    icon: 'error',
                    buttons: 'Aceptar',
                });
            }).fail(function() {
                $('#btn_confirmar_reserva_vet, #btn_anterior_reserva_vet').prop('disabled', false);
                swal({
                    title: 'No fue posible agendar la hora',
                    text: 'Ocurrió un error al guardar la reserva.',
                    icon: 'error',
                    buttons: 'Aceptar',
                });
            });
        }
    </script>

    <style>
        .page-header .page-header-title h5 {
            color: #ffffff !important;
            font-size: 20px;
            font-weight: 700 !important;
            text-shadow: 0 1px 2px rgba(0, 66, 63, .18);
        }

        .page-header .breadcrumb,
        .page-header .breadcrumb-item,
        .page-header .breadcrumb-item a,
        .page-header .breadcrumb-item i {
            color: rgba(255, 255, 255, .92) !important;
        }

        .page-header .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, .65) !important;
        }
    </style>
@endsection
