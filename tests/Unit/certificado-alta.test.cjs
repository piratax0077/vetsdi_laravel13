const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

for(const file of ['general/modal','general/info_simbologia/modal']) {
    test(`certificate submits POST with CSRF and opens PDF only after saving: ${file}`,()=>{
        const template=fs.readFileSync(`resources/views/${file}/m_certificado_alta.blade.php`,'utf8');
        const source=template.slice(template.indexOf('    function registrar_alta_medica('),template.indexOf('    function ver_pdf_informe_medico_alta(')).replace(/{{[\s\S]*?}}/g,'test');
        let request,success,failure,pdf,alert;
        const values={'#id_fc':'12','#hora_medica':'34','#id_lugar_atencion':'56','#tipo':'2','#comentario':'Alta veterinaria'};
        const $=selector=>({val:()=>values[selector],removeClass(){return this;},addClass(){return this;},hide(){return this;},show(){return this;},html(){return this;},modal(){return this;}});
        $.ajax=options=>{request=options;return {done(fn){success=fn;return this;},fail(fn){failure=fn;return this;}};};
        const context={$,agregarAdjuntoAlDiv(){},console:{log(){}},swal:message=>alert=message,ver_pdf_informe_medico_alta:(...args)=>pdf=args};
        vm.createContext(context);vm.runInContext(source,context);
        context.registrar_alta_medica('tipo','comentario','modal_cert_alta');
        assert.equal(request.type,'POST');
        assert.equal(request.data._token,'test');
        assert.equal(request.data.id_ficha_atencion,'12');
        assert.equal(request.data.comentarios_informe_medico,'Alta veterinaria');
        assert.equal(pdf,undefined);
        success({estado:1});
        assert.deepEqual(pdf,['12','2']);
        failure({status:419});
        assert.equal(alert.icon,"error"); assert.ok(alert.text.length > 0);
    });
}
