     window.esVeterinariaBusqueda = @json(!empty($es_veterinaria_busqueda));
     // En el modal, la videoconsulta trae su propio catálogo; se juntan para buscar cada servicio por su clave
     window.catalogoVeterinarioBusqueda = {!! json_encode(array_merge($catalogo_veterinario_busqueda ?? [], $catalogo_videoconsulta_busqueda ?? []), JSON_UNESCAPED_UNICODE) !!};
     if (window.esVeterinariaBusqueda) {
        $('#label_buscar_especialidad_especialidad, #label_buscar_videoconsulta_especialidad').text('Motivo de consulta');
     }
     // En el modal del escritorio la mascota se elige en el primer paso; con una sola queda marcada
     window.idMascotaReserva = @json(
        !empty($mascota)
            ? (string) $mascota->id
            : (((isset($mascotas) ? $mascotas->count() : 0) === 1) ? (string) $mascotas->first()->id : '')
     );

     // Reserva por pasos: búsqueda, veterinario, fecha y hora, y confirmar. En el modal "Agendar hora"
     // del escritorio del tutor va antes la mascota, por eso los números se corren uno.
     // Acá solo se decide qué paso se ve; buscar, cargar horas y agendar siguen en la plantilla.
     const CON_PASO_MASCOTA_RESERVA = @json(!empty($con_paso_mascota));
     const PASO_BUSQUEDA_RESERVA_HORA = CON_PASO_MASCOTA_RESERVA ? 2 : 1;
     const TOTAL_PASOS_RESERVA_HORA = PASO_BUSQUEDA_RESERVA_HORA + 3;

     const reservaHoraPasos = {
        actual: 1,
        alcanzado: 1,
     };

     // Después de la búsqueda hacen falta resultados en pantalla, y en el modal, una mascota elegida
     function pasoDisponibleReservaHora(paso) {
        if (paso > reservaHoraPasos.alcanzado) {
            return false;
        }

        if (paso === 1) {
            return true;
        }

        if (CON_PASO_MASCOTA_RESERVA && !window.idMascotaReserva) {
            return false;
        }

        return paso === PASO_BUSQUEDA_RESERVA_HORA || $('#div_resultado_busqueda').children().length > 0;
     }

     function irPasoReservaHora(paso) {
        if (paso < 1 || paso > TOTAL_PASOS_RESERVA_HORA || !pasoDisponibleReservaHora(paso)) {
            return;
        }

        // A confirmar solo se entra eligiendo una hora, no desde el indicador
        if (paso < TOTAL_PASOS_RESERVA_HORA) {
            reservaHoraPasos.alcanzado = Math.min(reservaHoraPasos.alcanzado, TOTAL_PASOS_RESERVA_HORA - 1);
        }

        reservaHoraPasos.actual = paso;
        $('#vista_reserva_veterinaria .reserva-vet-panel').removeClass('activo')
            .filter('[data-paso="' + paso + '"]').addClass('activo');
        actualizarPasosReservaHora();

        // Si la tarjeta quedó a mitad de pantalla (pasa en celular) se vuelve a su comienzo
        const tarjeta = document.getElementById('vista_reserva_veterinaria');
        if (tarjeta && tarjeta.scrollIntoView && tarjeta.getBoundingClientRect().top < 0) {
            const sinAnimacion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            tarjeta.scrollIntoView({ behavior: sinAnimacion ? 'auto' : 'smooth', block: 'start' });
        }
     }

     // Marca el indicador según el paso actual y hasta dónde se puede volver
     function actualizarPasosReservaHora() {
        if (!pasoDisponibleReservaHora(reservaHoraPasos.actual)) {
            reservaHoraPasos.actual = 1;
            $('#vista_reserva_veterinaria .reserva-vet-panel').removeClass('activo')
                .filter('[data-paso="1"]').addClass('activo');
        }

        $('#reserva_hora_pasos .reserva-vet-paso').each(function() {
            const paso = Number($(this).data('paso'));
            const disponible = pasoDisponibleReservaHora(paso);
            const completado = disponible && paso !== reservaHoraPasos.actual && paso < reservaHoraPasos.alcanzado;

            $(this).toggleClass('activo', paso === reservaHoraPasos.actual).toggleClass('completado', completado);
            $(this).find('.reserva-vet-paso-numero').html(completado ? '<i class="feather icon-check"></i>' : paso);
            $(this).find('.reserva-vet-paso-boton')
                .prop('disabled', !disponible)
                .attr('aria-current', paso === reservaHoraPasos.actual ? 'step' : null);
        });

        // En el modal los botones del paso visible se ordenan en el pie
        if (typeof window.alCambiarPasoReservaHora === 'function') {
            window.alCambiarPasoReservaHora();
        }
     }

     // Llegó una búsqueda: se parte de nuevo desde los resultados
     function mostrarResultadosReservaHora() {
        reservaHoraPasos.alcanzado = PASO_BUSQUEDA_RESERVA_HORA + 1;
        $('#reserva_hora_veterinario').text('');
        irPasoReservaHora(PASO_BUSQUEDA_RESERVA_HORA + 1);
     }

     // Se eligió un veterinario: su nombre queda arriba y se pasa al calendario
     function abrirFechaHoraReservaHora(idProfesional) {
        const nombre = $('#div_resultado_busqueda .reserva-vet-veterinario[data-profesional="' + idProfesional + '"]')
            .first().find('.reserva-vet-lugar-nombre').text().trim();

        $('#reserva_hora_veterinario').text(nombre ? 'Con ' + nombre : '');
        $('#reserva_resumen_veterinario').text(nombre || '-');

        // En el modal, el paso de fecha vuelve a mostrar el calendario del veterinario (no el de peluquería u hotel)
        $('#reserva_fecha_veterinario').show();
        $('.reserva-servicio-fecha').hide();

        // Mientras cargan sus lugares no se muestra lo del veterinario anterior
        $('#modal_reserva_fecha').prop('disabled', true);
        $('#modal_reserva_dias_atencion, #modal_reserva_fecha_seleccionada').text('');

        reservaHoraPasos.alcanzado = PASO_BUSQUEDA_RESERVA_HORA + 2;
        irPasoReservaHora(PASO_BUSQUEDA_RESERVA_HORA + 2);
     }

     // "2026-10-12" queda como "Lunes 12 de octubre de 2026"
     function textoFechaReservaHora(fecha) {
        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        const dia = moment(fecha, 'YYYY-MM-DD');

        if (!dia.isValid()) {
            return fecha || '-';
        }

        return dias[dia.day()] + ' ' + dia.date() + ' de ' + meses[dia.month()] + ' de ' + dia.year();
     }

     // Ya hay hora elegida y datos del tutor: se arma el resumen y se pasa a confirmar
     function abrirConfirmacionReservaHora(fecha, hora) {
        $('#modal_reserva_hora_lista_horas .reserva-vet-hora').each(function() {
            const elegida = $(this).attr('data-hora') === hora;
            $(this).toggleClass('active', elegida).attr('aria-pressed', elegida ? 'true' : 'false');
        });

        $('#reserva_resumen_fecha').text(textoFechaReservaHora(fecha));
        $('#reserva_resumen_hora').text(moment(hora, 'HH:mm:ss').format('HH:mm') + ' hrs.');
        $('#reserva_resumen_lugar').text($('#modal_reserva_hora_lugar_atencion option:selected').text() || '-');

        reservaHoraPasos.alcanzado = TOTAL_PASOS_RESERVA_HORA;
        irPasoReservaHora(TOTAL_PASOS_RESERVA_HORA);
     }

     // La hora quedó agendada: se vuelve a la búsqueda. Para reservar otra hay que
     // elegir de nuevo al veterinario, así las horas se cargan actualizadas.
     // En el modal del escritorio, además, se cierra y se recarga "Mis próximas citas".
     function cerrarReservaHora() {
        $('#reserva_hora_veterinario').text('');
        reservaHoraPasos.alcanzado = PASO_BUSQUEDA_RESERVA_HORA + 1;
        irPasoReservaHora(PASO_BUSQUEDA_RESERVA_HORA);

        if (CON_PASO_MASCOTA_RESERVA && typeof window.reservaHoraAgendada === 'function') {
            window.reservaHoraAgendada();
        }
     }

     // Al cambiar la forma de búsqueda se borran los resultados y el indicador debe reflejarlo
     $('#Buscadores').on('click', '.nav-link', actualizarPasosReservaHora);

     // Paso de mascota (solo en el modal): la elegida viaja al resumen y a la reserva.
     // Las registradas se eligen en el select; la tarjeta "Mascota nueva" abre el registro rápido.
     function elegirMascotaReservaHora(select) {
        const $opcion = $(select).find('option:selected');
        const valor = String($(select).val() || '');

        window.idMascotaReserva = valor;
        $('#reserva_nombre_mascota').text(valor ? ($opcion.data('nombre') || '') : '');
        $('#btn_continuar_mascota_reserva').prop('disabled', !window.idMascotaReserva);
        if (valor) {
            limpiarMascotaNuevaReservaHora();
            mostrarMascotaNuevaReservaHora(false);
        }

        // Si ya había una hora elegida hay que volver a elegirla, así los vouchers son de esta mascota
        reservaHoraPasos.alcanzado = Math.min(reservaHoraPasos.alcanzado, TOTAL_PASOS_RESERVA_HORA - 1);
        actualizarPasosReservaHora();
     }

     function abrirMascotaNuevaReservaHora() {
        $('#reserva_hora_id_mascota').val('');
        window.idMascotaReserva = '';
        $('#reserva_nombre_mascota').text('');
        $('#btn_continuar_mascota_reserva').prop('disabled', true);
        reservaHoraPasos.alcanzado = 1;
        actualizarPasosReservaHora();
        mostrarMascotaNuevaReservaHora(true);
     }

     function mostrarMascotaNuevaReservaHora(mostrar) {
        $('#reserva_hora_mascota_nueva').toggle(mostrar);
        $('#reserva_hora_mascota_acciones').toggle(!mostrar);
        $('#btn_mascota_nueva_reserva').toggleClass('active', mostrar).attr('aria-pressed', mostrar ? 'true' : 'false');

        if (mostrar) {
            setTimeout(function() { $('#reserva_mascota_nombre').trigger('focus'); }, 100);
        }

        if (typeof window.alCambiarPasoReservaHora === 'function') {
            window.alCambiarPasoReservaHora();
        }
     }

     function limpiarMascotaNuevaReservaHora() {
        $('#reserva_mascota_nombre, #reserva_mascota_otra_especie').val('');
        $('#reserva_mascota_especie, #reserva_mascota_sexo').val('');
        $('#reserva_mascota_raza').html('<option value="">Mixto</option>');
        $('#reserva_mascota_otra_especie_caja, #reserva_mascota_raza_caja').hide();
     }

     // Canino y felino piden raza (opcional); "Otro" pide escribir la especie
     const razasMascotaReservaHora = {};
     function cambiarEspecieMascotaReservaHora() {
        const especie = $('#reserva_mascota_especie').val();
        const $raza = $('#reserva_mascota_raza');

        $('#reserva_mascota_otra_especie_caja').toggle(especie === 'otra');
        $('#reserva_mascota_raza_caja').toggle(especie === 'canina' || especie === 'felina');
        $raza.html('<option value="">Mixto</option>');

        if (especie !== 'canina' && especie !== 'felina') {
            return;
        }

        const pintar = function(razas) {
            razas.forEach(function(raza) {
                $raza.append(new Option(raza.nombre, raza.id));
            });
        };

        if (razasMascotaReservaHora[especie]) {
            pintar(razasMascotaReservaHora[especie]);
            return;
        }

        const idEspecie = window.especiesMascotaReserva ? window.especiesMascotaReserva[especie] : null;
        if (!idEspecie) {
            return;
        }

        $.get(@json(route('paciente.mascotas.razas', ['especie' => '__ESPECIE__'])).replace('__ESPECIE__', idEspecie))
            .done(function(data) {
                razasMascotaReservaHora[especie] = (data && Array.isArray(data.razas)) ? data.razas : [];
                if ($('#reserva_mascota_especie').val() === especie) {
                    pintar(razasMascotaReservaHora[especie]);
                }
            });
     }

     function cancelarMascotaNuevaReservaHora() {
        limpiarMascotaNuevaReservaHora();
        mostrarMascotaNuevaReservaHora(false);
     }

     // Guarda la mascota para el tutor de la cuenta y la deja elegida en el select
     function guardarMascotaNuevaReservaHora() {
        const datos = {
            _token: $('#_token').val(),
            nombre: $.trim($('#reserva_mascota_nombre').val()),
            especie: $('#reserva_mascota_especie').val(),
            otra_especie: $.trim($('#reserva_mascota_otra_especie').val()),
            raza_id: $('#reserva_mascota_raza').val(),
            sexo: $('#reserva_mascota_sexo').val(),
        };

        let faltan = '';
        if (!datos.nombre) faltan += 'Nombre\n';
        if (!datos.especie) faltan += 'Especie\n';
        if (datos.especie === 'otra' && !datos.otra_especie) faltan += '¿Cuál es la especie?\n';
        if (!datos.sexo) faltan += 'Sexo\n';

        if (faltan) {
            swal({ title: 'Complete los datos de la mascota', text: faltan, icon: 'warning', buttons: 'Aceptar' });
            return;
        }

        const $boton = $('#btn_guardar_mascota_reserva').prop('disabled', true);

        $.ajax({
            url: @json(route('paciente.mascotas.registro_rapido')),
            type: 'post',
            headers: { Accept: 'application/json' },
            data: datos,
        })
        .done(function(data) {
            const mascota = data && data.mascota ? data.mascota : null;
            if (!mascota) {
                swal({ title: 'No fue posible registrar la mascota', text: 'Intente nuevamente.', icon: 'error', buttons: 'Cerrar' });
                return;
            }

            const texto = mascota.nombre + (mascota.especie_texto ? ' — ' + mascota.especie_texto : '');
            const opcion = new Option(texto, mascota.id);
            $(opcion).attr('data-nombre', mascota.nombre);
            $('#reserva_hora_id_mascota').append(opcion).val(String(mascota.id));
            // Si era su primera mascota, desde ahora también se ve el select
            $('.reserva-vet-mascota-select, .reserva-vet-mascota-o').removeClass('d-none');
            $('#btn_mascota_nueva_reserva .reserva-vet-lugar-direccion').text('Agendar para una mascota que aún no está registrada');

            limpiarMascotaNuevaReservaHora();
            elegirMascotaReservaHora(document.getElementById('reserva_hora_id_mascota'));
        })
        .fail(function(xhr) {
            const respuesta = xhr.responseJSON || {};
            const errores = respuesta.errors ? Object.values(respuesta.errors).map(function(e) { return e[0]; }).join('\n') : '';
            swal({ title: 'No fue posible registrar la mascota', text: errores || respuesta.msj || 'Intente nuevamente.', icon: 'error', buttons: 'Cerrar' });
        })
        .always(function() {
            $boton.prop('disabled', false);
        });
     }

     function continuarMascotaReservaHora() {
        if (!window.idMascotaReserva) {
            return;
        }

        reservaHoraPasos.alcanzado = Math.max(reservaHoraPasos.alcanzado, PASO_BUSQUEDA_RESERVA_HORA);
        irPasoReservaHora(PASO_BUSQUEDA_RESERVA_HORA);
     }

     // Cada vez que se abre el modal se parte desde la mascota, sin resultados anteriores.
     // Con una sola mascota queda elegida; sin ninguna se abre directo el registro.
     function reiniciarReservaHora() {
        const $select = $('#reserva_hora_id_mascota');
        const $mascotas = $select.find('option').filter(function() {
            return this.value;
        });

        $('#div_resultado_busqueda').html('');
        $('#reserva_hora_veterinario').text('');
        limpiarMascotaNuevaReservaHora();
        mostrarMascotaNuevaReservaHora(false);
        reservaHoraPasos.alcanzado = 1;
        $select.val($mascotas.length === 1 ? $mascotas.val() : '');

        if ($select.length) {
            elegirMascotaReservaHora($select[0]);
        }
        irPasoReservaHora(1);

        if ($mascotas.length === 0) {
            abrirMascotaNuevaReservaHora();
        }
     }
