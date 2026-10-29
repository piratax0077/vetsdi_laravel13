@php
    $rolActivoEscritorio = request()->hasSession() ? request()->session()->get('rol_activo') : null;
    $mostrarEscritorioVeterinario = ($forzarEscritorioVeterinario ?? false)
        || request()->routeIs('adm_cm.*','adm_hospital.*','administrador_comercial.*','vet.operacion.*')
        || $rolActivoEscritorio === 'clinica'
        || (!$rolActivoEscritorio && Auth::user()?->hasAnyRole(['Institucion','Adm_Institucion','Adm_Comercial','AsistenteAdm']));
@endphp
@if($mostrarEscritorioVeterinario)
    <a href="{{ \App\Support\EscritorioVeterinario::url(request(),$institucion ?? null) }}"
       class="sdi-home-header" title="Volver al escritorio" aria-label="Volver al escritorio">
        <i class="feather icon-home" aria-hidden="true"></i>
    </a>
    @once
    <style>
        .pcoded-header .sdi-home-header{position:absolute;left:244px;top:7px;z-index:1030;display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:5px;color:#fff!important;font-size:22px;line-height:1;text-decoration:none}
        .pcoded-header .sdi-home-header:hover,.pcoded-header .sdi-home-header:focus-visible{background:rgba(255,255,255,.15);color:#fff!important;outline:2px solid currentColor;outline-offset:2px}
        .pcoded-header .sdi-home-header i{font-size:22px}
        @media(min-width:992px){.pcoded-navbar.navbar-collapsed ~ .pcoded-header .sdi-home-header{left:94px}}
        @media(max-width:991.98px){.pcoded-header .sdi-home-header{left:56px}}
    </style>
    @endonce
@endif
