const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');

function setup(horario=null,puedeReservar=true){
    const elements=new Map(),modales=[],alertas=[],propiedadesEvento={};let requests=0,resolve,recargas=0,creados=0;
    function element(id){
        if(!elements.has(id))elements.set(id,{value:'',dataset:{fecha:'2026-10-21'},disabled:false,checked:false,hidden:false,options:[],children:[],append(...items){this.children.push(...items);},setAttribute(){},reset(){},querySelectorAll(){return [];},replaceChildren(...options){this.children=options;this.options=options;this.value='';},add(option){this.options.push(option);},listeners:{},addEventListener(name,fn){this.listeners[name]=fn;}});
        return elements.get(id);
    }
    element('reserva-inicio').value='2099-10-26T11:17';
    element('peluqueria-calendario').dataset.horario=JSON.stringify(horario);
    element('peluqueria-calendario').dataset.reservar=puedeReservar?'1':'0';
    let opciones;
    const calendar=class {constructor(el,config){opciones=config;}render(){}refetchEvents(){recargas++;}getEventById(){return {setProp(nombre,valor){propiedadesEvento[nombre]=valor;},setExtendedProp(){}};}};
    const ctx={swal:opciones=>alertas.push(opciones),document:{getElementById:element,createElement:()=>element('creado-'+(++creados)),querySelectorAll:()=>[]},FullCalendar:{Calendar:calendar},FormData:class{set(){}},
        Option:class{constructor(text,value){this.text=text;this.value=value;}},jQuery:(selector)=>({modal(accion){modales.push({selector,accion});},on(){}}),fetch:(url)=>{if(String(url).includes('?rut='))return Promise.resolve({ok:true,json:async()=>({tutor:'Tutor prueba',telefono:'123',email:'',mascotas:[{id:'local:1',nombre:'Luna'}]})});requests++;return new Promise(done=>resolve=done);}};
    ctx.window=ctx;vm.createContext(ctx);
    vm.runInContext(fs.readFileSync(path.join(__dirname,'../../public/js/peluqueria-agenda.js'),'utf8'),ctx);
    return {element,modales,alertas,propiedadesEvento,fijarMascota:()=>{const selector=element('reserva-cliente');selector.value='local:1';selector.listeners.change.call(selector);},get recargas(){return recargas;},submitEstado:()=>element('peluqueria-estado-form').listeners.submit({preventDefault(){},submitter:{value:'pagada',disabled:false}}),pagar:()=>resolve({ok:true,json:async()=>({mensaje:'Pago recibido',reserva:{id:1,estado:'pagada',color:'#7043b5',valor:15000,monto_pagado:15000,medio_pago:'Efectivo',acciones:['en_espera'],procedimientos:[]}})}),buscar:()=>element('reserva-buscar-tutor').listeners.click(),get opciones(){return opciones;},get requests(){return requests;},submit:()=>element('peluqueria-reserva-form').listeners.submit({preventDefault(){}}),
        fail:()=>resolve({ok:false,json:async()=>({errors:{inicio:['Horario ocupado.']}})})};
}

test('repeated reservation submissions send a single request and allow retry after failure',async()=>{
    const s=setup();await s.buscar();s.fijarMascota();const select=s.element('procedimiento-reserva');select.value='1';select.options=[{value:'1',textContent:'Baño — $15.000',dataset:{precio:'15000'}}];select.listeners.change();const pending=s.submit();await s.submit();
    assert.equal(s.requests,1);assert.equal(s.element('reserva-guardar').disabled,true);
    s.fail();await pending;
    assert.equal(s.element('reserva-guardar').disabled,false);
    assert.equal(s.element('peluqueria-reserva-error').textContent,'Horario ocupado.');
    const retry=s.submit();assert.equal(s.requests,2);s.fail();await retry;
});

