<div class="row">
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
        <!--CARD CONTRASEÑA PERSONAL-->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between bg-white">
                <h5 class="mb-0"><i class="feather icon-lock icono-purple"></i>Contraseña personal</h5>
                <button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".pass_personal" aria-expanded="false" aria-controls="pass_personal_1 pass_personal_2">
                    <i class="feather icon-edit"></i>
                </button>
            </div>
            <!--CONTRASEÑA PERSONAL-->
            <div class="card-body pass_personal collapse show" id="pass_personal_1">
                <form>
                    <div class="form-row">
                        <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <label class="font-weight-bolder ml-0 mb-0">Contraseña actual</label>
                            <div> •••••••• </div>
                        </div>
                    </div>
                </form>
            </div>
            <!--CIERRE: CONTRASEÑA PERSONAL-->
            <!--(EDITAR)CONTRASEÑA PERSONAL-->
            <div class="card-body border-top pass_personal collapse" id="pass_personal_2">
                <form method="get" action="{{ route('perfil.cambio_contrasena')}}" id="form_cambio_contrasena_perfil" name="form_cambio_contrasena_perfil">
                    @csrf
                    <input type="hidden" name="contrasena_mail" id="contrasena_mail" value="{{ Auth::user()->email }}">
                    <div class="form-row">
                        <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <label class="floating-label-activo">Contraseña actual</label>
                            <div class="campo-clave">
                                <input type="password" class="form-control form-control-sm" id="contrasena_actual" name="contrasena_actual">
                                <button type="button" class="campo-clave-ver" data-ver-clave="contrasena_actual" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <label class="floating-label-activo">Nueva contraseña</label>
                            <div class="campo-clave">
                                <input type="password" class="form-control form-control-sm" id="password_registro" name="password_registro">
                                <button type="button" class="campo-clave-ver" data-ver-clave="password_registro" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <label class="floating-label-activo">Repita nueva contraseña</label>
                            <div class="campo-clave">
                                <input type="password" class="form-control form-control-sm" id="password_confirmacion_registro" name="password_confirmacion_registro">
                                <button type="button" class="campo-clave-ver" data-ver-clave="password_confirmacion_registro" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-outline-dark mr-2"><i class="feather icon-x"></i> Cancelar</button>
                            <button type="submit" class="btn btn-sm btn-info"><i class="feather icon-save"></i> Guardar cambios</button>
                        </div>
                    </div>
                </form>
            </div>
            <!--CIERRE: (EDITAR)CONTRASEÑA PERSONAL-->
        </div>
        <!--CIERRE: CARD CONTRASEÑA PERSONAL-->
    </div>
</div>

<script>
    // Muestra u oculta la contraseña al presionar el ojo del campo
    document.addEventListener('click', function (evento) {
        var boton = evento.target.closest ? evento.target.closest('.campo-clave-ver') : null;
        if (!boton) return;

        var campo = document.getElementById(boton.getAttribute('data-ver-clave'));
        if (!campo) return;

        var visible = campo.type === 'password';
        campo.type = visible ? 'text' : 'password';
        boton.setAttribute('aria-pressed', visible ? 'true' : 'false');
        boton.setAttribute('aria-label', visible ? 'Ocultar la contraseña' : 'Mostrar la contraseña');
        boton.innerHTML = '<i class="feather ' + (visible ? 'icon-eye' : 'icon-eye-off') + '" aria-hidden="true"></i>';
    });
</script>
