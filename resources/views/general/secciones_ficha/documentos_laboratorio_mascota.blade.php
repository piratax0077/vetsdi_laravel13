@if (!empty($mascota) && !empty($mascota->id))
<div class="card mb-3" id="lab-vet-bandeja"
    data-mascota="{{ $mascota->id }}"
    data-listar="{{ route('documentos.laboratorio.mascota.listar') }}"
    data-guardar="{{ route('documentos.laboratorio.mascota.guardar') }}"
    data-clasificar-base="{{ route('documentos.laboratorio.mascota.clasificar', ['documento' => '__ID__']) }}"
    data-ver-base="{{ route('documentos.laboratorio.mascota.ver', ['documento' => '__ID__']) }}">
    <div class="card-top d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-flask mr-1"></i> Resultados externos pendientes</h6>
        <span class="badge badge-warning" id="lab-vet-pendientes-total">0</span>
    </div>
    <div class="card-body pt-2">
        <div class="row">
            <div class="col-lg-5 mb-3 mb-lg-0">
                <label for="lab-vet-observacion-ficha" class="small text-muted mb-1">Observación del examen (opcional)</label>
                <textarea id="lab-vet-observacion-ficha" class="form-control form-control-sm mb-2" rows="2"
                    placeholder="Nombre del examen, laboratorio u observación"></textarea>
                <input id="lab-vet-archivos-ficha" type="file"
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.rtf,.odt,.ods,.jpg,.jpeg,.png,.gif,.webp,.bmp,.tif,.tiff,.zip,.rar,.7z"
                    multiple class="d-none">
                <div id="lab-vet-dropzone-ficha" class="lab-vet-dropzone-ficha" tabindex="0" role="button"
                    aria-controls="lab-vet-archivos-ficha">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <strong>Arrastre documentos aquí</strong>
                    <span>o haga clic para seleccionar (PDF, imágenes, Office, etc. · máx. 20 MB)</span>
                </div>
                <div id="lab-vet-cola-ficha" class="mt-2"></div>
            </div>
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Archivo</th>
                                <th>Observación</th>
                                <th>Clasificar como</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="lab-vet-pendientes">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Cargando resultados...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .lab-vet-dropzone-ficha {
        min-height: 160px;
        border: 2px dashed #17b8b5;
        border-radius: 12px;
        background: #f4ffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        color: #168a88;
        cursor: pointer;
        text-align: center;
        padding: 18px;
        transition: .2s;
    }

    .lab-vet-dropzone-ficha:hover,
    .lab-vet-dropzone-ficha.is-dragging {
        background: #e0fbfa;
        border-color: #087d7b;
    }

    .lab-vet-dropzone-ficha.is-disabled {
        opacity: .5;
        pointer-events: none;
    }

    .lab-vet-dropzone-ficha i {
        font-size: 42px;
        color: #17b8b5;
    }

    .lab-vet-archivo-ficha {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #eee;
        padding: 6px 0;
        gap: 10px;
        font-size: 12px;
    }
</style>

