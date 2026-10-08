<div class="modal fade" id="modal_fallecimiento_mascota" tabindex="-1" role="dialog"
    aria-labelledby="modalFallecimientoMascotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title mb-1" id="modalFallecimientoMascotaLabel">Registrar fallecimiento</h5>
                    <small id="fallecimiento_mascota_subtitulo">Cree un espacio de recuerdo para su mascota</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fallecimiento_mascota_id" value="">

                <div class="fallecimiento-mascota-resumen">
                    <span class="fallecimiento-mascota-foto">
                        <img id="fallecimiento_mascota_foto" src="{{ asset('images/iconos/mascotas.svg') }}" alt="Foto de la mascota">
                    </span>
                    <div>
                        <strong class="fallecimiento-mascota-nombre" id="fallecimiento_mascota_nombre">—</strong>
                        <span class="fallecimiento-mascota-aviso">
                            Al confirmar, pasará a la sección <em>En memoria</em> y podrá ver su álbum de recuerdos.
                        </span>
                    </div>
                </div>

                <div class="titulo-item">Datos del fallecimiento</div>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="fallecimiento_fecha"><span class="text-danger">*</span> Fecha de fallecimiento</label>
                            <input type="date" class="form-control form-control-sm" id="fallecimiento_fecha" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="fallecimiento_causa">Causa (opcional)</label>
                            <input type="text" class="form-control form-control-sm" id="fallecimiento_causa" maxlength="500" placeholder="Ej. enfermedad, edad avanzada...">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group mb-0">
                            <label class="floating-label-activo-sm" for="fallecimiento_mensaje">Mensaje de despedida (opcional)</label>
                            <textarea class="form-control form-control-sm" id="fallecimiento_mensaje" rows="3" maxlength="2000" placeholder="Escriba unas palabras en su memoria..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="titulo-item">Álbum de fotos</div>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-purple" id="btn_confirmar_fallecimiento" onclick="return confirmarFallecimientoMascota();">
                    <i class="feather icon-check"></i> Confirmar fallecimiento
                </button>
            </div>
        </div>
    </div>
</div>
