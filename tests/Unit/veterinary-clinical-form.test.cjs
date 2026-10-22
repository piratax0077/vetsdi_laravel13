const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup(saved = {}, old = {}) {
    const controls = [
        {name: 'descripcion_hipotesis', value: 'Control', type: 'text'},
        {name: 'motivo', value: '', type: 'text', required: true},
        {name: 'enf_cronico', value: 'on', type: 'checkbox', checked: true},
    ];
    const events = {};
    let serialized = 0, prevented = false;
    const form = {
        elements: controls,
        querySelectorAll: selector => selector === '[required]' ? controls.filter(c => c.required) : controls.filter(c => c.type === 'checkbox'),
        addEventListener: (event, callback) => events[event] = callback,
    };
    controls.forEach(control => Object.assign(control, {
        closest: selector => selector === 'form' ? form : null,
        addEventListener() {}, setCustomValidity(text) { this.error = text; },
        reportValidity() {}, before(input) { this.hiddenFallback = input; },
    }));
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views/atencion_veterinaria/partials/guardar_ficha_completa.blade.php'), 'utf8');
    const script = source.match(/<script>([\s\S]*?)<\/script>/)[1]
        .replace(/@json\(\$fichaVeterinariaGeneralData \?\? \[\]\)/, JSON.stringify(saved))
        .replace(/@json\(session\(\)->getOldInput\(\)\)/, JSON.stringify(old));
    const context = {
        document: {addEventListener: (_, callback) => callback(), getElementById: () => controls[0], createElement: () => ({})},
        window: {}, agregar_medicamentos_ficha: () => serialized++, agregar_examenes_ficha: () => serialized++,
    };
    vm.runInNewContext(script, context);
    return {controls, context, get serialized() {return serialized;}, get prevented() {return prevented;},
        submit() {events.submit({preventDefault() {prevented = true;}});}};
}

test('diagnosis is the only required clinical control', () => {
    const s = setup();
    assert.equal(s.controls[0].required, true);
    assert.equal(s.controls[1].required, false);
    s.controls[0].value = '   ';
    s.submit();
    assert.equal(s.prevented, true);
    assert.equal(s.serialized, 0);
});

test('reopening restores saved data and validation errors restore the latest input', () => {
    const s = setup({motivo: 'Anterior', enf_cronico: '0'}, {motivo: 'Última edición'});
    assert.equal(s.controls[1].value, 'Última edición');
    assert.equal(s.controls[2].checked, false);
    assert.equal(s.controls[2].hiddenFallback.value, '0');
});

test('submitting with Enter also includes prescriptions and examinations', () => {
    const s = setup();
    s.submit();
    assert.equal(s.prevented, false);
    assert.equal(s.serialized, 2);
});

test('quick save waits for dental changes before sending the clinical record', async () => {
    const s=setup();let finish,requests=0;
    s.context.window.odontologiaVetPrepararGuardado=()=>new Promise(resolve=>finish=resolve);
    s.context.FormData=class {set(){}};
    s.context.fetch=async()=>{requests++;return {ok:true,json:async()=>({mensaje:'Guardado'})};};
    s.context.swal=()=>{};
    const saving=s.context.window.guardarFichaVeterinariaAjax();
    assert.equal(requests,0);
    finish();await saving;
    assert.equal(requests,1);
});

test('a failed dental change prevents quick save and explains the error', async () => {
    const s=setup();let requests=0,notice;
    s.context.window.odontologiaVetPrepararGuardado=async()=>{throw Error('Error al guardar la pieza');};
    s.context.fetch=async()=>{requests++;};
    s.context.swal=data=>notice=data;
    await s.context.window.guardarFichaVeterinariaAjax();
    assert.equal(requests,0);
    assert.equal(notice.text,'Error al guardar la pieza');
});
