const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const path=require('node:path');

function setup(fetchResult,tipo='profesional') {
    const elementos=new Map(),requests=[];
    function el(value='') {
        const classes=new Set(['show']);
        return {value,defaultValue:value,disabled:false,textContent:'',dataset:{},listeners:{},classList:{add:c=>classes.add(c),remove:c=>classes.delete(c),contains:c=>classes.has(c)},querySelector:()=>null,querySelectorAll:()=>[],addEventListener(type,fn){this.listeners[type]=fn;},setAttribute(){},prepend(aviso){this.aviso=aviso;}};
    }
    const campos={editar_rut:'11111111-1',editar_nombres:'Nombre',editar_nombre:'Nombre',editar_apellido_uno:'Apellido',editar_apellido_dos:'',editar_sexo:'F',editar_email:'nuevo@example.test',editar_telefono_uno:'+56 9 1234',num_colegio:'Colegio 42',contrasena_actual:'Anterior123',password_registro:'NuevaClave123',password_confirmacion_registro:'NuevaClave123'};
    for(const [id,v] of Object.entries(campos))elementos.set(id,el(v));
    for(const id of ['ver_nombre','ver_email','ver_num_colegio'])elementos.set(id,el());
    const forms={};
    for(const [tipo,ids] of Object.entries({personales:['info_basica-1','info_basica-2'],contacto:['info_contacto_1','info_contacto_2'],direccion:['info_residencial_1','info_residencial_2'],contrasena:['pass_personal_1','pass_personal_2']})){
        const form=el();form.resetCount=0;form.reset=()=>form.resetCount++;forms[tipo]=form;
        const panel=el();panel.querySelector=selector=>selector==='form'?form:panel.aviso||null;
        elementos.set(ids[0],el());elementos.set(ids[1],panel);
    }
    let ready;
    const context={document:{addEventListener:(name,fn)=>ready=fn,getElementById:id=>elementos.get(id)||null,createElement:()=>el()},perfilUsuarioConfig:{tipo,csrf:'token',personales:'/personales',contacto:'/contacto',direccion:'/direccion',contrasena:'/contrasena'},fetch:(url,options)=>{requests.push({url,options,data:JSON.parse(options.body)});return fetchResult(requests.length);}};
    context.window=context;vm.createContext(context);vm.runInContext(fs.readFileSync(path.join(__dirname,'../../public/js/perfil-usuario.js'),'utf8'),context);ready();
    return {context,elementos,requests,forms};
}
const ok=()=>Promise.resolve({ok:true,json:async()=>({success:true,message:'Guardado',profesional:{nombre:'Nombre',apellido_uno:'Apellido',num_colegio:'Colegio 42',email:'nuevo@example.test'}})});

test('professional saves college number and updates displayed data without reloading',async()=>{
    const s=setup(ok);assert.equal(await s.context.editar_profesional_datos_personales(),true);
    assert.equal(s.requests[0].data.num_colegio,'Colegio 42');
    assert.equal(s.requests[0].options.method,'POST');
    assert.equal(s.elementos.get('ver_num_colegio').textContent,'Colegio 42');
    assert.equal(s.elementos.get('info_basica-2').classList.contains('show'),false);
});
test('validation errors keep the draft open and explain the failed save',async()=>{
    const s=setup(()=>Promise.resolve({ok:false,json:async()=>({errors:{email:['Correo ya registrado.']}})}));
    assert.equal(await s.context.editar_datos_contacto_profesional(),false);
    assert.equal(s.elementos.get('editar_email').value,'nuevo@example.test');
    assert.equal(s.elementos.get('info_contacto_2').classList.contains('show'),true);
    assert.equal(s.elementos.get('info_contacto_2').aviso.textContent,'Correo ya registrado.');
});
test('repeated save clicks do not issue duplicate requests',async()=>{
    let finish;const s=setup(()=>new Promise(resolve=>finish=resolve));
    const first=s.context.editar_datos_contacto_profesional();
    assert.equal(await s.context.editar_datos_contacto_profesional(),false);
    assert.equal(s.requests.length,1);finish(await ok());assert.equal(await first,true);
});
test('Enter in the assistant form submits through the protected endpoint',async()=>{
    const s=setup(ok,'asistente');let prevented=false,stopped=false;
    s.forms.contacto.listeners.submit({preventDefault(){prevented=true;},stopImmediatePropagation(){stopped=true;}});
    await new Promise(resolve=>setImmediate(resolve));
    assert.equal(prevented,true);assert.equal(stopped,true);
    assert.equal(s.requests[0].url,'/contacto');
    assert.equal(s.requests[0].data.email,'nuevo@example.test');
});

test('cancelar restaura el formulario y cierra el panel sin guardar',()=>{
    const s=setup(ok);
    s.context.cancelar_edicion_perfil('personales');
    assert.equal(s.forms.personales.resetCount,1);
    assert.equal(s.elementos.get('info_basica-2').classList.contains('show'),false);
    assert.equal(s.requests.length,0);
});

test('el RUT se muestra formateado después de guardar',async()=>{
    const s=setup(()=>Promise.resolve({ok:true,json:async()=>({success:true,profesional:{nombre:'Nombre',rut:'12345678-5'}})}));
    s.elementos.set('ver_rut',{textContent:''});
    await s.context.editar_profesional_datos_personales();
    assert.equal(s.elementos.get('ver_rut').textContent,'12.345.678-5');
    assert.equal(s.elementos.get('editar_rut').value,'12.345.678-5');
});

test('elegir foto no modifica la imagen guardada antes de confirmar la subida',()=>{
    const fuente=fs.readFileSync(path.join(__dirname,'../../resources/views/app/profesional/perfil_profesional.blade.php'),'utf8');
    const inicio=fuente.indexOf('function cambiar_foto_perfil(');
    const fin=fuente.indexOf('function subir_foto_perfil(',inicio);
    const imagen={src:'/foto-guardada.png'},subidas=[];
    const contexto={document:{getElementById:()=>imagen},subir_foto_perfil:file=>subidas.push(file),swal(){}};
    vm.runInNewContext(fuente.slice(inicio,fin),contexto);
    const archivo={type:'image/png',size:1024};
    contexto.cambiar_foto_perfil({files:[archivo]});
    assert.equal(subidas.length,1);
    assert.equal(imagen.src,'/foto-guardada.png');
});
