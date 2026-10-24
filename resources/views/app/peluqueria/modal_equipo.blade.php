{{-- Modal para agregar o editar un integrante del equipo; lo abre abrir_modal_equipo() en configuracion --}}
@php
    // Todas las comunas con su región, para que al elegir la comuna la región se complete sola
    $ciudades_equipo = \App\Models\Ciudad::orderBy('nombre')->get(['id', 'nombre', 'id_region']);
    $regiones_equipo = \App\Models\Region::orderBy('nombre')->get();
@endphp
<div id="modal_equipo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_equipo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form_equipo" class="form-registro-pendiente" data-aviso="El registro del equipo aún no está disponible." novalidate>
                @csrf
                {{-- Solo lleva valor al editar; al agregar va deshabilitado para que no se envíe --}}
                <input type="hidden" name="id" value="" disabled>
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="titulo_modal_equipo">Agregar integrante</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    {{-- Aviso mientras el registro del equipo no exista --}}
                    <div class="alert alert-info d-none aviso-registro" role="status"></div>
                    <div class="form-row">
                        <div class="col-12">
                            <h6 class="titulo-sm">Información personal</h6>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="equipo_rut">RUT</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_rut" name="rut" placeholder="12.345.678-9" maxlength="12" required>
                            <div class="invalid-feedback">Escribe el RUT.</div>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="equipo_nombres">Nombre</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_nombres" name="nombres" maxlength="255" required>
                            <div class="invalid-feedback">Escribe el nombre.</div>
                        </div>
                        <div class="form-group col-12 col-lg-6">
                            <label class="floating-label-activo-sm" for="equipo_apellido_uno">Primer apellido</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_apellido_uno" name="apellido_uno" maxlength="255" required>
                            <div class="invalid-feedback">Escribe el primer apellido.</div>
                        </div>
                        <div class="form-group col-12 col-lg-6">
                            <label class="floating-label-activo-sm" for="equipo_apellido_dos">Segundo apellido</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_apellido_dos" name="apellido_dos" maxlength="255">
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-12">
                            <h6 class="titulo-sm">Trabajo</h6>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="equipo_cargo">Cargo</label>
                            <select class="form-control form-control-sm" id="equipo_cargo" name="cargo" required>
                                <option value="">Seleccione</option>
                                <option>Peluquero(a)</option>
                                <option>Estilista</option>
                                <option>Bañador(a)</option>
                                <option>Otro</option>
                            </select>
                            <div class="invalid-feedback">Selecciona el cargo.</div>
                        </div>
                        {{-- Con "Otro" se escribe el cargo a mano; oculto va deshabilitado para que no se envíe --}}
                        <div class="form-group col-12 d-none" id="equipo_cargo_otro_grupo">
                            <label class="floating-label-activo-sm" for="equipo_cargo_otro">¿Cuál cargo?</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_cargo_otro" name="cargo_otro" placeholder="Ej.: Groomer" maxlength="100" required disabled>
                            <div class="invalid-feedback">Escribe el cargo.</div>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="equipo_sucursal">Lugar de atención</label>
                            <select class="form-control form-control-sm" id="equipo_sucursal" name="sucursal">
                                {{-- Los mismos lugares del selector del panel; la casa matriz va primero --}}
                                <option value="">Seleccione</option>
                                @foreach ($lugares ?? [] as $lugar)
                                    <option value="{{ $lugar['valor'] }}">{{ $lugar['nombre'] }}{{ $lugar['matriz'] ? ' (Casa matriz)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-12">
                            <h6 class="titulo-sm">Contacto</h6>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="equipo_telefono">Teléfono</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_telefono" name="telefono" placeholder="+56 9 1234 5678" maxlength="30" required>
                            <div class="invalid-feedback">Escribe un teléfono de contacto.</div>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="equipo_email">Correo electrónico</label>
                            <input type="email" class="form-control form-control-sm" id="equipo_email" name="email" placeholder="nombre@correo.cl" maxlength="255">
                            <div class="invalid-feedback">Revisa el formato del correo.</div>
                        </div>
                    </div>
                    {{-- Ubicación en el orden en que se completa: dirección, comuna y la región sale de la comuna --}}
                    <div class="form-row mt-2">
                        <div class="col-12">
                            <h6 class="titulo-sm">Ubicación</h6>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="equipo_direccion">Dirección</label>
                            <input type="text" class="form-control form-control-sm" id="equipo_direccion" name="direccion" placeholder="Calle y número" maxlength="255">
                        </div>
                        <div class="form-group col-12 col-lg-6">
                            <label class="floating-label-activo-sm" for="equipo_region">Región</label>
                            <select class="form-control form-control-sm" id="equipo_region" name="id_region">
                                <option value="">Seleccione</option>
                                @foreach ($regiones_equipo as $region)
                                    <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-12 col-lg-6">
                            <label class="floating-label-activo-sm" for="equipo_ciudad">Comuna</label>
                            <select class="form-control form-control-sm" id="equipo_ciudad" name="id_ciudad">
                                <option value="">Seleccione</option>
                                @foreach ($ciudades_equipo as $ciudad)
                                    <option value="{{ $ciudad->id }}" data-region="{{ $ciudad->id_region }}">{{ $ciudad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Estado al final: encendido el integrante queda activo; apagado se envía 0 --}}
                    <div class="bloque-estado">
                        <input type="hidden" name="activo" value="0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input interruptor-estado" id="equipo_activo" name="activo" value="1" data-texto-si="Integrante activo" data-texto-no="Integrante inactivo" checked>
                            <label class="custom-control-label" for="equipo_activo">Integrante activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                    <button type="submit" class="btn btn-info" id="boton_guardar_equipo"><i class="feather icon-plus mr-1"></i>Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>
