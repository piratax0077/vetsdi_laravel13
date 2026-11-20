/*
 * Facturas de compra del inventario veterinario.
 * Parte visual del registro: filtros de la tabla, cálculo de IVA y total, campos según el tipo de documento,
 * validación del RUT (módulo 11) y modales de detalle y eliminación.
 * Lo que se guarda lo vuelve a revisar el servidor.
 */
(function () {
    'use strict';

    if (!window.jQuery) return;
    var $ = window.jQuery;

    var formatoPesos = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 });
    function pesos(monto) { return (monto < 0 ? '-' : '') + formatoPesos.format(Math.abs(monto || 0)); }

    /* ---------- RUT ---------- */
    function digitoVerificador(cuerpo) {
        var suma = 0, multiplicador = 2;
        for (var i = cuerpo.length - 1; i >= 0; i--) {
            suma += Number(cuerpo[i]) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }
        var resto = 11 - (suma % 11);
        return resto === 11 ? '0' : resto === 10 ? 'K' : String(resto);
    }
    function partesRut(texto) {
        var limpio = String(texto).replace(/[.\s-]/g, '').toUpperCase();
        var partes = limpio.match(/^(\d{7,8})([\dK])$/);
        return partes ? { cuerpo: partes[1], dv: partes[2] } : null;
    }
    function rutValido(texto) {
        var p = partesRut(texto);
        return !!p && digitoVerificador(p.cuerpo) === p.dv;
    }
    function rutConPuntos(texto) {
        var p = partesRut(texto);
        return p ? p.cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + p.dv : String(texto).toUpperCase();
    }

    /* ---------- Alertas flotantes (mismo diseño que el resto de la vista) ---------- */
    function alertaError(mensaje) {
        var $contenedor = $('.alertas-flotantes');
        if (!$contenedor.length) $contenedor = $('<div class="alertas-flotantes"></div>').appendTo('body');
        var $alerta = $(
            '<div class="alert alert-inverse alert-dismissible fade show alerta-flotante" role="alert">' +
                '<i class="feather icon-alert-circle alerta-flotante-icono alerta-flotante-error"></i>' +
                '<div class="alerta-flotante-texto"></div>' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">×</span></button>' +
            '</div>'
        );
        $alerta.find('.alerta-flotante-texto').text(mensaje);
        $contenedor.append($alerta);
        setTimeout(function () { $alerta.alert('close'); }, 6000);
    }

    /* ---------- Filtros de la tabla ---------- */
    function filtrarFacturas() {
        var texto = String($('#facturas_buscar').val() || '').toLowerCase().replace(/[.\s]/g, '');
        var tipo = $('#facturas_filtro_tipo').val();
        var pago = $('#facturas_filtro_pago').val();
        var $filas = $('#tabla_facturas .fila-factura');
        var visibles = 0;

        $filas.each(function () {
            var $fila = $(this);
            var busqueda = String($fila.data('busqueda')).replace(/[.\s]/g, '');
            var coincide = (!tipo || String($fila.data('tipo')) === tipo)
                && (!pago || $fila.data('pago') === pago)
                && (!texto || busqueda.indexOf(texto) !== -1);
            $fila.toggle(coincide);
            if (coincide) visibles++;
        });

        $('#tabla_facturas .facturas-sin-resultados').prop('hidden', !$filas.length || visibles > 0);
    }

    /* ---------- Formulario de agregar ---------- */
    var $form = $('#form_factura_compra');

    function esAjuste(tipo) { return tipo === '61' || tipo === '56'; }

    function recalcularTotal() {
        var tipo = $('#factura_tipo').val();
        var neto = Math.round(Number($('#factura_neto').val()) || 0);
        var exento = Math.round(Number($('#factura_exento').val()) || 0);
        var $iva = $('#factura_iva');

        // El IVA se calcula solo hasta que la persona lo corrige a mano
        if (!$iva.data('editado') && !$iva.prop('disabled')) $iva.val(Math.round(neto * 0.19));

        var total = neto + exento + (Math.round(Number($iva.val()) || 0));
        if (tipo === '61') total = -total;
        $('#factura_total').val(total);
        $('#factura_total_texto').text(pesos(total));
    }

    // La exenta no lleva neto ni IVA; las notas piden el documento que modifican
    function ajustarPorTipo() {
        var tipo = $('#factura_tipo').val();
        var exenta = tipo === '34';

        $('#grupo_factura_referencia, #nota_factura_ajuste').prop('hidden', !esAjuste(tipo));
        $('#factura_neto, #factura_iva').prop('readonly', exenta);
        if (exenta) $('#factura_neto, #factura_iva').val(0);
        $('#factura_iva').data('editado', false);
        recalcularTotal();
    }

    function marcarError($campo, mensaje) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $campo.addClass('is-invalid').trigger('focus');
        alertaError(mensaje);
        return false;
    }

    function validarFactura() {
        var hoy = $('#factura_fecha').attr('max');
        var tipo = $('#factura_tipo').val();
        var folio = $.trim($('#factura_folio').val());
        var fecha = $('#factura_fecha').val();
        var neto = Number($('#factura_neto').val()) || 0;
        var exento = Number($('#factura_exento').val()) || 0;
        var iva = Number($('#factura_iva').val()) || 0;

        if (!/^\d+$/.test(folio)) return marcarError($('#factura_folio'), 'Ingresa el folio del documento, solo con números.');
        if (!fecha) return marcarError($('#factura_fecha'), 'Ingresa la fecha de emisión.');
        if (hoy && fecha > hoy) return marcarError($('#factura_fecha'), 'La fecha de emisión no puede ser posterior a hoy.');
        if (!$.trim($('#factura_proveedor').val())) return marcarError($('#factura_proveedor'), 'Ingresa la razón social del proveedor.');
        if (!rutValido($('#factura_rut').val())) return marcarError($('#factura_rut'), 'El RUT del proveedor no es válido. Revisa el dígito verificador.');
        if (esAjuste(tipo) && !$('#factura_referencia').val()) return marcarError($('#factura_referencia'), 'Selecciona el documento que modifica la nota.');
        if (neto < 0 || exento < 0 || iva < 0) return marcarError($('#factura_neto'), 'Ingresa los montos en positivo.');
        if (neto === 0 && exento === 0) return marcarError(tipo === '34' ? $('#factura_exento') : $('#factura_neto'), 'Ingresa el monto neto o exento del documento.');
        return true;
    }

    /* ---------- Archivo del documento y lectura de sus datos ---------- */
    // La zona de carga se arma a mano; si no, Dropzone la tomaría sola al cargar la página
    if (window.Dropzone) window.Dropzone.autoDiscover = false;

    var zonaArchivo = null;
    var TIPOS_PERMITIDOS = ['33', '34', '61', '56'];

    function avisoLectura(texto, tipo) {
        var $aviso = $('#factura_lectura');
        if (!texto) { $aviso.prop('hidden', true).empty(); return; }
        var icono = tipo === 'ok' ? 'icon-check-circle' : tipo === 'cargando' ? 'icon-loader' : 'icon-info';
        $aviso.prop('hidden', false).empty()
            .append($('<i class="feather" aria-hidden="true"></i>').addClass(icono))
            .append(document.createTextNode(' ' + texto));
    }

    // Deja en el formulario los datos leídos; solo toca los campos que vinieron con valor
    function completarFormulario(datos) {
        var completados = 0;
        function poner(selector, valor) {
            if (valor === undefined || valor === null || String(valor).trim() === '') return;
            $(selector).val(String(valor).trim()).removeClass('is-invalid');
            completados++;
        }

        if (datos.tipo_documento && TIPOS_PERMITIDOS.indexOf(String(datos.tipo_documento)) !== -1) {
            $('#factura_tipo').val(String(datos.tipo_documento));
            completados++;
        }
        ajustarPorTipo();

        poner('#factura_folio', datos.folio);
        poner('#factura_fecha', datos.fecha_emision);
        poner('#factura_proveedor', datos.proveedor);
        if (datos.rut_proveedor) poner('#factura_rut', rutConPuntos(datos.rut_proveedor));
        if ($('#factura_tipo').val() !== '34') poner('#factura_neto', datos.neto);
        poner('#factura_exento', datos.exento);
        if (datos.iva !== undefined && datos.iva !== null && String(datos.iva) !== '' && !$('#factura_iva').prop('readonly')) {
            poner('#factura_iva', datos.iva);
            $('#factura_iva').data('editado', true);
        }

        // En las notas se busca la factura de referencia por su folio
        if (datos.folio_referencia) {
            var $opcion = $('#factura_referencia option').filter(function () { return String($(this).data('folio')) === String(datos.folio_referencia); }).first();
            if ($opcion.length) { $('#factura_referencia').val($opcion.val()); completados++; }
        }

        recalcularTotal();
        return completados;
    }

    // XML de factura electrónica del SII (DTE o EnvioDTE): se toma el primer documento
    function leerXmlSii(texto) {
        var xml = new DOMParser().parseFromString(texto, 'application/xml');
        if (xml.getElementsByTagName('parsererror').length) return null;

        var documento = xml.getElementsByTagName('Documento')[0] || xml.documentElement;
        function dato(contenedor, etiqueta) {
            var base = contenedor ? documento.getElementsByTagName(contenedor)[0] : documento;
            var nodo = base ? base.getElementsByTagName(etiqueta)[0] : null;
            return nodo ? nodo.textContent.trim() : '';
        }

        var tipo = dato('IdDoc', 'TipoDTE');
        if (!tipo) return null;

        return {
            tipo_documento: tipo,
            folio: dato('IdDoc', 'Folio'),
            fecha_emision: dato('IdDoc', 'FchEmis'),
            proveedor: dato('Emisor', 'RznSoc') || dato('Emisor', 'RznSocEmisor'),
            rut_proveedor: dato('Emisor', 'RUTEmisor'),
            neto: dato('Totales', 'MntNeto'),
            exento: dato('Totales', 'MntExe'),
            iva: dato('Totales', 'IVA'),
            folio_referencia: dato('Referencia', 'FolioRef')
        };
    }

    function leerArchivo(archivo) {
        var esXml = /\.xml$/i.test(archivo.name) || /xml/.test(archivo.type);

        if (esXml) {
            var lector = new FileReader();
            lector.onload = function () {
                var datos = leerXmlSii(String(lector.result));
                if (!datos) { avisoLectura('No se reconocieron los datos del XML. Complétalos a mano.'); return; }
                if (TIPOS_PERMITIDOS.indexOf(datos.tipo_documento) === -1) {
                    avisoLectura('El XML es de un tipo de documento que no se registra aquí (' + datos.tipo_documento + '). Revisa el archivo.');
                    return;
                }
                completarFormulario(datos);
                avisoLectura('Datos completados desde el XML. Revísalos antes de guardar.', 'ok');
            };
            lector.onerror = function () { avisoLectura('No se pudo leer el archivo. Completa los datos a mano.'); };
            lector.readAsText(archivo);
            return;
        }

        // PDF o imagen: lo lee el servidor, si ya está conectada esa ruta
        var urlLectura = $('#dropzone_factura').data('url-lectura');
        if (!urlLectura) { avisoLectura('Archivo adjuntado. Completa los datos del documento a mano.'); return; }

        var envio = new FormData();
        envio.append('archivo_factura', archivo);
        envio.append('_token', $form.find('input[name="_token"]').val());
        avisoLectura('Leyendo el documento…', 'cargando');

        $.ajax({ url: urlLectura, method: 'POST', data: envio, processData: false, contentType: false, dataType: 'json' })
            .done(function (datos) {
                var completados = completarFormulario(datos || {});
                avisoLectura(completados
                    ? 'Datos completados desde el documento. Revísalos y completa lo que falte.'
                    : 'No se pudieron leer datos del documento. Complétalos a mano.', completados ? 'ok' : null);
            })
            .fail(function () { avisoLectura('No se pudieron leer datos del documento. Complétalos a mano.'); });
    }

    function iniciarZonaArchivo() {
        var elemento = document.getElementById('dropzone_factura');
        if (zonaArchivo || !elemento || !window.Dropzone) return;

        zonaArchivo = new window.Dropzone(elemento, {
            url: '#',
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 10,
            acceptedFiles: '.xml,.pdf,.jpg,.jpeg,.png,application/xml,text/xml,application/pdf,image/jpeg,image/png',
            dictInvalidFileType: 'Solo se aceptan archivos XML, PDF, JPG o PNG.',
            dictFileTooBig: 'El archivo pesa más de 10 MB.',
            previewTemplate:
                '<div class="dz-preview dropzone-factura-archivo">' +
                    '<i class="feather icon-file-text dropzone-factura-archivo-icono" aria-hidden="true"></i>' +
                    '<div class="dropzone-factura-archivo-datos"><strong data-dz-name></strong><small data-dz-size></small>' +
                    '<small class="text-danger" data-dz-errormessage></small></div>' +
                    '<button type="button" class="btn btn-danger btn-icon" data-dz-remove title="Quitar" aria-label="Quitar archivo"><i class="feather icon-x"></i></button>' +
                '</div>',
            init: function () {
                this.on('addedfile', function (archivo) {
                    // Solo un documento: el nuevo reemplaza al anterior
                    if (this.files.length > 1) this.removeFile(this.files[0]);
                });
                this.on('error', function (archivo, mensaje) {
                    alertaError(typeof mensaje === 'string' ? mensaje : 'No se pudo adjuntar el archivo.');
                    this.removeFile(archivo);
                });
                this.on('removedfile', function () {
                    if (!this.files.length) avisoLectura('');
                });
            },
            accept: function (archivo, listo) {
                listo();
                leerArchivo(archivo);
            }
        });
    }

    // Copia el archivo de la zona de carga al campo del formulario para que viaje al guardar
    function pasarArchivoAlFormulario() {
        var campo = document.getElementById('factura_archivo');
        if (!campo) return;
        var archivo = zonaArchivo && zonaArchivo.getAcceptedFiles()[0];
        try {
            var transferencia = new DataTransfer();
            if (archivo) transferencia.items.add(archivo);
            campo.files = transferencia.files;
        } catch (e) {
            // Navegadores muy antiguos sin DataTransfer: el documento se guarda sin archivo
        }
    }

    $(function () {
        // Filtros
        $('#facturas_buscar').on('input', filtrarFacturas);
        $('#facturas_filtro_tipo, #facturas_filtro_pago').on('change', filtrarFacturas);

        // Formulario
        $('#factura_tipo').on('change', ajustarPorTipo);
        $('#factura_neto, #factura_exento').on('input', recalcularTotal);
        $('#factura_iva').on('input', function () { $(this).data('editado', true); recalcularTotal(); });
        $form.on('input change', '.is-invalid', function () { $(this).removeClass('is-invalid'); });

        // El RUT queda en mayúsculas y con puntos
        $('#factura_rut').on('input', function () { this.value = this.value.toUpperCase(); })
            .on('change', function () { if (rutValido(this.value)) this.value = rutConPuntos(this.value); });

        $form.on('submit', function (evento) {
            recalcularTotal();
            if (!validarFactura()) { evento.preventDefault(); return; }
            $('#factura_rut').val(rutConPuntos($('#factura_rut').val()));
            pasarArchivoAlFormulario();
            // Mientras no exista la ruta de guardado, el formulario no se envía
            if ($form.attr('action') === '#') {
                evento.preventDefault();
                alertaError('El guardado de documentos de compra todavía no está conectado.');
            }
        });

        var $modalFactura = $('#modal_factura_compra');
        // Al abrir para agregar una nueva, el formulario parte limpio (salvo que vuelva con errores del servidor)
        $modalFactura.on('show.bs.modal', function () {
            iniciarZonaArchivo();
            if ($modalFactura.data('reabrir') === 1) { $modalFactura.data('reabrir', 0); return; }
            $form.trigger('reset').find('.is-invalid').removeClass('is-invalid');
            if (zonaArchivo) zonaArchivo.removeAllFiles(true);
            avisoLectura('');
            ajustarPorTipo();
        });
        ajustarPorTipo();
        if ($modalFactura.data('reabrir') === 1) $modalFactura.modal('show');

        // Detalle de un documento con los datos de la fila
        $(document).on('click', '.ver-factura', function () {
            var datos = $(this).data();
            var $modal = $('#modal_detalle_factura');

            $('#titulo_detalle_factura').text(datos.titulo);
            ['proveedor', 'rut', 'fecha', 'neto', 'exento', 'iva', 'total', 'referencia', 'observaciones'].forEach(function (campo) {
                $modal.find('[data-campo="' + campo + '"]').text(datos[campo] || '');
            });
            var colores = { Pendiente: 'warning', Pagada: 'success', Aplicada: 'secondary' };
            $modal.find('[data-campo="pago"]').html($('<span class="badge"></span>').addClass('badge-light-' + (colores[datos.pago] || 'secondary')).text(datos.pago));
            $modal.find('[data-campo="url"]').attr('href', datos.url || '#');
            ['referencia', 'url', 'observaciones'].forEach(function (campo) {
                $modal.find('[data-opcional="' + campo + '"]').toggle(!!datos[campo]);
            });

            // La nota de crédito no cambia de estado de pago
            var urlPago = datos.cambiaPago;
            $('#form_pago_factura').toggle(!!urlPago).attr('action', urlPago || '#');
            $('#boton_pago_factura').text(datos.pago === 'Pagada' ? 'Marcar como pendiente' : 'Marcar como pagada');

            $modal.modal('show');
        });

        $('#form_pago_factura').on('submit', function (evento) {
            if ($(this).attr('action') === '#') {
                evento.preventDefault();
                alertaError('El cambio de estado de pago todavía no está conectado.');
            }
        });

        // Confirmación para eliminar
        $(document).on('click', '.eliminar-factura', function () {
            $('#eliminar_factura_descripcion').text($(this).data('descripcion'));
            $('#form_eliminar_factura').attr('action', $(this).data('url'));
            $('#modal_eliminar_factura').modal('show');
        });

        $('#form_eliminar_factura').on('submit', function (evento) {
            if ($(this).attr('action') === '#') {
                evento.preventDefault();
                alertaError('La eliminación de documentos de compra todavía no está conectada.');
            }
        });
    });
})();
