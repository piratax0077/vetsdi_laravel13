const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function ficha(precios, pagado) {
    const inputs = precios.map(value => ({value}));
    const lista = {listeners: {}, querySelectorAll: selector => selector === 'input[type=number]' ? inputs : [], addEventListener(event, fn) {this.listeners[event] = fn;}};
    const total = {dataset: {montoPagado: pagado}};
    const elements = {'pelu-lista': lista, 'pelu-total': total, 'pelu-ficha-form': {addEventListener() {}}};
    vm.runInNewContext(fs.readFileSync('public/js/peluqueria-ficha.js', 'utf8'), {
        document: {getElementById: id => elements[id] || null, querySelectorAll: () => []},
        jQuery: () => ({on() {}}),
    });
    return {total, inputs, recalcular: () => lista.listeners.input()};
}

test('reserva pagada más servicio adicional cobra solo el pendiente', () => {
    const s = ficha([15000, 5000], 15000);
    assert.equal(s.total.textContent, '$5.000');
    s.inputs.push({value: 3000}); s.recalcular();
    assert.equal(s.total.textContent, '$8.000');
});

test('una reserva totalmente pagada no vuelve a cobrarse', () => {
    assert.equal(ficha([15000], 15000).total.textContent, '$0');
});

test('sin pago registrado se cobra el total de los servicios', () => {
    assert.equal(ficha([15000, 5000], undefined).total.textContent, '$20.000');
});

test('el saldo nunca es negativo cuando el pago supera el total', () => {
    assert.equal(ficha([5000], 15000).total.textContent, '$0');
});
