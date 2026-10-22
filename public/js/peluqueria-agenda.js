(function () {
    const contenedor = document.getElementById('peluqueria-calendario');
    if (!contenedor || !window.FullCalendar) return;
    const modal = window.jQuery('#peluqueria-reserva-modal');
    const form = document.getElementById('peluqueria-reserva-form');
    const inicio = document.getElementById('reserva-inicio');
    function abrir(fecha) {
        if (inicio) inicio.value = fecha.length > 10 ? fecha.slice(0,16) : fecha + 'T09:00';
        modal.modal('show');
    }
    const calendario = new FullCalendar.Calendar(contenedor, {
        locale: 'es', firstDay: 1, initialView: 'timeGridDay', initialDate: contenedor.dataset.fecha,
        headerToolbar: {left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay'},
        height: 'auto', allDaySlot: false, nowIndicator: true, slotMinTime: '08:00:00', slotMaxTime: '22:00:00',
        slotDuration: '00:15:00', selectable: true, selectOverlap: false, events: contenedor.dataset.eventos,
        select: function (seleccion) {
            abrir(seleccion.startStr);
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
    modal.on('show.bs.modal', function () {
        if (inicio && !inicio.value) inicio.value = contenedor.dataset.fecha + 'T09:00';
    });
    if (!form) return;
    const nuevo = document.getElementById('reserva-nuevo');
    function tipoCliente() {
        const agregar = nuevo.checked;
        document.getElementById('reserva-cliente-existente').hidden = agregar;
        document.getElementById('reserva-cliente-nuevo').hidden = !agregar;
        document.getElementById('reserva-cliente').disabled = agregar;
        document.querySelectorAll('#reserva-cliente-nuevo input').forEach(function (input) {
            input.disabled = !agregar; input.required = agregar && input.dataset.obligatorio === '1';
        });
    }
    nuevo.addEventListener('change', tipoCliente); tipoCliente();
    const procedimiento = document.getElementById('procedimiento-reserva');
    procedimiento.addEventListener('change', function () {
        const opcion = this.selectedOptions[0];
        document.getElementById('duracion-reserva').value = opcion.dataset.duracion ? opcion.dataset.duracion + ' minutos' : '';
        document.getElementById('precio-reserva').value = opcion.dataset.precio ? '$' + Number(opcion.dataset.precio).toLocaleString('es-CL') : '';
    });
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const boton = document.getElementById('reserva-guardar'), error = document.getElementById('peluqueria-reserva-error');
        if (boton.disabled) return;
        boton.disabled = true; error.hidden = true;
        try {
            const respuesta = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {Accept: 'application/json'}});
            const datos = await respuesta.json();
            if (!respuesta.ok) throw new Error(Object.values(datos.errors || {}).flat().join('\n') || datos.message || 'No se pudo guardar la reserva.');
            modal.modal('hide'); calendario.refetchEvents();
            const aviso = document.createElement('div'); aviso.className = 'alert alert-success'; aviso.setAttribute('role','status'); aviso.textContent = datos.mensaje;
            contenedor.before(aviso);
            form.reset();
            if (datos.cliente) {
                const clientes = document.getElementById('reserva-cliente');
                if (!Array.from(clientes.options).some(opcion => opcion.value === String(datos.cliente.id))) {
                    clientes.add(new Option(datos.cliente.mascota + ' · ' + datos.cliente.tutor, datos.cliente.id));
                }
                clientes.value = String(datos.cliente.id); nuevo.checked = false;
            }
            tipoCliente();
        } catch (fallo) {error.textContent = fallo.message; error.hidden = false;}
        finally {boton.disabled = false;}
    });
}());
