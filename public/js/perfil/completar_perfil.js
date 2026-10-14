/*
 * Formularios de perfil.
 *
 * Resuelve lo que depende de otra lista: comunas segun la region y razas segun
 * la especie. Tambien marca la opcion elegida para los navegadores que todavia
 * no entienden el selector :has.
 */
(function () {
    'use strict';

    /** Deja un select con las opciones recibidas, manteniendo la seleccion previa. */
    function llenarSelect(select, opciones, textoVacio, valorElegido) {
        select.innerHTML = '';

        var vacia = document.createElement('option');
        vacia.value = '';
        vacia.textContent = textoVacio;
        select.appendChild(vacia);

        opciones.forEach(function (opcion) {
            var item = document.createElement('option');
            item.value = opcion.valor;
            item.textContent = opcion.texto;

            if (String(opcion.valor) === String(valorElegido)) {
                item.selected = true;
            }

            select.appendChild(item);
        });
    }

    // Region -> comuna
    var region = document.getElementById('id_region');
    var comuna = document.getElementById('id_ciudad');

    if (region && comuna) {
        var urlComunas = comuna.getAttribute('data-url');
        var comunaElegida = comuna.getAttribute('data-elegida');

        var cargarComunas = function (conservarElegida) {
            if (!region.value) {
                llenarSelect(comuna, [], 'Primero elige una región', '');
                return;
            }

            $.getJSON(urlComunas, { region: region.value })
                .done(function (ciudades) {
                    llenarSelect(
                        comuna,
                        ciudades.map(function (ciudad) {
                            return { valor: ciudad.id, texto: ciudad.nombre };
                        }),
                        'Elige tu comuna',
                        conservarElegida ? comunaElegida : ''
                    );
                })
                .fail(function () {
                    llenarSelect(comuna, [], 'No pudimos cargar las comunas', '');
                });
        };

        region.addEventListener('change', function () {
            cargarComunas(false);
        });

        if (region.value) {
            cargarComunas(true);
        }
    }

    // Especie -> raza, con el catalogo ya incrustado en la pagina
    var especie = document.getElementById('mascota_especie');
    var raza = document.getElementById('mascota_raza');

    if (especie && raza) {
        var catalogo = {};

        try {
            catalogo = JSON.parse(document.getElementById('catalogo-razas').textContent);
        } catch (error) {
            catalogo = {};
        }

        var razaElegida = raza.getAttribute('data-elegida');

        var cargarRazas = function (conservarElegida) {
            var razas = catalogo[especie.value] || [];

            llenarSelect(
                raza,
                razas.map(function (item) {
                    return { valor: item.slug, texto: item.nombre };
                }),
                razas.length ? 'Elige la raza' : 'Sin razas registradas para esta especie',
                conservarElegida ? razaElegida : ''
            );
        };

        especie.addEventListener('change', function () {
            cargarRazas(false);
        });

        if (especie.value) {
            cargarRazas(true);
        }
    }

    // Marca visual de la opcion elegida
    Array.prototype.forEach.call(document.querySelectorAll('.perfil-opciones'), function (grupo) {
        var opciones = grupo.querySelectorAll('.perfil-opcion');

        var marcar = function () {
            Array.prototype.forEach.call(opciones, function (opcion) {
                var radio = opcion.querySelector('input[type="radio"]');
                opcion.classList.toggle('esta-elegida', Boolean(radio && radio.checked));
            });
        };

        grupo.addEventListener('change', marcar);
        marcar();
    });
}());
