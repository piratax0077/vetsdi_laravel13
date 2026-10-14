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
    vm.runInNewContext(script, {
        document: {addEventListener: (_, callback) => callback(), getElementById: () => controls[0], createElement: () => ({})},
        window: {}, agregar_medicamentos_ficha: () => serialized++, agregar_examenes_ficha: () => serialized++,
    });
    return {controls, get serialized() {return serialized;}, get prevented() {return prevented;},
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
