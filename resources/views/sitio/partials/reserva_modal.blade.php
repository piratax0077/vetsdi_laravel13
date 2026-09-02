<div class="sw-modal" id="sw-modal" hidden>
    <div class="sw-modal-card">
        <button type="button" class="sw-modal-close" id="sw-cerrar">&times;</button>
        <p class="sw-agenda-kicker">Nueva hora</p>
        <h3>Confirmar reserva</h3>
        <p id="sw-resumen" class="sw-muted"></p>
        <form id="sw-form">
            <div class="sw-form-grid">
                <label>RUT<input name="responsable_rut" required placeholder="12.345.678-9"></label>
                <label>Nombre<input name="responsable_nombres" required></label>
                <label>Apellido<input name="responsable_apellido_uno" required></label>
                <label>Email<input type="email" name="responsable_email" required></label>
                <label>Teléfono<input name="responsable_telefono" required></label>
                <label>Mascota<input name="mascota_nombre" required></label>
                <label>Especie
                    <select name="mascota_especie_id" required>
                        <option value="">Seleccione</option>
                        @foreach($especies as $especie)
                            <option value="{{ $especie->id }}">{{ $especie->nombre }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="sw-span-2">Comentario<input name="comentarios" maxlength="200"></label>
            </div>
            <button type="submit" class="sw-btn sw-btn-solid sw-btn-block">Confirmar hora</button>
            <p id="sw-form-msg" class="sw-muted"></p>
        </form>
    </div>
</div>

<script>
(() => {
    const slug = @json($sitio->slug);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const horasUrl = new URL('sitio/api/horas', window.location.href).href;
    const reservarUrl = new URL('sitio/api/reservar', window.location.href).href;
    const equipoPorLugar = @json($equipoPorLugar);
    const horarios = @json($horariosJs);
    const lugar = document.getElementById('sw-lugar');
    const profesional = document.getElementById('sw-profesional');
    const fecha = document.getElementById('sw-fecha');
    const slots = document.getElementById('sw-slots');
    const msg = document.getElementById('sw-slots-msg');
    const modal = document.getElementById('sw-modal');
    const resumen = document.getElementById('sw-resumen');
    const form = document.getElementById('sw-form');
    const formMsg = document.getElementById('sw-form-msg');
    let seleccion = null;

    function isoDia(date) {
        const js = date.getDay();
        return js === 0 ? 7 : js;
    }

    function ymdLocal(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function diasDe(horario) {
        return String(horario.dia || '').split(',').map((d) => parseInt(d.trim(), 10)).filter((n) => !Number.isNaN(n));
    }

    function coincideDia(horario, date) {
        const n = isoDia(date);
        const dias = diasDe(horario);
        return dias.includes(n) || (n === 7 && dias.includes(0));
    }

    function proximaFecha() {
        const idLugar = parseInt(lugar.value, 10);
        const idPro = parseInt(profesional.value, 10);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        for (let i = 0; i < 21; i++) {
            const candidato = new Date(hoy);
            candidato.setDate(hoy.getDate() + i);
            const hay = horarios.some((h) =>
                Number(h.id_lugar_atencion) === idLugar &&
                Number(h.id_profesional) === idPro &&
                coincideDia(h, candidato)
            );
            if (hay) {
                return ymdLocal(candidato);
            }
        }
        return ymdLocal(hoy);
    }

    async function leerJson(res) {
        const text = (await res.text()).replace(/^\uFEFF+/, '').trim();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('La agenda no devolvió datos válidos.');
        }
    }

    function nombreProfesional() {
        if (profesional.tagName === 'SELECT' && profesional.selectedIndex >= 0) {
            return profesional.options[profesional.selectedIndex].text;
        }
        return @json(optional($equipo->first())->nombreCompleto() ?? '');
    }

    function pintarProfesionales() {
        if (profesional.tagName !== 'SELECT') {
            return;
        }
        const lista = equipoPorLugar[lugar.value] || [];
        const actual = profesional.value;
        profesional.innerHTML = '';
        lista.forEach((pro) => {
            const option = document.createElement('option');
            option.value = pro.id;
            option.textContent = pro.nombre;
            profesional.appendChild(option);
        });
        if ([...profesional.options].some((o) => o.value === actual)) {
            profesional.value = actual;
        }
    }

    async function cargarHoras() {
        slots.innerHTML = '';
        seleccion = null;
        if (!lugar.value || !profesional.value || !fecha.value) {
            msg.textContent = 'Selecciona lugar y fecha.';
            return;
        }
        msg.textContent = 'Buscando horas...';
        const params = new URLSearchParams({
            slug,
            id_profesional: profesional.value,
            id_lugar_atencion: lugar.value,
            fecha: fecha.value
        });
        try {
            const res = await fetch(`${horasUrl}?${params}`, { headers: { 'Accept': 'application/json' } });
            const data = await leerJson(res);
            const horas = Array.isArray(data.horarios) ? data.horarios : [];
            msg.textContent = horas.length ? (data.text_fecha || '') : (data.msj || 'Sin horas para esa fecha.');
            horas.forEach((item) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'sw-slot';
                btn.textContent = item.hora;
                btn.addEventListener('click', () => {
                    seleccion = item;
                    const lugarNombre = lugar.options[lugar.selectedIndex].text;
                    resumen.textContent = `${nombreProfesional()} · ${lugarNombre} · ${item.fecha} ${item.hora}`;
                    formMsg.textContent = '';
                    modal.hidden = false;
                });
                slots.appendChild(btn);
            });
        } catch (e) {
            msg.textContent = e.message || 'No se pudo leer la agenda de este lugar.';
        }
    }

    function alCambiarLugar() {
        pintarProfesionales();
        fecha.value = proximaFecha();
        cargarHoras();
    }

    lugar.addEventListener('change', alCambiarLugar);
    if (profesional.tagName === 'SELECT') {
        profesional.addEventListener('change', () => {
            fecha.value = proximaFecha();
            cargarHoras();
        });
    }
    fecha.addEventListener('change', cargarHoras);
    document.getElementById('sw-cerrar').addEventListener('click', () => modal.hidden = true);
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.hidden = true; });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!seleccion) return;
        formMsg.textContent = 'Reservando...';
        const body = new FormData(form);
        body.append('slug', slug);
        body.append('id_profesional', profesional.value);
        body.append('id_lugar_atencion', lugar.value);
        body.append('fecha', seleccion.fecha);
        body.append('hora', seleccion.hora);
        try {
            const res = await fetch(reservarUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body
            });
            const data = await leerJson(res);
            formMsg.textContent = data.msj || 'No se pudo reservar.';
            if (data.estado === 1) {
                modal.hidden = true;
                form.reset();
                cargarHoras();
                alert('Reserva confirmada para ' + data.registro.mascota + ' a las ' + data.registro.hora);
            }
        } catch (err) {
            formMsg.textContent = err.message || 'No se pudo reservar.';
        }
    });

    pintarProfesionales();
    fecha.value = proximaFecha();
    cargarHoras();
})();
</script>
