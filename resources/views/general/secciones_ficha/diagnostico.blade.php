<!--Diagnóstico-->
@php
    $esFichaVeterinaria = isset($mascota) && !empty($mascota);
    $datosVeterinarios = isset($fichaVeterinariaGeneralData) && is_array($fichaVeterinariaGeneralData)
        ? $fichaVeterinariaGeneralData
        : [];
    $diagnosticoActual = old(
        'descripcion_hipotesis',
        data_get($fichaAtencion ?? null, 'hipotesis_diagnostico', data_get($datosVeterinarios, 'descripcion_hipotesis', ''))
    );
    $cieActual = old(
        'descripcion_cie',
        data_get($fichaAtencion ?? null, 'diagnostico_ce10', data_get($datosVeterinarios, 'descripcion_cie', ''))
    );
    $observacionesActuales = old(
        'obs_clinicas',
        data_get($datosVeterinarios, 'obs_clinicas', data_get($fichaAtencion ?? null, 'obs_clinicas', ''))
    );
    $indicacionesActuales = old(
        'indicaciones',
        data_get($fichaAtencion ?? null, 'indicaciones', data_get($datosVeterinarios, 'indicaciones', ''))
    );
@endphp
<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
    <div class="card-a">
        <div class="card-header-a " id="diagnostico">
            <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#diagnostico_c" aria-expanded="false" aria-controls="diagnostico_c">
                Diagnóstico e Indicaciones
            </button>
        </div>
        <div id="diagnostico_c" class="collapse show" aria-labelledby="diagnostico" data-parent="#diagnostico">
            <div class="card-body-aten-a  shadow-none">
                <div class="form-row">
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label class="floating-label-activo-sm" for="descripcion_hipotesis">Diagnóstico</label>
                        <input type="text" class="form-control form-control-sm"  data-input_igual="lic_descripcion_hipotesis,hipotesis_certificado,eno_diagnositico_confirmado,diagnostico_cons,diag_endos_eda" name="descripcion_hipotesis" id="descripcion_hipotesis" onchange="cargarIgual('descripcion_hipotesis')" value="{{ $diagnosticoActual }}">
                    </div>
                    @unless($esFichaVeterinaria)
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label class="floating-label-activo-sm" for="descripcion_cie">Diagnóstico CIE-10</label>
                        <input type="text" class="form-control form-control-sm" data-input_igual="lic_descripcion_cie,descripcion_cie_esp,eno_diagnostico_cie" name="descripcion_cie" id="descripcion_cie" value="{{ $cieActual }}" onchange="cargarIgual('descripcion_cie')">
                        <input type="hidden" class="form-control form-control-sm" data-input_igual="id_lic_descripcion_cie,id_descripcion_cie_esp,eno_id_diagnostico_cie" name="id_descripcion_cie" id="id_descripcion_cie" value="" onchange="cargarIgual('id_descripcion_cie')">
                    </div>
                    @endunless
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label class="floating-label-activo-sm" for="descripcion_hipotesis">Obs. Cínicas para recordar en consulta</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=5" onblur="this.rows=1;" name="obs_clinicas" id="obs_clinicas">{{ $observacionesActuales }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 {{ $esFichaVeterinaria ? 'col-md-12 col-lg-12 col-xl-12' : 'col-md-6 col-lg-6 col-xl-6' }}">
                        <label class="floating-label-activo-sm" for="indicaciones">Indicaciones</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=5" onblur="this.rows=1;" name="indicaciones" id="indicaciones">{{ $indicacionesActuales }}</textarea>
                    </div>
                    <div class=" col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3 pt-2">
                        <div class="custom-control custom-switch" >
                            <input type="checkbox" class="custom-control-input" id="motivo1"  data-toggle="collapse" data-target="#motivo1_c" aria-expanded="false" aria-controls="motivo1_c">
                            <label class="custom-control-label font-weight-bold text-c-blue" for="motivo1">Enviar indicación por email</label>
                        </div>
                    </div>
                    <div class=" col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3 pt-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="motivo2"  data-toggle="collapse" data-target="#motivo2_c" aria-expanded="false" aria-controls="motivo2_c">
                            <label class="custom-control-label font-weight-bold text-c-blue" for="motivo2">Imprimir indicaciones</label>
                        </div>
                    </div> 
                    <div class=" col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
                        <button type="button" id="btn_agendar_proximo_control" class="btn btn-outline-purple btn-xs mt-1 btn-block" onclick="abrirAgendaProximoControl()">
                            <i class="feather icon-calendar"></i> Agendar Próximo Control
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@once
@php
    // A quién se le agenda: la mascota en la ficha veterinaria, el paciente en las demás
    $nombreProximoControl = $esFichaVeterinaria
        ? (data_get($mascota, 'nombre') ?: '-')
        : (trim(data_get($paciente ?? null, 'nombres', '').' '.data_get($paciente ?? null, 'apellido_uno', '')) ?: '-');
    $detalleProximoControl = $esFichaVeterinaria
        ? (data_get($mascota, 'especieMascota.nombre') ?: data_get($mascota, 'otra_especie', ''))
        : '';
    $fotoProximoControl = $esFichaVeterinaria
        ? (data_get($mascota, 'foto_url') ?: asset('images/iconos/mascotas.svg'))
        : asset('images/iconos/usuario_profesional.svg');
    $profesionalProximoControl = trim(data_get($profesional ?? null, 'nombre', '').' '.data_get($profesional ?? null, 'apellido_uno', ''))
        ?: (optional(Auth::user())->name ?: '-');
