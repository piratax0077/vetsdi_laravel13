{{-- Fila de signos básicos al inicio de la card Examen físico (ficha veterinaria general).
     Celular: de a dos. Tablet y notebook: 4 arriba y 3 abajo. Pantalla grande (1200px o más): los 7 en una línea. --}}
@php
    $datosSignos = isset($fichaVeterinariaGeneralData) && is_array($fichaVeterinariaGeneralData) ? $fichaVeterinariaGeneralData : [];
    $valorSigno = fn ($campo) => old($campo, data_get($datosSignos, $campo, ''));
@endphp
<div class="form-group col-6 col-md-3 col-xl">
    <label class="floating-label-activo-sm" for="temperatura_examen_fisico">T° (°C)</label>
    <input type="text" class="form-control form-control-sm" name="temperatura_examen_fisico" id="temperatura_examen_fisico" value="{{ $valorSigno('temperatura_examen_fisico') }}">
</div>
<div class="form-group col-6 col-md-3 col-xl">
    <label class="floating-label-activo-sm" for="peso_examen_fisico">Peso (kg)</label>
    <input type="text" class="form-control form-control-sm" name="peso_examen_fisico" id="peso_examen_fisico" value="{{ $valorSigno('peso_examen_fisico') }}">
</div>
<div class="form-group col-6 col-md-3 col-xl">
    <label class="floating-label-activo-sm" for="frecuencia_respiratoria">FR (R/min)</label>
    <input type="text" class="form-control form-control-sm" name="frecuencia_respiratoria" id="frecuencia_respiratoria" value="{{ $valorSigno('frecuencia_respiratoria') }}">
</div>
<div class="form-group col-6 col-md-3 col-xl">
    <label class="floating-label-activo-sm" for="tllc">TLLC (seg)</label>
    <input type="text" class="form-control form-control-sm" name="tllc" id="tllc" value="{{ $valorSigno('tllc') }}">
</div>
<div class="form-group col-6 col-md-4 col-xl">
    <label class="floating-label-activo-sm" for="frecuencia_cardiaca">FC (L/min)</label>
    <input type="text" class="form-control form-control-sm" name="frecuencia_cardiaca" id="frecuencia_cardiaca" value="{{ $valorSigno('frecuencia_cardiaca') }}">
</div>
<div class="form-group col-6 col-md-4 col-xl">
    <label class="floating-label-activo-sm" for="mucosas">Mucosas</label>
    <input type="text" class="form-control form-control-sm" name="mucosas" id="mucosas" value="{{ $valorSigno('mucosas') }}">
</div>
<div class="form-group col-12 col-md-4 col-xl">
    <label class="floating-label-activo-sm" for="presion_arterial_examen_fisico">Presión Arterial (mmHg)</label>
    <input type="text" class="form-control form-control-sm" name="presion_arterial_examen_fisico" id="presion_arterial_examen_fisico" value="{{ $valorSigno('presion_arterial_examen_fisico') }}">
</div>
