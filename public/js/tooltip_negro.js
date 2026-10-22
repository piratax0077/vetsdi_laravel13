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

    document.addEventListener('mouseover', function (evento) {
        var Tooltip = claseTooltip();
        var elemento = Tooltip && evento.target.closest ? evento.target.closest(selectorTooltip) : null;
        if (!elemento || elemento.hasAttribute('data-tooltip-negro') || !elemento.getAttribute('title')) {
            return;
        }

        elemento.setAttribute('data-tooltip-negro', '1');
        var tooltip = new Tooltip(elemento, {
            container: 'body',
            boundary: 'window',
            placement: elemento.getAttribute('data-placement') || 'top',
            trigger: 'hover focus'
        });
        tooltipsCreados.push(tooltip);
        // La primera vez se muestra al tiro, porque el mouse ya está encima
        tooltip.show();
    });

    // Al hacer clic (por ejemplo para abrir un modal) el tooltip no debe quedar pegado
    document.addEventListener('click', ocultarTodos, true);
})();
