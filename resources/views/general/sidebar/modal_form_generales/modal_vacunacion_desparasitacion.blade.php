<div id="modal_vacunacion_desparasitacion" class="modal fade" tabindex="-1" role="dialog"
    aria-labelledby="modal_vacunacion_desparasitacion_titulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title" id="modal_vacunacion_desparasitacion_titulo">
                        Vacunación y desparasitación
                    </h5>
                    <small class="text-white" id="vac_especie_resumen">Cargando datos de la mascota…</small>
                </div>
                <button type="button" class="close" id="cerrar_modal_vacunacion"
                    data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs-aten nav-fill mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link-aten text-reset active" data-toggle="tab" href="#panel_vacunacion"
                            role="tab">Vacunas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-aten text-reset" data-toggle="tab" href="#panel_desparasitacion"
                            role="tab">Desparasitación</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="panel_vacunacion" role="tabpanel">
                        <div class="card-lineal mb-3">
                            <div class="card-body-lineal">
                                <h6 class="mb-3">Formulario de vacunación</h6>
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="vac_edad_modal">Edad</label>
                                    <input type="text" class="form-control form-control-sm" id="vac_edad_modal" placeholder="Sin fecha de nacimiento" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="vac_fecha_dosis_modal">Fecha de dosis</label>
                                    <input type="date" class="form-control form-control-sm" id="vac_fecha_dosis_modal">
                                </div>
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="vac_nombre_modal">Vacuna</label>
                                    <input type="text" class="form-control form-control-sm" id="vac_nombre_modal" maxlength="255" placeholder="Nombre de la vacuna">
                                </div>
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="vac_proxima_dosis_modal">Próxima dosis</label>
                                    <input type="date" class="form-control form-control-sm" id="vac_proxima_dosis_modal">
                                </div>
                                <fieldset class="form-group d-none" id="vac_felv_modal" disabled>
                                    <legend class="f-14 mb-2">¿Se realizó el test de leucemia felina?</legend>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input" id="vac_felv_si" name="vac_felv_test" value="si">
                                        <label class="custom-control-label" for="vac_felv_si">Sí</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input" id="vac_felv_no" name="vac_felv_test" value="no">
                                        <label class="custom-control-label" for="vac_felv_no">No</label>
                                    </div>
                                    <div class="form-group mt-3 mb-0 d-none" id="vac_felv_resultado_grupo">
                                        <label class="floating-label-activo-sm" for="vac_felv_resultado">Resultado</label>
                                        <select class="form-control form-control-sm" id="vac_felv_resultado" disabled>
                                            <option value="">Seleccione</option>
                                            <option value="positivo">Positivo</option>
                                            <option value="negativo">Negativo</option>
                                        </select>
                                    </div>
                                </fieldset>
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="vac_observaciones_modal">Observaciones</label>
                                    <textarea class="form-control form-control-sm caja-texto" id="vac_observaciones_modal" maxlength="2000" rows="2"></textarea>
                                </div>
                                <button type="button" class="btn btn-sm btn-info" id="btn_add_vacuna_modal">Guardar</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-sm" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Fecha dosis</th>
                                        <th>Vacuna</th>
                                        <th>Próxima dosis</th>
                                        <th>Realizado por</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_vacunas_mascotas_body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="panel_desparasitacion" role="tabpanel">
                        <div class="alert alert-info py-2 mb-3" role="status">
                            <strong>Fecha de última desparasitación:</strong> <span id="des_ultima_fecha_modal">Sin registro</span>
                        </div>

                        <div class="card-lineal mb-3">
                            <div class="card-body-lineal">
                                <div class="form-row">
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_especie_modal">Especie</label>
                                        <input type="text" class="form-control form-control-sm" id="des_especie_modal" readonly>
                                    </div>
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_raza_modal">Raza</label>
                                        <input type="text" class="form-control form-control-sm" id="des_raza_modal" readonly>
                                    </div>
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_peso_modal">Peso (kg)</label>
                                        <input type="number" min="0" max="999.99" step="0.01" class="form-control form-control-sm" id="des_peso_modal">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_fecha_dosis_modal">Fecha de aplicación</label>
                                        <input type="date" class="form-control form-control-sm" id="des_fecha_dosis_modal">
                                    </div>
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_antiparasitario_modal">Producto antiparasitario</label>
                                        <input type="text" class="form-control form-control-sm" id="des_antiparasitario_modal" maxlength="255">
                                    </div>
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_tipo_modal">Tipo</label>
                                        <select class="form-control form-control-sm" id="des_tipo_modal">
                                            <option value="">Seleccione</option>
                                            <option value="Externo">Externo</option>
                                            <option value="Interno">Interno</option>
                                            <option value="Interno y Externo">Interno y externo</option>
                                            <option value="Otro">Otros</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_lote_modal">Lote (opcional)</label>
                                        <input type="text" class="form-control form-control-sm" id="des_lote_modal" maxlength="120">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-12 d-none" id="des_tipo_otro_grupo">
                                        <label class="floating-label-activo-sm" for="des_tipo_otro_modal">Especifique el tipo de desparasitación</label>
                                        <textarea class="form-control form-control-sm caja-texto" id="des_tipo_otro_modal" maxlength="255" rows="2" disabled></textarea>
                                    </div>
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_dosis_modal">Dosis administrada</label>
                                        <input type="text" class="form-control form-control-sm" id="des_dosis_modal" maxlength="120">
                                    </div>
                                    <div class="form-group col-sm-6 col-md-3">
                                        <label class="floating-label-activo-sm" for="des_proxima_dosis_modal">Próxima dosis</label>
                                        <input type="date" class="form-control form-control-sm" id="des_proxima_dosis_modal">
                                    </div>
                                    <div class="form-group col-sm-6 col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-info btn-block"
                                            id="btn_add_desparasitacion_modal">+ Añadir</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-sm" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Producto</th>
                                        <th>Cobertura / vía</th>
                                        <th>Dosis / peso</th>
                                        <th>Próximo control</th>
                                        <th>Realizado por</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_desparasitacion_body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Abrir y cerrar pasan siempre por Bootstrap: así el fondo oscuro, el scroll de la página
