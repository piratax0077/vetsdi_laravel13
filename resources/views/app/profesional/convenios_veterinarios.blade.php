@extends('template.profesional.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
    <h3>Convenios veterinarios externos</h3>
    <p>Registra el nombre del convenio y su descuento. Se aplica sobre el valor de la atención al recibir el pago.</p>
    @if(session('mensaje'))<div class="alert alert-success">{{ session('mensaje') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('profesional.convenios_veterinarios.guardar') }}">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-5"><label for="nombre_convenio">Nombre del convenio</label><input id="nombre_convenio" class="form-control" name="nombre" value="{{ old('nombre') }}" required maxlength="150"></div>
                <div class="form-group col-md-2"><label for="porcentaje_convenio">Descuento (%)</label><input id="porcentaje_convenio" class="form-control" name="porcentaje" type="number" min="0" max="100" step="0.01" value="{{ old('porcentaje') }}" required></div>
                <div class="form-group col-md-5"><label for="lugar_convenio">Lugar de atención</label><select id="lugar_convenio" name="id_lugar_atencion" class="form-control"><option value="">Todos mis lugares</option>@foreach($lugares as $lugar)<option value="{{ $lugar->id }}" @selected(old('id_lugar_atencion') == $lugar->id)>{{ $lugar->nombre }}</option>@endforeach</select></div>
            </div>
            <button class="btn btn-info" type="submit">Guardar convenio</button>
        </form>
    </div></div>
    <div class="card"><div class="card-body table-responsive"><table class="table"><thead><tr><th>Convenio</th><th>Descuento</th><th>Lugar</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
        @forelse($convenios as $convenio)<tr><td>{{ $convenio->nombre }}</td><td>{{ $convenio->porcentaje }}%</td><td>{{ optional($lugares->firstWhere('id', $convenio->id_lugar_atencion))->nombre ?? 'Todos mis lugares' }}</td><td>{{ $convenio->activo ? 'Activo' : 'Inactivo' }}</td><td>@if($convenio->activo)<form method="POST" action="{{ route('profesional.convenios_veterinarios.desactivar', $convenio->id) }}">@csrf<button class="btn btn-outline-danger btn-sm">Desactivar</button></form>@endif</td></tr>
        @empty<tr><td colspan="5">Aún no hay convenios registrados.</td></tr>@endforelse
    </tbody></table></div></div>
</div></div>
@endsection
