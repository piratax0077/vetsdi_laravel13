(function () {
    'use strict';

    // Abre en un modal la edición que ya viene en la página, sin salir de la lista.
    function abrirEdicion($, boton) {
        var enlace = boton.attr('href');
        var plantilla = document.getElementById(boton.attr('data-plantilla'));
        var contenido = plantilla && plantilla.content ? plantilla.content.querySelector('.modal') : null;
        // Sin plantilla se sigue el enlace como cualquier otro
        if (!contenido) { window.location.href = enlace; return; }

        var urlLista = window.location.href;
        var modal = $(document.importNode(contenido, true));
        modal.on('hidden.bs.modal', function () {
            modal.remove();
            window.history.replaceState(null, '', urlLista);
        });
        $(plantilla).after(modal);

        // Si el servidor rechaza los datos, vuelve a esta dirección y reabre esta misma edición
        window.history.replaceState(null, '', enlace);
        modal.modal('show');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form.classList || !form.classList.contains('vet-operacion-form')) return;
            if (form.dataset.enviando === '1') { event.preventDefault(); return; }
            form.dataset.enviando = '1';
            form.querySelectorAll('button[type="submit"]').forEach(function (button) { button.disabled = true; });
        });

        if (!window.jQuery) return;
        var $ = window.jQuery;

        // Las alertas flotantes se cierran solas a los 6 segundos
        setTimeout(function () { $('.alerta-flotante').alert('close'); }, 6000);

        // Modales del área: al cerrar una edición se vuelve a la lista, para que "Agregar" abra uno nuevo
        $('.modal-area-operacion').each(function () {
            var modal = $(this);
            modal.on('hidden.bs.modal', function () {
                var urlCerrar = modal.attr('data-url-cerrar');
                if (urlCerrar) window.location.href = urlCerrar;
            });
            if (modal.attr('data-abrir') === '1') modal.modal('show');
        });

        $(document).on('click', '.abrir-edicion-area', function (event) {
            event.preventDefault();
            abrirEdicion($, $(this));
        });
    });
}());
