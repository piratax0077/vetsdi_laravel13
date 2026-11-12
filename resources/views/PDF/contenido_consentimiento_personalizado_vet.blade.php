@unless($vistaPrevia ?? false)
    @include('PDF.datos_consentimiento_veterinario')
@endunless

@foreach(config('consentimiento_veterinario_personalizado') as $campo => $detalle)
    @if($vistaPrevia ?? false)
        <p @class(['text-break', 'd-none' => !$detalle['requerido']])>
            <strong>{{ $detalle['etiqueta'] }}</strong><br>
            <span style="white-space: pre-wrap" data-biopsia-campo="{{ $campo }}" @if(!$detalle['requerido']) data-biopsia-opcional @endif>________</span>
        </p>
    @elseif(($datos[$campo] ?? '') !== '')
        <p><strong>{{ $detalle['etiqueta'] }}</strong><br>{!! nl2br(e($datos[$campo])) !!}</p>
    @endif
@endforeach

<p><strong>Declaración de consentimiento</strong></p>
<p>Declaro que el profesional veterinario me explicó el procedimiento descrito y que tuve la oportunidad de hacer preguntas. Doy mi consentimiento libre y voluntario para que se realice el procedimiento en mi mascota.</p>