test('calendar uses opening hours while allowing minute-level flexible reservations',()=>{
    const s=setup({dias:[1,2,7],apertura:'09:00',cierre:'18:00',intervalo:30});
    assert.equal(s.opciones.slotMinTime,'09:00:00');assert.equal(s.opciones.slotMaxTime,'18:00:00');
    assert.equal(s.opciones.slotDuration,'00:15:00');assert.equal(s.opciones.snapDuration,'00:01:00');assert.deepEqual(Array.from(s.opciones.businessHours.daysOfWeek),[1,2,0]);
    assert.equal(s.opciones.selectOverlap,true);
});
test('calendar does not allow selecting a reservation without permission',()=>{
    const s=setup(null,false);assert.equal(s.opciones.selectable,false);
});

test('calendar supports professional-style split schedules by weekday',()=>{
 const s=setup({tramos:[{dia:1,inicio:'09:00',fin:'13:00'},{dia:1,inicio:'15:00',fin:'19:00'},{dia:3,inicio:'10:00',fin:'17:00'}]});
 assert.equal(s.opciones.slotMinTime,'09:00:00');assert.equal(s.opciones.slotMaxTime,'19:00:00');assert.equal(s.opciones.businessHours.length,3);
 assert.equal(s.opciones.businessHours[1].startTime,'15:00');assert.equal(s.opciones.snapDuration,'00:01:00');
 assert.equal(s.opciones.headerToolbar.right,'timeGridWeek,timeGridDay');
});

test('navigation places Today between both arrows',()=>{
 const s=setup();assert.equal(s.opciones.headerToolbar.left,'prev today next');
});


test('reservation keeps the exact start selected in the calendar',()=>{
 const s=setup();
 s.opciones.select({startStr:'2099-10-26T11:17:00-03:00'});
 assert.equal(s.element('reserva-inicio').value,'2099-10-26T11:17');
});

test('new client registration disables the existing pet selection and restores it',()=>{
 const s=setup(),nuevo=s.element('reserva-nuevo');
 nuevo.checked=true;nuevo.listeners.change();
 assert.equal(s.element('reserva-cliente-existente').hidden,true);
 assert.equal(s.element('reserva-cliente').disabled,true);
 assert.equal(s.element('reserva-cliente-nuevo').hidden,false);
 nuevo.checked=false;nuevo.listeners.change();
 assert.equal(s.element('reserva-cliente-existente').hidden,false);
 assert.equal(s.element('reserva-cliente').disabled,false);
 assert.equal(s.element('reserva-cliente-nuevo').hidden,true);
});


test('the first step only enables reservation details after searching the tutor',async()=>{
 const s=setup();await s.submit();assert.equal(s.requests,0);
 assert.match(s.element('peluqueria-reserva-error').textContent,/RUT/);
 await s.buscar();assert.equal(s.element('reserva-datos').hidden,false);assert.equal(s.element('reserva-datos').disabled,false);
 assert.equal(s.element('reserva-cliente').options[1].text,'Luna');
 s.element('reserva-rut').listeners.input();assert.equal(s.element('reserva-datos').hidden,true);
 assert.equal(s.element('reserva-datos').disabled,true);assert.equal(s.element('reserva-guardar').hidden,true);
});


test('tutor RUT is formatted while typing or pasting, including uppercase K',()=>{
 const s=setup(),rut=s.element('reserva-rut');
 for(const [entrada,esperado] of [['6187674k','6.187.674-K'],['123456785','12.345.678-5'],['12.345.678-5','12.345.678-5'],['',''],['1','1']]){
  rut.value=entrada;rut.listeners.input();assert.equal(rut.value,esperado);
 }
});


test('receiving payment closes its modal and leaves the appointment purple on the calendar',async()=>{
 const s=setup();s.element('peluqueria-pago').hidden=false;
 const pending=s.submitEstado();s.pagar();await pending;
 assert.equal(s.recargas,1);
 assert.equal(s.element('peluqueria-estado-error').hidden,true);
 assert.equal(s.propiedadesEvento.backgroundColor,'#7043b5');
 assert.deepEqual(s.modales,[{selector:'#peluqueria-estado-modal',accion:'hide'}]);
});


