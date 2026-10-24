{{-- Modal para agregar o editar un procedimiento; lo abre abrir_modal_procedimiento() en configuracion.
     Cada guardado es un procedimiento para una especie y tamaño; otra especie se agrega con otro procedimiento --}}
<div id="modal_procedimiento" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_procedimiento" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form id="form_procedimiento" method="post" action="{{ route('peluqueria.procedimientos.guardar') }}" novalidate>
                @csrf
                {{-- Solo lleva valor al editar; al agregar va deshabilitado para que no se envíe --}}
                <input type="hidden" name="id" value="" disabled>
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="titulo_modal_procedimiento">Agregar procedimiento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-12 col-md-7">
                            <label class="floating-label-activo-sm" for="procedimiento_nombre">Procedimiento</label>
                            <input type="text" class="form-control form-control-sm" id="procedimiento_nombre" name="nombre" placeholder="Ej.: Corte de uñas" maxlength="150" required>
                            <div class="invalid-feedback">Escribe el nombre del procedimiento.</div>
                        </div>
                        {{-- Los mismos lugares del selector del panel; la casa matriz va primero --}}
                        <div class="form-group col-12 col-md-5">
                            <label class="floating-label-activo-sm" for="procedimiento_sucursal">Lugar de atención</label>
                            <select class="form-control form-control-sm" id="procedimiento_sucursal" name="sucursal" required>
                                @foreach($lugares ?? [] as $lugar)
                                    <option value="{{ $lugar['valor'] }}">{{ $lugar['nombre'] }}{{ $lugar['matriz'] ? ' (Casa matriz)' : '' }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Selecciona el lugar de atención.</div>
                        </div>
                        {{-- A qué mascotas aplica: mismas especies de Aranceles y mismos tamaños de Suscripción --}}
                        <div class="form-group col-12 col-md">
                            <label class="floating-label-activo-sm" for="procedimiento_especie">Especie</label>
                            <select class="form-control form-control-sm" id="procedimiento_especie" name="especie" onchange="actualizar_mascota_procedimiento(this);">
                                <option value="Todas">Todas</option>
                                <option value="Canino">Canino</option>
                                <option value="Felino">Felino</option>
                                <option value="Exótico">Exótico</option>
                            </select>
                        </div>
                        {{-- Con "Exótico" se escribe qué animal es; oculto va deshabilitado para que no se envíe --}}
                        <div class="form-group col-12 col-md d-none" id="procedimiento_exotico_grupo">
                            <label class="floating-label-activo-sm" for="procedimiento_exotico">¿Qué animal exótico?</label>
                            <input type="text" class="form-control form-control-sm" id="procedimiento_exotico" name="especie_exotico" placeholder="Ej.: Conejo, hurón" maxlength="100" required disabled>
                            <div class="invalid-feedback">Escribe el tipo de animal.</div>
                        </div>
                        <div class="form-group col-12 col-md">
                            <label class="floating-label-activo-sm" for="procedimiento_tamano">Tamaño</label>
                            <select class="form-control form-control-sm" id="procedimiento_tamano" name="tamano" onchange="actualizar_mascota_procedimiento(this);">
                                <option value="Todos">Todos los tamaños</option>
                                <option value="Pequeño">Pequeño (hasta 10 kg)</option>
                                <option value="Mediano">Mediano (10 a 25 kg)</option>
                                <option value="Grande">Grande (más de 25 kg)</option>
                                <option value="Peso">Por peso (kg)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        {{-- "Por peso (kg)" muestra el rango Desde / Hasta --}}
                        <div class="form-group col-6 col-md rango-peso d-none">
                            <label class="floating-label-activo-sm" for="procedimiento_peso_desde">Desde (kg)</label>
                            <input type="number" class="form-control form-control-sm" id="procedimiento_peso_desde" name="peso_desde" min="0" max="150" step="0.1" placeholder="0" disabled>
                            <div class="invalid-feedback">Indica el peso.</div>
                        </div>
                        <div class="form-group col-6 col-md rango-peso d-none">
                            <label class="floating-label-activo-sm" for="procedimiento_peso_hasta">Hasta (kg)</label>
                            <input type="number" class="form-control form-control-sm" id="procedimiento_peso_hasta" name="peso_hasta" min="0" max="150" step="0.1" placeholder="10" disabled>
                            <div class="invalid-feedback">Debe ser mayor que "Desde".</div>
                        </div>
                        <div class="form-group col-6 col-md">
                            <label class="floating-label-activo-sm" for="procedimiento_duracion">Duración (minutos)</label>
                            <input type="number" class="form-control form-control-sm" id="procedimiento_duracion" name="duracion" min="15" max="480" step="1" value="30" required>
                            <div class="invalid-feedback">Entre 15 y 480 minutos.</div>
                        </div>
                        <div class="form-group col-6 col-md">
                            <label class="floating-label-activo-sm" for="procedimiento_precio">Precio ($ CLP)</label>
                            <input type="number" class="form-control form-control-sm" id="procedimiento_precio" name="precio" min="0" max="999999999" step="1" required>
                            <div class="invalid-feedback">Ingresa un precio válido, sin puntos ni decimales.</div>
                        </div>
                        <div class="form-group col-12 mb-0">
                            <label class="floating-label-activo-sm" for="procedimiento_indicaciones">Cuidados e indicaciones del procedimiento</label>
                            <textarea class="form-control form-control-sm" id="procedimiento_indicaciones" name="indicaciones" maxlength="3000" rows="3"></textarea>
                        </div>
                    </div>
                    {{-- Disponibilidad al final: encendido queda activo; apagado se envía 0 --}}
                    <div class="bloque-estado">
                        <input type="hidden" name="activo" value="0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="procedimiento_activo" name="activo" value="1" checked>
                            <label class="custom-control-label" for="procedimiento_activo">Procedimiento activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                    <button type="submit" class="btn btn-info" id="boton_guardar_procedimiento"><i class="feather icon-plus mr-1"></i>Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>
