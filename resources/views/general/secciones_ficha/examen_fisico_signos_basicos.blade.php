@php
    $datosSignos = $fichaVeterinariaGeneralData ?? [];
    $valorSigno = fn ($campo, $columna = null) => old($campo, data_get(
        $datosSignos, $campo, $columna ? data_get($fichaAtencion ?? null, $columna, '') : ''
    ));
@endphp
<div class="form-group col-12 col-md-4">
    <label class="floating-label-activo-sm" for="condicion_corporal">Condición corporal</label>
    <textarea class="form-control caja-texto form-control-sm" name="condicion_corporal" id="condicion_corporal" rows="1">{{ $valorSigno('condicion_corporal') }}</textarea>
</div>
{{-- Peso de la mascota: mismo id que usa el panel de signos vitales --}}
<div class="form-group col-6 col-md-2">
    <label class="floating-label-activo-sm" for="peso">Peso</label>
    <div class="campo-con-unidad">
        <input type="text" class="form-control form-control-sm" name="peso" id="peso" value="{{ $valorSigno('peso', 'peso') }}">
        <span class="campo-unidad">Kg</span>
    </div>
</div>
{{-- Cada signo lleva su unidad a la derecha del campo; los que no tienen unidad quedan en null --}}
@foreach ([
    'temperatura_examen_fisico' => ['T', 'temperatura', '°C'],
    'frecuencia_cardiaca' => ['FC', null, 'L/min'],
    'frecuencia_respiratoria' => ['FR', null, 'R/min'],
    'tllc' => ['TLLC', null, 'Seg'],
    'trpc' => ['TRPC', null, 'Seg'],
    'pulso_examen_fisico' => ['Pulso', 'pulso', null],
    'mucosas' => ['Mucosas', null, null],
    'deshidratacion' => ['Deshidratación', null, '%'],
] as $campo => [$etiqueta, $columna, $unidad])
    <div class="form-group col-6 col-md-2">
        <label class="floating-label-activo-sm" for="{{ $campo }}">{{ $etiqueta }}</label>
        @if ($unidad)
            <div class="campo-con-unidad">
                <input type="text" class="form-control form-control-sm" name="{{ $campo }}" id="{{ $campo }}" value="{{ $valorSigno($campo, $columna) }}">
                <span class="campo-unidad">{{ $unidad }}</span>
            </div>
        @else
            <input type="text" class="form-control form-control-sm" name="{{ $campo }}" id="{{ $campo }}" value="{{ $valorSigno($campo, $columna) }}">
        @endif
    </div>
@endforeach
