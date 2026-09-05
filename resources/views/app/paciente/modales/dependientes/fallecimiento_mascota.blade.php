<style>
    #modal_fallecimiento_mascota .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 22px 55px rgba(25, 43, 65, .28);
    }
    #modal_fallecimiento_mascota .modal-header {
        background: linear-gradient(135deg, #5c6370, #868e96);
        color: #fff;
        border: 0;
    }
    #modal_fallecimiento_mascota .memorial-section {
        border: 1px solid #e3e7ef;
        border-radius: 10px;
        margin-bottom: 14px;
        overflow: hidden;
        background: #fff;
    }
    #modal_fallecimiento_mascota .memorial-section-title {
        padding: 10px 14px;
        background: #f4f5f7;
        color: #495057;
        font-weight: 700;
        font-size: 13px;
    }
    #modal_fallecimiento_mascota .memorial-section-body {
        padding: 14px;
    }
    #modal_fallecimiento_mascota .dropzone {
        min-height: 120px;
        border: 2px dashed #ced4da;
        border-radius: 10px;
        background: #fafbfc;
    }
</style>

<div class="modal fade" id="modal_fallecimiento_mascota" tabindex="-1" role="dialog"
    aria-labelledby="modalFallecimientoMascotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="modalFallecimientoMascotaLabel">
                        <i class="feather icon-cloud"></i> Registrar fallecimiento
                    </h5>
                    <small id="fallecimiento_mascota_subtitulo">Cree un espacio de recuerdo para su mascota</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fallecimiento_mascota_id" value="">

                <div class="alert alert-light border mb-3">
                    <strong id="fallecimiento_mascota_nombre">—</strong>
                    <span class="d-block text-muted small mt-1">
                        Al confirmar, la mascota pasará a la sección <em>En memoria</em> y podrá ver su álbum de recuerdos.
                    </span>
                </div>

                <div class="memorial-section">
                    <div class="memorial-section-title">Datos del fallecimiento</div>
                    <div class="memorial-section-body">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm"><span class="text-danger">*</span> Fecha de fallecimiento</label>
                                    <input type="date" class="form-control form-control-sm" id="fallecimiento_fecha" max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Causa (opcional)</label>
                                    <input type="text" class="form-control form-control-sm" id="fallecimiento_causa" maxlength="500" placeholder="Ej. enfermedad, edad avanzada...">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="floating-label-activo-sm">Mensaje de despedida (opcional)</label>
                                    <textarea class="form-control form-control-sm" id="fallecimiento_mensaje" rows="3" maxlength="2000" placeholder="Escriba unas palabras en su memoria..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="memorial-section">
                    <div class="memorial-section-title">Álbum de fotos</div>
                    <div class="memorial-section-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="fallecimiento_incluir_fotos_actuales" checked>
                            <label class="form-check-label" for="fallecimiento_incluir_fotos_actuales">
                                Incluir las fotos ya guardadas en la ficha de la mascota
                            </label>
                        </div>
                        <p class="small text-muted mb-2">Puede agregar más fotos al álbum memorial:</p>
                        <input type="hidden" id="fallecimiento_album_json" value="[]">
                        <div class="dropzone" id="dropzone-memorial-mascota"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark btn-sm" id="btn_confirmar_fallecimiento" onclick="return confirmarFallecimientoMascota();">
                    <i class="feather icon-check"></i> Confirmar fallecimiento
                </button>
            </div>
        </div>
    </div>
</div>


