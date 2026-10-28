@php
    $animalEutanasia = $mascota ?? null;
    $plantillaEutanasia = \App\Models\ConConsentimientos::where('estado', 1)->where('nombre', 'like', '%eutanasia%')->orderBy('id')->first();
    $textoEutanasia = $plantillaEutanasia?->texto ?? view('general.modal.partials.texto_eutanasia')->render();
    $textoEutanasia = strtr($textoEutanasia, [
        '{nombre_mascota}' => e($animalEutanasia?->nombre ?? 'Sin registro'),
        '{nombre_dependiente}' => e($animalEutanasia?->nombre ?? 'Sin registro'),
        '{nombre_raza}' => e($animalEutanasia?->razaMascota?->nombre ?? 'Sin registro'),
        '{nombre_especie}' => e($animalEutanasia?->especieMascota?->nombre ?? 'Sin registro'),
        '{numero_chip}' => e($animalEutanasia?->chip ?: 'Sin registro'),
        '{diagnostico}' => '<span class="eutanasia-diagnostico"></span>',
        '{cirugia}' => '<span class="eutanasia-motivo"></span>',
    ]);
@endphp
<div id="m_aconsenteutanasia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="eutanasia-titulo" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document"><div class="modal-content">
    <div class="modal-header modal-header-purple">
        <h5 id="eutanasia-titulo" class="modal-title">Consentimiento informado para eutanasia</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="modal-body px-4">
        <div class="alert alert-primary">Complete el diagnóstico y el motivo. El documento quedará disponible en Documentos del tutor para su firma.</div>
        <p><strong>Veterinario:</strong> {{ $profesional->nombre }} {{ $profesional->apellido_uno }} {{ $profesional->apellido_dos }}<br>
            <strong>Mascota:</strong> {{ $animalEutanasia?->nombre ?? 'Sin registro' }}</p>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="floating-label-activo-sm" for="diagnostico_eutanasia">Diagnóstico</label>
                <input type="text" class="form-control form-control-sm" id="diagnostico_eutanasia" oninput="validarDiagnosticoEutanasia()">
            </div>
            <div class="form-group col-md-6">
                <label class="floating-label-activo-sm" for="eutanasia_motivo">Motivo del procedimiento</label>
                <input type="text" class="form-control form-control-sm" id="eutanasia_motivo" oninput="validarDiagnosticoEutanasia()">
            </div>
        </div>
        <div class="alert alert-secondary">{!! $textoEutanasia !!}</div>
        <div class="form-group">
            <label for="eutanasia_observaciones">Situaciones especiales de la mascota</label>
            <textarea id="eutanasia_observaciones" class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=3" onblur="this.rows=1"></textarea>
        </div>
        <div id="eutanasia_estado" role="status" aria-live="polite"></div>
        <a id="eutanasia_pdf" class="btn btn-danger-light btn-sm" target="_blank" rel="noopener" hidden>Ver PDF</a>
        <button id="eutanasia_nuevo" type="button" class="btn btn-info btn-sm" onclick="nuevoConsentimientoEutanasia()" hidden>Crear nuevo consentimiento</button>
        <h6 class="mt-3">Consentimientos de eutanasia de esta atención</h6>
        <div class="table-responsive"><table class="table table-striped table-sm" id="m_aconsenteutanasia_table">
            <thead><tr><th>Fecha</th><th>Diagnóstico</th><th>Motivo</th><th>Estado</th><th>PDF</th></tr></thead><tbody></tbody>
        </table></div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
        <button type="button" id="eutanasia_guardar" name="consentimiento" class="btn btn-success btn-sm" onclick="guardarConsentimientoEutanasia()">Generar y solicitar firma al tutor</button>
    </div>
