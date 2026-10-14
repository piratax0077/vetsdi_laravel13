@php
    $especieDentalVet = \App\Support\DenticionVeterinaria::especie($mascota);
@endphp
<ul class="nav nav-tabs nav-fill mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#vet-consulta">Consulta</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#vet-odontograma">Odontograma {{ $especieDentalVet }}</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#vet-periodonto">Evaluación periodontal</a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#vet-presupuestos">Presupuestos, pagos y trabajos</a></li>
</ul>
<div id="vet-dental-error" class="alert alert-danger" role="alert" hidden></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="tab-content">
    <div class="tab-pane fade show active" id="vet-consulta">
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
            <div class="card"><div class="card-body"><h5>Consulta odontológica de {{ $mascota->nombre }}</h5>
                <div class="form-row">
                    @foreach(['motivo' => 'Motivo de consulta', 'antecedentes' => 'Antecedentes odontológicos', 'examen_fisico' => 'Examen oral', 'anestesia_dental' => 'Anestesia y monitorización', 'hemorragias_dentales' => 'Hemorragias', 'fracturas_dentales' => 'Fracturas y lesiones', 'evaluacion_caras_grupos' => 'Evaluación de caras y grupos dentales', 'trabajos_laboratorio_dental' => 'Indicaciones para el laboratorio dental'] as $campo => $nombre)
                        <div class="form-group col-md-6"><label for="vet-{{ $campo }}">{{ $nombre }}</label><textarea class="form-control" id="vet-{{ $campo }}" name="{{ $campo }}" rows="3">{{ old($campo, $fichaVeterinariaGeneralData[$campo] ?? data_get($fichaAtencion ?? null, $campo, '')) }}</textarea></div>
                    @endforeach
                </div>
            </div></div>
            @include('general.secciones_ficha.signos_vitales')
            @include('general.secciones_ficha.diagnostico')
            <div class="text-center mb-3">
                <button type="submit" class="btn btn-info" onclick="document.getElementById('cerrarsession').value='0'">Guardar ficha y odontograma</button>
                <button type="submit" class="btn btn-purple" onclick="document.getElementById('cerrarsession').value='1'">Guardar y finalizar consulta</button>
            </div>
        </form>
        @include('atencion_veterinaria.partials.guardar_ficha_completa')
        <div class="card"><div class="card-body">@include('general.secciones_ficha.seccion_receta_examen_comunes')</div></div>
    </div>
    <div class="tab-pane fade" id="vet-odontograma">
        <p class="text-muted">Dentición permanente. Guarde los hallazgos de cada pieza; al guardar la ficha se conserva una copia completa por mascota y especie.</p>
        @include('general.secciones_ficha.partials.odontograma_mascota', ['presupuesto' => null])
    </div>
    <div class="tab-pane fade" id="vet-periodonto">@include('atencion_odontologica.generales.periodontograma_veterinario')</div>
    <div class="tab-pane fade" id="vet-presupuestos">
        <div class="card"><div class="card-body"><h5>Presupuestos y trabajos odontológicos</h5><p class="text-muted">Agregue tratamientos desde una pieza del odontograma. El estado del trabajo y sus pagos se registran por separado.</p><div id="vet-presupuestos-lista" aria-live="polite">Cargando presupuestos…</div></div></div>
    </div>
</div>
<div class="modal fade" id="vet-pago-modal" tabindex="-1" role="dialog" aria-labelledby="vet-pago-titulo">
    <div class="modal-dialog"><div class="modal-content">
        <form id="vet-pago-form">
            <div class="modal-header"><h5 id="vet-pago-titulo">Registrar pago</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">×</button></div>
            <div class="modal-body">
                <p id="vet-pago-saldo"></p>
                <div class="form-group"><label for="vet-pago-monto">Monto</label><input class="form-control" id="vet-pago-monto" name="monto" type="number" min="1" step="1" required></div>
                <div class="form-group"><label for="vet-pago-metodo">Medio de pago</label><select class="form-control" id="vet-pago-metodo" name="metodo_pago" required>@foreach(['Efectivo','Débito','Crédito','Transferencia','Bono veterinario','Otro'] as $metodo)<option>{{ $metodo }}</option>@endforeach</select></div>
                <div class="form-group"><label for="vet-pago-observaciones">Observaciones</label><textarea class="form-control" id="vet-pago-observaciones" name="observaciones" maxlength="500"></textarea></div>
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
