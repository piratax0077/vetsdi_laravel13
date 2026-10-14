(function () {
    const permisos = window.asistenteAutorizaciones || {};
    const rutas = window.asistenteRutasAutorizadas || {};
    const funciones = {
        'opcion_cancelar_hora|cancelar_hora': 'permiso_anular_hora',
        'opcion_confirmar_hora|confirmar_hora': 'permiso_confirmar_hora',
        'abrir_horas_extras': 'permiso_agendar_horas_extras',
        'abrir_horas_examen': 'permiso_agendar_examenes',
        'agendar_hora|agendar_hora_paciente_nuevo_prereserva': 'permiso_reservar_hora',
    };
    function aplicar() {
        document.querySelectorAll('a[href], [onclick], [data-permiso-asistente]').forEach(control => {
            let permiso = control.dataset.permisoAsistente;
            if (control.href) {
                const url = new URL(control.href, window.location.href);
                permiso ||= rutas[url.origin + url.pathname];
            }
            if (!permiso && control.getAttribute('onclick')) {
                for (const [nombres, clave] of Object.entries(funciones)) {
                    if (new RegExp('\\b(' + nombres + ')\\s*\\(').test(control.getAttribute('onclick'))) {
                        permiso = clave;
                        break;
                    }
                }
            }
            if (permiso && !permisos[permiso]) {
                const objetivo = control.closest('.card.subir') || control;
                objetivo.hidden = true;
                if ('disabled' in control) control.disabled = true;
            }
        });
    }
    aplicar();
    new MutationObserver(aplicar).observe(document.body, {childList: true, subtree: true});
})();
