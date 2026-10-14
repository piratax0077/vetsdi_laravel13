/*
 * Pantalla para escribir la contrasena nueva.
 *
 * Avisa de inmediato si la contrasena no cumple la politica o si la
 * repeticion no coincide. El servidor vuelve a revisar lo mismo al guardar.
 */
(function () {
    'use strict';

    var formulario = document.getElementById('form_clave_nueva');

    if (!formulario) {
        return;
    }

    var clave = document.getElementById('password');
    var repeticion = document.getElementById('password_confirmation');
    var boton = document.getElementById('btn-cambiar-clave');

    function mensajeClave() {
        var valor = clave.value;

        if (valor === '') {
            return 'Ingresa una contraseña.';
        }

        if (valor.length < 8) {
            return 'La contraseña debe tener al menos 8 caracteres.';
        }

        // Hay minuscula si al pasar a mayusculas el texto cambia, y al reves.
        return valor !== valor.toUpperCase() && valor !== valor.toLowerCase() && /\d/.test(valor)
            ? ''
            : 'La contraseña debe incluir una mayúscula, una minúscula y un número.';
    }

    function mensajeRepeticion() {
        if (repeticion.value === '') {
            return 'Repite tu contraseña.';
        }

        return repeticion.value === clave.value ? '' : 'Las contraseñas no coinciden.';
    }

    /** Muestra u oculta el mensaje de error que acompana a un campo. */
    function mostrarError(campo, mensaje) {
        var aviso = formulario.querySelector('[data-error-de="' + campo.id + '"]');

        if (mensaje !== '') {
            campo.classList.add('es-invalido');
        } else {
            campo.classList.remove('es-invalido');
        }

        if (aviso) {
            aviso.textContent = mensaje;
            aviso.hidden = mensaje === '';
        }

        return mensaje === '';
    }

    // Un campo marcado en rojo se vuelve a revisar mientras la persona lo corrige.
    clave.addEventListener('input', function () {
        if (clave.classList.contains('es-invalido')) {
            mostrarError(clave, mensajeClave());
        }

        if (repeticion.classList.contains('es-invalido')) {
            mostrarError(repeticion, mensajeRepeticion());
        }
    });

    repeticion.addEventListener('input', function () {
        if (repeticion.classList.contains('es-invalido')) {
            mostrarError(repeticion, mensajeRepeticion());
        }
    });

    formulario.addEventListener('submit', function (evento) {
        var claveBien = mostrarError(clave, mensajeClave());
        var repeticionBien = mostrarError(repeticion, mensajeRepeticion());

        if (!claveBien || !repeticionBien) {
            evento.preventDefault();
            (claveBien ? repeticion : clave).focus();
            return;
        }

        // Evita un segundo envio mientras se guarda.
        boton.disabled = true;
        boton.textContent = 'Guardando…';
    });
}());
