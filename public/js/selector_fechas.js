/*
 * Calendario moderno para los campos de fecha del sistema.
 *
 * Convierte los input[type="date"] y input[type="datetime-local"] en un
 * calendario flatpickr (copia local, sin CDN). El input original queda oculto
 * con el mismo name y el mismo formato de valor (Y-m-d), así que los
 * formularios y controladores siguen igual. Al usuario se le muestra dd/mm/aaaa.
 *
 * Si algún campo debe quedar con el calendario del navegador, agregarle
 * data-fecha-nativa o la clase fecha-nativa.
 */
(function () {
    'use strict';

    var script = document.currentScript;
    var rutaJs = script ? script.getAttribute('data-flatpickr-js') : '';
    var rutaCss = script ? script.getAttribute('data-flatpickr-css') : '';

    var espanol = {
        weekdays: {
            shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        },
        months: {
            shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        },
        ordinal: function () { return 'º'; },
        firstDayOfWeek: 1,
        rangeSeparator: ' a ',
        weekAbbreviation: 'Sem',
        scrollTitle: 'Desplace para cambiar',
        toggleTitle: 'Clic para cambiar',
        time_24hr: true
    };

    var valorNativo = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');
    var sincronizando = false;
    var abierto = null;

    function cargarFlatpickr(listo) {
        if (!document.querySelector('link[href*="flatpickr"]') && rutaCss) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = rutaCss;
            // Antes de style.css para que el tema propio mande
            var estilos = document.querySelector('link[href*="css/style.css"]');
            if (estilos) {
                estilos.parentNode.insertBefore(link, estilos);
            } else {
                document.head.appendChild(link);
            }
        }

        if (window.flatpickr) {
            listo();
            return;
        }
        if (!rutaJs) return;

        var s = document.createElement('script');
        s.src = rutaJs;
        s.onload = listo;
        document.head.appendChild(s);
    }

    function clasesVisibles(input) {
        var clases = (input.className || '').split(/\s+/).filter(function (c) {
            return c && c !== 'flatpickr-input' && c !== 'active';
        });
        clases.push('fecha-moderna');
        return clases.join(' ');
    }

    // Copia al campo visible lo que el código de cada vista le cambia al original
    function sincronizar(input) {
        var fp = input._flatpickr;
        if (!fp || !fp.altInput) return;
        var alt = fp.altInput;

        alt.className = clasesVisibles(input);
        alt.disabled = input.disabled;
        alt.required = input.required;
        alt.readOnly = input.readOnly;
        alt.style.cssText = input.style.cssText;
        alt.placeholder = input.getAttribute('placeholder') || (input.getAttribute('data-fecha-tipo') === 'datetime-local' ? 'dd/mm/aaaa hh:mm' : 'dd/mm/aaaa');
        if (input.title) alt.title = input.title;

        var abre = !input.readOnly && !input.disabled;
        if (fp.config.clickOpens !== abre) {
            fp.set('clickOpens', abre);
        }

        var min = input.getAttribute('min') || null;
        var max = input.getAttribute('max') || null;
        if (min !== (input._fechaMin || null)) {
            input._fechaMin = min;
            fp.set('minDate', min);
        }
        if (max !== (input._fechaMax || null)) {
            input._fechaMax = max;
            fp.set('maxDate', max);
        }
    }

    // Así un .val('2026-01-01') o un .value = '' desde cualquier vista también mueve el calendario
    function escucharValor(input) {
        if (!valorNativo || input._fechaValorEscuchado) return;
        input._fechaValorEscuchado = true;

        Object.defineProperty(input, 'value', {
            configurable: true,
            get: function () {
                return valorNativo.get.call(this);
            },
            set: function (valor) {
                valorNativo.set.call(this, valor);
                var fp = this._flatpickr;
                if (!fp || sincronizando) return;

                var texto = valor == null ? '' : String(valor);
                var actual = fp.selectedDates.length ? fp.formatDate(fp.selectedDates[0], fp.config.dateFormat) : '';
                if (texto === actual) return;

                sincronizando = true;
                try {
                    if (texto) {
                        fp.setDate(texto, false);
                    } else {
                        fp.clear(false);
                    }
                } finally {
                    sincronizando = false;
                }
            }
        });
    }

    function prepararVisible(input, fp) {
        var alt = fp.altInput;
        if (!alt) return;

        // Sin readonly para que el "required" del navegador siga funcionando,
        // pero sin teclado: la fecha se elige solo desde el calendario
        alt.removeAttribute('readonly');
        alt.setAttribute('inputmode', 'none');
        alt.setAttribute('autocomplete', 'off');

        alt.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' || e.key === 'Delete') {
                e.preventDefault();
                if (!input.readOnly && !input.disabled) fp.clear();
                return;
            }
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            if (e.key && e.key.length === 1) e.preventDefault();
        });
        alt.addEventListener('paste', function (e) { e.preventDefault(); });
        alt.addEventListener('drop', function (e) { e.preventDefault(); });

        fp.calendarContainer.classList.add('calendario-moderno');
        sincronizar(input);
    }

    function convertir(input) {
        if (input._flatpickr) return;
        if (input.hasAttribute('data-fecha-nativa') || input.classList.contains('fecha-nativa')) return;

        var tipo = input.getAttribute('data-fecha-tipo') || input.type;
        var conHora = tipo === 'datetime-local';

        input.setAttribute('data-fecha-tipo', conHora ? 'datetime-local' : 'date');
        input._fechaMin = input.getAttribute('min') || null;
        input._fechaMax = input.getAttribute('max') || null;
        escucharValor(input);

        window.flatpickr(input, {
            locale: espanol,
            dateFormat: conHora ? 'Y-m-d\\TH:i' : 'Y-m-d',
            altInput: true,
            altFormat: conHora ? 'd/m/Y H:i' : 'd/m/Y',
            altInputClass: clasesVisibles(input),
            enableTime: conHora,
            time_24hr: true,
            minuteIncrement: 1,
            allowInput: false,
            disableMobile: true,
            monthSelectorType: 'dropdown',
            minDate: input._fechaMin,
            maxDate: input._fechaMax,
            clickOpens: !input.readOnly && !input.disabled,
            onReady: function (fechas, texto, fp) {
                prepararVisible(input, fp);
            },
            onOpen: function (fechas, texto, fp) {
                abierto = fp;
            },
            onClose: function (fechas, texto, fp) {
                if (abierto === fp) abierto = null;
            }
        });

        if (window.MutationObserver) {
            new MutationObserver(function () {
                sincronizar(input);
            }).observe(input, {
                attributes: true,
                attributeFilter: ['class', 'style', 'disabled', 'readonly', 'required', 'min', 'max', 'placeholder', 'title']
            });
        }
    }

    // Filas o bloques clonados arrastran un input ya convertido sin su calendario
    function repararClonados(raiz) {
        var clonados = raiz.querySelectorAll('input[data-fecha-tipo]');
        for (var i = 0; i < clonados.length; i++) {
            var input = clonados[i];
            if (input._flatpickr) continue;

            var siguiente = input.nextElementSibling;
            if (siguiente && siguiente.classList.contains('fecha-moderna') && !siguiente._flatpickr) {
                siguiente.parentNode.removeChild(siguiente);
            }
            input.classList.remove('flatpickr-input');
            input.type = input.getAttribute('data-fecha-tipo');
            convertir(input);
        }
    }

    function buscar() {
        var campos = document.querySelectorAll('input[type="date"], input[type="datetime-local"]');
        for (var i = 0; i < campos.length; i++) {
            convertir(campos[i]);
        }
        repararClonados(document);

        // Los calendarios que arma cada vista por su cuenta también toman el tema
        var calendarios = document.querySelectorAll('.flatpickr-calendar:not(.calendario-moderno)');
        for (var j = 0; j < calendarios.length; j++) {
            calendarios[j].classList.add('calendario-moderno');
        }
    }

    var busquedaPendiente = false;
    function programarBusqueda() {
        if (busquedaPendiente) return;
        busquedaPendiente = true;
        var siguienteCuadro = window.requestAnimationFrame || function (fn) { return setTimeout(fn, 16); };
        siguienteCuadro(function () {
            busquedaPendiente = false;
            buscar();
        });
    }

    function iniciar() {
        window.flatpickr.l10ns.es = espanol;
        window.flatpickr.localize(espanol);

        // jQuery Validate ignora los ocultos; el input original de la fecha sí debe validarse
        if (window.jQuery && window.jQuery.validator && window.jQuery.validator.defaults.ignore === ':hidden') {
            window.jQuery.validator.defaults.ignore = ':hidden:not(.flatpickr-input)';
        }

        buscar();

        if (window.MutationObserver) {
            new MutationObserver(function (cambios) {
                for (var i = 0; i < cambios.length; i++) {
                    if (cambios[i].addedNodes.length) {
                        programarBusqueda();
                        return;
                    }
                }
            }).observe(document.body, { childList: true, subtree: true });
        }

        // Dentro de un modal de Bootstrap el foco no debe volver al modal al usar el selector de año o mes
        window.addEventListener('focusin', function (e) {
            if (e.target && e.target.closest && e.target.closest('.flatpickr-calendar')) {
                e.stopPropagation();
            }
        }, true);

        // Si el modal o la página se desplaza con el calendario abierto, lo acompaña
        document.addEventListener('scroll', function (e) {
            if (!abierto || !abierto.isOpen) return;
            if (e.target && e.target.closest && e.target.closest('.flatpickr-calendar')) return;
            abierto._positionCalendar();
        }, true);

        // El label del campo abre el calendario
        document.addEventListener('click', function (e) {
            var label = e.target && e.target.closest ? e.target.closest('label[for]') : null;
            if (!label) return;
            var campo = document.getElementById(label.getAttribute('for'));
            if (campo && campo._flatpickr && campo._flatpickr.altInput && !campo.disabled) {
                e.preventDefault();
                campo._flatpickr.altInput.focus();
            }
        });

        // form.reset() no pasa por el setter, así que se vuelve a leer el valor
        document.addEventListener('reset', function (e) {
            var formulario = e.target;
            setTimeout(function () {
                var campos = formulario.querySelectorAll ? formulario.querySelectorAll('input[data-fecha-tipo]') : [];
                for (var i = 0; i < campos.length; i++) {
                    var fp = campos[i]._flatpickr;
                    if (!fp) continue;
                    var valor = valorNativo.get.call(campos[i]);
                    sincronizando = true;
                    try {
                        if (valor) { fp.setDate(valor, false); } else { fp.clear(false); }
                    } finally {
                        sincronizando = false;
                    }
                }
            }, 0);
        }, true);
    }

    function arrancar() {
        cargarFlatpickr(iniciar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', arrancar);
    } else {
        arrancar();
    }
})();
