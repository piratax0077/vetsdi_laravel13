@php
    $especieDentalVet = \App\Support\DenticionVeterinaria::especie($mascota);
    // Los campos odontológicos se agrupan en dos bloques: lo que se examina y lo que se hace en el procedimiento.
    $bloquesOdontologicos = [
        'consulta_odontologica' => [
            'titulo' => 'Consulta odontológica de ' . $mascota->nombre,
            'campos' => [
                'antecedentes' => 'Antecedentes odontológicos',
                'examen_oral' => 'Examen oral',
                'evaluacion_caras_grupos' => 'Evaluación de caras y grupos dentales',
                'fracturas_dentales' => 'Fracturas y lesiones',
            ],
        ],
        'procedimiento_odontologico' => [
            'titulo' => 'Procedimiento y laboratorio',
            'campos' => [
                'anestesia_dental' => 'Anestesia y monitorización',
                'hemorragias_dentales' => 'Hemorragias',
                'trabajos_laboratorio_dental' => 'Indicaciones para el laboratorio dental',
            ],
        ],
    ];
@endphp
<div class="ficha-odontologica-vet">
    <div class="user-profile user-card mt-0">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-3">
            <ul class="nav nav-tabs-secciones mb-3 mt-3" role="tablist">
                <li class="nav-item-secciones">
                    <a class="nav-secciones active text-uppercase" data-toggle="tab" href="#vet-consulta" role="tab" aria-controls="vet-consulta" aria-selected="true">Consulta</a>
                </li>
                <li class="nav-item-secciones">
                    <a class="nav-secciones text-uppercase" data-toggle="tab" href="#vet-odontograma" role="tab" aria-controls="vet-odontograma" aria-selected="false">Odontograma {{ $especieDentalVet }}</a>
                </li>
                <li class="nav-item-secciones">
                    <a class="nav-secciones text-uppercase" data-toggle="tab" href="#vet-periodonto" role="tab" aria-controls="vet-periodonto" aria-selected="false">Evaluación periodontal</a>
                </li>
                <li class="nav-item-secciones">
                    <a class="nav-secciones text-uppercase" data-toggle="tab" href="#vet-presupuestos" role="tab" aria-controls="vet-presupuestos" aria-selected="false">Presupuestos, pagos y trabajos</a>
                </li>
            </ul>

            <div id="vet-dental-error" class="alert alert-danger" role="alert" hidden></div>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <div class="tab-content">
                <!--CONSULTA-->
                <div class="tab-pane fade show active" id="vet-consulta" role="tabpanel">
                    <form id="ficha-odontologia-vet" method="POST" action="{{ route('dental.registrar_ficha_atencion_dental') }}">
                        @csrf
                        <input type="hidden" name="hora_medica" id="hora_medica" value="{{ $hora_medica->id }}">
                        <input type="hidden" name="id_fc" id="id_fc" value="{{ $id_ficha_atencion }}">
                        <input type="hidden" name="id_mascota" id="id_mascota_fc" value="{{ $mascota->id }}">
                        <input type="hidden" name="id_paciente_fc" id="id_paciente_fc" value="{{ $paciente->id }}">
                        <input type="hidden" name="id_profesional_fc" id="id_profesional_fc" value="{{ $profesional->id }}">
                        <input type="hidden" name="rut_paciente_fc" id="rut_paciente_fc" value="{{ $paciente->rut }}">
                        <input type="hidden" name="prevision_paciente_fc" id="prevision_paciente_fc" value="{{ $paciente->id_prevision ?? '' }}">
                        <input type="hidden" name="id_especialidad" id="id_especialidad" value="{{ $profesional->id_especialidad }}">
                        <input type="hidden" name="id_lugar_atencion" id="id_lugar_atencion" value="{{ $id_lugar_atencion }}">
                        <input type="hidden" name="cerrarsession" id="cerrarsession" value="0">
                        <input type="hidden" name="medicamentos" id="medicamentos" value="{{ old('medicamentos', $fichaVeterinariaGeneralData['medicamentos'] ?? '[]') }}">
                        <input type="hidden" name="examenes" id="examenes" value="{{ old('examenes', $fichaVeterinariaGeneralData['examenes'] ?? '[]') }}">
                        <input type="hidden" name="input_lista_imagenes" id="input_lista_imagenes" value="{{ old('input_lista_imagenes', '') }}">

                        <div class="row">
                            @include('general.secciones_ficha.motivo', ['examenFisicoEnCard' => true, 'ocultarCamposAnamnesis' => true, 'anamnesisVetGeneral' => true])
                            @include('general.secciones_ficha.examenfisico', ['examenFisicoEnCard' => true])

                            @foreach($bloquesOdontologicos as $bloque => $datosBloque)
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <div class="card-a">
                                        <div class="card-header-a" id="{{ $bloque }}">
                                            <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#{{ $bloque }}_c" aria-expanded="true" aria-controls="{{ $bloque }}_c">
                                                {{ $datosBloque['titulo'] }}
                                            </button>
                                        </div>
                                        <div id="{{ $bloque }}_c" class="collapse show" aria-labelledby="{{ $bloque }}">
                                            <div class="card-body-aten-a">
                                                <div class="form-row">
                                                    @foreach($datosBloque['campos'] as $campo => $nombre)
                                                        @php
                                                            $campoAnterior = $campo === 'examen_oral' ? 'examen_fisico' : $campo;
                                                            $valor = $fichaVeterinariaGeneralData[$campo] ?? data_get($fichaAtencion ?? null, $campoAnterior, '');
                                                            $anchoCompleto = $loop->last && $loop->count % 2 === 1;
                                                        @endphp
                                                        <div class="form-group col-sm-12 {{ $anchoCompleto ? 'col-md-12' : 'col-md-6' }}">
                                                            <label class="floating-label-activo-sm" for="vet-{{ $campo }}">{{ $nombre }}</label>
                                                            <textarea class="form-control caja-texto form-control-sm" id="vet-{{ $campo }}" name="{{ $campo }}" rows="2">{{ old($campo, $valor) }}</textarea>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @include('atencion_veterinaria.secciones_especialidad.seccion_antecedentes_veterinarios')
                            @include('general.secciones_ficha.diagnostico', ['etiquetaNotasClinicas' => 'Notas internas para la consulta'])
                        </div>

                        <!--RECETA Y EXÁMENES-->
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    @include('general.secciones_ficha.seccion_receta_examen_comunes')
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-purple mt-1" onclick="document.getElementById('cerrarsession').value='1'">Guardar y finalizar consulta</button>
                                <button type="submit" class="btn btn-info mt-1" onclick="document.getElementById('cerrarsession').value='0'">Guardar ficha y odontograma</button>
                            </div>
                        </div>
                    </form>
                    @include('atencion_veterinaria.partials.guardar_ficha_completa')
                </div>

                <!--ODONTOGRAMA-->
                <div class="tab-pane fade" id="vet-odontograma" role="tabpanel">
                    <p class="text-muted">Dentición permanente. Guarde los hallazgos de cada pieza; al guardar la ficha se conserva una copia completa por mascota y especie.</p>
                    @include('general.secciones_ficha.partials.odontograma_mascota', ['presupuesto' => null])
                </div>

                <!--EVALUACIÓN PERIODONTAL-->
                <div class="tab-pane fade" id="vet-periodonto" role="tabpanel">@include('atencion_odontologica.generales.periodontograma_veterinario')</div>

                <!--PRESUPUESTOS-->
                <div class="tab-pane fade" id="vet-presupuestos" role="tabpanel">
                    <div class="card">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-file-text"></i></span>Presupuestos y trabajos odontológicos</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Agregue tratamientos desde una pieza del odontograma. El estado del trabajo y sus pagos se registran por separado.</p>
                            <div id="vet-presupuestos-lista" aria-live="polite">Cargando presupuestos…</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vet-pago-modal" tabindex="-1" role="dialog" aria-labelledby="vet-pago-titulo">
    <div class="modal-dialog"><div class="modal-content">
        <form id="vet-pago-form">
            <div class="modal-header modal-header-celeste">
                <h5 class="modal-title" id="vet-pago-titulo"><i class="feather icon-credit-card mr-1"></i> Registrar pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p id="vet-pago-saldo"></p>
                <div class="form-row">
                    <div class="form-group col-sm-12 col-md-6">
                        <label class="floating-label-activo-sm" for="vet-pago-monto">Monto</label>
                        <input class="form-control form-control-sm" id="vet-pago-monto" name="monto" type="number" min="1" step="1" required>
                    </div>
                    <div class="form-group col-sm-12 col-md-6">
                        <label class="floating-label-activo-sm" for="vet-pago-metodo">Medio de pago</label>
                        <select class="form-control form-control-sm" id="vet-pago-metodo" name="metodo_pago" required>@foreach(['Efectivo','Débito','Crédito','Transferencia','Bono veterinario','Otro'] as $metodo)<option>{{ $metodo }}</option>@endforeach</select>
                    </div>
                    <div class="form-group col-sm-12">
                        <label class="floating-label-activo-sm" for="vet-pago-observaciones">Observaciones</label>
                        <textarea class="form-control caja-texto form-control-sm" id="vet-pago-observaciones" name="observaciones" maxlength="500" rows="2"></textarea>
                    </div>
                </div>
                <div id="vet-pago-error" class="alert alert-danger" role="alert" hidden></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-info">Registrar pago</button></div>
        </form>
    </div></div>
</div>
<script>
    window.odontologiaVeterinariaConfig = {
        mascota: @json($mascota->id), ficha: @json($id_ficha_atencion), csrf: @json(csrf_token()),
        estado: @json(route('veterinaria.odontologia.estado')),
        presupuestoBase: @json(url('Profesional/Paciente/Odontologia_veterinaria/presupuesto'))
    };
</script>
<script src="{{ asset('js/odontologia-veterinaria.js') }}"></script>
@include('app.cirugia.modals.modals_cesarea.modal_indicar_examenes', ['modalIndicarExamenesId' => 'indicar_examenes_receta'])
