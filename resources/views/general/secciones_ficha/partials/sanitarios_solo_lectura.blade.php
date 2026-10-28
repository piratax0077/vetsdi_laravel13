@once
@if (!auth()->user()?->hasRole('Profesional'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    var scopes = '#form_vacuna_modal, #form_desparasitacion_modal, #modal_vacunacion_desparasitacion, #form_ges';
    document.querySelectorAll(scopes).forEach(function (scope) {
        scope.querySelectorAll('input, select, textarea, button[type="submit"], button[id*="guardar"], button[id^="btn_add_"], button[onclick*="guardar"]').forEach(function (control) { control.disabled = true; });
        var note = document.createElement('p');
        note.className = 'alert alert-info';
        note.textContent = 'Registros sanitarios de solo lectura. Las modificaciones corresponden al profesional autorizado.';
        (scope.querySelector('.modal-body') || scope).prepend(note);
    });
    ['vacunas', 'enfermedad_cronica', 'cirugias', 'ultima_desparasitacion', 'producto_desparasitacion', 'esterilizado', 'fecha_esterilizacion', 'fecha_esterilizacion_desconocida'].forEach(function (name) {
        document.querySelectorAll('[name="'+name+'"]').forEach(function (control) { control.disabled = true; });
    });
});
</script>
@endif
@endonce
