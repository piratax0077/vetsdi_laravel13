{{-- Modal para agregar o editar un lugar de atención; lo abre abrir_modal_sucursal() en sucursales --}}
@php($regiones_sucursal = \App\Models\Region::orderBy('nombre')->get())
<div id="modal_sucursal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_sucursal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form id="form_sucursal" novalidate>
                @csrf
                {{-- Solo se envía al editar --}}
                <input type="hidden" name="id" disabled>
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="titulo_modal_sucursal">Agregar lugar de atención</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    {{-- Aviso mientras el guardado de lugares de atención no exista --}}
                    <div class="alert alert-info d-none" id="aviso_sucursal" role="status"></div>
                    <div class="row">
                        {{-- Datos del lugar a la izquierda y su horario a la derecha, como en Mi perfil --}}
                        <div class="col-12 col-lg-6">
                            <div class="form-row">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Información general</h6>
                                </div>
                                <div class="form-group col-12">
                                    <label class="floating-label-activo-sm" for="sucursal_nombre">Nombre del lugar</label>
                                    <input type="text" class="form-control form-control-sm" id="sucursal_nombre" name="nombre" placeholder="Ej.: Peluquería centro" maxlength="150" required>
                                    <div class="invalid-feedback">Escribe el nombre del lugar.</div>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Contacto</h6>
                                </div>
                                <div class="form-group col-12">
                                    <label class="floating-label-activo-sm" for="sucursal_telefono">Teléfono</label>
                                    <input type="text" class="form-control form-control-sm" id="sucursal_telefono" name="telefono" placeholder="+56 9 1234 5678" maxlength="30" required>
                                    <div class="invalid-feedback">Escribe un teléfono de contacto.</div>
                                </div>
                                <div class="form-group col-12">
                                    <label class="floating-label-activo-sm" for="sucursal_email">Correo electrónico</label>
                                    <input type="email" class="form-control form-control-sm" id="sucursal_email" name="email" placeholder="lugar@correo.cl" maxlength="255">
                                    <div class="invalid-feedback">Revisa el formato del correo.</div>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Ubicación</h6>
                                </div>
                                <div class="form-group col-12">
                                    <label class="floating-label-activo-sm" for="sucursal_direccion">Dirección</label>
                                    <input type="text" class="form-control form-control-sm" id="sucursal_direccion" name="direccion" placeholder="Calle y número" maxlength="255" required>
                                    <div class="invalid-feedback">Escribe la dirección.</div>
                                </div>
                                <div class="form-group col-12 col-sm-6">
                                    <label class="floating-label-activo-sm" for="sucursal_region">Región</label>
                                    <select class="form-control form-control-sm" id="sucursal_region" name="id_region" required>
                                        <option value="">Seleccione</option>
                                        @foreach ($regiones_sucursal as $region)
                                            <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Selecciona la región.</div>
                                </div>
                                <div class="form-group col-12 col-sm-6">
                                    <label class="floating-label-activo-sm" for="sucursal_ciudad">Comuna</label>
                                    <select class="form-control form-control-sm" id="sucursal_ciudad" name="id_ciudad" required>
                                        <option value="">Primero seleccione la región</option>
                                    </select>
                                    <div class="invalid-feedback">Selecciona la comuna.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            @include('app.peluqueria.horario_atencion', ['prefijo' => 'sucursal_horario', 'horario' => [], 'modo' => null])
                        </div>
                    </div>
                    {{-- Estado al final: encendido el lugar queda activo; apagado se envía 0 --}}
                    <div class="bloque-estado">
                        <input type="hidden" name="activo" value="0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="sucursal_activo" name="activo" value="1" checked>
                            <label class="custom-control-label" for="sucursal_activo">Lugar activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                    <button type="submit" class="btn btn-info" id="boton_guardar_sucursal"><i class="feather icon-plus mr-1"></i>Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>
