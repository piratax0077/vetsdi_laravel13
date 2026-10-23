/*
 * Tooltip negro de Bootstrap para los botones con título.
 * En la plantilla del administrador jQuery UI se carga después de Bootstrap y se queda con
 * $.fn.tooltip, por eso aquí se usa directamente la clase Tooltip de Bootstrap (window.bootstrap).
 * Se crea al pasar el mouse, así también funciona en filas que se agregan después por AJAX.
 */
(function () {
    'use strict';

    var selectorTooltip = '[data-toggle="tooltip"], .btn-icon[title]';
    var tooltipsCreados = [];

    function claseTooltip() {
        return window.bootstrap && window.bootstrap.Tooltip ? window.bootstrap.Tooltip : null;
    }

    function ocultarTodos() {
        tooltipsCreados = tooltipsCreados.filter(function (tooltip) {
            // Los botones que ya no están en la página (tablas recargadas) se descartan
            if (!document.body.contains(tooltip.element)) {
                tooltip.dispose();
                return false;
            }
            tooltip.hide();
            return true;
        });
    }

    /*
     * pcoded.min.js hace $('[data-toggle="tooltip"]').tooltip() al cargar y, con jQuery UI, eso deja un tooltip blanco
     * que además quita el title del botón. Se desarma antes de crear el negro, así el title vuelve a su lugar.
     */
    function quitarTooltipJqueryUi(elemento) {
        var jq = window.jQuery;
        if (jq && jq(elemento).data('ui-tooltip')) {
            jq(elemento).tooltip('destroy');
        }
    }

    // Se escucha en captura para adelantarse al manejador de jQuery UI, que corre en el mismo botón
    document.addEventListener('mouseover', function (evento) {
        var Tooltip = claseTooltip();
        var elemento = Tooltip && evento.target.closest ? evento.target.closest(selectorTooltip) : null;
        if (!elemento || elemento.hasAttribute('data-tooltip-negro')) {
            return;
        }
        quitarTooltipJqueryUi(elemento);
        if (!elemento.getAttribute('title')) {
            return;
        }

        elemento.setAttribute('data-tooltip-negro', '1');
        var tooltip = new Tooltip(elemento, {
            container: 'body',
            boundary: 'window',
            placement: elemento.getAttribute('data-placement') || 'top',
            trigger: 'hover focus'
        });
        // Bootstrap busca la instancia en los datos del botón al recibir su mouseenter; sin esto crea un segundo tooltip
        if (window.jQuery) {
            window.jQuery(elemento).data('bs.tooltip', tooltip);
        }
        tooltipsCreados.push(tooltip);
        // La primera vez se muestra al tiro, porque el mouse ya está encima
        tooltip.show();
    }, true);

    // Al hacer clic (por ejemplo para abrir un modal) el tooltip no debe quedar pegado
    document.addEventListener('click', ocultarTodos, true);
})();
