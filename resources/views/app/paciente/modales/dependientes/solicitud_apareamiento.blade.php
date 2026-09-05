<style>
    #modal_solicitud_apareamiento .modal-content {
        border: 0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 22px 55px rgba(25, 43, 65, .28);
    }
    #modal_solicitud_apareamiento .modal-header {
        background: linear-gradient(135deg, #6f42c1, #9561e2);
        color: #fff;
        border: 0;
    }
    #modal_solicitud_apareamiento .apareamiento-section {
        border: 1px solid #e3e7ef;
        border-radius: 10px;
        margin-bottom: 14px;
        overflow: hidden;
        background: #fff;
    }
    #modal_solicitud_apareamiento .apareamiento-section-title {
        padding: 10px 14px;
        background: #f6f3fb;
        color: #4a3f63;
        font-weight: 700;
        font-size: 13px;
    }
    #modal_solicitud_apareamiento .apareamiento-section-body {
        padding: 14px;
    }
    #modal_solicitud_apareamiento .mascota-resumen-apareamiento {
        padding: 12px;
        border-radius: 8px;
        background: #f9f7fd;
        border: 1px solid #e5ddf5;
        font-size: 13px;
    }
    #modal_solicitud_apareamiento .form-group.fill {
        margin-bottom: 14px;
    }
    #modal_solicitud_apareamiento .form-group.fill.mb-0 {
        margin-bottom: 0;
    }
</style>

<div class="modal fade" id="modal_solicitud_apareamiento" tabindex="-1" role="dialog"
    aria-labelledby="modalSolicitudApareamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="modalSolicitudApareamientoLabel">
                        <i class="feather icon-heart"></i> Solicitud de apareamiento
                    </h5>
                    <small>Indique las características del compañero reproductivo que busca</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-3" id="apareamiento_tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="apareamiento-tab-publicar" data-toggle="pill"
                            href="#apareamiento_panel_publicar" role="tab">Publicar solicitud</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="apareamiento-tab-recibidas" data-toggle="pill"
                            href="#apareamiento_panel_recibidas" role="tab">
                            Solicitudes recibidas
                            <span class="badge badge-danger d-none" id="apareamiento_badge_nuevas">0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="apareamiento_panel_publicar" role="tabpanel">
                <div class="alert alert-info py-2 mb-3">
                    Esta solicitud es informativa para facilitar un cruza responsable. No garantiza emparejamientos;
                    revise siempre antecedentes clínicos, vacunas y evaluación veterinaria previa.
                </div>

                <section class="apareamiento-section">
                    <div class="apareamiento-section-title">1. Su mascota</div>
                    <div class="apareamiento-section-body">
                        <div class="form-group fill mb-3">
                            <label class="floating-label-activo-sm">Mascota</label>
                            <select class="form-control form-control-sm" id="apareamiento_mascota_id">
                                <option value="">Seleccione una mascota</option>
                            </select>
                        </div>
                        <div id="apareamiento_mascota_resumen" class="mascota-resumen-apareamiento d-none">
                            <strong id="apareamiento_resumen_nombre">-</strong>
                            <div class="text-muted mt-1">
                                <span id="apareamiento_resumen_especie">-</span> ·
                                Raza: <span id="apareamiento_resumen_raza">-</span> ·
                                Sexo: <span id="apareamiento_resumen_sexo">-</span> ·
                                Edad: <span id="apareamiento_resumen_edad">-</span>
                            </div>
                            <div id="apareamiento_sexo_companero_row" class="mt-2 d-none">
                                Compañero reproductivo buscado:
                                <strong id="apareamiento_sexo_companero_texto">—</strong>
                                <span class="text-muted">(asignado automáticamente)</span>
                            </div>
                            <div id="apareamiento_sexo_sin_registro" class="text-warning mt-2 d-none small">
                                Registre el sexo de su mascota en la ficha para poder publicar la solicitud.
                            </div>
                        </div>
                    </div>
                </section>

                <section class="apareamiento-section">
                    <div class="apareamiento-section-title">2. Compañero reproductivo que busca</div>
                    <div class="apareamiento-section-body">
                        <input type="hidden" id="apareamiento_sexo_buscado" value="">
                        <div class="form-row">
                            <div class="col-md-12">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm"><span style="color:red;">*</span> Raza buscada</label>
                                    <select class="form-control form-control-sm" id="apareamiento_raza_buscada" disabled>
                                        <option value="">Seleccione primero una mascota</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Edad mínima</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_edad_minima"
                                        placeholder="Ej: 1 año">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Edad máxima</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_edad_maxima"
                                        placeholder="Ej: 5 años">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Tamaño preferido</label>
                                    <select class="form-control form-control-sm" id="apareamiento_tamano_id">
                                        <option value="">Indiferente</option>
                                        @foreach(($tamanosMascotas ?? []) as $tamano)
                                            <option value="{{ $tamano->id }}">{{ $tamano->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Color o pelaje</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_color_pelaje">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Pedigrí / registro</label>
                                    <select class="form-control form-control-sm" id="apareamiento_pedigree">
                                        <option value="indiferente">Indiferente</option>
                                        <option value="si">Con registro</option>
                                        <option value="no">Sin registro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group fill mb-0">
                                    <label class="floating-label-activo-sm">Ubicación preferida</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_ubicacion"
                                        placeholder="Ej: Región Metropolitana, comuna, etc.">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="apareamiento-section">
                    <div class="apareamiento-section-title">3. Detalle y contacto</div>
                    <div class="apareamiento-section-body">
                        <div class="form-group fill">
                            <label class="floating-label-activo-sm">Características o requisitos adicionales</label>
                            <textarea class="form-control form-control-sm" id="apareamiento_observaciones" rows="3"
                                placeholder="Temperamento, pruebas de salud, tipo de cruce, disponibilidad, etc."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Teléfono de contacto</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_contacto_telefono"
                                        value="{{ optional($paciente ?? null)->telefono_uno }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill mb-0">
                                    <label class="floating-label-activo-sm">Correo de contacto</label>
                                    <input type="email" class="form-control form-control-sm" id="apareamiento_contacto_email"
                                        value="{{ optional($paciente ?? null)->email }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                    </div>

                    <div class="tab-pane fade" id="apareamiento_panel_recibidas" role="tabpanel">
                        <div class="alert alert-light border py-2 mb-3">
                            Aquí verá solicitudes de otros tutores cuyas mascotas son compatibles con las suyas.
                        </div>
                        <div id="apareamiento_lista_recibidas"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn_apareamiento_guardar">
                    <i class="feather icon-send"></i> Publicar solicitud
                </button>
            </div>
        </div>
    </div>
</div>

