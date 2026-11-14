{{-- Lógica de peluquería, hotel y cementerio en el modal "Reservar hora" del escritorio del tutor.
     Búsqueda (servicio, región y comuna) -> Resultado (peluquerías u hoteles) -> Fecha (servicios, días y horas) --}}
<script>
    (function () {
        const RUTAS = {
            peluquerias: @json(route('paciente.reserva_servicios.peluquerias')),
            peluqueriaHoras: @json(route('paciente.reserva_servicios.peluqueria_horas')),
            peluqueriaReservar: @json(route('paciente.reserva_servicios.peluqueria_reservar')),
            hoteles: @json(route('paciente.reserva_servicios.hoteles')),
        };
        const SERVICIOS_HOTEL = { hotel: 'Alojamiento', guarderia: 'Cuidado diurno' };
        const NOMBRES_DIAS = ['', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        const TEXTOS_RESULTADO = {
            veterinario: ['Selecciona a un profesional', ''],
            peluqueria: ['Elija una peluquería', 'Peluquerías de la zona elegida. Al reservar verá sus servicios, días y horas.'],
            hotel: ['Elija un hotel', 'Hoteles de la zona elegida. Al reservar elige el tipo de estadía y las fechas.'],
        };

        const estado = {
            flujo: 'veterinario',
            peluquerias: [],
            peluqueria: null,
            horaPeluqueria: '',
            hoteles: [],
            hotel: null,
            servicioHotel: '',
        };

        function peso(valor) {
            return '$' + Math.round(valor).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function texto(valor) {
            return $('<div>').text(valor == null ? '' : String(valor)).html();
        }

        function vacio(mensaje) {
            return '<div class="reserva-vet-horas-vacio"><i class="feather icon-search"></i><span>' + texto(mensaje) + '</span></div>';
        }

        function sinValor(mensaje) {
            return '<span class="reserva-servicio-sin-valor">' + texto(mensaje) + '</span>';
        }

        // Tarjeta con radio o checkbox, como la elección de lugar de la reserva veterinaria
        function tarjeta(tipo, nombre, valor, icono, titulo, detalle) {
            const $input = $('<input class="reserva-vet-lugar-radio">').attr({ type: tipo, name: nombre }).val(valor);
            const $caja = $('<span class="reserva-vet-lugar-caja">')
                .append('<span class="reserva-vet-lugar-icono"><i class="' + icono + '"></i></span>')
                .append($('<span class="reserva-vet-lugar-datos">')
                    .append($('<span class="reserva-vet-lugar-nombre">').text(titulo))
                    .append($('<span class="reserva-vet-lugar-direccion">').html(detalle)))
                .append('<span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>');

            return $('<label class="reserva-vet-lugar">').append($input, $caja);
        }

        // Resultado con el mismo aspecto que la tarjeta de un veterinario
        function tarjetaResultado(icono, titulo, detalle, valor, boton) {
            return '<div class="reserva-vet-lugar-caja reserva-vet-veterinario">' +
                '<span class="reserva-vet-lugar-icono reserva-servicio-icono"><i class="' + icono + '"></i></span>' +
                '<div class="reserva-vet-lugar-datos">' +
                    '<span class="reserva-vet-lugar-nombre">' + texto(titulo) + '</span>' +
                    '<span class="reserva-vet-lugar-direccion">' + detalle + '</span>' +
                '</div>' +
                '<div class="reserva-vet-veterinario-valor' + (valor ? '' : ' sin-valor') + '">' + (valor || '') + '</div>' +
                '<div class="reserva-vet-acciones">' + boton + '</div>' +
            '</div>';
        }

        function errorAjax(xhr, titulo) {
            const respuesta = (xhr && xhr.responseJSON) || {};
            const errores = respuesta.errors ? Object.values(respuesta.errors).map(function (e) { return e[0]; }).join('\n') : '';
            swal({ title: titulo, text: errores || respuesta.msj || respuesta.message || 'Intente nuevamente.', icon: 'error', buttons: 'Cerrar' });
        }

        function terminarReserva(titulo, detalle) {
            $('#modal_agenda_cita_tutor').modal('hide');
            swal({ title: titulo, text: detalle, icon: 'success', buttons: 'Aceptar' });
            // En la página de reservar hora de la mascota se vuelve a la búsqueda;
            // en el escritorio, además, se recarga "Mis próximas citas"
            if (typeof window.reservaHoraAgendada === 'function') {
                window.reservaHoraAgendada();
            }
        }

        function avisarPie() {
            if (typeof window.alCambiarPasoReservaHora === 'function') {
                window.alCambiarPasoReservaHora();
            }
        }

        function ubicacion() {
            return { id_region: $('#buscar_especialidad_region').val() || '', id_ciudad: $('#buscar_especialidad_comuna').val() || '' };
        }

        /* ----- Paso de búsqueda: qué campos se ven según el servicio ----- */

        window.alCambiarServicioReserva = function (area, esTelemedicina) {
            if (esTelemedicina) {
                return;
            }

            estado.flujo = area && area.flujo ? area.flujo : 'veterinario';
            const conBusqueda = estado.flujo !== 'cementerio';
            const conItems = estado.flujo === 'veterinario';

            // Peluquería y hotel buscan solo por región y comuna; cementerio todavía no busca
            $('#label_buscar_especialidad_especialidad').closest('.reserva-campo-veterinario').toggle(conItems);
            $('#buscar_especialidad_region, #buscar_especialidad_comuna').closest('.reserva-campo-veterinario').toggle(conBusqueda);
            $('#reserva_acciones_veterinario').toggle(conBusqueda);
            $('#btn_buscar_especialidad').html('<i class="feather icon-search"></i> ' + (conItems ? 'Buscar horas' : 'Buscar'));
            $('#reserva_servicio_cementerio').toggle(estado.flujo === 'cementerio');

            // Los resultados anteriores eran de otro servicio
            $('#div_resultado_busqueda').html('');
            if (typeof reservaHoraPasos !== 'undefined') {
                reservaHoraPasos.alcanzado = Math.min(reservaHoraPasos.alcanzado, PASO_BUSQUEDA_RESERVA_HORA);
            }
            if (typeof actualizarPasosReservaHora === 'function') {
                actualizarPasosReservaHora();
            } else {
                avisarPie();
            }
        };

        // Botón "Buscar" del paso de búsqueda
        window.buscarSegunServicioReserva = function () {
            // Para buscar hace falta el servicio (y su prestación, si la tiene) y al menos la región
            const sinServicio = !$('#buscar_especialidad_profesion').val() ||
                (estado.flujo === 'veterinario' && !$('#buscar_especialidad_especialidad').val());
            if (sinServicio || !$('#buscar_especialidad_region').val()) {
                swal({ title: 'Selecciona servicio y ubicación para continuar', icon: 'warning', buttons: 'OK' });
                return;
            }

            if (estado.flujo === 'peluqueria') {
                buscarPeluquerias();
            } else if (estado.flujo === 'hotel') {
                buscarHoteles();
            } else if (estado.flujo === 'veterinario') {
                buscar_profesional_especialidad();
            }
        };

        function mostrarResultado(flujo, html) {
            const textos = TEXTOS_RESULTADO[flujo] || TEXTOS_RESULTADO.veterinario;
            $('#reserva_resultado_titulo').text(textos[0]);
            $('#reserva_resultado_detalle').text(textos[1]).toggle(!!textos[1]);
            $('#div_resultado_busqueda').html(html);
            mostrarResultadosReservaHora();
        }

        // La búsqueda de veterinarios usa los textos de veterinario
        const mostrarResultadosOriginal = window.mostrarResultadosReservaHora;
        window.mostrarResultadosReservaHora = function () {
            if (estado.flujo === 'veterinario' || $('#buscar_especialidad-tab').attr('aria-selected') !== 'true') {
                $('#reserva_resultado_titulo').text(TEXTOS_RESULTADO.veterinario[0]);
                $('#reserva_resultado_detalle').text(TEXTOS_RESULTADO.veterinario[1]).toggle(!!TEXTOS_RESULTADO.veterinario[1]);
            }
            mostrarResultadosOriginal();
        };

        // Paso de fecha con el formulario de peluquería u hotel en vez del calendario del veterinario
        function abrirPasoFecha(flujo) {
            $('#reserva_fecha_veterinario').hide();
            $('.reserva-servicio-fecha').hide();
            $('#reserva_fecha_' + flujo).show();
            reservaHoraPasos.alcanzado = PASO_BUSQUEDA_RESERVA_HORA + 2;
            irPasoReservaHora(PASO_BUSQUEDA_RESERVA_HORA + 2);
        }

        // Al abrir el modal se parte sin servicio elegido
        window.reiniciarServiciosReserva = function () {
            const select = document.getElementById('buscar_especialidad_profesion');
            if (select) {
                select.value = '';
                buscar_tipo_especialidad(select);
            }
            estado.peluqueria = null;
            estado.hotel = null;
            limpiarFechaPeluqueria();
            limpiarHotel();
            $('#reserva_fecha_veterinario').show();
            $('.reserva-servicio-fecha').hide();
        };

        /* ----- Peluquería ----- */

        function buscarPeluquerias() {
            const $boton = $('#btn_buscar_especialidad').prop('disabled', true);
            $.get(RUTAS.peluquerias, ubicacion())
                .done(function (data) {
                    estado.peluquerias = (data && data.registros) || [];
                    if (!estado.peluquerias.length) {
                        mostrarResultado('peluqueria', vacio('No encontramos peluquerías en esa zona. Pruebe con otra comuna o solo la región.'));
                        return;
                    }
                    mostrarResultado('peluqueria', estado.peluquerias.map(function (lugar, indice) {
                        const precios = (lugar.procedimientos || []).map(function (p) { return p.precio; }).filter(Boolean);
                        const valor = precios.length ? 'Desde ' + peso(Math.min.apply(null, precios)) : 'Valor a consultar en la peluquería';
                        const detalle = texto(lugar.sucursal || '') + '<br>' + texto(lugar.direccion || '');
                        const boton = lugar.con_agenda
                            ? '<button type="button" class="btn btn-info btn-sm" onclick="elegirPeluqueriaReserva(' + indice + ');"><i class="feather icon-calendar"></i> Reservar</button>'
                            : '<button type="button" class="btn btn-secondary btn-sm" disabled><i class="feather icon-calendar"></i> Sin horario publicado</button>';
                        return tarjetaResultado('feather icon-scissors', lugar.nombre, detalle, valor, boton);
                    }).join(''));
                })
                .fail(function (xhr) { errorAjax(xhr, 'No pudimos buscar peluquerías'); })
                .always(function () { $boton.prop('disabled', false); });
        }

        function limpiarFechaPeluqueria() {
            estado.horaPeluqueria = '';
            $('#peluqueria_bloque_fecha').hide();
            $('#peluqueria_horas').empty();
            $('#peluqueria_fecha_texto').text('');
            $('#peluqueria_observaciones').val('');
            const fecha = document.getElementById('peluqueria_fecha');
            if (fecha && fecha._flatpickr) {
                fecha._flatpickr.destroy();
            }
            $('#btn_reservar_peluqueria').prop('disabled', true);
        }

        window.elegirPeluqueriaReserva = function (indice) {
            estado.peluqueria = estado.peluquerias[indice];
            limpiarFechaPeluqueria();

            $('#peluqueria_titulo').text(estado.peluqueria.nombre + (estado.peluqueria.sucursal ? ' · ' + estado.peluqueria.sucursal : ''));
            $('#peluqueria_detalle').text(estado.peluqueria.direccion || '');

            const $servicios = $('#peluqueria_servicios').empty();
            const procedimientos = estado.peluqueria.procedimientos || [];
            if (!procedimientos.length) {
                $servicios.html(vacio('Esta peluquería aún no publica sus servicios.'));
            }
            procedimientos.forEach(function (p) {
                const valor = p.precio ? peso(p.precio) : sinValor('Valor a consultar');
                $servicios.append(tarjeta('checkbox', 'peluqueria_servicio', p.id, 'feather icon-scissors', p.nombre, (p.duracion ? p.duracion + ' min · ' : '') + valor));
            });
            $('#peluqueria_total').text('');
            abrirPasoFecha('peluqueria');
        };

        function serviciosPeluqueria() {
            const ids = $('#peluqueria_servicios input:checked').map(function () { return Number(this.value); }).get();
            return (estado.peluqueria ? estado.peluqueria.procedimientos : []).filter(function (p) { return ids.indexOf(p.id) > -1; });
        }

        $(document).on('change', '#peluqueria_servicios input', function () {
            const elegidos = serviciosPeluqueria();
            const duracion = elegidos.reduce(function (t, p) { return t + (p.duracion || 0); }, 0);
            const conValor = elegidos.every(function (p) { return !!p.precio; });
            const total = elegidos.reduce(function (t, p) { return t + (p.precio || 0); }, 0);

            $('#peluqueria_total').text(!elegidos.length ? '' : (conValor
                ? 'Total referencial: ' + peso(total) + (duracion ? ' · ' + duracion + ' min' : '')
                : 'Algunos servicios tienen valor a consultar en la peluquería.'));

            limpiarFechaPeluqueria();
            if (elegidos.length) {
                abrirCalendarioPeluqueria(Math.max(duracion, 15));
            }
            avisarPie();
        });

        function abrirCalendarioPeluqueria(duracion) {
            $.get(RUTAS.peluqueriaHoras, { sucursal_id: estado.peluqueria.sucursal_id, duracion: duracion })
                .done(function (data) {
                    if (!data || data.estado !== 1) {
                        swal({ title: 'Sin horario disponible', text: (data && data.msj) || 'La peluquería aún no publica su horario.', icon: 'info', buttons: 'Aceptar' });
                        return;
                    }
                    $('#peluqueria_bloque_fecha').show();
                    const dias = (data.dias || []).map(Number);

                    window.flatpickr(document.getElementById('peluqueria_fecha'), {
                        inline: true,
                        minDate: data.desde,
                        maxDate: data.hasta,
                        locale: (window.flatpickr.l10ns && window.flatpickr.l10ns.es) || 'default',
                        disable: [function (fecha) { return dias.indexOf(fecha.getDay() || 7) === -1; }],
                        onChange: function (fechas, fechaTexto) {
                            cargarHorasPeluqueria(fechaTexto, duracion);
                        },
                    });
                    avisarPie();
                })
                .fail(function (xhr) { errorAjax(xhr, 'No pudimos cargar la agenda'); });
        }

        function cargarHorasPeluqueria(fecha, duracion) {
            const $horas = $('#peluqueria_horas').empty();
            estado.horaPeluqueria = '';
            $('#btn_reservar_peluqueria').prop('disabled', true);
            $('#peluqueria_fecha_texto').text(typeof textoFechaReservaHora === 'function' ? textoFechaReservaHora(fecha) : fecha);

            $.get(RUTAS.peluqueriaHoras, { sucursal_id: estado.peluqueria.sucursal_id, duracion: duracion, fecha: fecha })
                .done(function (data) {
                    const horas = (data && data.horas) || [];
                    if (!horas.length) {
                        $horas.html('<div class="reserva-vet-horas-vacio"><i class="feather icon-clock"></i><span>No quedan horas este día. Elija otro en el calendario.</span></div>');
                        return;
                    }
                    horas.forEach(function (hora) {
                        $('<button type="button" class="reserva-vet-hora" aria-pressed="false">').attr('data-hora', hora).text(hora).appendTo($horas);
                    });
                })
                .fail(function (xhr) { errorAjax(xhr, 'No pudimos cargar las horas'); });
        }

        $(document).on('click', '#peluqueria_horas .reserva-vet-hora', function () {
            estado.horaPeluqueria = $(this).attr('data-hora');
            $('#peluqueria_horas .reserva-vet-hora').removeClass('active').attr('aria-pressed', 'false');
            $(this).addClass('active').attr('aria-pressed', 'true');
            $('#btn_reservar_peluqueria').prop('disabled', false);
        });

        window.reservarPeluqueriaTutor = function () {
            const fp = document.getElementById('peluqueria_fecha')._flatpickr;
            const fecha = fp && fp.selectedDates.length ? fp.formatDate(fp.selectedDates[0], 'Y-m-d') : '';
            const elegidos = serviciosPeluqueria();

            if (!window.idMascotaReserva || !estado.peluqueria || !elegidos.length || !fecha || !estado.horaPeluqueria) {
                swal({ title: 'Faltan datos', text: 'Elija al menos un servicio, el día y la hora.', icon: 'warning', buttons: 'Aceptar' });
                return;
            }

            const $boton = $('#btn_reservar_peluqueria').prop('disabled', true);
            $.ajax({
                url: RUTAS.peluqueriaReservar,
                type: 'post',
                headers: { Accept: 'application/json' },
                data: {
                    _token: $('#_token').val(),
                    sucursal_id: estado.peluqueria.sucursal_id,
                    mascota_id: window.idMascotaReserva,
                    procedimiento_ids: elegidos.map(function (p) { return p.id; }),
                    inicio: fecha + 'T' + estado.horaPeluqueria,
                    observaciones: $('#peluqueria_observaciones').val(),
                },
            })
                .done(function () {
                    terminarReserva('Su hora de peluquería quedó reservada',
                        estado.peluqueria.nombre + ' · ' + moment(fecha, 'YYYY-MM-DD').format('DD/MM/YYYY') + ' a las ' + estado.horaPeluqueria + ' hrs.');
                })
                .fail(function (xhr) {
                    $boton.prop('disabled', false);
                    errorAjax(xhr, 'No fue posible reservar la hora');
                });
        };

        /* ----- Hotel ----- */

        function preciosHotel(hotel) {
            const p = hotel.precios || {};
            const partes = [];
            if (hotel.servicios.hotel && p.noche) partes.push('Noche ' + peso(p.noche));
            if (hotel.servicios.guarderia && p.semana) partes.push('Semana ' + peso(p.semana));
            if (hotel.servicios.guarderia && p.mes) partes.push('Mes ' + peso(p.mes));

            return partes.join(' · ');
        }

        function buscarHoteles() {
            const $boton = $('#btn_buscar_especialidad').prop('disabled', true);
            $.get(RUTAS.hoteles, ubicacion())
                .done(function (data) {
                    estado.hoteles = (data && data.registros) || [];
                    if (!estado.hoteles.length) {
                        mostrarResultado('hotel', vacio('No encontramos hoteles en esa zona. Pruebe con otra comuna o solo la región.'));
                        return;
                    }
                    mostrarResultado('hotel', estado.hoteles.map(function (hotel, indice) {
                        const tipos = Object.keys(hotel.servicios || {}).map(function (c) { return SERVICIOS_HOTEL[c]; }).filter(Boolean).join(' · ');
                        const detalle = texto(tipos) + '<br>' + texto(hotel.direccion || '');
                        const boton = '<button type="button" class="btn btn-info btn-sm" onclick="elegirHotelReserva(' + indice + ');"><i class="feather icon-calendar"></i> Reservar</button>';
                        return tarjetaResultado('feather icon-home', hotel.nombre, detalle, preciosHotel(hotel) || 'Valor a consultar con el hotel', boton);
                    }).join(''));
                    // Sin precios cargados el valor va en gris
                    $('#div_resultado_busqueda .reserva-vet-veterinario-valor').each(function () {
                        $(this).toggleClass('sin-valor', $(this).text().indexOf('consultar') > -1);
                    });
                })
                .fail(function (xhr) { errorAjax(xhr, 'No pudimos buscar hoteles'); })
                .always(function () { $boton.prop('disabled', false); });
        }

        function limpiarHotel() {
            estado.servicioHotel = '';
            $('#hotel_servicios input').prop('checked', false);
            $('#hotel_bloque_alojamiento, #hotel_bloque_guarderia, #hotel_bloque_indicaciones').hide();
            $('#hotel_ingreso, #hotel_salida, #hotel_guarderia_inicio, #hotel_indicaciones').val('');
            $('#btn_reservar_hotel').prop('disabled', true);
        }

        window.elegirHotelReserva = function (indice) {
            estado.hotel = estado.hoteles[indice];
            limpiarHotel();

            $('#hotel_titulo').text(estado.hotel.nombre);
            $('#hotel_detalle').text(estado.hotel.direccion || '');

            const $servicios = $('#hotel_servicios').empty();
            const p = estado.hotel.precios || {};
            Object.keys(estado.hotel.servicios || {}).forEach(function (clave) {
                if (!SERVICIOS_HOTEL[clave]) return;
                let valor;
                if (clave === 'hotel') {
                    valor = p.noche ? peso(p.noche) + ' por noche' : sinValor('Valor a consultar');
                } else {
                    const planes = [p.semana ? peso(p.semana) + ' semana' : '', p.mes ? peso(p.mes) + ' mes' : ''].filter(Boolean);
                    valor = planes.length ? planes.join(' · ') : sinValor('Valor a consultar');
                }
                const detalle = (clave === 'hotel' ? 'Fecha de ingreso y salida · ' : 'Plan por semana o por mes · ') + valor;
                $servicios.append(tarjeta('radio', 'hotel_servicio', clave, clave === 'hotel' ? 'feather icon-moon' : 'feather icon-sun', SERVICIOS_HOTEL[clave], detalle));
            });

            const dias = (estado.hotel.dias || []).map(function (d) { return NOMBRES_DIAS[d]; }).filter(Boolean);
            $('#hotel_guarderia_dias').text(dias.length ? 'El plan debe comenzar un día de atención: ' + dias.join(', ') + '.' : '');
            abrirPasoFecha('hotel');
        };

        $(document).on('change', '#hotel_servicios input', function () {
            estado.servicioHotel = this.value;
            $('#hotel_bloque_alojamiento').toggle(this.value === 'hotel');
            $('#hotel_bloque_guarderia').toggle(this.value === 'guarderia');
            $('#hotel_bloque_indicaciones').show();
            $('#btn_reservar_hotel').prop('disabled', false);
        });

        window.reservarHotelTutor = function () {
            if (!window.idMascotaReserva || !estado.hotel || !estado.servicioHotel) {
                swal({ title: 'Faltan datos', text: 'Elija el tipo de estadía.', icon: 'warning', buttons: 'Aceptar' });
                return;
            }

            const datos = {
                _token: $('#_token').val(),
                mascota_id: window.idMascotaReserva,
                servicio: estado.servicioHotel,
                indicaciones: $('#hotel_indicaciones').val(),
            };
            let faltan = '';
            if (estado.servicioHotel === 'hotel') {
                datos.inicio = $('#hotel_ingreso').val();
                datos.fin = $('#hotel_salida').val();
                if (!datos.inicio) faltan += 'Fecha de ingreso\n';
                if (!datos.fin) faltan += 'Fecha de salida\n';
            } else {
                datos.inicio = $('#hotel_guarderia_inicio').val();
                datos.periodo = $('#hotel_guarderia_periodo').val();
                datos.cantidad = $('#hotel_guarderia_cantidad').val();
                if (!datos.inicio) faltan += 'Fecha de inicio\n';
            }
            if (faltan) {
                swal({ title: 'Complete las fechas', text: faltan, icon: 'warning', buttons: 'Aceptar' });
                return;
            }

            const $boton = $('#btn_reservar_hotel').prop('disabled', true);
            $.ajax({ url: estado.hotel.solicitar, type: 'post', headers: { Accept: 'application/json' }, data: datos })
                .done(function () {
                    terminarReserva('Solicitud enviada', estado.hotel.nombre + ' revisará la disponibilidad y le confirmará la estadía.');
                })
                .fail(function (xhr) {
                    $boton.prop('disabled', false);
                    errorAjax(xhr, 'No fue posible enviar la solicitud');
                });
        };
    })();
</script>
