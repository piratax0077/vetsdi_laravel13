

<style>
    .ficha-vet-general-wrapper,
    .ficha-vet-general-wrapper > .user-profile {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }
    .ficha-vet-general-wrapper .tab-content,
    .ficha-vet-general-wrapper .tab-pane,
    .ficha-vet-general-wrapper .card { max-width: 100%; }
    .ficha-vet-general-wrapper .nav-tabs-secciones,
    .ficha-vet-general-wrapper .form-row { width: 100%; }
    .ficha-vet-general-wrapper .card-body { overflow: hidden; }
    .presupuesto-vet-mensaje {
        min-height: 48px;
        display: flex;
        align-items: center;
        margin-bottom: 0;
    }
    .ficha-vet-tabs-row {
        justify-content: center;
        margin-right: 0;
        margin-left: 0;
    }
    .ficha-vet-tabs-row .nav-tabs-secciones {
        justify-content: center;
        width: auto;
    }
    .ficha-vet-avisos {
        align-items: stretch;
        margin-right: 0;
        margin-left: 0;
        opacity: 1;
        transition: opacity .45s ease, max-height .45s ease, margin .45s ease;
        max-height: 90px;
        overflow: hidden;
    }
    .ficha-vet-avisos.is-hiding {
        opacity: 0;
        max-height: 0;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }
    .ficha-vet-avisos > [class*="col-"] {
        display: flex;
    }
    .ficha-vet-avisos .presupuesto-vet-mensaje {
        width: 100%;
    }
    @media (max-width: 767.98px) {
        .ficha-vet-general-wrapper .nav-tabs-secciones {
            display: flex;
            width: 100%;
            overflow-x: auto;
        }
        .ficha-vet-general-wrapper .table-responsive.float-md-right { width: 100% !important; }
    }
</style>

