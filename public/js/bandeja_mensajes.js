/*
 * Bandeja de mensajes en dos paneles (template/partials/bandeja_mensajes.blade.php).
 * Cada vista entrega en "cargar" la función que trae el mensaje del servidor y devuelve
 * { texto, subtitulo, archivos: [{ nombre, url }], puedeResponder }.
 * Con "abrirAlIniciar" se abre un mensaje apenas carga la página.
 */
(function (window, $) {
    'use strict';

    function escapar(valor) {
        return $('<div>').text(valor == null ? '' : valor).html();
    }

    function esCelular() {
        return window.matchMedia ? window.matchMedia('(max-width: 991.98px)').matches : $(window).width() < 992;
    }

    function iniciar(opciones) {
        var $bandeja = $(opciones.bandeja || '#bandeja_mensajes');
        if (!$bandeja.length) {
            return null;
        }

        var $lectura = $bandeja.find('.bandeja-mensajes-lectura');
        var $vacia = $lectura.find('.bandeja-mensajes-lectura-vacia');
        var $contenido = $lectura.find('.bandeja-mensajes-lectura-contenido');
        var abierto = null;

        function campo(nombre) {
            return $lectura.find('[data-lectura="' + nombre + '"]');
        }

        function itemDe(id) {
            return $bandeja.find('.bandeja-mensajes-item[data-id="' + id + '"]');
        }

        // Baja los contadores de la página y del sobre del encabezado, y apaga el punto del desplegable
        function descontarNoLeido(id) {
            var $filtro = $bandeja.find('.bandeja-mensajes-filtro-contador');
            var restantes = Math.max(0, (parseInt($filtro.text(), 10) || 0) - 1);
            $filtro.text(restantes).toggleClass('d-none', restantes === 0);

            var $sobre = $('.mensajes-encabezado').not('.avisos-encabezado').find('.mensajes-encabezado-contador');
            if ($sobre.length) {
                var enSobre = Math.max(0, (parseInt($sobre.first().text(), 10) || 0) - 1);
                if (enSobre === 0) {
                    $sobre.remove();
                } else {
                    $sobre.text(enSobre);
                }
            }

            var $sinLeer = $('.mensajes-encabezado-sin-leer');
            if ($sinLeer.length) {
                var enDesplegable = Math.max(0, (parseInt($sinLeer.find('span').text(), 10) || 0) - 1);
                if (enDesplegable === 0) {
                    $sinLeer.remove();
                } else {
                    $sinLeer.find('span').text(enDesplegable);
                }
            }

            var $enDesplegable = $('.mensajes-encabezado-item[data-mensaje="' + id + '"]');
            $enDesplegable.removeClass('mensajes-encabezado-item-no-leido').find('.mensajes-encabezado-punto').remove();
            $enDesplegable.find('.pro-title').removeClass('font-weight-bold');
        }

        function marcarLeido(id) {
            var $item = itemDe(id);
            if (!$item.hasClass('no-leido')) {
                return;
            }
            $item.removeClass('no-leido').find('.mensajes-encabezado-punto').remove();
            descontarNoLeido(id);
        }

        function mostrarVacia() {
            abierto = null;
            $contenido.addClass('d-none');
            $vacia.removeClass('d-none');
            $bandeja.removeClass('leyendo');
            $bandeja.find('.bandeja-mensajes-item.activo').removeClass('activo').removeAttr('aria-current');
        }

        function pintarArchivos(archivos) {
            var $archivos = campo('archivos').empty();
            (archivos || []).forEach(function (archivo) {
                if (!archivo || !archivo.url) {
                    return;
                }
                $archivos.append(
                    '<a class="btn btn-outline-info btn-sm" target="_blank" rel="noopener" href="' + escapar(archivo.url) + '">' +
                    '<i class="feather icon-paperclip mr-1"></i>' + escapar(archivo.nombre || 'Archivo adjunto') + '</a>'
                );
            });
            $archivos.toggleClass('d-none', $archivos.children().length === 0);
        }

        function abrir(id) {
            var $item = itemDe(id);
            if (!$item.length) {
                return;
            }

            abierto = String(id);
            $bandeja.find('.bandeja-mensajes-item.activo').removeClass('activo').removeAttr('aria-current');
            $item.addClass('activo').attr('aria-current', 'true');

            // La cabecera sale de la lista; el cuerpo llega del servidor
            campo('foto').attr('src', $item.find('.bandeja-mensajes-foto').attr('src'));
            campo('remitente').text($item.data('remitente'));
            campo('detalle').text($item.data('detalle'));
            var lugar = $.trim($item.attr('data-lugar') || '');
            campo('lugar').toggleClass('d-none', lugar === '').find('span').text(lugar);
            campo('asunto').text($item.data('asunto'));
            campo('subtitulo').addClass('d-none').text('');
            campo('texto').html('<span class="bandeja-mensajes-lectura-aviso">Cargando mensaje…</span>');
            campo('archivos').addClass('d-none').empty();
            campo('respuesta').addClass('d-none');
            $('#bandeja_mensajes_respuesta').val('');

            $vacia.addClass('d-none');
            $contenido.removeClass('d-none');
            $bandeja.addClass('leyendo');
            $lectura.find('.bandeja-mensajes-lectura-cuerpo').scrollTop(0);

            if (esCelular()) {
                $('html, body').scrollTop(Math.max(0, $bandeja.offset().top - 90));
            }

            opciones.cargar(id).then(function (datos) {
                if (abierto !== String(id)) {
                    return;
                }
                datos = datos || {};

                var texto = $.trim(datos.texto || '');
                campo('texto').html(texto ? escapar(texto).replace(/\n/g, '<br>') : '<span class="bandeja-mensajes-lectura-aviso">Este mensaje no tiene texto.</span>');

                var subtitulo = $.trim(datos.subtitulo || '');
                campo('subtitulo').text(subtitulo).toggleClass('d-none', subtitulo === '');

                pintarArchivos(datos.archivos);
                campo('respuesta').toggleClass('d-none', !datos.puedeResponder);
                marcarLeido(id);
            }, function () {
                if (abierto !== String(id)) {
                    return;
                }
                campo('texto').html('<span class="bandeja-mensajes-lectura-aviso">No pudimos abrir el mensaje. Intenta de nuevo en unos minutos.</span>');
            });
        }

        // Saca un mensaje de la lista (por ejemplo, al eliminarlo)
        function quitar(id) {
            var $item = itemDe(id);
            if ($item.hasClass('no-leido')) {
                descontarNoLeido(id);
            }
            $item.closest('li').remove();
            if (abierto === String(id)) {
                mostrarVacia();
            }
            aplicarFiltro();
        }

        function aplicarFiltro() {
            var soloNoLeidos = $bandeja.find('.bandeja-mensajes-filtro.activo').data('filtro') === 'no-leidos';
            var visibles = 0;

            $bandeja.find('.bandeja-mensajes-item').each(function () {
                var $item = $(this);
                // El que se está leyendo se queda aunque ya no esté sin leer
                var mostrar = !soloNoLeidos || $item.hasClass('no-leido') || $item.hasClass('activo');
                $item.closest('li').toggleClass('d-none', !mostrar);
                if (mostrar) {
                    visibles++;
                }
            });

            $bandeja.find('.bandeja-mensajes-sin-resultados')
                .text(soloNoLeidos ? 'No tienes mensajes sin leer.' : 'No tienes mensajes.')
                .toggleClass('d-none', visibles > 0);
        }

        $bandeja.on('click', '.bandeja-mensajes-item', function () {
            abrir($(this).data('id'));
        });

        $bandeja.on('click', '.bandeja-mensajes-filtro', function () {
            $bandeja.find('.bandeja-mensajes-filtro').removeClass('activo').attr('aria-pressed', 'false');
            $(this).addClass('activo').attr('aria-pressed', 'true');
            aplicarFiltro();
        });

        $bandeja.on('click', '.bandeja-mensajes-volver', function () {
            $bandeja.removeClass('leyendo');
        });

        // Estando en la bandeja, el sobre del encabezado abre el mensaje sin recargar
        $(document).on('click', '.mensajes-encabezado-item[data-mensaje]', function (evento) {
            var id = $(this).attr('data-mensaje');
            if (!itemDe(id).length) {
                return;
            }
            evento.preventDefault();
            $(this).closest('.dropdown').find('[data-toggle="dropdown"]').dropdown('hide');
            abrir(id);
        });

        // Si se llega desde el sobre del encabezado se abre el mensaje elegido
        var elegido = opciones.abrirAlIniciar || (window.location.search.match(/[?&]mensaje=(\d+)/) || [])[1];
        if (elegido && itemDe(elegido).length) {
            abrir(elegido);
            var lista = $bandeja.find('.bandeja-mensajes-lista').get(0);
            var item = itemDe(elegido).get(0);
            if (lista && item && !esCelular()) {
                lista.scrollTop = Math.max(0, item.offsetTop - lista.offsetTop - 12);
            }
        }

        return {
            abrir: abrir,
            quitar: quitar,
            abierto: function () {
                return abierto;
            }
        };
    }

    window.BandejaMensajes = { iniciar: iniciar };
})(window, jQuery);
