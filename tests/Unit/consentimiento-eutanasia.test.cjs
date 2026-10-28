const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

function setup() {
    const template=fs.readFileSync('resources/views/general/modal/m_consentimiento_eutanasia.blade.php','utf8');
    const fields=new Map();
    const requests=[];
    function $(selector) {
        if(!fields.has(selector)) fields.set(selector,{});
        const state=fields.get(selector);
        return {
            val(value){if(arguments.length){state.value=value;if(selector.includes(','))selector.split(',').forEach(s=>$(s.trim()).val(value));return this;}return state.value;},
            prop(key,value){state[key]=value;return this;},
            text(value){state.text=value;return this;},
            attr(key,value){if(typeof key==='object'){Object.assign(state,key);return this;}if(arguments.length===2){state[key]=value;return this;}return state[key];},
            modal(){return this;}
        };
    }
    $.ajax=options=>requests.push(options);
    const context={$};
    vm.createContext(context);
    vm.runInContext(template.split('<script>')[1].split('</script>')[0],context);
    context.cargarHistorialEutanasia=()=>{};
    $('#id_fc').val('42');
    $('#descripcion_hipotesis').val('Diagnóstico actual');
    $('#diagnostico_cons').val('Otro consentimiento');
    $('#cirugia_cons').val('Otro procedimiento');
    return {context,$,fields,requests};
}

test('precarga diagnóstico actual sin modificar otros consentimientos y exige motivo',()=>{
    const {context,$,fields}=setup();
    context.cons_eutan();
    assert.equal($('#diagnostico_eutanasia').val(),'Diagnóstico actual');
    assert.equal(fields.get('#m_aconsenteutanasia [name="consentimiento"]').disabled,true);
    $('#eutanasia_motivo').val('Tumor terminal');
    context.validarDiagnosticoEutanasia();
    assert.equal(fields.get('#m_aconsenteutanasia [name="consentimiento"]').disabled,false);
    $('#descripcion_hipotesis').val('Cambio sin guardar');
    context.cons_eutan();
    assert.equal($('#diagnostico_eutanasia').val(),'Cambio sin guardar');
    assert.equal($('#diagnostico_cons').val(),'Otro consentimiento');
    assert.equal($('#cirugia_cons').val(),'Otro procedimiento');
});

test('genera una vez, muestra PDF sin esperar token antiguo y conserva el documento al reabrir',()=>{
    const {context,$,fields,requests}=setup();
    context.cons_eutan();
    $('#eutanasia_motivo').val('Tumor terminal');
    context.guardarConsentimientoEutanasia();
    context.guardarConsentimientoEutanasia();
    assert.equal(requests.length,1);
    const req=requests[0];
    assert.equal(req.data.tipo_consentimiento,'eutanasia');
    assert.equal(req.data.id_ficha_atencion,'42');
    assert.equal(req.data.cirugia_cons,'Tumor terminal');
    req.success({estado:1,last_id:8,msj:'Disponible para firma'});
    req.complete();
    assert.equal(fields.get('#eutanasia_pdf').hidden,false);
    assert.match(fields.get('#eutanasia_pdf').href,/id_consentimiento=8&id_ficha_atencion=42/);
    context.cons_eutan();
    assert.equal($('#diagnostico_eutanasia').val(),'Diagnóstico actual');
    context.guardarConsentimientoEutanasia();
    assert.equal(requests.length,1);
    context.nuevoConsentimientoEutanasia();
    assert.equal($('#eutanasia_motivo').val(),'');
    assert.equal(fields.get('#eutanasia_pdf, #eutanasia_nuevo').hidden,true);
});

test('un error permite reintentar sin aparentar un documento generado',()=>{
    const {context,$,requests,fields}=setup();
    context.cons_eutan();
    $('#eutanasia_motivo').val('Motivo');
    context.guardarConsentimientoEutanasia();
    requests[0].error({responseJSON:{errors:{cirugia_cons:['Motivo inválido']}}});
    requests[0].complete();
    assert.equal(fields.get('#eutanasia_estado').text,'Motivo inválido');
    assert.equal(context.eutanasiaGenerada,false);
    context.guardarConsentimientoEutanasia();
    assert.equal(requests.length,2);
});
