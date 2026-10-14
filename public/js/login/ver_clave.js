/*
 * Boton del ojo para ver u ocultar una contrasena.
 *
 * Sirve para cualquier boton con data-ver-clave="id-del-campo". Los botones
 * vienen ocultos desde el HTML y aparecen aqui, asi no queda un boton sin
 * funcion cuando el navegador no ejecuta JavaScript.
 */
(function () {
    'use strict';

    Array.prototype.forEach.call(document.querySelectorAll('[data-ver-clave]'), function (boton) {
        var campo = document.getElementById(boton.getAttribute('data-ver-clave'));
        var icono = boton.querySelector('i');

        if (!campo) {
            return;
        }

        boton.hidden = false;

        boton.addEventListener('click', function () {
            var visible = campo.type === 'password';

            campo.type = visible ? 'text' : 'password';
            boton.setAttribute('aria-pressed', visible ? 'true' : 'false');
            boton.setAttribute('aria-label', visible ? 'Ocultar la contraseña' : 'Mostrar la contraseña');

            if (icono) {
                icono.className = visible ? 'feather icon-eye' : 'feather icon-eye-off';
            }
        });
    });
}());
