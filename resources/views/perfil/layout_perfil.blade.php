<!DOCTYPE html>
<html lang="es">

<head>
    <title>Completar perfil - @yield('titulo')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="author" content="SDI" />
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/completar_perfil.css') }}?t={{ time() }}">
</head>

<body class="pagina-perfil">

    <header class="perfil-barra">
        <a class="perfil-volver" href="{{ route('cuenta.seleccion') }}">
            <i class="feather icon-arrow-left"></i> Cambiar de escritorio
        </a>

        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="perfil-salir">
                <i class="feather icon-power"></i> Cerrar sesión
            </button>
        </form>
    </header>

    <main class="perfil-contenido">
        <div class="perfil-tarjeta">
            <img src="{{ asset('images/logo_pais_vertical.png') }}" alt="Veterchile" class="perfil-logo">

            <h1 class="perfil-titulo">@yield('titulo')</h1>
            <p class="perfil-bajada">@yield('bajada')</p>

            @if(session('mensaje_error'))
                <p class="perfil-aviso" role="alert">{{ session('mensaje_error') }}</p>
            @endif

            @if($errors->any())
                <p class="perfil-aviso" role="alert">Revisa los datos marcados más abajo.</p>
            @endif

            <form method="POST" action="{{ route('perfil.completar.guardar', ['tipo' => $tipo]) }}" novalidate>
                @csrf

                @yield('campos')

                <button type="submit" class="perfil-boton">Guardar y continuar</button>
            </form>
        </div>
    </main>

    <script src="{{ asset('js/jquery.js') }}"></script>
    <script src="{{ asset('js/plugins/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('js/funciones.js') }}"></script>
    @stack('scripts')
</body>

</html>
