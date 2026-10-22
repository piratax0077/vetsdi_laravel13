<table id="laboratorios_tabla" class="display table table-striped dt-responsive nowrap table-xs" style="width:100%">
    <thead>
        <tr>
            <th class="text-wrap text-left align-middle">RUT</th>
            <th class="text-wrap text-left align-middle">Laboratorio</th>
            <th class="text-wrap text-left align-middle">Ubicación</th>
            <th class="text-wrap text-left align-middle">Tipo Laboratorio</th>
            <th class="text-wrap text-left align-middle">Dirección</th>
            <th class="text-wrap text-left align-middle">Acción</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($laboratorios))
        @foreach($laboratorios as $laboratorio)
        <tr>
            <td class="align-middle text-left">{{ $laboratorio->rut }}</td>
            <td class="align-middle text-left">{{ $laboratorio->nombre }}</td>
            <td class="align-middle text-left">{{ $laboratorio->ubicacion == 1 ? 'Laboratorio Físico' : 'Solo toma de muestra'}}</td>
            <td class="align-middle text-left">{{ $laboratorio->tipo_sucursal_nombre }}</td>
            <td class="align-middle text-left">
                <div><i class="feather icon-map-pin text-purple mr-1" aria-hidden="true"></i>{{ \Illuminate\Support\Str::ucfirst($laboratorio->direccion ?? '') }}</div>
                <small class="text-muted">{{ \Illuminate\Support\Str::ucfirst($laboratorio->ciudad ?? '') }}</small>
            </td>
            <td class="align-middle text-left">
                <button title="Editar laboratorio" type="button" class="btn btn-warning btn-icon" onclick="dame_laboratorio_cm({{ $laboratorio->id }})"><i class="feather icon-edit"></i></button>
                <button title="Eliminar laboratorio" type="button" class="btn btn-danger btn-icon" onclick="eliminar_laboratorio_cm({{ $laboratorio->id }});"><i class="feather icon-x"></i></button>
                <button title="Horario del laboratorio" type="button" class="btn btn-primary btn-icon" onclick="horario_laboratorio_cm({{ $laboratorio->id }});"><i class="feather icon-clock"></i></button>
            </td>
        </tr>
        @endforeach
        @endif
    </tbody>
</table>


<script>
    $(document).ready(function() {
        $('#laboratorios_tabla').DataTable();
    });
</script>
