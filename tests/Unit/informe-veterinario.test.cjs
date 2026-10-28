const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
function setup() {
    const view=fs.readFileSync('resources/views/general/sidebar/modal_form_generales/modal_informe_medico.blade.php','utf8');
    const fields=new Map(), requests=[];
    function $(key) {
        if(!fields.has(key))fields.set(key,{value:'',disabled:false});
        const state=fields.get(key);
        return {
            val(value){if(arguments.length){state.value=value;return this;}return state.value;},
            prop(key,value){state[key]=value;return this;},
            attr(key,value){state[key]=value;return this;},
            text(value){state.text=value;return this;},
            next(){return {length:0};}
        };
    }
    $.fn={};
    $.ajax=req=>requests.push(req);
    const context=vm.createContext({$});
    vm.runInContext(view.split('<script>')[1].split('</script>')[0],context);
    $('#hora_medica').val('7');
    $('#comentarios_informe_medico').val('Informe veterinario');
    return {context,$,fields,requests};
}
test('genera una vez y muestra el PDF y resultado del envio',()=>{
    const s=setup();
    s.context.generarInformeVeterinario();
    s.context.generarInformeVeterinario();
    assert.equal(s.requests.length,1);
    assert.equal(s.requests[0].data.comentarios_informe_medico,'Informe veterinario');
    s.requests[0].success({estado:1,id_last:5,pdf_url:'/pdf?id_informe_medico=5',envio:{estado:1},msj:'Enviado'});
    s.requests[0].complete();
    assert.equal(s.fields.get('#informe_veterinario_pdf').href,'/pdf?id_informe_medico=5');
    assert.equal(s.fields.get('#informe_veterinario_estado').text,'Enviado');
    assert.equal(s.fields.get('#informe_veterinario_reenviar').hidden,true);
    s.context.generarInformeVeterinario();
    assert.equal(s.requests.length,1);
});
test('si falla el correo reenvia el mismo informe sin generar otro',()=>{
    const s=setup();
    s.context.generarInformeVeterinario();
    s.requests[0].success({estado:1,id_last:5,pdf_url:'/pdf',envio:{estado:0},msj:'Guardado, envío pendiente'});
    s.requests[0].complete();
    assert.equal(s.fields.get('#informe_veterinario_reenviar').hidden,false);
    s.context.reenviarInformeVeterinario();
    assert.equal(s.requests[1].data.id_informe_medico,5);
    assert.equal(s.requests[1].data.comentarios_informe_medico,undefined);
});
test('muestra errores y conserva el texto para reintentar',()=>{
    const s=setup();
    s.context.generarInformeVeterinario();
    s.requests[0].error({responseJSON:{errors:{hora_medica:['Hora inválida']}}});
    s.requests[0].complete();
    assert.equal(s.fields.get('#informe_veterinario_estado').text,'Hora inválida');
    assert.equal(s.$('#comentarios_informe_medico').val(),'Informe veterinario');
    assert.equal(s.fields.get('#informe_veterinario_generar').disabled,false);
});
test('un editor visual vacio no genera un informe',()=>{
    const s=setup();
    s.$('#comentarios_informe_medico').val('<p><br>&nbsp;</p>');
    s.context.generarInformeVeterinario();
    assert.equal(s.requests.length,0);
});
