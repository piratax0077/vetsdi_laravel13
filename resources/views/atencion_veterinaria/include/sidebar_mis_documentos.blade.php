{{-- Sidebar derecho de la ficha veterinaria: indicaciones y consentimientos creados por el profesional
     en Configuración > Configurar emisión de documentos, listos para abrir y emitir en esta atención. --}}
@php
    $plantillasActivas = ['indicaciones' => [], 'consentimientos' => []];
    $lugarConfiguracion = null;
    if (!empty($profesional->id) && (int) $profesional->id_usuario === (int) auth()->id()) {
        $lugarDocumento = $hora_medica->id_lugar_atencion ?? $fichaAtencion->id_lugar_atencion ?? $id_lugar_atencion ?? null;
        $plantillasActivas = \App\Models\PlantillaDocumentoVeterinario::delProfesional(
            $profesional->id, $lugarDocumento, soloActivas: true
        );
        if ($lugarDocumento && \App\Models\PlantillaDocumentoVeterinario::lugaresDelProfesional($profesional->id)
            ->where('id_lugar_atencion', $lugarDocumento)->exists()) {
            $lugarConfiguracion = $lugarDocumento;
        }
    }
    $horaDocumento = $hora_medica->id ?? null;
    $panelDocumentos = $panelDocumentosId ?? 'collapse_mis_documentos';
    $encabezadoDocumentos = isset($panelDocumentosId) ? $panelDocumentos.'_titulo' : 'heading_mis_documentos';

    $etiquetaDato = fn ($clave, $texto) => '<span class="indicacion-dato" data-dato="' . $clave . '">' . $texto . '</span>';
@endphp

<div class="card-sidebar">
    <div class="card-header-sidebar" id="{{ $encabezadoDocumentos }}">
        <h2 class="mb-0">
            <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#{{ $panelDocumentos }}" aria-expanded="false" aria-controls="{{ $panelDocumentos }}"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                <span class="sb-acordeon__icono"><i class="feather icon-folder"></i></span>
                <span class="sb-acordeon__titulo">MIS INDICACIONES Y CONSENTIMIENTOS</span>
            </button>
        </h2>
    </div>
    <div id="{{ $panelDocumentos }}" class="collapse" aria-labelledby="{{ $encabezadoDocumentos }}" data-parent="{{ $acordeonDocumentos ?? '#accordion_side_bar' }}" data-panel-documentos-vet>
        <div class="card-body-sidebar">
            @foreach (['indicaciones' => 'Indicaciones', 'consentimientos' => 'Consentimientos informados'] as $claveSeccion => $tituloSeccion)
                <p class="sidebar-documentos-titulo">{{ $tituloSeccion }}</p>
                <div data-plantillas-seccion="{{ $claveSeccion }}">
                    @foreach ($plantillasActivas[$claveSeccion] as $plantilla)
                        <button type="button" class="btn btn-sm btn-info btn-block text-left" data-plantilla-id="{{ $plantilla['id'] }}">+ {{ $plantilla['titulo'] }}</button>
                    @endforeach
                </div>
                <p @class(['sidebar-documentos-vacio', 'd-none' => $plantillasActivas[$claveSeccion]]) data-plantillas-vacio="{{ $claveSeccion }}">
                    Aún no tienes {{ \Illuminate\Support\Str::lower($tituloSeccion) }} habilitados.
                </p>
            @endforeach
            <a href="{{ route('profesional.indicaciones_medicas', ['id_lugar_atencion' => $lugarConfiguracion]) }}" target="_blank" rel="noopener" class="sidebar-documentos-administrar"><i class="feather icon-settings"></i> Administrar mis plantillas</a>
        </div>
    </div>
</div>

@once
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

