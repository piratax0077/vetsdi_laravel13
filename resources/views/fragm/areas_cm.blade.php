<div class="row">
    <div class="col-sm-12 col-md-12">
        <div style="overflow-x:auto;">
            <div class="table-responsive">
                {{-- Mismas columnas que la tabla de Áreas de la configuración, para que no cambie al guardar --}}
                <table id="area_cm" class="display table table-striped dt-responsive nowrap table-xs" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-wrap text-left align-middle">Tipo</th>
                            <th class="text-wrap text-left align-middle">Responsable</th>
                            <th class="text-wrap text-left align-middle">Contacto</th>
                            <th class="text-wrap text-left align-middle">N° personal</th>
                            <th class="text-wrap text-left align-middle">Integrantes</th>
                            <th class="text-wrap text-left align-middle">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($areas_cm as $area)
                            <tr>
                                <td class="align-middle text-left">{{ $area->tipo_area }}</td>
                                <td class="align-middle text-left">
                                    {{ $area->nombre . ' ' . $area->apellido_uno . ' ' . $area->apellido_dos }}
                                </td>
                                <td class="align-middle text-left">
                                    @if ($area->email)<i class="feather icon-mail text-purple mr-1" aria-hidden="true"></i>@endif{{ $area->email }}
                                </td>
                                <td class="align-middle text-left">{{ $area->numero_personas }}</td>
                                <td class="align-middle text-left">
                                    @if ($area->profesionales)
                                        @foreach (collect($area->profesionales)->filter() as $profesional)
                                            <span>{{ trim(($profesional->nombre ?? '') . ' ' . ($profesional->apellido_uno ?? '') . ' ' . ($profesional->apellido_dos ?? '')) }}</span><br>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="align-middle text-left">
                                    <button type="button" class="btn btn-info btn-icon" data-toggle="tooltip" data-placement="top"
                                        title="Asociar profesionales al área de {{ $area->tipo_area }}"
                                        onclick="asignar_profesionales_area({{ $area->id }})"><i class="feather icon-plus"></i></button>
                                    <button type="button" class="btn btn-warning btn-icon" data-toggle="tooltip" data-placement="top"
                                        title="Editar responsable {{ $area->tipo_area }}"
                                        onclick="dame_area_cm({{ $area->id }},{{ $area->id_lugar_atencion ?? ($institucion->id_lugar_atencion ?? 'null') }})"><i class="feather icon-edit"></i></button>
                                    <button type="button" class="btn btn-danger btn-icon" data-toggle="tooltip" data-placement="top"
                                        title="Eliminar área {{ $area->tipo_area }}"
                                        onclick="eliminar_area_cm({{ $area->id }});"><i class="feather icon-x"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#area_cm').DataTable();
    });
</script>
