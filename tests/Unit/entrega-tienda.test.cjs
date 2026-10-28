const {test}=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const view=fs.readFileSync('resources/views/app/paciente_dependiente/suscripcion_servicios.blade.php','utf8');
const source=view.slice(view.indexOf('    function normalizarEntregaTienda'),view.indexOf('    function configurarEntregaTienda'));
const context=vm.createContext({});
vm.runInContext(source,context);
const normalizar=config=>JSON.parse(JSON.stringify(context.normalizarEntregaTienda(config,'2026-10-27')));
test('sin configuración no inventa fecha ni entrega hoy',()=>{
    assert.deepEqual(normalizar({}),{permite_elegir_fecha:false,fechas_disponibles:[],fecha_estimada:'',recibe_hoy:false});
});
test('solo ofrece fechas válidas disponibles desde hoy, sin duplicados',()=>{
    const config=normalizar({permite_elegir_fecha:true,fechas_disponibles:['2026-10-29','2026-10-26','2026-10-29','2026-10-28','2026-02-30','sin fecha']});
    assert.deepEqual(config.fechas_disponibles,['2026-10-28','2026-10-29']);
    assert.equal(config.permite_elegir_fecha,true);
});
test('fecha estimada es informativa y no habilita selección por sí sola',()=>{
    const config=normalizar({fecha_estimada:'2026-10-29'});
    assert.equal(config.fecha_estimada,'2026-10-29');
    assert.equal(config.permite_elegir_fecha,false);
    assert.equal(config.recibe_hoy,false);
});
test('badge hoy requiere habilitación y no usa una fecha vencida o futura',()=>{
    assert.equal(normalizar({recibe_hoy:true,fecha_estimada:'2026-10-27'}).recibe_hoy,true);
    assert.equal(normalizar({recibe_hoy:true,fecha_estimada:'2026-10-28'}).recibe_hoy,false);
    assert.equal(normalizar({recibe_hoy:true,fecha_estimada:'2026-10-26'}).recibe_hoy,false);
});
test('dirección región y comuna usan filas completas y se retiran fecha y franja antiguas',()=>{
    assert.doesNotMatch(view,/id="tiendaFecha"|id="tiendaFranja"/);
    ['tiendaDireccion','tiendaRegion','tiendaComuna'].forEach(id=>assert.match(view,new RegExp('form-group col-12"[\\s\\S]*?id="'+id+'"')));
});
