<div id="modal_vacunacion_desparasitacion" class="modal fade" tabindex="-1" role="dialog"
    aria-labelledby="modal_vacunacion_desparasitacion_titulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <div>
                    <h5 class="modal-title text-white" id="modal_vacunacion_desparasitacion_titulo">
                        Vacunación y desparasitación
                    </h5>
                    <small class="text-white" id="vac_especie_resumen">Cargando datos de la mascota…</small>
                </div>
                <button type="button" class="close" id="cerrar_modal_vacunacion"
                    onclick="cerrarVacunacionVeterinaria(); return false;"
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
                        <input type="hidden" id="vac_edad_modal">

                        <div class="card-lineal mb-3">
                            <div class="card-body-lineal">
                                <div class="form-row">
                                    <div class="form-group col-md-6" id="grupo_vacunas_especie">
                                        <label class="floating-label-activo-sm" id="vac_esquema_label">
                                            Esquema principal
                                        </label>
                                        <select class="form-control form-control-sm" id="vac_esquema_modal">
                                            <option value="">Seleccione una vacuna</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="floating-label-activo-sm">Vacunas opcionales</label>
                                        <select class="form-control form-control-sm" id="vac_opcional_modal">
                                            <option value="">Seleccione una vacuna opcional</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-4">
                                        <label class="floating-label-activo-sm">Vacuna aplicada</label>
                                        <input type="text" class="form-control form-control-sm" id="vac_nombre_modal"
                                            placeholder="Nombre comercial o vacuna">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Fecha dosis</label>
                                        <input type="date" class="form-control form-control-sm" id="vac_fecha_dosis_modal">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Próxima dosis</label>
                                        <input type="date" class="form-control form-control-sm" id="vac_proxima_dosis_modal">
                                    </div>
                                    <div class="form-group col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-info btn-block"
                                            id="btn_add_vacuna_modal">+ Añadir</button>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    La edad se calcula automáticamente y se guarda de forma interna para el carné.
                                </small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-sm" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Fecha dosis</th>
                                        <th>Vacuna</th>
                                        <th>Próxima dosis</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_vacunas_mascotas_body"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="panel_desparasitacion" role="tabpanel">
                        <div class="alert alert-info py-2 mb-3" id="des_recomendacion_modal">
                            Seleccione el perfil de la mascota para consultar el esquema sugerido.
                        </div>

                        <div class="card-lineal mb-3">
                            <div class="card-body-lineal">
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Especie</label>
                                        <select class="form-control form-control-sm" id="des_especie_modal">
                                            <option value="canina">Canina</option>
                                            <option value="felina">Felina</option>
                                            <option value="otra">Otra especie</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Etapa</label>
                                        <select class="form-control form-control-sm" id="des_etapa_modal">
                                            <option value="">Calcular según nacimiento</option>
                                            <option value="cachorro_temprano">Cachorro/gatito hasta 12 semanas</option>
                                            <option value="juvenil">3 a 6 meses</option>
                                            <option value="adulto">Desde 6 meses</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Estilo de vida / riesgo</label>
                                        <select class="form-control form-control-sm" id="des_riesgo_modal">
                                            <option value="bajo">Interior / bajo riesgo</option>
                                            <option value="alto">Exterior, parques, campo o alto riesgo</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Esquema sugerido</label>
                                        <select class="form-control form-control-sm" id="des_producto_sugerido_modal">
                                            <option value="">Seleccione una alternativa</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Fecha aplicación</label>
                                        <input type="date" class="form-control form-control-sm" id="des_fecha_dosis_modal">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Producto / principio activo</label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="des_antiparasitario_modal">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Cobertura</label>
                                        <select class="form-control form-control-sm" id="des_tipo_modal">
                                            <option value="">Seleccione</option>
                                            <option value="Interno">Interno</option>
                                            <option value="Externo">Externo</option>
                                            <option value="Interno y Externo">Interno y externo</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Vía</label>
                                        <select class="form-control form-control-sm" id="des_via_modal">
                                            <option value="">Seleccione</option>
                                            <option value="Oral">Oral</option>
                                            <option value="Tópica">Tópica</option>
                                            <option value="Inyectable">Inyectable</option>
                                            <option value="Collar">Collar</option>
                                            <option value="Otra">Otra</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-2">
                                        <label class="floating-label-activo-sm">Peso (kg)</label>
                                        <input type="number" min="0" step="0.01" class="form-control form-control-sm"
                                            id="des_peso_modal">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Dosis administrada</label>
                                        <input type="text" class="form-control form-control-sm" id="des_dosis_modal"
                                            placeholder="Ej.: 1 comprimido / 0,5 ml">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label class="floating-label-activo-sm">Lote</label>
                                        <input type="text" class="form-control form-control-sm" id="des_lote_modal">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label class="floating-label-activo-sm">Próximo control</label>
                                        <input type="date" class="form-control form-control-sm"
                                            id="des_proxima_dosis_modal">
                                    </div>
                                    <div class="form-group col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-info btn-block"
                                            id="btn_add_desparasitacion_modal">+ Añadir</button>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    La pauta es una ayuda clínica. Antes de administrar, valide especie, edad mínima,
                                    peso, registro SAG, contraindicaciones y ficha técnica vigente del producto.
                                </small>
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
window.abrirVacunacionVeterinaria = function () {
    var modal = document.getElementById('modal_vacunacion_desparasitacion');
    if (!modal) {
        console.error('No se encontró modal_vacunacion_desparasitacion');
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

    if (window.jQuery) {
        window.jQuery(modal).trigger('shown.bs.modal');
    }
};

window.cerrarVacunacionVeterinaria = function () {
    var modal = document.getElementById('modal_vacunacion_desparasitacion');
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
    var interruptor = document.getElementById('abrir_vacunacion_desparasitacion');
    if (interruptor) interruptor.checked = false;
};

(function () {
    'use strict';

    var esquemas = {
        canina: [
            ['6 a 8 semanas · Séxtuple 1ª dosis', 21],
            ['9 a 11 semanas · Séxtuple 2ª dosis', 21],
            ['12 a 14 semanas · Séxtuple 3ª dosis + Antirrábica', 21],
            ['15 a 17 semanas · Refuerzo final (según indicación veterinaria)', 365],
            ['Anual · Refuerzo Séxtuple + Antirrábica', 365]
        ],
        felina: [
            ['6 a 8 semanas · Triple felina 1ª dosis', 21],
            ['9 a 11 semanas · Triple felina 2ª dosis', 21],
            ['12 a 14 semanas · Triple felina 3ª dosis + Antirrábica', 21],
            ['15 a 17 semanas · Refuerzo final (según indicación veterinaria)', 365],
            ['Cada 1 a 3 años · Refuerzo Triple felina + Antirrábica', 365]
        ],
        otra: [
            ['CANINO · 6 a 8 semanas · Séxtuple 1ª dosis', 21],
            ['CANINO · 9 a 11 semanas · Séxtuple 2ª dosis', 21],
            ['CANINO · 12 a 14 semanas · Séxtuple 3ª dosis + Antirrábica', 21],
            ['CANINO · Anual · Refuerzo Séxtuple + Antirrábica', 365],
            ['FELINO · 6 a 8 semanas · Triple felina 1ª dosis', 21],
            ['FELINO · 9 a 11 semanas · Triple felina 2ª dosis', 21],
            ['FELINO · 12 a 14 semanas · Triple felina 3ª dosis + Antirrábica', 21],
            ['FELINO · Cada 1 a 3 años · Refuerzo Triple felina + Antirrábica', 365],
            ['OTRA ESPECIE · Vacuna según indicación veterinaria', null]
        ]
    };

    var opcionales = {
        canina: [
            ['KC / Bordetella (tos de las perreras)', 365],
            ['Leptospirosis', 365],
            ['Influenza canina', 365],
            ['Leishmaniosis (según riesgo e indicación)', 365]
        ],
        felina: [
            ['Leucemia felina (FeLV)', 365],
            ['Clamidiosis felina', 365],
            ['Peritonitis infecciosa felina (según indicación)', 365]
        ],
        otra: [
            ['CANINO · KC / Bordetella (tos de las perreras)', 365],
            ['CANINO · Leptospirosis', 365],
            ['CANINO · Influenza canina', 365],
            ['FELINO · Leucemia felina (FeLV)', 365],
            ['FELINO · Clamidiosis felina', 365],
            ['Otra vacuna indicada por el veterinario', null]
        ]
    };

    var especieActual = 'otra';
    var fechaNacimiento = '';
    var perfilesDesparasitacion = {
        canina: {
            cachorro_temprano: {
                dias: 15,
                texto: 'Pauta esencial: iniciar desparasitación interna a las 2 semanas y repetir cada 15 días hasta las 12 semanas.',
                productos: [
                    ['Invermic Cachorros · interna oral', 'Interno', 'Oral', 15],
                    ['Gastroenteril · interna oral', 'Interno', 'Oral', 15],
                    ['Frontline Spray · externa tópica (validar edad)', 'Externo', 'Tópica', 30]
                ]
            },
            juvenil: {
                dias: 30,
                texto: 'Pauta esencial: desde los 3 hasta los 6 meses, control interno mensual.',
                productos: [
                    ['Drontal Perros · interna oral', 'Interno', 'Oral', 30],
                    ['Endogard · interna oral', 'Interno', 'Oral', 30],
                    ['NexGard · externa oral', 'Externo', 'Oral', 30]
                ]
            },
            adulto: {
                dias: 90,
                texto: 'Adulto: control interno cada 1 a 3 meses según riesgo; control externo mensual o según duración autorizada del producto.',
                productos: [
                    ['Drontal Perros · interna oral', 'Interno', 'Oral', 90],
                    ['Endogard · interna oral', 'Interno', 'Oral', 60],
                    ['NexGard · externa oral', 'Externo', 'Oral', 30],
                    ['Simparica · externa oral', 'Externo', 'Oral', 30],
                    ['Bravecto · externa oral', 'Externo', 'Oral', 84]
                ]
            }
        },
        felina: {
            cachorro_temprano: {
                dias: 15,
                texto: 'Pauta esencial: iniciar a los 21 días y repetir cada 15 días hasta cumplir 3 meses.',
                productos: [
                    ['Invermic Gato · interna oral', 'Interno', 'Oral', 15],
                    ['Nanormen (pyrantel) · interna oral', 'Interno', 'Oral', 15],
                    ['Frontline Spray · externa tópica (validar edad)', 'Externo', 'Tópica', 30]
                ]
            },
            juvenil: {
                dias: 30,
                texto: 'Pauta esencial: desde los 3 hasta los 6 meses, control interno mensual.',
                productos: [
                    ['Drontal Gatos · interna oral', 'Interno', 'Oral', 30],
                    ['Mebermic · interna oral', 'Interno', 'Oral', 30],
                    ['Flivovermic · interna oral', 'Interno', 'Oral', 30],
                    ['Revolution Plus · interna y externa tópica', 'Interno y Externo', 'Tópica', 30],
                    ['NexGard Combo · interna y externa tópica', 'Interno y Externo', 'Tópica', 30]
                ]
            },
            adulto: {
                dias: 120,
                texto: 'Adulto de interior: control interno cada 3 a 4 meses. Con acceso al exterior: control interno y externo mensual.',
                productos: [
                    ['Drontal Gatos · interna oral', 'Interno', 'Oral', 120],
                    ['Mebermic · interna oral', 'Interno', 'Oral', 120],
                    ['Revolution Plus · interna y externa tópica', 'Interno y Externo', 'Tópica', 30],
                    ['NexGard Combo · interna y externa tópica', 'Interno y Externo', 'Tópica', 30],
                    ['Bravecto Gato Plus · interna y externa tópica', 'Interno y Externo', 'Tópica', 84],
                    ['Seresto · externa collar', 'Externo', 'Collar', 240]
                ]
            }
        },
        otra: {
            adulto: {
                dias: null,
                texto: 'Otra especie: defina producto, dosis y periodicidad según evaluación veterinaria específica.',
                productos: []
            }
        }
    };

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

    function calcularEdad(fecha) {
        if (!fecha) return '';
        var nacimiento = new Date(fecha + 'T00:00:00');
        var hoy = new Date();
        var meses = (hoy.getFullYear() - nacimiento.getFullYear()) * 12 +
            hoy.getMonth() - nacimiento.getMonth();
        if (hoy.getDate() < nacimiento.getDate()) meses--;
        if (meses < 0) return '';
        return meses < 24 ? meses + ' meses' : Math.floor(meses / 12) + ' años';
    }

    function normalizarEspecie(nombre) {
        nombre = String(nombre || '').toLowerCase();
        if (nombre.indexOf('canin') !== -1 || nombre.indexOf('perro') !== -1) return 'canina';
        if (nombre.indexOf('felin') !== -1 || nombre.indexOf('gato') !== -1) return 'felina';
        return 'otra';
    }

    function llenarSelect($select, items, textoInicial) {
        $select.empty().append($('<option>', {value: '', text: textoInicial}));
        items.forEach(function (item) {
            $select.append($('<option>', {
                value: item[0],
                text: item[0],
                'data-dias': item[1] == null ? '' : item[1]
            }));
        });
    }

    function configurarEspecie(mascota) {
        especieActual = normalizarEspecie(mascota.especie);
        fechaNacimiento = mascota.fecha_nacimiento || '';
        $('#vac_edad_modal').val(calcularEdad(fechaNacimiento));
        $('#vac_especie_resumen').text(
            (mascota.nombre || 'Mascota') + ' · ' + (mascota.especie || 'Otra especie')
        );
        $('#vac_esquema_label').text(
            especieActual === 'canina' ? 'Esquema canino' :
            especieActual === 'felina' ? 'Esquema felino' : 'Esquema para otra especie'
        );
        llenarSelect($('#vac_esquema_modal'), esquemas[especieActual], 'Seleccione vacuna del esquema');
        llenarSelect($('#vac_opcional_modal'), opcionales[especieActual], 'Seleccione vacuna opcional');
        $('#des_especie_modal').val(especieActual);
        $('#des_etapa_modal').val(calcularEtapaDesparasitacion(fechaNacimiento));
        actualizarEsquemaDesparasitacion();
    }

    function calcularMeses(fecha) {
        if (!fecha) return null;
        var nacimiento = new Date(fecha + 'T00:00:00');
        var hoy = new Date();
        var meses = (hoy.getFullYear() - nacimiento.getFullYear()) * 12 +
            hoy.getMonth() - nacimiento.getMonth();
        if (hoy.getDate() < nacimiento.getDate()) meses--;
        return meses < 0 ? null : meses;
    }

    function calcularEtapaDesparasitacion(fecha) {
        var meses = calcularMeses(fecha);
        if (meses === null) return 'adulto';
        if (meses < 3) return 'cachorro_temprano';
        if (meses < 6) return 'juvenil';
        return 'adulto';
    }

    function sumarDias(fecha, dias) {
        if (!fecha || !dias) return '';
        var siguiente = new Date(fecha + 'T00:00:00');
        siguiente.setDate(siguiente.getDate() + Number(dias));
        return siguiente.toISOString().slice(0, 10);
    }

    function actualizarEsquemaDesparasitacion() {
        var especie = $('#des_especie_modal').val() || especieActual || 'otra';
        var etapa = $('#des_etapa_modal').val() || calcularEtapaDesparasitacion(fechaNacimiento);
        var riesgo = $('#des_riesgo_modal').val() || 'bajo';
        var especiePlanes = perfilesDesparasitacion[especie] || perfilesDesparasitacion.otra;
        var plan = especiePlanes[etapa] || especiePlanes.adulto || perfilesDesparasitacion.otra.adulto;
        var dias = plan.dias;

        if (etapa === 'adulto' && riesgo === 'alto') {
            dias = especie === 'canina' ? 60 : (especie === 'felina' ? 30 : dias);
        }

        var $select = $('#des_producto_sugerido_modal');
        $select.empty().append($('<option>', {value: '', text: 'Seleccione una alternativa'}));
        plan.productos.forEach(function (producto) {
            $select.append($('<option>', {
                value: producto[0],
                text: producto[0],
                'data-tipo': producto[1],
                'data-via': producto[2],
                'data-dias': producto[3] || dias || ''
            }));
        });

        var contexto = especie === 'canina' ? 'Canino' : (especie === 'felina' ? 'Felino' : 'Otra especie');
        $('#des_recomendacion_modal')
            .html('<strong>' + contexto + ':</strong> ' + escapar(plan.texto) +
                (riesgo === 'alto' ? ' <strong>Perfil de mayor exposición:</strong> reforzar control y evaluación epidemiológica.' : ''));

        if ($('#des_fecha_dosis_modal').val() && dias) {
            $('#des_proxima_dosis_modal').val(sumarDias($('#des_fecha_dosis_modal').val(), dias));
        }
    }

    function sugerirVacuna($select) {
        if (!$select.val()) return;
        $('#vac_nombre_modal').val($select.val());
        var dias = Number($select.find(':selected').data('dias'));
        var fecha = $('#vac_fecha_dosis_modal').val();
        if (fecha && dias > 0) {
            var proxima = new Date(fecha + 'T00:00:00');
            proxima.setDate(proxima.getDate() + dias);
            $('#vac_proxima_dosis_modal').val(proxima.toISOString().slice(0, 10));
        }
    }

    function renderVacunas(items) {
        var $body = $('#tabla_vacunas_mascotas_body').empty();
        if (!items.length) {
            $body.append('<tr><td colspan="3" class="text-center text-muted">Sin registros</td></tr>');
            return;
        }
        items.forEach(function (item) {
            $body.append('<tr><td>' + fechaCorta(item.fecha_dosis) + '</td><td>' +
                escapar(item.vacuna || '-') + '</td><td>' + fechaCorta(item.proxima_dosis) + '</td></tr>');
        });
    }

    function renderDesparasitaciones(items) {
        var $body = $('#tabla_desparasitacion_body').empty();
        if (!items.length) {
            $body.append('<tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>');
            return;
        }
        items.forEach(function (item) {
            $body.append('<tr><td>' + fechaCorta(item.fecha_dosis) + '</td><td>' +
                escapar(item.antiparasitario || '-') + '</td><td>' +
                escapar((item.tipo || '-') + (item.via ? ' / ' + item.via : '')) + '</td><td>' +
                escapar((item.dosis || '-') + (item.peso ? ' · ' + item.peso + ' kg' : '')) + '</td><td>' +
                fechaCorta(item.proxima_dosis) + '</td></tr>');
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
                'No se pudo identificar la mascota · seleccione el esquema canino o felino'
            );
            return;
        }
        $.get(urlMascota("{{ route('paciente.mascotas.registros_sanitarios', ['mascotaId' => '__ID__']) }}"))
            .done(function (respuesta) {
                configurarEspecie(respuesta.mascota || {});
                renderVacunas(respuesta.vacunas || []);
                renderDesparasitaciones(respuesta.desparasitaciones || []);
            })
            .fail(function (xhr) {
                $('#vac_especie_resumen').text(
                    'No fue posible cargar los antecedentes · los esquemas de Chile siguen disponibles'
                );
                console.error('Error al cargar registros sanitarios', xhr.responseText);
            });
    }

    $(function () {
        cargar();
    });

    $(document)
        .on('shown.bs.modal', '#modal_vacunacion_desparasitacion', cargar)
        .on('click', '#cerrar_modal_vacunacion', function (evento) {
            evento.preventDefault();
            cerrarVacunacionVeterinaria();
        })
        .on('hidden.bs.modal', '#modal_vacunacion_desparasitacion', function () {
            $('#abrir_vacunacion_desparasitacion').prop('checked', false);
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        })
        .on('change', '#vac_esquema_modal, #vac_opcional_modal', function () {
            sugerirVacuna($(this));
        })
        .on('change', '#vac_fecha_dosis_modal', function () {
            var $seleccion = $('#vac_opcional_modal').val() ? $('#vac_opcional_modal') : $('#vac_esquema_modal');
            sugerirVacuna($seleccion);
        })
        .on('change', '#des_especie_modal, #des_etapa_modal, #des_riesgo_modal', function () {
            actualizarEsquemaDesparasitacion();
        })
        .on('change', '#des_producto_sugerido_modal', function () {
            var $opcion = $(this).find(':selected');
            if (!$opcion.val()) return;

            $('#des_antiparasitario_modal').val(
                $opcion.val().split(' · ')[0]
            );
            $('#des_tipo_modal').val($opcion.data('tipo') || '');
            $('#des_via_modal').val($opcion.data('via') || '');

            var fecha = $('#des_fecha_dosis_modal').val();
            var dias = Number($opcion.data('dias'));
            if (fecha && dias > 0) {
                $('#des_proxima_dosis_modal').val(sumarDias(fecha, dias));
            }
        })
        .on('change', '#des_fecha_dosis_modal', function () {
            var diasProducto = Number($('#des_producto_sugerido_modal').find(':selected').data('dias'));
            if (diasProducto > 0) {
                $('#des_proxima_dosis_modal').val(sumarDias($(this).val(), diasProducto));
                return;
            }
            actualizarEsquemaDesparasitacion();
        })
        .on('click', '#btn_add_vacuna_modal', function () {
            var $boton = $(this).prop('disabled', true);
            $.post(urlMascota("{{ route('paciente.mascotas.vacunas.guardar', ['mascotaId' => '__ID__']) }}"), {
                _token: token(),
                edad: $('#vac_edad_modal').val(),
                fecha_dosis: $('#vac_fecha_dosis_modal').val(),
                vacuna: $('#vac_nombre_modal').val(),
                proxima_dosis: $('#vac_proxima_dosis_modal').val(),
                especie: especieActual
            }).done(function (r) {
                if (parseInt(r.estado || 0, 10) !== 1) {
                    mostrarError({responseJSON: r});
                    return;
                }
                renderVacunas(r.vacunas || []);
                $('#vac_nombre_modal,#vac_fecha_dosis_modal,#vac_proxima_dosis_modal').val('');
                $('#vac_esquema_modal,#vac_opcional_modal').val('');
                mostrarGuardado(r.msj || 'Vacuna guardada correctamente.');
            }).fail(mostrarError).always(function () {
                $boton.prop('disabled', false);
            });
        })
        .on('click', '#btn_add_desparasitacion_modal', function () {
            var $boton = $(this).prop('disabled', true);
            $.post(urlMascota("{{ route('paciente.mascotas.desparasitaciones.guardar', ['mascotaId' => '__ID__']) }}"), {
                _token: token(),
                fecha_dosis: $('#des_fecha_dosis_modal').val(),
                antiparasitario: $('#des_antiparasitario_modal').val(),
                tipo: $('#des_tipo_modal').val(),
                via: $('#des_via_modal').val(),
                peso: $('#des_peso_modal').val(),
                dosis: $('#des_dosis_modal').val(),
                lote: $('#des_lote_modal').val(),
                proxima_dosis: $('#des_proxima_dosis_modal').val()
            }).done(function (r) {
                if (parseInt(r.estado || 0, 10) !== 1) {
                    mostrarError({responseJSON: r});
                    return;
                }
                renderDesparasitaciones(r.desparasitaciones || []);
                $('#des_fecha_dosis_modal,#des_antiparasitario_modal,#des_peso_modal,#des_dosis_modal,#des_lote_modal,#des_proxima_dosis_modal').val('');
                $('#des_tipo_modal,#des_via_modal,#des_producto_sugerido_modal').val('');
                actualizarEsquemaDesparasitacion();
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
