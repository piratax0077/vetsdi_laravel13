{{-- Modal para crear / editar un ítem. Se comparte entre Aranceles, Exámenes y Procedimientos (public/js/aranceles.js) --}}
<div id="arvModalItem" class="modal fade arv-modal" tabindex="-1" role="dialog" aria-labelledby="arvModalTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <form id="arvFormulario" class="modal-content" novalidate autocomplete="off">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title mt-1" id="arvModalTitulo">Nuevo arancel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>

            <div class="modal-body">
                {{-- Datos generales --}}
                <h6 class="arv-seccion">Datos generales</h6>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="arvCodigo">Código <span class="text-danger">*</span></label>
                            <input type="text" id="arvCodigo" data-campo="codigo" class="form-control form-control-sm text-uppercase" maxlength="20" placeholder="CON-001">
                            <small class="arv-error" data-error-de="codigo"></small>
                        </div>
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="arvNombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="arvNombre" data-campo="nombre" class="form-control form-control-sm" maxlength="120" placeholder="Consulta general">
                            <small class="arv-error" data-error-de="nombre"></small>
                        </div>
                    </div>
                </div>

                {{-- Campos propios: Aranceles / Prestaciones --}}
                <div class="arv-propios" data-catalogo="aranceles">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvCategoria">Categoría</label>
                                <select id="arvCategoria" class="form-control form-control-sm">
                                    <option>Consulta</option>
                                    <option>Vacunación</option>
                                    <option>Desparasitación</option>
                                    <option>Control</option>
                                    <option>Otros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvEspecie">Especie aplicable</label>
                                <select id="arvEspecie" class="form-control form-control-sm">
                                    <option>Todas</option>
                                    <option>Canino</option>
                                    <option>Felino</option>
                                    <option>Exótico</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvLugar">Lugar de aplicación</label>
                                <select id="arvLugar" class="form-control form-control-sm"></select>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvDescripcion">Descripción <span class="arv-opcional">(opcional)</span></label>
                                <textarea id="arvDescripcion" class="form-control form-control-sm" rows="2" maxlength="300"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvVigenteDesde">Vigente desde</label>
                                <input type="date" id="arvVigenteDesde" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvVigenteHasta">Vigente hasta <span class="arv-opcional">(opcional)</span></label>
                                <input type="date" id="arvVigenteHasta" data-campo="vigenteHasta" class="form-control form-control-sm">
                                <small class="arv-error" data-error-de="vigenteHasta"></small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Campos propios: Exámenes --}}
                <div class="arv-propios" data-catalogo="examenes">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvExaTipo">Tipo</label>
                                <select id="arvExaTipo" class="form-control form-control-sm">
                                    <option>Laboratorio</option>
                                    <option>Imagenología</option>
                                    <option>Ecografía</option>
                                    <option>Citología</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvRealizacion">¿Dónde se realiza?</label>
                                <select id="arvRealizacion" class="form-control form-control-sm">
                                    <option value="propio">En mi consulta (propio)</option>
                                    <option value="externo">Laboratorio externo</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12" id="arvLaboratorioGrupo">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvLaboratorio">Nombre del laboratorio <span class="text-danger">*</span></label>
                                <input type="text" id="arvLaboratorio" data-campo="laboratorio" class="form-control form-control-sm" maxlength="120">
                                <small class="arv-error" data-error-de="laboratorio"></small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvMuestra">Muestra requerida</label>
                                <input type="text" id="arvMuestra" class="form-control form-control-sm" maxlength="120" placeholder="Sangre, orina, etc.">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvTiempoEntrega">Tiempo de entrega</label>
                                <input type="text" id="arvTiempoEntrega" class="form-control form-control-sm" maxlength="60" placeholder="24 horas, 3 días">
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvPreparacion">Preparación</label>
                                <input type="text" id="arvPreparacion" class="form-control form-control-sm" maxlength="200" placeholder="Ayuno de 8 horas">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvCostoLaboratorio">Costo del laboratorio</label>
                                <input type="number" id="arvCostoLaboratorio" data-campo="costoLaboratorio" class="form-control form-control-sm arv-monto" min="0" step="1" inputmode="numeric" placeholder="Opcional">
                                <small class="arv-error" data-error-de="costoLaboratorio"></small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Campos propios: Procedimientos --}}
                <div class="arv-propios" data-catalogo="procedimientos">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvProTipo">Tipo</label>
                                <select id="arvProTipo" class="form-control form-control-sm">
                                    <option>Cirugía</option>
                                    <option>Curación</option>
                                    <option>Dental</option>
                                    <option>Anestesia</option>
                                    <option>Hospitalización</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvDuracion">Duración estimada</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" id="arvDuracion" data-campo="duracionMin" class="form-control form-control-sm" min="1" step="1" inputmode="numeric" placeholder="45">
                                    <div class="input-group-append"><span class="input-group-text">min</span></div>
                                </div>
                                <small class="arv-error" data-error-de="duracionMin"></small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group arv-switch-campo">
                                <div class="switch switch-success d-inline m-r-10">
                                    <input type="checkbox" id="arvAnestesia">
                                    <label for="arvAnestesia" class="cr"></label>
                                </div>
                                <label for="arvAnestesia">¿Requiere anestesia?</label>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvInsumos">Insumos incluidos <span class="arv-opcional">(opcional)</span></label>
                                <textarea id="arvInsumos" class="form-control form-control-sm" rows="2" maxlength="300" placeholder="Sutura, gasas, etc."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Precio --}}
                <h6 class="arv-seccion">Precio</h6>
                <div class="form-group arv-switch-linea">
                    <div class="switch switch-success d-inline m-r-10">
                        <input type="checkbox" id="arvVaria">
                        <label for="arvVaria" class="cr"></label>
                    </div>
                    <label for="arvVaria">¿El precio varía por tamaño?</label>
                </div>

                {{-- Valor único --}}
                <div id="arvPrecioUnico">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvValorNeto">Valor neto <span class="text-danger">*</span></label>
                                <input type="number" id="arvValorNeto" data-campo="valorNeto" class="form-control form-control-sm arv-monto" min="1" step="1" inputmode="numeric" placeholder="25000">
                                <small class="arv-error" data-error-de="valorNeto"></small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group arv-switch-campo">
                                <div class="switch switch-success d-inline m-r-10">
                                    <input type="checkbox" id="arvAfectoIva">
                                    <label for="arvAfectoIva" class="cr"></label>
                                </div>
                                <label for="arvAfectoIva">¿Afecto a IVA? (<span id="arvEtiquetaIva">19%</span>)</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvValorFinal">Valor final</label>
                                <input type="text" id="arvValorFinal" class="form-control form-control-sm arv-solo-lectura" readonly tabindex="-1" placeholder="Se calcula solo">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvCostoInterno">Costo interno <span class="arv-opcional">(opcional)</span></label>
                                <input type="number" id="arvCostoInterno" data-campo="costoInterno" class="form-control form-control-sm arv-monto" min="0" step="1" inputmode="numeric" placeholder="8000">
                                <small class="arv-error" data-error-de="costoInterno"></small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="arvMargen">Margen</label>
                                <input type="text" id="arvMargen" class="form-control form-control-sm arv-solo-lectura" readonly tabindex="-1" placeholder="Ingresa el costo">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Precio por tamaño --}}
                <div id="arvPrecioTamanos" style="display:none">
                    <div class="arv-tamanos-acciones">
                        <button type="button" id="arvCopiarTodos" class="btn btn-outline-info btn-sm"><i class="feather icon-copy"></i> Copiar valor a todos</button>
                        <div class="input-group input-group-sm arv-escalon">
                            <input type="number" id="arvPorcentaje" class="form-control form-control-sm" value="20" min="0" step="1" aria-label="Porcentaje por escalón">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                                <button type="button" id="arvAplicarEscalon" class="btn btn-outline-info">Aplicar % por escalón</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table arv-tabla-tamanos mb-0" id="arvTablaTamanos">
                            <thead>
                                <tr>
                                    <th class="text-center">Activo</th>
                                    <th>Tamaño</th>
                                    <th>Valor neto</th>
                                    <th class="text-center">IVA</th>
                                    <th>Valor final</th>
                                    <th>Costo interno</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <small class="arv-error" data-error-de="tamanos"></small>
                    <small class="arv-ayuda">Desmarca "Activo" en los tamaños que no atiendes. El % por escalón parte del tamaño más pequeño y sube ese porcentaje en cada tamaño siguiente.</small>
                </div>

                {{-- Estado --}}
                <h6 class="arv-seccion">Estado</h6>
                <div class="form-group arv-switch-linea mb-0">
                    <div class="switch switch-success d-inline m-r-10">
                        <input type="checkbox" id="arvActivo">
                        <label for="arvActivo" class="cr"></label>
                    </div>
                    <label for="arvActivo">Activo (disponible para nuevas atenciones)</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                <button type="submit" id="arvGuardar" class="btn btn-info"><i class="feather icon-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
