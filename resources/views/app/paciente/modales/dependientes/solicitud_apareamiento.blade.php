<div class="modal fade" id="modal_solicitud_apareamiento" tabindex="-1" role="dialog"
    aria-labelledby="modalSolicitudApareamientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title mb-1" id="modalSolicitudApareamientoLabel">Solicitud de apareamiento</h5>
                    <small class="apareamiento-subtitulo">Indique las características del compañero reproductivo que busca</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills apareamiento-pestanas" id="apareamiento_tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="apareamiento-tab-publicar" data-toggle="pill"
                            href="#apareamiento_panel_publicar" role="tab">
                            <i class="feather icon-edit-2"></i> Publicar solicitud
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="apareamiento-tab-recibidas" data-toggle="pill"
                            href="#apareamiento_panel_recibidas" role="tab">
                            <i class="feather icon-inbox"></i> Solicitudes recibidas
                            <span class="badge badge-danger d-none" id="apareamiento_badge_nuevas">0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="apareamiento_panel_publicar" role="tabpanel">
                        <div class="apareamiento-aviso">
                            <i class="feather icon-info"></i>
                            <span>
                                Esta solicitud es informativa para facilitar una cruza responsable. No garantiza emparejamientos;
                                revise siempre antecedentes clínicos, vacunas y evaluación veterinaria previa.
                            </span>
                        </div>

                        <div class="titulo-item">Su mascota</div>
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="apareamiento_mascota_id">Mascota</label>
                            <select class="form-control form-control-sm" id="apareamiento_mascota_id">
                                <option value="">Seleccione una mascota</option>
                            </select>
                        </div>
                        <div id="apareamiento_mascota_resumen" class="apareamiento-resumen d-none">
                            <strong class="apareamiento-resumen-nombre" id="apareamiento_resumen_nombre">-</strong>
                            <div class="apareamiento-resumen-datos">
                                <span id="apareamiento_resumen_especie">-</span>
                                <span>Raza: <b id="apareamiento_resumen_raza">-</b></span>
                                <span>Sexo: <b id="apareamiento_resumen_sexo">-</b></span>
                                <span>Edad: <b id="apareamiento_resumen_edad">-</b></span>
                            </div>
                            <div id="apareamiento_sexo_companero_row" class="apareamiento-resumen-companero d-none">
                                Compañero reproductivo buscado:
                                <strong id="apareamiento_sexo_companero_texto">—</strong>
                                <span class="text-muted">(asignado automáticamente)</span>
                            </div>
                            <div id="apareamiento_sexo_sin_registro" class="apareamiento-resumen-alerta d-none">
                                Registre el sexo de su mascota en la ficha para poder publicar la solicitud.
                            </div>
                        </div>

                        <div class="titulo-item">Compañero reproductivo que busca</div>
                        <input type="hidden" id="apareamiento_sexo_buscado" value="">
                        <div class="form-row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_raza_buscada"><span class="text-danger">*</span> Raza buscada</label>
                                    <select class="form-control form-control-sm" id="apareamiento_raza_buscada" disabled>
                                        <option value="">Seleccione primero una mascota</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_edad_minima">Edad mínima</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_edad_minima"
                                        placeholder="Ej: 1 año">
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_edad_maxima">Edad máxima</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_edad_maxima"
                                        placeholder="Ej: 5 años">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_tamano_id">Tamaño preferido</label>
                                    <select class="form-control form-control-sm" id="apareamiento_tamano_id">
                                        <option value="">Indiferente</option>
                                        @foreach(($tamanosMascotas ?? []) as $tamano)
                                            <option value="{{ $tamano->id }}">{{ $tamano->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_color_pelaje">Color o pelaje</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_color_pelaje">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_pedigree">Pedigrí / registro</label>
                                    <select class="form-control form-control-sm" id="apareamiento_pedigree">
                                        <option value="indiferente">Indiferente</option>
                                        <option value="si">Con registro</option>
                                        <option value="no">Sin registro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_ubicacion">Ubicación preferida</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_ubicacion"
                                        placeholder="Ej: Región Metropolitana, comuna, etc.">
                                </div>
                            </div>
                        </div>

                        <div class="titulo-item">Detalle y contacto</div>
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="apareamiento_observaciones">Características o requisitos adicionales</label>
                            <textarea class="form-control form-control-sm" id="apareamiento_observaciones" rows="3"
                                placeholder="Temperamento, pruebas de salud, tipo de cruce, disponibilidad, etc."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="apareamiento_contacto_telefono">Teléfono de contacto</label>
                                    <input type="text" class="form-control form-control-sm" id="apareamiento_contacto_telefono"
                                        value="{{ optional($paciente ?? null)->telefono_uno }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="floating-label-activo-sm" for="apareamiento_contacto_email">Correo de contacto</label>
                                    <input type="email" class="form-control form-control-sm" id="apareamiento_contacto_email"
                                        value="{{ optional($paciente ?? null)->email }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="apareamiento_panel_recibidas" role="tabpanel">
                        <div class="apareamiento-aviso">
                            <i class="feather icon-info"></i>
                            <span>Aquí verá solicitudes de otros tutores cuyas mascotas son compatibles con las suyas.</span>
                        </div>
                        <div id="apareamiento_lista_recibidas"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-purple btn-sm" id="btn_apareamiento_guardar">
                    <i class="feather icon-send"></i> Publicar solicitud
                </button>
            </div>
        </div>
    </div>
</div>
