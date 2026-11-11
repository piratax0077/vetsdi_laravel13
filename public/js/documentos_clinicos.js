/*
 * Documentos clínicos del profesional: indicaciones y consentimientos informados.
 * Funciones compartidas entre la configuración (Indicaciones y consentimientos)
 * y la ficha de atención veterinaria: hoja A4 para el PDF, visor de archivos
 * y reemplazo de los datos automáticos ([Mascota], [Tutor]...) por los datos reales.
 */
(function (window, $) {
    'use strict';

    function escaparHtml(texto) {
        return $('<div>').text(texto || '').html();
    }

    // Reemplaza las etiquetas de datos automáticos por los valores de la atención.
    // Las que no tienen valor quedan marcadas como pendientes para completarlas a mano.
    function completarDatos(html, valores) {
        var copia = $('<div>').html(html);
        copia.find('.indicacion-dato').each(function () {
            var etiqueta = $(this);
            var valor = (valores || {})[etiqueta.attr('data-dato')];
            if (valor) {
                etiqueta.replaceWith(document.createTextNode(valor));
            } else {
                etiqueta.addClass('indicacion-dato-pendiente').attr('title', 'Completa este dato a mano');
            }
        });
        return copia.html();
    }

    // En el PDF los datos que siguen sin valor se muestran como [Mascota], [Tutor]...
    function contenidoParaPdf(html) {
        var copia = $('<div>').html(html);
        copia.find('.indicacion-dato').each(function () {
            $(this).replaceWith($('<span class="hoja-pdf-dato">').text('[' + $(this).text().trim() + ']'));
        });
        return copia.html();
    }

    /*
     * Arma el documento HTML de la hoja A4.
     * datos: titulo, logo, contenido (HTML), firmasHtml (opcional), centrado (título al centro), estilo (URL de style.css)
     */
    function armarDocumentoPdf(datos) {
        var fecha = new Date().toLocaleDateString('es-CL', { day: 'numeric', month: 'long', year: 'numeric' });
        return '<!doctype html><html lang="es"><head><meta charset="utf-8">'
            + '<meta name="viewport" content="width=device-width, initial-scale=1">'
            + '<title>' + escaparHtml(datos.titulo) + '</title>'
            + '<link rel="stylesheet" href="' + escaparHtml(datos.estilo) + '">'
            // Tamaño de hoja solo para este documento; en style.css afectaría la impresión de todo el sistema
            + '<style>@page { size: A4; margin: 0; }</style>'
            + '</head><body class="hoja-pdf-documento"><div class="hoja-pdf">'
            + '<div class="hoja-pdf-cabecera"><img src="' + escaparHtml(datos.logo) + '" alt="Logo"><span class="hoja-pdf-fecha">' + fecha + '</span></div>'
            + '<h1 class="hoja-pdf-titulo' + (datos.centrado ? ' hoja-pdf-titulo-centrado' : '') + '">' + escaparHtml(datos.titulo) + '</h1>'
            + '<div class="hoja-pdf-contenido">' + contenidoParaPdf(datos.contenido) + '</div>'
            + (datos.firmasHtml ? contenidoParaPdf(datos.firmasHtml) : '')
            + '</div></body></html>';
    }

    // Muestra la hoja A4 completa reducida al ancho del marco, tal como saldrá impresa
    function ajustarHoja(marco, escalaFija, intentos) {
        if (!marco) return;
        var documento = marco.contentDocument;
        var hoja = documento && documento.querySelector('.hoja-pdf');
        // La hoja puede terminar de cargar antes o después de que el modal tenga ancho; se reintenta un momento
        if (!hoja || !marco.clientWidth) {
            if ((intentos || 0) < 20) setTimeout(function () { ajustarHoja(marco, escalaFija, (intentos || 0) + 1); }, 100);
            return;
        }
        documento.documentElement.style.zoom = 1;
        documento.documentElement.style.zoom = escalaFija || Math.min(1, (marco.clientWidth - 32) / hoja.offsetWidth);
    }

    // Carga la hoja en el marco y la deja lista para imprimir a tamaño real
    function cargarHoja(marco, html) {
        marco.onload = function () {
            ajustarHoja(marco);
            marco.contentWindow.addEventListener('beforeprint', function () { ajustarHoja(marco, 1); });
            marco.contentWindow.addEventListener('afterprint', function () { ajustarHoja(marco); });
        };
        marco.srcdoc = html;
    }

    // Abre la ventana de impresión del navegador, donde se elige "Guardar como PDF"
    function imprimir(marco) {
        if (!marco || !marco.contentWindow) return;
        ajustarHoja(marco, 1);
        marco.contentWindow.focus();
        marco.contentWindow.print();
        ajustarHoja(marco);
    }

    // PDF e imágenes se ven dentro del sistema; Word y otros formatos se ofrecen para descargar
    function mostrarVisorArchivo(destino, archivo) {
        destino.empty().removeClass('d-none');
        if (!archivo || !archivo.nombre) { destino.addClass('d-none'); return; }

        var extension = archivo.nombre.split('.').pop().toLowerCase();
        var esPdf = archivo.tipo === 'application/pdf' || extension === 'pdf';
        var esImagen = /^image\//.test(archivo.tipo) || ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].indexOf(extension) !== -1;
        var icono = esPdf ? 'icon-file-text' : (esImagen ? 'icon-image' : 'icon-file');

        var cabecera = $('<div class="visor-archivo-cabecera">')
            .append($('<span class="visor-archivo-nombre">').append($('<i class="feather">').addClass(icono)).append($('<span>').text(archivo.nombre)));
        if (archivo.url) {
            var acciones = $('<div>');
            acciones.append($('<a class="btn btn-outline-info btn-sm" target="_blank" rel="noopener"><i class="feather icon-external-link"></i> Abrir</a>').attr('href', archivo.url));
            acciones.append(' ').append($('<a class="btn btn-info btn-sm"><i class="feather icon-download"></i> Descargar</a>').attr({ href: archivo.url, download: archivo.nombre }));
            cabecera.append(acciones);
        }
        destino.append(cabecera);

        if (!archivo.url) {
            destino.append('<div class="visor-archivo-aviso"><i class="feather icon-eye-off"></i>Archivo de ejemplo: la vista previa aparecerá cuando se conecte con los archivos guardados.</div>');
        } else if (esPdf) {
            destino.append($('<iframe class="visor-archivo-marco" title="Vista previa del archivo">').attr('src', archivo.url));
        } else if (esImagen) {
            destino.append($('<div class="visor-archivo-imagen">').append($('<img alt="Vista previa del archivo">').attr('src', archivo.url)));
        } else {
            destino.append('<div class="visor-archivo-aviso"><i class="feather icon-file"></i>Este formato no se puede mostrar en el navegador. Descárgalo para abrirlo en Word.</div>');
        }
    }

    window.DocumentosClinicos = {
        escaparHtml: escaparHtml,
        completarDatos: completarDatos,
        contenidoParaPdf: contenidoParaPdf,
        armarDocumentoPdf: armarDocumentoPdf,
        ajustarHoja: ajustarHoja,
        cargarHoja: cargarHoja,
        imprimir: imprimir,
        mostrarVisorArchivo: mostrarVisorArchivo
    };
})(window, jQuery);
