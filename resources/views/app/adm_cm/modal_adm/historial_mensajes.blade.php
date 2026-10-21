<!--Historial de mensajes enviados a un profesional; lo comparten varias pantallas de administración-->
<div id="historial_mensajes_profesional" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="historial_mensajes_profesional_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="historial_mensajes_profesional_titulo">Historial de mensajes</h5>
                <button type="button" class="close text-white" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="historial_mensajes_profesional_body">
                <p class="historial-mensajes-cargando">Cargando mensajes...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="$(this).closest('.modal').modal('hide');">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Dibuja la lista de mensajes con el diseño del modal; el texto se asigna con .text() para no interpretar HTML
    function pintar_historial_mensajes(mensajes) {
        let cuerpo = $('#historial_mensajes_profesional_body').html('');
        mensajes = mensajes || [];

        if (!mensajes.length) {
            cuerpo.append(
                '<div class="historial-mensajes-vacio">' +
                '    <span class="historial-mensajes-vacio-icono"><i class="feather icon-alert-circle"></i></span>' +
                '    <div>' +
                '        <p class="historial-mensajes-vacio-titulo">Sin registros</p>' +
                '        <p class="historial-mensajes-vacio-texto">Todavía no se le han enviado mensajes a este profesional. Los mensajes que envíe con el botón Mensaje aparecerán aquí.</p>' +
                '    </div>' +
                '</div>'
            );
            return;
        }

        let total = $('<p class="historial-mensajes-total"></p>').text(mensajes.length === 1 ? '1 mensaje enviado' : mensajes.length + ' mensajes enviados');
        cuerpo.append(total);

        $.each(mensajes, function(i, mensaje) {
            let datos = mensaje.datos_mensaje || {};
            let item = $(
                '<div class="historial-mensaje">' +
                '    <div class="historial-mensaje-icono"><i class="feather icon-mail"></i></div>' +
                '    <div class="historial-mensaje-contenido">' +
                '        <div class="historial-mensaje-encabezado">' +
                '            <h6 class="historial-mensaje-titulo"></h6>' +
                '            <span class="historial-mensaje-fecha"></span>' +
                '        </div>' +
                '        <p class="historial-mensaje-asunto"></p>' +
                '        <p class="historial-mensaje-texto"></p>' +
                '        <div class="historial-mensaje-archivos"></div>' +
                '    </div>' +
                '</div>'
            );

            item.find('.historial-mensaje-titulo').text(datos.titulo || 'Sin título');
            item.find('.historial-mensaje-fecha').text(historial_mensajes_fecha(mensaje.fecha_envio));

            // estado 1: no leído, 2: leído
            let leido = mensaje.estado == 2;
            item.find('.historial-mensaje-encabezado').append(
                $('<span class="badge"></span>').addClass(leido ? 'badge-light-success' : 'badge-light-warning').text(leido ? 'Leído' : 'No leído')
            );

            if (datos.asunto) {
                item.find('.historial-mensaje-asunto').text(datos.asunto);
            } else {
                item.find('.historial-mensaje-asunto').remove();
            }
            item.find('.historial-mensaje-texto').text(datos.mensaje || '');

            let archivos = item.find('.historial-mensaje-archivos');
            $.each(datos.archivos || [], function(j, archivo) {
                let enlace = $('<a class="historial-mensaje-archivo" target="_blank" rel="noopener"><i class="feather icon-paperclip"></i><span></span></a>');
                enlace.attr('href', archivo.url || '#');
                enlace.find('span').text(archivo.nombre_original || 'Archivo adjunto');
                archivos.append(enlace);
            });
            if (!archivos.children().length) {
                archivos.remove();
            }

            cuerpo.append(item);
        });
    }

    // Fecha de envío como dd-mm-aaaa (y hora si viene)
    function historial_mensajes_fecha(fecha) {
        if (!fecha) return '';
        let partes = String(fecha).split(' ');
        let dia = partes[0].split('-');
        let texto = dia.length === 3 ? dia[2] + '-' + dia[1] + '-' + dia[0] : partes[0];
        if (partes[1]) texto += ' ' + partes[1].substring(0, 5);
        return texto;
    }
</script>
