@extends('template.paciente_dependiente.template')
@section('content')
<!--Container Completo-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="row">
             <div class="col-md-12 mb-3 encabezado-pagina">
                <div>
                    <h5 class="f-26 d-block mb-0">Registro de vacunas</h5>
                </div>
                <div class="ml-auto">
                    <button type="button" class="btn  btn-info " data-toggle="modal" data-target="#modal_vacuna">
                            <i class="feather icon-plus"></i> Agregar registro
                    </button>
                </div>
            </div>
        </div>
        <!--Cierre: Header-->
        <div class="row">
            <div class="col-12">
                <div class="card">
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

@php
    // Datos de la mascota que se precargan en el modal
    $especieNombreMascota = trim((string) (optional($mascota->especieMascota)->nombre ?: ($mascota->otra_especie ?: $mascota->especie)));
    $especieNormalizada = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($especieNombreMascota));
    if ($especieNombreMascota === '') {
        $especieValorMascota = '';
    } elseif (\Illuminate\Support\Str::startsWith($especieNormalizada, ['canin', 'perr'])) {
        $especieValorMascota = 'canina';
    } elseif (\Illuminate\Support\Str::startsWith($especieNormalizada, ['felin', 'gat'])) {
        $especieValorMascota = 'felina';
    } else {
        $especieValorMascota = 'otra';
    }
    $nacimientoMascota = !empty($mascota->fecha_nacimiento) ? \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('Y-m-d') : '';
@endphp

<div class="modal fade" id="modal_vacuna" tabindex="-1" role="dialog" aria-labelledby="modal_vacuna_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="modal_vacuna_label"><i class="fas fa-syringe mr-2"></i>Añadir vacuna</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    Registre aquí una vacuna administrada a <strong>{{ $mascota->nombre ?? 'su mascota' }}</strong>.
                </div>
                <form id="form_vacuna_modal" novalidate>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm">Fecha de la dosis *</label>
                            <input type="date" class="form-control form-control-sm campo-obligatorio" id="vac_fecha_dosis_modal" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm">Próxima dosis *</label>
                            <input type="date" class="form-control form-control-sm campo-obligatorio" id="vac_proxima_dosis_modal" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Vacuna *</label>
                        <input type="text" class="form-control form-control-sm campo-obligatorio" id="vac_nombre_modal" maxlength="255" placeholder="Ej.: Antirrábica" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-7">
                            <label class="floating-label-activo-sm">Edad al momento de la dosis *</label>
                            <input type="text" class="form-control form-control-sm campo-obligatorio" id="vac_edad_modal" maxlength="120" placeholder="Ej.: 1 año 3 meses" required>
                        </div>
                        <div class="form-group col-md-5">
                            <label class="floating-label-activo-sm">Especie *</label>
                            @if($especieNombreMascota !== '')
                                {{-- La especie viene de la ficha de la mascota --}}
                                <input type="text" class="form-control form-control-sm" value="{{ $especieNombreMascota }}" readonly>
                                <input type="hidden" id="vac_especie_modal" value="{{ $especieValorMascota }}">
                            @else
                                <select class="form-control form-control-sm campo-obligatorio" id="vac_especie_modal" required>
                                    <option value="">Seleccione</option>
                                    <option value="canina">Canina</option>
                                    <option value="felina">Felina</option>
                                    <option value="otra">Otra</option>
                                </select>
                            @endif
                        </div>
                    </div>
                    <div class="text-right pt-2">
                        <button type="button" class="btn btn-outline-dark rounded-pill px-4" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info rounded-pill px-4" id="btn_guardar_vacuna">
                            <i class="feather icon-save"></i> Guardar vacuna
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

        // Edad de la mascota a la fecha de la dosis, según su fecha de nacimiento
        var nacimientoMascotaVacuna = "{{ $nacimientoMascota }}";
        var edadVacunaEditada = false;

        function calcularEdadVacuna(fechaDosis) {
            if (!nacimientoMascotaVacuna || !fechaDosis) {
                return '';
            }
            var nacimiento = moment(nacimientoMascotaVacuna, 'YYYY-MM-DD');
            var dosis = moment(fechaDosis, 'YYYY-MM-DD');
            if (!nacimiento.isValid() || !dosis.isValid() || dosis.isBefore(nacimiento)) {
                return '';
            }
            var anios = dosis.diff(nacimiento, 'years');
            var meses = dosis.diff(nacimiento.clone().add(anios, 'years'), 'months');
            if (anios === 0 && meses === 0) {
                var dias = dosis.diff(nacimiento, 'days');
                return dias + (dias === 1 ? ' día' : ' días');
            }
            var partes = [];
            if (anios > 0) { partes.push(anios + (anios === 1 ? ' año' : ' años')); }
            if (meses > 0) { partes.push(meses + (meses === 1 ? ' mes' : ' meses')); }
            return partes.join(' ');
        }

        function actualizarEdadVacuna() {
            if (edadVacunaEditada) {
                return;
            }
            $('#vac_edad_modal').val(calcularEdadVacuna($('#vac_fecha_dosis_modal').val()));
        }

        // Si el tutor escribe la edad a mano, se respeta lo que escribió
        $(document).on('input', '#vac_edad_modal', function () {
            edadVacunaEditada = $.trim($(this).val()) !== '';
        });

        $(document).on('change input', '#vac_fecha_dosis_modal', actualizarEdadVacuna);

        $(document).on('show.bs.modal', '#modal_vacuna', function () {
            edadVacunaEditada = false;
            if (!$('#vac_fecha_dosis_modal').val()) {
                $('#vac_fecha_dosis_modal').val(moment().format('YYYY-MM-DD'));
            }
            actualizarEdadVacuna();
        });

        // Todos los campos del modal son obligatorios
        function validarCamposObligatorios(formulario) {
            var $faltantes = $(formulario).find('.campo-obligatorio').removeClass('is-invalid').filter(function () {
                return $.trim($(this).val() || '') === '';
            });

            if (!$faltantes.length) {
                return true;
            }

            $faltantes.addClass('is-invalid');
            $faltantes.first().trigger('focus');
            swal({title: 'Faltan datos', text: 'Debe completar todos los campos para guardar el registro.', icon: 'warning'});
            return false;
        }

        $(document).on('change input', '.campo-obligatorio', function () {
            if ($.trim($(this).val() || '') !== '') {
                $(this).removeClass('is-invalid');
            }
        });

        $(document).on('submit', '#form_vacuna_modal', function (e) {
            e.preventDefault();
            if (!validarCamposObligatorios(this)) {
                return;
            }
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
