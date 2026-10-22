const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');

function setup(){
    const elements=new Map();let requests=0,resolve;
    function element(id){
        if(!elements.has(id))elements.set(id,{value:'',dataset:{fecha:'2026-10-21'},disabled:false,checked:false,hidden:false,listeners:{},addEventListener(name,fn){this.listeners[name]=fn;}});
        return elements.get(id);
    }
    const calendar=class {render(){}};
    const ctx={document:{getElementById:element,querySelectorAll:()=>[]},FullCalendar:{Calendar:calendar},FormData:class{},
        jQuery:()=>({modal(){},on(){}}),fetch:()=>{requests++;return new Promise(done=>resolve=done);}};
    ctx.window=ctx;vm.createContext(ctx);
    vm.runInContext(fs.readFileSync(path.join(__dirname,'../../public/js/peluqueria-agenda.js'),'utf8'),ctx);
    return {element,get requests(){return requests;},submit:()=>element('peluqueria-reserva-form').listeners.submit({preventDefault(){}}),
        fail:()=>resolve({ok:false,json:async()=>({errors:{inicio:['Horario ocupado.']}})})};
}

test('repeated reservation submissions send a single request and allow retry after failure',async()=>{
    const s=setup();const pending=s.submit();await s.submit();
    assert.equal(s.requests,1);assert.equal(s.element('reserva-guardar').disabled,true);
    s.fail();await pending;
    assert.equal(s.element('reserva-guardar').disabled,false);
    assert.equal(s.element('peluqueria-reserva-error').textContent,'Horario ocupado.');
    const retry=s.submit();assert.equal(s.requests,2);s.fail();await retry;
});
