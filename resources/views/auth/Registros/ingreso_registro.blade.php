@php
    use App\Services\CuentasService;

    // Se vuelve a abrir el registro cuando algo falló, para no perder lo escrito.
    $mostrarRegistro = old('tipo_cuenta') !== null || session('cuenta_existente') !== null;
    $tipoElegido = old('tipo_cuenta');

    // Los errores solo se pintan en el registro si el envío vino de ahí:
    // el ingreso comparte los nombres "email" y "password".
    $errorDe = fn (string $campo) => $mostrarRegistro ? $errors->first($campo) : '';

    // El registro se reabre en el paso donde quedó el primer problema.
    $pasoInicial = 1;

    if ($mostrarRegistro && ! $errors->has('tipo_cuenta')) {
        if ($errors->hasAny(['rut', 'nombres', 'apellido_uno', 'apellido_dos', 'email', 'telefono']) || session('cuenta_existente') !== null) {
            $pasoInicial = 2;
        } elseif ($errors->has('password')) {
            $pasoInicial = 3;
        }
    }

    // Recuperación de contraseña: opción que queda a la vista al volver del
    // servidor (correo, telefono o codigo). Sin valor, se muestra el ingreso.
    $recuperar = session('recuperar') ?? old('recuperar_por');
    $telefonoPendiente = session('recuperacion_telefono.telefono');

    if ($recuperar === 'codigo' && ! $telefonoPendiente) {
        $recuperar = 'telefono';
    }

    $recuperarPorCelular = in_array($recuperar, ['telefono', 'codigo'], true);
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <title>VET-SDI</title>
    <!--[if lt IE 11]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="SDI" />
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins/select2.min.css') }}">
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
    <div class="barra-superior">
        <div class="barra-superior-datos">
            <div class="barra-superior-redes">
                <a class="boton-red" href="#" aria-label="Facebook"><i class="feather icon-facebook"></i></a>
                <a class="boton-red" href="#" aria-label="Instagram"><i class="feather icon-instagram"></i></a>
            </div>
            <a class="barra-superior-correo" href="mailto:contacto@veterchile.cl"><i class="feather icon-mail"></i><span class="barra-superior-correo-texto">contacto@veterchile.cl</span></a>
        </div>
        <a class="barra-superior-inicio" href="#"><i class="feather icon-home"></i> Inicio Veterchile</a>
    </div>

    <header class="menu-flotante">
        <a class="menu-flotante-logo" href="#" aria-label="Vet SDI, ir al inicio">
            <img src="{{ asset('images/logo_pais_vertical.png') }}" alt="Vet SDI">
        </a>
        <nav class="menu-flotante-acciones" aria-label="Navegación principal">
            <a class="menu-flotante-enlace" href="#">Inicio Veterchile</a>
        </nav>
    </header>

    <div class="blur-bg-images"></div>
    <div class="auth-wrapper">
        <div class="auth-content">
            <!-- Ingreso a VETERCHILE -->
            <div class="card text-center" id="ingreso" @if($recuperar) hidden @endif>
                <div class="card-body">
                    <img src="{{ asset('images/logo_pais_vertical.png') }}" alt="" class="img-fluid mb-2 wid-120">
                    <h5 class="mb-4">¡Bienvenido a Veterchile!</h5>
                    <!-- mensaje -->
                    <div class="row div_mensaje">
                        @if(session('mensaje'))
                            <span class="col-sm-12 alert alert-success" role="status">{{ session('mensaje') }}</span>
                        @endif
                        @if(session('mensaje_error'))
                            <span class="col-sm-12 alert alert-warning" role="alert">{{ session('mensaje_error') }}</span>
                        @endif
                        @if(session('cuenta_existente'))
                            @php($cuentaExistente = session('cuenta_existente'))
                            <div class="col-sm-12 alert alert-warning text-left" role="alert">
                                Ya tienes una cuenta en Veterchile.
                                <a href="#" class="ingreso-enlace" data-ir-a="ingreso">Ingresa</a>
                                o
                                <a href="#" class="ingreso-enlace" onclick="activar_recuperacion(); return false;">recupera tu contraseña</a>.
                                @unless($cuentaExistente['verificado'])
                                    <br>
                                    Tu correo todavía no está confirmado.
                                    <form method="POST" action="{{ route('registro.reenviar') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0 ingreso-enlace align-baseline">Reenviar correo de confirmación</button>
                                    </form>
                                @endunless
                            </div>
                        @endif
                    </div>

                    <!-- Ingreso -->
                    <div class="bloque-acceso" id="panel-ingreso" @if($mostrarRegistro) hidden @endif>
                        <div class="form-group mb-3">

                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="floating-label-activo-sm" for="email">Ingrese email o RUT</label>
                                    <input type="text" class="form-control @error('email') es-invalido @enderror" name="email" id="email" value="{{ old('email') }}" autocomplete="username" aria-describedby="ayuda-email">
                                    <small class="ingreso-ayuda" id="ayuda-email">Escriba correo electrónico o RUT sin puntos y con guión</small>
                                    @error('email')
                                        <small class="mensaje-error" role="alert">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label class="floating-label-activo-sm" for="password">Ingrese su contraseña</label>
                                    <div>
                                        <input type="password" class="form-control" name="password" id="password" value="" style="padding-right: 40px;" autocomplete="current-password">
                                        <span id="toggle-password" role="button" tabindex="0" aria-label="Mostrar u ocultar la contraseña" onclick="togglePassword()"
                                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; user-select: none;">
                                            <i class="feather icon-eye-off"></i>
                                        </span>
                                    </div>
                                    @error('password')
                                        <small class="mensaje-error" role="alert">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <a href="#" class="ingreso-enlace ingreso-enlace-chico" onclick="activar_recuperacion(); return false;">¿Olvidó su contraseña?</a>
                                </div>
                                <button class="btn btn-info mb-4" id="btn-ingresar">Ingresar</button>
                            </form>

                            <p class="ingreso-mas-info text-muted mb-0">¿Quieres conocer más?
                                <a href="#" class="ingreso-enlace" data-ir-a="registro">Crea tu cuenta</a>
                                o
                                <a href="https://www.medichile.cl/sdinicio/" class="ingreso-enlace" target="_blank" rel="noopener noreferrer">descúbrelo aquí</a>
                            </p>
                        </div>

                    </div>
                    <!-- Cierre:Ingreso -->

                    <!-- Registro: tres pasos dentro de la misma tarjeta -->
                    <div class="bloque-acceso" id="panel-registro" @unless($mostrarRegistro) hidden @endunless>
                        <form method="post" action="{{ route('registro.cuenta') }}" id="form_registro" name="form_registro" data-paso-inicial="{{ $pasoInicial }}" novalidate>
                            @csrf

                            <!-- Avance del registro. Sin JavaScript queda oculto y los tres pasos se ven uno bajo el otro. -->
                            <ol class="registro-avance" id="registro-avance" aria-label="Pasos del registro" hidden>
                                <li class="registro-avance-paso">
                                    <button type="button" class="registro-avance-boton" data-ir-a-paso="1">
                                        <span class="registro-avance-numero" aria-hidden="true">1</span>
                                        <span class="registro-avance-nombre">Cuenta</span>
                                    </button>
                                </li>
                                <li class="registro-avance-paso">
                                    <button type="button" class="registro-avance-boton" data-ir-a-paso="2">
                                        <span class="registro-avance-numero" aria-hidden="true">2</span>
                                        <span class="registro-avance-nombre">Tus datos</span>
                                    </button>
                                </li>
                                <li class="registro-avance-paso">
                                    <button type="button" class="registro-avance-boton" data-ir-a-paso="3">
                                        <span class="registro-avance-numero" aria-hidden="true">3</span>
                                        <span class="registro-avance-nombre">Contraseña</span>
                                    </button>
                                </li>
                            </ol>

                            <!-- Paso 1: tipo de cuenta -->
                            <fieldset class="registro-paso" data-paso="1">
                                <legend class="registro-paso-titulo">Elige tu tipo de cuenta</legend>

                                <div class="form-group mb-0">
                                    <label class="sr-only" for="tipo_cuenta">Tipo de cuenta</label>
                                    <select class="form-control form-control-sm @if($errorDe('tipo_cuenta')) es-invalido @endif" name="tipo_cuenta" id="tipo_cuenta" style="width: 100%;">
                                        <option value="" @selected(! $tipoElegido)>Selecciona un tipo de cuenta</option>
                                        @foreach(CuentasService::TIPOS as $tipo)
                                            <option value="{{ $tipo }}" data-resena="{{ CuentasService::RESENAS[$tipo] }}" @selected($tipoElegido === $tipo)>{{ CuentasService::ETIQUETAS[$tipo] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="registro-resena" id="registro-resena" @unless($tipoElegido) hidden @endunless aria-live="polite">{{ $tipoElegido ? CuentasService::RESENAS[$tipoElegido] : '' }}</p>

                                <small class="mensaje-error" data-error-de="tipo_cuenta" role="alert" @unless($errorDe('tipo_cuenta')) hidden @endunless>{{ $errorDe('tipo_cuenta') }}</small>
                            </fieldset>

                            <!-- Paso 2: datos basicos, iguales para los cuatro tipos -->
                            <fieldset class="registro-paso" data-paso="2">
                                <legend class="registro-paso-titulo">Tus datos</legend>

                                <div class="form-group mb-3">
                                    <label class="floating-label-activo-sm" for="rut" id="etiqueta-rut">RUT</label>
                                    <input type="text" class="form-control form-control-sm @if($errorDe('rut')) es-invalido @endif" name="rut" id="rut" value="{{ old('rut') }}" maxlength="12" onkeyup="formatoRut(this)" aria-describedby="ayuda-rut">
                                    <small class="ingreso-ayuda" id="ayuda-rut">Se completa solo con el guion del dígito verificador.</small>
                                    <small class="mensaje-error" data-error-de="rut" role="alert" @unless($errorDe('rut')) hidden @endunless>{{ $errorDe('rut') }}</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="floating-label-activo-sm" for="nombres">Nombres</label>
                                    <input type="text" class="form-control form-control-sm @if($errorDe('nombres')) es-invalido @endif" name="nombres" id="nombres" value="{{ old('nombres') }}" maxlength="100" autocomplete="given-name">
                                    <small class="mensaje-error" data-error-de="nombres" role="alert" @unless($errorDe('nombres')) hidden @endunless>{{ $errorDe('nombres') }}</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="floating-label-activo-sm" for="apellido_uno">Apellido paterno</label>
                                    <input type="text" class="form-control form-control-sm @if($errorDe('apellido_uno')) es-invalido @endif" name="apellido_uno" id="apellido_uno" value="{{ old('apellido_uno') }}" maxlength="100" autocomplete="family-name">
                                    <small class="mensaje-error" data-error-de="apellido_uno" role="alert" @unless($errorDe('apellido_uno')) hidden @endunless>{{ $errorDe('apellido_uno') }}</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="floating-label-activo-sm" for="apellido_dos">Apellido materno <span class="texto-opcional">(opcional)</span></label>
                                    <input type="text" class="form-control form-control-sm @if($errorDe('apellido_dos')) es-invalido @endif" name="apellido_dos" id="apellido_dos" value="{{ old('apellido_dos') }}" maxlength="100">
                                    <small class="mensaje-error" data-error-de="apellido_dos" role="alert" @unless($errorDe('apellido_dos')) hidden @endunless>{{ $errorDe('apellido_dos') }}</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="floating-label-activo-sm" for="email_registro">Correo electrónico</label>
                                    <input type="email" class="form-control form-control-sm @if($errorDe('email')) es-invalido @endif" name="email" id="email_registro" value="{{ $mostrarRegistro ? old('email') : '' }}" maxlength="255" autocomplete="email">
                                    <small class="mensaje-error" data-error-de="email_registro" role="alert" @unless($errorDe('email')) hidden @endunless>{{ $errorDe('email') }}</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="floating-label-activo-sm" for="telefono">Teléfono</label>
                                    <input type="tel" class="form-control form-control-sm mask_telefono @if($errorDe('telefono')) es-invalido @endif" name="telefono" id="telefono" value="{{ old('telefono') }}" placeholder="+56 9 1234 5678" autocomplete="tel">
                                    <small class="mensaje-error" data-error-de="telefono" role="alert" @unless($errorDe('telefono')) hidden @endunless>{{ $errorDe('telefono') }}</small>
                                </div>
                            </fieldset>

                            <!-- Paso 3: contraseña y creacion de la cuenta -->
                            <fieldset class="registro-paso" data-paso="3">
                                <legend class="registro-paso-titulo">Crea tu contraseña</legend>

                                <div class="form-group mb-3">
                                    <label class="floating-label-activo-sm" for="password_registro">Contraseña</label>
                                    <div class="campo-clave">
                                        <input type="password" class="form-control form-control-sm @if($errorDe('password')) es-invalido @endif" name="password" id="password_registro" autocomplete="new-password" aria-describedby="ayuda-password">
                                        <button type="button" class="campo-clave-ver" data-ver-clave="password_registro" aria-label="Mostrar la contraseña" aria-pressed="false" hidden><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                                    </div>
                                    <small class="ingreso-ayuda" id="ayuda-password">Al menos 8 caracteres, con una mayúscula, una minúscula y un número.</small>
                                    <small class="mensaje-error" data-error-de="password_registro" role="alert" @unless($errorDe('password')) hidden @endunless>{{ $errorDe('password') }}</small>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="floating-label-activo-sm" for="password_confirmation">Repetir contraseña</label>
                                    <div class="campo-clave">
                                        <input type="password" class="form-control form-control-sm" name="password_confirmation" id="password_confirmation" autocomplete="new-password">
                                        <button type="button" class="campo-clave-ver" data-ver-clave="password_confirmation" aria-label="Mostrar la contraseña" aria-pressed="false" hidden><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                                    </div>
                                    <small class="mensaje-error" data-error-de="password_confirmation" role="alert" hidden></small>
                                </div>

                                <p class="registro-resena">
                                    Al crear tu cuenta te enviaremos un correo<span id="registro-aviso-destino"></span> para que la confirmes. Después podrás ingresar con tu RUT o tu correo.
                                </p>
                            </fieldset>

                            <div class="registro-acciones">
                                <button type="button" class="btn btn-outline-info" id="btn-paso-atras" hidden>Atrás</button>
                                <button type="button" class="btn btn-info" id="btn-paso-siguiente" hidden>Continuar</button>
                                <button type="submit" class="btn btn-info" id="btn-crear-cuenta">Crear mi cuenta</button>
                            </div>

                            <p class="ingreso-mas-info text-muted mb-0">¿Ya tienes cuenta?
                                <a href="#" class="ingreso-enlace" data-ir-a="ingreso">Ingresa</a>
                            </p>
                        </form>
                    </div>
                    <!-- Cierre: Registro -->
                </div>
            </div>
            <!-- Cierre: Ingreso a Veterchile -->


            <!-- recuperar contraseña usuario -->
            <div class="card text-center" id="recuperar" @unless($recuperar) hidden @endunless>
                <div class="card-body">
                    <img src="{{ asset('images/logo_pais_vertical.png') }}" alt="" class="img-fluid mb-2 wid-120">
                    <h5 class="mb-4">Recuperar contraseña</h5>
                    <!-- mensaje -->
                    <div class="row div_mensaje">
                        @if(session('mensaje'))
                            <span class="col-sm-12 alert alert-success" role="status">{{ session('mensaje') }}</span>
                        @endif
                        @if(session('mensaje_error'))
                            <span class="col-sm-12 alert alert-warning" role="alert">{{ session('mensaje_error') }}</span>
                        @endif
                    </div>

                    <!-- Dos caminos: un enlace al correo o un código al celular -->
                    <div class="recuperar-opciones" role="tablist" aria-label="Cómo quieres recuperar tu contraseña">
                        <button type="button" class="recuperar-opcion @unless($recuperarPorCelular) esta-activa @endunless" role="tab" id="pestana-correo" aria-controls="recuperar-correo" aria-selected="{{ $recuperarPorCelular ? 'false' : 'true' }}" data-recuperar-con="correo">
                            <i class="feather icon-mail" aria-hidden="true"></i> Por correo
                        </button>
                        <button type="button" class="recuperar-opcion @if($recuperarPorCelular) esta-activa @endif" role="tab" id="pestana-celular" aria-controls="recuperar-celular" aria-selected="{{ $recuperarPorCelular ? 'true' : 'false' }}" data-recuperar-con="celular">
                            <i class="feather icon-smartphone" aria-hidden="true"></i> Por celular
                        </button>
                    </div>

                    <!-- Por correo: llega un enlace para escribir la contraseña nueva -->
                    <div id="recuperar-correo" role="tabpanel" aria-labelledby="pestana-correo" @if($recuperarPorCelular) hidden @endif>
                        <form method="POST" action="{{ route('home.recuperar_contrasena') }}">
                            @csrf
                            <input type="hidden" name="recuperar_por" value="correo">

                            <div class="form-group mb-4">
                                <label class="floating-label-activo-sm" for="correo_recuperacion">Correo electrónico</label>
                                <input type="email" class="form-control @error('correo_recuperacion') es-invalido @enderror" name="correo_recuperacion" id="correo_recuperacion" value="{{ old('correo_recuperacion') }}" maxlength="255" autocomplete="email" aria-describedby="ayuda-correo-recuperacion" required>
                                <small class="ingreso-ayuda" id="ayuda-correo-recuperacion">Te enviaremos un enlace para crear una contraseña nueva.</small>
                                @error('correo_recuperacion')
                                    <small class="mensaje-error" role="alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="registro-acciones">
                                <button type="submit" class="btn boton-morado" id="btn-solicitar">Solicitar</button>
                                <button type="button" class="btn btn-outline-info" onclick="regresar_ingreso();">Regresar</button>
                            </div>
                        </form>
                    </div>

                    <!-- Por celular: llega un código y, al escribirlo, se pasa directo a cambiar la contraseña -->
                    <div id="recuperar-celular" role="tabpanel" aria-labelledby="pestana-celular" @unless($recuperarPorCelular) hidden @endunless>

                        @if($recuperar === 'codigo')
                            <div id="recuperar-codigo">
                                <p class="recuperar-texto">
                                    Escribe el código de 6 dígitos que enviamos por WhatsApp al <strong>{{ $telefonoPendiente }}</strong>.
                                </p>

                                <form method="POST" action="{{ route('recuperar.verificar') }}">
                                    @csrf
                                    <input type="hidden" name="recuperar_por" value="codigo">

                                    <div class="form-group mb-4">
                                        <label class="floating-label-activo-sm" for="codigo_recuperacion">Código</label>
                                        <input type="text" class="form-control campo-codigo @error('codigo_recuperacion') es-invalido @enderror" name="codigo_recuperacion" id="codigo_recuperacion" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
                                        @error('codigo_recuperacion')
                                            <small class="mensaje-error" role="alert">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="registro-acciones">
                                        <button type="submit" class="btn boton-morado">Verificar código</button>
                                        <button type="button" class="btn btn-outline-info" onclick="regresar_ingreso();">Regresar</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('recuperar.codigo') }}" class="recuperar-reenvio">
                                    @csrf
                                    <input type="hidden" name="recuperar_por" value="codigo">
                                    <input type="hidden" name="telefono_recuperacion" value="{{ $telefonoPendiente }}">
                                    ¿No te llegó?
                                    <button type="submit" class="btn btn-link p-0 ingreso-enlace align-baseline">Reenviar código</button>
                                    o
                                    <a href="#" class="ingreso-enlace" id="recuperar-otro-numero">usar otro número</a>
                                </form>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('recuperar.codigo') }}" id="recuperar-telefono" @if($recuperar === 'codigo') hidden @endif>
                            @csrf
                            <input type="hidden" name="recuperar_por" value="telefono">

                            <div class="form-group mb-4">
                                <label class="floating-label-activo-sm" for="telefono_recuperacion">Celular registrado</label>
                                <input type="tel" class="form-control mask_telefono @error('telefono_recuperacion') es-invalido @enderror" name="telefono_recuperacion" id="telefono_recuperacion" value="{{ $recuperar === 'telefono' ? old('telefono_recuperacion') : '' }}" placeholder="+56 9 1234 5678" autocomplete="tel" aria-describedby="ayuda-telefono-recuperacion" required>
                                <small class="ingreso-ayuda" id="ayuda-telefono-recuperacion">Te enviaremos por WhatsApp un código de 6 dígitos para cambiar tu contraseña de inmediato.</small>
                                @error('telefono_recuperacion')
                                    <small class="mensaje-error" role="alert">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="registro-acciones">
                                <button type="submit" class="btn boton-morado">Enviar código</button>
                                <button type="button" class="btn btn-outline-info" onclick="regresar_ingreso();">Regresar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Cierre: recuperar contraseña usuario -->

        </div>
    </div>

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/ripple.js') }}"></script>
    <script src="{{ asset('js/pcoded.min.js') }}"></script>
    <script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('js/plugins/select2.full.min.js') }}"></script>
    <script src="{{ asset('js/funciones.js') }}"></script>
    <script src="{{ asset('js/login/ver_clave.js') }}?t={{ time() }}"></script>
    <script src="{{ asset('js/login/registro.js') }}?t={{ time() }}"></script>
    <script src="{{ asset('js/login/recuperar.js') }}?t={{ time() }}"></script>
    <script>
        function togglePassword()
        {
            var campo = document.getElementById('password');
            var icono = document.getElementById('toggle-password');

            if (campo.type === 'password') {
                campo.type = 'text';
                icono.innerHTML = '<i class="feather icon-eye"></i>';
            } else {
                campo.type = 'password';
                icono.innerHTML = '<i class="feather icon-eye-off"></i>';
            }
        }

        function activar_recuperacion()
        {
            document.getElementById('ingreso').hidden = true;
            document.getElementById('recuperar').hidden = false;
        }

        function regresar_ingreso()
        {
            document.getElementById('ingreso').hidden = false;
            document.getElementById('recuperar').hidden = true;
        }
    </script>

</body>

</html>