<div class="ficha-vet-general-wrapper">
    <!-- FICHA ATENCION GENERAL -->
    <div class="user-profile user-card mt-0" style="background-color: #ecf0f5!important;">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3">
            <div class="form-row ficha-vet-tabs-row">
                <ul class="nav nav-tabs-secciones mb-3 mt-3" id="orl" role="tablist">
                    <li class="nav-item-secciones">
                        <a class="nav-secciones active text-uppercase" id="atencion_cirugia_gen-tab" data-toggle="tab" href="#atencion_cirugia_gen" role="tab" aria-controls="atencion_cirugia_gen" aria-selected="true">Atención especialidad</a>
                    </li>

                    <li class="nav-item-secciones">
                        <a class="nav-secciones text-uppercase" id="presupuesto-mascota-tab" data-toggle="tab" href="#presupuesto-mascota" role="tab" aria-controls="presupuesto-mascota" aria-selected="false">Presupuestos</a>
                    </li>
                </ul>
            </div>
             <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="form-row mb-1 ficha-vet-avisos" id="ficha-vet-avisos">
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 ">
                            <div class="alert-atencion alert alert-warning-b alert-dismissible fade show presupuesto-vet-mensaje" role="alert" id="mensaje_ficha">
                                <i class="feather icon-alert-circle mr-2"></i>
                                En esta ficha solamente el diagnóstico es obligatorio; los demás campos son opcionales.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 ">
                            <div class="alert-atencion alert alert-success-b alert-dismissible fade show presupuesto-vet-mensaje" role="alert" id="mensaje_historias">
                                <i class="feather icon-info mr-2"></i>
                                La historia veterinaria y los antecedentes están disponibles en FVU e Historial Clínico.
                            </div>
                        </div>
                    </div>
                </div>
            <script>
                (function () {
                    var avisos = document.getElementById('ficha-vet-avisos');
                    if (!avisos) return;

                    window.setTimeout(function () {
                        avisos.classList.add('is-hiding');
                        window.setTimeout(function () {
                            avisos.remove();
                        }, 500);
                    }, 5000);
                }());
            </script>
            <div class="form-row">
               

                <div class="col-sm-12 col-md-12">
                    <form action="{{ route('fichaAtencion.registrar_ficha_vet_general') }}" method="POST">
                        <div class="col-sm-12 col-md-12">
                            <input type="hidden" name="examenes" id="examenes" value="{!! old('examenes') !!}">
                            <input type="hidden" name="examenes_esp" id="examenes_esp" value="{!! old('examenes_esp') !!}">
                            <input type="hidden" name="medicamentos" id="medicamentos" value="{!! old('medicamentos') !!}">
                            <input type="hidden" name="hora_medica" id="hora_medica" value="{{ $hora_medica->id }}">
                            <input type="hidden" name="id_fc" value="{{ $id_ficha_atencion }}" id="id_fc">
                            <input type="hidden" name="id_paciente_fc" value="{{ $paciente->id }}" id="id_paciente_fc">
                            <input type="hidden" name="id_mascota" value="{{ $mascota->id ?? '' }}" id="id_mascota_fc">
                            <input type="hidden" name="rut_paciente_fc" value="{{ $paciente->rut }}" id="rut_paciente_fc">
                            <input type="hidden" name="prevision_paciente_fc" value="{{ $paciente->prevision->id }}" id="prevision_paciente_fc">
                            <input type="hidden" name="id_profesional_fc" value="{{ $profesional->id }}" id="id_profesional_fc">
                            <input type="hidden" name="id_lugar_atencion" id="id_lugar_atencion" value="{{ $id_lugar_atencion }}">
                            <input type="hidden" name="cerrarsession" id="cerrarsession" value="0">
                            <input type="hidden" name="input_lista_imagenes" id="input_lista_imagenes" value="">
                            @csrf

                            <div class="tab-content" id="med-contenido">
                                <!--ATENCIÓN ESPECIALIDAD GENERAL-->
                                <div class="tab-pane fade show active" id="atencion_cirugia_gen" role="tabpanel" aria-labelledby="atencion_cirugia_gen-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                            
                                                <!--Formulario / Menor de edad-->
                                                @include('general.secciones_ficha.seccion_menor', ['tipo_ficha' => "1"])
                                                <!--Cierre: Formulario / Menor de edad-->

                                                <!--Motivo consulta-->
                                                @include('general.secciones_ficha.motivo')
                                                <!--Cierre: Formulario /Motivo de la Consulta-->
                                                @include('general.secciones_ficha.examenfisico')
                                                <!--Formulario / Signos vitales y otros-->
                                                @include('general.secciones_ficha.signos_vitales')
                                                <!--Cierre: Formulario / Signos vitales y otros-->
                                                <!-- cierre hospitalizacion -->

                                                <!-- Antecedentes y controles veterinarios -->
                                                @include('atencion_veterinaria.secciones_especialidad.seccion_antecedentes_veterinarios')
                                                <!-- Cierre antecedentes y controles veterinarios -->

                                                <hr>

                                                <!--Diagnóstico-->
                                                @include('general.secciones_ficha.diagnostico')
                                                <!--Cierre: Formulario / Diagnóstico-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                  <!--PRESUPUESTO CLINICO MASCOTA-->
                                <div class="tab-pane fade" id="presupuesto-mascota" role="tabpanel" aria-labelledby="presupuesto-mascota-tab">
                                 
                                    <div class="row">
                                        <div class="col-md-12 mb-0">
                                            <h6 class="f-18 text-c-blue mb-2">
                                                Presupuesto Clínico N°
                                                <span id="presupuesto_mascota_numero">{{ optional($presupuesto_mascota)->id ?: 'Nuevo' }}</span>
                                            </h6>
                                        </div>
                                    </div>
                                    <input type="hidden" id="presupuesto_mascota_id_paciente" value="{{ $paciente->id }}">
                                    <input type="hidden" id="presupuesto_mascota_id_profesional" value="{{ $profesional->id }}">
                                    <input type="hidden" id="presupuesto_mascota_id_ficha_atencion" value="{{ $id_ficha_atencion }}">
                                    <input type="hidden" id="presupuesto_mascota_id_lugar_atencion" value="{{ $id_lugar_atencion }}">

                                    <!--NUEVO PRESUPUESTO-->
                                    <div class="row">
                                        <div class="col-md-12 mb-0">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4">
                                                            <label class="floating-label-activo-sm">Nombre del presupuesto</label>
                                                            <input type="text" class="form-control form-control-sm" id="presupuesto_mascota_nombre"
                                                                value="{{ data_get(optional($presupuesto_mascota)->datos_atencion, 'nombre', '') }}">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label class="floating-label-activo-sm">Clínica</label>
                                                            <input type="text" class="form-control form-control-sm" id="presupuesto_mascota_clinica"
                                                                value="{{ data_get(optional($presupuesto_mascota)->datos_atencion, 'clinica', '') }}">
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <label class="floating-label-activo-sm">Fecha</label>
                                                            <input type="date" class="form-control form-control-sm" id="presupuesto_mascota_fecha"
                                                                value="{{ optional(optional($presupuesto_mascota)->fecha)->format('Y-m-d') ?: now()->format('Y-m-d') }}">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label class="floating-label-activo-sm">Animal</label>
                                                            <input type="text" class="form-control form-control-sm" id="presupuesto_mascota_animal"
                                                                value="{{ data_get(optional($presupuesto_mascota)->datos_atencion, 'animal', optional($mascota ?? null)->nombre ?? '') }}">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label class="floating-label-activo-sm">Especie</label>
                                                            <input type="text" class="form-control form-control-sm" id="presupuesto_mascota_especie"
                                                                value="{{ data_get(optional($presupuesto_mascota)->datos_atencion, 'especie', data_get($mascota ?? null, 'especieMascota.nombre', '')) }}">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label class="floating-label-activo-sm">Identificación</label>
                                                            <input type="text" class="form-control form-control-sm" id="presupuesto_mascota_identificacion"
                                                                value="{{ data_get(optional($presupuesto_mascota)->datos_atencion, 'identificacion', optional($mascota ?? null)->microchip ?? '') }}"
                                                                placeholder="Microchip u otra documentación acreditativa">
                                                        </div>
                                                        <div class="form-group col-md-12">
                                                            <label class="floating-label-activo-sm">Observaciones</label>
                                                            <input type="text" class="form-control form-control-sm" id="presupuesto_mascota_observaciones"
                                                                value="{{ optional($presupuesto_mascota)->observaciones }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-0">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="form-row align-items-end">
                                                        <div class="form-group col-md-6">
                                                            <label class="floating-label-activo-sm">Diagnóstico</label>
                                                            <select class="form-control form-control-sm" id="presupuesto_vet_diagnostico">
                                                                <option value="">Seleccione</option>
                                                                @foreach ($diagnosticos_vet as $diagnostico_vet)
                                                                    <option value="{{ $diagnostico_vet->id }}">{{ $diagnostico_vet->descripcion }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label class="floating-label-activo-sm">Tratamiento</label>
                                                            <select class="form-control form-control-sm" id="presupuesto_vet_tratamiento">
                                                                <option value="">Seleccione</option>
                                                                @if(($tarifas_veterinarias ?? collect())->isNotEmpty())
                                                                    <optgroup label="Tarifario del profesional">
                                                                        @foreach ($tarifas_veterinarias as $tarifa_veterinaria)
                                                                            <option value="tarifa:{{ $tarifa_veterinaria->id }}" data-valor="{{ $tarifa_veterinaria->valor }}">{{ $tarifa_veterinaria->tipo_atencion }} — ${{ number_format($tarifa_veterinaria->valor, 0, ',', '.') }}</option>
                                                                        @endforeach
                                                                    </optgroup>
                                                                @endif
                                                                <optgroup label="Tratamientos generales">
                                                                @foreach ($tratamientos_vet as $tratamiento_vet)
                                                                    <option value="{{ $tratamiento_vet->id }}" data-valor="{{ $tratamiento_vet->valor ?? 0 }}">{{ $tratamiento_vet->descripcion }}</option>
                                                                @endforeach
                                                                </optgroup>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-2">
                                                            <button type="button" class="btn btn-sm btn-info btn-block" id="btn_agregar_presupuesto_vet">
                                                                <i class="feather icon-plus"></i>Añadir a presupuesto
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                            <div class="col-12">
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                        <th scope="col">Item</th>
                                                                        <th scope="col">Descripción</th>
                                                                        <th scope="col">Valor Unitario</th>
                                                                        <th scope="col">Cantidad</th>
                                                                        <th scope="col">Valor Total</th>
                                                                        <th scope="col">Descuento</th>
                                                                        <th scope="col">Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="presupuesto_vet_items">
                                                                        @foreach ($presupuestos_vet as $presupuesto_vet)
                                                                            @php
                                                                                $valor_unitario = $presupuesto_vet->valor_tratamiento ?? $presupuesto_vet->valor ?? 0;
                                                                                $cantidad = $presupuesto_vet->cantidad ?? 1;
                                                                                $valor_total = $valor_unitario * $cantidad;
                                                                            @endphp
                                                                            <tr data-id="{{ $presupuesto_vet->id }}">
                                                                                <td>{{ $loop->iteration }}</td>
                                                                                <td>{{ $presupuesto_vet->diagnostico }} / {{ $presupuesto_vet->tratamiento }}</td>
                                                                                <td>${{ number_format($valor_unitario, 0, ',', '.') }}</td>
                                                                                <td>{{ $cantidad }}</td>
                                                                                <td>${{ number_format($valor_total, 0, ',', '.') }}</td>
                                                                                <td>0%</td>
                                                                                <td>
                                                                                    <button type="button" class="btn btn-icon btn-danger btn-eliminar-presupuesto-vet" data-id="{{ $presupuesto_vet->id }}">
                                                                                        <i class="feather icon-x"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="table-responsive float-md-right" style="width: 300px">
                                                                    <table class="table table-sm table-bordered">
                                                                    <tbody>
                                                                        @php
                                                                            $subtotal_presupuesto_vet = 0;
                                                                            foreach ($presupuestos_vet as $presupuesto_vet) {
                                                                                $valor_unitario = $presupuesto_vet->valor_tratamiento ?? $presupuesto_vet->valor ?? 0;
                                                                                $cantidad = $presupuesto_vet->cantidad ?? 1;
                                                                                $subtotal_presupuesto_vet += ($valor_unitario * $cantidad);
                                                                            }
                                                                            $iva_presupuesto_vet = $subtotal_presupuesto_vet * 0.19;
                                                                            $total_presupuesto_vet = $subtotal_presupuesto_vet + $iva_presupuesto_vet;
                                                                        @endphp
                                                                        <tr>
                                                                        <th class="bg-light">Descuento</th>
                                                                        <td>0%</td>
                                                                        </tr>
                                                                        <tr>
                                                                        <th class="bg-light">Subtotal </th>
                                                                        <td id="presupuesto_vet_subtotal">${{ number_format($subtotal_presupuesto_vet, 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                        <th class="bg-light">IVA (19%)</th>
                                                                        <td id="presupuesto_vet_iva">${{ number_format($iva_presupuesto_vet, 0, ',', '.') }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                        <th class="bg-purple text-white">TOTAL</th>
                                                                        <th class="text-purple" id="presupuesto_vet_total">${{ number_format($total_presupuesto_vet, 0, ',', '.') }}</th>
                                                                        </tr>
                                                                    </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                    </div>
                                                    <hr>
                                                    <div class="form-row">
                                                        <div class="col-md-12 text-center mb-3">
                                                            <button type="button" class="btn btn-sm btn-danger" id="btn_pdf_presupuesto_mascota"><i class="fas fa-file-pdf"></i> Generar PDF</button>
                                                            <button type="button" class="btn btn-sm btn-primary" id="btn_envia_email_presupuesto_mascota"><i class="feather icon-mail"></i> Enviar a email</button>
                                                            <button type="button" onclick="window.print()" class="btn btn-sm btn-secondary" id="btn_imprimir_presupuesto_mascota"><i class="feather icon-printer"></i> Imprimir</button>
                                                            <button type="button" class="btn btn-sm btn-info" id="btn_guardar_presupuesto_mascota"><i class="feather icon-save"></i> Guardar presupuesto</button>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div id="presupuesto_mascota_resultado" class="text-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                            </div>
                        </div>

                        {{--  div de botones  --}}
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <!--SECCION DE MEDICAMENTOS Y EXAMENES GENERALES -->
                                        @include('general.secciones_ficha.seccion_receta_examen_comunes')
                                        <!--SECCION DE MEDICAMENTOS Y EXAMENES GENERALES FIN  -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--GUARDAR O IMPRIMIR FICHA-->
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <input type="submit" class="btn btn-purple mt-1" onclick="$('#cerrarsession').val('1');agregar_medicamentos_ficha(); agregar_examenes_ficha(); " value="Guardar ficha y finalizar su Consulta">
                                    <input type="submit" class="btn btn-info mt-1" onclick="agregar_medicamentos_ficha(); agregar_examenes_ficha(); " value="Guardar ficha e ir a su agenda">
                                </div>
                            </div>
                        </div>
                        <!--CIERRE: ATENCIÓN ESPECIALIDAD GENERAL-->
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--CIERRE: FICHA ATENCION GENERAL-->
</div>
@section('page-script-ficha-atencion')
    <script>
            $(document).ready(function() {

                $('#btn_guardar_presupuesto_mascota').on('click', function() {
                var esTarifa = String(tratamientoId).indexOf('tarifa:') === 0;
                var payload = {
                    _token: '{{ csrf_token() }}',
                    id_paciente: $('#presupuesto_mascota_id_paciente').val(),
                    id_profesional: $('#presupuesto_mascota_id_profesional').val(),
                    id_ficha_atencion: $('#presupuesto_mascota_id_ficha_atencion').val(),
                    id_lugar_atencion: $('#presupuesto_mascota_id_lugar_atencion').val(),
                    fecha: $('#presupuesto_mascota_fecha').val(),
                    fecha_control: $('#presupuesto_mascota_fecha_control').val(),
                    estado: $('#presupuesto_mascota_estado').val(),
                    aprobado: $('#presupuesto_mascota_aprobado').val(),
                    datos_atencion: JSON.stringify({
                        nombre: $('#presupuesto_mascota_nombre').val(),
                        clinica: $('#presupuesto_mascota_clinica').val(),
                        animal: $('#presupuesto_mascota_animal').val(),
                        especie: $('#presupuesto_mascota_especie').val(),
                        identificacion: $('#presupuesto_mascota_identificacion').val()
                    }),
                    otros: $('#presupuesto_mascota_otros').val(),
                    observaciones: $('#presupuesto_mascota_observaciones').val()
                };

                $.ajax({
                    url: '{{ route('profesional.presupuesto_mascota.guardar') }}',
                    type: 'POST',
                    data: payload
                })
                .done(function(resp) {
                    if (!resp || Number(resp.estado) !== 1) {
                        $('#presupuesto_mascota_resultado')
                            .removeClass('text-success')
                            .addClass('text-danger')
                            .text((resp && resp.msj) ? resp.msj : 'No se pudo guardar el presupuesto.');
                        return;
                    }
                    $('#presupuesto_mascota_numero').text(resp.id);
                    $('#presupuesto_mascota_resultado')
                        .removeClass('text-danger')
                        .addClass('text-success')
                        .text(resp.msj || 'Presupuesto guardado.');
                })
                .fail(function(xhr) {
                    var mensaje = 'Error al guardar presupuesto.';
                    if (xhr.responseJSON && xhr.responseJSON.msj) {
                        mensaje = xhr.responseJSON.msj;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        var detalle = Object.values(xhr.responseJSON.error).flat().join(' ');
                        if (detalle) mensaje += ' ' + detalle;
                    }
                    $('#presupuesto_mascota_resultado')
                        .removeClass('text-success')
                        .addClass('text-danger')
                        .text(mensaje);
                });
            });

            function formatearMoneda(valor)
            {
                if (valor === null || valor === undefined) return '-';
                var numero = Number(valor);
                if (Number.isNaN(numero)) return '-';
                return '$' + numero.toLocaleString('es-CL');
            }

            function actualizarCorrelativoPresupuesto()
            {
                $('#presupuesto_vet_items tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function parsearMoneda(texto) 
            {
                if (!texto) return 0;
                var limpio = texto.replace(/[^0-9]/g, '');
                return limpio ? Number(limpio) : 0;
            }

            function actualizarTotalesPresupuesto() 
            {
                var subtotal = 0;
                $('#presupuesto_vet_items tr').each(function() {
                    var valorTotal = parsearMoneda($(this).find('td').eq(4).text());
                    subtotal += valorTotal;
                });
                var iva = Math.round(subtotal * 0.19);
                var total = subtotal + iva;

                $('#presupuesto_vet_subtotal').text(formatearMoneda(subtotal));
                $('#presupuesto_vet_iva').text(formatearMoneda(iva));
                $('#presupuesto_vet_total').text(formatearMoneda(total));
            }

            $('#btn_agregar_presupuesto_vet').on('click', function() {
                var diagnosticoId = $('#presupuesto_vet_diagnostico').val();
                var tratamientoId = $('#presupuesto_vet_tratamiento').val();

                if (!diagnosticoId || !tratamientoId) {
                    swal({
                        title: 'Aviso',
                        text: 'Seleccione diagnostico y tratamiento.',
                        icon: 'warning',
                    });
                    return;
                }

                var payload = {
                    _token: '{{ csrf_token() }}',
                    id_paciente: $('#presupuesto_mascota_id_paciente').val(),
                    id_profesional: $('#presupuesto_mascota_id_profesional').val(),
                    id_ficha_atencion: $('#presupuesto_mascota_id_ficha_atencion').val(),
                    id_lugar_atencion: $('#presupuesto_mascota_id_lugar_atencion').val(),
                    id_diagnostico: diagnosticoId,
                    id_tratamiento: esTarifa ? '' : tratamientoId,
                    id_tarifa_veterinaria: esTarifa ? String(tratamientoId).replace('tarifa:', '') : ''
                };

                $.ajax({
                    url: '{{ route('profesional.presupuesto_vet.guardar_item') }}',
                    type: 'POST',
                    data: payload
                })
                .done(function(resp) {
                    if (!resp || resp.estado !== 1 || !resp.item) {
                        return;
                    }
                    var item = resp.item;
                    var valor = item.valor || 0;
                    var cantidad = item.cantidad || 1;
                    var total = valor * cantidad;

                    $('#presupuesto_vet_items').append(
                        '<tr data-id="' + item.id + '">' +
                        '<td></td>' +
                        '<td>' + item.diagnostico + ' / ' + item.tratamiento + '</td>' +
                        '<td>' + formatearMoneda(valor) + '</td>' +
                        '<td>' + cantidad + '</td>' +
                        '<td>' + formatearMoneda(total) + '</td>' +
                        '<td>0%</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-icon btn-danger btn-eliminar-presupuesto-vet" data-id="' + item.id + '">' +
                        '<i class="feather icon-x"></i>' +
                        '</button>' +
                        '</td>' +
                        '</tr>'
                    );

                    $('#presupuesto_vet_diagnostico').val('');
                    $('#presupuesto_vet_tratamiento').val('');
                    actualizarCorrelativoPresupuesto();
                    actualizarTotalesPresupuesto();
                    swal({
                        title: 'Agregado',
                        text: 'Tratamiento agregado al presupuesto.',
                        icon: 'success',
                    });
                })
                .fail(function(xhr) {
                    var mensaje = 'Error al agregar item.';
                    if (xhr.responseJSON && xhr.responseJSON.msj) {
                        mensaje = xhr.responseJSON.msj;
                    }
                    swal({
                        title: 'Error',
                        text: mensaje,
                        icon: 'error',
                    });
                });
            });

            $('#presupuesto_vet_items').on('click', '.btn-eliminar-presupuesto-vet', function() {
                var id = $(this).data('id');
                if (!id) return;

                $.ajax({
                    url: '{{ route('profesional.presupuesto_vet.eliminar_item') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id
                    }
                })
                .done(function(resp) {
                    if (!resp || resp.estado !== 1) {
                        return;
                    }
                    $('#presupuesto_vet_items').find('tr[data-id="' + id + '"]').remove();
                    actualizarCorrelativoPresupuesto();
                    actualizarTotalesPresupuesto();
                })
                .fail(function(xhr) {
                    var mensaje = 'Error al eliminar item.';
                    if (xhr.responseJSON && xhr.responseJSON.msj) {
                        mensaje = xhr.responseJSON.msj;
                    }
                    swal({
                        title: 'Error',
                        text: mensaje,
                        icon: 'error',
                    });
                });
            });

            $('#btn_pdf_presupuesto_mascota').on('click', function() {
                var payload = {
                    _token: '{{ csrf_token() }}',
                    id_paciente: $('#presupuesto_mascota_id_paciente').val(),
                    id_ficha_atencion: $('#presupuesto_mascota_id_ficha_atencion').val(),
                    id_lugar_atencion: $('#presupuesto_mascota_id_lugar_atencion').val()
                };

                $.ajax({
                    url: '{{ route('profesional.generar_pdf_presupuesto_vet') }}',
                    type: 'POST',
                    data: payload
                })
                .done(function(resp) {
                    if (resp && resp.ruta) {
                        swal({
                            title: 'Reporte generado',
                            text: 'El reporte se ha generado correctamente.',
                            icon: 'success',
                            button: 'Aceptar'
                        }).then(function() {
                            var width = 800;
                            var height = 600;
                            var left = (screen.width - width) / 2;
                            var top = (screen.height - height) / 2;
                            window.open(resp.ruta, 'Presupuesto veterinario', 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
                        });
                        return;
                    }

                    swal({
                        title: 'Error',
                        text: 'Ha ocurrido un error al generar el reporte.',
                        icon: 'error',
                        button: 'Aceptar'
                    });
                })
                .fail(function(xhr) {
                    var mensaje = 'Error al generar PDF.';
                    if (xhr.responseJSON && xhr.responseJSON.msj) {
                        mensaje = xhr.responseJSON.msj;
                    }
                    swal({
                        title: 'Error',
                        text: mensaje,
                        icon: 'error',
                        button: 'Aceptar'
                    });
                });
            });

            $('#btn_envia_email_presupuesto_mascota').on('click', function() {
                var $btn = $(this);
                var payload = {
                    _token: '{{ csrf_token() }}',
                    id_paciente: $('#presupuesto_mascota_id_paciente').val(),
                    id_ficha_atencion: $('#presupuesto_mascota_id_ficha_atencion').val(),
                    id_lugar_atencion: $('#presupuesto_mascota_id_lugar_atencion').val()
                };

                $btn.prop('disabled', true);
                swal({
                    title: 'Enviando...',
                    text: 'Por favor espere.',
                    icon: 'info',
                    buttons: false,
                    closeOnClickOutside: false,
                    closeOnEsc: false
                });

                $.ajax({
                    url: '{{ route('profesional.enviar_presupuesto_vet_email') }}',
                    type: 'POST',
                    data: payload
                })
                .done(function(resp) {
                    $btn.prop('disabled', false);
                    swal.close();
                    if (resp && resp.estado === 1) {
                        swal({
                            title: 'Presupuesto enviado',
                            text: resp.msj || 'El presupuesto se envió correctamente.',
                            icon: 'success',
                            button: 'Aceptar'
                        });
                        return;
                    }

                    swal({
                        title: 'Error',
                        text: (resp && resp.msj) ? resp.msj : 'No fue posible enviar el presupuesto.',
                        icon: 'error',
                        button: 'Aceptar'
                    });
                })
                .fail(function(xhr) {
                    $btn.prop('disabled', false);
                    swal.close();
                    var mensaje = 'Error al enviar presupuesto.';
                    if (xhr.responseJSON && xhr.responseJSON.msj) {
                        mensaje = xhr.responseJSON.msj;
                    }
                    swal({
                        title: 'Error',
                        text: mensaje,
                        icon: 'error',
                        button: 'Aceptar'
                    });
                });
            });
            actualizarTotalesPresupuesto();
            });

        $(function () 
        {
            const $bcs = $('#imc');
            const $estadoNutricional = $('#estado_nutricional');

            $bcs.attr({
                type: 'number',
                min: 1,
                max: 9,
                step: 1,
                inputmode: 'numeric',
                placeholder: '1 a 9'
            });
            $bcs.closest('.form-group').find('label').first()
                .text('Condición corporal (BCS)');

            if (!$bcs.siblings('.bcs-ayuda').length) 
            {
                $bcs.after(
                    '<small class="form-text text-muted bcs-ayuda">' +
                    'Escala veterinaria 1–9: evaluación visual y por palpación.' +
                    '</small>'
                );
            }

            $estadoNutricional.prop('readonly', true);

            function actualizarEstadoNutricionalBcs() 
            {
                const bcs = Number.parseInt($bcs.val(), 10);
                let estado = '';

                if (bcs >= 1 && bcs <= 3) estado = 'Bajo peso';
                else if (bcs >= 4 && bcs <= 5) estado = 'Condición ideal';
                else if (bcs >= 6 && bcs <= 7) estado = 'Sobrepeso';
                else if (bcs >= 8 && bcs <= 9) estado = 'Obesidad';

                $estadoNutricional.val(estado);
            }

            $bcs.on('input change', actualizarEstadoNutricionalBcs);
            actualizarEstadoNutricionalBcs();
        });

    

        $(document).ready(function() {
            $("#descripcion_cie").autocomplete({
                source: function(request, response) {
                    // Fetch data
                    $.ajax({
                        url: "{{ route('dental.getCie10') }}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            _token: CSRF_TOKEN,
                            search: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(event, ui) {
                    // Set selection
                    $('#descripcion_cie').val(ui.item.label); // display the selected text
                    $('#id_descripcion_cie').val(ui.item.value); // save selected id to input
                    return false;
                }
            });
        });

        function cargarIgual(input)
        {

            let actual = $('#'+input);
            let equivalentes = $('#'+input).attr('data-input_igual').split(',');
            $.each(equivalentes, function( index, value ) {
                var equivalente = $('#'+value);
                equivalente.val(actual.val());
            });
       
            let actual = $('#'+input);
            let equivalente = $('#'+$('#'+input).attr('data-input_igual'));

            equivalente.val(actual.val());
            
        }

        // function getDosis_cronico(id_medicamento, div_dosis) {

        //     console.log(id_medicamento);

        //     let url = "{{ route('dental.getDosis') }}";
        //     $.ajax({

        //             url: url,
        //             type: "get",
        //             data: {

        //                 id_medicamento: id_medicamento,

        //             },
        //         })
        //         .done(function(data) {
        //             console.log(data)

        //             if (data != null) {

        //                 data = JSON.parse(data);
        //                 console.log(data)
        //                 let dosis = $('#'+div_dosis);

        //                 dosis.find('option').remove();
        //                 dosis.append('<option value="0">Seleccione</option>');
        //                 $(data).each(function(i, v) { // indice, valor
        //                     dosis.append('<option value="' + v.dosis + '" data-id="'+v.id+'" data-cant_comp="'+v.cant_comp+'">' + v.present +
        //                         '</option>');
        //                 })

        //             } else {



        //             }

        //         })
        //         .fail(function(jqXHR, ajaxOptions, thrownError) {
        //             console.log(jqXHR, ajaxOptions, thrownError)
        //         });

        // };

        // /** GES */
        // function registrar_ges_ficha() {

        //     var validar = 0;
        //     var mensaje ='';
        //     let nombre_institucion_ficha_ges = $('#nombre_institucion_ficha_ges').val();
        //     let direccion_institucion_ficha_ges = $('#direccion_institucion_ficha_ges').val();
        //     let nombre_responsable_ficha_ges = $('#nombre_responsable_ficha_ges').val();
        //     let rut_responsable_ficha_ges = $('#rut_responsable_ficha_ges').val();
        //     let confirmacion_diagnostica_ficha_ges = $('#confirmacion_diagnostica_ficha_ges').val();
        //     let paciente_tratamiento_ficha_ges = $('#paciente_tratamiento_ficha_ges').val();
        //     let nombre_ges = $('#nombre_ges').val();
        //     let id_paciente = $('#id_paciente_fc').val();
        //     let id_profesional = $('#id_profesional').val();
        //     let id_ficha_atencion = $('#id_fc').val();
        //     let id_lugar_atencion = $('#id_lugar_atencion').val();
        //     let hora_medica = $('#hora_medica').val();
        //     let codigo_validacion_informe_ges = $('#codigo_validacion_informe_ges').val();


        //     {{--  if(nombre_institucion_ficha_ges == '')
        //     {
        //         $('#nombre_institucion_ficha_ges').focus();
        //         validar = 1;

        //     }
        //     if(direccion_institucion_ficha_ges == '')
        //     {
        //         $('#direccion_institucion_ficha_ges').focus();
        //         validar = 1;

        //     }  --}}
        //     {{--
        //     if(nombre_responsable_ficha_ges == '')
        //     {
        //         $('#nombre_responsable_ficha_ges').focus();
        //         validar = 1;

        //     }
        //     if(rut_responsable_ficha_ges == '')
        //     {
        //         $('#rut_responsable_ficha_ges').focus();
        //         validar = 1;

        //     }
        //     --}}
        //     if(confirmacion_diagnostica_ficha_ges == '')
        //     {
        //         $('#confirmacion_diagnostica_ficha_ges').focus();
        //         mensaje += ' Debe ingresar Confirmación diagnóstica GES.\n' ;
        //         validar = 1;

        //     }
        //     if(paciente_tratamiento_ficha_ges == '')
        //     {
        //         $('#paciente_tratamiento_ficha_ges').focus();
        //         mensaje += ' Debe Confimar si el paciente se encuentra en tratamiento.\n' ;
        //         validar = 1;

        //     }
        //     if(nombre_ges == '')
        //     {
        //         $('#nombre_ges').focus();
        //         mensaje += ' Debe ingresar el Diagnóstico GES.\n' ;
        //         validar = 1;
        //     }
        //     {{--  if(id_paciente == '')
        //     {
        //         $('#id_paciente').focus();
        //         validar = 1;

        //     }
        //     if(id_profesional == '')
        //     {
        //         $('#id_profesional').focus();
        //         validar = 1;

        //     }
        //     if(id_ficha_atencion == '')
        //     {
        //         $('#id_ficha_atencion').focus();
        //         validar = 1;

        //     }
        //     if(id_lugar_atencion == '')
        //     {
        //         $('#id_lugar_atencion').focus();
        //         validar = 1;

        //     }
        //     if(hora_medica == '')
        //     {
        //         $('#hora_medica').focus();
        //         validar = 1;

        //     }  --}}

        //     if(validar == 1)
        //     {
        //         swal({
        //             title: "Debe ingresar todos los datos requeridos." ,
        //             text: mensaje,
        //             icon: "error",
        //             // buttons: "Aceptar",
        //             //SuccessMode: true,
        //         })
        //         return false;
        //     }
        //     else
        //     {

        //         $.ajax({
        //             url: "{{ route('ficha_atencion.registrar_diagnostico_ges') }}",
        //             type: 'GET',
        //             dataType: 'json',
        //             data: {

        //                 nombre_institucion_ficha_ges :nombre_institucion_ficha_ges,
        //                 direccion_institucion_ficha_ges :direccion_institucion_ficha_ges,
        //                 nombre_responsable_ficha_ges :nombre_responsable_ficha_ges,
        //                 rut_responsable_ficha_ges :rut_responsable_ficha_ges,
        //                 confirmacion_diagnostica_ficha_ges :confirmacion_diagnostica_ficha_ges,
        //                 paciente_tratamiento_ficha_ges :paciente_tratamiento_ficha_ges,
        //                 nombre_ges :nombre_ges,
        //                 id_paciente :id_paciente,
        //                 id_profesional :id_profesional,
        //                 id_ficha_atencion :id_ficha_atencion,
        //                 id_lugar_atencion :id_lugar_atencion,
        //                 hora_medica :hora_medica,
        //                 codigo_verificacion :codigo_validacion_informe_ges,

        //             },
        //         })
        //         .done(function(response) {
        //             console.log(response);

        //             if (response != '') {
        //                 console.log(response);
        //                 //$('#form_control_obesidad').trigger("reset");
        //                 $('#mensaje').text('Se ha creado Diagnostico GES de forma correcta');
        //                 $('#mensaje').show();
        //                 $('#form_ges').modal('hide');


        //                 swal({
        //                     title: "Constancia GES (Artículo 24 Ley 19.966).",
        //                     text: 'Registro Exitoso.\n El paciente ha sido Notificado\n La constancia puede ser recuperada desde su escritorio (Documentos).',
        //                     icon: "success",
        //                     // buttons: "Aceptar",
        //                     //SuccessMode: true,
        //                 })
        //             }

        //         })
        //         .fail(function(e) {
        //             console.log("error");
        //             console.log(e);
        //         })

        //     }



        // };

        // function validar_codigo_ges(){
        //     let codigo_validacion_informe_ges = $('#codigo_validacion_informe_ges').val();
        //     if(codigo_validacion_informe_ges!='')
        //     {
        //         var id_ficha_atencion = $('#id_fc').val();

        //         var valido = 1;
        //         var mensaje = '';


        //         let url = "{{ route('cod_autorizacion.validar_codigo') }}";

        //         var _token = CSRF_TOKEN;
        //         $.ajax({

        //             url: url,
        //             type: "POST",
        //             data: {
        //                 _token: _token,
        //                 codigo:codigo_validacion_informe_ges,
        //                 id_control:id_ficha_atencion,
        //             },
        //         })
        //         .done(function(data)
        //         {

        //             if (data !== 'null')
        //             {
        //                 //data = JSON.parse(data);
        //                 console.log('-----------------------');
        //                 console.log(data);
        //                 console.log('-----------------------');
        //                 if(data.estado == 1)
        //                 {
        //                     registrar_ges_ficha();
        //                 }
        //                 else{

        //                     swal({
        //                         title: "Problema solicitar Autorizacion.",
        //                         text: data.msj,
        //                         icon: "warning",
        //                         // buttons: "Aceptar",
        //                         //SuccessMode: true,
        //                     })
        //                 }
        //             }
        //         })
        //         .fail(function(jqXHR, ajaxOptions, thrownError) {
        //             console.log(jqXHR, ajaxOptions, thrownError)
        //         });


        //     }
        //     else
        //     {
        //         swal({
		// 			title: "Constancia GES (Artículo 24 Ley 19.966).",
		// 			text:"Debe ingresar Código de notificación entrago por el Paciente.",
		// 			icon: "error",
		// 			// buttons: "Aceptar",
		// 			//SuccessMode: true,
		// 		});
        //     }
        // }

        // function envio_codigo_validacion_ges()
        // {
        //     let url = "{{ route('cod_autorizacion.agregar') }}";

        //     var _token = CSRF_TOKEN;
        //     var id_profesional = 0;
        //     var id_ficha_atencion = 0;

        //     // Autorizacion Licencia
        //     id_profesional = '{{ Auth::user()->id }}';
        //     id_ficha_atencion = $('#id_fc').val();

        //     var id_tipo_autorizacion_acompanante = 7;
        //     @if (\Carbon\Carbon::parse($paciente->fecha_nac)->age < 18)
        //         var rut_acompanante = $('#rut_acompanante').val();
        //         var nombre_acompanante = $('#nombre_acompanante').val();
        //         var apell_acompanante = $('#apell_acompanante').val();
        //         var relacion_acompanante = $('#relacion_acompanante').val();
        //         var tipo_medio_acompanante = $('#tipo_medio_acompanante').val();
        //         var tel_acompanante = $('#tel_acompanante').val();
        //         var email_acompanante = $('#email_acompanante').val();
        //     @else
        //         var rut_acompanante = '{{ $paciente->rut }}';
        //         var nombre_acompanante = '{{ $paciente->nombres }}';
        //         var apell_acompanante = '{{ $paciente->apellido_uno }}';
        //         var relacion_acompanante = '99';
        //         var tipo_medio_acompanante = 3;
        //         var tel_acompanante = '{{ $paciente->telefono_uno}}';
        //         var email_acompanante = '{{ $paciente->email }}';
        //     @endif
        //     var medio = '';
        //     if(tipo_medio_acompanante == 1)
        //         medio = tel_acompanante;
        //     else
        //         medio = email_acompanante;

        //     $.ajax({

        //         url: url,
        //         type: "POST",
        //         data: {
        //             _token: _token,

        //             id_tipo_autorizacion:id_tipo_autorizacion_acompanante,
        //             id_profesional:id_profesional,
        //             id_control:id_ficha_atencion,
        //             id_tipo_medio:tipo_medio_acompanante,
        //             medio:medio,
        //             nombre_autoriza:nombre_acompanante,
        //             apellido_autoriza:apell_acompanante,
        //             rut_autoriza:rut_acompanante,
        //             id_parentezco_autoriza:relacion_acompanante,
        //             telefono_autoriza:tel_acompanante,
        //             email_autoriza:email_acompanante,
        //         },
        //     })
        //     .done(function(data)
        //     {

        //         if (data !== 'null')
        //         {
        //             //data = JSON.parse(data);
        //             console.log('-----------------------');
        //             console.log(data);
        //             console.log('-----------------------');
        //             if(data.estado == 1)
        //             {
        //                 swal({
        //                     title: "Código Autorizacion enviado al Paciente.",
        //                     icon: "success",
        //                     // buttons: "Aceptar",
        //                     //SuccessMode: true,
        //                 })
        //             }
        //             else{

        //                 swal({
        //                     title: "Problema al Registrar Codigo de autorizacion.",
        //                     icon: "warning",
        //                     // buttons: "Aceptar",
        //                     //SuccessMode: true,
        //                 })
        //             }
        //         }
        //     })
        //     .fail(function(jqXHR, ajaxOptions, thrownError) {
        //         console.log(jqXHR, ajaxOptions, thrownError)
        //     });
        // }

        // function agregar_medicamentos_ficha()
        // {
        //     var rows1 = [];
        //     $('#tabla_medicamento_cirugia tr').each(function(i, n) {
        //         if (i > 0) {
        //             rol = {};
        //             var data = $(this).find("td");
        //             rol["id_producto"] = $.trim($(data[0]).text().split("\n").join(""));
        //             rol["uso_cronico"] = $.trim($(data[1]).text().split("\n").join(""));
        //             rol["medicamento"] = $.trim($(data[2]).text().split("\n").join(""));
        //             rol["presentacion"] = $.trim($(data[3]).text().split("\n").join(""));
        //             rol["posologia"] = $.trim($(data[4]).text().split("\n").join(""));
        //             rol["via_administracion"] = $.trim($(data[5]).text().split("\n").join(""));
        //             rol["periodo"] = $.trim($(data[6]).text().split("\n").join(""));
        //             rol["compra"] = $.trim($(data[7]).text().split("\n").join(""));
        //             rows1.push(rol);
        //         }
        //     });

        //     $('#medicamentos').val(JSON.stringify(rows1));
        // }

        // function agregar_examenes_ficha()
        // {
        //     var rows = [];
        //     $('#tabla_examen_cirugia tr').each(function(i, n) {
        //         if (i > 0) {
        //             console.log(i);
        //             rol = {};
        //             var data = $(this).find("td");
        //             rol["nombre_examen"] = $.trim($(data[0]).text().split("\n").join(""));
        //             rol["tipo"] = $.trim($(data[1]).text().split("\n").join(""));
        //             // rol["subtipo"] = $.trim($(data[2]).text().split("\n").join(""));
        //             rol["prioridad"] = $.trim($(data[2]).text().split("\n").join(""));
        //             rol["con_contraste"] = $.trim($(data[3]).text().split("\n").join(""));
        //             rows.push(rol);
        //         }
        //     });
        //     $('#examenes').val(JSON.stringify(rows));
        // }

        function evaluar_para_carga_detalle(select, div, input, valor)
        {
            var valor_select = $('#'+select+'').val();
            if(valor_select == valor) $('#'+div+'').show();
            else {
                $('#'+div+'').hide();
                $('#'+input+'').val('');
            }
        }

    </script>
@endsection
