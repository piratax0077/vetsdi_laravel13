const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

test('sending consent displays sent status without entering the legacy signature wait',()=>{
    const template=fs.readFileSync('resources/views/general/modal/m_aconsent_tto.blade.php','utf8');
    const source=template.slice(template.indexOf('    function registrarConsentimientoTutor('),template.indexOf('    function checkToken(')).replace(/{{[\s\S]*?}}/g,'valor');
    const fields=new Map();
    function $(selector){
        if(!fields.has(selector)) fields.set(selector,{value:'1',props:{},visible:null});
        const state=fields.get(selector);
        return {
            val(value){if(arguments.length){state.value=value;return this;}return state.value;},
            prop(key,value){state.props[key]=value;return this;},
            attr(){return this;}, text(value){state.text=value;return this;},
            show(){state.visible=true;return this;},hide(){state.visible=false;return this;},
        };
    }
    $.ajax=options=>options.success({estado:1,last_id:8,notificaciones:{resumen:'Correo pendiente.'}});
    let refreshed=false;
    const context={$,consentimientoEnProceso:false,CSRF_TOKEN:'test',console:{log(){}},swal(){},
        mostrarConsentimientosTutor(){refreshed=true;},
        registar_solicitar_autorizacion_cons(){throw Error('No debe usar el flujo antiguo');},
        checkToken(){throw Error('No debe esperar firma inmediata');},
    };
    vm.createContext(context);vm.runInContext(source,context);
    context.registrarConsentimientoTutor('documentos');
    assert.match(fields.get('#consentimiento-estado-envio').text,/Enviado.*pendiente de firma/);
    assert.equal(fields.get('#div_btn_aprobacion_espera, #div_btn_aprobacion_ok').visible,false);
    assert.equal(fields.get('#btn_solicitar_consentimiento_app').props.disabled,true);
    assert.equal(context.consentimientoEnProceso,false);
    assert.equal(refreshed,true);
});
