@php
    $edoProfesionalInicial = \App\Models\Profesional::where('id_usuario', \Illuminate\Support\Facades\Auth::id())->first();
    $edoNombreInicial = $edoProfesionalInicial
        ? trim($edoProfesionalInicial->nombre.' '.$edoProfesionalInicial->apellido_uno.' '.$edoProfesionalInicial->apellido_dos)
        : '';
    $edoRutInicial = $edoProfesionalInicial->rut ?? '';

    $edoIdLugarInicial = (int) (request('lugar_atencion_id') ?? data_get($lugar_atencion ?? null, 'id', 0));
    $edoLugarInicial = $edoIdLugarInicial > 0 ? \App\Models\LugarAtencion::find($edoIdLugarInicial) : null;
    $edoDireccionInicial = $edoLugarInicial ? $edoLugarInicial->Direccion()->first() : null;
    $edoComunaInicial = $edoDireccionInicial ? $edoDireccionInicial->Ciudad()->first() : null;
    $edoRegionInicial = $edoComunaInicial ? $edoComunaInicial->Region()->first() : null;
    $edoDireccionTextoInicial = $edoDireccionInicial
        ? trim(($edoDireccionInicial->direccion ?? '').' '.($edoDireccionInicial->numero_dir ?? ''))
        : ($edoLugarInicial->nombre ?? '');
@endphp

<style>
    #modal_enfermedades_denuncia_obligatoria .modal-dialog { height: calc(100vh - 1rem); margin-top: .5rem; margin-bottom: .5rem; }
    #modal_enfermedades_denuncia_obligatoria .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        max-height: 100%;
    }
    #modal_enfermedades_denuncia_obligatoria #edo_form_veterinario {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    #modal_enfermedades_denuncia_obligatoria .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding-bottom: 20px;
    }
    #modal_enfermedades_denuncia_obligatoria .modal-footer {
        position: relative;
        z-index: 3;
        flex: 0 0 auto;
        justify-content: flex-end;
        gap: 8px;
        background: #fff;
        border-top: 1px solid #dde4ea;
        box-shadow: 0 -5px 16px rgba(31, 41, 55, .08);
    }
    #modal_enfermedades_denuncia_obligatoria .modal-footer .btn { margin: 0; }
    #modal_enfermedades_denuncia_obligatoria .modal-header { background: linear-gradient(135deg, #a61b1b, #dc3545); color: #fff; border: 0; }
    #modal_enfermedades_denuncia_obligatoria .edo-section { border: 1px solid #dde4ea; border-radius: 10px; margin-bottom: 14px; overflow: hidden; }
    #modal_enfermedades_denuncia_obligatoria .edo-section-title { padding: 10px 14px; background: #f3f6f8; color: #344054; font-weight: 700; }
    #modal_enfermedades_denuncia_obligatoria .edo-section-body { padding: 14px; }
    #modal_enfermedades_denuncia_obligatoria .edo-firma-sdi { min-height: 175px; border: 1px solid #d0d5dd; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: center; }
    #edo_firma_qr svg { width: 125px; height: 125px; }
    @media (max-width: 767.98px) {
        #modal_enfermedades_denuncia_obligatoria .modal-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        #modal_enfermedades_denuncia_obligatoria .modal-footer .btn {
            width: 100%;
            white-space: normal;
        }
    }
</style>

