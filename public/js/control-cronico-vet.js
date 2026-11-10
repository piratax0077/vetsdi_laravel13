(function ($, config) {
    'use strict';

    const modal = $('#form_enfermedad_cronica');
    const selector = modal.find('#cronicos');
    const otra = modal.find('#cronico-vet-otra');
    const observaciones = modal.find('#cronico-vet-observaciones');
    const campos = modal.find('#cronico-vet-mediciones');
    const tabla = modal.find('#cronico-vet-historial');
    const graficos = modal.find('#cronico-vet-graficos');
    const guardar = modal.find('#guardar-cronico-vet');
    const colores = ['#6c3eb6', '#36a3ab', '#4b6eb1'];
    let charts = [];
    let peticionHistorial;

    function parametros() {
        return {id_ficha_atencion: $('#id_fc').val(), condicion: selector.val(), otra: otra.val().trim()};
    }

    function limpiarHistorial() {
        if (peticionHistorial) peticionHistorial.abort();
        charts.forEach(chart => chart.destroy());
        charts = [];
        tabla.empty();
        graficos.empty();
    }

    function cambiar() {
        const condicion = selector.val();
        const [nombre, metricas] = config.catalogo[condicion];
        modal.find('#titulo-control-vet').text('Control · ' + nombre);
        modal.find('#cronico-vet-otra-grupo').toggleClass('d-none', condicion !== 'otra');
        campos.empty();
        observaciones.val('');
        otra.val('');
        limpiarHistorial();

        Object.entries(metricas).forEach(([clave, [etiqueta, unidad, paso]]) => {
            const id = 'cronico-vet-' + clave;
            const grupo = $('<div>', {class: 'form-group col-sm-12 col-md-6 col-lg-3'}).appendTo(campos);
            $('<label>', {for: id, class: 'floating-label-activo-sm', text: etiqueta + ' (' + unidad + ')'}).appendTo(grupo);
            $('<input>', {id, name: clave, type: 'number', step: paso || 'any',
                min: clave === 'variacion' ? -1000000 : 0, max: 1000000, class: 'form-control form-control-sm'}).appendTo(grupo);
        });

        modal.find('#obes-ctrl-tab').tab('show');
    }

    function mostrarHistorial(registros, metricas) {
        const grupos = new Map();
        Object.entries(metricas).forEach(([clave, [etiqueta, unidad]]) => {
            if (!registros.some(registro => registro.mediciones[clave] != null)) return;
            if (!grupos.has(unidad)) grupos.set(unidad, []);
            grupos.get(unidad).push({clave, etiqueta});
        });

        registros.slice().reverse().forEach(registro => {
            const fila = $('<tr>').appendTo(tabla);
            $('<td>').text(registro.fecha).appendTo(fila);
            $('<td>').text(Object.entries(registro.mediciones).filter(([clave]) => metricas[clave])
                .map(([clave, valor]) => metricas[clave][0] + ': ' + valor + ' ' + metricas[clave][1]).join(' · ')).appendTo(fila);
            $('<td>').text(registro.observaciones || '').appendTo(fila);
        });

        if (!registros.length) {
            tabla.append($('<tr>').append($('<td>', {colspan: 3, text: 'Sin controles registrados.'})));
            return;
        }

        if (typeof Chart === 'undefined') return;
        grupos.forEach((series, unidad) => {
            const seccion = $('<div>', {class: 'card-informacion mb-3'}).appendTo(graficos);
            const cuerpo = $('<div>', {class: 'card-body'}).appendTo(seccion);
            $('<h6>').text(series.map(serie => serie.etiqueta).join(' / ') + ' (' + unidad + ')').appendTo(cuerpo);
            const canvas = $('<canvas>', {height: 90}).appendTo(cuerpo)[0];
            charts.push(new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: registros.map(registro => registro.fecha),
                    datasets: series.map(({clave, etiqueta}, indice) => ({
                        label: etiqueta + ' (' + unidad + ')',
                        data: registros.map(registro => registro.mediciones[clave] == null ? null : Number(registro.mediciones[clave])),
                        borderColor: colores[indice % colores.length], fill: false, spanGaps: false
                    }))
                },
                options: {responsive: true}
            }));
        });
    }

    function historial() {
        limpiarHistorial();
        const datos = parametros();
        if (datos.condicion === 'otra' && !datos.otra) {
            tabla.append($('<tr>').append($('<td>', {colspan: 3, text: 'Ingresa el nombre de la condición en la pestaña Control.'})));
            return;
        }

        peticionHistorial = $.getJSON(config.historial, datos)
            .done(respuesta => mostrarHistorial(respuesta.registros, config.catalogo[datos.condicion][1]))
            .fail((xhr, estado) => {
                if (estado !== 'abort') tabla.append($('<tr>').append($('<td>', {colspan: 3, text: 'No se pudo cargar el historial.'})));
            });
    }

    guardar.on('click', function () {
        if (guardar.prop('disabled')) return;
        const mediciones = {};
        campos.find('input').each(function () {
            if (this.value !== '') mediciones[this.name] = this.value;
        });
        const datos = Object.assign(parametros(), {mediciones, observaciones: observaciones.val()});
        guardar.prop('disabled', true);
        $.ajax({url: config.guardar, method: 'POST', dataType: 'json', data: datos,
            headers: {'X-CSRF-TOKEN': config.token, Accept: 'application/json'}})
            .done(function (respuesta) {
                if (selector.val() === datos.condicion && otra.val().trim() === datos.otra) {
                    campos.find('input').val('');
                    observaciones.val('');
                }
                swal({title: 'Control guardado', text: respuesta.mensaje, icon: 'success'});
            })
            .fail(function (xhr) {
                const respuesta = xhr.responseJSON || {};
                swal({title: 'No se pudo guardar', icon: 'error',
                    text: Object.values(respuesta.errors || {}).flat().join('\n') || respuesta.message || 'Intenta nuevamente.'});
            })
            .always(() => guardar.prop('disabled', false));
    });

    window.controlCronicoVet = {cambiar};
    window.cargarHistorialCronicoVet = historial;
})(jQuery, window.controlCronicoVetConfig);