<script>
(function () {
    var bandeja = document.getElementById('lab-vet-bandeja');
    if (!bandeja || bandeja.dataset.iniciado === '1') {
        return;
    }
    bandeja.dataset.iniciado = '1';

    var mascotaId = bandeja.dataset.mascota;
    var listarUrl = bandeja.dataset.listar;
    var guardarUrl = bandeja.dataset.guardar;
    var clasificarPlantilla = bandeja.dataset.clasificarBase || '';
    var verDocumentoPlantilla = bandeja.dataset.verBase || '';
    var extensionesPermitidas = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'rtf', 'odt', 'ods',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff', 'zip', 'rar', '7z'
    ];
    var token = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
    var subidasActivas = 0;

    var dropzone = document.getElementById('lab-vet-dropzone-ficha');
    var inputArchivos = document.getElementById('lab-vet-archivos-ficha');
    var cola = document.getElementById('lab-vet-cola-ficha');
    var observacion = document.getElementById('lab-vet-observacion-ficha');

    function elementosVisor() {
        return {
            visor: document.getElementById('lab-vet-visor'),
            titulo: document.getElementById('lab-vet-visor-titulo'),
            contenido: document.getElementById('lab-vet-visor-contenido')
        };
    }

    var etiquetas = {
        sangre: 'Exámenes de sangre',
        radiologico: 'Exámenes radiológicos',
        especialidad: 'Exámenes de especialidad',
        otro: 'Otros exámenes'
    };

    function iconoArchivo(nombre) {
        var ext = String(nombre || '').split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff'].indexOf(ext) >= 0) {
            return 'fa-file-image text-primary';
        }
        if (['doc', 'docx', 'odt', 'rtf', 'txt'].indexOf(ext) >= 0) {
            return 'fa-file-alt text-secondary';
        }
        if (['xls', 'xlsx', 'ods', 'csv'].indexOf(ext) >= 0) {
            return 'fa-file-excel text-success';
        }
        if (['ppt', 'pptx'].indexOf(ext) >= 0) {
            return 'fa-file-powerpoint text-warning';
        }
        if (['zip', 'rar', '7z'].indexOf(ext) >= 0) {
            return 'fa-file-archive text-muted';
        }
        if (ext === 'pdf') {
            return 'fa-file-pdf text-danger';
        }
        return 'fa-file text-info';
    }

    function parsearRespuestaFetch(respuesta) {
        return respuesta.text().then(function (texto) {
            var data = null;
            if (typeof vetSdiParseAjaxData === 'function') {
                data = vetSdiParseAjaxData(texto);
            } else {
                try {
                    data = JSON.parse(String(texto || '').replace(/^\uFEFF+/, '').trim());
                } catch (e) {
                    data = null;
                }
            }

            if (!respuesta.ok) {
                throw new Error((data && (data.msj || data.message)) || ('HTTP ' + respuesta.status));
            }

            if (!data) {
                throw new Error('La respuesta del servidor no es válida.');
            }

            return data;
        });
    }

    function archivoPermitido(archivo) {
        var nombre = String(archivo.name || '').toLowerCase();
        var extension = nombre.indexOf('.') >= 0 ? nombre.split('.').pop() : '';
        return extensionesPermitidas.indexOf(extension) >= 0;
    }
    function esc(v) {
        var d = document.createElement('div');
        d.textContent = v || '';
        return d.innerHTML;
    }

    function escAttr(v) {
        return esc(v).replace(/`/g, '&#96;');
    }

    function extensionArchivo(nombre, url) {
        var origen = String(nombre || url || '');
        var partes = origen.split('?')[0].split('.');
        return partes.length > 1 ? partes.pop().toLowerCase() : '';
    }

    function esImagen(extension) {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tif', 'tiff'].indexOf(extension) >= 0;
    }

    function esVisualizableEnIframe(extension) {
        return ['pdf', 'txt', 'csv', 'htm', 'html'].indexOf(extension) >= 0 || esImagen(extension);
    }

    function normalizarUrlRelativa(url) {
        var valor = String(url || '').trim();
        if (!valor) {
            return '';
        }

        try {
            if (/^https?:\/\//i.test(valor)) {
                var parsed = new URL(valor, window.location.origin);
                if (parsed.origin !== window.location.origin) {
                    return parsed.pathname + parsed.search;
                }
                return parsed.pathname + parsed.search;
            }
        } catch (e) {
            return valor;
        }

        return valor.charAt(0) === '/' ? valor : '/' + valor;
    }

    function urlDocumento(doc) {
        if (doc && doc.url) {
            return normalizarUrlRelativa(doc.url);
        }
        if (doc && doc.id && verDocumentoPlantilla) {
            return normalizarUrlRelativa(
                verDocumentoPlantilla.replace('__ID__', encodeURIComponent(doc.id))
            );
        }
        return '';
    }

    function botonVerDocumento(doc) {
        var url = urlDocumento(doc);
        return '<button type="button" class="btn btn-xxs btn-info mr-1 lab-vet-ver-documento" data-id="' + escAttr(doc.id) +
            '" data-url="' + escAttr(url) + '" data-nombre="' + escAttr(doc.nombre) +
            '"><i class="fas fa-eye"></i> Ver documento</button>';
    }

    function cerrarVisorDocumento() {
        var nodes = elementosVisor();
        if (!nodes.visor || !nodes.contenido) {
            return;
        }
        nodes.visor.classList.add('d-none');
        nodes.contenido.innerHTML = '';
    }

    function verDocumentoLaboratorio(url, nombre, idDocumento) {
        if (!url && idDocumento && verDocumentoPlantilla) {
            url = verDocumentoPlantilla.replace('__ID__', encodeURIComponent(idDocumento));
        }
        url = normalizarUrlRelativa(url);

        var nodes = elementosVisor();
        if (!url) {
            aviso('Documento no disponible', 'No se encontró la URL del archivo.', 'warning');
            return;
        }
        if (!nodes.visor || !nodes.contenido || !nodes.titulo) {
            if (window.Fancybox && typeof window.Fancybox.show === 'function') {
                window.Fancybox.show([{
                    src: url,
                    type: (extensionArchivo(nombre, url) === 'pdf' ? 'iframe' : 'image'),
                    preload: false
                }]);
                return;
            }
            aviso('Documento no disponible', 'No se pudo abrir el visor en esta pantalla.', 'warning');
            return;
        }

        var extension = extensionArchivo(nombre, url);
        nodes.titulo.innerHTML = '<i class="fas fa-file-medical mr-1"></i> ' + esc(nombre || 'Documento');
        mostrarDocumentoEnVisor(url, nodes, nombre, extension);
    }

    function mostrarDocumentoEnVisor(url, nodes, nombre, extension) {
        nodes.contenido.innerHTML = '<div class="lab-vet-visor-placeholder"><i class="fas fa-spinner fa-spin"></i> Cargando documento...</div>';
        nodes.visor.classList.remove('d-none');

        if (esImagen(extension)) {
            var imagen = document.createElement('img');
            imagen.src = url;
            imagen.alt = nombre || 'Documento';
            imagen.onload = function () {
                nodes.contenido.innerHTML = '';
                nodes.contenido.appendChild(imagen);
                nodes.visor.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            };
            imagen.onerror = function () {
                nodes.contenido.innerHTML = '<div class="lab-vet-visor-placeholder text-danger">No fue posible mostrar la imagen. Vuelva a subir el archivo.</div>';
            };
            return;
        }

        var iframe = document.createElement('iframe');
        iframe.className = 'lab-vet-visor-iframe';
        iframe.title = nombre || 'Documento';
        iframe.src = url;
        iframe.onload = function () {
            nodes.visor.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };
        nodes.contenido.innerHTML = '';
        nodes.contenido.appendChild(iframe);
    }

    window.verDocumentoLaboratorioMascota = verDocumentoLaboratorio;
    window.cerrarVisorDocumentoLaboratorioMascota = cerrarVisorDocumento;

    document.addEventListener('click', function (e) {
        var btnVer = e.target.closest('.lab-vet-ver-documento');
        if (btnVer) {
            e.preventDefault();
            verDocumentoLaboratorio(btnVer.dataset.url || '', btnVer.dataset.nombre || '', btnVer.dataset.id || '');
            return;
        }

        var btnCerrar = e.target.closest('.lab-vet-visor-cerrar');
        if (btnCerrar) {
            e.preventDefault();
            cerrarVisorDocumento();
        }
    });

    function aviso(titulo, texto, icono) {
        if (window.swal) {
            swal(titulo, texto, icono || 'info');
        } else {
            alert(texto);
        }
    }

    function grupo(tipo, docs) {
        var el = document.getElementById('lab-vet-clasificados-' + tipo);
        if (!el) {
            return;
        }
        if (!docs.length) {
            el.innerHTML = '';
            return;
        }
        el.innerHTML = '<div class="card mb-3"><div class="card-top"><h6 class="mb-0"><i class="fas fa-folder-open mr-1"></i> ' +
            etiquetas[tipo] + ' del laboratorio</h6></div><div class="card-body pt-2"><div class="table-responsive">' +
            '<table class="table table-sm"><thead><tr><th>Fecha</th><th>Documento</th><th>Observación</th><th></th></tr></thead><tbody>' +
            docs.map(function (x) {
                return '<tr><td>' + esc(x.fecha) + '</td><td>' + esc(x.nombre) + '</td><td>' + esc(x.observacion || '-') +
                    '</td><td>' + botonVerDocumento(x) + '</td></tr>';
            }).join('') + '</tbody></table></div></div></div>';
    }

    function pintar(docs) {
        var pendientes = docs.filter(function (x) { return x.estado === 'pendiente'; });
        document.getElementById('lab-vet-pendientes-total').textContent = pendientes.length;
        document.getElementById('lab-vet-pendientes').innerHTML = pendientes.length
            ? pendientes.map(function (x) {
                return '<tr data-id="' + x.id + '"><td>' + esc(x.fecha) + '</td><td>' + esc(x.nombre) + '</td><td>' +
                    esc(x.observacion || '-') + '</td><td><select class="form-control form-control-sm lab-vet-tipo">' +
                    '<option value="">Seleccione</option><option value="sangre">Sangre / laboratorio</option>' +
                    '<option value="radiologico">Radiológico / imagenología</option><option value="especialidad">Especialidad</option>' +
                    '<option value="otro">Otro</option></select></td><td class="text-nowrap">' +
                    botonVerDocumento(x) +
                    '<button type="button" class="btn btn-xxs btn-success lab-vet-clasificar"><i class="fas fa-check"></i> Clasificar</button></td></tr>';
            }).join('')
            : '<tr><td colspan="5" class="text-center text-muted">No hay resultados pendientes.</td></tr>';

        ['sangre', 'radiologico', 'especialidad', 'otro'].forEach(function (tipo) {
            grupo(tipo, docs.filter(function (x) {
                return x.estado === 'clasificado' && x.tipo_examen === tipo;
            }));
        });
    }

    function cargar() {
        fetch(listarUrl + '?id_mascota=' + encodeURIComponent(mascotaId), {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(parsearRespuestaFetch).then(function (r) {
            pintar(r.documentos || []);
        }).catch(function () {
            document.getElementById('lab-vet-pendientes').innerHTML =
                '<tr><td colspan="5" class="text-center text-danger">No fue posible cargar los resultados.</td></tr>';
        });
    }

    function subir(archivos) {
        if (!archivos || !archivos.length) {
            return;
        }

        Array.from(archivos).forEach(function (archivo) {
            if (!archivoPermitido(archivo)) {
                aviso('Archivo no permitido', archivo.name + ' no tiene una extensión admitida.', 'warning');
                return;
            }
            if (archivo.size > 20 * 1024 * 1024) {
                aviso('Archivo demasiado grande', archivo.name + ' supera el máximo de 20 MB.', 'warning');
                return;
            }

            var idFila = 'archivo-ficha-' + Date.now() + '-' + Math.random().toString(16).slice(2);
            cola.insertAdjacentHTML('afterbegin',
                '<div id="' + idFila + '" class="lab-vet-archivo-ficha"><span><i class="fas ' + iconoArchivo(archivo.name) + ' mr-1"></i>' +
                esc(archivo.name) + '</span><span class="text-info"><i class="fas fa-spinner fa-spin"></i> Subiendo</span></div>');

            var form = new FormData();
            form.append('_token', token);
            form.append('id_mascota', mascotaId);
            form.append('observacion', observacion.value);
            form.append('archivo', archivo);

            subidasActivas++;
            dropzone.classList.add('is-disabled');

            fetch(guardarUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: form
            }).then(parsearRespuestaFetch).then(function (resp) {
                if (!resp.estado) {
                    throw new Error(resp.msj || 'No fue posible guardar el documento.');
                }
                var fila = document.getElementById(idFila);
                if (fila) {
                    fila.querySelector('span:last-child').className = 'text-success';
                    fila.querySelector('span:last-child').innerHTML = '<i class="feather icon-check"></i> Guardado';
                }
                cargar();
                aviso('Documento recibido', resp.msj || 'Documento subido correctamente.', 'success');
            }).catch(function (err) {
                var fila = document.getElementById(idFila);
                if (fila) {
                    fila.querySelector('span:last-child').className = 'text-danger';
                    fila.querySelector('span:last-child').textContent = err.message || 'Error al subir';
                }
                aviso('No fue posible subir el documento', err.message || 'Revise el archivo e intente nuevamente.', 'error');
            }).finally(function () {
                subidasActivas = Math.max(0, subidasActivas - 1);
                if (!subidasActivas) {
                    dropzone.classList.remove('is-disabled');
                }
            });
        });
    }

    bandeja.addEventListener('click', function (e) {
        var btn = e.target.closest('.lab-vet-clasificar');
        if (!btn) {
            return;
        }
        var fila = btn.closest('tr');
        var tipo = fila.querySelector('.lab-vet-tipo').value;
        if (!tipo) {
            aviso('Seleccione una categoría', '', 'warning');
            return;
        }
        btn.disabled = true;
        var urlClasificar = clasificarPlantilla.replace('__ID__', encodeURIComponent(fila.dataset.id));
        fetch(urlClasificar, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ tipo_examen: tipo })
        }).then(parsearRespuestaFetch).then(function (r) {
            if (!r.estado) {
                throw new Error(r.msj || 'No fue posible clasificar');
            }
            cargar();
            aviso('Examen clasificado', r.msj, 'success');
        }).catch(function (x) {
            btn.disabled = false;
            aviso('Error', x.message, 'error');
        });
    });

    dropzone.addEventListener('click', function () {
        inputArchivos.click();
    });
    dropzone.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            inputArchivos.click();
        }
    });
    dropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropzone.classList.add('is-dragging');
    });
    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('is-dragging');
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropzone.classList.remove('is-dragging');
        if (e.dataTransfer && e.dataTransfer.files) {
            subir(e.dataTransfer.files);
        }
    });
    inputArchivos.addEventListener('change', function () {
        subir(this.files);
        this.value = '';
    });

    cargar();
})();
</script>
@endif


