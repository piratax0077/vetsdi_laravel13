const {test}=require('node:test');
const assert=require('node:assert/strict');
const vm=require('node:vm');const fs=require('node:fs');
function promesa(p){return {then(fn,fail){return promesa(p.then(fn,fail));},done(fn){p.then(fn,()=>{});return this;},fail(fn){p.catch(fn);return this;},always(fn){p.then(fn,fn);return this;},native:p};}
function setup(){
 const requests=[];let state={config:{lugares:[],tamanos:[],parametros:{}},aranceles:[],examenes:[],procedimientos:[]},failure=null;
 function $(arg){if(typeof arg==='function')return;throw new Error('DOM inesperado');}
 $.Deferred=()=>{let resolve,reject;let p=new Promise((a,b)=>{resolve=a;reject=b;});let d=promesa(p);d.resolve=resolve;d.reject=reject;d.promise=()=>d;return d;};
 $.ajax=options=>{requests.push(options);if(failure){const error=failure;failure=null;return promesa(Promise.reject(error));}if(options.method==='POST')return promesa(Promise.resolve({id:'guardado'}));return promesa(Promise.resolve(state));};
 const window={arancelesVeterinariosConfig:{listar:'/aranceles',guardar:'/aranceles',csrf:'token'}};
 vm.runInNewContext(fs.readFileSync('public/js/aranceles.js','utf8'),{window,jQuery:$,$,isFinite,JSON,Math,Date});
 return {service:window.Aranceles.servicio,requests,setFailure(value){failure=value;}};
}
test('catalogs and settings load through a single authenticated request',async()=>{
 const s=setup();const result=await Promise.all(['aranceles','examenes','procedimientos'].map(c=>s.service.listar(c).native));
 assert.deepEqual(JSON.parse(JSON.stringify(result)),[[],[],[]]);assert.equal(s.requests.length,1);
});
test('saving sends account scoped data with CSRF and refreshes the next list',async()=>{
 const s=setup();await s.service.listar('procedimientos').native;
 await s.service.crear('procedimientos',{codigo:'VET-001',valorNeto:80000}).native;
 const post=s.requests[1];assert.equal(post.headers['X-CSRF-TOKEN'],'token');assert.equal(JSON.parse(post.data).datos.valorNeto,80000);
 assert.equal(JSON.parse(post.data).id,null);assert.equal('id_profesional' in JSON.parse(post.data),false);
 await s.service.listar('procedimientos').native;assert.equal(s.requests.length,3);
});
test('server validation errors remain available to the price form',async()=>{
 const s=setup();s.setFailure({responseJSON:{errors:{codigo:['Código duplicado.']}}});
 await assert.rejects(s.service.crear('procedimientos',{}).native,error=>error.campo==='codigo'&&error.mensaje==='Código duplicado.');
});