// y el estado interno del modal se limpian solos. El manejo manual queda solo de respaldo.
window.abrirVacunacionVeterinaria = function () {
    var modal = document.getElementById('modal_vacunacion_desparasitacion');
    if (!modal) {
        console.error('No se encontró modal_vacunacion_desparasitacion');
        return;
    }

    if (window.jQuery && window.jQuery.fn.modal) {
        window.jQuery(modal).modal('show');
        return;
    }

    modal.style.display = 'block';
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
    modal.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');

    if (!document.querySelector('.modal-backdrop[data-modal-vacunacion]')) {
        var fondo = document.createElement('div');
        fondo.className = 'modal-backdrop fade show';
        fondo.setAttribute('data-modal-vacunacion', '1');
        document.body.appendChild(fondo);
    }
};

window.cerrarVacunacionVeterinaria = function () {
    var modal = document.getElementById('modal_vacunacion_desparasitacion');
    var interruptor = document.getElementById('abrir_vacunacion_desparasitacion');
    if (interruptor) interruptor.checked = false;

    if (modal && window.jQuery && window.jQuery.fn.modal) {
        window.jQuery(modal).modal('hide');
        return;
    }

    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
    }
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop[data-modal-vacunacion]').forEach(function (fondo) {
        fondo.remove();
    });
};

