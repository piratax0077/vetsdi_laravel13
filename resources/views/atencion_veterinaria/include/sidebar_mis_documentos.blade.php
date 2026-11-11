{{-- Sidebar derecho de la ficha veterinaria: indicaciones y consentimientos creados por el profesional
     en Configuración > Indicaciones y consentimientos, listos para abrir y emitir en esta atención. --}}
@php
    // Plantillas de muestra (las mismas de la configuración) mientras no existan los registros reales
    $documentosProfesional = require resource_path('views/app/profesional/indicaciones/datos_muestra.php');
    $plantillasActivas = [];
    foreach ($documentosProfesional as $claveSeccion => $seccionDocumentos) {
        $plantillasActivas[$claveSeccion] = array_values(array_filter($seccionDocumentos['registros'], fn ($registro) => $registro['activo']));
    }

    // Datos de esta atención para completar las etiquetas automáticas ([Mascota], [Tutor]...)
    $tutorDocumento = $responsable_mascota ?? $paciente ?? null;
    $edadDocumento = '';
    if (isset($mascota_edad) && $mascota_edad !== null) {
        $edadDocumento = (int) $mascota_edad === 0 ? 'Menos de 1 año' : $mascota_edad . ((int) $mascota_edad === 1 ? ' año' : ' años');
    }
    $nombreCompleto = fn ($persona, $nombres = 'nombres') => $persona ? trim(implode(' ', array_filter([$persona->{$nombres} ?? '', $persona->apellido_uno ?? '', $persona->apellido_dos ?? '']))) : '';
    $valoresDocumento = [
        'mascota' => $mascota->nombre ?? '',
        'especie' => isset($mascota) ? (optional($mascota->especieMascota)->nombre ?: ($mascota->otra_especie ?? '')) : '',
        'raza' => isset($mascota) ? (optional($mascota->razaMascota)->nombre ?? '') : '',
        'peso' => '',
        'microchip' => $mascota->chip ?? '',
        'edad' => $edadDocumento,
        'tutor' => $nombreCompleto($tutorDocumento),
        'rut_tutor' => mb_strtoupper($tutorDocumento->rut ?? ''),
        'profesional' => $nombreCompleto($profesional ?? null, 'nombre'),
        'rut_profesional' => mb_strtoupper($profesional->rut ?? ''),
        'fecha' => now()->format('d/m/Y'),
    ];
    $etiquetaDato = fn ($clave, $texto) => '<span class="indicacion-dato" data-dato="' . $clave . '">' . $texto . '</span>';
@endphp

<div class="card-sidebar">
    <div class="card-header-sidebar" id="heading_mis_documentos">
        <h2 class="mb-0">
            <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_mis_documentos" aria-expanded="false" aria-controls="collapse_mis_documentos"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                <span class="sb-acordeon__icono"><i class="feather icon-folder"></i></span>
                <span class="sb-acordeon__titulo">MIS INDICACIONES Y CONSENTIMIENTOS</span>
            </button>
        </h2>
    </div>
    <div id="collapse_mis_documentos" class="collapse" aria-labelledby="heading_mis_documentos" data-parent="#accordion_side_bar">
        <div class="card-body-sidebar">
            @foreach (['indicaciones' => 'Indicaciones', 'consentimientos' => 'Consentimientos informados'] as $claveSeccion => $tituloSeccion)
                <p class="sidebar-documentos-titulo">{{ $tituloSeccion }}</p>
                @forelse ($plantillasActivas[$claveSeccion] as $plantilla)
                    <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="abrirDocumentoAtencion('{{ $claveSeccion }}', {{ $plantilla['id'] }})">+ {{ $plantilla['titulo'] }}</button>
                @empty
                    <p class="sidebar-documentos-vacio">Aún no tienes {{ \Illuminate\Support\Str::lower($tituloSeccion) }} habilitados.</p>
                @endforelse
            @endforeach
            <a href="{{ route('profesional.indicaciones_medicas') }}" target="_blank" rel="noopener" class="sidebar-documentos-administrar"><i class="feather icon-settings"></i> Administrar mis plantillas</a>
        </div>
    </div>
</div>

