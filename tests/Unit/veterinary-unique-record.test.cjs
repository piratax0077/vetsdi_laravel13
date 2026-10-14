const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('resources/views/general/secciones_ficha/fmu_mascota.blade.php', 'utf8');

test('FVU inline scripts compile after the sanitary history changes', () => {
    const scripts = [...source.matchAll(/<script[^>]*>([\s\S]*?)<\/script>/g)];
    assert.ok(scripts.length);
    for (const [,script] of scripts) assert.doesNotThrow(() => new vm.Script(script));
});

test('sanitary read-only controls include the actual save buttons and preserve close buttons', () => {
    const source = fs.readFileSync('resources/views/general/secciones_ficha/partials/sanitarios_solo_lectura.blade.php', 'utf8');
    let ready;
    const save = {disabled:false};
    const close = {disabled:false};
    const scope = {
        querySelectorAll(selector) { assert.ok(selector.includes('button[id^="btn_add_"]')); return [save]; },
        querySelector() { return null; }, prepend() {},
    };
    const script = source.match(/<script>([\s\S]*?)<\/script>/)[1];
    vm.runInNewContext(script, {document:{
        addEventListener(name, callback) { ready=callback; },
        querySelectorAll(selector) { return selector.includes('#form_ges') ? [scope] : []; },
        createElement() { return {}; },
    }});
    ready(); assert.equal(save.disabled,true); assert.equal(close.disabled,false);
});
