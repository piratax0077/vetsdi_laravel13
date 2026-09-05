<style>
    #modal_traspasar_mascota .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 22px 55px rgba(25, 43, 65, .28);
    }
    #modal_traspasar_mascota .modal-header {
        background: linear-gradient(135deg, #117a8b, #17a2b8);
        color: #fff;
        border: 0;
    }
    #modal_traspasar_mascota .traspaso-section {
        border: 1px solid #dde4ea;
        border-radius: 10px;
        margin-bottom: 14px;
        overflow: hidden;
        background: #fff;
    }
    #modal_traspasar_mascota .traspaso-section-title {
        padding: 10px 14px;
        background: #f3f6f8;
        color: #344054;
        font-weight: 700;
        font-size: 13px;
    }
    #modal_traspasar_mascota .traspaso-section-body .form-group.fill {
        margin-bottom: 14px;
    }
    #modal_traspasar_mascota .traspaso-section-body .form-group.fill.mb-0 {
        margin-bottom: 0;
    }
    #modal_traspasar_mascota .traspaso-resumen-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid #e6edf1;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f9fcfd;
    }
    #modal_traspasar_mascota .traspaso-resumen-item i {
        color: #17a2b8;
        font-size: 18px;
        margin-top: 2px;
    }
    #modal_traspasar_mascota .traspaso-tutor-card {
        padding: 12px;
        border-radius: 8px;
        background: #eefafa;
        border: 1px solid #cfe9e7;
    }
    #modal_traspasar_mascota {
        z-index: 1060;
    }
    .modal-backdrop.traspaso-mascota-backdrop {
        z-index: 1055;
    }
</style>

<div class="modal fade" id="modal_traspasar_mascota" tabindex="-1" role="dialog"
    aria-labelledby="modalTraspasarMascotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="modalTraspasarMascotaLabel">
                        <i class="feather icon-shuffle"></i> Traspasar mascota a otro tutor
                    </h5>
                    <small>Transfiere la mascota con FVU, carné de vacunas y desparasitaciones</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    El traspaso cambia al tutor responsable. Se mantienen la ficha veterinaria única (FVU),
                    el carné de vacunas y el historial de desparasitaciones asociados a la mascota.
                </div>

                <section class="traspaso-section">
                    <div class="traspaso-section-title">1. Mascota a traspasar</div>
                    <div class="traspaso-section-body">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Seleccione mascota</label>
                            <select class="form-control form-control-sm" id="traspaso_mascota_id">
                                <option value="">Seleccione una mascota</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="traspaso-section">
                    <div class="traspaso-section-title">2. Situación del traspaso</div>
                    <div class="traspaso-section-body">
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Motivo o situación</label>
                            <textarea class="form-control form-control-sm" id="traspaso_situacion" rows="3"
                                placeholder="Ej: adopción, cambio de domicilio, entrega a familiar, etc."></textarea>
                        </div>
                    </div>
                </section>

                <section class="traspaso-section">
                    <div class="traspaso-section-title">3. Nuevo tutor responsable</div>
                    <div class="traspaso-section-body">
                        <div class="form-row align-items-end">
                            <div class="form-group fill col-md-8 mb-md-0">
                                <label class="floating-label-activo-sm">RUT del nuevo tutor</label>
                                <input type="text" class="form-control form-control-sm" id="traspaso_rut_tutor"
                                    placeholder="">
                            </div>
                            <div class="form-group col-md-4 mb-0">
                                <button type="button" class="btn btn-info btn-sm btn-block" id="btn_traspaso_buscar_tutor">
                                    <i class="feather icon-search"></i> Buscar tutor
                                </button>
                            </div>
                        </div>
                        <div id="traspaso_tutor_resultado" class="mt-3 d-none">
                            <div class="traspaso-tutor-card">
                                <strong id="traspaso_tutor_nombre">-</strong><br>
                                <small class="text-muted">RUT: <span id="traspaso_tutor_rut">-</span></small>
                                <input type="hidden" id="traspaso_tutor_id" value="">
                            </div>
                        </div>
                        <div id="traspaso_tutor_alerta" class="alert alert-warning py-2 mt-3 d-none mb-0"></div>
                        <div id="traspaso_tutor_formulario" class="mt-3 d-none">
                            <div class="alert alert-light border py-2 mb-3">
                                Complete los datos del nuevo tutor responsable.
                            </div>
                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm"><span style="color: red;">*</span> Nombres</label>
                                        <input type="text" class="form-control form-control-sm" id="traspaso_tutor_nombres">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm"><span style="color: red;">*</span> Apellido paterno</label>
                                        <input type="text" class="form-control form-control-sm" id="traspaso_tutor_apellido_uno">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Apellido materno</label>
                                        <input type="text" class="form-control form-control-sm" id="traspaso_tutor_apellido_dos">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Dirección</label>
                                        <input type="text" class="form-control form-control-sm" id="traspaso_tutor_direccion">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group fill mb-0">
                                        <label class="floating-label-activo-sm">Correo electrónico</label>
                                        <input type="email" class="form-control form-control-sm" id="traspaso_tutor_email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group fill mb-0">
                                        <label class="floating-label-activo-sm">Teléfono</label>
                                        <input type="text" class="form-control form-control-sm" id="traspaso_tutor_telefono">
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-outline-info btn-sm" id="btn_traspaso_validar_tutor">
                                    <i class="feather icon-check-circle"></i> Validar datos del tutor
                                </button>
                            </div>
                        </div>
                        <small class="text-danger d-none" id="traspaso_tutor_error"></small>
                    </div>
                </section>

                <section class="traspaso-section d-none" id="traspaso_resumen_bloque">
                    <div class="traspaso-section-title">4. Documentación que se traspasa</div>
                    <div class="traspaso-section-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Tutor actual</small>
                                <strong id="traspaso_tutor_actual">-</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Nuevo tutor</small>
                                <strong id="traspaso_tutor_nuevo_resumen">-</strong>
                            </div>
                        </div>
                        <div class="traspaso-resumen-item">
                            <i class="feather icon-file-text"></i>
                            <div>
                                <strong>Ficha Veterinaria Única (FVU)</strong>
                                <div class="text-muted small">
                                    <span id="traspaso_total_fvu">0</span> atención(es) clínica(s) registrada(s)
                                </div>
                            </div>
                        </div>
                        <div class="traspaso-resumen-item">
                            <i class="fas fa-syringe"></i>
                            <div>
                                <strong>Carné de vacunas</strong>
                                <div class="text-muted small">
                                    <span id="traspaso_total_vacunas">0</span> registro(s) de vacunación
                                </div>
                            </div>
                        </div>
                        <div class="traspaso-resumen-item">
                            <i class="feather icon-shield"></i>
                            <div>
                                <strong>Desparasitaciones</strong>
                                <div class="text-muted small">
                                    <span id="traspaso_total_desparasitaciones">0</span> registro(s) de desparasitación
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info btn-sm" id="btn_traspaso_confirmar" disabled>
                    <i class="feather icon-check"></i> Confirmar traspaso
                </button>
            </div>
        </div>
    </div>
</div>


