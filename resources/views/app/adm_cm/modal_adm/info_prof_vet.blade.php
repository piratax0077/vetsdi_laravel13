<!--Información del profesional: datos personales, contacto, cuentas bancarias, horario y convenios-->
<div id="info_prof_vet" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="info_prof_vet_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="info_prof_vet_titulo">Informaci&oacute;n</h5>
                <button type="button" class="close text-white" aria-label="Cerrar" onclick="cerrar_info_prof_vet();"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="nav pill-moderno" id="info_prof_vet_pills" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info_prof_vet_personal" role="tab">Informaci&oacute;n personal</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#info_prof_vet_cuentas" role="tab">Cuentas bancarias</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#info_prof_vet_horario" role="tab">Horario de atenci&oacute;n y convenios</a></li>
                </ul>

                <div class="tab-content">
                    <!--Información personal: mismos datos que el perfil del profesional-->
                    <div class="tab-pane fade show active" id="info_prof_vet_personal" role="tabpanel">
                        <div id="info_prof_vet_personal_cargando"></div>
                        <div id="info_prof_vet_personal_contenido" class="d-none">
                            <!--Ficha: nombre completo, con RUT y sexo debajo-->
                            <div class="info-prof-vet-ficha">
                                <div class="info-prof-vet-nombre" id="info_prof_vet_nombre"></div>
                                <div class="info-prof-vet-rut">RUT <strong id="info_prof_vet_rut"></strong></div>
                                <div class="info-prof-vet-rut">Sexo <strong id="info_prof_vet_sexo"></strong></div>
                            </div>

                            <div class="row info-prof-vet-bloques">
                                <div class="col-lg-4 col-md-6">
                                    <div class="info-prof-vet-bloque">
                                        <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-user"></i>Datos personales</h6>
                                        <div id="info_prof_vet_datos"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <div class="info-prof-vet-bloque">
                                        <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-phone"></i>Contacto</h6>
                                        <div id="info_prof_vet_contacto"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="info-prof-vet-bloque">
                                        <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-map-pin"></i>Residencia</h6>
                                        <div id="info_prof_vet_residencia"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Cuentas bancarias-->
                    <div class="tab-pane fade" id="info_prof_vet_cuentas" role="tabpanel">
                        <div class="row" id="info_prof_vet_cuentas_lista"></div>
                    </div>

                    <!--Horario de atención y convenios: ambos dependen del lugar elegido-->
                    <div class="tab-pane fade" id="info_prof_vet_horario" role="tabpanel">
                        <!--Solo aparece si el profesional atiende en más de una sucursal-->
                        <div class="form-group fill d-none" id="info_prof_vet_lugar_grupo">
                            <label class="floating-label-activo-sm" for="info_prof_vet_lugar">Lugar de atenci&oacute;n</label>
                            <select class="form-control form-control-sm" id="info_prof_vet_lugar" onchange="info_prof_vet_cambiar_lugar();"></select>
                        </div>

                        <div class="info-prof-vet-bloque mb-3">
                            <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-file-text"></i>Convenios</h6>
                            <div id="info_prof_vet_convenios_vacio"></div>
                            <div class="table-responsive" id="info_prof_vet_convenios_tabla">
                                <table class="table table-sm info-prof-vet-tabla mb-0">
                                    <thead>
                                        <tr>
                                            <th>Convenio</th>
                                            <th>Tipo de atenci&oacute;n</th>
                                            <th class="text-center">Porcentaje</th>
                                            <th class="text-right">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody id="info_prof_vet_convenios_filas"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="info-prof-vet-bloque">
                            <h6 class="info-prof-vet-bloque-titulo"><i class="feather icon-clock"></i>Horario de atenci&oacute;n</h6>
                            <div id="info_prof_vet_horario_vacio"></div>
                            <div class="table-responsive" id="info_prof_vet_horario_tabla">
                                <table class="table table-sm info-prof-vet-tabla mb-0">
                                    <thead>
                                        <tr>
                                            <th>D&iacute;as</th>
                                            <th>Hora inicio</th>
                                            <th>Hora t&eacute;rmino</th>
                                        </tr>
                                    </thead>
                                    <tbody id="info_prof_vet_horario_filas"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="cerrar_info_prof_vet();">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Un dato compacto: etiqueta chica arriba y valor debajo; el texto va con .text() para no interpretar HTML
    function info_prof_vet_dato(etiqueta, valor) {
        let vacio = (valor === null || valor === undefined || valor === '');
        let dato = $('<div class="info-prof-vet-dato"><span class="info-prof-vet-dato-etiqueta"></span><span class="info-prof-vet-dato-valor"></span></div>');
        dato.find('.info-prof-vet-dato-etiqueta').text(etiqueta);
        dato.find('.info-prof-vet-dato-valor').text(vacio ? 'Sin información' : valor).toggleClass('sin-dato', vacio);
        return dato;
    }

    // Aviso cuando no hay datos: ícono chico, título y una explicación de qué falta
    function info_prof_vet_sin_registros(explicacion) {
        let aviso = $(
            '<div class="info-prof-vet-vacio">' +
            '    <span class="info-prof-vet-vacio-icono"><i class="feather icon-alert-circle"></i></span>' +
            '    <div>' +
            '        <p class="info-prof-vet-vacio-titulo">Sin registros</p>' +
            '        <p class="info-prof-vet-vacio-texto"></p>' +
            '    </div>' +
            '</div>'
        );
        aviso.find('.info-prof-vet-vacio-texto').text(explicacion || '');
        return aviso;
    }

    function info_prof_vet_mensaje(texto) {
        return $('<p class="info-prof-vet-mensaje"></p>').text(texto);
    }

    /**
     * Recibe { titulo, id_profesional, id_usuario, id_lugar_atencion, lugares }.
     * Datos personales, cuentas, horario y convenios se consultan al abrir.
     */
    function abrir_info_prof_vet(info) {
        info = info || {};

        $('#info_prof_vet_titulo').text(info.titulo || 'Información');
        $('#info_prof_vet_pills a[href="#info_prof_vet_personal"]').tab('show');

        $('#info_prof_vet_personal_contenido').addClass('d-none');
        $('#info_prof_vet_personal_cargando').html('').append(info_prof_vet_mensaje('Cargando información del profesional...'));
        $('#info_prof_vet_cuentas_lista').html('').append($('<div class="col-12"></div>').append(info_prof_vet_mensaje('Cargando cuentas bancarias...')));

        // Lugares donde atiende dentro del centro; el select solo se muestra si hay más de uno
        let lugares = info.lugares || [];
        let idLugar = info.id_lugar_atencion;
        let select = $('#info_prof_vet_lugar').html('');
        $.each(lugares, function(i, lugar) {
            select.append($('<option></option>').val(lugar.id).text(lugar.nombre));
        });
        if (lugares.length && !lugares.some(function(l) { return String(l.id) === String(idLugar); })) {
            idLugar = lugares[0].id;
        }
        select.val(String(idLugar)).data('id_profesional', info.id_profesional);
        $('#info_prof_vet_lugar_grupo').toggleClass('d-none', lugares.length < 2);

        info_prof_vet_cargar_contacto(info.id_profesional);
        info_prof_vet_cargar_cuentas(info.id_usuario);
        info_prof_vet_cargar_horario(info.id_profesional, idLugar);
        info_prof_vet_cargar_convenios(info.id_profesional, idLugar);

        $('#info_prof_vet').modal('show');
    }

    function cerrar_info_prof_vet() {
        $('#info_prof_vet').modal('hide');
    }

    function info_prof_vet_cargar_contacto(id_profesional) {
        let cargando = $('#info_prof_vet_personal_cargando');
        let contenido = $('#info_prof_vet_personal_contenido');

        $.get("{{ route('adm_cm.profesional_buscar') }}", { id_profesional: id_profesional })
            .done(function(data) {
                cargando.html('');
                if (!data || data.estado != 1) {
                    cargando.append(info_prof_vet_mensaje('No fue posible cargar la información del profesional.'));
                    return;
                }
                let registro = data.registro || {};
                let perfil = data.perfil || {};
                let residencia = data.residencia || {};

                // Ficha superior
                let nombreCompleto = [perfil.nombres, perfil.apellido_uno, perfil.apellido_dos].filter(function(p) { return p; }).join(' ');
                $('#info_prof_vet_nombre').text(nombreCompleto || 'Sin información');
                $('#info_prof_vet_rut').text((perfil.rut || 'Sin información').toUpperCase());
                $('#info_prof_vet_sexo').text(perfil.sexo || 'Sin información');

                // Mismos datos que la tarjeta "Datos personales" del perfil
                $('#info_prof_vet_datos').html('')
                    .append(info_prof_vet_dato('Profesión', perfil.profesion))
                    .append(info_prof_vet_dato('Especialidad', perfil.especialidad))
                    .append(info_prof_vet_dato('Sub especialidad', perfil.sub_especialidad));

                // telefono_uno es el teléfono principal y telefono_dos el opcional (celular)
                $('#info_prof_vet_contacto').html('')
                    .append(info_prof_vet_dato('Correo electrónico', registro.email))
                    .append(info_prof_vet_dato('Teléfono', registro.telefono_uno))
                    .append(info_prof_vet_dato('Celular', registro.telefono_dos));

                $('#info_prof_vet_residencia').html('')
                    .append(info_prof_vet_dato('Región', residencia.region))
                    .append(info_prof_vet_dato('Comuna', residencia.comuna))
                    .append(info_prof_vet_dato('Dirección', residencia.direccion));

                contenido.removeClass('d-none');
            })
            .fail(function() {
                cargando.html('').append(info_prof_vet_mensaje('No fue posible cargar la información del profesional.'));
            });
    }

    function info_prof_vet_cargar_cuentas(id_usuario) {
        let contenedor = $('#info_prof_vet_cuentas_lista');
        $.get("{{ route('profesional.liquidacion_ver_profesional') }}", { id_seccion: id_usuario })
            .done(function(data) {
                contenedor.html('');
                if (!data || data.estado != 1 || !data.registros || !data.registros.length) {
                    contenedor.append($('<div class="col-12"></div>').append(
                        info_prof_vet_sin_registros('El profesional aún no tiene cuentas bancarias registradas.')
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

    function info_prof_vet_cambiar_lugar() {
        let select = $('#info_prof_vet_lugar');
        info_prof_vet_cargar_horario(select.data('id_profesional'), select.val());
        info_prof_vet_cargar_convenios(select.data('id_profesional'), select.val());
    }

    // Convenios del profesional en el lugar elegido, con el formato de Administración comercial
    function info_prof_vet_cargar_convenios(id_profesional, id_lugar_atencion) {
        let cuerpo = $('#info_prof_vet_convenios_filas');
        let tabla = $('#info_prof_vet_convenios_tabla').removeClass('d-none');
        let vacio = $('#info_prof_vet_convenios_vacio').html('');
        cuerpo.html('<tr><td colspan="4" class="text-center text-muted">Cargando convenios...</td></tr>');

        $.post("{{ route('adm_cm.dame_convenio_profesional') }}", {
            id_profesional: id_profesional,
            id_lugar_atencion: id_lugar_atencion,
            _token: CSRF_TOKEN,
        })
            .done(function(data) {
                cuerpo.html('');
                let convenios = (data && data.estado == 1) ? (data.convenios || [data.profesional_convenios]) : [];
                if (!convenios.length) {
                    tabla.addClass('d-none');
                    vacio.append(info_prof_vet_sin_registros('No hay convenios registrados para este profesional en el lugar de atención seleccionado.'));
                    return;
                }
                $.each(convenios, function(i, convenio) {
                    let valor = Math.round(parseFloat(convenio.valor) || 0).toLocaleString('es-CL');
                    let porcentaje = (convenio.porcentaje === null || convenio.porcentaje === '') ? '-' : convenio.porcentaje + '%';

                    let fila = $('<tr><td></td><td></td><td class="text-center"></td><td class="text-right"></td></tr>');
                    fila.children().eq(0).text(convenio.convenios || '');
                    fila.children().eq(1).text(convenio.tipo_atencion || '');
                    fila.children().eq(2).text(porcentaje);
                    fila.children().eq(3).text('$' + valor);
                    cuerpo.append(fila);
                });
            })
            .fail(function() {
                cuerpo.html('<tr><td colspan="4" class="text-center text-muted">No fue posible cargar los convenios.</td></tr>');
            });
    }

    function info_prof_vet_cargar_horario(id_profesional, id_lugar_atencion) {
        let cuerpo = $('#info_prof_vet_horario_filas');
        $('#info_prof_vet_horario_vacio').html('');
        $('#info_prof_vet_horario_tabla').removeClass('d-none');
        cuerpo.html('<tr><td colspan="3" class="text-center text-muted">Cargando horario...</td></tr>');
        let nombresDias = { 0: 'Domingo', 1: 'Lunes', 2: 'Martes', 3: 'Miércoles', 4: 'Jueves', 5: 'Viernes', 6: 'Sábado', 7: 'Domingo' };

        $.get("{{ route('adm_cm.prof_horario_lugar_atencion') }}", { id_profesional: id_profesional, id_lugar_atencion: id_lugar_atencion })
            .done(function(data) {
                cuerpo.html('');
                if (!data || !data.length) {
                    // Sin horario no se muestra la tabla vacía, solo el aviso
                    $('#info_prof_vet_horario_tabla').addClass('d-none');
                    $('#info_prof_vet_horario_vacio').append(info_prof_vet_sin_registros('El profesional no tiene días ni horas de atención configurados en el lugar de atención seleccionado.'));
                    return;
                }
                $.each(data, function(i, bloque) {
                    let dias = String(bloque.dia || '').split(',').map(function(d) {
                        return nombresDias[$.trim(d)] || '';
                    }).filter(function(d) { return d; }).join(', ');

                    // Las horas llegan como HH:MM:SS; solo se muestran horas y minutos
                    let fila = $('<tr><td></td><td></td><td></td></tr>');
                    fila.children().eq(0).text(dias);
                    fila.children().eq(1).text(String(bloque.hora_inicio || '').substring(0, 5));
                    fila.children().eq(2).text(String(bloque.hora_termino || '').substring(0, 5));
                    cuerpo.append(fila);
                });
            })
            .fail(function() {
                cuerpo.html('<tr><td colspan="3" class="text-center text-muted">No fue posible cargar el horario.</td></tr>');
            });
    }
</script>
