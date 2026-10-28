const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const view=fs.readFileSync('resources/views/app/paciente/dependientes.blade.php','utf8');
function extract(name) {
    const start=view.indexOf('function '+name+'(');
    let end=view.indexOf('{',start)+1,depth=1;
    while(depth){if(view[end]==='{')depth++;if(view[end]==='}')depth--;end++;}
    return view.slice(start,end).replace(/@json\(\(bool\) auth\(\)->user\(\)\?->hasRole\('Profesional'\)\)/g,'false');
}
function setup() {
    const fields=new Map();
    function $(key){
        if(!fields.has(key))fields.set(key,{value:'',checked:false});
        const state=fields.get(key);
        return {val(value){if(arguments.length){state.value=value;return this;}return state.value;},prop(key,value){state[key]=value;return this;},html(){return this;},is(){return state.checked;}};
    }
    const context=vm.createContext({$,mascotaEditandoId:null});
    vm.runInContext(extract('setModoEdicion')+extract('toggleFechaEsterilizacionDesconocida'),context);
    return {context,$,fields};
}
test('registro habilita antecedentes y permite alternar la fecha desconocida',()=>{
    const s=setup();
    s.context.setModoEdicion(null);
    ['cirugias','vacunas','ultima_desparasitacion','producto_desparasitacion','fecha_esterilizacion_desconocida'].forEach(campo=>assert.equal(s.fields.get('#modal_agregar_dep_nuevo_'+campo).disabled,false));
    s.$('#modal_agregar_dep_nuevo_esterilizado').val('1');
    s.context.toggleFechaEsterilizacionDesconocida();
    assert.equal(s.fields.get('#modal_agregar_dep_nuevo_fecha_esterilizacion').disabled,false);
    assert.equal(s.fields.get('#modal_agregar_dep_nuevo_fecha_esterilizacion').required,true);
    s.fields.get('#modal_agregar_dep_nuevo_fecha_esterilizacion_desconocida').checked=true;
    s.context.toggleFechaEsterilizacionDesconocida();
    assert.equal(s.fields.get('#modal_agregar_dep_nuevo_fecha_esterilizacion').disabled,true);
    s.fields.get('#modal_agregar_dep_nuevo_fecha_esterilizacion_desconocida').checked=false;
    s.context.toggleFechaEsterilizacionDesconocida();
    assert.equal(s.fields.get('#modal_agregar_dep_nuevo_fecha_esterilizacion').disabled,false);
});
test('volver de edición a registro no deja antecedentes bloqueados',()=>{
    const s=setup();
    s.context.setModoEdicion(1);
    s.context.toggleFechaEsterilizacionDesconocida();
    assert.equal(s.fields.get('#modal_agregar_dep_nuevo_vacunas').disabled,true);
    s.context.setModoEdicion(null);
    assert.equal(s.fields.get('#modal_agregar_dep_nuevo_vacunas').disabled,false);
    assert.equal(s.fields.get('#modal_agregar_dep_nuevo_producto_desparasitacion').disabled,false);
});
