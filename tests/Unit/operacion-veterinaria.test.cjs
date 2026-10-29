const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

test('a form submits once and disables only its submit button',()=>{
    let ready,submit;
    const button={disabled:false};
    const form={dataset:{},addEventListener(type,callback){if(type==='submit')submit=callback;},querySelectorAll:()=>[button]};
    const context={document:{addEventListener(type,callback){ready=callback;},querySelectorAll:()=>[form]}};
    vm.runInNewContext(fs.readFileSync('public/js/operacion-veterinaria.js','utf8'),context);ready();
    let prevented=0;
    submit({preventDefault(){prevented++;}});assert.equal(prevented,0);assert.equal(button.disabled,true);
    submit({preventDefault(){prevented++;}});assert.equal(prevented,1);
});
