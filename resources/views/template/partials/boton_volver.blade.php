{{--
    Botón "Volver" que va sobre el título de la página.

    Uso:
        @include('template.partials.boton_volver')
        @include('template.partials.boton_volver', ['url' => route('profesional.configuracion')])
        @include('template.partials.boton_volver', ['url' => route('...'), 'texto' => 'Volver a configuración'])

    Con "url" lleva a esa página. Sin "url" vuelve a la página anterior del navegador.
--}}
@php
    $url_volver = $url ?? null;
    $texto_volver = $texto ?? 'Volver';
@endphp
<div class="boton-volver-fila">
    <a class="boton-volver" href="{{ $url_volver ?: url()->previous() }}" @if (!$url_volver) onclick="if (window.history.length > 1) { window.history.back(); return false; }" @endif>
        <i class="feather icon-arrow-left" aria-hidden="true"></i><span>{{ $texto_volver }}</span>
    </a>
</div>
