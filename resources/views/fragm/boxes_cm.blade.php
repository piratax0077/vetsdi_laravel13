<table id="tabla_boxes_atencion" class="display table table-striped table-xs dt-responsive nowrap" style="width:100%">
    <thead>
        <tr>
            <th class="text-wrap align-middle">N° Asig.</th>
            <th class="text-wrap align-middle">Tipo</th>
            <th class="text-wrap align-middle">Especialización</th>
            <th class="text-wrap align-middle">Ubicación</th>
            <th class="text-wrap align-middle">Sección</th>
            <th class="text-wrap align-middle">Activo</th>
            <th class="text-wrap align-middle">Editar</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($boxes_servicio) && ($boxes_servicio->count()) > 0)
            @foreach($boxes_servicio as $box)
                <tr>
                    <td class="align-middle">{{ $box->numero_box }}</td>
                    <td class="align-middle">{{ $box->tipo_box }}</td>
                    <td class="align-middle">{{ $box->tipo_especializacion }}</td>
                    <td class="align-middle">Piso {{ $box->ubicacion }}</td>
                    <td class="align-middle">{{ $box->seccion }}</td>
                    <td class="align-middle">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="esp-{{ $box->id }}" onchange="checkboxChanged(this)" {{ $box->estado == 1 ? 'checked' : ''}}>
                            <label class="custom-control-label" for="esp-{{ $box->id }}"></label>
                        </div>
                    </td>
                    <td class="align-middle">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="dame_box({{ $box->id }});"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminar_box({{ $box->id }})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('#tabla_boxes_atencion').DataTable();
    });
</script>
