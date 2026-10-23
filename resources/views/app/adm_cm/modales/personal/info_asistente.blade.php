<!--Información del asistente: datos personales, cuentas bancarias, horario (con su configuración) y roles-->
<div id="info_asistente" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="info_asistente_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="info_asistente_titulo">Informaci&oacute;n del asistente</h5>
                <button type="button" class="close text-white" aria-label="Cerrar" onclick="cerrar_info_asistente();"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <!--Ficha: nombre completo, RUT y tipo de asistente; queda visible en todas las pestañas-->
                <div class="info-prof-vet-ficha info-asistente-ficha">
                    <div>
                        <div class="info-prof-vet-nombre" id="info_asistente_nombre"></div>
                        <div class="info-prof-vet-rut">RUT <strong id="info_asistente_rut"></strong></div>
                    </div>
                    <span class="info-asistente-tipo" id="info_asistente_tipo"></span>
                </div>

                <ul class="nav pill-moderno" id="info_asistente_pills" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info_asistente_personal" role="tab">Informaci&oacute;n personal</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#info_asistente_cuentas" role="tab">Cuentas bancarias</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#info_asistente_horario" role="tab">Horario</a></li>
                    <li class="nav-item" id="info_asistente_roles_pill"><a class="nav-link" data-toggle="tab" href="#info_asistente_roles" role="tab">Roles y permisos</a></li>
                </ul>

                <div class="tab-content">
                    <!--Información personal-->
                    <div class="tab-pane fade show active" id="info_asistente_personal" role="tabpanel">
                        <div class="row info-prof-vet-bloques">
                            <div class="col-lg-4 col-md-6">
                                <div class="info-prof-vet-bloque">
                                    <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-user"></i>Datos personales</h6>
                                    <div id="info_asistente_datos"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="info-prof-vet-bloque">
                                    <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-phone"></i>Contacto</h6>
                                    <div id="info_asistente_contacto"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="info-prof-vet-bloque">
                                    <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-map-pin"></i>Residencia</h6>
                                    <div id="info_asistente_residencia"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Cuentas bancarias-->
                    <div class="tab-pane fade" id="info_asistente_cuentas" role="tabpanel">
                        <div class="row" id="info_asistente_cuentas_lista"></div>
                    </div>

                    <!--Horario: a la izquierda el horario vigente y a la derecha el formulario para cambiarlo-->
                    <div class="tab-pane fade" id="info_asistente_horario" role="tabpanel">
                        <div class="row info-prof-vet-bloques">
                            <div class="col-md-6">
                                <div class="info-prof-vet-bloque">
                                    <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-clock"></i>Horario de trabajo</h6>
                                    <div id="info_asistente_horario_vista"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-prof-vet-bloque">
                                    <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-sliders"></i>Configurar horario</h6>
                                    <div id="info_asistente_horario_sin_contrato"></div>
                                    <div id="info_asistente_horario_formulario">
                                        <input type="hidden" id="info_asistente_horario_id_contrato" value="">
                                        <input type="hidden" id="info_asistente_horario_id_empleado" value="">
                                        <div class="form-group fill">
                                            <label class="floating-label-activo-sm" for="info_asistente_horario_dias">D&iacute;as laborales</label>
                                            <select class="form-control form-control-sm" id="info_asistente_horario_dias" multiple="multiple">
                                                <option value="1">Lunes</option>
                                                <option value="2">Martes</option>
                                                <option value="3">Mi&eacute;rcoles</option>
                                                <option value="4">Jueves</option>
                                                <option value="5">Viernes</option>
                                                <option value="6">S&aacute;bado</option>
                                                <option value="7">Domingo</option>
                                            </select>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group fill col-6">
                                                <label class="floating-label-activo-sm" for="info_asistente_horario_entrada">Hora de entrada</label>
                                                <input type="time" class="form-control form-control-sm" id="info_asistente_horario_entrada">
                                            </div>
                                            <div class="form-group fill col-6">
                                                <label class="floating-label-activo-sm" for="info_asistente_horario_salida">Hora de salida</label>
                                                <input type="time" class="form-control form-control-sm" id="info_asistente_horario_salida">
                                            </div>
                                            <div class="form-group fill col-6">
                                                <label class="floating-label-activo-sm" for="info_asistente_horario_colacion_inicio">Inicio colaci&oacute;n</label>
                                                <input type="time" class="form-control form-control-sm" id="info_asistente_horario_colacion_inicio">
                                            </div>
                                            <div class="form-group fill col-6">
                                                <label class="floating-label-activo-sm" for="info_asistente_horario_colacion_termino">T&eacute;rmino colaci&oacute;n</label>
                                                <input type="time" class="form-control form-control-sm" id="info_asistente_horario_colacion_termino">
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="button" class="btn btn-info" id="info_asistente_horario_guardar" onclick="info_asistente_guardar_horario();"><i class="feather icon-save mr-1"></i>Guardar horario</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Roles y permisos: un asistente queda con un solo rol activo-->
                    <div class="tab-pane fade" id="info_asistente_roles" role="tabpanel">
                        <div class="info-prof-vet-bloque">
                            <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-shield"></i>Rol del asistente</h6>
                            <p class="info-asistente-ayuda">Al activar un rol se reemplaza el que tenga asignado. Los cambios se aplican al confirmar.</p>
                            <div class="info-asistente-roles">
                                @foreach ($lista_tipo_asistente ?? [] as $tipo_asistente)
                                    @php
                                        // Mismo nombre de rol que se usaba en el modal anterior de permisos
                                        $nombreRolAsistente = str_replace([' ', 'Publico', 'Consulta', 'Administrativo', 'ManejodeAgenda'], ['', 'Caja', '', 'Adm', 'ManejoAgenda'], $tipo_asistente->nombre);
                                    @endphp
                                    <div class="info-asistente-rol">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input info-asistente-rol-check" id="info_asistente_rol_{{ $tipo_asistente->id }}" data-rol="{{ $nombreRolAsistente }}" onchange="info_asistente_cambiar_rol(this);">
                                            <label class="custom-control-label" for="info_asistente_rol_{{ $tipo_asistente->id }}">{{ $tipo_asistente->nombre }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="cerrar_info_asistente();">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Datos del asistente abierto; se usan al guardar horario y al cambiar rol
    let info_asistente_actual = {};
    const info_asistente_nombres_dias = { 1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 5: 'Viernes', 6: 'Sábado', 7: 'Domingo' };

    $(function() {
        // El desplegable de select2 va dentro del modal para que no quede detrás ni pierda el foco
        $('#info_asistente_horario_dias').select2({
            placeholder: 'Seleccione los días laborales',
            dropdownParent: $('#info_asistente'),
            width: '100%'
        });
    });

    /**
     * Recibe { id, id_usuario, id_tipo, tipo, tipo_contrato, nombre, rut, sexo, fecha_nacimiento, email,
     * telefono_uno, telefono_dos, region, comuna, direccion, roles, id_lugar_atencion }.
     * Los datos personales llegan listos; cuentas y horario se consultan al abrir.
     */
    function abrir_info_asistente(info) {
        info_asistente_actual = info || {};
        let a = info_asistente_actual;

        $('#info_asistente_nombre').text(a.nombre || 'Sin información');
        $('#info_asistente_rut').text((a.rut || 'Sin información').toUpperCase());
        $('#info_asistente_tipo').text(a.tipo || 'Sin tipo asignado');
        $('#info_asistente_pills a[href="#info_asistente_personal"]').tab('show');

        let sexos = { F: 'Femenino', M: 'Masculino' };
        $('#info_asistente_datos').html('')
            .append(info_prof_vet_dato('Tipo de asistente', a.tipo))
            .append(info_prof_vet_dato('Sexo', sexos[a.sexo] || a.sexo))
            .append(info_prof_vet_dato('Fecha de nacimiento', info_asistente_fecha(a.fecha_nacimiento)));

        // telefono_uno es el teléfono principal y telefono_dos el opcional (celular)
        $('#info_asistente_contacto').html('')
            .append(info_prof_vet_dato('Correo electrónico', a.email))
            .append(info_prof_vet_dato('Teléfono', a.telefono_uno))
            .append(info_prof_vet_dato('Celular', a.telefono_dos));

        $('#info_asistente_residencia').html('')
            .append(info_prof_vet_dato('Región', a.region))
            .append(info_prof_vet_dato('Comuna', a.comuna))
            .append(info_prof_vet_dato('Dirección', a.direccion));

        // Roles: la pestaña solo aparece si el asistente tiene tipo, igual que el botón anterior
        $('#info_asistente_roles_pill').toggleClass('d-none', !a.id_tipo);
        info_asistente_marcar_roles();

        info_asistente_cargar_cuentas(a.id_usuario);
        info_asistente_cargar_horario();

        $('#info_asistente').modal('show');
    }

    function cerrar_info_asistente() {
        $('#info_asistente').modal('hide');
    }

    // Las fechas llegan como AAAA-MM-DD y se muestran como DD-MM-AAAA
    function info_asistente_fecha(fecha) {
        let partes = String(fecha || '').substring(0, 10).split('-');
        return partes.length === 3 ? partes[2] + '-' + partes[1] + '-' + partes[0] : '';
    }

    function info_asistente_cargar_cuentas(id_usuario) {
        let contenedor = $('#info_asistente_cuentas_lista').html('');
        contenedor.append($('<div class="col-12"></div>').append(info_prof_vet_mensaje('Cargando cuentas bancarias...')));

        $.get("{{ route('profesional.liquidacion_ver_profesional') }}", { id_seccion: id_usuario })
            .done(function(data) {
                contenedor.html('');
                if (!data || data.estado != 1 || !data.registros || !data.registros.length) {
                    contenedor.append($('<div class="col-12"></div>').append(
                        info_prof_vet_sin_registros('El asistente aún no tiene cuentas bancarias registradas.')
                    ));
                    return;
                }
                $.each(data.registros, function(i, cuenta) {
                    let bloque = $(
                        '<div class="col-md-6">' +
                        '    <div class="info-prof-vet-bloque info-prof-vet-cuenta">' +
                        '        <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-credit-card"></i><span></span></h6>' +
                        '        <div class="info-prof-vet-cuenta-datos"></div>' +
                        '    </div>' +
                        '</div>'
                    );
                    let titulo = bloque.find('.info-prof-vet-bloque-titulo');
                    titulo.find('span').text(cuenta.banco ? cuenta.banco.nombre : 'Banco');
                    if (cuenta.principal == 1) {
                        titulo.append('<span class="badge badge-info ml-auto">Principal</span>');
                    }
                    bloque.find('.info-prof-vet-cuenta-datos')
                        .append(info_prof_vet_dato('Titular', cuenta.autor))
                        .append(info_prof_vet_dato('RUT', cuenta.serie ? String(cuenta.serie).toUpperCase() : ''))
                        .append(info_prof_vet_dato('Tipo de cuenta', cuenta.otro))
                        .append(info_prof_vet_dato('Número de cuenta', cuenta.numero_control))
                        .append(info_prof_vet_dato('Correo electrónico', cuenta.email));
                    contenedor.append(bloque);
                });
            })
            .fail(function() {
                contenedor.html('').append($('<div class="col-12"></div>').append(info_prof_vet_mensaje('No fue posible cargar las cuentas bancarias.')));
            });
    }

    // Horario del contrato vigente en el lugar de atención (misma consulta del modal anterior)
    function info_asistente_cargar_horario() {
        let a = info_asistente_actual;
        let vista = $('#info_asistente_horario_vista').html('').append(info_prof_vet_mensaje('Cargando horario...'));
        let formulario = $('#info_asistente_horario_formulario').addClass('d-none');
        let sinContrato = $('#info_asistente_horario_sin_contrato').html('');

        $.get("{{ route('adm_cm.empleado_horario_lugar_atencion') }}", {
            id_empleado: a.id,
            // tipo_contrato es el tipo con que quedó guardado el contrato vigente; si no hay, se usa el tipo actual
            tipo_empleado: String(a.tipo_contrato || a.tipo || '').toUpperCase(),
            id_lugar_atencion: a.id_lugar_atencion,
            estado: 2,
        })
            .done(function(data) {
                vista.html('');
                let contrato = (data && data.estado == 1 && data.registros && data.registros.length) ? data.registros[0] : null;
                if (!contrato) {
                    vista.append(info_prof_vet_sin_registros('El asistente no tiene un contrato vigente con días y horas de trabajo en este lugar de atención.'));
                    sinContrato.append(info_prof_vet_sin_registros('El horario se configura sobre el contrato del asistente; primero debe tener uno vigente.'));
                    return;
                }

                let dias = String(contrato.dias_laborales || '').split(',').map(function(d) { return $.trim(d); }).filter(function(d) { return d; });

                // Los siete días a la vista, marcando los que trabaja
                let semana = $('<div class="info-asistente-semana"></div>');
                $.each(info_asistente_nombres_dias, function(numero, nombre) {
                    let trabaja = dias.indexOf(String(numero)) !== -1;
                    semana.append(
                        $('<span class="info-asistente-dia"></span>')
                            .toggleClass('activo', trabaja)
                            .attr('title', nombre + (trabaja ? ' (trabaja)' : ' (libre)'))
                            .text(nombre.substring(0, 2))
                    );
                });

                let horas = $('<div class="info-asistente-horas"></div>')
                    .append(info_prof_vet_dato('Entrada', info_asistente_hora(contrato.hora_ingreso)))
                    .append(info_prof_vet_dato('Salida', info_asistente_hora(contrato.hora_salida)))
                    .append(info_prof_vet_dato('Inicio colación', info_asistente_hora(contrato.hora_inicio_colacion)))
                    .append(info_prof_vet_dato('Término colación', info_asistente_hora(contrato.hora_termino_colacion)));

                vista.append(semana).append(horas);

                // El formulario parte con el horario vigente
                $('#info_asistente_horario_id_contrato').val(contrato.id);
                $('#info_asistente_horario_id_empleado').val(contrato.id_empleado);
                $('#info_asistente_horario_dias').val(dias).trigger('change');
                $('#info_asistente_horario_entrada').val(info_asistente_hora(contrato.hora_ingreso));
                $('#info_asistente_horario_salida').val(info_asistente_hora(contrato.hora_salida));
                $('#info_asistente_horario_colacion_inicio').val(info_asistente_hora(contrato.hora_inicio_colacion));
                $('#info_asistente_horario_colacion_termino').val(info_asistente_hora(contrato.hora_termino_colacion));
                formulario.removeClass('d-none');
            })
            .fail(function() {
                vista.html('').append(info_prof_vet_mensaje('No fue posible cargar el horario.'));
            });
    }

    // Las horas llegan como HH:MM:SS; solo se usan horas y minutos
    function info_asistente_hora(hora) {
        return String(hora || '').substring(0, 5);
    }

    function info_asistente_guardar_horario() {
        let datos = {
            _token: CSRF_TOKEN,
            id: $('#info_asistente_horario_id_contrato').val(),
            id_empleado: $('#info_asistente_horario_id_empleado').val(),
            dias_laborales: $('#info_asistente_horario_dias').val() || [],
            hora_ingreso: $('#info_asistente_horario_entrada').val(),
            hora_salida: $('#info_asistente_horario_salida').val(),
            hora_inicio_colacion: $('#info_asistente_horario_colacion_inicio').val(),
            hora_termino_colacion: $('#info_asistente_horario_colacion_termino').val(),
        };

        let faltantes = [];
        if (!datos.dias_laborales.length) faltantes.push('Días laborales');
        if (!datos.hora_ingreso) faltantes.push('Hora de entrada');
        if (!datos.hora_salida) faltantes.push('Hora de salida');
        if (!datos.hora_inicio_colacion) faltantes.push('Inicio colación');
        if (!datos.hora_termino_colacion) faltantes.push('Término colación');
        if (faltantes.length) {
            swal({ title: 'Horario del asistente', text: 'Complete: ' + faltantes.join(', ') + '.', icon: 'warning', buttons: 'Aceptar' });
            return;
        }

        let boton = $('#info_asistente_horario_guardar').prop('disabled', true);
        $.post("{{ route('adm_cm.personal.horario.editar') }}", datos)
            .done(function(data) {
                if (data && data.estado == 1) {
                    swal({ title: 'Horario del asistente', text: 'Horario modificado con éxito.', icon: 'success', buttons: 'Aceptar' });
                    info_asistente_cargar_horario();
                    return;
                }
                let mensaje = (data && data.error) ? $.map(data.error, function(texto) { return texto; }).join('\n') : 'No se encontró el horario solicitado.';
                swal({ title: 'Horario del asistente', text: mensaje, icon: 'error', buttons: 'Cerrar' });
            })
            .fail(function() {
                swal({ title: 'Horario del asistente', text: 'No fue posible guardar el horario. Intente nuevamente.', icon: 'error', buttons: 'Cerrar' });
            })
            .always(function() {
                boton.prop('disabled', false);
            });
    }

    // Deja encendidos solo los roles que el usuario tiene hoy
    function info_asistente_marcar_roles() {
        let roles = String(info_asistente_actual.roles || '').split(',');
        $('.info-asistente-rol-check').each(function() {
            $(this).prop('checked', roles.indexOf($(this).attr('data-rol')) !== -1);
        });
    }

    /**
     * Activar un rol quita los demás que estén encendidos; apagarlo solo quita ese.
     * Se pide confirmación antes de tocar los permisos y al terminar se recarga la tabla.
     */
    function info_asistente_cambiar_rol(check) {
        let interruptor = $(check);
        let activar = interruptor.prop('checked');
        let nombreRol = $.trim(interruptor.next('label').text());

        swal({
            title: 'Roles y permisos',
            text: activar
                ? 'El asistente quedará con el rol "' + nombreRol + '" y se quitará el que tenga asignado. ¿Desea continuar?'
                : 'Se quitará el rol "' + nombreRol + '" al asistente. ¿Desea continuar?',
            icon: 'warning',
            buttons: ['Cancelar', 'Continuar'],
        }).then(function(confirmado) {
            if (!confirmado) {
                interruptor.prop('checked', !activar);
                return;
            }

            let cambios = [];
            if (activar) {
                $('.info-asistente-rol-check:checked').not(interruptor).each(function() {
                    cambios.push({ rol: $(this).attr('data-rol'), movimiento: 0 });
                    $(this).prop('checked', false);
                });
            }
            cambios.push({ rol: interruptor.attr('data-rol'), movimiento: activar ? 1 : 0 });

            $('.info-asistente-rol-check').prop('disabled', true);
            let peticiones = $.map(cambios, function(cambio) {
                return $.post("{{ route('adm_cm.personal.asistente.actualizar.rol') }}", {
                    _token: CSRF_TOKEN,
                    id_user: info_asistente_actual.id_usuario,
                    id_tipo: cambio.rol,
                    id_tipo_movimiento: 'Asistente',
                    movimiento: cambio.movimiento,
                });
            });

            $.when.apply($, peticiones)
                .done(function() {
                    // Con una sola petición jQuery entrega (data, estado, xhr); con varias, un arreglo por petición
                    let respuestas = peticiones.length === 1 ? [arguments[0]] : $.map(arguments, function(r) { return [r[0]]; });
                    let fallida = respuestas.filter(function(r) { return !r || r.estado != 1; })[0];
                    if (fallida) {
                        swal({ title: 'Roles y permisos', text: fallida.msj || 'No fue posible actualizar el rol. Intente nuevamente.', icon: 'error', buttons: 'Aceptar' })
                            .then(cargar_tabla_asistentes);
                        return;
                    }
                    swal({ title: 'Roles y permisos', text: 'Rol actualizado con éxito.', icon: 'success', buttons: 'Aceptar' })
                        .then(cargar_tabla_asistentes);
                })
                .fail(function() {
                    swal({ title: 'Roles y permisos', text: 'No fue posible actualizar el rol. Intente nuevamente.', icon: 'error', buttons: 'Aceptar' })
                        .then(cargar_tabla_asistentes);
                });
        });
    }
</script>
