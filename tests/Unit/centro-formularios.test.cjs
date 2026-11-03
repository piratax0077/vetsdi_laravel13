const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');

function entorno(){
    const eventos={};
    class Input {constructor(name,value){this.name=name;this.id='';this.type='text';this.value=value;}}
    const context={window:{},HTMLInputElement:Input,document:{readyState:'loading',addEventListener(nombre,fn){eventos[nombre]=fn;}}};
    vm.runInNewContext(fs.readFileSync('public/js/centro-formularios.js','utf8'),context);
    return {formatear:context.window.CentroFormularios.formatearRut,eventos,Input};
}
test('RUT del centro usa puntos, guion y K mayúscula sin alterar valores incompletos',()=>{
    const {formatear}=entorno();
    assert.equal(formatear('123456785'),'12.345.678-5');
    assert.equal(formatear('12.345.678-k'),'12.345.678-K');
    assert.equal(formatear('123'),'123');
    assert.equal(formatear(''), '');
});
test('el formato se aplica al RUT al salir sin modificar teléfonos ni campos ocultos',()=>{
    const {eventos,Input}=entorno();
    const rut=new Input('rut','123456785');eventos.blur({target:rut});assert.equal(rut.value,'12.345.678-5');
    const tutor=new Input('rut_tutor','123456785');eventos.blur({target:tutor});assert.equal(tutor.value,'12.345.678-5');
    const fono=new Input('telefono','123456785');eventos.blur({target:fono});assert.equal(fono.value,'123456785');
    const oculto=new Input('rut','123456785');oculto.type='hidden';eventos.blur({target:oculto});assert.equal(oculto.value,'123456785');
});
