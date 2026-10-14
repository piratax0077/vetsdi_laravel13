@php
    $contextoMenu = session('asistente_contexto', []);
    $asistenteMenu = \App\Models\Asistente::where('id_usuario', auth()->id())->first();
    $permisosMenu = $asistenteMenu && !empty($contextoMenu['id_profesional'])
        ? app(\App\Services\AutorizacionesAsistentes::class)->permisos($asistenteMenu->id, (int) $contextoMenu['id_lugar_atencion'], (int) $contextoMenu['id_profesional'])
        : [];
@endphp
<nav class="pcoded-navbar menu-light navbar-collapsed">
    <div class="navbar-wrapper"><div class="navbar-content scroll-div">
        <div class="main-menu-header">
            <img class="img-radius" src="{{ asset('images/iconos/usuario_asistente.svg') }}" alt="Asistente">
            <div class="user-details"><div>{{ auth()->user()->name }}</div><small>Asistente</small></div>
        </div>
        <ul class="nav pcoded-inner-navbar">
            <li class="nav-item"><a class="nav-link" href="{{ route('asistentes.home') }}"><span class="pcoded-micon"><i class="feather icon-home"></i></span><span class="pcoded-mtext">Mi escritorio</span></a></li>
            @foreach(config('asistentes.modulos') as $modulo)
                @if(($permisosMenu[$modulo['permiso']] ?? false) && \Illuminate\Support\Facades\Route::has($modulo['ruta']))
                    <li class="nav-item"><a class="nav-link" href="{{ route($modulo['ruta']) }}"><span class="pcoded-micon"><i class="feather icon-{{ $modulo['icono'] }}"></i></span><span class="pcoded-mtext">{{ $modulo['nombre'] }}</span></a></li>
                @endif
            @endforeach
            <li class="nav-item"><a class="nav-link" href="{{ route('asistentes.perfil') }}"><span class="pcoded-micon"><i class="feather icon-user"></i></span><span class="pcoded-mtext">Mi perfil</span></a></li>
        </ul>
    </div></div>
</nav>
