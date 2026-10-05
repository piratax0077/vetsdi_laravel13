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
                    <h5 class="mb-3">Te enviamos un correo para confirmar tu cuenta</h5>

                    <div class="row div_mensaje">
                        @if(session('mensaje'))
                            <span class="col-sm-12 alert alert-success" role="status">{{ session('mensaje') }}</span>
                        @endif
                        @if(session('mensaje_error'))
                            <span class="col-sm-12 alert alert-warning" role="alert">{{ session('mensaje_error') }}</span>
                        @endif
                        @unless($correoEnviado)
                            <span class="col-sm-12 alert alert-warning" role="alert">
                                No pudimos enviar el correo. Vuelve a intentarlo en unos minutos.
                            </span>
                        @endunless
                    </div>

                    <p class="text-muted">
                        Lo enviamos a <strong>{{ $usuario->email }}</strong>. Abre el enlace del mensaje para activar tu cuenta.
                        El enlace vence en {{ \App\Services\VerificacionCorreoService::HORAS_VIGENCIA }} horas.
                    </p>

                    <p class="ingreso-ayuda text-center">
                        Si no lo ves, revisa tu carpeta de correo no deseado.
                    </p>

                    <form method="POST" action="{{ route('registro.reenviar') }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-info" id="btn-reenviar" @disabled($segundosEspera > 0)>
                            Reenviar correo
                        </button>
                        @if($segundosEspera > 0)
                            <small class="ingreso-ayuda text-center" id="aviso-espera">
                                Puedes pedir otro correo en {{ $segundosEspera }} segundos.
                            </small>
                        @endif
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
    @if($segundosEspera > 0)
        <script>
            // Habilita el reenvio cuando se cumple la espera, sin recargar la pagina.
            (function () {
                var boton = document.getElementById('btn-reenviar');
                var aviso = document.getElementById('aviso-espera');
                var restante = {{ $segundosEspera }};

                var cuenta = setInterval(function () {
                    restante = restante - 1;

                    if (restante <= 0) {
                        clearInterval(cuenta);
                        boton.disabled = false;

                        if (aviso) {
                            aviso.remove();
                        }

                        return;
                    }

                    if (aviso) {
                        aviso.textContent = 'Puedes pedir otro correo en ' + restante + ' segundos.';
                    }
                }, 1000);
            }());
        </script>
    @endif
</body>

</html>
