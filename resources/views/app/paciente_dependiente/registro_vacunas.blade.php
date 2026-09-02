@extends('template.paciente_dependiente.template')
@section('content')
<!--Container Completo-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
<ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ ROUTE('paciente.home') }}" data-toggle="tooltip"
                                    data-placement="top" title="Volver a mi escritorio"><i
                                        class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ ROUTE('paciente.mis_profesionales') }}">Registro de vacunas</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!--Cierre: Header-->
        <div class="row">
            <div class="col-12">
                <!--Card Nav Pills-->
                <div class="card">
                    <div class="card-header bg-info">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                            <h4 class="f-20 text-white mb-2 mb-md-0">Registro de vacunas</h4>
                            <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2" data-toggle="modal" data-target="#modal_vacuna">
                                <i class="feather icon-plus"></i> Añadir registro
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tabla_recetas_paciente_ro"
                                        class="display table table-striped dt-responsive nowrap table-sm"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Edad</th>
                                                <th class="align-middle">Fecha dosis</th>
                                                <th class="align-middle">Vacuna</th>
                                                <th class="align-middle">Próx.Dosis</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tabla_vacunas_body">
                                            @forelse(($vacunas ?? []) as $vacuna)
                                                <tr>
                                                    <td class="align-middle">{{ $vacuna['edad'] ?? '-' }}</td>
                                                    <td class="align-middle">
                                                        <span class="badge badge-secondary">
                                                            {{ !empty($vacuna['fecha_dosis']) ? \Carbon\Carbon::parse($vacuna['fecha_dosis'])->format('d-m-Y') : '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">{{ $vacuna['vacuna'] ?? '-' }}</td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge badge-info">
                                                            {{ !empty($vacuna['proxima_dosis']) ? \Carbon\Carbon::parse($vacuna['proxima_dosis'])->format('d-m-Y') : '-' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="align-middle text-center text-muted" colspan="4">Sin registros</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--Cierre: Container Completo-->

<div class="modal fade" id="modal_vacuna" tabindex="-1" role="dialog" aria-labelledby="modal_vacuna_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="modal_vacuna_label"><i class="fas fa-syringe mr-2"></i>Añadir vacuna</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    Registre aquí una vacuna administrada a <strong>{{ $mascota->nombre ?? 'su mascota' }}</strong>.
                </div>
                <form id="form_vacuna_modal">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm">Fecha de la dosis *</label>
                            <input type="date" class="form-control form-control-sm" id="vac_fecha_dosis_modal" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm">Próxima dosis</label>
                            <input type="date" class="form-control form-control-sm" id="vac_proxima_dosis_modal">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Vacuna *</label>
                        <input type="text" class="form-control form-control-sm" id="vac_nombre_modal" maxlength="255" placeholder="Ej.: Antirrábica" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-7">
                            <label class="floating-label-activo-sm">Edad al momento de la dosis</label>
                            <input type="text" class="form-control form-control-sm" id="vac_edad_modal" maxlength="120" placeholder="Ej.: 1 año 3 meses">
                        </div>
                        <div class="form-group col-md-5">
                            <label class="floating-label-activo-sm">Especie</label>
                            <select class="form-control form-control-sm" id="vac_especie_modal">
                                <option value="">No indicar</option>
                                <option value="canina">Canina</option>
                                <option value="felina">Felina</option>
                                <option value="otra">Otra</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-right pt-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info rounded-pill px-4" id="btn_guardar_vacuna">
                            <i class="feather icon-check"></i> Guardar vacuna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
    <script>
        function escaparVacuna(valor) {
            return $('<div>').text(valor == null ? '' : valor).html();
        }

        function formatearFechaVacuna(fecha) {
            return fecha ? moment(fecha).format('DD-MM-YYYY') : '-';
        }

        function renderTablaVacunas(registros) {
            var $tbody = $('#tabla_vacunas_body').empty();
            if (!registros || !registros.length) {
                $tbody.append('<tr><td class="align-middle text-center text-muted" colspan="4">Sin registros</td></tr>');
                return;
            }

            $.each(registros, function (_, vacuna) {
                $tbody.append('<tr>' +
                    '<td class="align-middle">' + escaparVacuna(vacuna.edad || '-') + '</td>' +
                    '<td class="align-middle"><span class="badge badge-secondary">' + formatearFechaVacuna(vacuna.fecha_dosis) + '</span></td>' +
                    '<td class="align-middle">' + escaparVacuna(vacuna.vacuna || '-') + '</td>' +
                    '<td class="align-middle text-center"><span class="badge badge-info">' + formatearFechaVacuna(vacuna.proxima_dosis) + '</span></td>' +
                '</tr>');
            });
        }

        $(document).on('submit', '#form_vacuna_modal', function (e) {
            e.preventDefault();
            var $boton = $('#btn_guardar_vacuna').prop('disabled', true);
            var url = "{{ route('paciente.mascotas.vacunas.guardar', ['mascotaId' => $mascota->id]) }}";

            $.post(url, {
                _token: CSRF_TOKEN,
                fecha_dosis: $('#vac_fecha_dosis_modal').val(),
                proxima_dosis: $('#vac_proxima_dosis_modal').val(),
                vacuna: $('#vac_nombre_modal').val(),
                edad: $('#vac_edad_modal').val(),
                especie: $('#vac_especie_modal').val(),
            }).done(function (resp) {
                renderTablaVacunas(resp.vacunas || []);
                $('#form_vacuna_modal')[0].reset();
                $('#modal_vacuna').modal('hide');
                swal({title: 'Registro guardado', text: 'La vacuna fue agregada correctamente.', icon: 'success'});
            }).fail(function (xhr) {
                var json = xhr.responseJSON || {};
                var primerError = json.error ? Object.values(json.error)[0] : null;
                var mensaje = primerError && primerError[0] ? primerError[0] : (json.msj || 'No se pudo guardar la vacuna.');
                swal({title: 'Revise la información', text: mensaje, icon: 'warning'});
            }).always(function () {
                $boton.prop('disabled', false);
            });
        });

        function active_e(tipo_esp){
            if(tipo_esp=='all')
            {
                $('.filtro_le').removeClass('d-none');
            }else{
                $('.filtro_le').addClass('d-none');
                $('.le_'+tipo_esp).removeClass('d-none');
            }
        }
    </script>
@endsection
