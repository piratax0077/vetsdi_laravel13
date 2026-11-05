{{-- Tutoría compartida: lista de personas con acceso a la mascota y modal para invitar.
     El script va en partials/tutoria_compartida/script.blade.php --}}
<div class="modal fade" id="modal_tutoria_compartida" tabindex="-1" role="dialog"
    aria-labelledby="modalTutoriaCompartidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title mb-1" id="modalTutoriaCompartidaLabel">Tutoría compartida</h5>
                    <small>Comparte el cuidado de tus mascotas con otra persona</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="tutoria_mascota_id">Mascota</label>
                    <select class="form-control form-control-sm" id="tutoria_mascota_id"></select>
                </div>

                <div class="tutoria-lista-titulo">
                    Personas con acceso a <span class="tutoria-nombre-mascota">tu mascota</span>
                </div>
                <div class="tutoria-lista" id="tutoria_lista" aria-live="polite"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-info" id="btn_tutoria_invitar">
                    <i class="feather icon-user-plus"></i> Invitar co-tutor
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_invitar_cotutor" tabindex="-1" role="dialog"
    aria-labelledby="modalInvitarCotutorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title mb-1" id="modalInvitarCotutorLabel">Invita a un co-tutor</h5>
                    <small>Para cuidar de <span class="tutoria-nombre-mascota">tu mascota</span></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_invitar_cotutor" novalidate>
                <div class="modal-body">
                    <p class="tutoria-descripcion">
                        Comparte la tutoría de <span class="tutoria-nombre-mascota">tu mascota</span> con otra persona.
                        Podrá ver y gestionar su información.
                    </p>

                    <div class="form-group mb-1">
                        <label class="floating-label-activo-sm" for="invitacion_destino">Correo electrónico o RUT</label>
                        <input type="text" class="form-control" id="invitacion_destino" name="destino"
                            placeholder="nombre@correo.com" autocomplete="off" inputmode="email"
                            aria-describedby="invitacion_destino_ayuda">
                    </div>
                    <small class="form-text text-muted" id="invitacion_destino_ayuda">
                        Le enviaremos un correo para que acepte la invitación. Si ya tiene cuenta, también puedes buscarla por su RUT.
                    </small>
                    <small class="traspaso-error d-none" id="invitacion_destino_error" role="alert"></small>

                    {{-- Persona encontrada por RUT --}}
                    <div class="traspaso-tutor d-none" id="invitacion_persona">
                        <span class="traspaso-tutor-avatar"><i class="feather icon-user"></i></span>
                        <div class="traspaso-tutor-datos">
                            <strong class="traspaso-tutor-nombre" id="invitacion_persona_nombre">-</strong>
                            <span class="traspaso-tutor-rut" id="invitacion_persona_correo"></span>
                        </div>
                        <span class="traspaso-tutor-estado"><i class="feather icon-check"></i> Con cuenta</span>
                    </div>

                    <div class="tutoria-roles-titulo">Permisos</div>
                    <div class="reserva-vet-lugares tutoria-roles" role="radiogroup" aria-label="Permisos">
                        <label class="reserva-vet-lugar">
                            <input type="radio" class="reserva-vet-lugar-radio" name="rol" value="co_tutor" checked>
                            <span class="reserva-vet-lugar-caja">
                                <span class="reserva-vet-lugar-icono"><i class="feather icon-users"></i></span>
                                <span class="reserva-vet-lugar-datos">
                                    <span class="reserva-vet-lugar-nombre">Co-tutor</span>
                                    <span class="reserva-vet-lugar-direccion">
                                        Comparte la responsabilidad. Puede ver y editar toda la información de <span class="tutoria-nombre-mascota">tu mascota</span>.
                                    </span>
                                </span>
                                <span class="reserva-vet-lugar-marca"><i class="feather icon-check"></i></span>
                            </span>
                        </label>
                        <label class="reserva-vet-lugar">
                            <input type="radio" class="reserva-vet-lugar-radio" name="rol" value="cuidador">
                            <span class="reserva-vet-lugar-caja">
                                <span class="reserva-vet-lugar-icono"><i class="feather icon-user-check"></i></span>
                                <span class="reserva-vet-lugar-datos">
                                    <span class="reserva-vet-lugar-nombre">Cuidador</span>
                                    <span class="reserva-vet-lugar-direccion">
                                        Ayuda con el día a día. Puede ver la ficha, agendar citas y llevar a <span class="tutoria-nombre-mascota">tu mascota</span> al veterinario o de paseo.
                                    </span>
                                </span>
                                <span class="reserva-vet-lugar-marca"><i class="feather icon-check"></i></span>
                            </span>
                        </label>
                    </div>

                    <div class="traspaso-aviso">
                        <i class="feather icon-clock"></i>
                        <span>La invitación vence en 7 días. Puedes cancelarla o reenviarla cuando quieras.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info" id="btn_enviar_invitacion">Enviar invitación</button>
                </div>
            </form>
        </div>
    </div>
</div>
