(function () {
    const contenedor = document.getElementById('peluqueria-calendario');
    if (!contenedor || !window.FullCalendar) return;
    const modal = window.jQuery('#peluqueria-reserva-modal');
    const form = document.getElementById('peluqueria-reserva-form');
    const inicio = document.getElementById('reserva-inicio');
    function horaFutura(fecha){
        if(!/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(fecha||''))return false;
        const partes=new Intl.DateTimeFormat('en-CA',{timeZone:'America/Santiago',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',hourCycle:'h23'}).formatToParts(new Date());
        const valores=Object.fromEntries(partes.map(p=>[p.type,p.value]));
        const ahora=valores.year+'-'+valores.month+'-'+valores.day+'T'+valores.hour+':'+valores.minute;
        return fecha.slice(0,16)>ahora;
    }
    function avisarHoraPasada(){
        const aviso=document.getElementById('peluqueria-seleccionar-hora');
        aviso.textContent='Selecciona una fecha y hora futura para reservar.';aviso.hidden=false;
    }
    function validarInicio(){
        if(inicio&&horaFutura(inicio.value))return true;
        const error=document.getElementById('peluqueria-reserva-error');
        error.textContent='Selecciona una fecha y hora futura para continuar.';error.hidden=false;
        if(inicio){inicio.type='datetime-local';inicio.focus?.();}
        return false;
    }
    function abrir(fecha) {
        const valor=fecha.length>10?fecha.slice(0,16):fecha+'T09:00';
        if(!horaFutura(valor)){avisarHoraPasada();return;}
        document.getElementById('peluqueria-seleccionar-hora').hidden=true;
        if (inicio) inicio.value=valor;
        modal.modal('show');
    }
    const horario=JSON.parse(contenedor.dataset.horario||'null');
    const puedeReservar=contenedor.dataset.reservar!=='0';
    const tramos=horario?.tramos;
    const apertura=tramos?.length?tramos.map(t=>t.inicio).sort()[0]:horario?.apertura;
    const cierre=tramos?.length?tramos.map(t=>t.fin).sort().at(-1):horario?.cierre;
    const horasAtencion=tramos?tramos.map(t=>({daysOfWeek:[Number(t.dia)===7?0:Number(t.dia)],startTime:t.inicio,endTime:t.fin})):(horario?{daysOfWeek:horario.dias.map(d=>d===7?0:d),startTime:horario.apertura,endTime:horario.cierre}:undefined);

    const calendario = new FullCalendar.Calendar(contenedor, {
        locale: 'es', firstDay: 1, initialView: 'timeGridDay', initialDate: contenedor.dataset.fecha,
        headerToolbar: {left: 'prev today next', center: 'title', right: 'timeGridWeek,timeGridDay'},
        height: 'auto', allDaySlot: false, nowIndicator: true, slotMinTime: apertura?apertura+':00':'08:00:00', slotMaxTime: cierre?cierre+':00':'22:00:00',
        businessHours:horasAtencion,
        titleFormat:{day:'2-digit',month:'2-digit',year:'numeric'},
        datesSet:function(info){
            const titulo=contenedor.querySelector('.fc-toolbar-title');
            if(!titulo)return;
            if(info.view.type==='timeGridDay')titulo.setAttribute('data-fecha-larga',info.view.currentStart.toLocaleDateString('es-CL',{day:'numeric',month:'long',year:'numeric'}));
            else titulo.removeAttribute('data-fecha-larga');
        },
        slotLabelFormat:{hour:'2-digit',minute:'2-digit',hour12:false},
        eventTimeFormat:{hour:'2-digit',minute:'2-digit',hour12:false},
        slotDuration: '00:15:00', snapDuration: '00:01:00', selectable: puedeReservar, selectOverlap: true, events: contenedor.dataset.eventos,
        selectAllow: function(seleccion){if(!horaFutura(seleccion.startStr)){avisarHoraPasada();return false;}return puedeReservar;},
        select: function (seleccion) {
            if(puedeReservar)abrir(seleccion.startStr);
        },
        eventClick: function(info){info.jsEvent.preventDefault();abrirEstado(info.event.extendedProps.reserva);},
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
    modal.on('show.bs.modal', function (event) {
        if (!inicio || !form) return;
        limpiarTutor();rut.value='';nuevo.checked=false;tipoCliente();
        document.getElementById('peluqueria-reserva-error').hidden=true;
        inicio.type=inicio.value?'hidden':'datetime-local';
        inicio.required=true;
        document.getElementById('reserva-fecha-seleccionada').textContent = inicio.value ? new Date(inicio.value).toLocaleString('es-CL', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        }) : 'Selecciona la fecha y hora de la reserva:';
    });
    document.getElementById('peluqueria-reservar-hora')?.addEventListener('click', function () {
        inicio.value='';
        modal.modal('show');
    });
    const estadoModal=window.jQuery('#peluqueria-estado-modal');
    const estadoForm=document.getElementById('peluqueria-estado-form');
    const etiquetas={reservada:'Reservada',confirmada:'Confirmada',pagada:'Pagada',en_espera:'En espera',en_atencion:'En atención',finalizada:'Finalizada',cancelada:'Cancelada'};
    function rutPresentacion(valor){
        const limpio=String(valor||'').replace(/[^0-9k]/gi,'').toUpperCase();
        return limpio.length>1?limpio.slice(0,-1).replace(/\B(?=(\d{3})+(?!\d))/g,'.')+'-'+limpio.slice(-1):limpio;
    }
    function avisarSinPermiso(){
        window.swal({title:'Hora tomada con éxito',text:'No cuentas con los permisos para seguir. La atención debe continuarla el profesional.',icon:'success',button:'Aceptar'});
    }
    function abrirEstado(reserva){
        if(!reserva||!estadoForm)return;
        if(reserva.bloqueada){avisarSinPermiso();return;}
        estadoForm.reset();estadoForm.action=reserva.url;
        const pantallas={
            reservada:{titulo:'Confirmar hora',mensaje:'Confirma la reserva e indica la vía de confirmación.',campo:'confirmacion',accion:'confirmada'},
            confirmada:{titulo:'Recepción de pago de atención',mensaje:'Recepciona el pago de los procedimientos reservados.',campo:'pago',accion:'pagada'},
            pagada:{titulo:'Atender mascota',mensaje:'Hora pagada. Pulsa Atender para abrir la ficha de esta mascota.'},
            en_espera:{titulo:'En espera · Lista para atención',mensaje:'La mascota está en espera. Pulsa Atender para abrir su ficha.'},
            en_atencion:{titulo:'Atención en curso',mensaje:'Abre la ficha para continuar la atención.'},
            finalizada:{titulo:'Atención finalizada',mensaje:'La ficha de esta atención está disponible para consulta.'},
            cancelada:{titulo:'Reserva anulada',mensaje:'Esta reserva está anulada.'}
        };
        const pantalla=pantallas[reserva.estado]||pantallas.reservada;
        document.getElementById('peluqueria-estado-titulo').textContent=pantalla.titulo;
        document.getElementById('peluqueria-modal-instruccion').textContent=pantalla.mensaje;
        document.getElementById('peluqueria-resumen-costos').hidden=['pagada','en_espera','en_atencion','finalizada'].includes(reserva.estado);
        document.getElementById('peluqueria-estado-error').hidden=true;
        document.querySelectorAll('[data-reserva-dato]').forEach(el=>el.textContent=el.dataset.reservaDato==='estado'?etiquetas[reserva.estado]:(el.dataset.reservaDato==='rut'?rutPresentacion(reserva.rut):(reserva[el.dataset.reservaDato]||'—')));
        document.querySelectorAll('[data-reserva-accion]').forEach(el=>{el.hidden=!reserva.acciones.includes(el.dataset.reservaAccion);el.disabled=false;});
        for(const [campo,accion] of [['confirmacion','confirmada'],['pago','pagada']]){
            const grupo=document.getElementById('peluqueria-'+campo);
            grupo.hidden=!(pantalla.campo===campo&&reserva.acciones.includes(accion));grupo.disabled=grupo.hidden;
        }
        document.getElementById('peluqueria-monto').value=reserva.valor;
        document.getElementById('peluqueria-total-reserva').textContent='$'+Number(reserva.valor).toLocaleString('es-CL');
        document.getElementById('peluqueria-detalle-costos').textContent=(reserva.procedimientos||[]).map(p=>p.nombre+': $'+Number(p.precio).toLocaleString('es-CL')).join(' · ');
        const registrado=document.getElementById('peluqueria-pago-registrado');
        registrado.hidden=reserva.monto_pagado===null;
        registrado.textContent='Pago recibido: $'+Number(reserva.monto_pagado).toLocaleString('es-CL')+' · '+(reserva.medio_pago||'');
        const ficha=document.getElementById('peluqueria-ver-ficha');ficha.hidden=!reserva.ficha;ficha.href=reserva.ficha||'#';
        estadoModal.modal('show');
    }
    document.querySelectorAll('[data-reserva-detalle]').forEach(el=>el.addEventListener('click',()=>abrirEstado(JSON.parse(el.dataset.reservaDetalle))));
    if(estadoForm)estadoForm.addEventListener('submit',async function(event){
        event.preventDefault();const boton=event.submitter;if(!boton||boton.disabled)return;
        const error=document.getElementById('peluqueria-estado-error');error.hidden=true;
        const grupo=boton.value==='confirmada'?document.getElementById('peluqueria-confirmacion'):(boton.value==='pagada'?document.getElementById('peluqueria-pago'):null);
        if(grupo&&grupo.hidden){grupo.hidden=false;grupo.disabled=false;grupo.querySelector('select')?.focus();return;}
        const datos=new FormData(estadoForm);datos.set('estado',boton.value);
        document.querySelectorAll('[data-reserva-accion]').forEach(el=>el.disabled=true);
        try{
            const respuesta=await fetch(estadoForm.action,{method:'POST',body:datos,headers:{Accept:'application/json'}});
            const resultado=await respuesta.json();
            if(!respuesta.ok)throw new Error(Object.values(resultado.errors||{}).flat().join(' ')||resultado.message||'No se pudo actualizar la reserva.');
            if(resultado.ficha){window.location.assign(resultado.ficha);return;}
            if(resultado.reserva){
                const evento=calendario.getEventById?.(String(resultado.reserva.id));
                if(evento){
                    evento.setProp('backgroundColor',resultado.reserva.color);
                    evento.setProp('borderColor',resultado.reserva.color);
                    evento.setProp('title',etiquetas[resultado.reserva.estado]+' · '+resultado.reserva.mascota+' · '+resultado.reserva.servicio);
                    evento.setExtendedProp('reserva',resultado.reserva);
                }
            }
            calendario.refetchEvents();
            if(resultado.reserva){
                document.querySelectorAll('[data-reserva-detalle]').forEach(el=>{
                    if(String(JSON.parse(el.dataset.reservaDetalle).id)!==String(resultado.reserva.id))return;
                    el.dataset.reservaDetalle=JSON.stringify(resultado.reserva);
                    const estado=el.closest('tr')?.querySelector('[data-reserva-estado]');
                    if(estado)estado.textContent=etiquetas[resultado.reserva.estado];
                });
                if(resultado.reserva.estado==='pagada'){estadoModal.modal('hide');if(resultado.reserva.bloqueada)avisarSinPermiso();}
                else abrirEstado(resultado.reserva);
            }
        }catch(fallo){error.textContent=fallo.message;error.hidden=false;document.querySelectorAll('[data-reserva-accion]').forEach(el=>el.disabled=false);}
    });
    if (!form) return;
    let tutorBuscado=false;
    let mascotaFijada=null;
    const nuevo = document.getElementById('reserva-nuevo');
    function tipoCliente() {
        const selector=document.getElementById('reserva-cliente');
        if(mascotaFijada){
            nuevo.disabled=true;
            selector.disabled=true;
            document.getElementById('reserva-cliente-existente').hidden=true;
            document.getElementById('reserva-cliente-nuevo').hidden=true;
            document.getElementById('reserva-opcion-mascota-nueva').hidden=true;
            document.getElementById('reserva-fijar-nueva-mascota').hidden=true;
            document.getElementById('reserva-cambiar-tutor').hidden=true;
            return;
        }
        const opcionNueva=document.getElementById('reserva-opcion-mascota-nueva');
        const seleccionada=!!selector.value;
        if(seleccionada)nuevo.checked=false;
        nuevo.disabled=seleccionada||opcionNueva.dataset.permitido==='0';
        opcionNueva.hidden=nuevo.disabled;
        const agregar = nuevo.checked&&!nuevo.disabled;
        document.getElementById('reserva-guardar').hidden=true;
        document.getElementById('reserva-fijar-nueva-mascota').hidden=!agregar;
        document.getElementById('reserva-cliente-existente').hidden = agregar;
        document.getElementById('reserva-cliente-nuevo').hidden = !agregar;
        document.getElementById('reserva-cliente').disabled = agregar;
        document.querySelectorAll('#reserva-cliente-nuevo input').forEach(function (input) {
            input.disabled = !agregar; input.required = agregar && input.dataset.obligatorio === '1';
        });
    }
    nuevo.addEventListener('change', tipoCliente);
    document.getElementById('reserva-cliente').addEventListener('change',function(){
        if(!tutorBuscado)return;
        if(mascotaFijada){this.value=mascotaFijada.id;return;}
        const opcion=Array.from(this.options).find(o=>o.value===this.value);
        if(opcion?.value)fijarMascota({id:opcion.value,nombre:opcion.textContent||opcion.text,nueva:false});
        else tipoCliente();
    });
    function fijarMascota(mascota){
        mascotaFijada=mascota;
        document.getElementById('reserva-mascota-fijada').textContent='Mascota: '+mascota.nombre+'. Todos los procedimientos de esta hora son para ella. Para otra mascota, reserva otra hora.';
        document.getElementById('reserva-mascota-fijada').hidden=false;
        document.getElementById('reserva-paso-procedimientos').hidden=false;
        document.getElementById('reserva-paso-procedimientos').disabled=false;
        document.getElementById('reserva-guardar').hidden=false;
        tipoCliente();
    }
    document.getElementById('reserva-fijar-nueva-mascota').addEventListener('click',function(){
        if(mascotaFijada)return;
        const campos={};
        for(const input of document.querySelectorAll('#reserva-cliente-nuevo input')){
            if(!input.reportValidity())return;
            campos[input.name]=input.value;
        }
        fijarMascota({id:'',nombre:campos.mascota,nueva:true,campos});
    });
    tipoCliente();
    const rut=document.getElementById('reserva-rut'), mascotas=document.getElementById('reserva-cliente');
    let busqueda=0;
    function limpiarTutor(){
        busqueda++;tutorBuscado=false;mascotaFijada=null;
        document.getElementById('reserva-mascota-fijada').hidden=true;
        document.getElementById('reserva-paso-procedimientos').hidden=true;
        document.getElementById('reserva-paso-procedimientos').disabled=true;
        document.getElementById('reserva-buscador-tutor').hidden=false;
        document.getElementById('reserva-cambiar-tutor').hidden=true;
        document.getElementById('reserva-datos').hidden=true;
        document.getElementById('reserva-datos').disabled=true;
        document.getElementById('reserva-guardar').hidden=true;
        mascotas.replaceChildren(new Option('Primero busca el RUT del tutor',''));
        document.getElementById('reserva-mascota-sistema').value='';
        document.getElementById('reserva-tutor-encontrado').textContent='';
    }
    function formatearRutTutor(){
        const cursor=rut.selectionStart;
        const caracteresAntes=cursor===null?null:rut.value.slice(0,cursor).replace(/[^0-9k]/gi,'').length;
        const limpio=rut.value.replace(/[^0-9k]/gi,'').toUpperCase().slice(0,9);
        rut.value=limpio.length>1?limpio.slice(0,-1).replace(/\B(?=(\d{3})+(?!\d))/g,'.')+'-'+limpio.slice(-1):limpio;
        if(caracteresAntes!==null&&rut.setSelectionRange){
            let posicion=0,contados=0;
            while(posicion<rut.value.length&&contados<caracteresAntes){if(/[0-9K]/.test(rut.value[posicion]))contados++;posicion++;}
            rut.setSelectionRange(posicion,posicion);
        }
    }
    document.getElementById('reserva-cambiar-tutor').addEventListener('click',function(){
        if(mascotaFijada)return;
        limpiarTutor();nuevo.checked=false;tipoCliente();
        document.getElementById('peluqueria-reserva-error').hidden=true;
        rut.focus?.();rut.select?.();
    });
    rut.addEventListener('input',function(){formatearRutTutor();limpiarTutor();});
    rut.addEventListener('blur',formatearRutTutor);
    rut.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();document.getElementById('reserva-buscar-tutor').click();}});
    document.getElementById('reserva-buscar-tutor').addEventListener('click',async function(){
        if(inicio?.value&&!validarInicio())return;
        formatearRutTutor();
        limpiarTutor();const actual=busqueda;
        const mensaje=document.getElementById('reserva-tutor-encontrado');
        try{
            const respuesta=await fetch(rut.dataset.url+'?rut='+encodeURIComponent(rut.value),{headers:{Accept:'application/json'}});
            const datos=await respuesta.json();if(actual!==busqueda)return;
            if(!respuesta.ok)throw new Error(Object.values(datos.errors||{}).flat().join(' ')||'No se pudo buscar el tutor.');
            mascotas.replaceChildren(new Option('Seleccione una mascota',''));
            for(const mascota of datos.mascotas)mascotas.add(new Option(mascota.nombre,mascota.id));
            mensaje.textContent=datos.tutor?datos.tutor+(datos.mascotas.length?'':' · Sin mascotas registradas.'):'Tutor no encontrado. Puedes registrar al tutor y su mascota.';
            tutorBuscado=true;
            document.getElementById('reserva-buscador-tutor').hidden=true;
            document.getElementById('reserva-cambiar-tutor').hidden=false;
            document.getElementById('reserva-datos').hidden=false;
            document.getElementById('reserva-datos').disabled=false;
            for(const campo of ['tutor','telefono','email']){
                const input=document.getElementById('reserva-'+campo);
                input.value=datos[campo]||'';
                input.readOnly=!!datos.tutor&&!!datos[campo];
            }
            nuevo.checked=!datos.mascotas.length;tipoCliente();mostrarCostos();
        }catch(error){if(actual===busqueda)mensaje.textContent=error.message;}
    });
    const principal=document.getElementById('procedimiento-reserva');
    const adicionales=document.getElementById('reserva-procedimientos-adicionales');
    const elegidos=new Map();
    function mostrarCostos(){
        adicionales.replaceChildren();
        for(const [id,procedimiento] of elegidos){
            const fila=document.createElement('div');fila.className='d-flex align-items-center border-bottom py-2';
            const nombre=document.createElement('span');nombre.className='flex-grow-1 pr-2';nombre.textContent=procedimiento.nombre;
            const precio=document.createElement('strong');precio.className='text-info text-nowrap mr-3';precio.textContent='$'+procedimiento.precio.toLocaleString('es-CL');
            const quitar=document.createElement('button');quitar.type='button';quitar.className='btn btn-link btn-sm text-danger';quitar.textContent='Eliminar';
            quitar.setAttribute('aria-label','Eliminar '+procedimiento.nombre);
            quitar.addEventListener('click',function(){elegidos.delete(id);mostrarCostos();});
            const input=document.createElement('input');input.type='hidden';input.name='procedimiento_ids[]';input.value=id;
            fila.append(nombre,precio,quitar,input);adicionales.append(fila);
        }
        document.getElementById('reserva-total').textContent='$'+Array.from(elegidos.values()).reduce((total,p)=>total+p.precio,0).toLocaleString('es-CL');
        document.getElementById('reserva-detalle-costos').textContent='';
    }
    function seleccionarProcedimiento(){
        if(!mascotaFijada)return;
        const opcion=Array.from(principal.options||[]).find(opcion=>String(opcion.value)===String(principal.value));
        if(!opcion?.value)return;
        if(elegidos.size<20&&!elegidos.has(opcion.value))elegidos.set(opcion.value,{nombre:opcion.textContent.split(' — $')[0],precio:Number(opcion.dataset.precio||0)});
        principal.value='';mostrarCostos();
    }
    principal.addEventListener('change',seleccionarProcedimiento);
    modal.on('show.bs.modal',function(){elegidos.clear();principal.value='';mostrarCostos();});
    function datosReserva(){
        mostrarCostos();
        const datos=new FormData(form);
        datos.set('nuevo_cliente',mascotaFijada.nueva?'1':'0');
        if(mascotaFijada.nueva){for(const [campo,valor] of Object.entries(mascotaFijada.campos))datos.set(campo,valor);}
        else{
            const [origen,id]=mascotaFijada.id.split(':');
            datos.set('cliente_id',origen==='local'?id:'0');
            datos.set('mascota_sistema_id',origen==='sistema'?id:'');
        }
        return datos;
    }
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const boton = document.getElementById('reserva-guardar'), error = document.getElementById('peluqueria-reserva-error');
        if (boton.disabled) return;
        if(!tutorBuscado){error.textContent='Primero busca el RUT del tutor.';error.hidden=false;return;}
        if(!validarInicio())return;
        if(!mascotaFijada){error.textContent='Primero selecciona la mascota de esta hora.';error.hidden=false;return;}
        if(!elegidos.size){error.textContent='Selecciona al menos un procedimiento.';error.hidden=false;return;}
        boton.disabled = true; error.hidden = true;
        try {
            const respuesta = await fetch(form.action, {method: 'POST', body: datosReserva(), headers: {Accept: 'application/json'}});
            const datos = await respuesta.json();
            if (!respuesta.ok) throw new Error(Object.values(datos.errors || {}).flat().join('\n') || datos.message || 'No se pudo guardar la reserva.');
            modal.modal('hide'); calendario.refetchEvents();
            const aviso = document.createElement('div'); aviso.className = 'alert alert-success'; aviso.setAttribute('role','status'); aviso.textContent = datos.mensaje;
            contenedor.before(aviso);
            form.reset();
            limpiarTutor(); nuevo.checked=false;
            tipoCliente();
        } catch (fallo) {error.textContent = fallo.message; error.hidden = false;}
        finally {boton.disabled = false;}
    });
}());
