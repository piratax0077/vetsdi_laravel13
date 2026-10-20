(function () {
    'use strict';
    // Only encounter diagnoses: never overwrite historical or postoperative findings.
    const campos = new Set([
        'diagnostico_cons', 'diagnostico_eutanasia', 'm_rech_tto_diagnostico',
        'modal_solalta_diagnostico', 'hipotesis_certificado', 'hipotesis_interconsulta',
        'inter_resp_diagnostico', 'diagnostico_especialidad', 'diagnostico_rx',
        'diagnostico_comunes', 'diagnostico_endoscopico', 'diagnostico_pre',
        'diag_endos_eda', 'sospecha_diagnostica_cisto', 'eno_diagnositico_confirmado',
        'diagnostico_causa_gastos_medicos', 'diagnostico_gastos_medicos',
        'diagnostico_causa_dental', 'diagnostico_dental'
    ]);

    function completar(modal) {
        const origen = Array.from(document.querySelectorAll('[id="descripcion_hipotesis"]'))
            .find(campo => !campo.closest('.modal'));
        if (!origen || !modal || !modal.querySelectorAll) return;
        modal.querySelectorAll('input, textarea').forEach(campo => {
            if (campo.closest('.modal') !== modal || campo.disabled || campo.readOnly) return;
            if (!campos.has(campo.id) && !campos.has(campo.name)) return;
            campo.value = origen.value;
            // Do not trigger legacy change handlers: they write back into the encounter.
        });
    }

    window.completarDiagnosticoFormularioVeterinario = completar;
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery) {
            window.jQuery(document).on('show.bs.modal.diagnosticoFicha shown.bs.modal.diagnosticoFicha', '.modal', function () {
                completar(this);
            });
        }
        document.addEventListener('show.bs.modal', event => completar(event.target));
        document.addEventListener('shown.bs.modal', event => completar(event.target));
    });
})();
