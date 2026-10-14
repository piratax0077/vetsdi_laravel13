{{-- Pantalla para escribir la contraseña nueva. Se llega desde el enlace del correo o al validar el código del celular. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <title>VET-SDI</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="SDI" />
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}?t={{ time() }}">
</head>
<style type="text/css">
    .auth-wrapper {
        background-size: cover;
        background-image: url("{{ asset('images/background_1.jpg') }}");
        background-position: center center;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
</style>

<body>
    <div class="auth-wrapper">
        <div class="auth-content">
            <div class="card text-center">
                <div class="card-body">
                    <img src="{{ asset('images/logo_pais_vertical.png') }}" alt="" class="img-fluid mb-2 wid-120">
                    <h5 class="mb-3">Crea tu contraseña nueva</h5>

                    <p class="recuperar-texto">
                        Será la contraseña de la cuenta <strong>{{ old('email', $request->email) }}</strong>.
                    </p>

                    <form method="POST" action="{{ route('password.update') }}" id="form_clave_nueva" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                        <div class="registro-paso">
                            <div class="form-group mb-3">
                                <label class="floating-label-activo-sm" for="password">Contraseña nueva</label>
                                <div class="campo-clave">
                                    <input type="password" class="form-control form-control-sm @error('password') es-invalido @enderror" name="password" id="password" autocomplete="new-password" aria-describedby="ayuda-password" autofocus>
                                    <button type="button" class="campo-clave-ver" data-ver-clave="password" aria-label="Mostrar la contraseña" aria-pressed="false" hidden><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                                </div>
                                <small class="ingreso-ayuda" id="ayuda-password">Al menos 8 caracteres, con una mayúscula, una minúscula y un número.</small>
                                <small class="mensaje-error" data-error-de="password" role="alert" @unless($errors->has('password')) hidden @endunless>{{ $errors->first('password') }}</small>
                            </div>

                            <div class="form-group mb-0">
                                <label class="floating-label-activo-sm" for="password_confirmation">Repetir contraseña</label>
                                <div class="campo-clave">
                                    <input type="password" class="form-control form-control-sm" name="password_confirmation" id="password_confirmation" autocomplete="new-password">
                                    <button type="button" class="campo-clave-ver" data-ver-clave="password_confirmation" aria-label="Mostrar la contraseña" aria-pressed="false" hidden><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                                </div>
                                <small class="mensaje-error" data-error-de="password_confirmation" role="alert" hidden></small>
                            </div>
                        </div>

                        <div class="registro-acciones">
                            <button type="submit" class="btn boton-morado" id="btn-cambiar-clave">Cambiar contraseña</button>
                        </div>
                    </form>

                    <p class="ingreso-mas-info text-muted mb-0">
                        <a href="{{ route('home.ingreso') }}" class="ingreso-enlace">Volver al ingreso</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/login/ver_clave.js') }}?t={{ time() }}"></script>
    <script src="{{ asset('js/login/clave_nueva.js') }}?t={{ time() }}"></script>
</body>

</html>
