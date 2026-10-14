(function () {
    const config = window.odontologiaVeterinariaConfig;
    if (!config) return;
    const iniciar = function () {
        const lista = document.getElementById('vet-presupuestos-lista');
        const error = document.getElementById('vet-dental-error');
        const pagosForm = document.getElementById('vet-pago-form');
        const moneda = valor => '$' + Number(valor).toLocaleString('es-CL');
        const pendientes = window.odontologiaVetPendientes ||= new Set();
        let presupuestoPago = null, referenciaPago = null;
        const uuid = () => crypto.randomUUID ? crypto.randomUUID() : ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16));
        window.odontologiaVetUuid = uuid;
        function mensaje(texto) { error.textContent = texto; error.hidden = !texto; }
        function nodo(tipo, texto, clase) {
            const el = document.createElement(tipo);
            if (texto !== undefined) el.textContent = texto;
            if (clase) el.className = clase;
            return el;
        }
        function seguir(promesa) {
            pendientes.add(promesa);
            promesa.then(() => pendientes.delete(promesa), () => pendientes.delete(promesa));
            return promesa;
        }
        async function peticion(url, datos) {
            const respuesta = await fetch(url, datos ? {method: 'POST', headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':config.csrf}, body: JSON.stringify(datos)} : {headers:{Accept:'application/json'}});
            const resultado = await respuesta.json();
            if (!respuesta.ok || Number(resultado.estado) !== 1) throw new Error(Object.values(resultado.errors || {}).flat().join('\n') || resultado.message || resultado.msj || 'No se pudo completar la operación.');
            return resultado;
        }
        async function cargar() {
            try {
                const datos = await peticion(config.estado + '?' + new URLSearchParams({mascota_id:config.mascota, ficha_atencion_id:config.ficha}));
                lista.replaceChildren();
                if (!datos.presupuestos.length) lista.append(nodo('p', 'Sin presupuestos. Seleccione una pieza y use Guardar y presupuestar.', 'text-muted'));
                datos.presupuestos.forEach(p => {
                    const bloque = nodo('section', undefined, 'border rounded p-3 mb-3');
                    bloque.append(nodo('h5', 'Presupuesto N.º ' + p.id + (Number(p.ficha_atencion_id) === Number(config.ficha) ? ' · Ficha actual' : ' · Ficha ' + p.ficha_atencion_id)));
                    bloque.append(nodo('p', 'Total: '+moneda(p.total)+' · Pagado: '+moneda(p.abonado)+' · Saldo: '+moneda(p.saldo)+' · '+p.estado_pago+' · Trabajos pendientes: '+p.pendientes));
                    const aprobacion = nodo('button', p.aprobado ? 'Revocar aprobación' : 'Aprobar presupuesto', 'btn btn-sm btn-outline-info mr-2 mb-2');
                    aprobacion.type = 'button';
                    aprobacion.addEventListener('click', async () => {
                        aprobacion.disabled = true;
                        try { await seguir(peticion(config.presupuestoBase+'/'+p.id+'/aprobar', {aprobado:!p.aprobado})); await cargar(); }
                        catch (e) { mensaje(e.message); aprobacion.disabled = false; }
                    });
                    const pdf = nodo('a', 'Descargar presupuesto PDF', 'btn btn-sm btn-outline-secondary mr-2 mb-2');
                    pdf.href = config.presupuestoBase+'/'+p.id+'/pdf';
                    bloque.append(aprobacion, pdf);
                    if (p.saldo > 0) {
                        const pago = nodo('button', 'Registrar pago', 'btn btn-sm btn-info mb-2');
                        pago.type = 'button'; pago.addEventListener('click', () => window.registrarAbonoDentalVet(p.id, p.saldo)); bloque.append(pago);
                    }
                    const tabla = nodo('table', undefined, 'table table-sm mt-3');
                    const encabezado = nodo('thead'), fila = nodo('tr');
                    ['Pieza','Tratamiento','Valor','Estado del trabajo'].forEach(t => fila.append(nodo('th',t)));
                    encabezado.append(fila); tabla.append(encabezado);
                    const cuerpo = nodo('tbody');
                    p.trabajos.forEach(item => {
                        const tr = nodo('tr');
                        [item.pieza, item.tratamiento, moneda(item.valor)].forEach(t => tr.append(nodo('td',t)));
                        const celda = nodo('td'), select = nodo('select', undefined, 'form-control form-control-sm');
                        Object.entries({pendiente:'Pendiente', en_proceso:'En proceso', terminado:'Terminado', cancelado:'Cancelado'}).forEach(([valor,texto]) => {
                            const opcion = nodo('option',texto); opcion.value=valor; select.append(opcion);
                        });
                        select.value = item.estado_trabajo;
                        select.addEventListener('change', async () => {
                            select.disabled=true;
                            try { await seguir(peticion(config.presupuestoBase+'/'+p.id+'/trabajo', {item:item.id,estado:select.value})); await cargar(); }
                            catch(e) { mensaje(e.message); select.value=item.estado_trabajo; select.disabled=false; }
                        });
                        celda.append(select); tr.append(celda); cuerpo.append(tr);
                    });
                    tabla.append(cuerpo); const contenedor=nodo('div',undefined,'table-responsive');contenedor.append(tabla);bloque.append(contenedor);
                    bloque.append(nodo('h6','Historial de pagos'));
                    if (!p.pagos.length) bloque.append(nodo('p','Sin pagos registrados.','text-muted'));
                    p.pagos.forEach(pago => bloque.append(nodo('p',pago.fecha_pago+' · '+pago.metodo_pago+' · '+moneda(pago.total)+(pago.observaciones ? ' · '+pago.observaciones : ''))));
                    lista.append(bloque);
                });
            } catch(e) { mensaje(e.message); lista.textContent='No se pudo cargar el presupuesto. Vuelva a abrir esta pestaña para reintentar.'; }
        }
        window.registrarAbonoDentalVet = function (id, saldo) {
            presupuestoPago=id; referenciaPago=uuid(); pagosForm.reset();
            document.getElementById('vet-pago-monto').max=saldo;
            document.getElementById('vet-pago-saldo').textContent='Saldo disponible: '+moneda(saldo);
            document.getElementById('vet-pago-error').hidden=true;
            $('#vet-pago-modal').modal('show');
        };
        pagosForm.addEventListener('submit', async event => {
            event.preventDefault();
            const boton=pagosForm.querySelector('button[type=submit]'); if(boton.disabled) return;
            boton.disabled=true;const datos=Object.fromEntries(new FormData(pagosForm));
            datos.referencia_operacion=referenciaPago;
            try { await seguir(peticion(config.presupuestoBase+'/'+presupuestoPago+'/abono', datos)); $('#vet-pago-modal').modal('hide'); await cargar(); }
            catch(e) { const aviso=document.getElementById('vet-pago-error');aviso.textContent=e.message;aviso.hidden=false; }
            finally { boton.disabled=false; }
        });
        window.odontologiaVetPrepararGuardado = async function () {
            mensaje('');
            if (window.piezaDentalVetActual && window.odontoVetPiezaModificada && window.odontoVetPiezaModificada()) {
                if (await window.guardarPiezaVet(false) === false) throw new Error('No se pudo guardar la pieza dental. Revise sus datos antes de finalizar.');
            }
            if (window.guardarPeriodontoVeterinario && document.getElementById('pv-form')?.dataset.modificado === '1') await window.guardarPeriodontoVeterinario();
            await Promise.all(Array.from(pendientes));
            // Modals are outside the clinical form; their table data is explicitly included.
            const campos = {
                medicamentos: ['#tabla_medicamento_cirugia', ['id_producto','uso_cronico','medicamento','presentacion','posologia','via_administracion','periodo','compra']],
                examenes: ['#tabla_examen_cirugia', ['nombre_examen','tipo','prioridad','con_contraste']],
            };
            for (const [nombre,[selector,claves]] of Object.entries(campos)) {
                const tabla=document.querySelector(selector);
                if (!tabla) continue;
                const filas=Array.from(tabla.querySelectorAll('tbody tr')).filter(tr => tr.querySelectorAll('td').length >= claves.length);
                if (filas.length) document.getElementById(nombre).value=JSON.stringify(filas.map(tr=>Object.fromEntries(claves.map((clave,i)=>[clave,tr.cells[i].textContent.trim()]))));
            }
        };
        document.addEventListener('odontologia-veterinaria:actualizado', cargar);
        document.querySelector('[href="#vet-presupuestos"]').addEventListener('click', cargar);
        cargar();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar); else iniciar();
})();