<script src="{{ asset('js/documentos_clinicos.js') }}?v=20261111-2"></script>
<script>
    // Plantillas del profesional y datos de esta atención para completarlas
    var DOCUMENTOS_ATENCION = @json($plantillasActivas);
    var VALORES_ATENCION = @json($valoresDocumento ?? []);
    var LOGO_DOCUMENTO_BASE = "{{ asset('images/vet-color-h.svg') }}";
    var documentoAtencionActual = null;

    $(function () {
        const panel = $('[data-panel-documentos-vet]');
        const ruta = @json($horaDocumento ? route('profesional.plantillas.atencion', ['hora' => $horaDocumento]) : null);
        let consultandoPlantillas = false;

        async function actualizarPlantillas() {
            if (!ruta || consultandoPlantillas) return;
            consultandoPlantillas = true;
            try {
                const respuesta = await fetch(ruta, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
                if (!respuesta.ok) return;
                const datos = await respuesta.json();
                if (datos.estado !== 1) return;
                DOCUMENTOS_ATENCION = datos.plantillas;
                Object.entries(DOCUMENTOS_ATENCION).forEach(([seccion, plantillas]) => {
                    const lista = panel.find('[data-plantillas-seccion="' + seccion + '"]').empty();
                    plantillas.forEach(plantilla => {
                        $('<button>', {
                            type: 'button',
                            class: 'btn btn-sm btn-info btn-block text-left',
                            'data-plantilla-id': plantilla.id,
                            text: '+ ' + plantilla.titulo
                        }).appendTo(lista);
                    });
                    panel.find('[data-plantillas-vacio="' + seccion + '"]').toggleClass('d-none', plantillas.length > 0);
                });
            } catch (error) {
                console.warn('No fue posible actualizar las plantillas de la atención.', error);
            } finally {
                consultandoPlantillas = false;
            }
        }

        panel.on('show.bs.collapse', actualizarPlantillas);
        $(window).on('focus', function () {
            if (panel.hasClass('show')) actualizarPlantillas();
        });
        panel.on('click', '[data-plantilla-id]', function () {
            abrirDocumentoAtencion($(this).closest('[data-plantillas-seccion]').attr('data-plantillas-seccion'), this.dataset.plantillaId);
        });

        $('#documento_atencion_contenido').on('input', function () {
            $(this).find('[data-etiqueta-original]').each(function () {
                const valor = this.textContent.trim();
                const pendiente = !valor || valor === this.dataset.etiquetaOriginal;
                $(this).toggleClass('indicacion-dato indicacion-dato-pendiente', pendiente);
                this.title = pendiente ? 'Completa este dato a mano' : '';
            });
        });

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
        var plantilla = (DOCUMENTOS_ATENCION[seccion] || []).find(function (registro) { return String(registro.id) === String(id); });
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
            DocumentosClinicos.mostrarVisorArchivo($('#documento_atencion_archivo'), { url: plantilla.archivo_url || '', nombre: plantilla.archivo || '', tipo: plantilla.archivo_tipo || '' });
        } else {
            $('#documento_atencion_logo').attr('src', plantilla.logo || LOGO_DOCUMENTO_BASE);
            $('#documento_atencion_titulo_hoja').text(plantilla.titulo).toggleClass('d-none', !esConsentimiento);
            $('.editor-indicacion-hoja', '#documento_atencion_texto').toggleClass('con-firmas', esConsentimiento);
            const valores = {...VALORES_ATENCION};
            for (const selector of ['#peso_examen_fisico', '#peso']) {
                const campo = $(selector + ':visible').first();
                const peso = String(campo.val() ?? '').trim();
                if (/^\d+(?:[.,]\d+)?$/.test(peso) && Number(peso.replace(',', '.')) > 0) {
                    valores.peso = peso;
                    break;
                }
            }
            const editor = $('#documento_atencion_contenido');
            documentoAtencionActual.datosUsados = Object.fromEntries(
                $('<div>').html(plantilla.contenido).find('.indicacion-dato').get()
                    .map(etiqueta => etiqueta.dataset.dato)
                    .filter(clave => Object.hasOwn(valores, clave) && valores[clave])
                    .map(clave => [clave, String(valores[clave])])
            );
            editor.html(DocumentosClinicos.completarDatos(plantilla.contenido, valores));
            editor.find('.indicacion-dato-pendiente').each(function () {
                this.removeAttribute('contenteditable');
                this.dataset.etiquetaOriginal = this.textContent.trim();
            });
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

    const urlEmitirDocumento = @json(route('profesional.plantillas.emitir'));
    const horaDocumento = @json($horaDocumento);
    let emitiendoDocumento = false;
    function emitirDocumentoAtencion() {
        if (!documentoAtencionActual) return;
        var esConsentimiento = documentoAtencionActual.seccion === 'consentimientos';
        var pendientes = $('#documento_atencion_contenido:visible .indicacion-dato-pendiente').length;
        var emitir = async function () {
            if (emitiendoDocumento) return;
            if (!horaDocumento) {
                swal({icon: 'warning', text: 'Guarda la ficha de atención antes de emitir el documento.'});
                return;
            }
            emitiendoDocumento = true;
            try {
                const respuesta = await fetch(urlEmitirDocumento, {
                    method: 'POST',
                    headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token())},
                    body: JSON.stringify({
                        id_hora_medica: horaDocumento,
                        id_documento_profesional: documentoAtencionActual.plantilla.id,
                        contenido: DocumentosClinicos.contenidoParaPdf($('#documento_atencion_contenido').html()),
                        datos_usados: documentoAtencionActual.datosUsados || {}
                    })
                });
                const datos = await respuesta.json().catch(() => ({}));
                if (!respuesta.ok || datos.estado !== 1) throw new Error(datos.msj || datos.message || 'No se pudo emitir el documento.');
                $('#modal_documento_atencion').modal('hide');
                swal({
                    icon: 'success',
                    title: esConsentimiento ? 'Consentimiento emitido' : 'Indicación emitida',
                    text: datos.msj,
                    buttons: {
                        cancel: 'Cerrar',
                        ver: {text: 'Ver documento', value: 'ver'},
                        ...(datos.consulta_url ? {firmar: {text: 'Firmar en la consulta', value: 'firmar'}} : {})
                    }
                }).then(accion => {
                    if (accion === 'ver') location.href = datos.url;
                    if (accion === 'firmar') DocumentosClinicos.firmarEnConsulta(datos.consulta_url);
                });
            } catch (error) {
                swal({icon: 'error', text: error.message});
            } finally {
                emitiendoDocumento = false;
            }
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
@endonce
