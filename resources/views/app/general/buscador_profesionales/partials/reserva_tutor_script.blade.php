{{-- Flujo "Reservar hora" del tutor (va dentro de un <script>): especialidad en pastilla y lugares
     desplegables en cada veterinario, días de atención, fecha completa de las horas y botones de cada
     paso en el pie (#reserva_hora_pie). Lo usan el modal del escritorio del tutor y la página de
     reservar hora de cada mascota. $ambito es el contenedor de la reserva. --}}
        (function () {
            const $lista = $('#div_resultado_busqueda');

            // En cada veterinario se muestra la especialidad buscada, sin la palabra "consulta",
            // en una pastilla morada (como los convenios de la ficha). La ciudad queda debajo.
            function especialidadBuscada() {
                const pestana = $('#Buscadores .nav-link.active').attr('id');
                const valor = pestana === 'buscar_especialidad-tab' ? $('#buscar_especialidad_especialidad').val()
                    : (pestana === 'buscar_videoconsulta-tab' ? $('#buscar_videoconsulta_especialidad').val() : '');
                const limpio = String(valor || '').replace(/\bconsulta\b/gi, ' ').replace(/\s+/g, ' ').trim();

                return limpio ? limpio.charAt(0).toLocaleUpperCase('es') + limpio.slice(1) : 'Veterinario/a';
            }

            function pintarEspecialidadVeterinarios() {
                const especialidad = especialidadBuscada();
                $lista.find('.reserva-vet-veterinario[data-profesional]').each(function () {
                    const $datos = $(this).find('.reserva-vet-lugar-datos').first();
                    const $detalle = $datos.find('.reserva-vet-lugar-direccion').first();
                    if ($datos.find('.reserva-vet-especialidad').length) {
                        return;
                    }
                    // En la búsqueda por nombre la segunda línea trae la ciudad; se conserva
                    const lineas = $detalle.html().split(/<br\s*\/?>/i).map(function (l) { return $('<div>').html(l).text().trim(); });
                    const ciudad = especialidad === 'Veterinario/a' ? (lineas[1] || '') : '';
                    $detalle.text(ciudad).toggle(!!ciudad);
                    $('<span class="reserva-vet-especialidad">').text(especialidad).insertBefore($detalle);

                    // Antes de reservar se pueden revisar los lugares donde atiende, sin salir de la lista
                    const idProfesional = $(this).attr('data-profesional');
                    $('<div class="reserva-vet-lugares-atencion">')
                        .append(
                            $('<button type="button" class="reserva-vet-lugares-boton" aria-expanded="false">')
                                .attr('data-profesional', idProfesional)
                                .html('<i class="feather icon-map-pin"></i> Ver lugares de atención <i class="feather icon-chevron-down reserva-vet-lugares-flecha"></i>')
                        )
                        .append('<div class="reserva-vet-lugares-lista" style="display:none;"></div>')
                        .insertBefore($(this).children('.reserva-vet-acciones').first());
                });
            }

            const lugaresCargados = {};
            function direccionLugar(lugar) {
                const d = lugar.direccion;
                return d ? [d.direccion, d.numero_dir ? '#' + d.numero_dir : '', d.ciudad ? d.ciudad.nombre : ''].filter(Boolean).join(', ') : 'Dirección no informada';
            }
            // Próxima hora corta: "Sáb 14/11 · 09:30"
            function proximaCorta(proxima) {
                const dia = moment(proxima.dia + ' ' + proxima.hora, 'YYYY-MM-DD HH:mm:ss');
                if (!dia.isValid()) {
                    return '';
                }
                const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                return dias[dia.day()] + ' ' + dia.format('DD/MM') + ' · ' + dia.format('HH:mm');
            }

            function pintarLugares($caja, lugares, proximas) {
                if (!lugares.length) {
                    $caja.html('<p class="reserva-vet-lugares-vacio">Sin lugares de atención registrados.</p>');
                    return;
                }
                $caja.empty();
                lugares.forEach(function (lugar) {
                    const proxima = proximas && proximas[lugar.id] ? proximaCorta(proximas[lugar.id]) : '';
                    $('<div class="reserva-vet-lugar-atencion">')
                        .append(
                            $('<div class="reserva-vet-lugar-atencion-datos">')
                                .append($('<strong>').text(lugar.nombre || 'Sin nombre'))
                                .append($('<span>').append('<i class="feather icon-map-pin"></i> ').append(document.createTextNode(direccionLugar(lugar))))
                        )
                        .append(
                            $('<span class="reserva-vet-lugar-proxima">').toggleClass('sin-hora', !proxima)
                                .append('<i class="feather icon-calendar"></i> ')
                                .append(document.createTextNode(proxima || 'Sin horas'))
                        )
                        .appendTo($caja);
                });
            }

            $(document).on('click', '{{ $ambito }} .reserva-vet-lugares-boton', function () {
                const $boton = $(this);
                const $caja = $boton.next('.reserva-vet-lugares-lista');
                const abrir = $boton.attr('aria-expanded') !== 'true';
                const id = $boton.attr('data-profesional');

                $boton.attr('aria-expanded', abrir ? 'true' : 'false').toggleClass('abierto', abrir);
                $boton.contents().filter(function () { return this.nodeType === 3; }).first()
                    .replaceWith(abrir ? ' Ocultar lugares de atención ' : ' Ver lugares de atención ');
                $caja.toggle(abrir);
                window.alCambiarPasoReservaHora();
                if (!abrir) {
                    return;
                }

                if (lugaresCargados[id]) {
                    pintarLugares($caja, lugaresCargados[id].lugares, lugaresCargados[id].proximas);
                    return;
                }
                $caja.html('<p class="reserva-vet-lugares-vacio">Cargando lugares…</p>');
                // Lugares (de la ficha) y la próxima hora libre en cada uno
                const tipoAgenda = $('#Buscadores .nav-link.active').attr('id') === 'buscar_videoconsulta-tab' ? '3' : '1,2';
                $.when(
                    $.get(@json(route('profesional.informacionProfesional')), { id_profesional: id }),
                    $.get(@json(route('profesional.proximaHoraPorLugar')), { id_profesional: id, tipo_agenda: tipoAgenda })
                )
                    .done(function (ficha, proximas) {
                        // $.when entrega [datos, estado, xhr] por cada consulta
                        ficha = Array.isArray(ficha) ? ficha[0] : ficha;
                        proximas = Array.isArray(proximas) ? proximas[0] : proximas;
                        lugaresCargados[id] = {
                            lugares: (ficha && ficha.lugares_atencion) || [],
                            proximas: (proximas && proximas.registros) || {},
                        };
                        pintarLugares($caja, lugaresCargados[id].lugares, lugaresCargados[id].proximas);
                    })
                    .fail(function () {
                        $caja.html('<p class="reserva-vet-lugares-vacio">No pudimos cargar los lugares. Intente nuevamente.</p>');
                    });
            });

            // Cada búsqueda nueva pinta la especialidad y el desplegable de lugares
            const mostrarResultadosAnterior = window.mostrarResultadosReservaHora;
            window.mostrarResultadosReservaHora = function () {
                pintarEspecialidadVeterinarios();
                mostrarResultadosAnterior();
            };
        })();

        // Paso de fecha: "Atiende los días ..." se muestra solo con un lugar elegido y sus días ya cargados,
        // cada día con mayúscula inicial y sin comas sueltas (el buscador los arma como texto)
        (function () {
            const dias = document.getElementById('modal_reserva_dias_atencion');
            if (!dias) {
                return;
            }
            const $linea = $(dias).closest('p');
            let ordenando = false;

            function acomodarDias() {
                if (ordenando) {
                    return;
                }
                ordenando = true;
                const partes = String(dias.textContent || '').split(',')
                    .map(function (d) { return d.trim(); })
                    .filter(Boolean)
                    .map(function (d) { return d.charAt(0).toLocaleUpperCase('es') + d.slice(1); });
                const texto = partes.join(', ');
                if (dias.textContent !== texto) {
                    dias.textContent = texto;
                }
                $linea.toggle(!!$('#modal_reserva_hora_lugar_atencion').val() && !!texto);
                ordenando = false;
            }

            if (window.MutationObserver) {
                new MutationObserver(acomodarDias).observe(dias, { childList: true, characterData: true, subtree: true });
            }
            $(document).on('change', '#modal_reserva_hora_lugar_atencion', acomodarDias);
            acomodarDias();

            // "Horas disponibles": la fecha completa con el año ("Sábado 14 de noviembre de 2026").
            // El servidor la manda sin año, así que se arma con el día que se pidió.
            const fechaHoras = document.getElementById('modal_reserva_fecha_seleccionada');
            let ultimoDia = '';
            const cargarHorasAnterior = window.cargar_horas_disponibles_calendario_profesion;
            window.cargar_horas_disponibles_calendario_profesion = function (dia) {
                ultimoDia = dia;
                return cargarHorasAnterior.apply(this, arguments);
            };
            if (fechaHoras && window.MutationObserver) {
                new MutationObserver(function () {
                    const actual = fechaHoras.textContent || '';
                    if (!ultimoDia || !actual || actual.indexOf('No hay horas') === 0) {
                        return;
                    }
                    const completa = textoFechaReservaHora(ultimoDia);
                    if (actual !== completa) {
                        fechaHoras.textContent = completa;
                    }
                }).observe(fechaHoras, { childList: true, characterData: true, subtree: true });
            }
        })();

        // Cada paso trae sus botones dentro del panel; en este modal se pasan al pie.
        // Antes de mover los nuevos se devuelven los anteriores a su lugar (queda una marca donde estaban).
        window.alCambiarPasoReservaHora = function () {
            const $pie = $('#reserva_hora_pie');
            const $panel = $('#vista_reserva_veterinaria .reserva-vet-panel.activo');

            $pie.children('.reserva-vet-acciones').each(function () {
                const marca = $(this).data('marcaPie');
                if (marca && marca.parentNode) {
                    marca.parentNode.insertBefore(this, marca);
                }
            });

            // Solo los botones que se ven: no los de cada veterinario, pestañas inactivas ni bloques ocultos
            $panel.find('.reserva-vet-acciones').filter(function () {
                if (this.style.display === 'none' || $(this).closest('.reserva-vet-veterinario').length) {
                    return false;
                }
                return $(this).parentsUntil($panel).filter(function () {
                    return this.style.display === 'none' || $(this).hasClass('d-none') ||
                        ($(this).hasClass('tab-pane') && !$(this).hasClass('active'));
                }).length === 0;
            }).each(function () {
                if (!$(this).data('marcaPie')) {
                    const marca = document.createComment('botones del paso');
                    this.parentNode.insertBefore(marca, this);
                    $(this).data('marcaPie', marca);
                }
                $pie.append(this);
            });

            $pie.toggleClass('d-none', $pie.children().length === 0);
        };

        // Al cambiar la forma de búsqueda cambian los botones del pie
        $(document).on('shown.bs.tab', '#Buscadores [data-toggle="tab"]', function () {
            window.alCambiarPasoReservaHora();
        });
