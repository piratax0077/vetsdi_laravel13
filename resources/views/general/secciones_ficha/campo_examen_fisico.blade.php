{{-- Campo libre de examen físico: va en Anamnesis o dentro de la card Examen físico --}}
@php
    $valorExamenFisico = old('examen_fisico', data_get(
        isset($fichaVeterinariaGeneralData) && is_array($fichaVeterinariaGeneralData) ? $fichaVeterinariaGeneralData : [],
        'examen_fisico',
        data_get($fichaAtencion ?? null, 'examen_fisico', '')
    ));
@endphp
<div class="form-group col-12">
    <label class="floating-label-activo-sm" for="examen_fisico">Examen Físico</label>
    {{-- Con filasExamenFisico el campo queda siempre de ese alto; si no, se abre al escribir --}}
    <textarea class="form-control caja-texto form-control-sm"
        @if (!empty($filasExamenFisico)) rows="{{ $filasExamenFisico }}" data-fixed-rows @else rows="1" onfocus="this.rows=3" onblur="this.rows=1" @endif
        name="examen_fisico"
        id="examen_fisico"
        placeholder="DESCRIBA LAS CARACTERÍSTICAS QUE CONSIDERE NECESARIO">{{ $valorExamenFisico }}</textarea>
</div>
