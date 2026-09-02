@extends('template.laboratorio.laboratorio_asistente_subir_ex.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <h5 class="text-white mb-1"><i class="fas fa-flask mr-2"></i>Laboratorio veterinario</h5>
                <span class="text-white">Carga de resultados PDF para mascotas</span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header bg-info text-white"><strong>1. Ubicar mascota</strong></div>
                    <div class="card-body">
                        <label for="lab-vet-busqueda">RUT del tutor o microchip</label>
                        <div class="input-group">
                            <input id="lab-vet-busqueda" class="form-control" placeholder="Ej.: 6187674-k o número de chip">
                            <div class="input-group-append">
                                <button id="lab-vet-buscar" type="button" class="btn btn-info"><i class="feather icon-search"></i> Buscar</button>
                            </div>
                        </div>
                        <small class="text-muted">Si el RUT tiene más de una mascota, podrá elegirla en los resultados.</small>
                        <div id="lab-vet-resultados" class="mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header bg-info text-white"><strong>2. Subir resultados</strong></div>
                    <div class="card-body">
                        <div id="lab-vet-mascota-seleccionada" class="alert alert-light border">Primero seleccione una mascota.</div>
                        <label for="lab-vet-observacion">Observación del laboratorio</label>
                        <textarea id="lab-vet-observacion" class="form-control mb-3" rows="2" placeholder="Nombre del examen, orden u observación"></textarea>
                        <input id="lab-vet-archivos" type="file" accept="application/pdf,.pdf" multiple class="d-none">
                        <div id="lab-vet-dropzone" class="lab-vet-dropzone" tabindex="0" role="button" aria-controls="lab-vet-archivos">
                            <i class="fas fa-file-pdf"></i>
                            <strong>Arrastre los PDF aquí</strong>
                            <span>o haga clic para seleccionarlos (máximo 20 MB por archivo)</span>
                        </div>
                        <div id="lab-vet-cola" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.lab-vet-dropzone{min-height:210px;border:2px dashed #17b8b5;border-radius:14px;background:#f4ffff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:#168a88;cursor:pointer;text-align:center;padding:25px;transition:.2s}
.lab-vet-dropzone:hover,.lab-vet-dropzone.is-dragging{background:#e0fbfa;border-color:#087d7b;transform:translateY(-1px)}
.lab-vet-dropzone.is-disabled{opacity:.5;pointer-events:none}.lab-vet-dropzone i{font-size:54px;color:#dc3545}.lab-vet-mascota{border:1px solid #dce4eb;border-radius:10px;padding:12px;margin-bottom:8px;cursor:pointer}.lab-vet-mascota:hover,.lab-vet-mascota.active{border-color:#17b8b5;background:#efffff}.lab-vet-archivo{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eee;padding:8px 0;gap:12px}
</style>

<script>
$(function () {
    var mascotaId = null;
    var subidasActivas = 0;
    var $dropzone = $('#lab-vet-dropzone');

    function aviso(titulo, texto, icono) {
        if (window.swal) return swal({title: titulo, text: texto, icon: icono || 'info'});
        alert(texto);
    }

    $('#lab-vet-buscar').on('click', buscar);
    $('#lab-vet-busqueda').on('keydown', function (e) { if (e.key === 'Enter') buscar(); });

    function buscar() {
        var busqueda = $('#lab-vet-busqueda').val().trim();
        if (!busqueda) return aviso('Falta información', 'Ingrese el RUT del tutor o el microchip.', 'warning');
        $('#lab-vet-resultados').html('<div class="text-info"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>');
        $.get('{{ route('laboratorio.veterinario.buscar') }}', {busqueda: busqueda})
            .done(function (resp) {
                var html = '';
                (resp.mascotas || []).forEach(function (m) {
                    html += '<div class="lab-vet-mascota" data-id="'+m.id+'" data-nombre="'+$('<div>').text(m.nombre).html()+'">'+
                        '<strong><i class="fas fa-paw mr-1"></i>'+$('<div>').text(m.nombre).html()+'</strong><br>'+
                        '<small>'+ $('<div>').text(m.especie+' · Chip: '+m.chip).html() +'</small><br>'+
                        '<small>Tutor: '+$('<div>').text((m.tutor || '-')+' · '+(m.rut || '-')).html()+'</small></div>';
                });
                $('#lab-vet-resultados').html(html || '<div class="alert alert-warning">No se encontraron mascotas.</div>');
            }).fail(function () { $('#lab-vet-resultados').html('<div class="alert alert-danger">No fue posible realizar la búsqueda.</div>'); });
    }

    $(document).on('click', '.lab-vet-mascota', function () {
        $('.lab-vet-mascota').removeClass('active'); $(this).addClass('active');
        mascotaId = $(this).data('id');
        $('#lab-vet-mascota-seleccionada').removeClass('alert-light').addClass('alert-success')
            .html('<strong>Mascota seleccionada:</strong> '+$(this).data('nombre'));
        $dropzone.removeClass('is-disabled');
    });

    $dropzone.on('click', function () { document.getElementById('lab-vet-archivos').click(); });
    $dropzone.on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            document.getElementById('lab-vet-archivos').click();
        }
    });
    $dropzone.on('dragover dragenter', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (e.originalEvent && e.originalEvent.dataTransfer) e.originalEvent.dataTransfer.dropEffect = 'copy';
        $dropzone.addClass('is-dragging');
    });
    $dropzone.on('dragleave', function (e) { e.preventDefault(); e.stopPropagation(); $dropzone.removeClass('is-dragging'); });
    $dropzone.on('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $dropzone.removeClass('is-dragging');
        var transferencia = e.originalEvent && e.originalEvent.dataTransfer;
        if (transferencia && transferencia.files) subir(transferencia.files);
    });
    $(document).on('dragover drop', function (e) { e.preventDefault(); });
    $('#lab-vet-archivos').on('change', function () { subir(this.files); this.value = ''; });

    function subir(archivos) {
        if (!mascotaId) return aviso('Seleccione una mascota', 'Debe buscar y seleccionar la mascota antes de subir documentos.', 'warning');
        if (!archivos || !archivos.length) return;
        Array.from(archivos).forEach(function (archivo) {
            if (archivo.type !== 'application/pdf' && !archivo.name.toLowerCase().endsWith('.pdf')) return aviso('Archivo no permitido', archivo.name+' no es PDF.', 'warning');
            if (archivo.size > 20 * 1024 * 1024) return aviso('Archivo demasiado grande', archivo.name+' supera el máximo de 20 MB.', 'warning');
            var idFila = 'archivo-'+Date.now()+'-'+Math.random().toString(16).slice(2);
            $('#lab-vet-cola').prepend('<div id="'+idFila+'" class="lab-vet-archivo"><span><i class="fas fa-file-pdf text-danger mr-2"></i>'+$('<div>').text(archivo.name).html()+'</span><span class="text-info"><i class="fas fa-spinner fa-spin"></i> Subiendo</span></div>');
            var form = new FormData(); form.append('_token', '{{ csrf_token() }}'); form.append('id_mascota', mascotaId); form.append('observacion', $('#lab-vet-observacion').val()); form.append('archivo', archivo);
            subidasActivas++;
            $dropzone.addClass('is-disabled');
            $.ajax({url:'{{ route('laboratorio.veterinario.guardar') }}', method:'POST', data:form, processData:false, contentType:false, dataType:'json'})
                .done(function (resp) { $('#'+idFila+' span:last').removeClass('text-info').addClass('text-success').html('<i class="feather icon-check"></i> Guardado'); aviso('Documento recibido', resp.msj, 'success'); })
                .fail(function (xhr) {
                    var json = xhr.responseJSON || {};
                    var errores = json.errors ? Object.values(json.errors).flat().join(' ') : '';
                    var msg = errores || json.message || 'Error al subir PDF (HTTP '+xhr.status+').';
                    $('#'+idFila+' span:last').removeClass('text-info').addClass('text-danger').text(msg);
                    aviso('No fue posible subir el PDF', msg, 'error');
                })
                .always(function () {
                    subidasActivas = Math.max(0, subidasActivas - 1);
                    if (!subidasActivas) $dropzone.removeClass('is-disabled');
                });
        });
    }
});
</script>
@endsection
