const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');

function setup(){
    const source=fs.readFileSync(path.join(__dirname,'../../resources/views/template/profesional/template.blade.php'),'utf8');
    const code=source.slice(source.indexOf('        function carga_tipo_especialidad('),source.indexOf('        function cargar_flujo_caja_rendicion_diario('));
    const elements=new Map(),requests=[];
    function $(selector){
        if(Array.isArray(selector))return {each(fn){selector.forEach((v,i)=>fn(i,v));}};
        if(!elements.has(selector))elements.set(selector,{
            value:'',options:[],
            val(v){if(arguments.length){this.value=String(v);return this;}return this.value;},
            empty(){this.options=[];this.value='';return this;},
            append(html){this.options.push(html);return this;},
            find(){return {remove:()=>this.empty()};},
        });
        return elements.get(selector);
    }
    $.ajax=()=>{const request={done(fn){this.resolve=fn;return this;},fail(){return this;}};requests.push(request);return request;};
    const ctx={$};vm.createContext(ctx);vm.runInContext(code,ctx);
    return {$,ctx,requests};
}

test('changing or clearing profession removes old specialty and subspecialty',()=>{
    const {$,ctx,requests}=setup();
    $('#id_especialidad').val('1');$('#id_tipo_especialidad').val('7');$('#id_sub_tipo_especialidad').val('8');
    ctx.carga_tipo_especialidad('id_especialidad','id_tipo_especialidad','');
    assert.equal($('#id_tipo_especialidad').val(),'');
    assert.equal($('#id_sub_tipo_especialidad').val(),'');
    $('#id_especialidad').val('');
    ctx.carga_tipo_especialidad('id_especialidad','id_tipo_especialidad','');
    requests[0].resolve({estado:1,registros:[{id:7,nombre:'Anterior'}]});
    assert.equal($('#id_tipo_especialidad').options.some(s=>s.includes('Anterior')),false);
    assert.equal(requests.length,1);
});

test('out of order specialty responses cannot restore an unrelated subspecialty',()=>{
    const {$,ctx,requests}=setup();
    $('#id_tipo_especialidad').val('7');
    ctx.carga_sub_tipo_especialidad('id_tipo_especialidad','id_sub_tipo_especialidad','');
    $('#id_tipo_especialidad').val('9');
    ctx.carga_sub_tipo_especialidad('id_tipo_especialidad','id_sub_tipo_especialidad','');
    requests[1].resolve({estado:1,registros:[]});
    requests[0].resolve({estado:1,registros:[{id:8,nombre:'Anterior'}]});
    assert.equal($('#id_sub_tipo_especialidad').val(),'0');
    assert.equal($('#id_sub_tipo_especialidad').options.some(s=>s.includes('Anterior')),false);
});