@endphp
<div class="modal fade" id="modal_agendar_proximo_control" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="titulo_agendar_proximo_control" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="titulo_agendar_proximo_control">Agendar próximo control</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pc_id_profesional">
                <input type="hidden" id="pc_id_paciente">
                <input type="hidden" id="pc_id_mascota">
                <input type="hidden" id="pc_lugar_atencion" value="">
                <input type="hidden" id="pc_fecha" value="">
                <input type="hidden" id="pc_hora" value="">

                <!--A quién se le agenda el control, queda arriba en todos los pasos-->
                <div class="reserva-vet-profesional">
                    <img class="reserva-vet-profesional-icono" src="{{ $fotoProximoControl }}" alt="">
                    <div class="reserva-vet-profesional-datos">
                        <span class="reserva-vet-profesional-etiqueta">Próximo control de</span>
                        <span class="reserva-vet-profesional-nombre">{{ $nombreProximoControl }}</span>
                    </div>
                    <span class="reserva-vet-profesional-especialidad">{{ $detalleProximoControl }}</span>
                </div>

                <!--Indicador de pasos-->
                <ol class="reserva-vet-pasos" id="pc_pasos">
                    <li class="reserva-vet-paso activo" data-paso="1">
                        <button type="button" class="reserva-vet-paso-boton" onclick="irPasoProximoControl(1);">
                            <span class="reserva-vet-paso-numero">1</span>
                            <span class="reserva-vet-paso-texto">Lugar</span>
                        </button>
                    </li>
                    <li class="reserva-vet-paso" data-paso="2">
                        <button type="button" class="reserva-vet-paso-boton" onclick="irPasoProximoControl(2);" disabled>
                            <span class="reserva-vet-paso-numero">2</span>
                            <span class="reserva-vet-paso-texto">Fecha y hora</span>
                        </button>
                    </li>
                    <li class="reserva-vet-paso" data-paso="3">
                        <button type="button" class="reserva-vet-paso-boton" onclick="irPasoProximoControl(3);" disabled>
                            <span class="reserva-vet-paso-numero">3</span>
                            <span class="reserva-vet-paso-texto">Confirmar</span>
                        </button>
                    </li>
                </ol>

                <!--Paso 1: lugar de atención-->
                <div class="reserva-vet-panel activo" data-paso="1">
                    <div class="reserva-vet-panel-encabezado">
                        <h6>¿Dónde será el control?</h6>
                        <p class="small text-muted mb-0">Queda marcado el lugar de esta atención; puede cambiarlo si atiende en otro.</p>
                    </div>

                    <!--Cada lugar es una tarjeta que se marca con un clic; se llenan al abrir el modal-->
                    <div class="reserva-vet-lugares" id="pc_lugares" role="radiogroup" aria-label="Lugar de atención"></div>
                    <div class="reserva-vet-aviso d-none" id="pc_lugar_aviso"></div>

                    <!--Días y horario del lugar elegido, solo informativos-->
                    <div class="reserva-vet-lugar-vacio" id="pc_lugar_vacio">
                        <i class="feather icon-clock"></i>
                        <span>Al elegir un lugar verá aquí sus días y horario de atención.</span>
                    </div>
                    <div class="reserva-vet-lugar-detalle d-none" id="pc_lugar_info">
                        <span class="reserva-vet-etiqueta">Días y horario de atención</span>
                        <div class="reserva-vet-horarios" id="pc_dias_atencion"></div>
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
                                <input type="text" class="d-none" id="pc_fecha_calendario" value="" tabindex="-1" aria-hidden="true">
                            </div>
                            <div class="reserva-vet-leyenda" aria-hidden="true">
                                <span><i class="reserva-vet-leyenda-disponible"></i>Disponible</span>
                                <span><i class="reserva-vet-leyenda-elegido"></i>Elegido</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="reserva-vet-horas-caja" id="pc_horas_caja">
                                <div class="reserva-vet-horas-cabecera">
                                    <div class="reserva-vet-horas-titulo">Horas disponibles</div>
                                    <span class="reserva-vet-horas-total d-none" id="pc_horas_total"></span>
                                </div>
                                <div class="reserva-vet-horas-fecha" id="pc_titulo_horas">Primero elija una fecha.</div>
                                <div class="reserva-vet-horas" id="pc_horas_disponibles" aria-live="polite"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Paso 3: resumen antes de confirmar-->
                <div class="reserva-vet-panel" data-paso="3">
                    <div class="reserva-vet-panel-encabezado">
                        <h6>Revise el control</h6>
                        <p class="small text-muted mb-0">Si todo está correcto, confirme para dejar la hora reservada.</p>
                    </div>
                    <div class="reserva-vet-resumen">
                        <!--Lo más importante primero: cuándo es la hora-->
                        <div class="reserva-vet-resumen-cita">
                            <span class="reserva-vet-resumen-cita-icono"><i class="feather icon-calendar"></i></span>
                            <div class="reserva-vet-resumen-cita-datos">
                                <span class="reserva-vet-resumen-fecha" id="pc_resumen_fecha">-</span>
                                <span class="reserva-vet-resumen-hora">
                                    <i class="feather icon-clock"></i>
                                    <span id="pc_resumen_hora">-</span>
                                </span>
                            </div>
                        </div>
                        <ul class="reserva-vet-detalle">
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="{{ $esFichaVeterinaria ? 'fas fa-paw' : 'feather icon-user' }}"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">{{ $esFichaVeterinaria ? 'Mascota' : 'Paciente' }}</span>
                                    <span class="reserva-vet-detalle-valor">{{ $nombreProximoControl }}</span>
                                </div>
                            </li>
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="feather icon-user"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">{{ $esFichaVeterinaria ? 'Veterinario' : 'Profesional' }}</span>
                                    <span class="reserva-vet-detalle-valor">{{ $profesionalProximoControl }}</span>
                                </div>
                            </li>
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="feather icon-map-pin"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">Lugar de atención</span>
                                    <span class="reserva-vet-detalle-valor" id="pc_resumen_lugar">-</span>
                                    <span class="reserva-vet-detalle-extra" id="pc_resumen_direccion"></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer reserva-vet-acciones">
                <button type="button" class="btn btn-outline-dark reserva-vet-volver" id="pc_btn_cancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-secondary reserva-vet-volver d-none" id="pc_btn_anterior" onclick="anteriorPasoProximoControl();">
                    <i class="feather icon-chevron-left"></i> Anterior
                </button>
                <button type="button" class="btn btn-info" id="pc_btn_siguiente" onclick="siguientePasoProximoControl();" disabled>
                    Siguiente <i class="feather icon-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-info d-none" id="pc_btn_confirmar" onclick="confirmarProximoControl();" disabled>
                    <i class="feather icon-check"></i> Reservar control
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Agenda del próximo control por pasos: lugar, fecha y hora, confirmación.
    // Usa los mismos estilos del agendamiento de "Mis Veterinarios" (reserva-vet-*).
    const proximoControlEstado = {
        lugares: [],
        calendario: null,
        pasoActual: 1,
        diasCargados: false,
        textoFecha: ''
    };

    const PC_TOTAL_PASOS = 3;

    const PC_DIAS_SEMANA = {
        '1': 'Lunes',
        '2': 'Martes',
        '3': 'Miércoles',
        '4': 'Jueves',
        '5': 'Viernes',
        '6': 'Sábado',
        '7': 'Domingo'
    };

    const PC_MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    // flatpickr no siempre trae el español cargado, así que se deja acá
    const PC_CALENDARIO_ESPANOL = {
        weekdays: {
            shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        },
        months: {
            shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        },
        ordinal: function () {
            return 'º';
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' a ',
        time_24hr: true,
        yearAriaLabel: 'Año',
        monthAriaLabel: 'Mes'
    };

    function respuestaProximoControl(data) {
        if (typeof data === 'string') {
            try {
                return JSON.parse(data);
            } catch (error) {
                return {};
            }
        }

        return data || {};
    }

    function abrirAgendaProximoControl() {
        const lugarAtencion = document.getElementById('id_lugar_atencion')?.value || '';
        const idPaciente = document.getElementById('id_paciente_fc')?.value || '';
        const idMascota = document.getElementById('id_mascota')?.value
            || document.getElementById('reserva_hora_mascota_id')?.value
            || @json(data_get($mascota ?? null, 'id', ''));
        const idProfesional = document.getElementById('id_profesional_fc')?.value || '';

        if (!lugarAtencion || !idPaciente || !idProfesional) {
            const mensaje = 'No se pudo identificar el profesional, responsable o lugar de atención de esta ficha.';
            if (typeof swal === 'function') {
                swal({ title: 'Próximo control', text: mensaje, icon: 'warning', button: 'Aceptar' });
            } else {
                alert(mensaje);
            }
            return;
        }

        $('#pc_id_profesional').val(idProfesional);
        $('#pc_id_paciente').val(idPaciente);
        $('#pc_id_mascota').val(idMascota);

        reiniciarProximoControl();
        $('#modal_agendar_proximo_control').modal('show');
        cargarLugaresProximoControl(idProfesional, lugarAtencion);
    }

    function reiniciarProximoControl() {
        proximoControlEstado.lugares = [];
        proximoControlEstado.diasCargados = false;
        proximoControlEstado.textoFecha = '';
        destruirCalendarioProximoControl();

        $('#pc_lugar_atencion').val('');
        $('#pc_fecha').val('');
        $('#pc_hora').val('');
        $('#pc_lugares').empty();
        $('#pc_lugar_aviso').addClass('d-none').text('');
        mostrarInfoLugarProximoControl(null);
        limpiarHorasProximoControl();
        $('#pc_btn_anterior').prop('disabled', false);

        irPasoProximoControl(1);
    }

    // Muestra el horario del lugar, o el aviso vacío si todavía no hay lugar elegido
    function mostrarInfoLugarProximoControl(lugar) {
        $('#pc_lugar_vacio').toggleClass('d-none', !!lugar);
        $('#pc_lugar_info').toggleClass('d-none', !lugar);

        if (lugar) {
            $('#pc_dias_atencion').html('<span class="reserva-vet-cargando">Cargando horario...</span>');
        }
    }

    function avisoLugarProximoControl(mensaje) {
        $('#pc_lugar_aviso').text(mensaje).removeClass('d-none');
        $('#pc_lugar_vacio').addClass('d-none');
    }

    function direccionLugarProximoControl(lugar) {
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

    function tarjetaLugarProximoControl(lugar) {
        const $radio = $('<input type="radio" class="reserva-vet-lugar-radio" name="pc_lugar">')
            .val(lugar.id)
            .on('change', cambiarLugarProximoControl);

        const $caja = $('<span class="reserva-vet-lugar-caja">')
            .append('<span class="reserva-vet-lugar-icono"><i class="feather icon-map-pin"></i></span>')
            .append(
                $('<span class="reserva-vet-lugar-datos">')
                    .append($('<span class="reserva-vet-lugar-nombre">').text(lugar.nombre || 'Sin nombre'))
                    .append($('<span class="reserva-vet-lugar-direccion">').text(direccionLugarProximoControl(lugar)))
            )
            .append('<span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>');

        return $('<label class="reserva-vet-lugar">').append($radio, $caja);
    }

    function cargarLugaresProximoControl(idProfesional, lugarDeLaFicha) {
        const $lista = $('#pc_lugares').empty();

        // Tarjetas grises mientras llegan los lugares
        for (let i = 0; i < 2; i++) {
            $lista.append(
                '<div class="reserva-vet-lugar-esqueleto" aria-hidden="true">' +
                    '<span class="reserva-vet-lugar-icono"></span>' +
                    '<span class="reserva-vet-lugar-datos"><span></span><span></span></span>' +
                '</div>'
            );
        }

        $.get(@json(route('profesional.lugaresAtencionProfesionalBuscador')), {
            id_profesional: idProfesional
        }).done(function (data) {
            data = respuestaProximoControl(data);
            $lista.empty();

            if (data.estado != 1 || !Array.isArray(data.registros) || data.registros.length === 0) {
                avisoLugarProximoControl('El profesional no tiene lugares de atención configurados.');
                return;
            }

            proximoControlEstado.lugares = data.registros;
            $.each(data.registros, function (_, lugar) {
                $lista.append(tarjetaLugarProximoControl(lugar));
            });

            // Parte marcado el lugar de la ficha; si atiende en uno solo, ese
            let $elegido = $lista.find('.reserva-vet-lugar-radio').filter(function () {
                return String(this.value) === String(lugarDeLaFicha);
            });
            if ($elegido.length === 0 && data.registros.length === 1) {
                $elegido = $lista.find('.reserva-vet-lugar-radio');
            }
            if ($elegido.length > 0) {
                $elegido.first().prop('checked', true);
                cambiarLugarProximoControl();
            }
        }).fail(function () {
            $lista.empty();
            avisoLugarProximoControl('No fue posible cargar los lugares de atención.');
        });
    }

    function lugarElegidoProximoControl() {
        const idLugar = $('#pc_lugares .reserva-vet-lugar-radio:checked').val();
        return proximoControlEstado.lugares.find(function (lugar) {
            return String(lugar.id) === String(idLugar);
        }) || null;
    }

    function cambiarLugarProximoControl() {
        const lugar = lugarElegidoProximoControl();

        $('#pc_lugar_atencion').val(lugar ? lugar.id : '');
        $('#pc_fecha').val('');
        $('#pc_hora').val('');
        limpiarHorasProximoControl();
        proximoControlEstado.diasCargados = false;
        destruirCalendarioProximoControl();
        mostrarInfoLugarProximoControl(lugar);
        actualizarWizardProximoControl();

        if (lugar) {
            cargarDiasProximoControl();
        }
    }

    function nombresDiasProximoControl(dias) {
        const nombres = dias.map(function (dia) {
            return PC_DIAS_SEMANA[dia] || dia;
        });

        if (nombres.length <= 1) {
            return nombres.join('');
        }

        return nombres.slice(0, -1).join(', ') + ' y ' + nombres[nombres.length - 1];
    }

    // Junta los bloques que comparten días: una fila con los días y al lado sus rangos de hora.
    // Si el lugar no informa bloques, se muestran solo los días en que atiende.
    function pintarHorarioProximoControl(horarios, diasActivos) {
        const $horario = $('#pc_dias_atencion').empty();
        const grupos = [];

        $.each(horarios || [], function (_, bloque) {
            if (!bloque.hora_inicio || !bloque.hora_termino) {
                return;
            }

            // Solo días reales de la semana: el "0" que viene en algunos bloques se descarta
            const dias = String(bloque.dia || '').split(',').map(function (dia) {
                return dia.trim();
            }).filter(function (dia) {
                return Object.prototype.hasOwnProperty.call(PC_DIAS_SEMANA, dia);
            }).sort();
            if (dias.length === 0) {
                return;
            }

            const clave = dias.join(',');
            const rango = String(bloque.hora_inicio).slice(0, 5) + ' – ' + String(bloque.hora_termino).slice(0, 5);

            let grupo = grupos.find(function (item) {
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
            $horario.append(
                $('<div class="reserva-vet-horario">')
                    .append($('<span class="reserva-vet-horario-dias">').text(nombresDiasProximoControl(diasActivos)))
            );
            return;
        }

        grupos.sort(function (a, b) {
            return a.clave < b.clave ? -1 : 1;
        });

        $.each(grupos, function (_, grupo) {
            const $rangos = $('<span class="reserva-vet-horario-rangos">');
            $.each(grupo.rangos, function (_, rango) {
                $rangos.append($('<span class="reserva-vet-horario-rango">').text(rango));
            });

            $horario.append(
                $('<div class="reserva-vet-horario">')
                    .append($('<span class="reserva-vet-horario-dias">').text(nombresDiasProximoControl(grupo.dias)))
                    .append($rangos)
            );
        });
    }

    // El calendario necesita flatpickr; si la plantilla no lo trae se carga la copia local
    function conCalendarioProximoControl(listo) {
        if (window.flatpickr) {
            listo();
            return;
        }

        if (!document.querySelector('link[href*="flatpickr"]')) {
            const estilos = document.createElement('link');
            estilos.rel = 'stylesheet';
            estilos.href = @json(asset('css/flatpickr/flatpickr.min.css'));
            document.head.insertBefore(estilos, document.head.firstChild);
        }

        const script = document.createElement('script');
        script.src = @json(asset('js/flatpickr/flatpickr.min.js'));
        script.onload = listo;
        document.head.appendChild(script);
    }

    function destruirCalendarioProximoControl() {
        if (proximoControlEstado.calendario) {
            proximoControlEstado.calendario.destroy();
            proximoControlEstado.calendario = null;
        }
        $('#pc_fecha_calendario').val('');
    }

    function cargarDiasProximoControl() {
        const idProfesional = $('#pc_id_profesional').val();
        const idLugar = $('#pc_lugar_atencion').val();

        $.get(@json(route('profesional.DiasLaboralesProfesionaLugarAtencionBuscador')), {
            id_profesional: idProfesional,
            lugar_atencion: idLugar
        }).done(function (data) {
            data = respuestaProximoControl(data);

            // Si mientras cargaba se cambió de lugar, esta respuesta ya no sirve
            if (String(idLugar) !== String($('#pc_lugar_atencion').val())) {
                return;
            }

            const horario = data.registros ? data.registros.horario_agenda_laboral : '';
            if (data.estado != 1 || !horario) {
                $('#pc_dias_atencion').html('<span class="reserva-vet-cargando">No existe agenda configurada para este lugar.</span>');
                return;
            }

            // El "0" no corresponde a ningún día de la semana, se descarta
            const diasActivos = String(horario).split(',').filter(function (dia) {
                return Boolean(dia) && Object.prototype.hasOwnProperty.call(PC_DIAS_SEMANA, dia);
            });

            pintarHorarioProximoControl(data.registros.horarios, diasActivos);

            conCalendarioProximoControl(function () {
                if (String(idLugar) !== String($('#pc_lugar_atencion').val())) {
                    return;
                }

                destruirCalendarioProximoControl();
                proximoControlEstado.calendario = flatpickr('#pc_fecha_calendario', {
                    inline: true,
                    dateFormat: 'Y-m-d',
                    minDate: 'today',
                    maxDate: new Date().fp_incr(60),
                    disable: [
                        function (date) {
                            const diaSemana = date.getDay() === 0 ? '7' : String(date.getDay());
                            return !diasActivos.includes(diaSemana);
                        }
                    ],
                    locale: PC_CALENDARIO_ESPANOL,
                    onChange: function (fechas, fecha) {
                        $('#pc_fecha').val(fecha || '');
                        $('#pc_hora').val('');

                        if (!fecha) {
                            limpiarHorasProximoControl();
                            actualizarWizardProximoControl();
                            return;
                        }

                        // Queda como "Lunes 5 de octubre de 2026"
                        const dia = fechas[0];
                        proximoControlEstado.textoFecha = PC_CALENDARIO_ESPANOL.weekdays.longhand[dia.getDay()]
                            + ' ' + dia.getDate() + ' de ' + PC_MESES[dia.getMonth()] + ' de ' + dia.getFullYear();

                        actualizarWizardProximoControl();
                        cargarHorasProximoControl(fecha);
                    }
                });

                proximoControlEstado.diasCargados = true;
                actualizarWizardProximoControl();
            });
        }).fail(function () {
            if (String(idLugar) !== String($('#pc_lugar_atencion').val())) {
                return;
            }
            $('#pc_dias_atencion').html('<span class="reserva-vet-cargando text-danger">No fue posible consultar los días de atención.</span>');
        });
    }

    // Deja la caja de horas esperando que se elija una fecha
    function limpiarHorasProximoControl() {
        proximoControlEstado.textoFecha = '';
        $('#pc_titulo_horas').text('Primero elija una fecha.');
        mensajeHorasProximoControl('Las horas aparecerán aquí cuando elija un día en el calendario.', false);
    }

    function mensajeHorasProximoControl(texto, esError) {
        const $mensaje = $('<div class="reserva-vet-horas-vacio">')
            .toggleClass('text-danger', !!esError)
            .append('<i class="feather icon-clock"></i>')
            .append($('<span>').text(texto));

        $('#pc_horas_total').addClass('d-none').text('');
        $('#pc_horas_disponibles').empty().append($mensaje);
    }

    // Separa las horas en mañana y tarde para que se ubiquen de un vistazo
    function pintarHorasProximoControl(registros) {
        const $horas = $('#pc_horas_disponibles').empty();
        const bloques = [
            { titulo: 'Mañana', icono: 'icon-sun', horas: [] },
            { titulo: 'Tarde', icono: 'icon-sunset', horas: [] }
        ];

        $.each(registros, function (_, registro) {
            const texto = String(registro.hora).slice(0, 5);
            bloques[Number(texto.slice(0, 2)) < 12 ? 0 : 1].horas.push({ valor: registro.hora, texto: texto });
        });

        $.each(bloques, function (_, bloque) {
            if (bloque.horas.length === 0) {
                return;
            }

            const $grilla = $('<div class="reserva-vet-horas-grilla">');
            $.each(bloque.horas, function (_, hora) {
                $grilla.append(
                    $('<button type="button" class="reserva-vet-hora" aria-pressed="false">')
                        .attr('data-hora', hora.valor)
                        .text(hora.texto)
                        .on('click', function () {
                            seleccionarHoraProximoControl(this);
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

        $('#pc_horas_total')
            .text(registros.length === 1 ? '1 hora' : registros.length + ' horas')
            .removeClass('d-none');
    }

    // En pantallas angostas las horas quedan bajo el calendario, así que se acercan a la vista
    function acercarHorasProximoControl() {
        if (!window.matchMedia || !window.matchMedia('(max-width: 991.98px)').matches) {
            return;
        }

        const caja = document.getElementById('pc_horas_caja');
        const sinAnimacion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (caja && caja.scrollIntoView) {
            caja.scrollIntoView({ behavior: sinAnimacion ? 'auto' : 'smooth', block: 'nearest' });
        }
    }

    function cargarHorasProximoControl(fecha) {
        fecha = fecha || $('#pc_fecha').val();
        if (!fecha) {
            return;
        }

        $('#pc_titulo_horas').text(proximoControlEstado.textoFecha);
        mensajeHorasProximoControl('Buscando horas disponibles...', false);

        $.get(@json(route('profesional.HorasDisponiblesProfesionalLugarAtencionBuscador')), {
            id_profesional: $('#pc_id_profesional').val(),
            id_lugar_atencion: $('#pc_lugar_atencion').val(),
            dia: fecha
        }).done(function (data) {
            data = respuestaProximoControl(data);

            // Si mientras cargaba se eligió otra fecha, esta respuesta ya no sirve
            if (fecha !== $('#pc_fecha').val()) {
                return;
            }

            $('#pc_hora').val('');
            actualizarWizardProximoControl();

            if (data.estado != 1 || !Array.isArray(data.registros) || data.registros.length === 0) {
                mensajeHorasProximoControl('Sin horas disponibles para esta fecha. Elija otro día en el calendario.', false);
                acercarHorasProximoControl();
                return;
            }

            pintarHorasProximoControl(data.registros);
            acercarHorasProximoControl();
        }).fail(function () {
            if (fecha !== $('#pc_fecha').val()) {
                return;
            }

            mensajeHorasProximoControl('No fue posible consultar las horas disponibles.', true);
        });
    }

    function seleccionarHoraProximoControl(boton) {
        $('#pc_horas_disponibles .reserva-vet-hora').removeClass('active').attr('aria-pressed', 'false');
        $(boton).addClass('active').attr('aria-pressed', 'true');
        $('#pc_hora').val($(boton).attr('data-hora'));
        actualizarWizardProximoControl();
    }

    // Lo mínimo que necesita cada paso para poder avanzar
    function pasoCompletoProximoControl(paso) {
        switch (paso) {
            case 1:
                return $('#pc_lugar_atencion').val() !== '' && proximoControlEstado.diasCargados;
            case 2:
                return $('#pc_fecha').val() !== '' && $('#pc_hora').val() !== '';
            default:
                return true;
        }
    }

    // A un paso solo se llega si los anteriores ya están listos
    function pasoDisponibleProximoControl(paso) {
        for (let i = 1; i < paso; i++) {
            if (!pasoCompletoProximoControl(i)) {
                return false;
            }
        }

        return true;
    }

    function irPasoProximoControl(paso) {
        if (paso < 1 || paso > PC_TOTAL_PASOS || !pasoDisponibleProximoControl(paso)) {
            return;
        }

        proximoControlEstado.pasoActual = paso;
        $('#modal_agendar_proximo_control .reserva-vet-panel').removeClass('activo');
        $('#modal_agendar_proximo_control .reserva-vet-panel[data-paso="' + paso + '"]').addClass('activo');

        if (paso === PC_TOTAL_PASOS) {
            llenarResumenProximoControl();
        }

        actualizarWizardProximoControl();

        // En celular el modal puede quedar desplazado, se vuelve arriba al cambiar de paso
        $('#modal_agendar_proximo_control').scrollTop(0);
    }

    function siguientePasoProximoControl() {
        const paso = proximoControlEstado.pasoActual;

        if (pasoCompletoProximoControl(paso)) {
            irPasoProximoControl(paso + 1);
        }
    }

    function anteriorPasoProximoControl() {
        irPasoProximoControl(proximoControlEstado.pasoActual - 1);
    }

    // Marca el indicador de pasos y deja los botones del pie según el paso en que se está
    function actualizarWizardProximoControl() {
        const pasoActual = proximoControlEstado.pasoActual;
        const esUltimoPaso = pasoActual === PC_TOTAL_PASOS;

        $('#pc_pasos .reserva-vet-paso').each(function () {
            const paso = Number($(this).data('paso'));
            const completado = paso !== pasoActual
                && paso < PC_TOTAL_PASOS
                && pasoDisponibleProximoControl(paso)
                && pasoCompletoProximoControl(paso);

            $(this).toggleClass('activo', paso === pasoActual).toggleClass('completado', completado);
            $(this).find('.reserva-vet-paso-numero').html(completado ? '<i class="feather icon-check"></i>' : paso);
            $(this).find('.reserva-vet-paso-boton')
                .prop('disabled', !pasoDisponibleProximoControl(paso))
                .attr('aria-current', paso === pasoActual ? 'step' : null);
        });

        $('#pc_btn_cancelar').toggleClass('d-none', pasoActual > 1);
        $('#pc_btn_anterior').toggleClass('d-none', pasoActual === 1);
        $('#pc_btn_siguiente')
            .toggleClass('d-none', esUltimoPaso)
            .prop('disabled', !pasoCompletoProximoControl(pasoActual));
        $('#pc_btn_confirmar')
            .toggleClass('d-none', !esUltimoPaso)
            .prop('disabled', !esUltimoPaso || !pasoDisponibleProximoControl(PC_TOTAL_PASOS));
    }

    function llenarResumenProximoControl() {
        const lugar = lugarElegidoProximoControl();
        const hora = $('#pc_hora').val();

        $('#pc_resumen_lugar').text(lugar ? lugar.nombre : '-');
        $('#pc_resumen_direccion').text(lugar ? direccionLugarProximoControl(lugar) : '');
        $('#pc_resumen_fecha').text(proximoControlEstado.textoFecha || $('#pc_fecha').val() || '-');
        $('#pc_resumen_hora').text(hora ? String(hora).slice(0, 5) + ' hrs.' : '-');
    }

    function confirmarProximoControl() {
        const fecha = $('#pc_fecha').val();
        const hora = $('#pc_hora').val();
        const horaCorta = String(hora).slice(0, 5);
        const textoFecha = proximoControlEstado.textoFecha || fecha;

        if (!$('#pc_lugar_atencion').val() || !fecha || !hora) {
            swal({ title: 'Próximo control', text: 'Debe seleccionar lugar, fecha y hora.', icon: 'warning', button: 'Aceptar' });
            return;
        }

        // Mientras se guarda no se puede volver atrás ni confirmar de nuevo
        $('#pc_btn_confirmar, #pc_btn_anterior').prop('disabled', true);

        // Si la hora ya no está, se vuelve al paso de fecha y hora con las horas actualizadas
        const horaNoDisponible = function (mensaje) {
            $('#pc_btn_confirmar, #pc_btn_anterior').prop('disabled', false);
            swal({ title: 'No se pudo reservar', text: mensaje || 'La hora ya no está disponible.', icon: 'error', button: 'Aceptar' });
            $('#pc_hora').val('');
            irPasoProximoControl(2);
            cargarHorasProximoControl(fecha);
        };

        $.post(@json(route('paciente.solicitar.hora')), {
            _token: @json(csrf_token()),
            fecha_consulta: fecha + ' ' + hora,
            reserva_hora_id: $('#pc_id_paciente').val(),
            id_profesional: $('#pc_id_profesional').val(),
            id_lugar_atencion: $('#pc_lugar_atencion').val(),
            id_asistente: 2,
            origen: 'ficha_veterinaria',
            tipo_hora_medica: 'C',
            id_mascota: $('#pc_id_mascota').val()
        }).done(function (respuesta) {
            respuesta = respuestaProximoControl(respuesta);

            if (respuesta.estado === 'error' || respuesta.estado === 0) {
                horaNoDisponible(respuesta.msj);
                return;
            }

            $('#modal_agendar_proximo_control').modal('hide');
            swal({ title: 'Hora reservada', text: 'El próximo control quedó agendado para el ' + textoFecha.toLowerCase() + ' a las ' + horaCorta + ' hrs.', icon: 'success', button: 'Aceptar' });
        }).fail(function (xhr) {
            horaNoDisponible(xhr.responseJSON ? xhr.responseJSON.msj : '');
        });
    }
</script>
@endonce
