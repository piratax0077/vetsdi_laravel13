{{-- Modal para agregar o editar una sala o box; lo abre abrir_modal_sala() en configuracion --}}
<div id="modal_sala" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_sala" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form_sala" class="form-registro-pendiente" data-aviso="El registro de salas y boxes aún no está disponible." novalidate>
                @csrf
                {{-- Solo lleva valor al editar; al agregar va deshabilitado para que no se envíe --}}
                <input type="hidden" name="id" value="" disabled>
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="titulo_modal_sala">Agregar sala o box</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    {{-- Aviso mientras el registro de salas y boxes no exista --}}
                    <div class="alert alert-info d-none aviso-registro" role="status"></div>
                    <div class="form-row">
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="sala_nombre">Nombre</label>
                            <input type="text" class="form-control form-control-sm" id="sala_nombre" name="nombre" placeholder="Ej.: Box 1" maxlength="150" required>
                            <div class="invalid-feedback">Escribe el nombre de la sala o box.</div>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="sala_tipo">Tipo</label>
                            <select class="form-control form-control-sm" id="sala_tipo" name="tipo" required>
                                <option value="">Seleccione</option>
                                <option>Sala</option>
                                <option>Box</option>
                            </select>
                            <div class="invalid-feedback">Selecciona el tipo.</div>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="sala_uso">Uso</label>
                            <input type="text" class="form-control form-control-sm" id="sala_uso" name="uso" placeholder="Ej.: Baño y secado" maxlength="150">
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="sala_sucursal">Lugar de atención</label>
                            <select class="form-control form-control-sm" id="sala_sucursal" name="sucursal">
                                {{-- Los mismos lugares del selector del panel; la casa matriz va primero --}}
                                <option value="">Seleccione</option>
                                @foreach ($lugares ?? [] as $lugar)
                                    <option value="{{ $lugar['valor'] }}">{{ $lugar['nombre'] }}{{ $lugar['matriz'] ? ' (Casa matriz)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- Estado al final: encendido queda disponible; apagado se envía 0 --}}
                    <div class="bloque-estado">
                        <input type="hidden" name="activo" value="0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input interruptor-estado" id="sala_activo" name="activo" value="1" data-texto-si="Disponible" data-texto-no="No disponible" checked>
                            <label class="custom-control-label" for="sala_activo">Disponible</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                    <button type="submit" class="btn btn-info" id="boton_guardar_sala"><i class="feather icon-plus mr-1"></i>Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>
