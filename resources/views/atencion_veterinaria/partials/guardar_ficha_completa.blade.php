@if(isset($mascota) && $mascota)
@once
<script src="{{ asset('js/diagnostico-formularios-veterinarios.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const diagnostico = document.getElementById('descripcion_hipotesis');
        const form = diagnostico && diagnostico.closest('form');
        if (!form) return;

        window.guardarFichaVeterinariaAjax = async function () {
            if (!diagnostico.value.trim()) {
                diagnostico.setCustomValidity('El diagnóstico es obligatorio.');
                diagnostico.reportValidity();
                return;
            }
            if (typeof agregar_medicamentos_ficha === 'function') agregar_medicamentos_ficha();
            if (typeof agregar_examenes_ficha === 'function') agregar_examenes_ficha();
            const datos = new FormData(form);
            datos.set('cerrarsession', '2');
            try {
                const respuesta = await fetch(form.action, {method: 'POST', body: datos, headers: {Accept: 'application/json'}});
                const resultado = await respuesta.json();
                if (!respuesta.ok) {
                    const errores = Object.values(resultado.errors || {}).flat().join('\n');
                    throw new Error(errores || resultado.mensaje || resultado.message || 'No se pudo guardar la ficha.');
                }
                swal({title: 'Ficha guardada', text: resultado.mensaje, icon: 'success'});
            } catch (error) {
                swal({title: 'No se pudo guardar', text: error.message, icon: 'error'});
            }
        };

        form.querySelectorAll('[required]').forEach(control => control.required = false);
        diagnostico.required = true;
        diagnostico.addEventListener('input', function () { this.setCustomValidity(''); });

        const guardados = @json($fichaVeterinariaGeneralData ?? []);
        const anteriores = @json(session()->getOldInput());
        Object.entries({...guardados, ...anteriores}).forEach(([nombre, valor]) => {
            const controles = Array.from(form.elements).filter(control => control.name === nombre);
            controles.forEach(control => {
                if (['hidden', 'file', 'submit', 'button'].includes(control.type)) return;
                if (control.type === 'checkbox' || control.type === 'radio') {
                    control.checked = control.type === 'radio'
                        ? String(control.value) === String(valor)
                        : ['1', 'true', 'on', 'yes'].includes(String(valor));
                } else if (valor === null || typeof valor !== 'object') {
                    control.value = valor ?? '';
                }
            });
        });

        // An unchecked checkbox must explicitly replace its previously saved value.
        form.querySelectorAll('input[type="checkbox"][name]').forEach(control => {
            const vacio = document.createElement('input');
            vacio.type = 'hidden';
            vacio.name = control.name;
            vacio.value = '0';
            control.before(vacio);
        });

        form.addEventListener('submit', function (event) {
            if (!diagnostico.value.trim()) {
                event.preventDefault();
                diagnostico.setCustomValidity('El diagnóstico es obligatorio.');
                const panel = diagnostico.closest('.collapse');
                if (panel) $(panel).collapse('show');
                diagnostico.reportValidity();
                return;
            }
            if (typeof window.odontologiaVetPrepararGuardado === 'function' && !form.dataset.odontologiaPreparada) {
                event.preventDefault();
                if (form.dataset.odontologiaGuardando) return;
                form.dataset.odontologiaGuardando = '1';
                window.odontologiaVetPrepararGuardado().then(function () {
                    form.dataset.odontologiaPreparada = '1';
                    form.requestSubmit(event.submitter);
                }).catch(function (error) {
                    const aviso = document.getElementById('vet-dental-error');
                    if (aviso) { aviso.textContent = error.message; aviso.hidden = false; }
                }).finally(function () { delete form.dataset.odontologiaGuardando; delete form.dataset.odontologiaPreparada; });
                return;
            }
            // Also serialize when submitting with Enter, without relying on the button's onclick.
            if (typeof agregar_medicamentos_ficha === 'function') agregar_medicamentos_ficha();
            if (typeof agregar_examenes_ficha === 'function') agregar_examenes_ficha();
        });
    });
</script>
@endonce
@endif
