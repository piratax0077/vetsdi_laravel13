@php
    $base = $centro->tipo;
    $rol = \App\Services\CuentasService::ETIQUETAS[$base];
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $rol }} · Veterchile</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/escritorio_profesional.css') }}">
    <link rel="stylesheet" href="{{ asset('css/formularios.css') }}">
</head>
<body>
<nav class="pcoded-navbar menu-light" aria-label="Administración">
    <div class="navbar-wrapper"><div class="navbar-content scroll-div">
        <ul class="nav pcoded-inner-navbar">
            @php($menu = ['home' => ['icon-calendar', 'Solicitudes y agenda'], 'mensajes' => ['icon-mail', 'Mensajes'], 'configuracion' => ['icon-settings', 'Configuración']])
            @if($base === 'cementerio_mascotas')
                @php($menu['inventario'] = ['icon-map-pin', 'Lugares y ventas'])
            @endif
            @foreach($menu as $ruta => [$icono, $etiqueta])
                <li class="nav-item {{ request()->routeIs($base.'.'.$ruta) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route($base.'.'.$ruta) }}">
                        <span class="pcoded-micon"><i class="feather {{ $icono }}"></i></span>
                        <span class="pcoded-mtext">{{ $etiqueta }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div></div>
</nav>
<header class="navbar pcoded-header navbar-expand-lg navbar-light header-blue">
    <div class="m-header">
        <a class="mobile-menu" id="mobile-collapse" href="#!" aria-label="Abrir menú"><span></span></a>
        <a href="{{ route($base.'.home') }}" class="b-brand"><img src="{{ asset('images/logo_pais.png') }}" alt="Veterchile" class="logo" height="45"></a>
        <a href="#!" class="mob-toggler" aria-label="Menú de cuenta"><i class="feather icon-more-vertical icono-header"></i></a>
    </div>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="d-flex align-items-center mr-2"><a href="{{ route($base.'.home') }}" class="btn btn-outline-header btn-xxs"><i class="feather icon-home mr-1"></i>Mi escritorio</a></li>
            @include('template.partials.mensajes_encabezado')
            @include('template.partials.workspace_switcher')
            <li>@include('template.partials.perfil_encabezado', ['tipoPerfil' => $base])</li>
        </ul>
    </div>
</header>
<div class="pcoded-main-container"><main class="pcoded-content">
    @include('app.centros_servicios.mensajes')
    @yield('content')
</main></div>
<script src="{{ asset('js/vendor-all.min.js') }}"></script>
<script src="{{ asset('js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/pcoded.min.js') }}"></script>
@yield('page-script')
</body>
</html>
