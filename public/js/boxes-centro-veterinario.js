(function (global, $) {
    'use strict';
    let guardando = false;
    function error(respuesta) {
        swal({title: 'Boxes o salas', icon: 'error', text: Object.values(respuesta.errors || {}).flat().join('\n') || respuesta.mensaje || respuesta.message || 'No fue posible guardar. Intente nuevamente.'});
    }
    function actualizarTabla(html) {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#tabla_boxes_atencion')) $('#tabla_boxes_atencion').DataTable().destroy();
        $('#tabla_boxes_atencion').replaceWith(html);
    }
    function guardar(editar) {
        if (guardando) return;
        const config = global.boxesCentroVeterinarioConfig;
        const prefijo = editar ? 'editar_' : '';
        const modal = editar ? '#editar_box' : '#agreg_box_ad';
        const nombre = String($('#' + prefijo + 'numero_box').val() || '').trim();
        const seleccion = $('#' + prefijo + 'tpo_especializacion').val();
        const tipo = seleccion === 'Otro' ? String($('#' + prefijo + 'tpo_especializacion_otro').val() || '').trim() : seleccion;
        const piso = $('#' + prefijo + 'tpo_equip_servicio').val();
        if (!nombre || !tipo || tipo === '0' || !piso || piso === '0') {
            error({mensaje: 'Complete nombre, tipo y ubicación. Si eligió Otro, especifique el tipo.'});
            return;
        }
        const datos = {origen: 'centro_veterinario', id_institucion: config.institucion, tipo_institucion: config.tipoInstitucion,
            numero_box: nombre, tpo_especializacion: tipo, tpo_equip_servicio: piso, _token: config.token};
        if (editar) datos.id_box = $('#id_box').val();
        else datos.id_lugar_atencion = config.lugar;
        guardando = true;
        $(modal + ' button').prop('disabled', true);
        $.ajax({url: editar ? config.editar : config.guardar, type: 'POST', dataType: 'json', headers: {Accept: 'application/json'}, data: datos})
            .done(function (respuesta) {
                if (respuesta.estado != 1) { error(respuesta); return; }
                actualizarTabla(respuesta.v);
                $(modal).modal('hide');
                swal({title: 'Boxes o salas', text: respuesta.mensaje, icon: 'success'});
            }).fail(function (xhr) { error(xhr.responseJSON || {}); })
            .always(function () { guardando = false; $(modal + ' button').prop('disabled', false); });
    }
    function cargar(id) {
        const config = global.boxesCentroVeterinarioConfig;
        $.ajax({url: config.cargar + '/' + encodeURIComponent(id), type: 'GET', dataType: 'json',
            data: {origen: 'centro_veterinario', id_institucion: config.institucion, tipo_institucion: config.tipoInstitucion}})
            .done(function (respuesta) {
                if (respuesta.estado != 1 || !respuesta.box) { error(respuesta); return; }
                const box = respuesta.box;
                const tipo = box.tipo_especializacion || box.tipo_box || '';
                const existe = $('#editar_tpo_especializacion option').filter(function () { return this.value === tipo; }).length > 0;
                $('#id_box').val(box.id);
                $('#editar_numero_box').val(box.numero_box);
                $('#editar_tpo_especializacion').val(existe ? tipo : 'Otro');
                $('#editar_tpo_especializacion_otro').val(existe ? '' : tipo);
                $('#editar_contenedor_tpo_especializacion_otro').toggleClass('d-none', existe);
                $('#editar_tpo_equip_servicio').val(String(box.ubicacion || '0'));
                $('#editar_box').modal('show');
            }).fail(function (xhr) { error(xhr.responseJSON || {}); });
    }
    global.boxesCentroVeterinario = {guardar: guardar, cargar: cargar};
})(window, jQuery);
