/*
 * Buscador para los campos de especie y raza de las mascotas.
 *
 * Convierte en un buscador tipo Select2 los select marcados con:
 *   data-buscador-especie                  -> especie, con buscador
 *   data-buscador-raza                     -> raza, con buscador y "Agregar ... como otra raza"
 *   data-especie="#selector"               -> (en la raza) el select de especie del que depende
 *   data-sin-agregar                       -> (en la raza) solo buscar, sin agregar razas nuevas
 *   data-cambio-nativo                     -> avisa el cambio también a quien escucha con addEventListener
 *   data-cargar-razas                      -> (en la raza) carga sola las razas de la especie elegida
 *   data-valor-nombre                      -> (en la raza) el valor es el nombre de la raza y no su id
 *                                             (para formularios que guardan la raza como texto)
 *   data-elegida="..."                     -> (en la raza) valor a dejar elegido al cargar las razas
 *
 * Una raza escrita a mano se guarda en el catálogo (ruta razas_mascotas.crear) y queda elegida con su id,
 * así los formularios siguen enviando el id de la raza como siempre.
 * Select2 se carga desde la copia local si la página no lo trae.
 */
(function () {
    'use strict';

    var script = document.currentScript;
    var rutaJs = script ? script.getAttribute('data-select2-js') : '';
    var rutaCss = script ? script.getAttribute('data-select2-css') : '';
    var rutaCrear = script ? script.getAttribute('data-crear-raza') : '';
    var rutaListar = script ? script.getAttribute('data-listar-razas') : '';
    var token = script ? script.getAttribute('data-token') : '';

    var idioma = {
        noResults: function () { return 'Sin resultados'; },
        searching: function () { return 'Buscando…'; },
        errorLoading: function () { return 'No se pudieron cargar los resultados'; }
    };

    function normalizar(texto) {
        return String(texto || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/\s+/g, ' ').trim();
    }

    function avisar(titulo, texto) {
        if (typeof window.swal === 'function') {
            window.swal({ title: titulo, text: texto || '', icon: 'warning', buttons: 'OK' });
        } else {
            window.alert(titulo + (texto ? '\n' + texto : ''));
        }
    }

    function cargarSelect2(listo) {
        var $ = window.jQuery;
        if (!$) return;
        if (!document.querySelector('link[href*="select2"]') && rutaCss) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = rutaCss;
            // Antes de style.css para que mande el tema propio
            var estilos = document.querySelector('link[href*="css/style.css"]');
            if (estilos) {
                estilos.parentNode.insertBefore(link, estilos);
            } else {
                document.head.appendChild(link);
            }
        }
        if ($.fn.select2) {
            listo($);
            return;
        }
        if (!rutaJs) return;
        var s = document.createElement('script');
        s.src = rutaJs;
        s.onload = function () { listo(window.jQuery); };
        document.head.appendChild(s);
    }

    function padre($, el) {
        var $modal = $(el).closest('.modal');
        return $modal.length ? $modal : $(document.body);
    }

    function valorEspecie(el) {
        var selector = el.getAttribute('data-especie');
        var especie = selector ? document.querySelector(selector) : null;
        return especie ? especie.value : '';
    }

    function avisarCambio(el) {
        if (el.hasAttribute('data-cambio-nativo')) {
            el.dispatchEvent(new Event('change', { bubbles: false }));
        }
    }

    // Actualiza lo que muestra el buscador sin disparar "change" (así no se repiten los onchange de la vista)
    function refrescar($, el) {
        var buscador = $(el).data('select2');
        if (buscador && buscador.dataAdapter) {
            buscador.dataAdapter.current(function (datos) {
                buscador.trigger('selection:update', { data: datos });
            });
        }
    }

    // Cuando el código de la vista cambia las opciones, el buscador debe mostrarlo
    function seguirCambios($, el) {
        if (window.MutationObserver) {
            new MutationObserver(function () {
                refrescar($, el);
            }).observe(el, { childList: true });
        }
    }

    // El buscador toma el mismo aspecto del campo original (form-control o form-control-sm)
    function vestir($, el) {
        $(el).next('.select2-container')
            .addClass('selector-mascota')
            .toggleClass('selector-mascota-sm', el.classList.contains('form-control-sm'))
            .toggleClass('selector-mascota-control', el.classList.contains('form-control'));
    }

    function mejorarEspecie($, el) {
        $(el).select2({ width: '100%', dropdownParent: padre($, el), language: idioma })
            .on('select2:select', function () { avisarCambio(el); });
        vestir($, el);
        seguirCambios($, el);
    }

    // Razas de la especie elegida, desde el catálogo; se deja elegida la de data-elegida si viene
    function cargarRazas($, el) {
        var especie = valorEspecie(el);
        var conNombre = el.hasAttribute('data-valor-nombre');
        var elegida = el.getAttribute('data-elegida') || '';
        var consulta = (el._consultaRazas || 0) + 1;
        el._consultaRazas = consulta;

        $(el).empty().append(new Option(el.getAttribute('data-texto-vacio') || 'Seleccione', ''));
        if (!especie || especie === '0' || !rutaListar) {
            refrescar($, el);
            return;
        }

        $.get(rutaListar, { especie: especie }).done(function (respuesta) {
            if (el._consultaRazas !== consulta) return;
            ((respuesta && respuesta.razas) || []).forEach(function (raza) {
                el.appendChild(new Option(raza.nombre, conNombre ? raza.nombre : raza.id));
            });
            if (elegida) {
                if (!$(el).find('option').filter(function () { return this.value === String(elegida); }).length && conNombre) {
                    el.appendChild(new Option(elegida, elegida));
                }
                $(el).val(String(elegida));
            }
            refrescar($, el);
        });
    }

    function enlazarCarga($, el) {
        var selector = el.getAttribute('data-especie');
        if (!selector) return;
        $(document).on('change', selector, function () {
            el.setAttribute('data-elegida', '');
            cargarRazas($, el);
        });
        cargarRazas($, el);
    }

    function mejorarRaza($, el) {
        var agregar = !el.hasAttribute('data-sin-agregar') && !!rutaCrear;

        $(el).select2({
            width: '100%',
            dropdownParent: padre($, el),
            language: idioma,
            tags: agregar,
            createTag: function (params) {
                var termino = $.trim(params.term || '');
                if (termino.length < 2) return null;
                var existe = $(el).find('option').filter(function () {
                    return normalizar(this.text) === normalizar(termino);
                }).length;
                return existe ? null : { id: '__nueva__' + termino, text: termino, nueva: true };
            },
            // La opción de agregar va al final de la lista
            insertTag: function (datos, etiqueta) { datos.push(etiqueta); },
            templateResult: function (dato) {
                if (dato.nueva) {
                    return $('<span class="selector-raza-nueva">').text('Agregar “' + dato.text + '” como otra raza');
                }
                return dato.text;
            }
        }).on('select2:select', function (evento) {
            var dato = evento.params.data || {};
            if (dato.nueva) {
                guardarRaza($, el, dato.text);
            } else {
                avisarCambio(el);
            }
        });
        vestir($, el);
        seguirCambios($, el);
        if (el.hasAttribute('data-cargar-razas')) {
            enlazarCarga($, el);
        }
    }

    // Para que la vista pida de nuevo las razas (por ejemplo, al abrir otra mascota)
    window.recargarRazasMascota = function (el, elegida) {
        var $ = window.jQuery;
        if (!$ || !el) return;
        el.setAttribute('data-elegida', elegida == null ? '' : String(elegida));
        cargarRazas($, el);
    };

    function quitarTemporal($, el) {
        $(el).find('option').filter(function () { return String(this.value).indexOf('__nueva__') === 0; }).remove();
    }

    function guardarRaza($, el, nombre) {
        var especie = valorEspecie(el);
        if (!especie || especie === '0') {
            quitarTemporal($, el);
            $(el).val('').trigger('change');
            avisar('Elija primero la especie', 'Así la nueva raza queda en la especie correcta.');
            return;
        }

        $.ajax({
            url: rutaCrear,
            type: 'post',
            headers: { Accept: 'application/json' },
            data: {
                _token: token || $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val(),
                especie: especie,
                nombre: nombre
            }
        }).done(function (respuesta) {
            var raza = respuesta && respuesta.raza;
            quitarTemporal($, el);
            if (!raza) {
                $(el).val('').trigger('change');
                return;
            }
            var valor = el.hasAttribute('data-valor-nombre') ? raza.nombre : String(raza.id);
            if (!$(el).find('option').filter(function () { return this.value === valor; }).length) {
                $(el).append(new Option(raza.nombre, valor, false, false));
            }
            $(el).val(valor).trigger('change');
            avisarCambio(el);
        }).fail(function (xhr) {
            quitarTemporal($, el);
            $(el).val('').trigger('change');
            var r = (xhr && xhr.responseJSON) || {};
            var errores = r.errors ? Object.keys(r.errors).map(function (k) { return r.errors[k][0]; }).join('\n') : '';
            avisar('No se pudo agregar la raza', errores || r.msj || 'Intente nuevamente.');
        });
    }

    function buscar($) {
        $('select[data-buscador-especie]:not(.select2-hidden-accessible)').each(function () { mejorarEspecie($, this); });
        $('select[data-buscador-raza]:not(.select2-hidden-accessible)').each(function () { mejorarRaza($, this); });
    }

    function iniciar($) {
        // Si el código de la vista fija el valor con .val() sin avisar, el buscador igual lo muestra
        var valOriginal = $.fn.val;
        $.fn.val = function (valor) {
            var resultado = valOriginal.apply(this, arguments);
            if (arguments.length) {
                this.filter('select.select2-hidden-accessible[data-buscador-especie], select.select2-hidden-accessible[data-buscador-raza]')
                    .each(function () { refrescar($, this); });
            }
            return resultado;
        };

        buscar($);
        if (window.MutationObserver) {
            var pendiente = false;
            new MutationObserver(function (cambios) {
                for (var i = 0; i < cambios.length; i++) {
                    if (cambios[i].addedNodes.length && !pendiente) {
                        pendiente = true;
                        setTimeout(function () { pendiente = false; buscar($); }, 50);
                        return;
                    }
                }
            }).observe(document.body, { childList: true, subtree: true });
        }
    }

    function arrancar() {
        if (!document.querySelector('select[data-buscador-especie], select[data-buscador-raza]')) {
            // Puede que el formulario aparezca después (modales que se arman con ajax)
            if (window.MutationObserver) {
                var observador = new MutationObserver(function () {
                    if (document.querySelector('select[data-buscador-especie], select[data-buscador-raza]')) {
                        observador.disconnect();
                        cargarSelect2(iniciar);
                    }
                });
                observador.observe(document.body, { childList: true, subtree: true });
            }
            return;
        }
        cargarSelect2(iniciar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', arrancar);
    } else {
        arrancar();
    }
})();
