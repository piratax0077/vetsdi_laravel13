const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const path=require('node:path');

function setup(fetchResult,tipo='centro'){
    const requests=[],aviso={className:'',textContent:''};
    const campos=[{name:'nombre',value:'Centro editado',disabled:false},{name:'ciudad',value:'',disabled:false},{name:'direccion',value:'',disabled:false},{name:'numero_dir',value:'',disabled:false},{name:'id_responsable',value:'',disabled:false}];
    const form={action:'/guardar',dataset:{centroForm:tipo},listeners:{},querySelector:selector=>selector==='[data-resultado]'?aviso:null,querySelectorAll:()=>campos,addEventListener(type,fn){this.listeners[type]=fn;},reset(){}};
    let ready;
    const foto={src:'anterior.png'};
    const ctx={document:{addEventListener:(name,fn)=>ready=fn,querySelectorAll:()=>[form],getElementById:()=>foto},perfilCentroConfig:{csrf:'token',fotoPredeterminada:'predeterminada.png'},FormData:class extends Map{constructor(){super(campos.map(c=>[c.name,c.value]));}},fetch:(url,options)=>{requests.push({url,options});return fetchResult(requests.length);},setTimeout:fn=>fn()};
    ctx.window=ctx;vm.createContext(ctx);vm.runInContext(fs.readFileSync(path.join(__dirname,'../../public/js/perfil-centro-medico.js'),'utf8'),ctx);ready();
    return {ctx,form,campos,aviso,requests,foto};
}
const ok=()=>Promise.resolve({ok:true,json:async()=>({estado:1,msj:'Guardado'})});

test('saving an incomplete initial profile omits the unregistered address',async()=>{
    const s=setup(ok);await s.form.listeners.submit({preventDefault(){}});
    assert.equal(s.requests[0].options.method,'POST');
    assert.equal(s.requests[0].options.headers['X-CSRF-TOKEN'],'token');
    assert.equal(s.requests[0].options.body.get('nombre'),'Centro editado');
    assert.equal(s.requests[0].options.body.has('direccion'),false);
    assert.equal(s.campos[0].defaultValue,'Centro editado');
    assert.equal(s.aviso.className,'alert alert-success');
});
test('validation errors preserve edited fields and enable retry',async()=>{
    const s=setup(()=>Promise.resolve({ok:false,json:async()=>({errors:{email:['Correo no válido.']}})}));
    await s.form.listeners.submit({preventDefault(){}});
    assert.equal(s.aviso.textContent,'Correo no válido.');
    assert.equal(s.campos[0].value,'Centro editado');
    assert.equal(s.campos[0].disabled,false);
    assert.equal(s.campos[0].defaultValue,undefined);
});
test('repeated clicks issue only one save request',async()=>{
    let finish;const s=setup(()=>new Promise(resolve=>finish=resolve));
    const first=s.form.listeners.submit({preventDefault(){}});
    await s.form.listeners.submit({preventDefault(){}});
    assert.equal(s.requests.length,1);finish(await ok());await first;
});
test('photo upload updates the preview only after a successful save',async()=>{
    const s=setup(()=>Promise.resolve({ok:true,json:async()=>({success:true,foto_url:'/storage/fotos_perfil/nueva.png'})}),'foto');
    await s.form.listeners.submit({preventDefault(){}});
    assert.equal(s.foto.src,'/storage/fotos_perfil/nueva.png');
});