<div id="modal_enfermedades_denuncia_obligatoria" class="modal fade" tabindex="-1" role="dialog"
    aria-labelledby="modal_edo_titulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="modal_edo_titulo">
                        <i class="feather icon-alert-triangle"></i> Enfermedades de Denuncia Obligatoria (EDO)
                    </h5>
                    <small>Registro veterinario de sospecha para notificación al SAG</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="edo_form_veterinario" novalidate>
                <div class="modal-body">
                    <div class="alert alert-warning py-2">
                        Este registro apoya la denuncia, pero no reemplaza su envío oficial al SAG.
                    </div>

                    <section class="edo-section">
                        <div class="edo-section-title">1. Datos del denunciante</div>
                        <div class="edo-section-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Nombre completo *</label>
                                    <input class="form-control" id="edo_nombre" value="{{ $edoNombreInicial }}" readonly required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>RUT *</label>
                                    <input class="form-control" id="edo_rut" value="{{ $edoRutInicial }}" placeholder="12345678-9" readonly required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Calidad *</label>
                                    <select class="form-control" id="edo_calidad" required>
                                        <option value="Médico Veterinario">Médico Veterinario</option>
                                        <option value="Propietario o tenedor">Propietario o tenedor</option>
                                        <option value="Otro denunciante">Otro denunciante</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="edo-section">
                        <div class="edo-section-title">2. Ubicación del foco</div>
                        <div class="edo-section-body">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Región *</label>
                                    <input class="form-control" id="edo_region" value="{{ $edoRegionInicial->nombre ?? '' }}" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Comuna *</label>
                                    <input class="form-control" id="edo_comuna" value="{{ $edoComunaInicial->nombre ?? '' }}" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Dirección, predio o establecimiento *</label>
                                    <input class="form-control" id="edo_direccion" value="{{ $edoDireccionTextoInicial }}" required>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="edo-section">
                        <div class="edo-section-title">3. Animal y detalles de la sospecha</div>
                        <div class="edo-section-body">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label>Identificación del animal</label>
                                    <input class="form-control" id="edo_animal">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Especie *</label>
                                    <select class="form-control" id="edo_especie" required>
                                        <option value="">Seleccione</option>
                                        <option>Canino</option><option>Felino</option><option>Bovino</option>
                                        <option>Ovino / Caprino</option><option>Porcino</option><option>Equino</option>
                                        <option>Ave</option><option>Abeja</option><option>Otra</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Enfermedad sospechada *</label>
                                    <select class="form-control" id="edo_enfermedad" required>
                                        <option value="">Seleccione</option>
                                        <option>Rabia</option><option>Leishmaniosis</option><option>Dirofilariosis</option>
                                        <option>Influenza aviar</option><option>Enfermedad de Newcastle</option>
                                        <option>Fiebre aftosa</option><option>Tuberculosis bovina</option>
                                        <option>Brucelosis bovina</option><option>Peste porcina africana</option>
                                        <option>Peste porcina clásica</option><option>Loque americana / europea</option>
                                        <option>Otra EDO</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Fecha de sospecha *</label>
                                    <input type="date" class="form-control" id="edo_fecha" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Animales enfermos *</label>
                                    <input type="number" min="0" class="form-control" id="edo_enfermos" value="1" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Animales muertos *</label>
                                    <input type="number" min="0" class="form-control" id="edo_muertos" value="0" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Adjuntar evidencia (imágenes o PDF)</label>
                                    <input type="file" class="form-control-file" id="edo_evidencia" multiple accept="image/*,.pdf">
                                </div>
                                <div class="col-12 form-group">
                                    <label>Signos clínicos y antecedentes epidemiológicos *</label>
                                    <textarea class="form-control" id="edo_sintomas" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="edo-section">
                        <div class="edo-section-title">4. Firma Digital Avanzada SDI</div>
                        <div class="edo-section-body">
                            <div class="edo-firma-sdi">
                                <div class="text-center" id="edo_firma_cargando">
                                    <i class="fa fa-spinner fa-spin text-info"></i> Generando firma del veterinario conectado...
                                </div>
                                <div class="text-center d-none" id="edo_firma_contenido">
                                    <div id="edo_firma_qr"></div>
                                    <strong>Firma Digital Avanzada SDI</strong>
                                    <div id="edo_firma_profesional"></div>
                                </div>
                            </div>
                            <input type="hidden" id="edo_firma_token">
                            <small class="text-danger d-none" id="edo_error_firma">No se pudo generar la firma digital del profesional.</small>
                        </div>
                    </section>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="edo_btn_guardar">
                        <i class="feather icon-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="edo_btn_pdf">
                        <i class="feather icon-file-text"></i> Ver PDF
                    </button>
                    <button type="button" class="btn btn-outline-info" id="edo_btn_email">
                        <i class="feather icon-mail"></i> Enviar por email
                    </button>
                    <button type="button" class="btn btn-danger" id="edo_btn_sag">
                        <i class="feather icon-send"></i> Enviar al SAG
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var edoBorradorKey = 'vet_sdi_edo_' + ({{ (int) ($id_ficha_atencion ?? 0) }} || 'nueva');

    function datosEdo() {
        return {
            nombre: $('#edo_nombre').val() || '',
            rut: $('#edo_rut').val() || '',
            calidad: $('#edo_calidad').val() || '',
            region: $('#edo_region').val() || '',
            comuna: $('#edo_comuna').val() || '',
            direccion: $('#edo_direccion').val() || '',
            animal: $('#edo_animal').val() || '',
            especie: $('#edo_especie').val() || '',
            enfermedad: $('#edo_enfermedad').val() || '',
            fecha: $('#edo_fecha').val() || '',
            enfermos: $('#edo_enfermos').val() || '0',
            muertos: $('#edo_muertos').val() || '0',
            sintomas: $('#edo_sintomas').val() || ''
        };
    }

    function validarEdo() {
        var formulario = document.getElementById('edo_form_veterinario');
        if (!formulario.checkValidity()) {
            formulario.classList.add('was-validated');
            var primerCampoInvalido = formulario.querySelector(':invalid');
            if (primerCampoInvalido) {
                primerCampoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
                primerCampoInvalido.focus({ preventScroll: true });
            }
            if (window.Swal) {
                Swal.fire('Datos incompletos', 'Complete los campos obligatorios del formulario EDO.', 'warning');
            } else {
                window.alert('Complete los campos obligatorios del formulario EDO antes de enviarlo.');
            }
            return false;
        }
        return true;
    }

    function cuerpoCorreoEdo(datos) {
        return [
            'ENFERMEDAD DE DENUNCIA OBLIGATORIA (EDO)',
            '',
            'Denunciante: ' + datos.nombre,
            'RUT: ' + datos.rut,
            'Calidad: ' + datos.calidad,
            'Ubicación: ' + datos.direccion + ', ' + datos.comuna + ', ' + datos.region,
            'Animal: ' + datos.animal,
            'Especie: ' + datos.especie,
            'Enfermedad sospechada: ' + datos.enfermedad,
            'Fecha de sospecha: ' + datos.fecha,
            'Animales enfermos: ' + datos.enfermos,
            'Animales muertos: ' + datos.muertos,
            '',
            'Signos clínicos y antecedentes epidemiológicos:',
            datos.sintomas,
            '',
            'Documento preparado mediante VET SDI.'
        ].join('\r\n');
    }

    function abrirDocumentoEdo(datos) {
        var qr = $('#edo_firma_qr').html() || '';
        var ventana = window.open('', '_blank');
        if (!ventana) {
            if (window.Swal) Swal.fire('Ventana bloqueada', 'Permita ventanas emergentes para visualizar el documento.', 'warning');
            return;
        }
        ventana.document.write(
            '<!doctype html><html><head><meta charset="utf-8"><title>Formulario EDO</title>' +
            '<style>body{font-family:Arial,sans-serif;color:#263238;margin:35px}h1{color:#b4232c;font-size:22px}' +
            '.bloque{border:1px solid #ccd5dd;border-radius:8px;padding:14px;margin:12px 0}.fila{margin:7px 0}' +
            '.firma{margin-top:30px;text-align:right}.firma svg{width:120px;height:120px}@media print{button{display:none}}</style></head><body>' +
            '<h1>Enfermedades de Denuncia Obligatoria (EDO)</h1>' +
            '<div class="bloque"><div class="fila"><b>Denunciante:</b> ' + $('<div>').text(datos.nombre).html() + '</div>' +
            '<div class="fila"><b>RUT:</b> ' + $('<div>').text(datos.rut).html() + ' · ' + $('<div>').text(datos.calidad).html() + '</div></div>' +
            '<div class="bloque"><div class="fila"><b>Ubicación:</b> ' + $('<div>').text(datos.direccion + ', ' + datos.comuna + ', ' + datos.region).html() + '</div></div>' +
            '<div class="bloque"><div class="fila"><b>Animal:</b> ' + $('<div>').text(datos.animal || 'Sin identificación').html() + '</div>' +
            '<div class="fila"><b>Especie:</b> ' + $('<div>').text(datos.especie).html() + '</div>' +
            '<div class="fila"><b>Sospecha:</b> ' + $('<div>').text(datos.enfermedad).html() + '</div>' +
            '<div class="fila"><b>Fecha:</b> ' + $('<div>').text(datos.fecha).html() + '</div>' +
            '<div class="fila"><b>Enfermos / muertos:</b> ' + $('<div>').text(datos.enfermos + ' / ' + datos.muertos).html() + '</div>' +
            '<div class="fila"><b>Antecedentes:</b><br>' + $('<div>').text(datos.sintomas).html() + '</div></div>' +
            '<div class="firma">' + qr + '<br><b>Firma Digital Avanzada SDI</b><br>' + $('<div>').text(datos.nombre).html() + '</div>' +
            '<p><button onclick="window.print()">Imprimir / Guardar como PDF</button></p></body></html>'
        );
        ventana.document.close();
    }

    function cargarFirmaSdi() {
        $('#edo_firma_token').val('');
        $('#edo_firma_contenido').addClass('d-none');
        $('#edo_error_firma').addClass('d-none');
        $('#edo_firma_cargando').removeClass('d-none');
        $.ajax({
            url: '{{ route('veterinaria.edo.firma_sdi') }}',
            type: 'GET',
            dataType: 'json',
            data: {
                id_ficha_atencion: $('#id_fc').val() || {{ (int) ($id_ficha_atencion ?? 0) }},
                id_lugar_atencion: {{ (int) (request('lugar_atencion_id') ?? ($lugar_atencion->id ?? 0)) }}
            }
        }).done(function (respuesta) {
            $('#edo_firma_qr').html(respuesta.qr || '');
            $('#edo_firma_profesional').text(respuesta.profesional + ' · RUT ' + respuesta.rut);
            $('#edo_firma_token').val(respuesta.token || '');
            $('#edo_nombre').val(respuesta.profesional).prop('readonly', true);
            $('#edo_rut').val(respuesta.rut).prop('readonly', true);
            $('#edo_region').val(respuesta.region);
            $('#edo_comuna').val(respuesta.comuna);
            $('#edo_direccion').val(respuesta.direccion || respuesta.establecimiento);
            if (respuesta.token && respuesta.qr) {
                $('#edo_firma_contenido').removeClass('d-none');
            } else {
                $('#edo_error_firma').text(
                    respuesta.mensaje || 'No se pudo generar la firma digital del profesional.'
                ).removeClass('d-none');
            }
        }).fail(function (xhr) {
            $('#edo_error_firma').text(
                (xhr.responseJSON && (xhr.responseJSON.mensaje || xhr.responseJSON.message))
                    || 'No se pudo generar la firma digital del profesional.'
            ).removeClass('d-none');
        }).always(function () {
            $('#edo_firma_cargando').addClass('d-none');
        });
    }
    $('#modal_enfermedades_denuncia_obligatoria').on('shown.bs.modal', function () {
        cargarFirmaSdi();
        var borrador = localStorage.getItem(edoBorradorKey);
        if (borrador) {
            try {
                var datos = JSON.parse(borrador);
                ['calidad', 'region', 'comuna', 'direccion', 'animal', 'especie', 'enfermedad', 'fecha', 'enfermos', 'muertos', 'sintomas']
                    .forEach(function (campo) {
                        if (datos[campo] !== undefined && datos[campo] !== '') $('#edo_' + campo).val(datos[campo]);
                    });
            } catch (e) {}
        }
    });

    $('#edo_btn_guardar').on('click', function () {
        localStorage.setItem(edoBorradorKey, JSON.stringify(datosEdo()));
        if (window.Swal) Swal.fire('Borrador guardado', 'El formulario EDO quedó guardado en este equipo.', 'success');
    });

    $('#edo_form_veterinario').on('submit', function (evento) {
        evento.preventDefault();
    });

    $('#edo_btn_pdf').on('click', function () {
        // La vista previa también debe funcionar mientras el formulario sea borrador.
        // La obligatoriedad se controla al enviar por correo o al SAG.
        abrirDocumentoEdo(datosEdo());
    });

    $('#edo_btn_email').on('click', function () {
        if (!validarEdo()) return;
        var datos = datosEdo();
        window.location.href = 'mailto:?subject=' + encodeURIComponent('Formulario veterinario EDO - ' + datos.enfermedad)
            + '&body=' + encodeURIComponent(cuerpoCorreoEdo(datos));
    });

    $('#edo_btn_sag').on('click', function () {
        if (!validarEdo()) return;
        var datos = datosEdo();
        localStorage.setItem(edoBorradorKey, JSON.stringify(datos));
        window.location.href = 'mailto:oficina.informaciones@sag.gob.cl?subject='
            + encodeURIComponent('Denuncia EDO veterinaria - ' + datos.enfermedad)
            + '&body=' + encodeURIComponent(cuerpoCorreoEdo(datos) + '\r\n\r\nAdjunte las evidencias y el PDF generado antes de enviar.');
    });
})();
</script>
