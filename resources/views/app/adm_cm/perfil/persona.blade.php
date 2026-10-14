<div class="form-row">
    @foreach(['rut'=>'RUT','nombres'=>'Nombres','apellido_uno'=>'Primer apellido','apellido_dos'=>'Segundo apellido','email'=>'Correo electrónico','telefono_uno'=>'Teléfono','telefono_dos'=>'Teléfono alternativo'] as $campo=>$etiqueta)
    <div class="form-group col-md-6"><label>{{ $etiqueta }}</label><input class="form-control" name="{{ $campo }}" type="{{ $campo === 'email' ? 'email' : 'text' }}" maxlength="{{ str_starts_with($campo,'telefono') ? 30 : 255 }}" value="{{ $campo === 'nombres' && $medico ? $persona?->nombre : $persona?->{$campo} }}"></div>
    @endforeach
    <div class="form-group col-md-6"><label>Sexo</label><select name="sexo" class="form-control"><option value="">Seleccione</option>@foreach(['M'=>'Masculino','F'=>'Femenino','O'=>'Otro'] as $codigo=>$nombre)<option value="{{ $codigo }}" @selected($persona?->sexo === $codigo)>{{ $nombre }}</option>@endforeach</select></div>
    <div class="form-group col-md-6"><label>Fecha de nacimiento</label><input name="fecha_nac" type="date" class="form-control" value="{{ substr((string)($medico ? $persona?->fecha_nacimiento : $persona?->fecha_nac),0,10) }}"></div>
</div>
@include('app.adm_cm.perfil.direccion')
