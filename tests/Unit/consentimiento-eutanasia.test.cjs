const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

test('euthanasia takes the current encounter diagnosis on every opening without changing other consents',()=>{
    const template=fs.readFileSync('resources/views/general/modal/m_consentimiento_eutanasia.blade.php','utf8');
    const source=template.slice(template.indexOf('    function validarDiagnosticoEutanasia()'),template.indexOf('    function validarDiagnostico(diagnostico_cons'));
    const fields=new Map([['#descripcion_hipotesis',{value:'Diagnóstico actual'}],['#diagnostico_cons',{value:'Otro consentimiento'}]]);
    function $(selector){
        if(!fields.has(selector)) fields.set(selector,{});
        const state=fields.get(selector);
        return {
            val(value){if(arguments.length){state.value=value;return this;}return state.value;},
            prop(key,value){state[key]=value;return this;},
            modal(){return this;},show(){return this;},hide(){return this;}
        };
    }
    const context={$ ,mostrar_consentimientos_paciente(){}};
    vm.createContext(context);vm.runInContext(source,context);
    context.cons_eutan();
    assert.equal(fields.get('#diagnostico_eutanasia').value,'Diagnóstico actual');
    fields.get('#descripcion_hipotesis').value='Cambio sin guardar';
    context.cons_eutan();
    assert.equal(fields.get('#diagnostico_eutanasia').value,'Cambio sin guardar');
    assert.equal(fields.get('#diagnostico_cons').value,'Otro consentimiento');
    fields.get('#descripcion_hipotesis').value='';
    context.cons_eutan();
    assert.equal(fields.get('#diagnostico_eutanasia').value,'');
    assert.equal(fields.get('#m_aconsenteutanasia [name="consentimiento"]').disabled,true);
    assert.match(template,/var diagnostico_cons = \$\('#diagnostico_eutanasia'\)\.val\(\)/);
});