</div></div></div>
<script>
    var eutanasiaEnProceso = false;
    var eutanasiaGenerada = false;
    function validarDiagnosticoEutanasia() {
        var diagnostico = String($('#diagnostico_eutanasia').val() || '').trim();
        var motivo = String($('#eutanasia_motivo').val() || '').trim();
        $('#m_aconsenteutanasia .eutanasia-diagnostico').text(diagnostico);
        $('#m_aconsenteutanasia .eutanasia-motivo').text(motivo);
        $('#m_aconsenteutanasia [name="consentimiento"]').prop('disabled', eutanasiaEnProceso || eutanasiaGenerada || !diagnostico || !motivo);
    }
    function sincronizarDiagnosticoEutanasia() {
        if (!eutanasiaGenerada) $('#diagnostico_eutanasia').val($('#descripcion_hipotesis').val() || '');
        validarDiagnosticoEutanasia();
    }
    function cons_eutan() {
        sincronizarDiagnosticoEutanasia();
        $('#m_aconsenteutanasia').modal('show');
        cargarHistorialEutanasia();
    }
    function nuevoConsentimientoEutanasia() {
        if (eutanasiaEnProceso) return;
        eutanasiaGenerada = false;
        $('#eutanasia_motivo, #eutanasia_observaciones').val('');
        $('#eutanasia_estado').text('');
        $('#eutanasia_pdf, #eutanasia_nuevo').prop('hidden', true);
        $('#diagnostico_eutanasia, #eutanasia_motivo, #eutanasia_observaciones').prop('disabled', false);
        sincronizarDiagnosticoEutanasia();
    }
    function guardarConsentimientoEutanasia() {
        if (eutanasiaEnProceso || eutanasiaGenerada) return;
        var diagnostico_cons = $('#diagnostico_eutanasia').val();
        var motivo = $('#eutanasia_motivo').val();
        if (!String(diagnostico_cons || '').trim() || !String(motivo || '').trim() || !$('#id_fc').val()) {
            $('#eutanasia_estado').text('Complete diagnóstico y motivo, y abra una ficha de atención válida.');
            return;
        }
        eutanasiaEnProceso = true;
        validarDiagnosticoEutanasia();
        $('#eutanasia_estado').text('Generando consentimiento…');
        $.ajax({
            url: "{{ route('consentimiento.registrar.autorizacion') }}", type: 'POST', dataType: 'json',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
                tipo_consentimiento: 'eutanasia', id_ficha_atencion: $('#id_fc').val(),
                diagnostico_cons: diagnostico_cons, cirugia_cons: motivo,
                observaciones_con: $('#eutanasia_observaciones').val(), num_consentimiento: 0
            },
            success: function(data) {
                if (data.estado != 1) { $('#eutanasia_estado').text(data.msj || 'No fue posible generar el consentimiento.'); return; }
                eutanasiaGenerada = true;
                $('#eutanasia_estado').text(data.msj || 'Disponible en Documentos del tutor; pendiente de firma.');
                $('#eutanasia_pdf').attr('href', urlPdfEutanasia(data.last_id)).prop('hidden', false);
                $('#eutanasia_nuevo').prop('hidden', false);
                $('#diagnostico_eutanasia, #eutanasia_motivo, #eutanasia_observaciones').prop('disabled', true);
                cargarHistorialEutanasia();
            },
            error: function(xhr) {
                var respuesta = xhr.responseJSON || {};
                var errores = respuesta.errors ? Object.values(respuesta.errors).flat().join(' ') : '';
                $('#eutanasia_estado').text(errores || respuesta.message || 'No fue posible generar el consentimiento. Inténtelo nuevamente.');
            },
            complete: function() { eutanasiaEnProceso = false; validarDiagnosticoEutanasia(); }
        });
    }
    function urlPdfEutanasia(id) {
        return '{{ route("consentimiento.pdf") }}?id_consentimiento=' + encodeURIComponent(id) + '&id_ficha_atencion=' + encodeURIComponent($('#id_fc').val());
    }
    function cargarHistorialEutanasia() {
        $.ajax({
            url: '{{ route("consentimiento.paciente.ver") }}', type: 'GET', dataType: 'json',
            data: {id_paciente: $('#id_paciente_fc').val(), id_profesional: $('#id_profesional_fc').val(), id_ficha_atencion: $('#id_fc').val(), id_lugar_atencion: $('#id_lugar_atencion').val()},
            success: function(data) {
                var tbody = $('#m_aconsenteutanasia_table tbody').empty();
                (data.registros || []).filter(function(item) {
                    return /eutanasia/i.test((item.consentimiento || {}).nombre || '');
                }).forEach(function(item) {
                    var fila = $('<tr>');
                    [item.fecha_cons, item.diagnostico_cons, item.cirugia_cons, item.revocacion == 1 ? 'Revocado' : (item.confirmacion == 1 ? 'Firmado' : 'Pendiente de firma')].forEach(function(valor) {
                        $('<td>').text(valor || '-').appendTo(fila);
                    });
                    $('<td>').append($('<a>').attr({href: urlPdfEutanasia(item.id), target: '_blank', rel: 'noopener'}).text('Ver PDF')).appendTo(fila);
                    fila.appendTo(tbody);
                });
                if (!tbody.children().length) $('<tr>').append($('<td>').attr('colspan', 5).text('Sin consentimientos de eutanasia registrados.')).appendTo(tbody);
            },
            error: function() {
                $('#m_aconsenteutanasia_table tbody').empty().append($('<tr>').append($('<td>').attr('colspan', 5).text('No se pudo cargar el historial. Vuelva a abrir el formulario.')));
            }
        });
    }
</script>
