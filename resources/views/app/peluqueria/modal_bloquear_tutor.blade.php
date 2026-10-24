{{-- Confirmación para bloquear a un tutor; la abre abrir_bloquear_tutor() en Mascotas y tutores --}}
<div id="modal_bloquear_tutor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_bloquear_tutor" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="titulo_modal_bloquear_tutor"><i class="feather icon-lock mr-1"></i>Bloquear tutor</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                {{-- Aviso mientras el bloqueo de tutores no exista --}}
                <div class="alert alert-info d-none" id="aviso_bloquear_tutor" role="status"></div>
                <p class="mb-1 text-muted">Tutor a bloquear</p>
                {{-- El mismo contador de Notas internas; queda en 0 mientras las notas no se guarden --}}
                @php($veces_conflictivo_bloqueo = 0)
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                    <h5 class="mb-0 mr-2 font-weight-bold" id="bloquear_tutor_nombre">-</h5>
                    <span class="badge badge-light-danger f-12 px-2 py-1 mt-1" title="Notas que lo marcaron como conflictivo">
                        <i class="feather icon-alert-triangle mr-1" aria-hidden="true"></i><strong>{{ $veces_conflictivo_bloqueo }}</strong> {{ $veces_conflictivo_bloqueo === 1 ? 'vez conflictivo' : 'veces conflictivo' }}
                    </span>
                </div>
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="bloquear_tutor_motivo">Motivo de bloqueo</label>
                    <input type="text" class="form-control form-control-sm" id="bloquear_tutor_motivo" name="motivo" maxlength="255" placeholder="Ej.: No se presentó a sus horas" required>
                    <div class="invalid-feedback">Escribe el motivo del bloqueo.</div>
                </div>
                <div class="aviso-sin-registros aviso-sin-registros--peligro">
                    <span class="aviso-sin-registros-icono"><i class="feather icon-alert-triangle"></i></span>
                    <div>
                        <p class="aviso-sin-registros-titulo">No podrá agendar nuevas horas</p>
                        <p class="aviso-sin-registros-texto">Mientras esté bloqueado no se le podrán reservar atenciones en tu peluquería. Sus mascotas y el historial de atenciones se mantienen.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                <button type="button" class="btn btn-danger" id="boton_bloquear_tutor"><i class="feather icon-lock mr-1"></i>Bloquear tutor</button>
            </div>
        </div>
    </div>
</div>
