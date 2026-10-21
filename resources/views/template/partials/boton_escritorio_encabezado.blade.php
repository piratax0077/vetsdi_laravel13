{{--
    Botón del encabezado para volver al escritorio (home) desde cualquier vista.
    Uso: @include('template.partials.boton_escritorio_encabezado', ['rutaEscritorio' => route('adm_cm.home')])
--}}
@if (!empty($rutaEscritorio))
    <li>
        <a href="{{ $rutaEscritorio }}" class="boton-escritorio-encabezado" title="Ir al escritorio" aria-label="Ir al escritorio">
            <i class="feather icon-home"></i>
            <span class="boton-escritorio-encabezado-texto">Escritorio</span>
        </a>
    </li>
@endif
