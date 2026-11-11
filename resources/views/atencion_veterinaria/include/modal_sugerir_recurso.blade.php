{{-- Sidebar "Ayúdenos a mejorar": el profesional sugiere al equipo Veterchile un recurso que le falta
     (consentimiento, formulario, procedimiento u otro). Reúne lo que antes eran varios modales separados. --}}
@php
    $especialidadSugerencia = isset($profesional) ? (optional($profesional->Especialidad)->nombre ?? '') : '';
@endphp
<div id="modal_sugerir_recurso" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_sugerir_recurso_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-celeste">
                <h5 class="modal-title" id="modal_sugerir_recurso_titulo"><i class="feather icon-message-square mr-1"></i> Sugerir recurso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="form_sugerir_recurso" novalidate onsubmit="enviarSugerenciaRecurso(); return false;">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="sugerencia_tipo">Tipo de sugerencia</label>
                            <select class="form-control form-control-sm" id="sugerencia_tipo" onchange="alternarOtraSugerencia()">
                                <option value="">Seleccione</option>
                                <option value="consentimiento">Consentimiento informado</option>
                                <option value="formulario">Formulario</option>
                                <option value="procedimiento">Procedimiento</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="sugerencia_especialidad">Especialidad</label>
                            <input type="text" class="form-control form-control-sm" id="sugerencia_especialidad" value="{{ $especialidadSugerencia }}" readonly>
                        </div>
                        <div class="form-group col-md-12 d-none" id="sugerencia_otra_grupo">
                            <label class="floating-label-activo-sm" for="sugerencia_otra">Otra sugerencia</label>
                            <input type="text" class="form-control form-control-sm" id="sugerencia_otra" maxlength="150" placeholder="Escribe qué recurso necesitas">
                        </div>
                        <div class="form-group col-md-12">
                            <label class="floating-label-activo-sm" for="sugerencia_comentarios">Comentarios</label>
                            <textarea class="form-control form-control-sm sugerencia-comentarios" id="sugerencia_comentarios" rows="4" maxlength="1000" placeholder="Cuéntanos para qué lo necesitas y cómo te gustaría que funcionara"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x"></i> Cancelar</button>
                <button type="submit" form="form_sugerir_recurso" class="btn btn-info"><i class="feather icon-send"></i> Enviar solicitud</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        // El modal vive dentro del sidebar; se pasa al body para que se vea sobre toda la ficha
        $('#modal_sugerir_recurso').appendTo('body');
    });

    function abrirSugerirRecurso() {
        $('#sugerencia_tipo, #sugerencia_otra, #sugerencia_comentarios').val('');
        alternarOtraSugerencia();
        $('#modal_sugerir_recurso').modal('show');
    }

    // "Otro" abre un campo para escribir la sugerencia que no está en la lista
    function alternarOtraSugerencia() {
        var esOtro = $('#sugerencia_tipo').val() === 'otro';
        $('#sugerencia_otra_grupo').toggleClass('d-none', !esOtro);
        if (esOtro) $('#sugerencia_otra').trigger('focus');
    }

    function enviarSugerenciaRecurso() {
        var tipo = $('#sugerencia_tipo').val();
        var faltan = [];
        if (!tipo) faltan.push('Tipo de sugerencia');
        if (tipo === 'otro' && !$('#sugerencia_otra').val().trim()) faltan.push('Otra sugerencia');
        if (!$('#sugerencia_comentarios').val().trim()) faltan.push('Comentarios');
        if (faltan.length) {
            swal({ title: 'Faltan datos', text: faltan.join(', '), icon: 'error' });
            return;
        }
        $('#modal_sugerir_recurso').modal('hide');
        swal({ icon: 'success', title: 'Solicitud enviada', text: 'Solicitud enviada al equipo Veterchile.' });
    }
</script>
