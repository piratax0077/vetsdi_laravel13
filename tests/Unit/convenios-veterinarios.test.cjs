const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function pantalla() {
    const eventos = {}, solicitudes = [], avisos = [];
    const doc = {readyState: 'complete'};
    let hora = '1', valor = '10005', abono = '1000', saldo, resumen;
    const estado = {}, opciones = [];
    let seleccionado = '';
    const modal = {find(selector) {
        if (selector === '.bono-convenio-veterinario') return select;
        if (selector === '#bono_hora_medica') return {val: () => hora};
        if (selector === '#bono_valor_consulta') return {val: () => valor};
        if (selector === '#bono_valor_abono_consulta') return {val: () => abono};
        if (selector === '#bono_valor_saldo_consulta') return {val: v => {saldo = v;}};
        if (selector === '.resumen-convenio-veterinario') return {text: v => {resumen = v;}};
        throw new Error(selector);
    }};
    const select = {
        each(fn) {fn.call(select); return select;}, closest() {return modal;},
        data(k, v) {if (arguments.length === 1) return estado[k]; estado[k] = v; return select;},
        prop() {return select;}, empty() {opciones.length = 0; seleccionado = ''; return select;},
        append(o) {opciones.push(o); return select;},
        val(v) {if (!arguments.length) return seleccionado; seleccionado = v; return select;},
        attr() {return '/convenios';},
        find() {return {attr(k) {return opciones.find(o => String(o.value) === String(seleccionado))?.attrs[k];}};},
    };
    const $ = target => {
        if (target === doc) return {on(evento, selector, fn) {eventos[evento] = fn;}};
        if (target === '.modal:visible .bono-convenio-veterinario') return select;
        return target;
    };
    $.getJSON = () => {
        const callbacks = {};
        const req = {done(fn) {callbacks.done = fn; return req;}, fail(fn) {callbacks.fail = fn; return req;}, always(fn) {callbacks.always = fn; return req;}};
        solicitudes.push({ok(data) {callbacks.done(data); callbacks.always();}, error() {callbacks.fail(); callbacks.always();}});
        return req;
    };
    const win = {jQuery: $, swal: mensaje => avisos.push(mensaje)};
    vm.runInNewContext(fs.readFileSync('public/js/convenios-veterinarios.js', 'utf8'), {
        document: doc, window: win, Option: class {constructor(text, value) {this.text = text; this.value = value; this.attrs = {};} setAttribute(k, v) {this.attrs[k] = v;}},
    });
    return {abrir() {eventos['shown.bs.modal'].call(modal);}, setHora(v) {hora = v;}, solicitudes, opciones, estado, win, avisos, saldo: () => saldo, resumen: () => resumen};
}

test('carga convenios, conserva porcentaje acordado y calcula el saldo después del descuento', () => {
    const p = pantalla(); p.abrir();
    assert.equal(p.win.conveniosVeterinariosListos(), false);
    p.solicitudes[0].ok({convenios: [{id: 7, nombre: 'Convenio', porcentaje: 50}], seleccionado: 7, aplicado: {id: 7, nombre: 'Convenio', porcentaje: 12.5}});
    assert.equal(p.win.conveniosVeterinariosListos(), true);
    assert.equal(p.saldo(), 7754);
    assert.match(p.resumen(), /Descuento/);
});

test('una respuesta atrasada no reemplaza convenios de otra hora', () => {
    const p = pantalla(); p.abrir(); p.setHora('2'); p.abrir();
    p.solicitudes[0].ok({convenios: [{id: 1, nombre: 'Ajeno', porcentaje: 10}]});
    assert.equal(p.estado.cargando, true);
    assert.equal(p.opciones.length, 1);
    p.solicitudes[1].ok({convenios: [{id: 2, nombre: 'Correcto', porcentaje: 10}]});
    assert.deepEqual(p.opciones.map(o => o.text), ['Sin convenio', 'Correcto (10%)']);
});

test('el fallo de la consulta impide confirmar silenciosamente sin el convenio aplicado', () => {
    const p = pantalla(); p.abrir(); p.solicitudes[0].error();
    assert.equal(p.win.conveniosVeterinariosListos(), false);
    assert.match(p.avisos.at(-1).text, /reintentar/);
    p.abrir(); p.solicitudes[1].ok({convenios: []});
    assert.equal(p.win.conveniosVeterinariosListos(), true);
});
