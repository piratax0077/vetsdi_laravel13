{{-- Confirmación para bloquear a un tutor; la abre abrir_bloquear_tutor() desde la pantalla del tutor
     o sola al guardar la tercera nota conflictiva --}}
<div id="modal_bloquear_tutor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_bloquear_tutor" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-danger">
                <h5 class="modal-title" id="titulo_modal_bloquear_tutor"><i class="feather icon-lock mr-1"></i>Bloquear tutor</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                {{-- Se muestra cuando el modal se abre solo por las notas conflictivas --}}
                <div class="alert alert-danger d-none" id="aviso_tres_conflictivas" role="alert">
                    Este tutor ya suma <strong class="js-veces-conflictivo">3</strong> notas conflictivas. Te recomendamos bloquearlo.
                </div>
                <p class="mb-1 text-muted">Tutor a bloquear</p>
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                    <h5 class="mb-0 mr-2 font-weight-bold">{{ $nombreTutor }}</h5>
                    <span class="badge badge-light-danger f-12 px-2 py-1 mt-1" title="Notas que lo marcaron como conflictivo">
                        <i class="feather icon-alert-triangle mr-1" aria-hidden="true"></i><strong class="js-veces-conflictivo">{{ $notasTutor->where('conflictivo', true)->count() }}</strong>
                        <span class="js-veces-conflictivo-texto">{{ $notasTutor->where('conflictivo', true)->count() === 1 ? 'vez conflictivo' : 'veces conflictivo' }}</span>
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
                        <p class="aviso-sin-registros-texto">Mientras esté bloqueado no se le podrán reservar horas contigo. Sus mascotas y el historial de atenciones se mantienen.</p>
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
