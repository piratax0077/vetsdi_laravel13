{{-- Modal de configuración de aranceles: lugares, tamaños y parámetros (public/js/aranceles.js) --}}
<div id="arvModalConfig" class="modal fade arv-modal" tabindex="-1" role="dialog" aria-labelledby="arvModalConfigTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title mt-1" id="arvModalConfigTitulo">Configuración de aranceles</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>

            <div class="modal-body">
                <h6 class="arv-seccion">Lugares de atención y recargo</h6>
                <div class="table-responsive">
                    <table class="table arv-tabla-config mb-2" id="arvCfgLugares">
                        <thead><tr><th>Lugar</th><th style="width:160px">Recargo</th><th style="width:50px"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="button" id="arvCfgAgregarLugar" class="btn btn-outline-info btn-sm"><i class="feather icon-plus"></i> Agregar lugar</button>
                <small class="arv-ayuda">Deja el recargo vacío si aún no lo defines: se calculará como 0%.</small>

                <h6 class="arv-seccion">Tamaños y rangos de peso</h6>
                <div class="table-responsive">
                    <table class="table arv-tabla-config mb-2" id="arvCfgTamanos">
                        <thead><tr><th>Tamaño</th><th style="width:130px">Desde (kg)</th><th style="width:130px">Hasta (kg)</th><th style="width:50px"></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="button" id="arvCfgAgregarTamano" class="btn btn-outline-info btn-sm"><i class="feather icon-plus"></i> Agregar tamaño</button>
                <small class="arv-ayuda">Deja "Desde" vacío para el tamaño más pequeño y "Hasta" vacío para el más grande.</small>

                <h6 class="arv-seccion">Parámetros</h6>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="arvCfgIva">IVA</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="arvCfgIva" class="form-control form-control-sm" min="0" max="100" step="0.1">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="arvCfgRetencion">Retención boleta de honorarios</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="arvCfgRetencion" class="form-control form-control-sm" min="0" max="100" step="0.01">
                                <div class="input-group-append"><span class="input-group-text">%</span></div>
                            </div>
                            <small class="arv-ayuda">Cambia cada año. Es informativa: no se suma al precio.</small>
                        </div>
                    </div>
                </div>
                <small class="arv-error" id="arvCfgError"></small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                <button type="button" id="arvCfgGuardar" class="btn btn-info"><i class="feather icon-save"></i> Guardar configuración</button>
            </div>
        </div>
    </div>
</div>
