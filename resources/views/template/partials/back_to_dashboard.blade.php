@php
    $dashboardRoute = $dashboardRoute ?? null;
    $homeRouteNames = (array) ($homeRouteNames ?? []);
    $showDashboardBack = $dashboardRoute
        && \Illuminate\Support\Facades\Route::has($dashboardRoute)
        && ! request()->routeIs(...$homeRouteNames)
        && ! request()->routeIs('paciente.dependiente.mis_profesionales');
@endphp

@if($showDashboardBack)
    <a href="{{ route($dashboardRoute) }}"
       class="sdi-back-dashboard"
       aria-label="Volver a mi escritorio"
       title="Volver a mi escritorio">
        <i class="feather icon-home" aria-hidden="true"></i>
    </a>
    @once
        <style>
            .sdi-back-dashboard{
                position:fixed;top:66px;left:238px;z-index:1028;
                display:inline-flex;align-items:center;justify-content:center;
                padding:7px;border:0;background:transparent;color:#fff!important;
                font-size:24px;line-height:1;
                transition:transform .18s ease,color .18s ease;
            }
            .sdi-back-dashboard:hover,.sdi-back-dashboard:focus{
                color:#d9fffc!important;text-decoration:none;background:transparent;
                transform:translateY(-1px) scale(1.08);box-shadow:none;
            }
            .sdi-back-dashboard i{font-size:24px}
            @media(max-width:767.98px){
                .sdi-back-dashboard{top:58px;left:78px}
            }
        </style>
    @endonce
@endif
