/*
 * Tarjeta para recuperar la contrasena.
 *
 * Se puede pedir un enlace al correo o un codigo al celular. Aqui solo se
 * cambia la opcion que esta a la vista; los envios los resuelve el servidor.
 */
(function () {
    'use strict';

    var tarjeta = document.getElementById('recuperar');

    if (!tarjeta) {
        return;
    }

    var opciones = tarjeta.querySelectorAll('[data-recuperar-con]');
    var paneles = {
        correo: document.getElementById('recuperar-correo'),
        celular: document.getElementById('recuperar-celular')
    };

    /** Deja el foco en el primer campo visible del panel. */
    function enfocarPanel(panel) {
        var campos = panel.querySelectorAll('input:not([type="hidden"])');

        for (var i = 0; i < campos.length; i++) {
            // offsetParent es null cuando el campo esta dentro de algo oculto.
            if (campos[i].offsetParent !== null) {
                campos[i].focus();
                return;
            }
        }
    }

    function mostrarOpcion(cual) {
        Array.prototype.forEach.call(opciones, function (opcion) {
            var activa = opcion.getAttribute('data-recuperar-con') === cual;

            if (activa) {
                opcion.classList.add('esta-activa');
            } else {
                opcion.classList.remove('esta-activa');
            }

            opcion.setAttribute('aria-selected', activa ? 'true' : 'false');
        });

        paneles.correo.hidden = cual !== 'correo';
        paneles.celular.hidden = cual !== 'celular';

        enfocarPanel(paneles[cual]);
    }

    Array.prototype.forEach.call(opciones, function (opcion) {
        opcion.addEventListener('click', function () {
            mostrarOpcion(opcion.getAttribute('data-recuperar-con'));
        });
    });

    // "Usar otro numero": se vuelve al campo del celular para pedir un codigo nuevo.
    var otroNumero = document.getElementById('recuperar-otro-numero');

    if (otroNumero) {
        otroNumero.addEventListener('click', function (evento) {
            evento.preventDefault();

            document.getElementById('recuperar-codigo').hidden = true;
            document.getElementById('recuperar-telefono').hidden = false;
            document.getElementById('telefono_recuperacion').focus();
        });
    }

    // El codigo son solo numeros: lo demas se descarta mientras se escribe o se pega.
    var codigo = document.getElementById('codigo_recuperacion');

    if (codigo) {
        codigo.addEventListener('input', function () {
            codigo.value = codigo.value.replace(/\D/g, '').slice(0, 6);
        });

        if (!tarjeta.hidden) {
            codigo.focus();
        }
    }
}());
