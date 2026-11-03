(function () {
    'use strict';

    function formatearRut(valor) {
        var limpio = String(valor || '').replace(/[.\s\-‐]/g, '').toUpperCase();
        if (!/^\d{6,8}[\dK]$/.test(limpio)) return valor || '';
        return limpio.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + limpio.slice(-1);
    }

    function esRut(input) {
        return input instanceof HTMLInputElement && input.type !== 'hidden' &&
            [input.name || '', input.id || ''].some(function (nombre) {
                return /(^|[_-])rut($|[_-])/i.test(nombre);
            });
    }

    function preparar(root) {
        root.querySelectorAll('input').forEach(function (input) {
            if (esRut(input)) input.value = formatearRut(input.value);
        });
    }

    window.CentroFormularios = { formatearRut: formatearRut };
    document.addEventListener('blur', function (event) {
        if (esRut(event.target)) event.target.value = formatearRut(event.target.value);
    }, true);
    function iniciar() {
        preparar(document);
        if (window.jQuery) window.jQuery(document).on('shown.bs.modal', function () { preparar(document); });
        new MutationObserver(function (cambios) {
            cambios.forEach(function (cambio) {
                cambio.addedNodes.forEach(function (nodo) {
                    if (nodo.nodeType !== 1) return;
                    if (esRut(nodo)) nodo.value = formatearRut(nodo.value);
                    preparar(nodo);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
}());
