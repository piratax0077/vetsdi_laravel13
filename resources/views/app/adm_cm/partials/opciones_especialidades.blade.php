<option value="0">Seleccione</option>
@foreach (\App\Services\EspecialidadesCentroVeterinario::CATALOGO as $claveEspecialidad => $nombreEspecialidad)
<option value="{{ $claveEspecialidad }}">{{ $nombreEspecialidad }}</option>
@endforeach
