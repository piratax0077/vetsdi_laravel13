const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

test('a form submits once and disables only its submit button',()=>{
    let ready,submit;
    const button={disabled:false};
    const form={dataset:{},classList:{contains(name){return name==='vet-operacion-form';}},querySelectorAll:()=>[button]};
    const context={window:{},document:{addEventListener(type,callback){if(type==='DOMContentLoaded')ready=callback;if(type==='submit')submit=callback;}}};
    vm.runInNewContext(fs.readFileSync('public/js/operacion-veterinaria.js','utf8'),context);ready();
    let prevented=0;
    submit({target:form,preventDefault(){prevented++;}});assert.equal(prevented,0);assert.equal(button.disabled,true);
    submit({target:form,preventDefault(){prevented++;}});assert.equal(prevented,1);
});
