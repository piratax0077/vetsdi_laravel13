(function () {
    'use strict';
    const contenidoHistorial = document.getElementById('pelu-historial-contenido');
    let consultaHistorial;
    document.querySelectorAll('.pelu-ver-ficha').forEach(function (boton) {
        boton.addEventListener('click', async function () {
            if (consultaHistorial) consultaHistorial.abort();
            consultaHistorial = new AbortController();
            contenidoHistorial.textContent = 'Cargando ficha…';
            jQuery('#pelu-historial-modal').modal('show');
            try {
                const respuesta = await fetch(boton.dataset.url, {signal: consultaHistorial.signal});
                if (!respuesta.ok || respuesta.redirected) throw new Error('Ficha no disponible');
                const documento = new DOMParser().parseFromString(await respuesta.text(), 'text/html');
                const ficha = documento.getElementById('pelu-ficha-form');
                if (!ficha) throw new Error('Ficha no disponible');
                ficha.querySelectorAll('input[type="hidden"], script, .pelu-guardar').forEach(nodo => nodo.remove());
                ficha.querySelectorAll('[id]').forEach(nodo => nodo.removeAttribute('id'));
                ficha.removeAttribute('id');
                ficha.querySelectorAll('fieldset').forEach(nodo => { nodo.disabled = true; });
                const fecha = documento.querySelector('.pelu-cabecera p');
                contenidoHistorial.replaceChildren();
                if (fecha) contenidoHistorial.appendChild(fecha);
                contenidoHistorial.appendChild(ficha);
            } catch (error) {
                if (error.name !== 'AbortError') contenidoHistorial.textContent = 'No se pudo cargar la ficha. Cierra el modal y vuelve a intentarlo.';
            }
        });
    });
    jQuery('#pelu-historial-modal').on('hidden.bs.modal', function () {
        if (consultaHistorial) consultaHistorial.abort();
        contenidoHistorial.replaceChildren();
    });
    const lista = document.getElementById('pelu-lista');
    if (!lista) return;
    const form = document.getElementById('pelu-ficha-form');
    let indice = Math.max(-1, ...Array.from(lista.querySelectorAll('input[name]')).map(input => Number(input.name.match(/\[(\d+)\]/)[1]))) + 1;
    function total() {
        const valor = Array.from(lista.querySelectorAll('input[type=number]')).reduce((suma, input) => suma + (Number(input.value) || 0), 0);
        document.getElementById('pelu-total').textContent = '$' + valor.toLocaleString('es-CL');
    }
    lista.addEventListener('input', total);
    lista.addEventListener('click', function (event) {
        if (!event.target.closest('.pelu-quitar')) return;
        event.target.closest('tr').remove(); total();
    });
    const anadir = document.getElementById('pelu-anadir');
    const selector = document.getElementById('pelu-servicio');
    if (selector) selector.addEventListener('change', function () {
        this.setCustomValidity('');
        document.getElementById('pelu-precio').value = this.selectedOptions[0].dataset.precio || '';
    });
    if (anadir) anadir.addEventListener('click', function () {
        const nombre = document.getElementById('pelu-servicio');
        const precio = document.getElementById('pelu-precio');
        nombre.setCustomValidity(nombre.value ? '' : 'Selecciona un servicio configurado.');
        precio.setCustomValidity(precio.value === '' ? 'Ingresa el precio del servicio.' : '');
        if (!nombre.reportValidity() || !precio.reportValidity()) return;
        const fila = document.createElement('tr');
        const opcion = nombre.selectedOptions[0];
        [['nombre', opcion.dataset.nombre], ['precio', opcion.dataset.precio]].forEach(function ([campo, valor]) {
            const celda = document.createElement('td'), input = document.createElement('input');
            input.name = 'servicios[' + indice + '][' + campo + ']'; input.value = valor; input.required = true;
            input.readOnly = true;
            input.setAttribute('aria-label', campo === 'nombre' ? 'Nombre del servicio' : 'Precio del servicio en pesos');
            if (campo === 'precio') { input.type = 'number'; input.min = '0'; input.max = '999999999'; input.step = '1'; }
            else input.maxLength = 150;
            celda.appendChild(input); fila.appendChild(celda);
        });
        const id = document.createElement('input'); id.type = 'hidden';
        id.name = 'servicios[' + indice + '][procedimiento_id]'; id.value = nombre.value; fila.firstElementChild.appendChild(id);
        const celda = document.createElement('td'), boton = document.createElement('button');
        boton.type = 'button'; boton.className = 'pelu-quitar'; boton.textContent = 'Quitar';
        celda.appendChild(boton); fila.appendChild(celda); lista.appendChild(fila); indice++;
        nombre.value = ''; precio.value = ''; nombre.focus(); total();
        document.getElementById('pelu-lista-error').hidden = true;
    });
    for (const id of ['pelu-servicio', 'pelu-precio']) {
        const input = document.getElementById(id);
        if (input) input.addEventListener('input', function () { this.setCustomValidity(''); });
    }
    form.addEventListener('submit', function (event) {
        if (lista.children.length) return;
        event.preventDefault();
        const error = document.getElementById('pelu-lista-error'); error.textContent = 'Añade al menos un servicio antes de guardar.'; error.hidden = false;
    });
    total();
}());
