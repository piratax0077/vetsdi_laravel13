const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const vm=require('node:vm');

function setup(fetchResult){
    const campos=Object.entries({perfil_nombre:'Tutor',perfil_apellido_uno:'Apellido',perfil_nac:'',Perfil_email:'nuevo@example.test',Perfil_fono:'123',perfil_dire:'Calle',perfil_region:'1',perfil_ciudad:'2',perfil_numero_dir:'12 B'}).map(([name,value])=>({name,value,defaultValue:value,disabled:false}));
    campos.push({name:'perfil_rut',value:'11111111-1',disabled:true});
    const form={listeners:{},querySelectorAll:s=>s==='button'?[]:campos,addEventListener(type,fn){this.listeners[type]=fn;},reset(){}};
    const clases=new Set(['show']);
    const panel={aviso:null,classList:{add:c=>clases.add(c),remove:c=>clases.delete(c),contains:c=>clases.has(c)},querySelector:s=>s==='form'?form:panel.aviso,prepend:el=>panel.aviso=el};
    const lectura={classList:{add(){}}};const requests=[];let ready;
    const ctx={document:{addEventListener:(n,fn)=>ready=fn,getElementById:id=>id==='pinfo_basica_2'?panel:id==='info_basica-1'?lectura:null,querySelectorAll:()=>[],createElement:()=>({dataset:{},setAttribute(){}})},perfilTutorConfig:{todo:'/guardar',csrf:'token'},fetch:(url,options)=>{requests.push({url,data:JSON.parse(options.body),options});return fetchResult();}};
    ctx.window=ctx;vm.createContext(ctx);vm.runInContext(fs.readFileSync(path.join(__dirname,'../../public/js/perfil-tutor.js'),'utf8'),ctx);ready();
    return {ctx,form,panel,campos,requests};
}
const ok=()=>Promise.resolve({ok:true,json:async()=>({success:true,paciente:{nombres:'Tutor',apellido_uno:'Apellido',email:'nuevo@example.test'}})});

test('tutor saves local form values and address number with optional birthdate',async()=>{
    const s=setup(ok);assert.equal(await s.ctx.guardar_perfil_paciente(),true);
    assert.equal(s.requests[0].data.perfil_email,'nuevo@example.test');
    assert.equal(s.requests[0].data.perfil_numero_dir,'12 B');
    assert.equal(s.requests[0].data.perfil_nac,null);
    assert.equal('perfil_rut' in s.requests[0].data,false);
    assert.equal(s.requests[0].options.method,'POST');
    assert.equal(s.panel.classList.contains('show'),false);
});
test('failed tutor save keeps draft open and explains validation errors',async()=>{
    const s=setup(()=>Promise.resolve({ok:false,json:async()=>({errors:{perfil_email:['Correo ya registrado.']}})}));
    assert.equal(await s.ctx.guardar_perfil_paciente(),false);
    assert.equal(s.panel.aviso.textContent,'Correo ya registrado.');
    assert.equal(s.panel.classList.contains('show'),true);
    assert.equal(s.campos[0].disabled,false);
    assert.equal(s.campos[0].value,'Tutor');
});
test('Enter saves the tutor once while a request is pending',async()=>{
    let finish;const s=setup(()=>new Promise(resolve=>finish=resolve));let prevented=false;
    s.form.listeners.submit({preventDefault(){prevented=true;},stopImmediatePropagation(){}});
    assert.equal(await s.ctx.guardar_perfil_paciente(),false);
    assert.equal(prevented,true);assert.equal(s.requests.length,1);
    finish(await ok());await new Promise(resolve=>setImmediate(resolve));
    assert.equal(s.campos[0].disabled,false);
});
