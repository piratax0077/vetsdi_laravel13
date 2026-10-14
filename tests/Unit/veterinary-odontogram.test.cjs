const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup(fetchResult) {
    const source=fs.readFileSync(path.join(__dirname,'../../resources/views/general/secciones_ficha/partials/odontograma_mascota.blade.php'),'utf8');
    const script=source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/\{\{[\s\S]*?\}\}/g,'0');
    const elements=new Map(),requests=[];let reloads=0;
    const context={
        document:{getElementById:id=>elements.get(id)||null,querySelectorAll:()=>[],addEventListener(){},dispatchEvent(){}},
        CustomEvent:function(type){this.type=type;},alert(){},
        fetch:(url,options)=>{requests.push({url,data:JSON.parse(options.body)});return fetchResult(requests.length);},
        location:{reload:()=>reloads++},setTimeout:callback=>callback(),clearTimeout(){},
        odontologiaVetUuid:()=> '11111111-1111-4111-8111-111111111111',
    };
    context.window=context;vm.createContext(context);vm.runInContext(script,context);
    context.odontoVetContexto={mascota:1,paciente:9,ficha:100,lugar:20,csrf:'test'};
    context.piezaDentalVetActual={dataset:{pieza:'104'},style:{}};
    for(const [id,value] of Object.entries({odontoVetHallazgo:'Fractura',odontoVetTratamiento:'Extracción',odontoVetObservaciones:'Consulta en curso',odontoVetValor:'10000',odontoVetTarifaUco:''}))elements.set(id,{value});
    return {context,requests,get reloads(){return reloads;}};
}
const ok=()=>Promise.resolve({ok:true,json:async()=>({estado:1,msj:'Guardado'})});

test('saving a tooth preserves clinical drafts by avoiding navigation',async()=>{
    const s=setup(ok);const result=await s.context.guardarPiezaVet(false);
    assert.equal(result,true);assert.equal(s.reloads,0);
    assert.equal(s.context.piezaDentalVetActual.dataset.hallazgo,'Fractura');
    assert.equal(s.context.odontoVetPiezaModificada(),false);
});

test('tooth and budget use a single request and prevent repeated clicks',async()=>{
    let finish;const s=setup(()=>new Promise(resolve=>finish=resolve));
    const first=s.context.guardarPiezaVet(true),second=s.context.guardarPiezaVet(true);
    assert.equal(s.requests.length,1);
    assert.equal(s.requests[0].data.valor,10000);
    assert.equal(s.requests[0].data.hallazgo,'Fractura');
    assert.equal(s.requests[0].data.referencia_operacion,'11111111-1111-4111-8111-111111111111');
    finish({ok:true,json:async()=>({estado:1,msj:'Guardado'})});
    assert.equal(await first,true);assert.equal(await second,true);
});

test('retrying a failed budget keeps the same operation reference',async()=>{
    const s=setup(attempt=>attempt===1?Promise.reject(new Error('Sin conexión')):ok());
    assert.equal(await s.context.guardarPiezaVet(true),false);
    assert.equal(await s.context.guardarPiezaVet(true),true);
    assert.equal(s.requests[0].data.referencia_operacion,s.requests[1].data.referencia_operacion);
    assert.equal(s.reloads,0);
});
