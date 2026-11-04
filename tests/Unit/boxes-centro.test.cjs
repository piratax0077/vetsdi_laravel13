const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
function setup() {
    const fields=new Map(),requests=[],handlers={};
    function $(key){
        if(!fields.has(key))fields.set(key,{value:'',options:[]});
        const state=fields.get(key);
        return {val(value){if(arguments.length){state.value=value;return this;}return state.value;},
            prop(name,value){state[name]=value;return this;},modal(value){state.modal=value;return this;},
            replaceWith(html){state.replaced=html;return this;},toggleClass(name,value){state[name]=value;return this;},
            filter(callback){return {length:state.options.filter(value=>callback.call({value})).length};}};
    }
    $.fn={};
    $.ajax=req=>{requests.push(req);const chain={};['done','fail','always'].forEach(name=>chain[name]=fn=>{handlers[name]=fn;return chain;});return chain;};
    const window={boxesCentroVeterinarioConfig:{institucion:10,tipoInstitucion:'institucion',lugar:100,token:'token',guardar:'/guardar',editar:'/editar',cargar:'/dame_box'}};
    vm.runInNewContext(fs.readFileSync('public/js/boxes-centro-veterinario.js','utf8'),{window,jQuery:$,swal(){}});
    return {$,fields,requests,handlers,api:window.boxesCentroVeterinario};
}
test('agrega con nombre tipo y piso sin los campos retirados y reemplaza la tabla',()=>{
    const s=setup();
    s.$('#numero_box').val('aa');s.$('#tpo_especializacion').val('Consulta general');s.$('#tpo_equip_servicio').val('1');
    s.api.guardar(false);s.api.guardar(false);
    assert.equal(s.requests.length,1);
    assert.equal(s.requests[0].data.tpo_especializacion,'Consulta general');
    assert.equal(s.requests[0].data.id_lugar_atencion,100);
    assert.equal(s.requests[0].data.seccion_box,undefined);
    s.handlers.done({estado:1,v:'<table>nuevo</table>',mensaje:'Guardado'});s.handlers.always();
    assert.equal(s.fields.get('#tabla_boxes_atencion').replaced,'<table>nuevo</table>');
    assert.equal(s.fields.get('#agreg_box_ad').modal,'hide');
});
test('carga un tipo personalizado y lo guarda al editar sin trasladar el box de sede',()=>{
    const s=setup();
    s.api.cargar(7);
    s.handlers.done({estado:1,box:{id:7,numero_box:'Sala 7',tipo_especializacion:'Tipo personalizado',ubicacion:2}});
    assert.equal(s.$('#editar_tpo_especializacion').val(),'Otro');
    assert.equal(s.$('#editar_tpo_especializacion_otro').val(),'Tipo personalizado');
    assert.equal(s.$('#editar_tpo_equip_servicio').val(),'2');
    s.api.guardar(true);
    assert.equal(s.requests[1].url,'/editar');
    assert.equal(s.requests[1].data.id_box,7);
    assert.equal(s.requests[1].data.tpo_especializacion,'Tipo personalizado');
    assert.equal(s.requests[1].data.id_lugar_atencion,undefined);
});