test('one selector adds priced procedures without duplicates and allows removing an item',async()=>{
 const s=setup();await s.buscar();s.fijarMascota();const principal=s.element('procedimiento-reserva');
 principal.options=[{value:'1',textContent:'Baño — $18.000',dataset:{precio:'18000'}},{value:'2',textContent:'Corte — $5.000',dataset:{precio:'5000'}}];
 principal.value='1';principal.listeners.change();assert.equal(s.element('reserva-total').textContent,'$18.000');assert.equal(principal.value,'');
 principal.value='2';principal.listeners.change();assert.equal(s.element('reserva-total').textContent,'$23.000');
 principal.value='2';principal.listeners.change();assert.equal(s.element('reserva-total').textContent,'$23.000');
 const lista=s.element('reserva-procedimientos-adicionales');assert.equal(lista.children.length,2);
 assert.equal(lista.children[0].children[3].name,'procedimiento_ids[]');
 lista.children[0].children[2].listeners.click();assert.equal(s.element('reserva-total').textContent,'$5.000');assert.equal(lista.children.length,1);
});

test('finding the tutor hides the search and changing tutor restores the first step',async()=>{
 const s=setup();await s.buscar();
 assert.equal(s.element('reserva-buscador-tutor').hidden,true);
 assert.equal(s.element('reserva-cambiar-tutor').hidden,false);
 assert.equal(s.element('reserva-datos').hidden,false);
 s.element('reserva-cambiar-tutor').listeners.click();
 assert.equal(s.element('reserva-buscador-tutor').hidden,false);
 assert.equal(s.element('reserva-cambiar-tutor').hidden,true);
 assert.equal(s.element('reserva-datos').disabled,true);
 assert.equal(s.element('reserva-guardar').hidden,true);
});


test('past calendar hours are blocked before opening a reservation',()=>{
 const s=setup();assert.equal(s.opciones.selectAllow({startStr:'2000-01-01T09:00:00-03:00'}),false);
 s.opciones.select({startStr:'2000-01-01T09:00:00-03:00'});
 assert.equal(s.element('reserva-inicio').value,'2099-10-26T11:17');
 assert.equal(s.element('peluqueria-seleccionar-hora').hidden,false);
 assert.equal(s.opciones.selectAllow({startStr:'2099-10-26T11:17:00-03:00'}),true);
});

test('expired reservation time is stopped before tutor lookup and before submission',async()=>{
 const s=setup();s.element('reserva-datos').hidden=true;s.element('reserva-inicio').value='2000-01-01T09:00';await s.buscar();
 assert.equal(s.element('reserva-datos').hidden,true);
 assert.match(s.element('peluqueria-reserva-error').textContent,/futura/);
 s.element('reserva-inicio').value='2099-10-26T11:17';await s.buscar();
 s.element('reserva-inicio').value='2000-01-01T09:00';await s.submit();
 assert.equal(s.requests,0);assert.equal(s.element('reserva-inicio').type,'datetime-local');
});


test('pet is fixed before procedures and cannot be changed within the reservation',async()=>{
 const s=setup();await s.buscar();
 assert.equal(s.element('reserva-paso-procedimientos').disabled,true);
 s.fijarMascota();
 const selector=s.element('reserva-cliente');
 assert.equal(selector.disabled,true);
 assert.equal(s.element('reserva-cliente-existente').hidden,true);
 assert.equal(s.element('reserva-cambiar-tutor').hidden,true);
 assert.equal(s.element('reserva-opcion-mascota-nueva').hidden,true);
 assert.equal(s.element('reserva-paso-procedimientos').disabled,false);
 assert.match(s.element('reserva-mascota-fijada').textContent,/Luna/);
 selector.value='local:2';selector.listeners.change.call(selector);
 assert.equal(selector.value,'local:1');
 s.element('reserva-cambiar-tutor').listeners.click();
 assert.equal(s.element('reserva-cambiar-tutor').hidden,true);
 assert.equal(s.element('reserva-paso-procedimientos').disabled,false);
});


test('a paid appointment blocks unauthorized users with SweetAlert instead of opening a modal',()=>{
 const s=setup();
 s.opciones.eventClick({jsEvent:{preventDefault(){}},event:{extendedProps:{reserva:{estado:'pagada',bloqueada:true}}}});
 assert.equal(s.modales.length,0);assert.equal(s.alertas.length,1);
 assert.equal(s.alertas[0].title,'Hora tomada con éxito');
 assert.match(s.alertas[0].text,/No cuentas con los permisos para seguir/);
});