(function () {
    'use strict';

    var especieActual = 'otra';
    var fechaNacimiento = '';
    function escapar(valor) {
        return $('<div>').text(valor == null ? '' : String(valor)).html();
    }

    function idMascota() {
        if ($('#id_mascota_fc').val()) return $('#id_mascota_fc').val();
        var params = new URLSearchParams(window.location.search || '');
        return params.get('id_mascota') || params.get('id_dependiente_activo') || '';
    }

    function urlMascota(nombreRuta) {
        return nombreRuta.replace('__ID__', idMascota());
    }

    function token() {
        return (typeof CSRF_TOKEN !== 'undefined' && CSRF_TOKEN) ||
            $('meta[name="csrf-token"]').attr('content');
    }

    function fechaCorta(fecha) {
        if (!fecha) return '-';
        var p = fecha.split('-');
        return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : fecha;
    }

    function calcularEdad(fecha, dosis) {
        if (!fecha) return '';
        var nacimiento = new Date(fecha + 'T00:00:00');
        var hoy = dosis ? new Date(dosis + 'T00:00:00') : new Date();
        var meses = (hoy.getFullYear() - nacimiento.getFullYear()) * 12 +
            hoy.getMonth() - nacimiento.getMonth();
        if (hoy.getDate() < nacimiento.getDate()) meses--;
        if (meses < 0) return '';
        var anos = Math.floor(meses / 12);
        var resto = meses % 12;
        return [anos ? anos + (anos === 1 ? ' año' : ' años') : '',
            resto || !anos ? resto + (resto === 1 ? ' mes' : ' meses') : ''].filter(Boolean).join(' ');
    }

    function normalizarEspecie(nombre) {
        nombre = String(nombre || '').toLowerCase();
        if (nombre.indexOf('canin') !== -1 || nombre.indexOf('perro') !== -1) return 'canina';
        if (nombre.indexOf('felin') !== -1 || nombre.indexOf('gato') !== -1) return 'felina';
        return 'otra';
    }

    function actualizarResultadoFelv() {
        var mostrar = especieActual === 'felina' && $('input[name="vac_felv_test"]:checked').val() === 'si';
        $('#vac_felv_resultado_grupo').toggleClass('d-none', !mostrar);
        $('#vac_felv_resultado').prop('disabled', !mostrar);
        if (!mostrar) $('#vac_felv_resultado').val('');
    }

    function configurarEspecie(mascota) {
        especieActual = normalizarEspecie(mascota.tipo_especie || mascota.especie);
        fechaNacimiento = mascota.fecha_nacimiento || '';
        $('#vac_edad_modal').val(calcularEdad(fechaNacimiento, $('#vac_fecha_dosis_modal').val()));
        $('#vac_especie_resumen').text(
            (mascota.nombre || 'Mascota') + ' · ' + (mascota.especie || 'Otra especie')
        );
        var felina = especieActual === 'felina';
        $('#vac_felv_modal').toggleClass('d-none', !felina).prop('disabled', !felina);
        if (!felina) $('input[name="vac_felv_test"]').prop('checked', false);
        actualizarResultadoFelv();
        $('#des_especie_modal').val(mascota.especie || '');
        $('#des_raza_modal').val(mascota.raza || '');
    }

    function renderVacunas(items) {
        var $body = $('#tabla_vacunas_mascotas_body').empty();
        if (!items.length) {
            $body.append('<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>');
            return;
        }
        items.forEach(function (item) {
            $body.append('<tr><td>' + fechaCorta(item.fecha_dosis) + '</td><td>' +
                escapar(item.vacuna || '-') +
                (item.observaciones ? '<small class="d-block text-muted">' + escapar(item.observaciones) + '</small>' : '') +
                (item.felv_test ? '<small class="d-block text-muted">Test FeLV: ' +
                    (item.felv_test === 'si' ? 'Sí' + (item.felv_resultado ? ' · ' + escapar(item.felv_resultado) : '') : 'No') + '</small>' : '') +
                '</td><td>' + fechaCorta(item.proxima_dosis) + '</td><td>' +
                escapar(item.realizado_por || '-') + '</td></tr>');
        });
    }

    function renderDesparasitaciones(items, ultimaFecha) {
        var fechaReciente = items.reduce(function (fecha, item) {
            return item.fecha_dosis && item.fecha_dosis > fecha ? item.fecha_dosis : fecha;
        }, ultimaFecha || '');
        $('#des_ultima_fecha_modal').text(fechaReciente ? fechaCorta(fechaReciente) : 'Sin registro');
        var $body = $('#tabla_desparasitacion_body').empty();
        if (!items.length) {
            $body.append('<tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>');
            return;
        }
        items.forEach(function (item) {
            $body.append('<tr><td>' + fechaCorta(item.fecha_dosis) + '</td><td>' +
                escapar(item.antiparasitario || '-') + '</td><td>' +
                escapar((item.tipo === 'Otro' && item.tipo_otro ? item.tipo_otro : (item.tipo || '-')) +
                    (item.via ? ' / ' + item.via : '')) + '</td><td>' +
                escapar((item.dosis || '-') + (item.peso ? ' · ' + item.peso + ' kg' : '')) + '</td><td>' +
                fechaCorta(item.proxima_dosis) + '</td><td>' +
                escapar(item.realizado_por || '-') + '</td></tr>');
        });
    }

    function cargar() {
        configurarEspecie({
            nombre: 'Mascota',
            especie: '',
            fecha_nacimiento: ''
        });
        renderVacunas([]);
        renderDesparasitaciones([]);

        if (!idMascota()) {
            $('#vac_especie_resumen').text(
                'No se pudo identificar la mascota'
            );
            return;
        }
        $.get(urlMascota("{{ route('paciente.mascotas.registros_sanitarios', ['mascotaId' => '__ID__']) }}"))
            .done(function (respuesta) {
                configurarEspecie(respuesta.mascota || {});
                renderVacunas(respuesta.vacunas || []);
                renderDesparasitaciones(respuesta.desparasitaciones || [], respuesta.mascota?.ultima_desparasitacion);
            })
            .fail(function (xhr) {
                $('#vac_especie_resumen').text(
                    'No fue posible cargar los antecedentes de la mascota'
                );
                console.error('Error al cargar registros sanitarios', xhr.responseText);
            });
    }

    $(function () {
        cargar();
    });

    $(document)
        .on('shown.bs.modal', '#modal_vacunacion_desparasitacion', cargar)
        .on('hidden.bs.modal', '#modal_vacunacion_desparasitacion', function () {
            $('#abrir_vacunacion_desparasitacion').prop('checked', false);
            if (!$('.modal.show').length) {
                $('body').removeClass('modal-open').css('padding-right', '');
                $('.modal-backdrop').remove();
            }
        })
        .on('change', 'input[name="vac_felv_test"]', actualizarResultadoFelv)
        .on('change', '#vac_fecha_dosis_modal', function () {
            $('#vac_edad_modal').val(calcularEdad(fechaNacimiento, this.value));
        })
        .on('click', '#btn_add_vacuna_modal', function () {
            var felvTest = especieActual === 'felina' ? $('input[name="vac_felv_test"]:checked').val() : '';
            if (especieActual === 'felina' && !felvTest) {
                mostrarError({responseJSON: {msj: 'Indique si se realizó el test de leucemia felina.'}});
                return;
            }
            var $boton = $(this).prop('disabled', true);
            $.post(urlMascota("{{ route('paciente.mascotas.vacunas.guardar', ['mascotaId' => '__ID__']) }}"), {
                _token: token(),
                edad: $('#vac_edad_modal').val(),
                fecha_dosis: $('#vac_fecha_dosis_modal').val(),
                vacuna: $('#vac_nombre_modal').val(),
                proxima_dosis: $('#vac_proxima_dosis_modal').val(),
                especie: especieActual,
                observaciones: $('#vac_observaciones_modal').val(),
                felv_test: felvTest,
                felv_resultado: especieActual === 'felina' ? $('#vac_felv_resultado').val() : ''
            }).done(function (r) {
                if (parseInt(r.estado || 0, 10) !== 1) {
                    mostrarError({responseJSON: r});
                    return;
                }
                renderVacunas(r.vacunas || []);
                $('#vac_nombre_modal,#vac_fecha_dosis_modal,#vac_proxima_dosis_modal,#vac_observaciones_modal').val('');
                $('#vac_edad_modal').val(calcularEdad(fechaNacimiento));
                $('input[name="vac_felv_test"]').prop('checked', false);
                actualizarResultadoFelv();
                mostrarGuardado(r.msj || 'Vacuna guardada correctamente.');
            }).fail(mostrarError).always(function () {
                $boton.prop('disabled', false);
            });
        })
        .on('change', '#des_tipo_modal', function () {
            var otro = this.value === 'Otro';
            $('#des_tipo_otro_grupo').toggleClass('d-none', !otro);
            var $detalle = $('#des_tipo_otro_modal').prop('disabled', !otro);
            if (otro) $detalle.trigger('focus');
            else $detalle.val('');
        })
        .on('click', '#btn_add_desparasitacion_modal', function () {
            var tipo = $('#des_tipo_modal').val();
            var tipoOtro = tipo === 'Otro' ? $('#des_tipo_otro_modal').val().trim() : '';
            if (tipo === 'Otro' && !tipoOtro) {
                mostrarError({responseJSON: {msj: 'Especifique el otro tipo de desparasitación.'}});
                return;
            }
            var $boton = $(this).prop('disabled', true);
            $.post(urlMascota("{{ route('paciente.mascotas.desparasitaciones.guardar', ['mascotaId' => '__ID__']) }}"), {
                _token: token(),
                fecha_dosis: $('#des_fecha_dosis_modal').val(),
                antiparasitario: $('#des_antiparasitario_modal').val(),
                tipo: tipo,
                tipo_otro: tipoOtro,
                peso: $('#des_peso_modal').val(),
                dosis: $('#des_dosis_modal').val(),
                lote: $('#des_lote_modal').val(),
                proxima_dosis: $('#des_proxima_dosis_modal').val()
            }).done(function (r) {
                if (parseInt(r.estado || 0, 10) !== 1) {
                    mostrarError({responseJSON: r});
                    return;
                }
                renderDesparasitaciones(r.desparasitaciones || [], r.ultima_desparasitacion);
                $('#des_fecha_dosis_modal,#des_antiparasitario_modal,#des_peso_modal,#des_dosis_modal,#des_lote_modal,#des_proxima_dosis_modal').val('');
                $('#des_tipo_modal').val('').trigger('change');
                mostrarGuardado(r.msj || 'Desparasitación guardada correctamente.');
            }).fail(mostrarError).always(function () {
                $boton.prop('disabled', false);
            });
        });

    function mostrarGuardado(mensaje) {
        swal({
            title: 'Registro guardado',
            text: mensaje,
            icon: 'success',
            button: 'Aceptar'
        });
    }

    function mostrarError(xhr) {
        var errores = xhr.responseJSON && xhr.responseJSON.error;
        var campo = errores && Object.keys(errores)[0];
        var mensaje = campo
            ? errores[campo][0]
            : (xhr.responseJSON && (xhr.responseJSON.msj || xhr.responseJSON.message)) || 'No se pudo guardar el registro.';
        swal({title: mensaje, icon: 'warning'});
    }
})();
</script>

@include('general.secciones_ficha.partials.sanitarios_solo_lectura')
