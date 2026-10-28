const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function setup() {
    const view = fs.readFileSync(path.join(__dirname, '../../resources/views/atencion_veterinaria/sidebars/informacion_mascota.blade.php'), 'utf8');
    let script = view.match(/<script>([\s\S]*?)<\/script>/)[1];
    // Replace balanced Blade JSON calls; nested route() arguments must remain balanced.
    script = script.replace(/@json\(/g, '\u0001(');
    while (script.includes('\u0001')) {
        const start = script.indexOf('\u0001');
        let end = start + 2, depth = 1;
        while (depth) {
            if (script[end] === '(') depth++;
            if (script[end] === ')') depth--;
            end++;
        }
        const expression = script.slice(start, end);
        script = script.slice(0, start) + (expression.includes('especiesSidebar') ? '[]' : 'null') + script.slice(end);
    }
    const nodes = new Map();
    let resetCount = 0, reloadCount = 0, request, handlers = {};
    const form = { querySelectorAll: () => [{reportValidity: () => true}], reset: () => resetCount++ };
    function $(key) {
        if (key === form) return { find: () => $('buttons') };
        if (String(key).includes('button[type=submit]')) key = 'buttons';
        if (!nodes.has(key)) {
            const node = { value: '', disabled: false, visible: true, content: '',
                0: { scrollIntoView() {} },
                val(value) { if (value === undefined) return this.value; this.value = value; return this; },
                prop(name, value) { if (value === undefined) return this[name]; this[name] = value; return this; },
                hide() { this.visible = false; return this; },
                show() { this.visible = true; return this; },
                toggle(value) { this.visible = value; return this; },
                text(value) { if (value === undefined) return this.content; this.content = value; return this; },
                each() { return this; },
                empty() { return this; }, append() { return this; }
            };
            nodes.set(key, node);
        }
        return nodes.get(key);
    }
    $.ajax = options => {
        request = options;
        const chain = {};
        for (const event of ['done', 'fail', 'always']) chain[event] = callback => { handlers[event] = callback; return chain; };
        return chain;
    };
    const context = vm.createContext({ $, document: { getElementById: () => form }, window: { location: { reload: () => reloadCount++ } }, Option: function () {}, swal() {} });
    vm.runInContext(script, context);
    $('#sidebar_mascota_nombre').val('Plumita');
    return { context, $, form, handlers, get request() { return request; }, get resets() { return resetCount; }, get reloads() { return reloadCount; } };
}

test('cancel restores the original form and shows the saved information', () => {
    const s = setup();
    s.context.editarInformacionMascotaSidebar();
    s.$('#sidebar_mascota_nombre').val('Cambio descartado');
    s.context.cancelarInformacionMascotaSidebar();
    assert.equal(s.$('#sidebar_mascota_nombre').val(), 'Plumita');
    assert.equal(s.$('#info_mascota_sidebar_edit').visible, false);
    assert.equal(s.$('#info_mascota_sidebar_read').visible, true);
});

test('save includes clinical history and prevents duplicate submissions', () => {
    const s = setup();
    s.$('#sidebar_mascota_cirugias').val('Cirugía previa');
    s.$('#sidebar_mascota_vive_con_animales').val('0');
    s.$('#sidebar_mascota_esterilizado').val('0');
    s.$('#sidebar_mascota_fecha_esterilizacion').val('2024-01-01');
    s.context.guardarInformacionMascotaSidebar();
    assert.equal(s.request.data.cirugias, 'Cirugía previa');
    assert.equal(s.request.data.vive_con_animales, '0');
    assert.equal(s.request.data.fecha_esterilizacion, '');
    assert.equal(s.$('buttons').disabled, true);
    s.context.cancelarInformacionMascotaSidebar();
    assert.equal(s.resets, 0);
});

test('validation failure keeps the form available and allows retry', () => {
    const s = setup();
    s.context.guardarInformacionMascotaSidebar();
    s.handlers.fail({ responseJSON: { error: { chip: ['Chip duplicado.'] } } });
    s.handlers.always();
    assert.equal(s.$('#sidebar_mascota_error').content, 'Chip duplicado.');
    assert.equal(s.$('buttons').disabled, false);
    assert.equal(s.reloads, 0);
});

test('updates the saved baseline without reloading and preserves consultation text', () => {
    const s = setup();
    s.context.editarInformacionMascotaSidebar();
    s.$('#sidebar_mascota_dieta').val('Nueva dieta');
    s.$('#dieta').val('Texto sin guardar de la consulta');
    s.$('#descripcion_hipotesis').val('Diagnóstico en edición');
    s.context.guardarInformacionMascotaSidebar();
    s.handlers.done({ estado: 0, msj: 'Error' });
    assert.equal(s.reloads, 0);
    s.handlers.done({ estado: 1, mascota: {nombre: 'Plumita', dieta: 'Nueva dieta'} });
    s.handlers.always();
    assert.equal(s.reloads, 0);
    assert.equal(s.$('#dieta').val(), 'Texto sin guardar de la consulta');
    assert.equal(s.$('#descripcion_hipotesis').val(), 'Diagnóstico en edición');
    assert.equal(s.$('#info_mascota_sidebar_edit').visible, false);
    s.context.editarInformacionMascotaSidebar();
    s.$('#sidebar_mascota_dieta').val('Cambio descartado');
    s.context.cancelarInformacionMascotaSidebar();
    assert.equal(s.$('#sidebar_mascota_dieta').val(), 'Nueva dieta');
});

test('saving without changes cancels without a request or consultation validation', () => {
    const s = setup();
    s.context.editarInformacionMascotaSidebar();
    s.form.querySelectorAll = () => { throw new Error('No debe validar sin cambios'); };
    s.context.guardarInformacionMascotaSidebar();
    assert.equal(s.request, undefined);
    assert.equal(s.reloads, 0);
    assert.equal(s.$('#info_mascota_sidebar_edit').visible, false);
});

test('updates matching antecedents while leaving other consultation inputs intact', () => {
    const s = setup();
    s.$('#sidebar_mascota_dieta').val('Anterior');
    s.$('#dieta').val('Anterior');
    s.context.editarInformacionMascotaSidebar();
    s.$('#sidebar_mascota_dieta').val('Nueva');
    s.context.guardarInformacionMascotaSidebar();
    s.handlers.done({estado: 1, mascota: {dieta: 'Nueva'}});
    assert.equal(s.$('#dieta').val(), 'Nueva');
    assert.equal(s.reloads, 0);
});
