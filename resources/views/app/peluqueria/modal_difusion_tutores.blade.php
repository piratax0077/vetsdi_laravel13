{{-- Mensaje de difusión a los tutores, con el mismo diseño del profesional.
     Se envía desde el correo del equipo con todos los tutores en copia oculta; lo abre abrir_difusion_tutores() --}}
@php($correos_difusion = $clientes->pluck('email')->filter()->map(fn ($correo) => mb_strtolower(trim($correo)))->unique()->values())
<div class="modal fade" id="modal_difusion_tutores" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_difusion_tutores" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="titulo_modal_difusion_tutores">Mensaje difusión a tutores</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <form id="form_difusion_tutores" novalidate data-correos="{{ $correos_difusion->implode(',') }}">
                    <div class="form-row">
                        <div class="col-12">
                            <p class="small text-muted mb-3">
                                @if($correos_difusion->isEmpty())
                                    Ningún tutor tiene correo registrado todavía.
                                @else
                                    Se enviará a {{ $correos_difusion->count() === 1 ? '1 tutor' : $correos_difusion->count() . ' tutores' }} con correo registrado, en copia oculta.
                                @endif
                            </p>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="difusion_asunto">Asunto</label>
                            <input type="text" class="form-control form-control-sm" id="difusion_asunto" maxlength="150" required>
                            <div class="invalid-feedback">Escribe el asunto.</div>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="difusion_mensaje">Mensaje</label>
                            <textarea class="form-control form-control-sm" id="difusion_mensaje" rows="5" maxlength="1500" required></textarea>
                            <div class="invalid-feedback">Escribe el mensaje.</div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-info" @disabled($correos_difusion->isEmpty())><i class="feather icon-mail"></i> Enviar mensaje</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
