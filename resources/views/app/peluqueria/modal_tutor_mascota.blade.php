{{-- Modal para registrar un tutor con su mascota; lo abre abrir_modal_tutor_mascota() en Mascotas y tutores --}}
@php
    $error_tutor_mascota = $errors->any() && old('mascota') !== null;
    // Si el guardado vuelve con error, "Exótico (Conejo)" se separa otra vez en especie y tipo de exótico
    $especie_anterior = (string) old('especie');
    $exotico_anterior = old('especie_exotico');
    if (preg_match('/^ex[oó]tico\s*\((.+)\)$/iu', $especie_anterior, $partes)) {
        $especie_anterior = 'Exótico';
        $exotico_anterior = $exotico_anterior ?: $partes[1];
    }
@endphp
<div id="modal_tutor_mascota" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_tutor_mascota" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="form_tutor_mascota" method="post" action="{{ route('peluqueria.clientes') }}" novalidate>
                @csrf
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="titulo_modal_tutor_mascota">Registrar tutor y mascota</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    @if($error_tutor_mascota)
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="form-row">
                        <div class="col-12">
                            <h6 class="titulo-sm">Datos del tutor</h6>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="cliente_rut">RUT</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_rut" name="rut" value="{{ old('rut') }}" placeholder="12.345.678-9" maxlength="12">
                        </div>
                        {{-- El guardado recibe un solo "tutor": se arma con nombre y apellidos al enviar --}}
                        <input type="hidden" id="cliente_tutor" name="tutor" value="{{ old('tutor') }}">
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="cliente_nombres">Nombre</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_nombres" name="nombres" value="{{ old('nombres') }}" maxlength="150" required>
                            <div class="invalid-feedback">Escribe el nombre del tutor.</div>
                        </div>
                        <div class="form-group col-12 col-lg-6">
                            <label class="floating-label-activo-sm" for="cliente_apellido_uno">Primer apellido</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_apellido_uno" name="apellido_uno" value="{{ old('apellido_uno') }}" maxlength="100" required>
                            <div class="invalid-feedback">Escribe el primer apellido del tutor.</div>
                        </div>
                        <div class="form-group col-12 col-lg-6">
                            <label class="floating-label-activo-sm" for="cliente_apellido_dos">Segundo apellido</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_apellido_dos" name="apellido_dos" value="{{ old('apellido_dos') }}" maxlength="100">
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label class="floating-label-activo-sm" for="cliente_telefono">Teléfono</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_telefono" name="telefono" value="{{ old('telefono') }}" placeholder="+56 9 1234 5678" maxlength="30" required>
                            <div class="invalid-feedback">Escribe un teléfono de contacto.</div>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label class="floating-label-activo-sm" for="cliente_email">Correo electrónico</label>
                            <input type="email" class="form-control form-control-sm" id="cliente_email" name="email" value="{{ old('email') }}" placeholder="nombre@correo.cl" maxlength="255">
                            <div class="invalid-feedback">Revisa el formato del correo.</div>
                        </div>
                    </div>
                    <div class="form-row mt-2">
                        <div class="col-12">
                            <h6 class="titulo-sm">Datos de la mascota</h6>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="cliente_mascota">Nombre de la mascota</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_mascota" name="mascota" value="{{ old('mascota') }}" maxlength="100" required>
                            <div class="invalid-feedback">Escribe el nombre de la mascota.</div>
                        </div>
                        {{-- Una mascota exótica se guarda como "Exótico (Conejo)", igual que al agendar hora;
                             el valor que se envía lo arma el formulario en el campo oculto "especie" --}}
                        <input type="hidden" id="cliente_especie_valor" name="especie" value="{{ old('especie') }}">
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="cliente_especie">Especie</label>
                            <select class="form-control form-control-sm" id="cliente_especie" required>
                                <option value="">Seleccione</option>
                                @foreach(['Canino', 'Felino', 'Exótico'] as $especie)
                                    <option value="{{ $especie }}" @selected($especie_anterior === $especie)>{{ $especie }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Selecciona la especie.</div>
                        </div>
                        {{-- Con "Exótico" se escribe qué animal es; oculto va deshabilitado para que no se revise --}}
                        <div class="form-group col-12 {{ $especie_anterior === 'Exótico' ? '' : 'd-none' }}" id="cliente_exotico_grupo">
                            <label class="floating-label-activo-sm" for="cliente_exotico">¿Qué exótico es?</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_exotico" name="especie_exotico" value="{{ $exotico_anterior }}" placeholder="Ej.: Conejo, hurón" maxlength="45" required @disabled($especie_anterior !== 'Exótico')>
                            <div class="invalid-feedback">Escribe qué animal exótico es.</div>
                        </div>
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="cliente_raza">Raza (opcional)</label>
                            <input type="text" class="form-control form-control-sm" id="cliente_raza" name="raza" value="{{ old('raza') }}" placeholder="Ej.: Poodle" maxlength="100">
                        </div>
                        <div class="form-group col-12 mb-0">
                            <label class="floating-label-activo-sm" for="cliente_cuidados">Cuidados y advertencias del tutor</label>
                            <textarea class="form-control form-control-sm" id="cliente_cuidados" name="cuidados" rows="3" maxlength="3000" placeholder="Alergias, temores, zonas sensibles u otras indicaciones">{{ old('cuidados') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                    <button type="submit" class="btn btn-info" id="boton_guardar_tutor_mascota"><i class="feather icon-plus mr-1"></i>Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
