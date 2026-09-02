        <div class="user-profile user-card mt-0 bg-fondo-gris">
            <div class="col-md-12 py-0 px-2 shadow-none">
                <div class="row mx-0">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        @if (isset($titulo) && $titulo == 'NO')
                            {{--  nada  --}}
                        @else
                    </div>
                    @endif
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <h4 class="text-c-blue mt-3 f-20">Historial de atenciones</h4>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                        <div class="dt-responsive table-responsive">
                                            <table id="table_atenciones_profesional"
                                                class="display table table-striped dt-responsive nowrap align-middle table-xs"
                                                style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th class="d-none">ID</th>
                                                        <th>Fecha</th>
                                                        <th>Diagnóstico</th>
                                                        <th>Ficha clínica</th>
                                                        @if($profesional->id_tipo_especialidad != 8 && $profesional->id_especialidad != 16 && $profesional->id_sub_tipo_especialidad != 121)
                                                        <th>Ev. Especialidad</th>
                                                        @endif
                                                        <th>Exámenes</th>
                                                        <th>Recetas</th>

                                                        @if ($profesional->id_especialidad == 2)
                                                            <th>Presupuestos</th>
                                                        @endif
                                                        @if ($profesional->id_especialidad == 2)
                                                            <th>Tratamientos </th>
                                                        @endif
                                                        @if ($profesional->id_especialidad == 2)
                                                            <th>Evoluciones </th>
                                                        @endif

                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @if (isset($fichas) && $fichas->count() > 0)
                                                        @foreach ($fichas as $f)
                                                            @php
                                                                $cantidadRecetasHistorial = method_exists($f, 'Recetas')
                                                                    ? $f->Recetas()->count()
                                                                    : 0;
                                                                $cantidadExamenesHistorial = method_exists($f, 'Examenes')
                                                                    ? $f->Examenes()->count()
                                                                    : 0;
                                                            @endphp
                                                            <tr>
                                                                <td class="d-none">
                                                                    {{ $f->id }}
                                                                </td>
                                                                <td>
                                                                    {{ \Carbon\Carbon::parse($f->created_at)->format('d/m/Y') }}
                                                                </td>

                                                                <td>{{ $f->hipotesis_diagnostico }}</td>

                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-xxs btn-info-light-c historial-doc-btn tiene-contenido"
                                                                        @if (isset($f->id)) onclick="buscar_ficha_atencion_atencion_previa({{ $f->id }});" @endif><i
                                                                            class="feather icon-file-text"></i>
                                                                        Ver ficha <span class="historial-status-dot"></span></button>
                                                                </td>
                                                                @if($profesional->id_tipo_especialidad != 8 && $profesional->id_especialidad != 16 && $profesional->id_sub_tipo_especialidad != 121)
                                                                <td><button type="button"
                                                                            class="btn btn-xxs btn-primary-light-c"
                                                                            @if (isset($f->id)) onclick="buscar_evaluaciones_especialidad({{ $f->id }});" @endif><i
                                                                                class="feather icon-folder"></i>
                                                                            Ver</button></td>
                                                                @endif
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-xxs historial-doc-btn {{ $cantidadExamenesHistorial > 0 ? 'btn-success-light-c tiene-contenido' : 'btn-light sin-contenido' }}"
                                                                        {{ $cantidadExamenesHistorial > 0 ? '' : 'disabled' }}
                                                                        @if ($cantidadExamenesHistorial > 0 && isset($f->id)) onclick="buscar_examenes({{ $f->id }});" @endif><i
                                                                            class="feather icon-activity"></i>
                                                                        {{ $cantidadExamenesHistorial > 0 ? 'Ver' : 'Sin registros' }}
                                                                        @if ($cantidadExamenesHistorial > 0)<span class="historial-count">{{ $cantidadExamenesHistorial }}</span>@endif
                                                                    </button>
                                                                </td>
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-xxs historial-doc-btn {{ $cantidadRecetasHistorial > 0 ? 'btn-warning-light-c tiene-contenido' : 'btn-light sin-contenido' }}"
                                                                        {{ $cantidadRecetasHistorial > 0 ? '' : 'disabled' }}
                                                                        @if ($cantidadRecetasHistorial > 0 && isset($f->id)) onclick="buscar_receta({{ $f->id }});" @endif><i
                                                                            class="feather icon-file-plus"></i>
                                                                        {{ $cantidadRecetasHistorial > 0 ? 'Ver' : 'Sin registros' }}
                                                                        @if ($cantidadRecetasHistorial > 0)<span class="historial-count">{{ $cantidadRecetasHistorial }}</span>@endif
                                                                    </button>
                                                                </td>

                                                                @if ($profesional->id_especialidad == 2)
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-xxs btn-purple-light-c"
                                                                            @if (isset($f->id)) onclick="generar_pdf_historial({{ $f->id }});" @endif><i
                                                                                class="feather icon-folder"></i>
                                                                            PDF</button>
                                                                    </td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-xxs btn-success-light-c"
                                                                            @if (isset($f->id)) onclick="buscar_trabajos({{ $f->id }});" @endif>
                                                                            Tratamientos </button>
                                                                    </td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-xxs btn-primary-light-c"
                                                                            onclick="buscar_evoluciones({{ $f->id }})">Evoluciones</button>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <span style="text-align: center">
                                                            <h5>No existen registros</h5>
                                                        </span>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--MODALS-->
        @include('general.secciones_ficha.modal_atencion_previa.hist_cons_receta')
        @include('general.secciones_ficha.modal_atencion_previa.hist_cons_examen')
        @include('general.secciones_ficha.modal_atencion_previa.hist_cons_archivo')
        @include('general.secciones_ficha.modal_atencion_previa.hist_trabajos_dental')
        @include('general.secciones_ficha.modal_atencion_previa.hist_evoluciones_dental')
        @include('general.secciones_ficha.modal_atencion_previa.hist_cons')
        @include('general.secciones_ficha.modal_atencion_previa.evaluaciones_especialidad')
        <style>
            #table_atenciones_profesional .historial-doc-btn {
                min-width: 88px;
                min-height: 30px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 5px;
                border-radius: 16px;
                font-weight: 600;
                transition: transform .16s ease, box-shadow .16s ease;
            }
            #table_atenciones_profesional .historial-doc-btn.tiene-contenido:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 10px rgba(33, 60, 77, .16);
            }
            #table_atenciones_profesional .historial-doc-btn.sin-contenido {
                color: #98a2b3 !important;
                background: #f2f4f7 !important;
                border: 1px solid #e4e7ec !important;
                cursor: not-allowed;
                opacity: .82;
            }
            #table_atenciones_profesional .historial-count {
                min-width: 18px;
                height: 18px;
                padding: 0 5px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 9px;
                color: #fff;
                background: rgba(30, 41, 59, .72);
                font-size: 10px;
                line-height: 1;
            }
            #table_atenciones_profesional .historial-status-dot {
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #19a99d;
                box-shadow: 0 0 0 3px rgba(25, 169, 157, .15);
            }
        </style>
        <script>
            $(document).ready(function() {
                var selectorTablaHistorial = '#table_atenciones_profesional';
                if ($.fn.DataTable && $.fn.DataTable.isDataTable(selectorTablaHistorial)) {
                    $(selectorTablaHistorial).DataTable().order([[0, 'desc']]).draw();
                } else if ($.fn.DataTable) {
                    $(selectorTablaHistorial).DataTable({
                        responsive: true,
                        order: [[0, 'desc']]
                    });
                }
            });

            function historialTextoSeguro(valor) {
                return $('<div>').text(valor == null || valor === '' ? '-' : valor).html();
            }

            function historialFecha(valor) {
                if (!valor) return '-';
                var fecha = new Date(valor);
                return isNaN(fecha.getTime()) ? valor : fecha.toLocaleDateString('es-CL');
            }

            function historialTotal(valor) {
                var numero = Number(String(valor == null ? 0 : valor).replace(/[^0-9.-]/g, ''));
                return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 })
                    .format(isNaN(numero) ? 0 : numero);
            }

            function buscar_trabajos(idFichaClinica) {
                var $cuerpo = $('#table_atenciones_previas_trabajos tbody');
                $cuerpo.html('<tr><td colspan="4" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando tratamientos...</td></tr>');
                $('#valor_odontograma_hist').empty();
                $('#m_cons_trabajos').modal('show');

                $.getJSON("{{ route('ficha_atencion.ver_trabajos') }}", { id_ficha_atencion_soli: idFichaClinica })
                    .done(function (data) {
                        var registros = data && Array.isArray(data.odontograma) ? data.odontograma : [];
                        var nombre = data && data.mascota ? data.mascota.nombre : (data && data.paciente ? data.paciente.nombres : '');
                        $('#m_cons_trabajosLabel').text('Tratamientos realizados' + (nombre ? ' · ' + nombre : ''));
                        $cuerpo.empty();

                        if (!registros.length) {
                            $cuerpo.html('<tr><td colspan="4" class="text-center text-muted py-3">No existen tratamientos registrados</td></tr>');
                            return;
                        }

                        registros.forEach(function (item) {
                            $cuerpo.append('<tr>' +
                                '<td>' + historialTextoSeguro(historialFecha(item.fecha)) + '</td>' +
                                '<td>' + historialTextoSeguro(item.pieza) + '</td>' +
                                '<td>' + historialTextoSeguro(item.diagnostico) + '</td>' +
                                '<td>' + historialTextoSeguro(item.tratamiento) + '</td>' +
                            '</tr>');
                        });

                        if (data.valores_odontograma && data.valores_odontograma[1] != null) {
                            $('#valor_odontograma_hist').text('Total odontograma: ' + historialTotal(data.valores_odontograma[1]));
                        }
                    })
                    .fail(function () {
                        $cuerpo.html('<tr><td colspan="4" class="text-center text-danger py-3">No fue posible cargar los tratamientos</td></tr>');
                    });
            }

            function buscar_evoluciones(idFichaClinica) {
                var $contenedor = $('#odontograma-pills-container');
                $('#odontograma-tab-content, #valor_odontograma_evol').empty();
                $contenedor.html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando evoluciones...</div>');
                $('#m_cons_evoluciones').modal('show');

                $.getJSON("{{ route('ficha_atencion.ver_evoluciones') }}", { id_ficha_atencion_soli: idFichaClinica })
                    .done(function (data) {
                        var registros = data && Array.isArray(data.odontograma) ? data.odontograma : [];
                        var nombre = data && data.mascota ? data.mascota.nombre : (data && data.paciente ? data.paciente.nombres : '');
                        $('#m_cons_EvolucionesLabel').text('Evoluciones realizadas' + (nombre ? ' · ' + nombre : ''));
                        $contenedor.empty();

                        if (!registros.length) {
                            $contenedor.html('<div class="alert alert-info text-center mb-0">No existen evoluciones registradas</div>');
                            return;
                        }

                        registros.forEach(function (item) {
                            $contenedor.append('<div class="card mb-2"><div class="card-body py-3">' +
                                '<h6 class="text-info mb-2">Pieza ' + historialTextoSeguro(item.pieza) + '</h6>' +
                                '<div><strong>Fecha:</strong> ' + historialTextoSeguro(historialFecha(item.fecha)) + '</div>' +
                                '<div><strong>Diagnóstico:</strong> ' + historialTextoSeguro(item.diagnostico) + '</div>' +
                                '<div><strong>Tratamiento:</strong> ' + historialTextoSeguro(item.tratamiento) + '</div>' +
                            '</div></div>');
                        });

                        if (data.valores_odontograma && data.valores_odontograma[1] != null) {
                            $('#valor_odontograma_evol').text('Total odontograma: ' + historialTotal(data.valores_odontograma[1]));
                        }
                    })
                    .fail(function () {
                        $contenedor.html('<div class="alert alert-danger text-center mb-0">No fue posible cargar las evoluciones</div>');
                    });
            }

            function generar_pdf_historial(idFichaClinica) {
                var ventanaPdf = window.open('', '_blank');
                if (ventanaPdf) {
                    ventanaPdf.document.write('<div style="font-family:Arial,sans-serif;padding:30px;text-align:center">Generando presupuesto...</div>');
                }
                $.ajax({
                    url: "{{ route('profesional.generar_pdf_presupuesto_dental_hist') }}",
                    type: 'POST',
                    data: {
                        id_paciente: $('#id_paciente').val(),
                        id_ficha_atencion: idFichaClinica,
                        _token: "{{ csrf_token() }}"
                    }
                }).done(function (data) {
                    if (data && data.ruta) {
                        if (ventanaPdf) {
                            ventanaPdf.location.href = data.ruta;
                        } else {
                            window.location.href = data.ruta;
                        }
                        return;
                    }
                    if (ventanaPdf) ventanaPdf.close();
                    swal({
                        title: 'Presupuesto',
                        text: data && data.error ? data.error : 'No fue posible generar el presupuesto.',
                        icon: 'error',
                        button: 'Aceptar'
                    });
                }).fail(function () {
                    if (ventanaPdf) ventanaPdf.close();
                    swal({
                        title: 'Presupuesto',
                        text: 'No fue posible generar el presupuesto.',
                        icon: 'error',
                        button: 'Aceptar'
                    });
                });
            }
        </script>
