{{-- Notas internas del tutor: contador, formulario y registro de notas.
     Se usa igual en la pestaña de la pantalla del tutor y en el modal de Mascotas y tutores.
     Con $en_modal = true el título y la descripción van en el encabezado del modal, no aquí --}}
@php
    // Cuántas notas lo marcaron como conflictivo; queda en 0 mientras las notas no se guarden
    $vecesConflictivo = 0;
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
    @if (empty($en_modal))
        <div class="mr-2">
            <h6 class="ficha-profesional__titulo"><i class="fas fa-sticky-note mr-1" aria-hidden="true"></i> Notas internas del tutor</h6>
            <p class="text-muted mb-0">Solo las ve el equipo de la peluquería; el tutor no las ve.</p>
        </div>
    @endif
    <span class="badge badge-light-danger f-12 px-2 py-1 mt-1 ml-auto" title="Notas que lo marcaron como conflictivo">
        <i class="feather icon-alert-triangle mr-1" aria-hidden="true"></i><strong>{{ $vecesConflictivo }}</strong> {{ $vecesConflictivo === 1 ? 'vez conflictivo' : 'veces conflictivo' }}
    </span>
</div>
{{-- Aviso mientras las notas no tengan dónde guardarse --}}
<div class="alert alert-info d-none" id="aviso_nota_tutor" role="status"></div>
<form id="form_nota_tutor" class="d-print-none" novalidate>
    <div class="form-group">
        <label class="floating-label-activo-sm" for="nota_tutor_texto">Nueva nota</label>
        <textarea class="form-control form-control-sm" id="nota_tutor_texto" rows="3" maxlength="2000" placeholder="Ej.: Prefiere que lo llamen en la tarde" required></textarea>
        <div class="invalid-feedback">Escribe la nota.</div>
    </div>
    <div class="bloque-estado mb-3">
        <span class="d-block mb-2 font-weight-bold text-dark" id="pregunta_tutor_conflictivo">¿Tutor conflictivo?</span>
        <div class="custom-control custom-switch" role="group" aria-labelledby="pregunta_tutor_conflictivo">
            <input type="checkbox" class="custom-control-input" id="nota_tutor_conflictivo" name="conflictivo" value="1">
            <label class="custom-control-label" for="nota_tutor_conflictivo">No</label>
        </div>
    </div>
    <div class="text-right">
        <button type="submit" class="btn btn-info"><i class="feather icon-plus mr-1"></i>Agregar nota</button>
    </div>
</form>
<div class="ficha-profesional__vacio mt-3">Todavía no hay notas para este tutor.</div>

<script>
    // Sin jQuery a propósito: este bloque se carga antes que los scripts de la plantilla
    (function () {
        var formulario = document.getElementById('form_nota_tutor');
        var conflictivo = document.getElementById('nota_tutor_conflictivo');
        var aviso = document.getElementById('aviso_nota_tutor');
        var texto_switch = formulario.querySelector('label[for="nota_tutor_conflictivo"]');

        // El texto del interruptor dice si la nota lo marca como conflictivo
        conflictivo.addEventListener('change', function () {
            texto_switch.textContent = conflictivo.checked ? 'Sí' : 'No';
        });

        // Todavía no hay dónde guardar las notas: se revisa lo escrito y se avisa
        formulario.addEventListener('submit', function (evento) {
            var nota = document.getElementById('nota_tutor_texto');
            evento.preventDefault();
            nota.value = nota.value.trim();
            if (!formulario.checkValidity()) {
                formulario.classList.add('was-validated');
                return;
            }
            aviso.textContent = 'El guardado de notas internas aún no está disponible.';
            aviso.classList.remove('d-none');
        });

        // Deja el formulario como recién abierto (lo usa el modal cada vez que se abre)
        window.reiniciar_notas_tutor = function () {
            formulario.reset();
            formulario.classList.remove('was-validated');
            texto_switch.textContent = 'No';
            aviso.classList.add('d-none');
            aviso.textContent = '';
        };
    })();
</script>
