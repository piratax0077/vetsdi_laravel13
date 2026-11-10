@foreach ($boxes_servicio as $box)
    <tr data-box-id="{{ $box->id }}">
        <td class="align-middle">{{ $box->numero_box }}</td>
        <td class="align-middle">{{ $box->tipo_especializacion ?: $box->tipo_box }}</td>
        <td class="align-middle">Piso {{ $box->ubicacion }}</td>
        <td class="align-middle">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="esp-{{ $box->id }}" data-id="{{ $box->id }}" onchange="window.boxesCentroVeterinario.cambiarEstado(this)" @checked($box->estado == 1)>
                <label class="custom-control-label" for="esp-{{ $box->id }}"></label>
            </div>
        </td>
        <td class="align-middle">
            <button title="Editar box" type="button" class="btn btn-warning btn-icon" onclick="dame_box({{ $box->id }});"><i class="feather icon-edit"></i></button>
            <button title="Eliminar box" type="button" class="btn btn-danger btn-icon" onclick="eliminar_box({{ $box->id }});"><i class="feather icon-x"></i></button>
        </td>
    </tr>
@endforeach
