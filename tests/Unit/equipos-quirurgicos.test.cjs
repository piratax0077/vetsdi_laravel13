const {test}=require('node:test');const assert=require('node:assert/strict');const fs=require('node:fs');const vm=require('node:vm');
function setup(){
 const elementos=new Map(),peticiones=[];
 function elemento(){return {children:[],value:'',textValue:'',empty(){this.children=[];return this;},append(el){this.children.push(el);return this;},text(t){this.textValue=t;return this;},val(t){this.value=t;return this;},modal(){return this;},on(){return this;}};}
 function $(selector){if(selector.startsWith('<'))return elemento();if(!elementos.has(selector))elementos.set(selector,elemento());return elementos.get(selector);}
 $.ajax=()=>{const req={done(fn){this.success=fn;return this;},fail(fn){this.failure=fn;return this;}};peticiones.push(req);return req;};
 const source=fs.readFileSync('resources/views/app/profesional/equipo.blade.php','utf8');const start=source.indexOf('    var peticion_ver_equipo');const end=source.indexOf('    function guardar_profesional_equipo()',start);
 const context={$,lista_profesionales_eq:[{viejo:true}],profesional_autocomplete(){},eliminar_nuevo_profesional_editar(){}};vm.runInNewContext(source.slice(start,end),context);
 return {context,elementos,peticiones};
}
const miembros=[{posicion:'CIRUJANO',id_profesional:1,profesional:{nombre:'Uno',apellido_uno:'Apellido',apellido_dos:'Segundo'}},{posicion:'AYUDANTE',id_profesional:2,profesional:null},{posicion:'ANESTESISTA',id_profesional:3,profesional:{nombre:'Tres',apellido_uno:'Apellido'}}];
test('view renders every member even if a professional reference is missing',()=>{
 const s=setup();s.context.ver_equipo(1);s.peticiones[0].success({equipo:{nombre:'Completo'},registros:miembros});
 const filas=s.elementos.get('#lista_profesionales_equipo').children;assert.equal(filas.length,3);assert.equal(filas[0].children[1].children[0].value,'Uno Apellido Segundo');assert.match(filas[1].children[1].children[0].value,/no disponible/);assert.equal(filas[2].children[0].textValue,'ANESTESISTA');
});
test('opening another team for editing clears the previous list',()=>{
 const s=setup();s.context.ver_equipo(1,'editar');s.peticiones[0].success({registros:miembros});assert.equal(s.context.lista_profesionales_eq.length,3);
 s.context.ver_equipo(2,'editar');s.peticiones[1].success({registros:[miembros[0]]});assert.equal(s.context.lista_profesionales_eq.length,1);
});
test('outdated response cannot replace the team currently being viewed',()=>{
 const s=setup();s.context.ver_equipo(1);s.context.ver_equipo(2);s.peticiones[1].success({equipo:{nombre:'Actual'},registros:[miembros[2]]});s.peticiones[0].success({equipo:{nombre:'Anterior'},registros:miembros});
 assert.equal(s.elementos.get('#lista_profesionales_equipo').children.length,1);assert.equal(s.elementos.get('#titulo_ver_equipo').textValue,'Equipo: Actual');
});
