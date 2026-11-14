@php
    use App\Services\CuentasService;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Elige tu perfil</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="SDI" />
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/seleccion_cuenta.css') }}?t={{ time() }}">
</head>

<body class="pagina-seleccion">

    <header class="seleccion-barra">
        <form action="{{ route('logout') }}" method="post" id="cerrar-sesion">
            @csrf
            <button type="submit" class="seleccion-salir">
                <i class="feather icon-power"></i> Cerrar sesión
            </button>
        </form>
    </header>

    <main class="seleccion-contenido">
        <div class="seleccion-marca">
            {{-- El logo ya trae el nombre de la plataforma, por eso no se repite abajo --}}
            <img src="{{ asset('images/vet-color-h.svg') }}" alt="Veterchile" class="seleccion-logo">
        </div>

        @if(session('mensaje_error'))
            <p class="seleccion-aviso" role="alert">{{ session('mensaje_error') }}</p>
        @endif

        <p class="seleccion-saludo">
            Hola, <strong>{{ $usuario->nombreParaMostrar() }}</strong>. Elige el perfil con el que quieres trabajar en Veterchile.
            <br>
            Puedes cambiar de escritorio cuando lo necesites desde el menú superior.
        </p>

        <ul class="seleccion-grilla">
            @foreach($roles as $rol)
                <li class="seleccion-card @if($rolActivo === $rol->tipo) es-activa @endif">
                    <img class="seleccion-card-icono" src="{{ asset(CuentasService::ICONOS[$rol->tipo]) }}" alt="" aria-hidden="true">

                    <h2 class="seleccion-card-titulo">{{ CuentasService::ETIQUETAS[$rol->tipo] }}</h2>

                    @unless($rol->perfil_completo)
                        <span class="seleccion-etiqueta">Completar perfil</span>
                    @endunless

                    <p class="seleccion-card-texto">{{ CuentasService::DESCRIPCIONES[$rol->tipo] }}</p>

                    <a class="seleccion-card-enlace" href="{{ route('cuenta.entrar', ['tipo' => $rol->tipo]) }}">
                        Ingresar <span aria-hidden="true">&rarr;</span>
                        <span class="sr-only">a {{ CuentasService::ETIQUETAS[$rol->tipo] }}</span>
                    </a>
                </li>
            @endforeach

            {{-- Guardería y Hotel / Cementerio de Mascotas que aún no tiene: al ingresar se crea el perfil y entra --}}
            @foreach(\App\Services\CentrosServiciosMascotas::TIPOS as $tipoCentro)
                @continue($roles->contains('tipo', $tipoCentro))
                <li class="seleccion-card">
                    <img class="seleccion-card-icono" src="{{ asset(CuentasService::ICONOS[$tipoCentro]) }}" alt="" aria-hidden="true">

                    <h2 class="seleccion-card-titulo">{{ CuentasService::ETIQUETAS[$tipoCentro] }}</h2>

                    <p class="seleccion-card-texto">{{ CuentasService::DESCRIPCIONES[$tipoCentro] }}</p>

                    <form class="seleccion-card-formulario" method="post" action="{{ route('centros_servicios.activar') }}">
                        @csrf
                        <input type="hidden" name="tipo" value="{{ $tipoCentro }}">
                        <button type="submit" class="seleccion-card-enlace">
                            Ingresar <span aria-hidden="true">&rarr;</span>
                            <span class="sr-only">a {{ CuentasService::ETIQUETAS[$tipoCentro] }}</span>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </main>

</body>

</html>
