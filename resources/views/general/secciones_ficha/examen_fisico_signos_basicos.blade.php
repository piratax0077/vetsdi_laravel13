@php
    $datosSignos = $fichaVeterinariaGeneralData ?? [];
    $valorSigno = fn ($campo, $columna = null) => old($campo, data_get(
        $datosSignos, $campo, $columna ? data_get($fichaAtencion ?? null, $columna, '') : ''
    ));
@endphp
<div class="form-group col-12 col-md-6">
    <label class="floating-label-activo-sm" for="condicion_corporal">Condición corporal</label>
    <textarea class="form-control caja-texto form-control-sm" name="condicion_corporal" id="condicion_corporal" rows="1">{{ $valorSigno('condicion_corporal') }}</textarea>
</div>
@foreach ([
    'temperatura_examen_fisico' => ['T (°C)', 'temperatura'],
    'frecuencia_cardiaca' => ['FC (L/min)', null],
    'frecuencia_respiratoria' => ['FR (R/min)', null],
] as $campo => [$etiqueta, $columna])
    <div class="form-group col-6 col-md-2">
        <label class="floating-label-activo-sm" for="{{ $campo }}">{{ $etiqueta }}</label>
        <input type="text" class="form-control form-control-sm" name="{{ $campo }}" id="{{ $campo }}" value="{{ $valorSigno($campo, $columna) }}">
    </div>
@endforeach
@foreach ([
    'tllc' => ['TLLC (seg)', null],
    'trpc' => ['TRPC (seg)', null],
    'pulso_examen_fisico' => ['Pulso', 'pulso'],
    'mucosas' => ['Mucosas', null],
    'deshidratacion' => ['% Deshidratación', null],
] as $campo => [$etiqueta, $columna])
    <div class="form-group col-6 col-md-2">
        <label class="floating-label-activo-sm" for="{{ $campo }}">{{ $etiqueta }}</label>
        <input type="text" class="form-control form-control-sm" name="{{ $campo }}" id="{{ $campo }}" value="{{ $valorSigno($campo, $columna) }}">
    </div>
@endforeach
