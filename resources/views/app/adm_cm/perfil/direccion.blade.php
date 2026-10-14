@php
    $domicilio = \App\Models\Direccion::find($persona?->id_direccion);
    $ciudadActual = $domicilio ? \App\Models\Ciudad::find($domicilio->id_ciudad) : null;
@endphp
<div class="form-row">
    <div class="form-group col-md-6"><label>Región</label><select name="region" class="form-control"><option value="">Seleccione</option>@foreach($regiones as $region)<option value="{{ $region->id }}" @selected((int)$ciudadActual?->id_region === (int)$region->id)>{{ $region->nombre }}</option>@endforeach</select></div>
    <div class="form-group col-md-6"><label>Comuna</label><select name="ciudad" class="form-control"><option value="">Seleccione</option>@foreach($ciudades as $ciudad)<option value="{{ $ciudad->id }}" data-region="{{ $ciudad->id_region }}" @selected((int)$domicilio?->id_ciudad === (int)$ciudad->id)>{{ $ciudad->nombre }}</option>@endforeach</select></div>
    <div class="form-group col-md-9"><label>Dirección</label><input name="direccion" class="form-control" maxlength="255" value="{{ $domicilio?->direccion }}"></div>
    <div class="form-group col-md-3"><label>Número</label><input name="numero_dir" class="form-control" maxlength="30" value="{{ $domicilio?->numero_dir }}"></div>
</div>