<!-- Modal: abrir y emitir un documento en esta atención -->
<div id="modal_documento_atencion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_documento_atencion_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-celeste">
                <h5 class="modal-title" id="modal_documento_atencion_titulo"><i class="feather icon-file-text mr-1"></i> Emitir indicación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="documento-atencion-ayuda" id="documento_atencion_ayuda"></p>
                <!-- Documento escrito: hoja con los datos de la atención, editable antes de emitir -->
                <div class="editor-indicacion" id="documento_atencion_texto">
                    <div class="editor-indicacion-hoja">
                        <div class="editor-indicacion-logo">
                            <img src="" alt="Logo del documento" id="documento_atencion_logo">
                        </div>
                        <div class="editor-documento-titulo d-none" id="documento_atencion_titulo_hoja"></div>
                        <div class="editor-indicacion-texto" id="documento_atencion_contenido" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Contenido del documento"></div>
                        <!-- Firmas al pie: solo en consentimientos informados -->
                        <div class="documento-firmas d-none" id="documento_atencion_firmas" contenteditable="false">
                            <div class="documento-firma">
                                <div class="documento-firma-espacio"></div>
                                <div class="documento-firma-linea">Firma del tutor / responsable</div>
                                <dl class="documento-firma-datos">
                                    <div><dt>Nombre</dt><dd>{!! $etiquetaDato('tutor', 'Tutor') !!}</dd></div>
                                    <div><dt>RUT</dt><dd>{!! $etiquetaDato('rut_tutor', 'RUT del tutor') !!}</dd></div>
                                </dl>
                            </div>
                            <div class="documento-firma">
                                <div class="documento-firma-espacio"></div>
                                <div class="documento-firma-linea">Firma del profesional veterinario</div>
                                <dl class="documento-firma-datos">
                                    <div><dt>Nombre</dt><dd>{!! $etiquetaDato('profesional', 'Profesional') !!}</dd></div>
                                    <div><dt>RUT</dt><dd>{!! $etiquetaDato('rut_profesional', 'RUT del profesional') !!}</dd></div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Documento subido como archivo -->
                <div class="visor-archivo d-none" id="documento_atencion_archivo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x"></i> Cancelar</button>
                <button type="button" class="btn btn-outline-info" id="documento_atencion_boton_pdf" onclick="vistaPdfDocumentoAtencion()"><i class="feather icon-file-text"></i> Vista previa PDF</button>
                <button type="button" class="btn btn-info" onclick="emitirDocumentoAtencion()"><i class="feather icon-send"></i> Emitir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: vista previa del PDF del documento de la atención -->
<div id="modal_documento_atencion_pdf" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_documento_atencion_pdf_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-celeste">
                <h5 class="modal-title" id="modal_documento_atencion_pdf_titulo"><i class="feather icon-file-text mr-1"></i> Vista previa PDF</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <iframe class="visor-pdf-marco" id="documento_atencion_marco" title="Vista previa del PDF del documento"></iframe>
                <p class="visor-pdf-ayuda mb-0">Al descargar se abre la ventana de impresión: elige <b>Guardar como PDF</b> en el destino.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-arrow-left"></i> Volver</button>
                <button type="button" class="btn btn-info" onclick="DocumentosClinicos.imprimir(document.getElementById('documento_atencion_marco'))"><i class="feather icon-download"></i> Descargar PDF</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/documentos_clinicos.js') }}?v=20261110-1"></script>
