{{-- Personalizar el PDF de receta o de orden de exámenes ($tipo: receta | examenes).
     A la izquierda las opciones; a la derecha la vista previa, que se actualiza sola al cambiar algo --}}
@php
    $secciones = \App\Models\PlantillaPdfProfesional::SECCIONES[$tipo];
    $esReceta = $tipo === 'receta';
@endphp
<div class="card plantilla-pdf" data-tipo="{{ $tipo }}"
    data-url-guardar="{{ route('profesional.plantilla_pdf.guardar', $tipo) }}"
    data-url-restablecer="{{ route('profesional.plantilla_pdf.restablecer', $tipo) }}"
    data-url-vista-previa="{{ route('profesional.plantilla_pdf.vista_previa', $tipo) }}"
    data-tiene-logo="{{ $configuracion['logo'] ? '1' : '0' }}">
    <div class="card-header encabezado-card">
        <h5 class="encabezado-card-titulo">
            <span class="encabezado-card-icono"><i class="feather {{ $esReceta ? 'icon-file-text' : 'icon-clipboard' }}"></i></span>
            {{ $esReceta ? 'Personalizar receta' : 'Personalizar orden de exámenes' }}
        </h5>
        <div class="encabezado-card-acciones">
            <button type="button" class="btn btn-info js-plantilla-ver-pdf"><i class="feather icon-eye"></i> Ver PDF</button>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted mb-4">
            Así saldrá el PDF cada vez que emitas {{ $esReceta ? 'una receta' : 'una orden de exámenes' }}.
            La vista previa usa datos de ejemplo.
        </p>
        <div class="row">
            <!--Opciones del documento-->
            <div class="col-lg-5">
                <form class="js-plantilla-formulario" novalidate>
                    <div class="plantilla-pdf-subtitulo" role="heading" aria-level="6">Encabezado</div>
                    <div class="plantilla-pdf-logo mb-3">
                        <div class="plantilla-pdf-logo-muestra">
                            <img src="{{ $configuracion['logo'] ?? '' }}" alt="Logo del documento" @class(['js-plantilla-logo-imagen', 'd-none' => !$configuracion['logo']])>
                            <span @class(['js-plantilla-logo-vacio text-muted', 'd-none' => $configuracion['logo']])>Sin logo propio</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-info btn-sm js-plantilla-logo-cambiar"><i class="feather icon-image"></i> Cambiar logo</button>
                            <button type="button" @class(['btn btn-outline-dark btn-sm js-plantilla-logo-quitar', 'd-none' => !$configuracion['logo']])>Quitar logo</button>
                            <input type="file" class="d-none js-plantilla-logo-archivo" accept="image/png,image/jpeg,image/svg+xml,image/webp">
                            <small class="d-block text-muted mt-1">PNG, JPG, SVG o WebP de hasta 2 MB.</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="floating-label-activo-sm">Texto si no hay logo</label>
                            <input type="text" class="form-control form-control-sm" name="texto_marca" maxlength="40" value="{{ $configuracion['texto_marca'] }}" placeholder="Ej.: Veterinaria Patitas">
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="floating-label-activo-sm">Posición del logo</label>
                            <select class="form-control form-control-sm" name="posicion_logo">
                                @foreach (['izquierda' => 'Izquierda', 'centro' => 'Centro', 'derecha' => 'Derecha'] as $valor => $texto)
                                    <option value="{{ $valor }}" @selected($configuracion['posicion_logo'] === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-8">
                            <label class="floating-label-activo-sm">Título del documento</label>
                            <input type="text" class="form-control form-control-sm" name="titulo" maxlength="80" value="{{ $configuracion['titulo'] }}" required>
                            <div class="invalid-feedback">Escribe el título del documento.</div>
                        </div>
                        <div class="form-group col-sm-4">
                            <label class="floating-label-activo-sm">Color principal</label>
                            <input type="color" class="form-control form-control-sm plantilla-pdf-color" name="color" value="{{ $configuracion['color'] }}">
                        </div>
                    </div>

                    <div class="plantilla-pdf-subtitulo" role="heading" aria-level="6">Formato de la información</div>
                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="floating-label-activo-sm">{{ $esReceta ? 'Medicamentos' : 'Exámenes' }}</label>
                            <select class="form-control form-control-sm" name="formato">
                                <option value="tabla" @selected($configuracion['formato'] === 'tabla')>En tabla</option>
                                <option value="lista" @selected($configuracion['formato'] === 'lista')>Uno debajo del otro</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="floating-label-activo-sm">Tamaño de letra</label>
                            <select class="form-control form-control-sm" name="tamano_letra">
                                @foreach (['pequena' => 'Pequeña', 'normal' => 'Normal', 'grande' => 'Grande'] as $valor => $texto)
                                    <option value="{{ $valor }}" @selected($configuracion['tamano_letra'] === $valor)>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="plantilla-pdf-subtitulo" role="heading" aria-level="6">Datos que se muestran</div>
                    <div class="plantilla-pdf-datos mb-3">
                        @foreach ($secciones as $clave => $texto)
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="plantilla_{{ $tipo }}_{{ $clave }}" name="mostrar[]" value="{{ $clave }}" @checked(!empty($configuracion['mostrar'][$clave]))>
                                <label class="custom-control-label" for="plantilla_{{ $tipo }}_{{ $clave }}">{{ $texto }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="form-group">
                        <label class="floating-label-activo-sm">Texto al pie (opcional)</label>
                        <textarea class="form-control form-control-sm" name="pie" rows="2" maxlength="300" placeholder="Ej.: Horario de atención de lunes a viernes de 9:00 a 19:00">{{ $configuracion['pie'] }}</textarea>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end">
                        <button type="button" class="btn btn-outline-dark mr-2 mb-2 js-plantilla-restablecer"><i class="feather icon-rotate-ccw"></i> Restablecer</button>
                        <button type="submit" class="btn btn-info mb-2 js-plantilla-guardar"><i class="feather icon-save"></i> Guardar plantilla</button>
                    </div>
                </form>
            </div>

            <!--Vista previa-->
            <div class="col-lg-7">
                <div class="plantilla-pdf-vista">
                    <div class="plantilla-pdf-vista-cargando js-plantilla-cargando">Actualizando vista previa…</div>
                    <iframe class="js-plantilla-vista" title="Vista previa del PDF" sandbox></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
