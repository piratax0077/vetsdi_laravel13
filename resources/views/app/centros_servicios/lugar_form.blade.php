<form method="post" action="{{ route('cementerio_mascotas.inventario.guardar', $lugar?->id ? ['id' => $lugar->id] : []) }}">
    @csrf
    @php($sufijo = $lugar?->id ?? 'nuevo')
    <div class="form-row">
        @foreach(['codigo' => 'Código', 'sector' => 'Sector', 'precio' => 'Precio (CLP)', 'descripcion' => 'Descripción'] as $campo => $texto)
            <div class="form-group col-md-3">
                <label for="{{ $campo }}-{{ $sufijo }}">{{ $texto }}</label>
                <input class="form-control" id="{{ $campo }}-{{ $sufijo }}" name="{{ $campo }}" type="{{ $campo === 'precio' ? 'number' : 'text' }}"
                    @if($campo === 'precio') min="0" max="100000000" @endif @required($campo !== 'descripcion') value="{{ $lugar?->{$campo} }}">
            </div>
        @endforeach
    </div>
    <label class="mr-3"><input type="checkbox" name="disponible" value="1" @checked($lugar?->disponible ?? true)> Ofrecer en catálogo</label>
    <button class="btn btn-info">{{ $lugar ? 'Guardar lugar' : 'Agregar lugar' }}</button>
</form>
