@extends('template.profesional.template')

@section('content')
@php
    $iconosSeccion = [
        'antecedentes' => 'icon-file-text',
        'examen_detallado' => 'icon-clipboard',
        'signos_vitales' => 'icon-activity',
        'vacunas' => 'icon-shield',
    ];
    $marcadas = (array) old('secciones', $visibles);
@endphp
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="row">
            <div class="col-md-12 mb-3">
                <h5 class="titulo-modulo f-26 d-inline">Personalizar mi ficha de atención</h5>
            </div>
        </div>
        <!--Cierre: Header-->

        @if (session('ficha_personalizada'))
            <div class="alert alert-success" role="status">{{ session('ficha_personalizada') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('profesional.personalizar_ficha.guardar') }}">
            @csrf
            <div class="row">
                <!-- Columna de opciones -->
                <div class="col-lg-5 col-xl-4">
                    <div class="card arv-card">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-sliders"></i></span>Secciones de la ficha de atención</h5>
                        </div>
                        <div class="card-body">
                            <div class="pf-fija">
                                <div>
                                    <strong>Ficha de atención básica</strong>
                                    <span>Motivo de consulta, examen físico, diagnóstico y crónicos siempre están disponibles.</span>
                                </div>
                            </div>

                            @foreach ($secciones as $clave => $nombre)
                                <label class="pf-opcion" for="ficha-{{ $clave }}" data-seccion="{{ $clave }}">
                                    <span class="pf-opcion-icono"><i class="feather {{ $iconosSeccion[$clave] ?? 'icon-layers' }}"></i></span>
                                    <span class="pf-opcion-texto">
                                        <strong>{{ $nombre }}</strong>
                                    </span>
                                    <span class="custom-control custom-switch pf-opcion-switch">
                                        <input type="checkbox" class="custom-control-input pf-switch" id="ficha-{{ $clave }}"
                                            name="secciones[]" value="{{ $clave }}" data-seccion="{{ $clave }}" @checked(in_array($clave, $marcadas, true))>
                                        <span class="custom-control-label" aria-hidden="true"></span>
                                    </span>
                                </label>
                            @endforeach

                            <div class="pf-acciones">
                                <a href="{{ route('profesional.configuracion') }}" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-info"><i class="feather icon-save"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista previa: imita la pestaña "Atención especialidad" de la ficha de veterinaria general -->
                <div class="col-lg-7 col-xl-8">
                    <div class="card arv-card">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-eye"></i></span>Así se verá tu ficha de atención</h5>
                        </div>
                        <div class="card-body pf-previa" aria-label="Vista previa de la ficha">
                            <div class="pf-previa-pestana">Atención especialidad</div>

                            <div class="card-a">
                                <div class="card-header-a"><span class="accor-closed d-block">Anamnesis</span></div>
                                <div class="card-body-aten-a">
                                    <div class="form-row">
                                        <div class="col-12 pf-campo"><span>Motivo de consulta</span></div>
                                    </div>
                                    <div class="pf-bloque" data-bloque="antecedentes">
                                        <div class="form-row">
                                            <div class="col-md-6 pf-campo"><span>Dieta</span></div>
                                            <div class="col-md-6 pf-campo"><span>¿Enfermedades recientes o crónicas?</span></div>
                                            <div class="col-md-6 pf-campo"><span>Tratamiento en curso o permanente</span></div>
                                            <div class="col-md-6 pf-campo"><span>¿Viajará en los próximos días?</span></div>
                                            <div class="col-md-6 pf-campo"><span>¿Vive con animales?</span></div>
                                            <div class="col-md-6 pf-campo"><span>¿Cuáles?</span></div>
                                        </div>
                                    </div>
                                    <div class="pf-oculta" data-oculta="antecedentes">
                                        <i class="feather icon-eye-off"></i> Los antecedentes no aparecerán en la anamnesis
                                    </div>
                                    <div class="pf-chips">
                                        <span data-bloque="antecedentes" class="pf-chip"><i class="feather icon-toggle-right"></i> Agregar antecedente</span>
                                        <span><i class="feather icon-toggle-right"></i> Control crónico</span>
                                        <span data-bloque="vacunas" class="pf-chip"><i class="feather icon-toggle-right"></i> Vacunas y desparasitación</span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-a">
                                <div class="card-header-a"><span class="accor-closed d-block">Examen físico</span></div>
                                <div class="card-body-aten-a">
                                    <div class="form-row">
                                        <div class="col-md-6 pf-campo"><span>Condición corporal</span></div>
                                        <div class="col-md-6 pf-campo"><span>Peso</span></div>
                                        <div class="col-12 pf-campo"><span>T · FC · FR · TLLC · TRPC · Pulso · Mucosas · Deshidratación</span></div>
                                        <div class="col-12 pf-campo"><span>Examen físico</span></div>
                                    </div>
                                    <div class="pf-bloque" data-bloque="examen_detallado">
                                        <div class="form-row">
                                            <div class="col-md-4 pf-campo"><span>Órganos de los sentidos</span></div>
                                            <div class="col-md-4 pf-campo"><span>Piel y pelaje</span></div>
                                            <div class="col-md-4 pf-campo"><span>Ganglios linfáticos</span></div>
                                            <div class="col-md-4 pf-campo"><span>Sistema digestivo</span></div>
                                            <div class="col-md-4 pf-campo"><span>Sistema endocrino</span></div>
                                            <div class="col-md-4 pf-campo"><span>Sistema músculo esquelético</span></div>
                                            <div class="col-md-4 pf-campo"><span>Sistema nervioso</span></div>
                                            <div class="col-md-4 pf-campo"><span>Sistema urinario</span></div>
                                            <div class="col-md-4 pf-campo"><span>Sistema reproductivo</span></div>
                                            <div class="col-md-6 pf-campo"><span>Palpación rectal</span></div>
                                            <div class="col-md-6 pf-campo"><span>Otros</span></div>
                                        </div>
                                    </div>
                                    <div class="pf-oculta" data-oculta="examen_detallado">
                                        <i class="feather icon-eye-off"></i> El examen por sistemas no aparecerá en la ficha
                                    </div>
                                    <div class="form-row">
                                        <div class="col-12 pf-campo"><span>Imágenes de la atención</span></div>
                                    </div>
                                </div>
                            </div>

                            <div class="pf-bloque" data-bloque="signos_vitales">
                                <div class="card-a">
                                    <div class="card-header-a"><span class="accor-closed d-block">Signos vitales y otros</span></div>
                                    <div class="card-body-aten-a">
                                        <div class="form-row">
                                            <div class="col-6 col-md-3 pf-campo"><span>Tº</span></div>
                                            <div class="col-6 col-md-3 pf-campo"><span>Pulso</span></div>
                                            <div class="col-6 col-md-3 pf-campo"><span>Frec. Reposo</span></div>
                                            <div class="col-6 col-md-3 pf-campo"><span>Peso</span></div>
                                            <div class="col-md-6 pf-campo"><span>Presión arterial veterinaria</span></div>
                                            <div class="col-md-6 pf-campo"><span>Evaluación neurológica</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pf-oculta" data-oculta="signos_vitales">
                                <i class="feather icon-eye-off"></i> Signos vitales y otros no aparecerá en la ficha
                            </div>

                            <div class="card-a">
                                <div class="card-header-a"><span class="accor-closed d-block">Diagnóstico e Indicaciones</span></div>
                                <div class="card-body-aten-a">
                                    <div class="form-row">
                                        <div class="col-md-6 pf-campo"><span>Diagnóstico</span></div>
                                        <div class="col-md-6 pf-campo"><span>Notas internas para la consulta</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Muestra u oculta en la vista previa los bloques que dependen de cada switch
    document.addEventListener('DOMContentLoaded', function () {
        var switches = document.querySelectorAll('.pf-switch');

        function pintarPrevia(input) {
            var seccion = input.getAttribute('data-seccion');
            var activa = input.checked;
            var opcion = input.closest('.pf-opcion');
            if (opcion) {
                opcion.classList.toggle('pf-opcion-activa', activa);
            }
            document.querySelectorAll('[data-bloque="' + seccion + '"]').forEach(function (bloque) {
                bloque.classList.toggle('pf-bloque-oculto', !activa);
            });
            document.querySelectorAll('[data-oculta="' + seccion + '"]').forEach(function (aviso) {
                aviso.classList.toggle('pf-oculta-visible', !activa);
            });
        }

        switches.forEach(function (input) {
            pintarPrevia(input);
            input.addEventListener('change', function () { pintarPrevia(input); });
        });
    });
</script>
@endsection
