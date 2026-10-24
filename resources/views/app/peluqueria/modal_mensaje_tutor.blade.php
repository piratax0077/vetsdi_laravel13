{{-- Mensaje al tutor: se escribe aquí y se envía desde el correo o WhatsApp del equipo; lo abre abrir_mensaje_tutor() --}}
<div id="modal_mensaje_tutor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_mensaje_tutor" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form_mensaje_tutor" novalidate>
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="titulo_modal_mensaje_tutor">Mensaje al tutor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning d-none" id="mensaje_tutor_sin_medio" role="status">Este tutor no tiene correo ni celular registrados. Puedes llamarlo al teléfono de contacto.</div>
                    <div class="form-row">
                        <div class="col-12">
                            <h6 class="titulo-sm">Para</h6>
                            <p class="mb-3">
                                <strong id="mensaje_tutor_nombre">-</strong>
                                <span class="d-block text-muted small" id="mensaje_tutor_contacto">-</span>
                            </p>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="mensaje_tutor_asunto">Asunto</label>
                            <input type="text" class="form-control form-control-sm" id="mensaje_tutor_asunto" name="asunto" maxlength="150">
                        </div>
                        <div class="form-group col-12 mb-0">
                            <label class="floating-label-activo-sm" for="mensaje_tutor_texto">Mensaje</label>
                            <textarea class="form-control form-control-sm" id="mensaje_tutor_texto" name="mensaje" rows="4" maxlength="1500" required></textarea>
                            <div class="invalid-feedback">Escribe el mensaje.</div>
                        </div>
                    </div>
                </div>
                {{-- Los tres botones en una sola línea; en celular dicen solo "WhatsApp" y "Correo" para caber --}}
                <div class="modal-footer pie-botones-en-linea">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cerrar</button>
                    <button type="button" class="btn btn-success boton-enviar-mensaje" data-via="whatsapp" id="boton_mensaje_whatsapp"><i class="feather icon-message-circle mr-1"></i><span class="d-none d-sm-inline">Enviar por </span>WhatsApp</button>
                    <button type="button" class="btn btn-info boton-enviar-mensaje" data-via="correo" id="boton_mensaje_correo"><i class="feather icon-mail mr-1"></i><span class="d-none d-sm-inline">Enviar por correo</span><span class="d-sm-none">Correo</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
