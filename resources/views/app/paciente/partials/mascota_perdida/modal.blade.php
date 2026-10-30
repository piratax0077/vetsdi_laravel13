        <!--MODAL MASCOTA PERDIDA-->
        <div class="modal fade" id="modal_mascota_perdida" tabindex="-1" role="dialog"
            aria-labelledby="modalMascotaPerdidaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="modalMascotaPerdidaLabel">
                            <i class="feather icon-alert-triangle"></i> Publicar mascota perdida
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <!-- Primera vista: elegir la mascota -->
                        <div id="perdida_vista_seleccion">
                            <p class="perdida-intro">Selecciona la mascota que se perdió para completar el aviso.</p>
                            <div class="form-group fill mb-0">
                                <label class="floating-label-activo-sm" for="perdida_mascota_id"><span class="text-danger">*</span> Mascota</label>
                                <select class="form-control form-control-sm" id="perdida_mascota_id">
                                    <option value="">Seleccione una mascota</option>
                                </select>
                            </div>
                        </div>

                        <!-- Segunda vista: datos del aviso -->
                        <div id="perdida_vista_formulario" class="d-none">
                            <div class="perdida-resumen">
                                <img id="perdida_mascota_foto" src="{{ asset('images/iconos/mascotas.svg') }}" alt="Foto de la mascota">
                                <div class="perdida-resumen-texto">
                                    <span class="perdida-resumen-titulo">Marcaste a <strong id="perdida_mascota_nombre"></strong> como perdida</span>
                                    <button type="button" class="perdida-cambiar text-dark" onclick="return volverSeleccionMascotaPerdida(event);">
                                        <i class="feather icon-repeat"></i> Cambiar mascota
                                    </button>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm" for="perdida_direccion"><span class="text-danger">*</span> Dirección donde se perdió</label>
                                        <select class="form-control form-control-sm" id="perdida_direccion"></select>
                                        <input type="hidden" id="perdida_latitud" value="">
                                        <input type="hidden" id="perdida_longitud" value="">
                                        <div class="perdida-mapa" id="perdida_mapa"></div>
                                        <small class="perdida-mapa-estado" id="perdida_mapa_estado">Escribe la dirección y elige una de las sugerencias. Puedes arrastrar el pin para ajustar el punto exacto.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm" for="perdida_fecha"><span class="text-danger">*</span> Fecha</label>
                                        <input type="date" class="form-control form-control-sm" id="perdida_fecha" max="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm" for="perdida_hora">Hora aproximada</label>
                                        <input type="time" class="form-control form-control-sm" id="perdida_hora">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm" for="perdida_descripcion"><span class="text-danger">*</span> Descripción</label>
                                        <textarea class="form-control form-control-sm" id="perdida_descripcion" rows="4" maxlength="2000"
                                            placeholder="Cuenta cómo lucía cuando se perdió: collar, ropa, color, señas particulares, cómo ocurrió..."></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="perdida-recompensa">
                                        <div class="perdida-recompensa-cabecera">
                                            <span class="perdida-recompensa-icono"><img src="{{ asset('images/iconos/regalo.svg') }}" alt=""></span>
                                            <label class="perdida-recompensa-texto" for="perdida_recompensa">
                                                <strong>Ofrecer recompensa</strong>
                                                <small>Puede motivar a más personas a ayudar en la búsqueda.</small>
                                            </label>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="perdida_recompensa" onchange="toggleRecompensaMascotaPerdida();">
                                                <label class="custom-control-label" for="perdida_recompensa"><span class="sr-only">Ofrecer recompensa</span></label>
                                            </div>
                                        </div>
                                        <div class="perdida-recompensa-monto d-none" id="perdida_recompensa_detalle">
                                            <div class="perdida-monto-campo">
                                                <span class="perdida-monto-signo">$</span>
                                                <input type="number" class="form-control" id="perdida_recompensa_monto" min="0" step="1000" placeholder="Ej. 50000">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="titulo-item text-dark">Fotos</div>
                                    <p class="small text-muted mb-2">Agrega fotos recientes para que sea más fácil reconocerla.</p>
                                    <input type="hidden" id="perdida_fotos_json" value="[]">
                                    <div class="dropzone" id="dropzone-mascota-perdida"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger d-none" id="btn_publicar_mascota_perdida" onclick="return publicarMascotaPerdida(event);">
                            <i class="feather icon-send"></i> Publicar
                        </button>
                    </div>
                </div>
            </div>
        </div>
