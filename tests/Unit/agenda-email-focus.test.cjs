const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const view = fs.readFileSync('resources/views/template/profesional/template.blade.php', 'utf8');
const source = view.slice(view.indexOf('function validar_email_agenda()'), view.indexOf('function validar_email_agenda_representante()'))
    .replace(/\{\{[\s\S]*?\}\}/g, 'url');

function setup() {
    const fields = new Map();
    let callback;
    let focused = '#reserva_hora_telefono_uno';
    function $(id) {
        if (!fields.has(id)) fields.set(id, {value: ''});
        const field = fields.get(id);
        return {
            val(value) { if (arguments.length) {field.value = value; return this;} return field.value; },
            text(value) {field.text = value; return this;},
            html() {return this;},
            show() {field.visible = true; return this;},
            hide() {field.visible = false; return this;},
            prop(key, value) {field[key] = value; return this;},
            attr(key, value) {field[key] = value; return this;},
            focus() {focused = id; return this;},
        };
    }
    $.ajax = () => ({done(fn) {callback = fn; return this;}, fail() {return this;}});
    const context = vm.createContext({$, validarEdad: () => true, console});
    vm.runInContext(source, context);
    $('#reserva_hora_correo').val('existente@example.com');
    context.validar_email_agenda();
    return {$, fields, respond: value => callback(value), focus: () => focused};
}

test('un correo duplicado conserva el foco en el teléfono y muestra el aviso', () => {
    const state = setup();
    state.respond('fail');
    assert.equal(state.focus(), '#reserva_hora_telefono_uno');
    assert.equal(state.fields.get('#mensaje_email_reserva').visible, true);
    assert.equal(state.fields.get('#guardar_reserva_paciente').disabled, true);
    state.$('#reserva_hora_telefono_uno').val('912345678');
    assert.equal(state.$('#reserva_hora_telefono_uno').val(), '912345678');
});

test('una respuesta para un correo anterior no altera la validación actual', () => {
    const state = setup();
    state.$('#reserva_hora_correo').val('nuevo@example.com');
    state.respond('fail');
    assert.equal(state.fields.has('#mensaje_email_reserva'), false);
    assert.equal(state.fields.has('#guardar_reserva_paciente'), false);
    assert.equal(state.focus(), '#reserva_hora_telefono_uno');
});
