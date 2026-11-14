/*
 * Pestañas desplazables: a los pills con la clase "pill-con-flechas" les agrega una flecha a cada lado
 * cuando las pestañas no caben (por ejemplo en celular). Cada flecha mueve la fila y se esconde
 * al llegar al borde. Al abrir una pestaña, la fila se mueve para que quede a la vista.
 * Uso: <ul class="nav pill-moderno pill-con-flechas"> ... </ul> y cargar este archivo.
 */
(function () {
    'use strict';

    function prepararPestanas(lista) {
        if (lista.parentNode && lista.parentNode.classList.contains('pestanas-flechas')) {
            return;
        }

        var contenedor = document.createElement('div');
        contenedor.className = 'pestanas-flechas';
        lista.parentNode.insertBefore(contenedor, lista);
        contenedor.appendChild(lista);

        function crearFlecha(lado, texto, icono) {
            var boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'pestanas-flechas-boton pestanas-flechas-boton--' + lado;
            boton.setAttribute('aria-label', texto);
            boton.innerHTML = '<i class="feather ' + icono + '" aria-hidden="true"></i>';
            contenedor.appendChild(boton);
            return boton;
        }

        var izquierda = crearFlecha('izquierda', 'Ver pestañas anteriores', 'icon-chevron-left');
        var derecha = crearFlecha('derecha', 'Ver más pestañas', 'icon-chevron-right');

        function actualizar() {
            var maximo = lista.scrollWidth - lista.clientWidth;
            izquierda.classList.toggle('visible', lista.scrollLeft > 2);
            derecha.classList.toggle('visible', lista.scrollLeft < maximo - 2);
        }

        function mover(direccion) {
            var paso = Math.max(lista.clientWidth * 0.7, 120);
            if (typeof lista.scrollBy === 'function') {
                lista.scrollBy({ left: paso * direccion, behavior: 'smooth' });
            } else {
                lista.scrollLeft += paso * direccion;
            }
        }

        izquierda.addEventListener('click', function () { mover(-1); });
        derecha.addEventListener('click', function () { mover(1); });
        lista.addEventListener('scroll', actualizar, { passive: true });
        window.addEventListener('resize', actualizar);

        // La pestaña que se abre queda entera a la vista
        lista.addEventListener('click', function (evento) {
            var enlace = evento.target.closest ? evento.target.closest('.nav-link') : null;
            if (!enlace) {
                return;
            }
            var inicio = enlace.offsetLeft - lista.offsetLeft;
            var fin = inicio + enlace.offsetWidth;
            if (inicio < lista.scrollLeft || fin > lista.scrollLeft + lista.clientWidth) {
                lista.scrollTo({ left: inicio - 40, behavior: 'smooth' });
            }
        });

        actualizar();
        // Las fuentes pueden cambiar el ancho después de cargar
        window.addEventListener('load', actualizar);
    }

    function iniciar() {
        var listas = document.querySelectorAll('.pill-con-flechas');
        for (var i = 0; i < listas.length; i++) {
            prepararPestanas(listas[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