<script>
    // Plantillas del profesional y datos de esta atención para completarlas
    var DOCUMENTOS_ATENCION = @json($plantillasActivas);
    var VALORES_ATENCION = @json($valoresDocumento);
    var LOGO_DOCUMENTO_BASE = "{{ asset('images/vet-color-h.svg') }}";
    var documentoAtencionActual = null;

    $(function () {
        // Los modales viven dentro del sidebar; se pasan al body para que se vean sobre toda la ficha
        $('#modal_documento_atencion, #modal_documento_atencion_pdf').appendTo('body');

        // Al cerrar la vista previa se vuelve al documento para seguir revisándolo
        $('#modal_documento_atencion_pdf').on('hidden.bs.modal', function () {
            $('#modal_documento_atencion').modal('show');
        });
        $('#modal_documento_atencion_pdf').on('shown.bs.modal', function () {
            DocumentosClinicos.ajustarHoja(document.getElementById('documento_atencion_marco'));
        });
    });

    function abrirDocumentoAtencion(seccion, id) {
        var plantilla = (DOCUMENTOS_ATENCION[seccion] || []).filter(function (registro) { return registro.id === id; })[0];
        if (!plantilla) return;
        var esConsentimiento = seccion === 'consentimientos';
        var esArchivo = plantilla.tipo === 'archivo';
        documentoAtencionActual = { seccion: seccion, plantilla: plantilla };

        $('#modal_documento_atencion_titulo').html('<i class="feather ' + (esConsentimiento ? 'icon-clipboard' : 'icon-file-text') + ' mr-1"></i> '
            + (esConsentimiento ? 'Emitir consentimiento informado' : 'Emitir indicación'));

        $('#documento_atencion_texto').toggleClass('d-none', esArchivo);
        $('#documento_atencion_archivo').toggleClass('d-none', !esArchivo);
        $('#documento_atencion_boton_pdf').toggleClass('d-none', esArchivo);

        if (esArchivo) {
            $('#documento_atencion_ayuda').text('Este documento se subió como archivo. Revísalo y emítelo tal como está.');
            DocumentosClinicos.mostrarVisorArchivo($('#documento_atencion_archivo'), { url: plantilla.archivo_url || '', nombre: plantilla.archivo || '', tipo: '' });
        } else {
            $('#documento_atencion_logo').attr('src', plantilla.logo || LOGO_DOCUMENTO_BASE);
            $('#documento_atencion_titulo_hoja').text(plantilla.titulo).toggleClass('d-none', !esConsentimiento);
            $('.editor-indicacion-hoja', '#documento_atencion_texto').toggleClass('con-firmas', esConsentimiento);
            $('#documento_atencion_contenido').html(DocumentosClinicos.completarDatos(plantilla.contenido, VALORES_ATENCION));
            var firmas = $('#documento_atencion_firmas').toggleClass('d-none', !esConsentimiento);
            if (!firmas.data('original')) firmas.data('original', firmas.html());
            firmas.html(DocumentosClinicos.completarDatos(firmas.data('original'), VALORES_ATENCION));

            var pendientes = $('#documento_atencion_texto .indicacion-dato-pendiente').length;
            $('#documento_atencion_ayuda').html('Los datos de la mascota y del tutor se completaron con esta atención. Puedes ajustar el texto antes de emitir.'
                + (pendientes ? ' <b>Completa a mano los datos marcados en amarillo.</b>' : ''));
        }
        $('#modal_documento_atencion').modal('show');
    }

    function datosPdfDocumentoAtencion() {
        var plantilla = documentoAtencionActual.plantilla;
        var esConsentimiento = documentoAtencionActual.seccion === 'consentimientos';
        return {
            titulo: plantilla.titulo,
            logo: $('#documento_atencion_logo').attr('src'),
            contenido: $('#documento_atencion_contenido').html(),
            firmasHtml: esConsentimiento ? $('#documento_atencion_firmas').clone().removeAttr('id contenteditable').removeClass('d-none').prop('outerHTML') : '',
            centrado: esConsentimiento,
            estilo: $('link[href*="css/style.css"]').attr('href') || "{{ asset('css/style.css') }}"
        };
    }

    function vistaPdfDocumentoAtencion() {
        if (!documentoAtencionActual) return;
        var html = DocumentosClinicos.armarDocumentoPdf(datosPdfDocumentoAtencion());
        $('#modal_documento_atencion').one('hidden.bs.modal', function () {
            DocumentosClinicos.cargarHoja(document.getElementById('documento_atencion_marco'), html);
            $('#modal_documento_atencion_pdf').modal('show');
        }).modal('hide');
    }

    function emitirDocumentoAtencion() {
        if (!documentoAtencionActual) return;
        var esConsentimiento = documentoAtencionActual.seccion === 'consentimientos';
        var pendientes = $('#documento_atencion_texto:visible .indicacion-dato-pendiente').length;
        var emitir = function () {
            $('#modal_documento_atencion').modal('hide');
            swal({
                icon: 'success',
                title: esConsentimiento ? 'Consentimiento emitido' : 'Indicación emitida',
                text: esConsentimiento
                    ? 'Quedó en los documentos de esta atención y se envió al tutor para su firma.'
                    : 'Quedó en los documentos de esta atención y se envió al tutor.'
            });
        };
        if (!pendientes) { emitir(); return; }
        swal({
            title: 'Hay datos sin completar',
            text: 'Quedan ' + pendientes + ' datos marcados en amarillo. Si emites así, saldrán como [Dato] en el documento.',
            icon: 'warning',
            buttons: ['Revisar', 'Emitir igual']
        }).then(function (acepta) { if (acepta) emitir(); });
    }
</script>
