/*
 * Tablas responsivas
 *
 * En celulares, y en tablets cuando la tabla tiene muchas columnas, cada fila
 * se muestra como una tarjeta con el nombre de la columna sobre cada dato.
 * Este archivo solo pone esos nombres (data-label) y la clase .tabla-tarjetas;
 * el diseño está en style.css, sección "Tablas".
 *
 * Funciona también con las filas que se agregan después (DataTables, AJAX,
 * modales) y junto a DataTables Responsive.
 * Para que una tabla nunca pase a tarjetas: clase .tabla-sin-tarjetas.
 *
 * Al final del archivo está el orden por defecto de las tablas con DataTables
 * (primero el registro más reciente).
 */
(function () {
    'use strict';

    if (window.tablasResponsivasListas || !window.matchMedia) {
        return;
    }
    window.tablasResponsivasListas = true;

    var celular = window.matchMedia('(max-width: 767.98px)');
    var tablet = window.matchMedia('(max-width: 991.98px)');

    // En tablets solo pasan a tarjetas las tablas con esta cantidad de columnas o más
    var COLUMNAS_TABLA_ANCHA = 6;
    // Textos más largos que esto ocupan todo el ancho de la tarjeta
    var LARGO_TEXTO_ANCHO = 28;

    // Calendarios y cabeceras de DataTables con scroll no se tocan
    var NO_TOCAR = '.tabla-sin-tarjetas, .fc, .daterangepicker, .ui-datepicker, .datepicker, .flatpickr-calendar, .dataTables_scrollHead, .dataTables_scrollFoot';
    var NOMBRE_ACCIONES = /^(acci[oó]n(es)?|opci[oó]n(es)?|herramientas|gesti[oó]n)$/i;
    var CONTENIDO_ANCHO = 'ul, ol, p, table, textarea, select, .progress, input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"])';
    var CONTENIDO_SIN_TEXTO = 'img, svg, i, canvas, input:not([type="hidden"]), select, textarea, button, .badge, .btn';
    var CONTROLES = 'button, .btn, a, .dropdown, input[type="checkbox"], input[type="radio"]';

    function textoDe(elemento) {
        return (elemento.textContent || '').replace(/\s+/g, ' ').trim();
    }

    // Texto de la celda sin contar el de sus botones y enlaces
    function textoFueraDeControles(nodo) {
        var texto = '';
        for (var i = 0; i < nodo.childNodes.length; i++) {
            var hijo = nodo.childNodes[i];
            if (hijo.nodeType === 3) {
                texto += hijo.nodeValue;
            } else if (hijo.nodeType === 1 && !hijo.matches(CONTROLES + ', .dropdown-menu, i, svg, script, style')) {
                texto += textoFueraDeControles(hijo);
            }
        }
        return texto.replace(/\s+/g, ' ').trim();
    }

    // Un encabezado por columna, respetando colspan y rowspan
    function encabezadosDe(tabla) {
        var thead = tabla.tHead;
        if (!thead || !thead.rows.length) {
            return null;
        }
        var filas = thead.rows;
        var matriz = [];
        for (var f = 0; f < filas.length; f++) {
            matriz[f] = matriz[f] || [];
            var columna = 0;
            for (var c = 0; c < filas[f].cells.length; c++) {
                var celda = filas[f].cells[c];
                while (matriz[f][columna]) {
                    columna++;
                }
                var ancho = celda.colSpan || 1;
                var alto = Math.min(celda.rowSpan || 1, filas.length - f);
                for (var a = 0; a < alto; a++) {
                    matriz[f + a] = matriz[f + a] || [];
                    for (var b = 0; b < ancho; b++) {
                        matriz[f + a][columna + b] = celda;
                    }
                }
                columna += ancho;
            }
        }
        return matriz[filas.length - 1];
    }

    function nombreDe(th) {
        if (!th) {
            return '';
        }
        return textoDe(th) || (th.getAttribute('title') || '').trim();
    }

    function marcarCeldas(fila, encabezados) {
        var columna = 0;
        var unica = fila.cells.length === 1 && (fila.cells[0].colSpan || 1) > 1;
        var hayAcciones = false;

        for (var c = 0; c < fila.cells.length; c++) {
            var celda = fila.cells[c];
            var th = encabezados[columna];
            var nombre = nombreDe(th);
            var texto = textoDe(celda);
            var tieneControles = !!celda.querySelector(CONTROLES);
            var esAcciones = !unica && (NOMBRE_ACCIONES.test(nombre) ||
                (tieneControles && textoFueraDeControles(celda) === '' && !celda.querySelector('input[type="checkbox"], input[type="radio"]')));

            if (celda.getAttribute('data-label') !== nombre) {
                celda.setAttribute('data-label', nombre);
            }
            celda.classList.toggle('celda-unica', unica);
            celda.classList.toggle('celda-acciones', esAcciones);
            celda.classList.toggle('celda-ancha', !unica && !esAcciones &&
                (texto.length > LARGO_TEXTO_ANCHO || !!celda.querySelector(CONTENIDO_ANCHO) || (celda.colSpan || 1) > 1));
            celda.classList.toggle('celda-vacia', !unica && texto === '' && !celda.querySelector(CONTENIDO_SIN_TEXTO));

            // Columnas que DataTables Responsive esconde siempre (clase "never")
            if (th && th.classList.contains('never')) {
                celda.setAttribute('data-tabla-oculta', '');
            } else {
                celda.removeAttribute('data-tabla-oculta');
            }

            hayAcciones = hayAcciones || esAcciones;
            columna += celda.colSpan || 1;
        }
        return hayAcciones;
    }

    function prepararTabla(tabla) {
        var encabezados = tabla.tBodies.length && !tabla.closest(NO_TOCAR) ? encabezadosDe(tabla) : null;
        var usarTarjetas = !!encabezados && encabezados.length >= 2 &&
            (celular.matches || (tablet.matches && encabezados.length >= COLUMNAS_TABLA_ANCHA));

        tabla.classList.toggle('tabla-tarjetas', usarTarjetas);

        var scroll = tabla.closest('.dataTables_scroll');
        if (scroll) {
            scroll.classList.toggle('tabla-tarjetas-scroll', usarTarjetas);
        }

        if (!usarTarjetas) {
            return;
        }

        var grupos = Array.prototype.slice.call(tabla.tBodies);
        if (tabla.tFoot) {
            grupos.push(tabla.tFoot);
        }
        for (var g = 0; g < grupos.length; g++) {
            for (var r = 0; r < grupos[g].rows.length; r++) {
                var fila = grupos[g].rows[r];
                // La fila de detalle de DataTables Responsive se oculta en tarjetas
                if (!fila.classList.contains('child')) {
                    marcarCeldas(fila, encabezados);
                }
            }
        }
    }

    function prepararTodas() {
        var tablas = document.querySelectorAll('table.table');
        for (var i = 0; i < tablas.length; i++) {
            prepararTabla(tablas[i]);
        }
    }

    // Las tablas que cambian se revisan una sola vez por cuadro
    var pendientes = [];
    var programado = false;

    function encolar(tabla) {
        if (tabla && pendientes.indexOf(tabla) === -1) {
            pendientes.push(tabla);
        }
        if (!programado && pendientes.length) {
            programado = true;
            window.requestAnimationFrame(function () {
                var lote = pendientes;
                pendientes = [];
                programado = false;
                for (var i = 0; i < lote.length; i++) {
                    if (lote[i].isConnected) {
                        prepararTabla(lote[i]);
                    }
                }
            });
        }
    }

    function revisarCambios(cambios) {
        for (var i = 0; i < cambios.length; i++) {
            var objetivo = cambios[i].target;
            if (objetivo.closest) {
                encolar(objetivo.closest('table.table'));
            }
            var agregados = cambios[i].addedNodes;
            for (var j = 0; j < agregados.length; j++) {
                var nodo = agregados[j];
                if (nodo.nodeType !== 1) {
                    continue;
                }
                if (nodo.matches('table.table')) {
                    encolar(nodo);
                } else if (nodo.querySelector('table.table')) {
                    var internas = nodo.querySelectorAll('table.table');
                    for (var k = 0; k < internas.length; k++) {
                        encolar(internas[k]);
                    }
                }
            }
        }
    }

    function escuchar(consulta) {
        if (consulta.addEventListener) {
            consulta.addEventListener('change', prepararTodas);
        } else if (consulta.addListener) {
            consulta.addListener(prepararTodas);
        }
    }

    function iniciar() {
        prepararTodas();
        new MutationObserver(revisarCambios).observe(document.documentElement, { childList: true, subtree: true });
        escuchar(celular);
        escuchar(tablet);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();

/*
 * Orden de las tablas: primero lo más reciente
 *
 * Toda tabla con DataTables que no indique su propio orden parte ordenada de
 * forma descendente por su primera columna de "Fecha", así el último
 * documento o registro queda arriba.
 * No se usan las fechas que no son del registro: nacimiento, vencimiento,
 * próxima dosis, término, etc.
 *
 * También le enseña a DataTables a leer las fechas como se escriben en el
 * sistema (06/11/2026, 06-11-2026 o 2026-11-06, con hora o sin ella) para que
 * ordene por fecha real y no como texto.
 *
 * Para que una tabla conserve el orden con que llega: indicar "order" al crear
 * el DataTable o agregarle la clase .tabla-orden-propio.
 */
(function () {
    'use strict';

    if (window.tablasOrdenRecienteListo) {
        return;
    }
    window.tablasOrdenRecienteListo = true;

    var TIPO_FECHA = 'fecha-registro';
    var ENCABEZADO_FECHA = /fecha/i;
    var FECHA_QUE_NO_ES_DEL_REGISTRO = /nac|venc|pr[oó]x|t[eé]rmino|caduc|expira|l[ií]mite|fin\b/i;
    var FECHA_DIA_PRIMERO = /^(\d{1,2})[\/.\-](\d{1,2})[\/.\-](\d{4})(?:[\sT,]+(\d{1,2}):(\d{2})(?::(\d{2}))?)?/;
    var FECHA_ANIO_PRIMERO = /^(\d{4})[\/.\-](\d{1,2})[\/.\-](\d{1,2})(?:[\sT,]+(\d{1,2}):(\d{2})(?::(\d{2}))?)?/;
    var SIN_FECHA = /^(-+|—|–|s\/f|n\/a|sin fecha.*|sin registro.*|no registra.*)$/i;

    function textoPlano(dato) {
        if (dato === null || dato === undefined) {
            return '';
        }
        return String(dato)
            .replace(/<[^>]*>/g, ' ')
            .replace(/&nbsp;/gi, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    // Convierte la fecha en un número comparable (aaaammddhhmmss) o null si no es fecha
    function valorDeFecha(dato) {
        var texto = textoPlano(dato);
        var partes = FECHA_DIA_PRIMERO.exec(texto);
        var anio, mes, dia;

        if (partes) {
            dia = +partes[1];
            mes = +partes[2];
            anio = +partes[3];
        } else {
            partes = FECHA_ANIO_PRIMERO.exec(texto);
            if (!partes) {
                return null;
            }
            anio = +partes[1];
            mes = +partes[2];
            dia = +partes[3];
        }

        if (anio < 1900 || anio > 2200 || mes < 1 || mes > 12 || dia < 1 || dia > 31) {
            return null;
        }

        var hora = +(partes[4] || 0);
        var minuto = +(partes[5] || 0);
        var segundo = +(partes[6] || 0);
        return ((((anio * 100 + mes) * 100 + dia) * 100 + hora) * 100 + minuto) * 100 + segundo;
    }

    function registrarTipoFecha($) {
        var tipos = $.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.type;
        if (!tipos || tipos.order[TIPO_FECHA + '-pre']) {
            return;
        }

        // Va antes que los tipos de DataTables, que leen 06/11/2026 como 11 de junio
        tipos.detect.unshift(function (dato) {
            var texto = textoPlano(dato);
            if (texto === '' || SIN_FECHA.test(texto)) {
                return TIPO_FECHA;
            }
            return valorDeFecha(texto) !== null ? TIPO_FECHA : null;
        });

        // Las celdas sin fecha quedan al final
        tipos.order[TIPO_FECHA + '-pre'] = function (dato) {
            var valor = valorDeFecha(dato);
            return valor === null ? 0 : valor;
        };
    }

    function columnaDeFecha(ajustes) {
        var columnas = ajustes.aoColumns || [];
        for (var i = 0; i < columnas.length; i++) {
            var columna = columnas[i];
            if (columna.bSortable === false) {
                continue;
            }
            var titulo = textoPlano(columna.nTh ? columna.nTh.textContent : columna.sTitle);
            if (ENCABEZADO_FECHA.test(titulo) && !FECHA_QUE_NO_ES_DEL_REGISTRO.test(titulo)) {
                return i;
            }
        }
        return -1;
    }

    function escuchar($) {
        // preInit ocurre justo antes del primer orden de la tabla
        $(document).on('preInit.dt', function (evento, ajustes) {
            if (evento.namespace !== 'dt' || !ajustes) {
                return;
            }
            registrarTipoFecha($);

            var inicial = ajustes.oInit || {};
            var tabla = ajustes.nTable;
            var ordenGuardado = ajustes.oLoadedState && ajustes.oLoadedState.order;

            if (!ajustes.oFeatures || !ajustes.oFeatures.bSort || ordenGuardado ||
                inicial.order !== undefined || inicial.aaSorting !== undefined ||
                (tabla && tabla.classList.contains('tabla-orden-propio'))) {
                return;
            }

            var indice = columnaDeFecha(ajustes);
            if (indice !== -1) {
                ajustes.aaSorting = [[indice, 'desc']];
            }
        });
    }

    if (window.jQuery) {
        escuchar(window.jQuery);
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery) {
                escuchar(window.jQuery);
            }
        });
    }
})();
