{{-- Resumen de las atenciones anteriores de la mascota; va arriba de la anamnesis --}}
@php
    $historialMascota = ($mascota ?? null) instanceof \App\Models\Mascota
        ? \App\Support\ResumenMascotaFicha::historialAtenciones($mascota, $id_ficha_atencion ?? null)
        : null;
@endphp

@if ($historialMascota)
<div class="col-12">
    <div class="resumen-atenciones">
        <span class="resumen-atenciones__etiqueta">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M10 2.5l1.9 5.4 5.4 1.9-5.4 1.9-1.9 5.4-1.9-5.4-5.4-1.9 5.4-1.9z" />
                <path d="M18.5 1.5l.9 2.4 2.4.9-2.4.9-.9 2.4-.9-2.4-2.4-.9 2.4-.9z" />
                <path d="M18 14.5l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8z" />
            </svg>
            Resumen
        </span>

        <button type="button" class="resumen-atenciones__plegar" data-toggle="collapse" data-target="#resumen_atenciones_texto"
                aria-expanded="true" aria-controls="resumen_atenciones_texto" title="Mostrar u ocultar el resumen">
            <i class="feather icon-chevron-up"></i>
        </button>

        <div id="resumen_atenciones_texto" class="collapse show">
            <p class="resumen-atenciones__texto">{!! $historialMascota['texto'] !!}</p>
            <span class="resumen-atenciones__pie">Según las atenciones registradas hasta el {{ $historialMascota['hasta'] }}</span>
        </div>
    </div>
</div>
@endif
