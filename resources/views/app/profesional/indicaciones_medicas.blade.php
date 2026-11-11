@extends('template.profesional.template')
@section('content')
@php
    // Plantillas de muestra compartidas con el sidebar de la ficha veterinaria
    $secciones = require resource_path('views/app/profesional/indicaciones/datos_muestra.php');
@endphp
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="row">
            <div class="col-md-12 mb-3">
                <h5 class="titulo-modulo f-26 d-inline">Indicaciones y consentimientos</h5>
            </div>
        </div>
        <!--Cierre: Header-->
        <ul class="nav pill-moderno pill-moderno-white pill-sin-subrayado mb-4" role="tablist" aria-label="Indicaciones y consentimientos">
            @foreach ($secciones as $clave => $seccion)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="pestana_{{ $clave }}_tab" data-toggle="pill" href="#pestana_{{ $clave }}" role="tab"
                        aria-controls="pestana_{{ $clave }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $seccion['pestana'] }}</a>
                </li>
            @endforeach
        </ul>
        <div class="tab-content">
            @foreach ($secciones as $clave => $seccion)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pestana_{{ $clave }}" role="tabpanel" aria-labelledby="pestana_{{ $clave }}_tab">
                    <div class="card">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather {{ $seccion['icono'] }}"></i></span>{{ $seccion['titulo'] }}</h5>
                            <div class="encabezado-card-acciones">
                                <button type="button" class="btn btn-info" onclick="abrirModalIndicacion(null, '{{ $clave }}')"><i class="feather icon-plus"></i> {{ $seccion['agregar'] }}</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Buscar</label>
                                        <input type="text" class="form-control form-control-sm filtro-documento" data-seccion="{{ $clave }}" data-filtro="texto" placeholder="{{ $seccion['buscar'] }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Categoría</label>
                                        <select class="form-control form-control-sm filtro-documento" data-seccion="{{ $clave }}" data-filtro="categoria">
                                            <option value="">Todas</option>
                                            @foreach ($seccion['categorias'] as $categoria)
                                                <option value="{{ $categoria }}">{{ $categoria }}</option>
                                            @endforeach
                                        </select>
                                        {{-- Sugerencias para el campo Categoría del modal; ahí se puede escribir cualquier otra --}}
                                        <datalist id="categorias_{{ $clave }}">
                                            @foreach ($seccion['categorias'] as $categoria)
                                                <option value="{{ $categoria }}"></option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="tabla_{{ $clave }}" class="table table-striped table-hover table-sm" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Categoría</th>
                                            <th>Alcance</th>
                                            <th>Habilitar</th>
                                            <th class="text-left align-middle">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($seccion['registros'] as $registro)
                                            <tr data-seccion="{{ $clave }}" data-titulo="{{ \Illuminate\Support\Str::lower($registro['titulo']) }}" data-categoria="{{ $registro['categoria'] }}" data-tipo="{{ $registro['tipo'] }}"
                                                data-contenido="{{ $registro['contenido'] ?? '' }}" data-archivo="{{ $registro['archivo'] ?? '' }}" data-logo="{{ $registro['logo'] ?? '' }}">
                                                <td class="align-middle">{{ $registro['titulo'] }}</td>
                                                <td class="align-middle">{{ $registro['categoria'] }}</td>
                                                <td class="align-middle">{{ $registro['alcance'] === 'personal' ? 'Personal' : 'Compartida' }}</td>
                                                <td class="align-middle">
                                                    <div class="switch switch-success d-inline m-r-10">
                                                        <input type="checkbox" id="documento_estado_{{ $registro['id'] }}" @checked($registro['activo'])>
                                                        <label for="documento_estado_{{ $registro['id'] }}" class="cr"></label>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-left">
                                                    <button type="button" class="btn btn-purple btn-icon" onclick="verIndicacion(this)"><i class="feather icon-eye"></i></button>
                                                    <button type="button" class="btn btn-danger btn-icon" onclick="eliminarIndicacion(this)"><i class="feather icon-x"></i></button>
                                                    <button type="button" class="btn btn-warning btn-icon" onclick="editarIndicacion(this)"><i class="feather icon-edit"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="indicaciones-vacio" id="vacio_{{ $clave }}" role="status">{{ $seccion['vacio'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal: agregar / editar -->
<div id="modal_indicacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_indicacion_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-celeste">
                <h5 class="modal-title" id="modal_indicacion_titulo"><i class="feather icon-file-text mr-1"></i> Agregar indicación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="floating-label-activo-sm">Título</label>
                        <input type="text" class="form-control form-control-sm" id="indicacion_titulo" maxlength="120">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="floating-label-activo-sm">Categoría</label>
                        <input type="text" class="form-control form-control-sm" id="indicacion_categoria" list="categorias_indicaciones" maxlength="80" placeholder="Escribe o elige una categoría" autocomplete="off">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="floating-label-activo-sm">Alcance</label>
                        <select class="form-control form-control-sm" id="indicacion_alcance">
                            <option value="personal">Personal (solo yo)</option>
                            <option value="compartida">Compartida con mi centro</option>
                        </select>
                    </div>
                </div>

                <ul class="nav pill-moderno pill-sin-subrayado mb-3" role="tablist" aria-label="Forma de crear el documento">
                    <li class="nav-item"><a class="nav-link active" id="indicacion_tab_texto" data-toggle="pill" href="#indicacion_panel_texto" role="tab" aria-selected="true">Escribir texto</a></li>
                    <li class="nav-item"><a class="nav-link" id="indicacion_tab_archivo" data-toggle="pill" href="#indicacion_panel_archivo" role="tab" aria-selected="false">Subir archivo</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="indicacion_panel_texto" role="tabpanel">
                        <div class="editor-indicacion">
                            <!-- Barra de formato -->
                            <div class="editor-indicacion-barra" role="toolbar" aria-label="Formato del texto">
                                <select class="form-control form-control-sm" id="editor_tamano" aria-label="Tamaño del texto" onchange="aplicarTamano(this.value)">
                                    <option value="3">Normal</option>
                                    <option value="2">Pequeño</option>
                                    <option value="4">Grande</option>
                                    <option value="5">Título</option>
                                </select>
                                <span class="editor-separador"></span>
                                <button type="button" class="editor-boton" data-comando="bold" title="Negrita" aria-label="Negrita"><i class="feather icon-bold"></i></button>
                                <button type="button" class="editor-boton" data-comando="italic" title="Cursiva" aria-label="Cursiva"><i class="feather icon-italic"></i></button>
                                <button type="button" class="editor-boton" data-comando="underline" title="Subrayado" aria-label="Subrayado"><i class="feather icon-underline"></i></button>
                                <span class="editor-separador"></span>
                                <div class="editor-color">
                                    <button type="button" class="editor-boton" id="editor_boton_color" title="Color del texto" aria-label="Color del texto" aria-expanded="false" onclick="alternarPanelColor()"><span class="editor-color-actual" id="editor_color_actual"></span></button>
                                    <div class="editor-color-panel" id="editor_color_panel">
                                        <div class="editor-color-muestras">
                                            @foreach (['#272727', '#3f3f46', '#52525b', '#71717a', '#a1a1aa', '#d4d4d8', '#e4e4e7', '#ffffff', '#16a34a', '#2563eb', '#4f46e5', '#7b35a5', '#c026d3', '#db2777', '#dc2626', '#ea580c'] as $color)
                                                <button type="button" style="background: {{ $color }}" title="{{ $color }}" aria-label="Color {{ $color }}" onclick="aplicarColor('{{ $color }}')"></button>
                                            @endforeach
                                        </div>
                                        <label class="editor-color-personalizado mb-0">
                                            Personalizado
                                            <input type="color" id="editor_color_personalizado" value="#2c69f6" onchange="aplicarColor(this.value)">
                                        </label>
                                    </div>
                                </div>
                                <span class="editor-separador"></span>
                                <button type="button" class="editor-boton" data-comando="justifyLeft" title="Alinear a la izquierda" aria-label="Alinear a la izquierda"><i class="feather icon-align-left"></i></button>
                                <button type="button" class="editor-boton" data-comando="justifyCenter" title="Centrar" aria-label="Centrar"><i class="feather icon-align-center"></i></button>
                                <button type="button" class="editor-boton" data-comando="justifyRight" title="Alinear a la derecha" aria-label="Alinear a la derecha"><i class="feather icon-align-right"></i></button>
                                <button type="button" class="editor-boton" data-comando="insertUnorderedList" title="Lista con viñetas" aria-label="Lista con viñetas"><i class="feather icon-list"></i></button>
                                <button type="button" class="editor-boton" data-comando="insertOrderedList" title="Lista numerada" aria-label="Lista numerada"><strong class="f-12">1.</strong></button>
                                <span class="editor-separador"></span>
                                <div class="dropdown d-inline-block">
                                    <button type="button" class="editor-boton dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Insertar dato automático">
                                        <i class="feather icon-plus-circle mr-1"></i> Insertar dato
                                    </button>
                                    <div class="dropdown-menu">
                                        @foreach (['mascota' => 'Mascota', 'especie' => 'Especie', 'raza' => 'Raza', 'peso' => 'Peso', 'tutor' => 'Tutor', 'rut_tutor' => 'RUT del tutor', 'microchip' => 'N° de microchip', 'edad' => 'Edad', 'profesional' => 'Profesional', 'rut_profesional' => 'RUT del profesional', 'fecha' => 'Fecha'] as $clave => $texto)
                                            <button type="button" class="dropdown-item" onmousedown="event.preventDefault()" onclick="insertarDato('{{ $clave }}', '{{ $texto }}')">{{ $texto }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="editor-separador"></span>
                                <button type="button" class="editor-boton" data-comando="undo" title="Deshacer" aria-label="Deshacer"><i class="feather icon-rotate-ccw"></i></button>
                                <button type="button" class="editor-boton" data-comando="redo" title="Rehacer" aria-label="Rehacer"><i class="feather icon-rotate-cw"></i></button>
                                <span class="editor-separador"></span>
                                <button type="button" class="editor-boton editor-boton-dictado" id="editor_dictado" title="Dictar por voz" aria-label="Dictar por voz" aria-pressed="false" onclick="alternarDictado()"><i class="feather icon-mic"></i></button>
                                <span class="editor-dictado-estado" id="editor_dictado_estado" role="status" aria-live="polite"></span>
                            </div>
                            <!-- Hoja: logo + contenido -->
                            <div class="editor-indicacion-hoja">
                                <div class="editor-indicacion-logo">
                                    <img src="{{ asset('images/vet-color-h.svg') }}" alt="Logo de la indicación" id="editor_logo">
                                    <div>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="$('#editor_logo_archivo').trigger('click')"><i class="feather icon-image"></i> Cambiar logo</button>
                                        <button type="button" class="btn btn-outline-dark btn-sm d-none" id="editor_logo_restablecer" onclick="restablecerLogo()">Usar logo Veterchile</button>
                                        <input type="file" id="editor_logo_archivo" class="d-none" accept="image/png,image/jpeg,image/svg+xml,image/webp" onchange="cambiarLogo(this)">
                                    </div>
                                </div>
                                <!-- Título del consentimiento, centrado bajo la línea del logo; se completa con el campo Título -->
                                <div class="editor-documento-titulo d-none" id="editor_titulo_documento" data-placeholder="Título del consentimiento"></div>
                                <div class="editor-indicacion-texto" id="indicacion_contenido" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Texto de la indicación" data-placeholder="Escribe aquí la indicación..."></div>
                                <!-- Firmas al pie: solo en consentimientos informados -->
                                <div class="documento-firmas d-none" id="editor_firmas" contenteditable="false">
                                    {{-- Los datos se completan solos con la información de la atención --}}
                                    <div class="documento-firma">
                                        <div class="documento-firma-espacio"></div>
                                        <div class="documento-firma-linea">Firma del tutor / responsable</div>
                                        <dl class="documento-firma-datos">
                                            <div><dt>Nombre</dt><dd><span class="indicacion-dato" data-dato="tutor">Tutor</span></dd></div>
                                            <div><dt>RUT</dt><dd><span class="indicacion-dato" data-dato="rut_tutor">RUT del tutor</span></dd></div>
                                        </dl>
                                    </div>
                                    <div class="documento-firma">
                                        <div class="documento-firma-espacio"></div>
                                        <div class="documento-firma-linea">Firma del profesional veterinario</div>
                                        <dl class="documento-firma-datos">
                                            <div><dt>Nombre</dt><dd><span class="indicacion-dato" data-dato="profesional">Profesional</span></dd></div>
                                            <div><dt>RUT</dt><dd><span class="indicacion-dato" data-dato="rut_profesional">RUT del profesional</span></dd></div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade indicaciones-archivo" id="indicacion_panel_archivo" role="tabpanel">
                        <form action="#" class="dropzone" id="indicacion_dropzone">
                            <div class="dz-message">Arrastra aquí el archivo o haz clic para seleccionarlo<br><small>PDF, Word o imagen. Máximo 5 MB.</small></div>
                        </form>
                        <!-- Visor del archivo subido -->
                        <div class="visor-archivo mt-3 d-none" id="indicacion_visor_archivo"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x"></i> Cancelar</button>
                <button type="button" class="btn btn-outline-info" id="indicacion_boton_pdf" onclick="vistaPdfDesdeEditor()"><i class="feather icon-file-text"></i> Vista previa PDF</button>
                <button type="button" class="btn btn-info" onclick="guardarIndicacion()"><i class="feather icon-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: ver -->
<div id="modal_ver_indicacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_ver_indicacion_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-celeste">
                <h5 class="modal-title" id="modal_ver_indicacion_titulo"><i class="feather icon-eye mr-1"></i> Indicación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <h6 class="tit-gen mb-3" id="ver_indicacion_nombre"></h6>
                <div class="indicaciones-vista-previa" id="ver_indicacion_contenido"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x"></i> Cerrar</button>
                <button type="button" class="btn btn-info" id="ver_indicacion_boton_pdf" onclick="vistaPdfDesdeVer()"><i class="feather icon-file-text"></i> Generar PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: vista previa del PDF -->
<div id="modal_vista_pdf" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_vista_pdf_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-celeste">
                <h5 class="modal-title" id="modal_vista_pdf_titulo"><i class="feather icon-file-text mr-1"></i> Vista previa PDF</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <iframe class="visor-pdf-marco" id="vista_pdf_marco" title="Vista previa del PDF de la indicación"></iframe>
                <p class="visor-pdf-ayuda mb-0">Al descargar se abre la ventana de impresión: elige <b>Guardar como PDF</b> en el destino.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-arrow-left"></i> Volver</button>
                <button type="button" class="btn btn-info" onclick="descargarPdf()"><i class="feather icon-download"></i> Descargar PDF</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('js/documentos_clinicos.js') }}?v=20261110-1"></script>
<script>
    var filaEnEdicion = null;
    var dropzoneIndicacion = null;
    var LOGO_BASE = "{{ asset('images/vet-color-h.svg') }}";
    var rangoEditor = null;
    var archivoActual = null;
    // La hoja del PDF usa el mismo style.css (y la misma versión) que ya cargó la página
    var STYLE_CSS = $('link[href*="css/style.css"]').attr('href') || "{{ asset('css/style.css') }}";
    var modalOrigenPdf = null;
    var filaVista = null;
    // Sección del documento que se está editando: indicaciones o consentimientos
    var seccionActual = 'indicaciones';
    var TEXTOS_SECCION = {
        indicaciones: {
            agregar: 'Agregar indicación', editar: 'Editar indicación', ver: 'Indicación', icono: 'icon-file-text',
            campo: 'Texto de la indicación', placeholder: 'Escribe aquí la indicación...', titulo: 'Indicación médica',
            guardado: 'La indicación quedó guardada.', eliminar: '¿Está seguro de que desea ELIMINAR la indicación?'
        },
        consentimientos: {
            agregar: 'Agregar consentimiento', editar: 'Editar consentimiento', ver: 'Consentimiento informado', icono: 'icon-clipboard',
            campo: 'Texto del consentimiento', placeholder: 'Escribe aquí el consentimiento informado...', titulo: 'Consentimiento informado',
            guardado: 'El consentimiento quedó guardado.', eliminar: '¿Está seguro de que desea ELIMINAR el consentimiento?'
        }
    };
    // Dropzone se activa solo al cargar la página; se desactiva antes para configurarlo a mano
    if (window.Dropzone) Dropzone.autoDiscover = false;

    $(document).ready(function () {
        var zonaArchivo = document.getElementById('indicacion_dropzone');
        if (window.Dropzone && zonaArchivo) {
            if (zonaArchivo.dropzone) zonaArchivo.dropzone.destroy();
            dropzoneIndicacion = new Dropzone(zonaArchivo, {
                url: '#',
                autoProcessQueue: false,
                maxFiles: 1,
                maxFilesize: 5,
                acceptedFiles: '.pdf,.doc,.docx,image/*',
                addRemoveLinks: true,
                dictRemoveFile: 'Quitar',
                dictMaxFilesExceeded: 'Solo se puede adjuntar un archivo.'
            });
            // Al elegir un archivo se muestra en el visor; al quitarlo, se oculta
            dropzoneIndicacion.on('addedfile', function (archivo) {
                if (this.files.length > 1) this.removeFile(this.files[0]);
                archivoActual = { url: URL.createObjectURL(archivo), nombre: archivo.name, tipo: archivo.type };
                mostrarVisorArchivo($('#indicacion_visor_archivo'), archivoActual);
            });
            dropzoneIndicacion.on('removedfile', function () {
                if (this.files.length) return;
                archivoActual = null;
                $('#indicacion_visor_archivo').addClass('d-none').empty();
            });
        }
        // El título escrito arriba se refleja en la hoja del consentimiento
        $('#indicacion_titulo').on('input', actualizarTituloHoja);

        $('.filtro-documento').on('input change', function () { filtrarIndicaciones($(this).data('seccion')); });

        // Los botones de la barra no deben quitarle la selección al texto
        $('.editor-indicacion-barra').on('mousedown', '.editor-boton, .editor-color-muestras button', function (e) { e.preventDefault(); });
        $('.editor-indicacion-barra').on('click', '.editor-boton[data-comando]', function () {
            ejecutarComando($(this).data('comando'));
        });

        // Se recuerda dónde estaba el cursor para insertar datos, colores o dictado en ese punto
        $(document).on('selectionchange', function () {
            var seleccion = window.getSelection();
            if (seleccion.rangeCount && document.getElementById('indicacion_contenido').contains(seleccion.anchorNode)) {
                rangoEditor = seleccion.getRangeAt(0).cloneRange();
                marcarBotonesActivos();
            }
        });

        // Cierra el panel de colores al hacer clic fuera
        $(document).on('mousedown', function (e) {
            if (!$(e.target).closest('.editor-color').length) cerrarPanelColor();
        });

        $('#modal_indicacion').on('hidden.bs.modal', function () { detenerDictado(); cerrarPanelColor(); });

        // La vista previa PDF solo aplica a indicaciones escritas
        $('#indicacion_tab_texto, #indicacion_tab_archivo').on('shown.bs.tab', function () {
            $('#indicacion_boton_pdf').toggleClass('d-none', this.id === 'indicacion_tab_archivo');
        });

        // Al cerrar la vista previa se vuelve al modal desde donde se abrió
        $('#modal_vista_pdf').on('shown.bs.modal', function () { ajustarHojaPdf(); });
        // Reajusta la hoja si cambia el ancho del marco (girar el celular, cambiar el tamaño de la ventana)
        if (window.ResizeObserver) {
            new ResizeObserver(function () { if ($('#modal_vista_pdf').hasClass('show')) ajustarHojaPdf(); }).observe(document.getElementById('vista_pdf_marco'));
        } else {
            $(window).on('resize', function () { if ($('#modal_vista_pdf').hasClass('show')) ajustarHojaPdf(); });
        }
        $('#modal_vista_pdf').on('hidden.bs.modal', function () {
            if (modalOrigenPdf) $(modalOrigenPdf).modal('show');
            modalOrigenPdf = null;
        });
    });

    /* ---------- Tabla ---------- */

    function valorFiltro(seccion, filtro) {
        return ($('.filtro-documento[data-seccion="' + seccion + '"][data-filtro="' + filtro + '"]').val() || '').toLowerCase().trim();
    }

    function filtrarIndicaciones(seccion) {
        seccion = seccion || seccionActual;
        var texto = valorFiltro(seccion, 'texto');
        var categoria = valorFiltro(seccion, 'categoria');

        var visibles = 0;
        $('#tabla_' + seccion + ' tbody tr').each(function () {
            var fila = $(this);
            var coincide = (!texto || String(fila.attr('data-titulo')).indexOf(texto) !== -1)
                && (!categoria || String(fila.attr('data-categoria')).toLowerCase() === categoria);

            fila.toggle(coincide);
            if (coincide) visibles++;
        });
        $('#vacio_' + seccion).toggle(visibles === 0);
    }

    // Suma la categoría a las sugerencias si es nueva
    // Una categoría nueva se suma a las sugerencias del modal y al select del buscador de su tabla
    function recordarCategoria(categoria) {
        var lista = $('#categorias_' + seccionActual);
        var existe = lista.find('option').filter(function () {
            return this.value.toLowerCase() === categoria.toLowerCase();
        }).length;
        if (existe) return;
        lista.append($('<option>').val(categoria));
        $('.filtro-documento[data-seccion="' + seccionActual + '"][data-filtro="categoria"]').append($('<option>').val(categoria).text(categoria));
    }

    /* ---------- Modal agregar / editar ---------- */

    function prepararModalSeccion(seccion, editando) {
        seccionActual = seccion;
        var textos = TEXTOS_SECCION[seccion];
        $('#modal_indicacion_titulo').html('<i class="feather ' + textos.icono + ' mr-1"></i> ' + (editando ? textos.editar : textos.agregar));
        $('#indicacion_categoria').attr('list', 'categorias_' + seccion);
        $('#indicacion_contenido').attr({ 'data-placeholder': textos.placeholder, 'aria-label': textos.campo });
        $('#editor_firmas').toggleClass('d-none', seccion !== 'consentimientos');
        $('.editor-indicacion-hoja').toggleClass('con-firmas', seccion === 'consentimientos');
        $('#editor_titulo_documento').toggleClass('d-none', seccion !== 'consentimientos');
    }

    function abrirModalIndicacion(fila, seccion) {
        filaEnEdicion = fila || null;
        prepararModalSeccion(fila ? fila.attr('data-seccion') : (seccion || 'indicaciones'), !!fila);
        $('#indicacion_titulo, #indicacion_categoria').val('');
        $('#indicacion_alcance').val('personal');
        $('#indicacion_contenido').html('');
        $('#editor_tamano').val('3');
        ponerLogo(LOGO_BASE);
        if (dropzoneIndicacion) dropzoneIndicacion.removeAllFiles(true);
        archivoActual = null;
        $('#indicacion_visor_archivo').addClass('d-none').empty();
        $('#indicacion_tab_texto').tab('show');
        if (fila) {
            $('#indicacion_titulo').val(fila.find('td:eq(0)').text().trim());
            $('#indicacion_categoria').val(fila.attr('data-categoria'));
            $('#indicacion_alcance').val(fila.find('td:eq(2)').text().trim() === 'Personal' ? 'personal' : 'compartida');
            if (fila.attr('data-tipo') === 'texto') {
                $('#indicacion_contenido').html(fila.attr('data-contenido'));
                ponerLogo(fila.attr('data-logo') || LOGO_BASE);
            } else {
                // Al editar se muestra el archivo guardado; si sube otro, lo reemplaza
                archivoActual = archivoDeFila(fila);
                mostrarVisorArchivo($('#indicacion_visor_archivo'), archivoActual);
                $('#indicacion_tab_archivo').tab('show');
            }
        }
        rangoEditor = null;
        actualizarTituloHoja();
        $('#modal_indicacion').modal('show');
    }

    function actualizarTituloHoja() {
        $('#editor_titulo_documento').text($('#indicacion_titulo').val().trim());
    }

    function editarIndicacion(boton) { abrirModalIndicacion($(boton).closest('tr')); }

    /* ---------- Editor de texto ---------- */

    function restaurarSeleccion() {
        var editor = document.getElementById('indicacion_contenido');
        editor.focus();
        var seleccion = window.getSelection();
        if (rangoEditor) {
            seleccion.removeAllRanges();
            seleccion.addRange(rangoEditor);
        } else {
            // Sin cursor previo, se escribe al final del texto
            var rango = document.createRange();
            rango.selectNodeContents(editor);
            rango.collapse(false);
            seleccion.removeAllRanges();
            seleccion.addRange(rango);
        }
    }

    function ejecutarComando(comando, valor) {
        restaurarSeleccion();
        document.execCommand('styleWithCSS', false, comando === 'foreColor');
        document.execCommand(comando, false, valor || null);
        marcarBotonesActivos();
    }

    function aplicarTamano(valor) { ejecutarComando('fontSize', valor); }

    function marcarBotonesActivos() {
        $('.editor-indicacion-barra .editor-boton[data-comando]').each(function () {
            var comando = $(this).data('comando');
            if (comando === 'undo' || comando === 'redo') return;
            var activo = false;
            try { activo = document.queryCommandState(comando); } catch (e) { activo = false; }
            $(this).toggleClass('activo', activo);
        });
    }

    function alternarPanelColor() {
        var panel = $('#editor_color_panel');
        panel.toggleClass('abierto');
        $('#editor_boton_color').attr('aria-expanded', panel.hasClass('abierto'));
    }

    function cerrarPanelColor() {
        $('#editor_color_panel').removeClass('abierto');
        $('#editor_boton_color').attr('aria-expanded', 'false');
    }

    function aplicarColor(color) {
        ejecutarComando('foreColor', color);
        $('#editor_color_actual').css('background', color);
        cerrarPanelColor();
    }

    // Inserta una etiqueta de dato automático que no se puede editar letra a letra
    function insertarDato(clave, texto) {
        restaurarSeleccion();
        var etiqueta = '<span class="indicacion-dato" contenteditable="false" data-dato="' + clave + '">' + texto + '</span>&nbsp;';
        document.execCommand('insertHTML', false, etiqueta);
    }

    /* ---------- Logo ---------- */

    function ponerLogo(ruta) {
        $('#editor_logo').attr('src', ruta);
        $('#editor_logo_restablecer').toggleClass('d-none', ruta === LOGO_BASE);
    }

    function cambiarLogo(input) {
        var archivo = input.files && input.files[0];
        input.value = '';
        if (!archivo) return;
        if (!/^image\//.test(archivo.type)) {
            swal({ title: 'Archivo no válido', text: 'El logo debe ser una imagen (PNG, JPG, SVG o WEBP).', icon: 'error' });
            return;
        }
        if (archivo.size > 2 * 1024 * 1024) {
            swal({ title: 'Imagen muy pesada', text: 'El logo no puede superar los 2 MB.', icon: 'error' });
            return;
        }
        var lector = new FileReader();
        lector.onload = function (e) { ponerLogo(e.target.result); };
        lector.readAsDataURL(archivo);
    }

    function restablecerLogo() { ponerLogo(LOGO_BASE); }

    /* ---------- Dictado por voz ---------- */

    var reconocimientoVoz = null;
    var dictando = false;

    function alternarDictado() {
        if (dictando) { detenerDictado(); return; }

        var Reconocimiento = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!Reconocimiento) {
            swal({ title: 'Dictado no disponible', text: 'Este navegador no permite el dictado por voz. Prueba con Chrome o Edge.', icon: 'info' });
            return;
        }

        reconocimientoVoz = new Reconocimiento();
        reconocimientoVoz.lang = 'es-CL';
        reconocimientoVoz.continuous = true;
        reconocimientoVoz.interimResults = true;

        reconocimientoVoz.onresult = function (evento) {
            var parcial = '';
            for (var i = evento.resultIndex; i < evento.results.length; i++) {
                var texto = evento.results[i][0].transcript;
                if (evento.results[i].isFinal) {
                    restaurarSeleccion();
                    document.execCommand('insertText', false, texto.trim() + ' ');
                    rangoEditor = window.getSelection().getRangeAt(0).cloneRange();
                } else {
                    parcial += texto;
                }
            }
            $('#editor_dictado_estado').text(parcial ? 'Escuchando: ' + parcial : 'Escuchando...');
        };
        reconocimientoVoz.onerror = function (evento) {
            if (evento.error === 'not-allowed' || evento.error === 'service-not-allowed') {
                swal({ title: 'Micrófono bloqueado', text: 'Permite el uso del micrófono en el navegador para dictar.', icon: 'warning' });
            }
            detenerDictado();
        };
        reconocimientoVoz.onend = function () {
            // Chrome corta tras un silencio; si el profesional no lo detuvo, se retoma
            if (dictando) { try { reconocimientoVoz.start(); } catch (e) { detenerDictado(); } }
        };

        try { reconocimientoVoz.start(); } catch (e) { return; }
        dictando = true;
        restaurarSeleccion();
        $('#editor_dictado').addClass('grabando').attr({ 'aria-pressed': 'true', title: 'Detener dictado' });
        $('#editor_dictado_estado').text('Escuchando...');
    }

    function detenerDictado() {
        dictando = false;
        if (reconocimientoVoz) { try { reconocimientoVoz.stop(); } catch (e) {} }
        $('#editor_dictado').removeClass('grabando').attr({ 'aria-pressed': 'false', title: 'Dictar por voz' });
        $('#editor_dictado_estado').text('');
    }

    /* ---------- Visor de archivos ---------- */

    function archivoDeFila(fila) {
        return { url: fila.attr('data-archivo-url') || '', nombre: fila.attr('data-archivo') || '', tipo: fila.attr('data-archivo-tipo') || '' };
    }

    // El visor (PDF, imagen o descarga) está en js/documentos_clinicos.js
    function mostrarVisorArchivo(destino, archivo) {
        DocumentosClinicos.mostrarVisorArchivo(destino, archivo);
    }

    /* ---------- Vista previa PDF ---------- */
    // Se arma siempre con el contenido actual, así que al editar y volver a generar refleja los cambios.

    // Líneas de firma del consentimiento informado (se usan en Ver y en el PDF)
    function htmlFirmas() {
        return $('#editor_firmas').clone().removeAttr('id contenteditable').removeClass('d-none').prop('outerHTML');
    }

    function armarDocumentoPdf(datos) {
        return DocumentosClinicos.armarDocumentoPdf({
            titulo: datos.titulo,
            logo: datos.logo,
            contenido: datos.contenido,
            firmasHtml: datos.firmas ? htmlFirmas() : '',
            centrado: datos.firmas,
            estilo: STYLE_CSS
        });
    }

    function ajustarHojaPdf(escalaFija) {
        DocumentosClinicos.ajustarHoja(document.getElementById('vista_pdf_marco'), escalaFija);
    }

    function abrirVistaPdf(datos, modalOrigen) {
        modalOrigenPdf = modalOrigen;
        $(modalOrigen).one('hidden.bs.modal', function () {
            DocumentosClinicos.cargarHoja(document.getElementById('vista_pdf_marco'), armarDocumentoPdf(datos));

            $('#modal_vista_pdf').modal('show');
        }).modal('hide');
    }

    function vistaPdfDesdeEditor() {
        detenerDictado();
        var editor = $('#indicacion_contenido');
        if (!editor.text().trim() && !editor.find('.indicacion-dato').length) {
            swal({ title: 'Sin contenido', text: 'Escribe el contenido antes de generar el PDF.', icon: 'info' });
            return;
        }
        abrirVistaPdf({
            titulo: $('#indicacion_titulo').val().trim() || TEXTOS_SECCION[seccionActual].titulo,
            logo: $('#editor_logo').attr('src'),
            contenido: editor.html(),
            firmas: seccionActual === 'consentimientos'
        }, '#modal_indicacion');
    }

    function vistaPdfDesdeVer() {
        if (!filaVista) return;
        abrirVistaPdf({
            titulo: filaVista.find('td:eq(0)').text().trim(),
            logo: filaVista.attr('data-logo') || LOGO_BASE,
            contenido: filaVista.attr('data-contenido'),
            firmas: filaVista.attr('data-seccion') === 'consentimientos'
        }, '#modal_ver_indicacion');
    }

    // Abre la ventana de impresión del navegador, donde se elige "Guardar como PDF"
    function descargarPdf() {
        DocumentosClinicos.imprimir(document.getElementById('vista_pdf_marco'));

    }

    /* ---------- Ver, guardar y eliminar ---------- */

    function verIndicacion(boton) {
        var fila = $(boton).closest('tr');
        var destino = $('#ver_indicacion_contenido').empty();
        filaVista = fila;
        $('#ver_indicacion_boton_pdf').toggleClass('d-none', fila.attr('data-tipo') !== 'texto');
        $('#modal_ver_indicacion_titulo').html('<i class="feather icon-eye mr-1"></i> ' + TEXTOS_SECCION[fila.attr('data-seccion')].ver);
        var esConsentimiento = fila.attr('data-seccion') === 'consentimientos' && fila.attr('data-tipo') === 'texto';
        $('#ver_indicacion_nombre').text(fila.find('td:eq(0)').text().trim()).toggleClass('d-none', esConsentimiento);
        if (fila.attr('data-tipo') === 'texto') {
            var logo = $('<div class="editor-indicacion-logo">').append($('<img alt="Logo de la indicación">').attr('src', fila.attr('data-logo') || LOGO_BASE));
            destino.append(logo);
            if (esConsentimiento) destino.append($('<div class="editor-documento-titulo">').text(fila.find('td:eq(0)').text().trim()));
            destino.append($('<div>').html(fila.attr('data-contenido')));
            if (fila.attr('data-seccion') === 'consentimientos') destino.append(htmlFirmas());
        } else {
            mostrarVisorArchivo(destino, archivoDeFila(fila));
        }
        $('#modal_ver_indicacion').modal('show');
    }

    // Fila vacía con el mismo formato de las que vienen del servidor
    function crearFila(seccion) {
        var id = 'documento_estado_' + Date.now();
        return $('<tr>').attr('data-seccion', seccion).append(
            '<td class="align-middle"></td><td class="align-middle"></td><td class="align-middle"></td>'
            + '<td class="align-middle"><div class="switch switch-success d-inline m-r-10"><input type="checkbox" id="' + id + '" checked><label for="' + id + '" class="cr"></label></div></td>'
            + '<td class="align-middle text-left">'
            + '<button type="button" class="btn btn-purple btn-icon" onclick="verIndicacion(this)"><i class="feather icon-eye"></i></button> '
            + '<button type="button" class="btn btn-danger btn-icon" onclick="eliminarIndicacion(this)"><i class="feather icon-x"></i></button> '
            + '<button type="button" class="btn btn-warning btn-icon" onclick="editarIndicacion(this)"><i class="feather icon-edit"></i></button>'
            + '</td>'
        );
    }

    function guardarIndicacion() {
        detenerDictado();
        var titulo = $('#indicacion_titulo').val().trim();
        var categoria = $('#indicacion_categoria').val().trim();
        var esTexto = $('#indicacion_tab_texto').hasClass('active');
        var editor = $('#indicacion_contenido');
        var contenido = editor.html().trim();
        var tieneTexto = editor.text().trim() !== '' || editor.find('.indicacion-dato').length > 0;
        var archivo = archivoActual ? archivoActual.nombre : '';
        var faltan = [];
        if (!titulo) faltan.push('Título');
        if (!categoria) faltan.push('Categoría');
        if (esTexto && !tieneTexto) faltan.push(TEXTOS_SECCION[seccionActual].campo);
        if (!esTexto && !archivo) faltan.push('Archivo');
        if (faltan.length) {
            swal({ title: 'Faltan datos', text: faltan.join(', '), icon: 'error' });
            return;
        }
        var alcance = $('#indicacion_alcance').val() === 'personal' ? 'Personal' : 'Compartida';
        var fila = filaEnEdicion || crearFila(seccionActual).appendTo('#tabla_' + seccionActual + ' tbody');
        fila.attr({
            'data-titulo': titulo.toLowerCase(),
            'data-categoria': categoria,
            'data-tipo': esTexto ? 'texto' : 'archivo',
            'data-contenido': esTexto ? contenido : '',
            'data-archivo': esTexto ? '' : archivo,
            'data-archivo-url': esTexto ? '' : archivoActual.url,
            'data-archivo-tipo': esTexto ? '' : archivoActual.tipo,
            'data-logo': esTexto ? $('#editor_logo').attr('src') : ''
        });
        fila.find('td:eq(0)').text(titulo);
        fila.find('td:eq(1)').text(categoria);
        fila.find('td:eq(2)').text(alcance);

        recordarCategoria(categoria);
        $('#modal_indicacion').modal('hide');
        filtrarIndicaciones(seccionActual);
        swal({ icon: 'success', title: 'Éxito', text: TEXTOS_SECCION[seccionActual].guardado });
    }

    function eliminarIndicacion(boton) {
        var seccion = $(boton).closest('tr').attr('data-seccion');
        swal({
            title: TEXTOS_SECCION[seccion].eliminar,
            text: 'Favor confirme o cancele la solicitud',
            icon: 'warning',
            buttons: ['Cancelar', 'Eliminar'],
            dangerMode: true
        }).then(function (acepta) {
            if (!acepta) return;
            $(boton).closest('tr').remove();
            filtrarIndicaciones(seccion);
        });
    }
</script>
@endsection
