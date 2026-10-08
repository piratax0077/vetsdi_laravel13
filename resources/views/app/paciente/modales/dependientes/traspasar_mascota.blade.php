<div class="modal fade" id="modal_traspasar_mascota" tabindex="-1" role="dialog"
    aria-labelledby="modalTraspasarMascotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title mb-1" id="modalTraspasarMascotaLabel">Traspasar mascota a otro tutor</h5>
                    <small>La mascota pasa al nuevo tutor junto a su FVU, carné de vacunas y desparasitaciones</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!--Indicador de pasos-->
                <ol class="traspaso-pasos" id="traspaso_pasos">
                    <li class="traspaso-paso activo" data-paso="1">
                        <button type="button" class="traspaso-paso-boton" data-paso="1">
                            <span class="traspaso-paso-numero">1</span>
                            <span class="traspaso-paso-texto">Mascota</span>
                        </button>
                    </li>
                    <li class="traspaso-paso" data-paso="2">
                        <button type="button" class="traspaso-paso-boton" data-paso="2" disabled>
                            <span class="traspaso-paso-numero">2</span>
                            <span class="traspaso-paso-texto">Nuevo tutor</span>
                        </button>
                    </li>
                    <li class="traspaso-paso" data-paso="3">
                        <button type="button" class="traspaso-paso-boton" data-paso="3" disabled>
                            <span class="traspaso-paso-numero">3</span>
                            <span class="traspaso-paso-texto">Confirmar</span>
                        </button>
                    </li>
                </ol>

                <!--Paso 1: mascota y motivo-->
                <div class="traspaso-panel activo" data-paso="1">
                    <div class="traspaso-panel-encabezado">
                        <h6>¿Qué mascota va a traspasar?</h6>
                        <p class="small text-muted mb-0">Elija la mascota e indique por qué cambia de tutor.</p>
                    </div>

                    <div class="form-group">
                        <label class="floating-label-activo-sm" for="traspaso_mascota_id"><span class="text-danger">*</span> Mascota</label>
                        <select class="form-control form-control-sm" id="traspaso_mascota_id">
                            <option value="">Seleccione una mascota</option>
                        </select>
                    </div>

                    <div class="traspaso-mascota d-none" id="traspaso_mascota_resumen">
                        <span class="traspaso-mascota-foto">
                            <img id="traspaso_mascota_img" src="{{ asset('images/iconos/mascotas.svg') }}" alt="Foto de la mascota">
                        </span>
                        <div class="traspaso-mascota-datos">
                            <strong class="traspaso-mascota-nombre" id="traspaso_mascota_nombre">-</strong>
                            <span class="traspaso-mascota-detalle" id="traspaso_mascota_detalle"></span>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label class="floating-label-activo-sm" for="traspaso_situacion"><span class="text-danger">*</span> Motivo del traspaso</label>
                        <textarea class="form-control form-control-sm" id="traspaso_situacion" rows="3" maxlength="1000"
                            placeholder="Ej: adopción, cambio de domicilio, entrega a un familiar..."></textarea>
                    </div>

                    <div class="traspaso-motivos">
                        <span class="traspaso-motivos-titulo">Motivos frecuentes:</span>
                        <button type="button" class="traspaso-motivo" data-motivo="Adopción">Adopción</button>
                        <button type="button" class="traspaso-motivo" data-motivo="Entrega a un familiar">Entrega a un familiar</button>
                        <button type="button" class="traspaso-motivo" data-motivo="Cambio de domicilio">Cambio de domicilio</button>
                    </div>
                    <small class="traspaso-error d-none" id="traspaso_paso1_error"></small>
                </div>

                <!--Paso 2: nuevo tutor-->
                <div class="traspaso-panel" data-paso="2">
                    <div class="traspaso-panel-encabezado">
                        <h6>¿Quién será el nuevo tutor?</h6>
                        <p class="small text-muted mb-0">Búsquelo por su RUT. Si aún no está registrado, podrá ingresar sus datos.</p>
                    </div>

                    <div class="traspaso-buscador">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="traspaso_rut_tutor"><span class="text-danger">*</span> RUT del nuevo tutor</label>
                            <input type="text" class="form-control form-control-sm" id="traspaso_rut_tutor"
                                placeholder="Ej: 12345678-9" autocomplete="off">
                        </div>
                        <button type="button" class="btn btn-info btn-sm" id="btn_traspaso_buscar_tutor">
                            <i class="feather icon-search"></i> Buscar
                        </button>
                    </div>
                    <small class="traspaso-error d-none" id="traspaso_tutor_error"></small>

                    <div class="traspaso-aviso d-none" id="traspaso_tutor_alerta">
                        <i class="feather icon-info"></i>
                        <span id="traspaso_tutor_alerta_texto"></span>
                    </div>

                    <!--Tutor ya registrado en VET-SDI-->
                    <div class="traspaso-tutor d-none" id="traspaso_tutor_resultado">
                        <span class="traspaso-tutor-avatar"><i class="feather icon-user"></i></span>
                        <div class="traspaso-tutor-datos">
                            <strong class="traspaso-tutor-nombre" id="traspaso_tutor_nombre">-</strong>
                            <span class="traspaso-tutor-rut">RUT <span id="traspaso_tutor_rut">-</span></span>
                        </div>
                        <span class="traspaso-tutor-estado"><i class="feather icon-check"></i> Registrado</span>
                        <input type="hidden" id="traspaso_tutor_id" value="">
                    </div>

                    <!--Tutor sin cuenta: se piden sus datos-->
                    <div class="d-none" id="traspaso_tutor_formulario">
                        <div class="titulo-item">Datos del nuevo tutor</div>
                        <div class="form-row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="traspaso_tutor_nombres"><span class="text-danger">*</span> Nombres</label>
                                    <input type="text" class="form-control form-control-sm" id="traspaso_tutor_nombres" maxlength="120">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="traspaso_tutor_apellido_uno"><span class="text-danger">*</span> Apellido paterno</label>
                                    <input type="text" class="form-control form-control-sm" id="traspaso_tutor_apellido_uno" maxlength="120">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="traspaso_tutor_apellido_dos">Apellido materno</label>
                                    <input type="text" class="form-control form-control-sm" id="traspaso_tutor_apellido_dos" maxlength="120">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm" for="traspaso_tutor_direccion">Dirección</label>
                                    <input type="text" class="form-control form-control-sm" id="traspaso_tutor_direccion" maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-md-0">
                                    <label class="floating-label-activo-sm" for="traspaso_tutor_email">Correo electrónico</label>
                                    <input type="email" class="form-control form-control-sm" id="traspaso_tutor_email" maxlength="190">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="floating-label-activo-sm" for="traspaso_tutor_telefono">Teléfono</label>
                                    <input type="text" class="form-control form-control-sm" id="traspaso_tutor_telefono" maxlength="30">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Paso 3: resumen y confirmación-->
                <div class="traspaso-panel" data-paso="3">
                    <div class="traspaso-panel-encabezado">
                        <h6>Revise el traspaso</h6>
                        <p class="small text-muted mb-0">Si todo está correcto, confirme para traspasar la mascota.</p>
                    </div>

                    <div class="traspaso-cambio">
                        <div class="traspaso-cambio-tutor">
                            <span class="traspaso-etiqueta">Tutor actual</span>
                            <strong class="traspaso-cambio-nombre" id="traspaso_tutor_actual">-</strong>
                            <small class="traspaso-cambio-rut" id="traspaso_tutor_actual_rut"></small>
                        </div>
                        <div class="traspaso-cambio-mascota">
                            <span class="traspaso-mascota-foto">
                                <img id="traspaso_resumen_img" src="{{ asset('images/iconos/mascotas.svg') }}" alt="Foto de la mascota">
                            </span>
                            <strong class="traspaso-mascota-nombre" id="traspaso_resumen_mascota">-</strong>
                            <span class="traspaso-cambio-flecha"><i class="feather icon-arrow-right"></i></span>
                        </div>
                        <div class="traspaso-cambio-tutor traspaso-cambio-nuevo">
                            <span class="traspaso-etiqueta">Nuevo tutor</span>
                            <strong class="traspaso-cambio-nombre" id="traspaso_tutor_nuevo_resumen">-</strong>
                            <small class="traspaso-cambio-rut" id="traspaso_tutor_nuevo_rut"></small>
                        </div>
                    </div>

                    <div class="traspaso-motivo-resumen">
                        <span class="traspaso-etiqueta">Motivo</span>
                        <p id="traspaso_resumen_motivo">-</p>
                    </div>

                    <div class="titulo-item">Se traspasa junto a la mascota</div>
                    <div class="traspaso-documentos">
                        <div class="traspaso-documento">
                            <span class="traspaso-documento-icono"><i class="feather icon-file-text"></i></span>
                            <div>
                                <strong class="traspaso-documento-total" id="traspaso_total_fvu">0</strong>
                                <span class="traspaso-documento-nombre">Ficha veterinaria única</span>
                                <small>atenciones clínicas</small>
                            </div>
                        </div>
                        <div class="traspaso-documento">
                            <span class="traspaso-documento-icono"><i class="fas fa-syringe"></i></span>
                            <div>
                                <strong class="traspaso-documento-total" id="traspaso_total_vacunas">0</strong>
                                <span class="traspaso-documento-nombre">Carné de vacunas</span>
                                <small>registros de vacunación</small>
                            </div>
                        </div>
                        <div class="traspaso-documento">
                            <span class="traspaso-documento-icono"><i class="feather icon-shield"></i></span>
                            <div>
                                <strong class="traspaso-documento-total" id="traspaso_total_desparasitaciones">0</strong>
                                <span class="traspaso-documento-nombre">Desparasitaciones</span>
                                <small>registros de desparasitación</small>
                            </div>
                        </div>
                    </div>

                    <div class="traspaso-aviso traspaso-aviso-alerta">
                        <i class="feather icon-alert-triangle"></i>
                        <span>
                            Al confirmar, el nuevo tutor queda como responsable de la mascota. La FVU, el carné de vacunas
                            y las desparasitaciones se mantienen asociados a ella.
                        </span>
                    </div>
                </div>
            </div>
            <div class="modal-footer traspaso-acciones">
                <button type="button" class="btn btn-outline-dark traspaso-volver" id="btn_traspaso_cancelar" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-secondary traspaso-volver d-none" id="btn_traspaso_anterior">
                    <i class="feather icon-chevron-left"></i> Anterior
                </button>
                <button type="button" class="btn btn-info" id="btn_traspaso_siguiente" disabled>
                    Siguiente <i class="feather icon-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-info d-none" id="btn_traspaso_confirmar" disabled>
                    <i class="feather icon-check"></i> Confirmar traspaso
                </button>
            </div>
        </div>
    </div>
</div>
