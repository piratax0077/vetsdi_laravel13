// Agenda de peluquería: calendario, modal de agendar hora en 3 pasos y estado de la reserva
(function () {
    const contenedor = document.getElementById('peluqueria-calendario');
    const modal = window.jQuery ? window.jQuery('#modal_agendar_hora') : null;
    if (!contenedor || !modal || !modal.length) return;

    const modalEstado = window.jQuery('#modal_estado_reserva');
    const form = document.getElementById('form_agendar_hora');
    // Mismo rango que muestra el calendario, en bloques de 15 minutos; es el que usa un lugar sin horario guardado
    const APERTURA = 8 * 60, CIERRE = 22 * 60, BLOQUE = 15;
    // Claves del horario de los lugares de atención según getDay() (0 = domingo)
    const DIAS_HORARIO = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
    const DIAS_CORTOS = {lunes: 'Lun', martes: 'Mar', miercoles: 'Mié', jueves: 'Jue', viernes: 'Vie', sabado: 'Sáb', domingo: 'Dom'};
    const ocupadasPorFecha = {};
    let calendario = null;

    function dos(numero) { return (numero < 10 ? '0' : '') + numero; }
    function fechaTexto(fecha) { return fecha.getFullYear() + '-' + dos(fecha.getMonth() + 1) + '-' + dos(fecha.getDate()); }
    function horaTexto(minutos) { return dos(Math.floor(minutos / 60)) + ':' + dos(minutos % 60); }
    function aMinutos(hora) { return Number(hora.slice(0, 2)) * 60 + Number(hora.slice(3, 5)); }
    function minutosDe(fecha) { return fecha.getHours() * 60 + fecha.getMinutes(); }
    function hoy() { return fechaTexto(new Date()); }
    function pesos(valor) { return '$' + Number(valor).toLocaleString('es-CL'); }
    function primeraMayuscula(texto) {
        texto = String(texto || '').trim();
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    }

    function fechaLarga(fecha) {
        const partes = fecha.split('-');
        return primeraMayuscula(new Date(Number(partes[0]), Number(partes[1]) - 1, Number(partes[2]))
            .toLocaleDateString('es-CL', {weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'}));
    }

    // Estado del pago guardado en la cita; las atenciones anteriores a la agenda nueva no lo tienen
    function textoPago(pago, valor) {
        if (!pago || !pago.estado_pago) return {clase: 'badge-secondary', texto: 'Sin registrar', detalle: ''};
        const efectivo = pago.forma_pago === 'efectivo';
        if (pago.estado_pago === 'pagado') {
            const monto = Number(pago.monto_recibido) || 0;
            return {clase: 'badge-success', texto: 'Pagado', detalle: (efectivo ? 'Efectivo' : 'Pago en línea') + ' · recibido ' + pesos(monto) + ' · vuelto ' + pesos(Math.max(monto - valor, 0))};
        }
        return {clase: 'badge-warning', texto: 'Pendiente', detalle: efectivo ? 'Efectivo · ' + pesos(valor) + ' por pagar en el local' : 'Pago en línea · ' + pesos(valor) + ' por pagar en la pasarela'};
    }

    // Estado de la reserva con el acceso a la ficha para atender
    function mostrarEstado(reserva) {
        document.getElementById('estado_reserva_fecha').textContent = fechaLarga(reserva.fecha);
        document.getElementById('estado_reserva_hora').textContent = reserva.hora;
        document.getElementById('estado_reserva_mascota').textContent = reserva.mascota || '-';
        document.getElementById('estado_reserva_tutor').textContent = reserva.tutor ? 'Tutor: ' + reserva.tutor : '';
        // El servicio de las atenciones del calendario viene tal como se guardó
        document.getElementById('estado_reserva_servicio').textContent = primeraMayuscula(reserva.servicio) || '-';
        const pago = document.getElementById('estado_reserva_pago');
        pago.className = 'badge ' + reserva.pago.clase;
        pago.textContent = reserva.pago.texto;
        document.getElementById('estado_reserva_pago_detalle').textContent = reserva.pago.detalle;
        document.getElementById('estado_reserva_ficha').href = reserva.ficha;
        modalEstado.modal('show');
    }

    function abrir(fecha, hora) {
        // Las fechas pasadas no se agendan: se parte desde hoy
        if (form) preparar(fecha && fecha >= hoy() ? fecha : hoy(), hora || '');
        modal.modal('show');
    }

    if (window.FullCalendar) {
        calendario = new FullCalendar.Calendar(contenedor, {
            locale: 'es', firstDay: 1, initialView: 'timeGridDay', initialDate: contenedor.dataset.fecha,
            headerToolbar: {left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay'},
            height: 'auto', allDaySlot: false, nowIndicator: true, slotMinTime: '08:00:00', slotMaxTime: '22:00:00',
            slotDuration: '00:15:00', selectable: true, selectOverlap: false, events: contenedor.dataset.eventos,
            // Solo se pueden elegir horarios de ahora en adelante
            selectAllow: function (seleccion) {
                return seleccion.allDay ? fechaTexto(seleccion.start) >= hoy() : seleccion.start >= new Date();
            },
            select: function (seleccion) {
                calendario.unselect();
                abrir(fechaTexto(seleccion.start), seleccion.allDay ? '' : horaTexto(minutosDe(seleccion.start)));
            },
            // Cada atención muestra su estado antes de entrar a la ficha (el título viene como "Mascota · Servicio")
            eventClick: function (info) {
                if (!info.event.url) return;
                info.jsEvent.preventDefault();
                const titulo = info.event.title.split(' · ');
                const datos = info.event.extendedProps || {};
                const inicio = info.event.start, fin = info.event.end || info.event.start;
                mostrarEstado({
                    fecha: fechaTexto(inicio), hora: horaTexto(minutosDe(inicio)) + ' a ' + horaTexto(minutosDe(fin)),
                    mascota: datos.mascota || titulo[0], tutor: datos.tutor || '',
                    servicio: datos.servicio || titulo.slice(1).join(' · '),
                    pago: textoPago(datos, Number(datos.valor) || 0),
                    ficha: info.event.url
                });
            },
            eventSourceFailure: function () {
                let aviso = document.getElementById('peluqueria-calendario-error');
                if (!aviso) {
                    aviso = document.createElement('p'); aviso.id = 'peluqueria-calendario-error'; aviso.className = 'alert alert-warning';
                    aviso.setAttribute('role', 'alert'); contenedor.before(aviso);
                }
                aviso.textContent = 'No se pudo cargar la agenda. Recarga la página para reintentar.';
            }
        });
        calendario.render();
    }

    document.getElementById('boton_abrir_agendar_hora').addEventListener('click', function () {
        abrir(calendario ? fechaTexto(calendario.getDate()) : contenedor.dataset.fecha, '');
    });

    // Sin procedimientos el modal solo invita a configurarlos
    if (!form) return;

    const pasos = form.querySelectorAll('.reserva-vet-paso');
    const paneles = form.querySelectorAll('.reserva-vet-panel');
    const campoFecha = document.getElementById('agendar_fecha');
    const campoHora = document.getElementById('agendar_hora');
    const cajaHoras = document.getElementById('agendar_horas');
    const avisoHora = document.getElementById('aviso_agendar_hora');
    const procedimiento = document.getElementById('agendar_procedimiento');
    const lugar = document.getElementById('agendar_lugar');
    const campoRut = document.getElementById('agendar_rut');
    const nuevoCliente = document.getElementById('agendar_nuevo_cliente');
    const mascotasTutor = document.getElementById('agendar_mascota_tutor');
    const region = document.getElementById('agendar_region');
    const comuna = document.getElementById('agendar_comuna');
    // Todas las comunas con su región, tal como vienen en el select, para volver a filtrarlas
    const comunas = Array.prototype.slice.call(comuna.options, 1).map(function (opcion) {
        return {id: opcion.value, nombre: opcion.text, region: opcion.dataset.region};
    });
    // Tutores ya registrados por RUT (sin puntos ni guión); una colección vacía llega como []
    const tutoresGuardados = JSON.parse(document.getElementById('agendar_tutores').textContent || '{}');
    const tutores = Array.isArray(tutoresGuardados) ? {} : tutoresGuardados;
    let estadoTutor = 'espera', tutorEncontrado = null, rutBuscado = '';
    const montoRecibido = document.getElementById('agendar_monto_recibido');
    const avisoPago = document.getElementById('aviso_forma_pago');
    const error = document.getElementById('error_agendar_hora');
    const botonCancelar = document.getElementById('agendar_cancelar');
    const botonAnterior = document.getElementById('agendar_anterior');
    const botonSiguiente = document.getElementById('agendar_siguiente');
    const boton = document.getElementById('boton_agendar_hora');
    // Errores del guardado: a qué paso pertenece cada campo (lo demás es del paso 2)
    const PASO_DE_CAMPO = {inicio: 2, procedimiento_id: 2, duracion: 2, servicio: 2, valor: 2, peso: 2, forma_pago: 3, monto_recibido: 3};
    let pasoActual = 1, pasoMaximo = 1, calendarioFecha = null;
    // Hora pedida (desde el calendario o antes de cambiar el procedimiento) que todavía no se pudo marcar
    let horaPedida = '';
    // Día pedido (desde el calendario o el último elegido): se vuelve a marcar si el lugar atiende ese día
    let fechaPedida = '';

    // flatpickr lo carga selector_fechas.js; el calendario del paso 2 se arma la primera vez que se abre el modal.
    // Los días en que el lugar elegido no atiende quedan bloqueados
    function conCalendario(listo, intentos) {
        if (calendarioFecha) { listo(); return; }
        if (!window.flatpickr) {
            if ((intentos || 0) < 50) setTimeout(function () { conCalendario(listo, (intentos || 0) + 1); }, 100);
            return;
        }
        const opciones = {
            inline: true, dateFormat: 'Y-m-d', minDate: 'today', disableMobile: true,
            disable: [function (dia) { return !tramoDelDia(fechaTexto(dia)); }]
        };
        if (window.flatpickr.l10ns.es) opciones.locale = window.flatpickr.l10ns.es;
        calendarioFecha = window.flatpickr(campoFecha, opciones);
        listo();
    }

    function opcionElegida(select) { return select.options[select.selectedIndex]; }

    // Horario del lugar elegido ({lunes: {abierto, desde, hasta}, ...}); null si el lugar no tiene uno guardado
    function horarioLugar() {
        if (!lugar.value) return null;
        try {
            const horario = JSON.parse(opcionElegida(lugar).dataset.horario || 'null');
            return horario && Object.keys(horario).length ? horario : null;
        } catch (e) { return null; }
    }

    function diaAbierto(dato) { return !!dato && !!dato.abierto && String(dato.abierto) !== '0' && !!dato.desde && !!dato.hasta; }

    // Desde y hasta (en minutos) en que atiende el lugar ese día; null si no hay lugar o ese día está cerrado
    function tramoDelDia(fecha) {
        if (!lugar.value || !fecha) return null;
        const horario = horarioLugar();
        if (!horario) return {desde: APERTURA, hasta: CIERRE};
        const partes = fecha.split('-');
        const dato = horario[DIAS_HORARIO[new Date(Number(partes[0]), Number(partes[1]) - 1, Number(partes[2])).getDay()]];
        return diaAbierto(dato) ? {desde: aMinutos(dato.desde), hasta: aMinutos(dato.hasta)} : null;
    }

    // Los días seguidos con las mismas horas se juntan, igual que en Lugares de atención (ej.: "Lun a Vie 09:00 – 18:00")
    function textoHorario() {
        const horario = horarioLugar();
        if (!horario) return 'Sin horario guardado: se muestran las horas de ' + horaTexto(APERTURA) + ' a ' + horaTexto(CIERRE) + '.';
        const tramos = [];
        let seguido = false;
        ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'].forEach(function (clave) {
            const dato = horario[clave];
            if (!diaAbierto(dato)) { seguido = false; return; }
            const horas = dato.desde + ' – ' + dato.hasta, ultimo = tramos[tramos.length - 1];
            if (seguido && ultimo.horas === horas) { ultimo.hasta = DIAS_CORTOS[clave]; ultimo.dias++; }
            else tramos.push({desde: DIAS_CORTOS[clave], hasta: DIAS_CORTOS[clave], dias: 1, horas: horas});
            seguido = true;
        });
        if (!tramos.length) return 'Este lugar no tiene días de atención en su horario.';
        return tramos.map(function (tramo) {
            const dias = tramo.dias === 1 ? tramo.desde : tramo.desde + (tramo.dias === 2 ? ' y ' : ' a ') + tramo.hasta;
            return dias + ' ' + tramo.horas;
        }).join(' · ');
    }

    // Al elegir el lugar se ve su dirección y horario, y el calendario se arma con sus días de atención
    function elegirLugar() {
        const elegido = !!lugar.value;
        const opcion = opcionElegida(lugar);
        campoFecha.disabled = !elegido;
        document.getElementById('agendar_lugar_detalle').classList.toggle('d-none', !elegido);
        document.getElementById('agendar_lugar_direccion').textContent = elegido ? (opcion.dataset.direccion || 'Sin dirección') : '';
        document.getElementById('agendar_lugar_horario').textContent = elegido ? textoHorario() : '';
        conCalendario(function () {
            const fecha = campoFecha.value || fechaPedida;
            calendarioFecha.redraw();
            if (fecha && fecha >= hoy() && tramoDelDia(fecha)) {
                calendarioFecha.setDate(fecha, false);
                campoFecha.value = fecha;
            } else {
                calendarioFecha.clear(false);
                campoFecha.value = '';
                // Muestra el mes del día pedido aunque ese día el lugar no atienda
                if (fecha) calendarioFecha.jumpToDate(fecha);
            }
            cargarHoras();
        });
    }

    // Lista de precios del procedimiento por especie y tamaño (la arma el modal; sin lista, el precio general)
    function tarifas() {
        if (!procedimiento.value) return [];
        const opcion = opcionElegida(procedimiento);
        try {
            const lista = JSON.parse(opcion.dataset.precios || '[]');
            if (lista.length) return lista;
        } catch (e) { /* se usa el precio general */ }
        return [{especie: 'Todas', tamano: 'Todos', duracion: Number(opcion.dataset.duracion), precio: Number(opcion.dataset.precio)}];
    }


    function sinTildes(texto) {
        texto = String(texto || '').trim().toLowerCase();
        return texto.normalize ? texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : texto;
    }

    // Pequeño hasta 10 kg, Mediano de 10 a 25 kg, Grande más de 25 kg (los mismos del panel de configuración)
    function calzaTamano(tarifa, peso) {
        if (tarifa.tamano === 'Pequeño') return peso <= 10;
        if (tarifa.tamano === 'Mediano') return peso > 10 && peso <= 25;
        if (tarifa.tamano === 'Grande') return peso > 25;
        if (tarifa.tamano === 'Peso') return peso >= Number(tarifa.peso_desde) && peso <= Number(tarifa.peso_hasta);
        return true;
    }

    // Especie, tipo de exótico y peso de la mascota elegida o de la que se está registrando
    function mascotaActual() {
        const registrada = !mascotasTutor.disabled && mascotasTutor.value !== '' && mascotasTutor.value !== 'nueva';
        const nueva = !document.getElementById('agendar_mascota_nueva').classList.contains('d-none');
        if (!registrada && !nueva) return null;
        const opcion = opcionElegida(mascotasTutor);
        const peso = form.elements.peso.value === '' ? null : Number(form.elements.peso.value);
        let especie = registrada ? opcion.dataset.especie : form.elements.especie.value;
        let exotico = registrada ? opcion.dataset.exotico : form.elements.especie_exotico.value;
        // Una mascota exótica registrada trae el animal en la especie, ej.: "Exótico (Conejo)"
        const partes = /^ex[oó]tico\s*\((.+)\)$/i.exec(String(especie || '').trim());
        if (partes) { especie = 'Exótico'; exotico = exotico || partes[1]; }
        return {especie: especie, exotico: exotico, peso: peso};
    }

    // Puntos por especie: -1 si el precio no es para esta especie (o este tipo de exótico)
    function puntosEspecie(tarifa, mascota) {
        if (!tarifa.especie || tarifa.especie === 'Todas') return 0;
        if (sinTildes(tarifa.especie) !== sinTildes(mascota.especie)) return -1;
        if (tarifa.especie === 'Exótico' && tarifa.especie_exotico) {
            return sinTildes(tarifa.especie_exotico) === sinTildes(mascota.exotico) ? 4 : -1;
        }
        return 2;
    }

    function necesitaPeso(mascota) {
        return tarifas().some(function (tarifa) {
            return puntosEspecie(tarifa, mascota) >= 0 && tarifa.tamano && tarifa.tamano !== 'Todos';
        });
    }

    // El precio que calza con la mascota; gana el más específico (especie y tipo de exótico, luego tamaño o peso)
    function tarifaMascota() {
        const mascota = mascotaActual();
        if (!mascota || !mascota.especie) return null;
        let elegida = null, puntaje = -1;
        tarifas().forEach(function (tarifa) {
            let puntos = puntosEspecie(tarifa, mascota);
            if (puntos < 0) return;
            if (tarifa.tamano && tarifa.tamano !== 'Todos') {
                if (mascota.peso === null || !calzaTamano(tarifa, mascota.peso)) return;
                puntos += 1;
            }
            if (puntos > puntaje) { elegida = tarifa; puntaje = puntos; }
        });
        return elegida;
    }

    // El precio de la mascota cuenta cuando ya se sabe lo que el procedimiento necesita (especie y, si hace falta, peso)
    function tarifaLista() {
        const mascota = mascotaActual();
        if (!mascota || !mascota.especie || (necesitaPeso(mascota) && mascota.peso === null)) return null;
        return tarifaMascota();
    }

    // Mientras falta el peso, los precios que le pueden tocar a su especie (sin mascota, todos)
    function tarifasPosibles() {
        const tarifa = tarifaLista();
        if (tarifa) return [tarifa];
        const mascota = mascotaActual();
        return tarifas().filter(function (opcion) { return !mascota || !mascota.especie || puntosEspecie(opcion, mascota) >= 0; });
    }

    // La de la mascota; si todavía no se sabe, la más larga posible, para que las horas libres alcancen
    function duracion() {
        return Math.max.apply(null, [0].concat(tarifasPosibles().map(function (tarifa) { return Number(tarifa.duracion) || 0; })));
    }

    function precio() {
        const tarifa = tarifaLista() || tarifas()[0];
        return tarifa ? Number(tarifa.precio) || 0 : 0;
    }

    // Paso 2: pide el peso si el precio de su especie depende del tamaño y avisa si el procedimiento no tiene precio para ella
    function mostrarValorMascota() {
        const mascota = mascotaActual();
        const conPeso = !!mascota && !!mascota.especie && necesitaPeso(mascota);
        document.getElementById('agendar_peso_contenedor').classList.toggle('d-none', !conPeso);
        form.elements.peso.disabled = !conPeso;
        const listo = !!procedimiento.value && !!mascota && !!mascota.especie && (!conPeso || mascota.peso !== null);
        const sinPrecio = listo && !tarifaLista();
        const aviso = document.getElementById('aviso_valor_mascota');
        aviso.textContent = sinPrecio ? 'Este procedimiento no tiene un precio para esta mascota. Elige otro o revisa sus precios en el panel de configuración.' : '';
        aviso.classList.toggle('d-none', !sinPrecio);
        mostrarResumen();
        return !sinPrecio;
    }

    function horario() {
        if (!campoHora.value) return '-';
        return campoHora.value + ' a ' + horaTexto(aMinutos(campoHora.value) + (duracion() || BLOQUE));
    }

    function irPaso(numero) {
        pasoActual = numero;
        pasoMaximo = Math.max(pasoMaximo, numero);
        pasos.forEach(function (paso) {
            const n = Number(paso.dataset.paso), botonPaso = paso.querySelector('button');
            paso.classList.toggle('activo', n === numero);
            paso.classList.toggle('completado', n !== numero && n < pasoMaximo);
            botonPaso.disabled = n > pasoMaximo;
            if (n === numero) botonPaso.setAttribute('aria-current', 'step'); else botonPaso.removeAttribute('aria-current');
        });
        paneles.forEach(function (panel) { panel.classList.toggle('activo', Number(panel.dataset.paso) === numero); });
        botonCancelar.classList.toggle('d-none', numero > 1);
        botonAnterior.classList.toggle('d-none', numero === 1);
        botonSiguiente.classList.toggle('d-none', numero === 3);
        boton.classList.toggle('d-none', numero !== 3);
        if (numero === 2) {
            mostrarValorMascota();
            cargarHoras();
        }
        if (numero === 3) llenarResumen();
    }

    // Revisa solo los campos del paso; lo que está oculto o deshabilitado no cuenta
    function validarPaso(numero) {
        const panel = form.querySelector('.reserva-vet-panel[data-paso="' + numero + '"]');
        panel.querySelectorAll('input[type="text"]:not([readonly]), input[type="email"], textarea').forEach(function (campo) {
            campo.value = campo.value.trim();
        });
        let valido = Array.prototype.filter.call(panel.querySelectorAll('input, select, textarea'), function (campo) {
            return !campo.checkValidity();
        }).length === 0;
        if (numero === 2 && !campoHora.value) {
            if (avisoHora.classList.contains('d-none')) mostrarAvisoHora(!lugar.value ? 'Elige el lugar de atención, luego la fecha y una hora.' : campoFecha.value ? 'Elige una hora disponible.' : 'Elige la fecha y luego una hora.');
            valido = false;
        }
        if (numero === 2 && !mostrarValorMascota()) valido = false;
        if (numero === 3) avisoPago.classList.toggle('d-none', !!formaPago());
        if (!valido) panel.classList.add('was-validated');
        return valido;
    }

    function siguiente() {
        if (!validarPaso(pasoActual)) return;
        error.classList.add('d-none');
        irPaso(pasoActual + 1);
    }

    function mostrarAvisoHora(texto) {
        avisoHora.textContent = texto;
        avisoHora.classList.remove('d-none');
    }

    // Con la mascota se ve su duración y su valor; si aún falta el peso, el rango posible (ej.: 30 a 60 min, $10.000 a $25.000)
    function mostrarResumen() {
        const lista = tarifasPosibles();
        const duraciones = lista.map(function (tarifa) { return Number(tarifa.duracion) || 0; });
        const valores = lista.map(function (tarifa) { return Number(tarifa.precio) || 0; });
        const menor = function (numeros) { return Math.min.apply(null, numeros); };
        const mayor = function (numeros) { return Math.max.apply(null, numeros); };
        document.getElementById('agendar_duracion').value = !lista.length ? ''
            : (menor(duraciones) === mayor(duraciones) ? mayor(duraciones) + ' min' : menor(duraciones) + ' a ' + mayor(duraciones) + ' min');
        document.getElementById('agendar_valor').value = !lista.length ? ''
            : (menor(valores) === mayor(valores) ? pesos(mayor(valores)) : pesos(menor(valores)) + ' a ' + pesos(mayor(valores)));
    }

    function minutoDelDia(texto, fecha) {
        const dia = texto.slice(0, 10);
        if (dia < fecha) return 0;
        if (dia > fecha) return 24 * 60;
        return aMinutos(texto.slice(11, 16));
    }

    // Reservas del día (las canceladas no vienen) como rangos en minutos
    function horasOcupadas(fecha) {
        if (ocupadasPorFecha[fecha]) return Promise.resolve(ocupadasPorFecha[fecha]);
        const partes = fecha.split('-');
        const siguienteDia = fechaTexto(new Date(Number(partes[0]), Number(partes[1]) - 1, Number(partes[2]) + 1));
        const url = contenedor.dataset.eventos + '?start=' + fecha + 'T00:00:00&end=' + siguienteDia + 'T00:00:00';
        return fetch(url, {headers: {Accept: 'application/json'}, credentials: 'same-origin'})
            .then(function (respuesta) {
                if (!respuesta.ok) throw new Error();
                return respuesta.json();
            })
            .then(function (eventos) {
                ocupadasPorFecha[fecha] = eventos.map(function (evento) {
                    return [minutoDelDia(evento.start, fecha), minutoDelDia(evento.end, fecha)];
                });
                return ocupadasPorFecha[fecha];
            })
            // Si no se pudo leer, el guardado igual revisa los choques de horario
            .catch(function () { return []; });
    }

    function horasVacias(icono, texto) {
        cajaHoras.innerHTML = '<div class="reserva-vet-horas-vacio"><i class="feather ' + icono + '"></i><span>' + texto + '</span></div>';
    }

    function cargarHoras() {
        const fecha = campoFecha.value;
        horaPedida = campoHora.value || horaPedida;
        campoHora.value = '';
        avisoHora.classList.add('d-none');
        mostrarResumen();
        if (!lugar.value || !fecha) {
            document.getElementById('agendar_horas_fecha').textContent = lugar.value ? 'Elige un día con atención en el calendario.' : 'Primero elige el lugar de atención.';
            cajaHoras.innerHTML = '';
            return;
        }
        document.getElementById('agendar_horas_fecha').textContent = fechaLarga(fecha);
        horasVacias('icon-clock', 'Buscando horas libres…');
        horasOcupadas(fecha).then(function (ocupadas) {
            if (campoFecha.value === fecha) llenarHoras(fecha, ocupadas);
        });
    }

    // Solo aparecen las horas del horario del lugar en que cabe completo el procedimiento elegido
    function llenarHoras(fecha, ocupadas) {
        const minutos = duracion() || BLOQUE;
        const tramo = tramoDelDia(fecha) || {desde: 0, hasta: 0};
        const ahora = new Date();
        const desde = fecha === fechaTexto(ahora) ? minutosDe(ahora) : -1;
        const libres = [];
        for (let inicio = tramo.desde; inicio + minutos <= tramo.hasta; inicio += BLOQUE) {
            const ocupada = ocupadas.some(function (rango) { return inicio < rango[1] && inicio + minutos > rango[0]; });
            if (inicio > desde && !ocupada) libres.push(horaTexto(inicio));
        }

        if (!libres.length) {
            horasVacias('icon-calendar', 'No quedan horas libres este día. Prueba con otra fecha.');
        } else {
            const grilla = document.createElement('div');
            grilla.className = 'reserva-vet-horas-grilla';
            libres.forEach(function (hora) {
                const opcion = document.createElement('button');
                opcion.type = 'button'; opcion.className = 'reserva-vet-hora'; opcion.dataset.hora = hora; opcion.textContent = hora;
                opcion.setAttribute('aria-pressed', 'false');
                grilla.appendChild(opcion);
            });
            cajaHoras.innerHTML = '';
            cajaHoras.appendChild(grilla);
        }

        if (horaPedida && libres.indexOf(horaPedida) !== -1) {
            marcarHora(horaPedida);
        } else if (horaPedida) {
            mostrarAvisoHora('La hora ' + horaPedida + ' no está disponible para este procedimiento. Elige otra.');
        }
        mostrarResumen();
    }

    function marcarHora(hora) {
        campoHora.value = hora;
        horaPedida = '';
        cajaHoras.querySelectorAll('.reserva-vet-hora').forEach(function (opcion) {
            const elegida = opcion.dataset.hora === hora;
            opcion.classList.toggle('active', elegida);
            opcion.setAttribute('aria-pressed', elegida ? 'true' : 'false');
        });
        avisoHora.classList.add('d-none');
        mostrarResumen();
    }

    function rutLimpio(texto) { return String(texto || '').replace(/[^0-9kK]/g, '').toUpperCase(); }

    // Mismo cálculo del dígito verificador que formatoRut() de funciones.js
    function rutValido(limpio) {
        const cuerpo = limpio.slice(0, -1), dv = limpio.slice(-1);
        if (cuerpo.length < 7 || !/^\d+$/.test(cuerpo)) return false;
        let suma = 0, multiplo = 2;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += Number(cuerpo.charAt(i)) * multiplo;
            multiplo = multiplo < 7 ? multiplo + 1 : 2;
        }
        const esperado = 11 - (suma % 11);
        return dv === (esperado === 11 ? '0' : esperado === 10 ? 'K' : String(esperado));
    }

    // Busca primero entre los clientes de la peluquería; si el formulario trae data-buscar-tutor, también en el sistema
    function buscarTutor(rut) {
        if (tutores[rut]) return Promise.resolve(tutores[rut]);
        const url = form.dataset.buscarTutor;
        if (!url) return Promise.resolve(null);
        return fetch(url + '?rut=' + encodeURIComponent(rut), {headers: {Accept: 'application/json'}, credentials: 'same-origin'})
            .then(function (respuesta) { return respuesta.ok ? respuesta.json() : null; })
            .then(function (datos) { return datos && datos.tutor ? datos : null; })
            .catch(function () { return null; });
    }

    // Al escribir: queda como 12345678-9 (con K mayúscula) y, si el RUT es válido, se busca al tutor
    function escribirRut() {
        const limpio = rutLimpio(campoRut.value).slice(0, 9);
        campoRut.value = limpio.length > 1 ? limpio.slice(0, -1) + '-' + limpio.slice(-1) : limpio;
        const valido = rutValido(limpio);
        campoRut.setCustomValidity(valido ? '' : 'RUT inválido');
        campoRut.classList.toggle('is-invalid', limpio.length >= 8 && !valido);
        if (!valido) {
            rutBuscado = '';
            if (estadoTutor !== 'espera') mostrarTutor('espera');
            return;
        }
        if (limpio === rutBuscado) return;
        rutBuscado = limpio;
        buscarTutor(limpio).then(function (encontrado) {
            if (rutBuscado === limpio) mostrarTutor(encontrado ? 'registrado' : 'nuevo', encontrado);
        });
    }

    // El controlador guarda un solo nombre de tutor: se arma con nombre y apellidos
    function armarTutor() {
        form.elements.tutor.value = [form.elements.nombres, form.elements.apellido_uno, form.elements.apellido_dos].map(function (campo) { return campo.value.trim(); }).filter(Boolean).join(' ');
    }

    // Lo que queda oculto va deshabilitado para que no se envíe ni se revise
    function mostrarSeccion(id, visible) {
        const seccion = document.getElementById(id);
        seccion.classList.toggle('d-none', !visible);
        seccion.querySelectorAll('input, select, textarea').forEach(function (campo) { campo.disabled = !visible; });
    }

    // espera: falta un RUT válido · registrado: se elige entre sus mascotas · nuevo: se registra todo
    function mostrarTutor(estado, datos) {
        // Si antes se había reconocido a otro tutor, sus datos no pasan al registro de uno nuevo
        if (tutorEncontrado) ['tutor', 'telefono', 'email'].forEach(function (campo) { form.elements[campo].value = ''; });
        estadoTutor = estado;
        tutorEncontrado = estado === 'registrado' ? datos : null;
        document.getElementById('agendar_tutor_espera').classList.toggle('d-none', estado !== 'espera');
        document.getElementById('agendar_tutor_registrado').classList.toggle('d-none', estado !== 'registrado');
        mostrarSeccion('agendar_tutor_nuevo', estado === 'nuevo');
        if (estado === 'nuevo') armarTutor();

        const mascotas = tutorEncontrado ? (tutorEncontrado.mascotas || []) : [];
        mascotasTutor.innerHTML = '';
        mascotasTutor.add(new Option('Seleccione una mascota', ''));
        mascotas.forEach(function (mascota) {
            const nombre = primeraMayuscula(mascota.nombre);
            const detalle = [mascota.especie, mascota.raza].filter(Boolean).join(' ');
            const opcion = new Option(nombre + (detalle ? ' · ' + detalle : ''), mascota.id);
            opcion.dataset.especie = mascota.especie || '';
            opcion.dataset.exotico = mascota.especie_exotico || '';
            opcion.dataset.nombre = nombre;
            opcion.dataset.cuidados = mascota.cuidados || '';
            mascotasTutor.add(opcion);
        });
        mascotasTutor.add(new Option('Agregar nueva mascota', 'nueva'));
        // Con una sola mascota registrada queda elegida de una vez
        if (mascotas.length === 1) mascotasTutor.selectedIndex = 1;
        // Sin mascotas registradas no hay nada que elegir: se pasa directo al registro
        document.getElementById('agendar_mascotas_contenedor').classList.toggle('d-none', !mascotas.length);
        mascotasTutor.disabled = !mascotas.length;

        if (tutorEncontrado) {
            document.querySelectorAll('#agendar_tutor_registrado [data-tutor]').forEach(function (dato) {
                dato.textContent = tutorEncontrado.tutor[dato.dataset.tutor] || '';
            });
        }
        elegirMascota();
    }

    // Una mascota registrada no envía datos nuevos; "Agregar nueva mascota" la registra con los datos del tutor encontrado.
    // Si el select queda en "nueva", el controlador igual usa nuevo_cliente y no lee cliente_id
    function elegirMascota() {
        const registrada = !mascotasTutor.disabled && mascotasTutor.value !== '' && mascotasTutor.value !== 'nueva';
        const mascotaNueva = estadoTutor === 'nuevo' || (estadoTutor === 'registrado' && (mascotasTutor.disabled || mascotasTutor.value === 'nueva'));
        mostrarSeccion('agendar_mascota_nueva', mascotaNueva);
        nuevoCliente.value = mascotaNueva ? '1' : '0';
        if (tutorEncontrado) {
            ['tutor', 'telefono', 'email'].forEach(function (campo) {
                form.elements[campo].value = tutorEncontrado.tutor[campo] || '';
                form.elements[campo].disabled = !mascotaNueva;
            });
        }
        const cuidados = document.getElementById('agendar_cuidados_mascota');
        const textoCuidados = registrada ? opcionElegida(mascotasTutor).dataset.cuidados : '';
        cuidados.querySelector('span').textContent = textoCuidados ? 'Cuidados y advertencias: ' + textoCuidados : '';
        cuidados.classList.toggle('d-none', !textoCuidados);
        mostrarExotico(false);
        mostrarValorMascota();
    }

    // Con "Exótico" se pide qué animal es; oculto queda deshabilitado para que no se envíe ni se revise
    function mostrarExotico(enfocar) {
        const visible = !document.getElementById('agendar_mascota_nueva').classList.contains('d-none');
        const exotico = visible && form.elements.especie.value === 'Exótico';
        const campo = document.getElementById('agendar_exotico');
        document.getElementById('agendar_exotico_contenedor').classList.toggle('d-none', !exotico);
        campo.disabled = !exotico;
        if (!exotico) campo.value = '';
        else if (enfocar) campo.focus();
    }

    // Muestra las comunas de la región elegida (o todas si no hay región) y conserva la elegida si sigue en la lista
    function llenarComunas() {
        const elegida = comuna.value;
        comuna.innerHTML = '';
        comuna.add(new Option('Seleccione', ''));
        comunas.forEach(function (dato) {
            if (region.value && dato.region !== region.value) return;
            const opcion = new Option(dato.nombre, dato.id);
            opcion.dataset.region = dato.region;
            comuna.add(opcion);
        });
        comuna.value = elegida;
        if (comuna.value !== elegida) comuna.value = '';
    }

    // Al elegir la comuna, su región se completa sola
    function completarRegion() {
        const opcion = opcionElegida(comuna);
        if (comuna.value && opcion.dataset.region) region.value = opcion.dataset.region;
    }

    function datosMascota() {
        const tutor = tutorEncontrado ? tutorEncontrado.tutor.tutor : form.elements.tutor.value;
        const registrada = !mascotasTutor.disabled && mascotasTutor.value !== '' && mascotasTutor.value !== 'nueva';
        return {mascota: registrada ? opcionElegida(mascotasTutor).dataset.nombre : form.elements.mascota.value, tutor: tutor};
    }

    function llenarResumen() {
        const mascota = datosMascota();
        const opcionLugar = opcionElegida(lugar);
        document.getElementById('agendar_resumen_lugar').textContent = lugar.value ? opcionLugar.dataset.nombre : '-';
        document.getElementById('agendar_resumen_lugar_direccion').textContent = lugar.value ? opcionLugar.dataset.direccion || '' : '';
        document.getElementById('agendar_resumen_fecha').textContent = campoFecha.value ? fechaLarga(campoFecha.value) : '-';
        document.getElementById('agendar_resumen_hora').textContent = horario();
        document.getElementById('agendar_resumen_servicio').textContent = procedimiento.value ? opcionElegida(procedimiento).dataset.nombre : '-';
        document.getElementById('agendar_resumen_mascota').textContent = mascota.mascota || '-';
        document.getElementById('agendar_resumen_tutor').textContent = mascota.tutor ? 'Tutor: ' + mascota.tutor : '';
        document.getElementById('agendar_resumen_total').textContent = pesos(precio());
        montoConTotal();
    }

    // El monto recibido parte con el total; mientras no se escriba otro, sigue al total si cambia el procedimiento
    function montoConTotal() {
        if (formaPago() === 'efectivo' && (montoRecibido.value === '' || montoRecibido.dataset.automatico === '1')) {
            montoRecibido.value = precio();
            montoRecibido.dataset.automatico = '1';
        }
        calcularVuelto();
    }

    function formaPago() {
        const marcada = form.querySelector('[name="forma_pago"]:checked');
        return marcada ? marcada.value : '';
    }

    function mostrarFormaPago() {
        const forma = formaPago();
        document.getElementById('agendar_pago_efectivo').classList.toggle('d-none', forma !== 'efectivo');
        document.getElementById('agendar_pago_linea').classList.toggle('d-none', forma !== 'en_linea');
        montoRecibido.disabled = forma !== 'efectivo';
        if (forma) avisoPago.classList.add('d-none');
        montoConTotal();
    }

    // En efectivo el monto es obligatorio y debe cubrir el total
    function calcularVuelto() {
        const total = precio();
        const monto = montoRecibido.value === '' ? null : Number(montoRecibido.value);
        const falta = monto !== null && monto < total;
        montoRecibido.setCustomValidity(falta ? 'Monto insuficiente' : '');
        montoRecibido.classList.toggle('is-invalid', falta);
        document.getElementById('aviso_monto_recibido').textContent = falta ? 'Faltan ' + pesos(total - monto) + ' para el total.' : 'Ingresa el monto recibido.';
        document.getElementById('agendar_vuelto').value = monto !== null && !falta ? pesos(monto - total) : '';
    }

    // Por ahora el pago no se guarda: el estado sale de lo elegido en el paso 3
    function estadoPago() {
        const total = precio();
        if (formaPago() === 'efectivo') {
            if (montoRecibido.value !== '' && Number(montoRecibido.value) >= total) {
                return {clase: 'badge-success', texto: 'Pagado', detalle: 'Efectivo · recibido ' + pesos(montoRecibido.value) + ' · vuelto ' + pesos(Number(montoRecibido.value) - total)};
            }
            return {clase: 'badge-warning', texto: 'Pendiente', detalle: 'Efectivo · ' + pesos(total) + ' por pagar en el local'};
        }
        return {clase: 'badge-warning', texto: 'Pendiente', detalle: 'Pago en línea · ' + pesos(total) + ' por pagar en la pasarela'};
    }

    function preparar(fecha, hora) {
        form.reset();
        paneles.forEach(function (panel) { panel.classList.remove('was-validated'); });
        error.classList.add('d-none');
        avisoPago.classList.add('d-none');
        boton.disabled = false;
        campoRut.setCustomValidity('');
        campoRut.classList.remove('is-invalid');
        rutBuscado = '';
        llenarComunas();
        // La fecha se marca al tener lugar, y solo si ese lugar atiende ese día
        campoFecha.value = '';
        fechaPedida = fecha;
        campoHora.value = '';
        horaPedida = hora;
        pasoMaximo = 1;
        irPaso(1);
        mostrarTutor('espera');
        mostrarFormaPago();
        elegirLugar();
    }

    function mensajeError(estado, datos) {
        if (datos.errors) return [].concat.apply([], Object.values(datos.errors)).join(' ');
        if (estado === 419) return 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
        if (estado === 404) return 'El procedimiento o la mascota ya no está disponible. Recarga la página.';
        return datos.message && estado < 500 ? datos.message : 'No se pudo agendar la hora. Inténtalo de nuevo.';
    }

    // El tutor y su mascota nueva quedan reconocidos por su RUT para la próxima hora de esta página
    function recordarMascota(cliente) {
        const rut = rutLimpio(campoRut.value);
        if (!rut || nuevoCliente.value !== '1') return;
        const ficha = tutores[rut] || (tutores[rut] = {
            tutor: {
                tutor: form.elements.tutor.value, telefono: form.elements.telefono.value, email: form.elements.email.value,
                direccion: [form.elements.direccion.value, comuna.value ? opcionElegida(comuna).text : ''].filter(Boolean).join(', ')
            },
            mascotas: []
        });
        if (ficha.mascotas.some(function (mascota) { return String(mascota.id) === String(cliente.id); })) return;
        const exotico = form.elements.especie_exotico.value;
        ficha.mascotas.push({id: cliente.id, nombre: cliente.mascota, especie: form.elements.especie.value, especie_exotico: exotico, raza: form.elements.raza.value, cuidados: form.elements.cuidados.value});
    }

    pasos.forEach(function (paso) {
        paso.querySelector('button').addEventListener('click', function () {
            const destino = Number(paso.dataset.paso);
            for (let n = pasoActual; n < destino; n++) {
                if (!validarPaso(n)) { irPaso(n); return; }
            }
            irPaso(destino);
        });
    });
    botonSiguiente.addEventListener('click', siguiente);
    botonAnterior.addEventListener('click', function () { irPaso(pasoActual - 1); });
    campoFecha.addEventListener('change', function () {
        fechaPedida = campoFecha.value;
        cargarHoras();
    });
    lugar.addEventListener('change', elegirLugar);
    procedimiento.addEventListener('change', function () {
        cargarHoras();
        mostrarValorMascota();
    });
    cajaHoras.addEventListener('click', function (evento) {
        const opcion = evento.target.closest('.reserva-vet-hora');
        if (opcion) marcarHora(opcion.dataset.hora);
    });
    campoRut.addEventListener('input', escribirRut);
    form.elements.nombres.addEventListener('input', armarTutor);
    form.elements.apellido_uno.addEventListener('input', armarTutor);
    form.elements.apellido_dos.addEventListener('input', armarTutor);
    mascotasTutor.addEventListener('change', elegirMascota);
    region.addEventListener('change', llenarComunas);
    comuna.addEventListener('change', completarRegion);
    document.getElementById('agendar_especie').addEventListener('change', function () {
        mostrarExotico(true);
        mostrarValorMascota();
    });
    document.getElementById('agendar_exotico').addEventListener('input', mostrarValorMascota);
    form.elements.peso.addEventListener('input', function () {
        mostrarValorMascota();
        cargarHoras();
    });
    form.querySelectorAll('[name="forma_pago"]').forEach(function (opcion) { opcion.addEventListener('change', mostrarFormaPago); });
    montoRecibido.addEventListener('input', function () {
        montoRecibido.dataset.automatico = '';
        calcularVuelto();
    });

    form.addEventListener('submit', async function (evento) {
        evento.preventDefault();
        // Enter en un campo avanza de paso en vez de enviar
        if (pasoActual < 3) { siguiente(); return; }
        if (boton.disabled) return;
        for (let n = 1; n <= 3; n++) {
            if (!validarPaso(n)) { irPaso(n); return; }
        }
        document.getElementById('agendar_inicio').value = campoFecha.value + 'T' + campoHora.value;
        error.classList.add('d-none');
        // Se arma antes de enviar, porque al abrir de nuevo el modal el formulario se limpia
        const mascota = datosMascota();
        const reserva = {
            fecha: campoFecha.value, hora: horario(), mascota: mascota.mascota, tutor: mascota.tutor,
            servicio: opcionElegida(procedimiento).dataset.nombre, pago: estadoPago()
        };

        boton.disabled = true;
        try {
            const respuesta = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {Accept: 'application/json'}, credentials: 'same-origin'});
            const datos = await respuesta.json().catch(function () { return {}; });
            if (!respuesta.ok) {
                const fallo = new Error(mensajeError(respuesta.status, datos));
                fallo.campos = Object.keys(datos.errors || {});
                throw fallo;
            }
            if (datos.cliente) recordarMascota(datos.cliente);
            delete ocupadasPorFecha[reserva.fecha];
            if (calendario) calendario.refetchEvents();
            reserva.ficha = datos.ficha_url;
            // Lo que quedó guardado manda: el valor y el pago que calculó el servidor
            if (datos.pago) reserva.pago = textoPago(datos.pago, Number(datos.valor) || precio());
            // El estado se abre recién cerrado este modal, para que no se junten los dos fondos
            modal.one('hidden.bs.modal', function () { mostrarEstado(reserva); });
            modal.modal('hide');
        } catch (fallo) {
            // Sin conexión fetch falla con un TypeError en inglés
            error.textContent = fallo instanceof TypeError ? 'No se pudo conectar. Revisa tu conexión e inténtalo de nuevo.' : fallo.message;
            error.classList.remove('d-none');
            const campos = fallo.campos || [];
            if (campos.length) {
                const paso = Math.min.apply(null, campos.map(function (campo) { return PASO_DE_CAMPO[campo] || 1; }));
                if (paso === 2) {
                    // Otra persona pudo tomar la hora: se vuelven a leer las libres
                    delete ocupadasPorFecha[campoFecha.value];
                    cargarHoras();
                }
                irPaso(paso);
            }
            error.scrollIntoView({block: 'nearest'});
        } finally {
            boton.disabled = false;
        }
    });
}());
