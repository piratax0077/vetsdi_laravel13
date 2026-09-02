@extends('template.adm_cm.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title"><h5 class="m-b-10 font-weight-bold">Procedimientos</h5></div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('adm_cm.laboratorio') }}"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item">Procedimientos</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="text-white f-20 mb-0">Procedimientos del centro</h4>
                    <button type="button" class="btn btn-sm btn-outline-light" onclick="nuevoProcedimiento()">
                        <i class="feather icon-plus"></i> Agregar procedimiento
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="tabla_procedimientos" class="display table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center">Duración</th>
                            <th class="text-right">Valor</th>
                            <th class="text-center">Tipo ficha</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($procedimientos as $procedimiento)
                        <tr>
                            <td>{{ $procedimiento->nombre }}</td>
                            <td>{{ $procedimiento->descripcion ?: 'Sin descripción' }}</td>
                            <td class="text-center">{{ ((int) $procedimiento->minutos_bloque ?: 15) * ((int) $procedimiento->cantidad_bloques ?: 1) }} min</td>
                            <td class="text-right">${{ number_format((float) $procedimiento->valor, 0, ',', '.') }}</td>
                            <td class="text-center">{{ (int) $procedimiento->tipo_ficha_atencion === 1 ? 'General' : 'Especialidad' }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-success btn-sm" onclick='editarProcedimiento(@json($procedimiento))'>
                                    <i class="feather icon-edit"></i> Editar
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="eliminarProcedimiento({{ $procedimiento->id }})">
                                    <i class="feather icon-x-circle"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal_procedimiento" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 id="titulo_modal_procedimiento" class="modal-title text-white">Agregar procedimiento</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>×</span></button>
            </div>
            <form id="form_procedimiento">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="procedimiento_id">
                    <input type="hidden" id="procedimiento_lugar" value="{{ $institucion->id_lugar_atencion }}">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" class="form-control" id="procedimiento_nombre" required maxlength="190">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" id="procedimiento_descripcion" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Duración del bloque</label>
                                <select class="form-control" id="procedimiento_minutos" required>
                                    <option value="15">15 minutos</option>
                                    <option value="30">30 minutos</option>
                                    <option value="45">45 minutos</option>
                                    <option value="60">60 minutos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Cantidad de bloques</label>
                                <input type="number" class="form-control" id="procedimiento_bloques" min="1" value="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Valor</label>
                                <input type="number" class="form-control" id="procedimiento_valor" min="0" step="1" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Tipo de ficha</label>
                                <select class="form-control" id="procedimiento_tipo_ficha">
                                    <option value="1">General</option>
                                    <option value="2">Especialidad</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Indicaciones u otros antecedentes</label>
                        <textarea class="form-control" id="procedimiento_otros" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(function () {
    $('#tabla_procedimientos').DataTable({ responsive: true, language: { search: 'Buscar:' } });

    $('#form_procedimiento').on('submit', function (event) {
        event.preventDefault();
        const id = $('#procedimiento_id').val();
        const data = datosProcedimiento();
        if (id) {
            data.id = id;
            data.estado = 1;
        }
        $.post(id ? @json(route('adm_cm.procedimiento.modificar')) : @json(route('adm_cm.procedimiento.registrar')), data)
            .done(function (respuesta) {
                if (Number(respuesta.estado) === 1) {
                    swal('Guardado', 'El procedimiento fue guardado correctamente.', 'success').then(function () { location.reload(); });
                } else {
                    swal('Error', respuesta.msj || 'No fue posible guardar el procedimiento.', 'error');
                }
            })
            .fail(function () { swal('Error', 'No fue posible comunicarse con el servidor.', 'error'); });
    });
});

function datosProcedimiento() {
    return {
        _token: @json(csrf_token()),
        id_lugar_atencion: $('#procedimiento_lugar').val(),
        nombre: $('#procedimiento_nombre').val(),
        descripcion: $('#procedimiento_descripcion').val(),
        minutos_bloque: $('#procedimiento_minutos').val(),
        cantidad_bloques: $('#procedimiento_bloques').val(),
        valor: $('#procedimiento_valor').val(),
        tipo_ficha_atencion: $('#procedimiento_tipo_ficha').val(),
        otros: $('#procedimiento_otros').val()
    };
}

function nuevoProcedimiento() {
    $('#form_procedimiento')[0].reset();
    $('#procedimiento_id').val('');
    $('#procedimiento_bloques').val(1);
    $('#titulo_modal_procedimiento').text('Agregar procedimiento');
    $('#modal_procedimiento').modal('show');
}

function editarProcedimiento(procedimiento) {
    $('#procedimiento_id').val(procedimiento.id);
    $('#procedimiento_nombre').val(procedimiento.nombre);
    $('#procedimiento_descripcion').val(procedimiento.descripcion);
    $('#procedimiento_minutos').val(procedimiento.minutos_bloque || 15);
    $('#procedimiento_bloques').val(procedimiento.cantidad_bloques || 1);
    $('#procedimiento_valor').val(procedimiento.valor || 0);
    $('#procedimiento_tipo_ficha').val(procedimiento.tipo_ficha_atencion || 1);
    $('#procedimiento_otros').val(procedimiento.otros);
    $('#titulo_modal_procedimiento').text('Editar procedimiento');
    $('#modal_procedimiento').modal('show');
}

function eliminarProcedimiento(id) {
    swal({ title: 'Eliminar procedimiento', text: '¿Desea eliminar este procedimiento?', icon: 'warning', buttons: ['Cancelar', 'Eliminar'], dangerMode: true })
        .then(function (confirmado) {
            if (!confirmado) return;
            $.post(@json(route('adm_cm.procedimiento.modificar')), { _token: @json(csrf_token()), id: id, estado: 0 })
                .done(function (respuesta) {
                    if (Number(respuesta.estado) === 1) location.reload();
                    else swal('Error', respuesta.msj || 'No fue posible eliminar.', 'error');
                })
                .fail(function () { swal('Error', 'No fue posible comunicarse con el servidor.', 'error'); });
        });
}
</script>
@endsection
