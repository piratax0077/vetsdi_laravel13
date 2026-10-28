const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
function setup(hasMedicine=true) {
    const view=fs.readFileSync('resources/views/general/secciones_ficha/receta_examen/modal_recetario_sdi.blade.php','utf8');
    const source=(view.slice(view.indexOf('    function editorMedicamentoVacioSdi'), view.indexOf('    function eliminar_medicamento_sdi'))+
        view.slice(view.indexOf('    var recetaSdiEnProceso'),view.indexOf('    {{-- MEDICAMENTO MANUAL --}}'))).replace(/{{--[\s\S]*?--}}/g,'');
    const values=new Map(), notices=[], requests=[], handlers={};
    const cells=['1','20','Meloxivet Gatos','Meloxicam','30','Frasco 20 mL.','2','2 mL cada 24 horas','1','Vía Oral','3','3 días','0','1','Un(1) frasco','Quitar'].map(text=>({text}));
    const rows=hasMedicine?[{header:true},{cells}]:[{header:true},{cells:[{text:'Sin medicamentos'}]}];
    function $(key) {
        if(key&&key.cells)return {find:()=>Object.assign([...key.cells],{length:key.cells.length})};
        if(key&&key.header)return {find:()=>[]};
        if(key&&typeof key==='object'&&'text' in key)return {text:()=>key.text};
        if(key==='#tabla_medicamento_cirugia_sdi tr')return {length:rows.length,each(callback){rows.forEach((row,i)=>callback.call(row,i,row));}};
        if(key==='#tabla_medicamento_cirugia_sdi tr.tabla_medicamento_cirugia_sdi')return {length:hasMedicine?1:0};
        if(!values.has(key))values.set(key,{value:'',disabled:false,textValue:''});
        const node=values.get(key);
        return {
            val(value){if(arguments.length){node.value=value;return this;}return node.value;},
            text(){return node.textValue;},html(){return '';},is(){return false;},
            prop(name,value){node[name]=value;return this;},modal(value){node.modal=value;return this;}
        };
    }
    $.trim=value=>String(value??'').trim();
    $.inArray=(value,array)=>array.indexOf(value);
    $.ajax=req=>{
        requests.push(req);
        const chain={};
        ['done','fail','always'].forEach(event=>chain[event]=fn=>{handlers[event]=fn;return chain;});
        return chain;
    };
    const context=vm.createContext({$,swal:notice=>notices.push(notice),fixSwalZ(){},CSRF_TOKEN:'test',console:{log(){}}});
    vm.runInContext(source,context);
    return {context,values,requests,notices,handlers,$};
}
test('una segunda pulsación con el editor limpio conserva el medicamento sin mostrar campos faltantes',()=>{
    const s=setup();
    s.context.indicar_medicamento_sdi();
    assert.equal(s.notices.length,0);
    assert.equal(s.requests.length,0);
});
test('generar usa los medicamentos de la tabla aunque los campos de captura estén vacíos',()=>{
    const s=setup();
    s.context.registrar_medicamentos_ficha_sdi();
    const medicamentos=JSON.parse(s.requests[0].data.medicamentos);
    assert.equal(medicamentos.length,1);
    assert.equal(medicamentos[0].producto,'Meloxivet Gatos');
    assert.equal(medicamentos[0].posologia,'2 mL cada 24 horas');
    assert.equal(s.notices.length,0);
    s.context.registrar_medicamentos_ficha_sdi();
    assert.equal(s.requests.length,1);
    s.handlers.done({falla:'0',correo_enviado:true});
    s.handlers.always();
    assert.equal(s.notices[0].icon,'success');
    assert.equal(s.values.get('#indicar_recetario').modal,'hide');
});
test('una tabla sin medicamentos no emite una receta con la fila de marcador',()=>{
    const s=setup(false);
    s.context.registrar_medicamentos_ficha_sdi();
    assert.equal(s.requests.length,0);
    assert.match(s.notices[0].text,/al menos un medicamento/);
});
test('un error de guardado mantiene la receta disponible para reintentar',()=>{
    const s=setup();
    s.context.registrar_medicamentos_ficha_sdi();
    s.handlers.fail({responseJSON:{message:'No se pudo guardar'}});
    s.handlers.always();
    assert.equal(s.values.get('#generar_receta_sdi').disabled,false);
    assert.equal(s.values.get('#indicar_recetario'),undefined);
    s.context.registrar_medicamentos_ficha_sdi();
    assert.equal(s.requests.length,2);
});
test('sin un medicamento agregado se conserva la validación de campos obligatorios',()=>{
    const s=setup(false);
    s.context.indicar_medicamento_sdi();
    assert.match(s.notices[0].text,/Medicamento/);
});
