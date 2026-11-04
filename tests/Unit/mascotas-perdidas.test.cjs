// Ejecutar después de MascotasPerdidasTest: utiliza el HTML renderizado por Blade.
const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const {webcrypto} = require('node:crypto');

function pantalla() {
    const html = fs.readFileSync('tmp/tests/mascotas-perdidas.html', 'utf8');
    const valores = {
        '#perdida_mascota_id': '1', '#perdida_region': '1', '#perdida_comuna': '1',
        '#perdida_direccion option:selected': 'Plaza Uno', '#perdida_fecha': '2026-11-03',
        '#perdida_latitud': '-33.4', '#perdida_longitud': '-70.6', '#perdida_descripcion': 'Collar rojo',
    };
    const eventos = new Map(), propiedades = {}, modales = [], avisos = [], peticiones = [], listos = [];
    const tarjetas = [
        {estado: 'perdida', especie: 'Perro', region: 1, comuna: 1},
        {estado: 'perdida', especie: 'Gato', region: 2, comuna: 2},
        {estado: 'encontrada', especie: 'Perro', region: 1, comuna: 3},
    ];
    const $ = (selector) => {
        if (typeof selector === 'function') { listos.push(selector); return; }
        const obj = {
            on(evento, fn) { eventos.set(String(selector) + ':' + evento, fn); return obj; },
            val(v) { if (v === undefined) return valores[selector] || ''; valores[selector] = v; return obj; },
            text(v) { return v === undefined ? valores[selector] || '' : obj; },
            prop(k, v) { const key = selector + k; if (v === undefined) return propiedades[key] || false; propiedades[key] = v; return obj; },
            is() { return false; },
            data(k) { return typeof selector === 'object' ? selector[k] : undefined; },
            toggle(v) { if (typeof selector === 'object') selector.visible = v; return obj; },
            each(fn) { if (selector === '#perdidas_grilla .aviso-perdida') tarjetas.forEach(t => fn.call(t)); return obj; },
            modal(v) { modales.push(v); return obj; },
            find() { return obj; }, empty() { return obj; },
        };
        return obj;
    };
    $.trim = v => String(v || '').trim();
    $.ajax = config => {
        const cb = {};
        const resultado = {done(fn) { cb.done = fn; return resultado; }, fail(fn) { cb.fail = fn; return resultado; }, always(fn) { cb.always = fn; return resultado; }};
        peticiones.push({config, cb}); return resultado;
    };
    const contexto = { $, document: {}, Dropzone: {ERROR: 'error'}, CSRF_TOKEN: 'csrf', crypto: webcrypto,
        window: {location: {}}, location: {reload() {}}, console,
        swal(d) { avisos.push(d); return {then() {}}; },
    };
    vm.createContext(contexto);
    for (const script of html.matchAll(/<script[^>]*>([\s\S]*?)<\/script>/g)) vm.runInContext(script[1], contexto);
    listos.forEach(fn => fn());
    return {contexto, valores, eventos, tarjetas, modales, avisos, peticiones, propiedades};
}

test('filtros automáticos combinan región, comuna por id, especie y estado', () => {
    const p = pantalla();
    p.valores['#filtro_region'] = '1'; p.valores['#filtro_comuna'] = '1';
    p.eventos.get('#filtro_estado, #filtro_especie, #filtro_comuna:change')();
    assert.deepEqual(p.tarjetas.map(t => t.visible), [true, false, false]);
    p.valores['#filtro_comuna'] = ''; p.valores['#filtro_estado'] = 'encontrada';
    p.eventos.get('#filtro_estado, #filtro_especie, #filtro_comuna:change')();
    assert.deepEqual(p.tarjetas.map(t => t.visible), [false, false, true]);
});

test('publicar envía datos reales y bloquea doble clic; fallo conserva formulario', () => {
    const p = pantalla();
    p.contexto.tokenPublicacionPerdida = p.contexto.tokenMascotaPerdida();
    p.contexto.publicarMascotaPerdida(); p.contexto.publicarMascotaPerdida();
    assert.equal(p.peticiones.length, 1);
    const req = p.peticiones[0];
    const datos = JSON.parse(req.config.data);
    assert.equal(datos.mascota_id, '1'); assert.equal(datos.comuna_id, '1');
    assert.equal(datos.direccion, 'Plaza Uno'); assert.match(datos.token, /^[0-9a-f-]{36}$/);
    assert.deepEqual(p.modales, []);
    req.cb.fail({responseJSON: {errors: {direccion: ['Corrige la dirección']}}}); req.cb.always();
    assert.deepEqual(p.modales, []); assert.equal(p.valores['#perdida_descripcion'], 'Collar rojo');
    assert.equal(p.avisos.at(-1).text, 'Corrige la dirección');
    p.contexto.publicarMascotaPerdida();
    assert.equal(JSON.parse(p.peticiones[1].config.data).token, datos.token);
    p.peticiones[1].cb.done({mensaje: 'Publicado'});
    assert.deepEqual(p.modales, ['hide']);
});

test('encontrada publica sin id de mascota y los datos faltantes no envían', () => {
    const p = pantalla(); p.contexto.estadoPublicacionPerdida = 'encontrada';
    p.valores['#perdida_mascota_id'] = ''; p.contexto.publicarMascotaPerdida();
    assert.equal(JSON.parse(p.peticiones[0].config.data).estado, 'encontrada');
    const q = pantalla(); q.valores['#perdida_comuna'] = ''; q.contexto.publicarMascotaPerdida();
    assert.equal(q.peticiones.length, 0); assert.equal(q.avisos.at(-1).title, 'Faltan datos');
});
