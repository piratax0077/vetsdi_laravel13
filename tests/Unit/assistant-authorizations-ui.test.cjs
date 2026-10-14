const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

function apply(permisos, controls) {
    const script = fs.readFileSync(path.join(__dirname, '../../public/js/asistente-autorizaciones.js'), 'utf8');
    controls.forEach(control => Object.assign(control, {
        dataset: {}, hidden: false, disabled: false,
        getAttribute: () => control.onclick || '', closest: () => null,
    }));
    vm.runInNewContext(script, {
        window: {asistenteAutorizaciones: permisos, asistenteRutasAutorizadas: {'https://local.test/reservar': 'permiso_reservar_hora'}, location: {href: 'https://local.test/'}},
        document: {querySelectorAll: () => controls, body: {}}, URL,
        MutationObserver: class {observe() {}},
    });
}

test('a read-only assistant cannot see confirmation and cancellation controls', () => {
    const controls = [{onclick: 'confirmar_hora()'}, {onclick: 'opcion_cancelar_hora()'}];
    apply({permiso_ver_agenda: true}, controls);
    assert.ok(controls.every(control => control.hidden && control.disabled));
});

test('an explicitly granted action remains available', () => {
    const controls = [{onclick: 'confirmar_hora()'}];
    apply({permiso_confirmar_hora: true}, controls);
    assert.equal(controls[0].hidden, false);
});

test('direct module links follow the same permissions as the server', () => {
    const controls = [{href: 'https://local.test/reservar?fecha=2026-10-13'}];
    apply({}, controls);
    assert.equal(controls[0].hidden, true);
});
