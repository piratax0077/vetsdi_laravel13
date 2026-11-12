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
    #modal_enfermedades_denuncia_obligatoria .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
    #modal_enfermedades_denuncia_obligatoria .modal-header { background: linear-gradient(135deg, #a61b1b, #dc3545); color: #fff; border: 0; }
    #modal_enfermedades_denuncia_obligatoria .modal-body { background: #f7f8fb; padding: 20px 22px 8px; }
    #modal_enfermedades_denuncia_obligatoria .modal-footer { gap: 8px; }
    #modal_enfermedades_denuncia_obligatoria .modal-footer .btn { margin: 0; }
    #modal_enfermedades_denuncia_obligatoria .edo-aviso { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; padding: 10px 14px; color: #8a5a00; font-size: 13px; font-weight: 600; background: #fff7e6; border: 1px solid #f6dfa8; border-radius: 10px; }
    #modal_enfermedades_denuncia_obligatoria .edo-aviso i { flex-shrink: 0; font-size: 18px; }
    #modal_enfermedades_denuncia_obligatoria .edo-section { margin-bottom: 16px; background: #fff; border: 1px solid #e6eaef; border-radius: 12px; box-shadow: 0 2px 8px rgba(38, 59, 80, .05); }
    #modal_enfermedades_denuncia_obligatoria .edo-section-title { display: flex; align-items: center; gap: 10px; padding: 12px 16px; color: #272727; font-size: 15px; font-weight: 800; border-bottom: 1px solid #eef1f4; }
    #modal_enfermedades_denuncia_obligatoria .edo-section-numero { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; width: 26px; height: 26px; color: #fff; font-size: 13px; font-weight: 800; background: #168f86; border-radius: 8px; }
    #modal_enfermedades_denuncia_obligatoria .edo-section-body { padding: 22px 16px 6px; }
    #modal_enfermedades_denuncia_obligatoria .form-group { margin-bottom: 1.3rem; }
    #modal_enfermedades_denuncia_obligatoria input[type="file"].form-control { padding-top: 3px; }
    #modal_enfermedades_denuncia_obligatoria .edo-firma-sdi { min-height: 175px; margin-bottom: 16px; background: #fff; border: 1px dashed #cfd6de; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    #edo_firma_qr svg { width: 125px; height: 125px; }
    @media (max-width: 767.98px) {
        #modal_enfermedades_denuncia_obligatoria .modal-body { padding: 16px 14px 4px; }
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
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title mb-1" id="modal_edo_titulo">
                        <i class="feather icon-alert-triangle"></i> Enfermedades de Denuncia Obligatoria (EDO)
                    </h5>
                    <small>Registro veterinario de sospecha para notificación al SAG</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="edo_form_veterinario" novalidate>
                <div class="modal-body">
                    <div class="edo-aviso">
                        <i class="feather icon-info"></i>
                        <span>Este registro apoya la denuncia, pero no reemplaza su envío oficial al SAG.</span>
                    </div>

                    <section class="edo-section">
                        <div class="edo-section-title"><span class="edo-section-numero">1</span> Datos del denunciante</div>
                        <div class="edo-section-body">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Nombre completo *</label>
                                        <input class="form-control form-control-sm" id="edo_nombre" value="{{ $edoNombreInicial }}" readonly required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">RUT *</label>
                                        <input class="form-control form-control-sm" id="edo_rut" value="{{ $edoRutInicial }}" placeholder="12345678-9" readonly required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Calidad *</label>
                                        <select class="form-control form-control-sm" id="edo_calidad" required>
                                            <option value="Médico Veterinario">Médico Veterinario</option>
                                            <option value="Propietario o tenedor">Propietario o tenedor</option>
                                            <option value="Otro denunciante">Otro denunciante</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="edo-section">
                        <div class="edo-section-title"><span class="edo-section-numero">2</span> Ubicación del foco</div>
                        <div class="edo-section-body">
                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Región *</label>
                                        <input class="form-control form-control-sm" id="edo_region" value="{{ $edoRegionInicial->nombre ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Comuna *</label>
                                        <input class="form-control form-control-sm" id="edo_comuna" value="{{ $edoComunaInicial->nombre ?? '' }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Dirección, predio o establecimiento *</label>
                                        <input class="form-control form-control-sm" id="edo_direccion" value="{{ $edoDireccionTextoInicial }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="edo-section">
                        <div class="edo-section-title"><span class="edo-section-numero">3</span> Animal y detalles de la sospecha</div>
                        <div class="edo-section-body">
                            <div class="form-row">
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Identificación del animal</label>
                                        <input class="form-control form-control-sm" id="edo_animal">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Especie *</label>
                                        <select class="form-control form-control-sm" id="edo_especie" required>
                                            <option value="">Seleccione</option>
                                            <option>Canino</option><option>Felino</option><option>Bovino</option>
                                            <option>Ovino / Caprino</option><option>Porcino</option><option>Equino</option>
                                            <option>Ave</option><option>Abeja</option><option>Otra</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Enfermedad sospechada *</label>
                                        <select class="form-control form-control-sm" id="edo_enfermedad" required>
                                            <option value="">Seleccione</option>
                                            <option>Rabia</option><option>Leishmaniosis</option><option>Dirofilariosis</option>
                                            <option>Influenza aviar</option><option>Enfermedad de Newcastle</option>
                                            <option>Fiebre aftosa</option><option>Tuberculosis bovina</option>
                                            <option>Brucelosis bovina</option><option>Peste porcina africana</option>
                                            <option>Peste porcina clásica</option><option>Loque americana / europea</option>
                                            <option>Otra EDO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Fecha de sospecha *</label>
                                        <input type="date" class="form-control form-control-sm" id="edo_fecha" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Animales enfermos *</label>
                                        <input type="number" min="0" class="form-control form-control-sm" id="edo_enfermos" value="1" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Animales muertos *</label>
                                        <input type="number" min="0" class="form-control form-control-sm" id="edo_muertos" value="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Adjuntar evidencia (imágenes o PDF)</label>
                                        <input type="file" class="form-control form-control-sm" id="edo_evidencia" multiple accept="image/*,.pdf">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Signos clínicos y antecedentes epidemiológicos *</label>
                                        <textarea class="form-control caja-texto form-control-sm" id="edo_sintomas" required rows="1" onfocus="this.rows=2;" onblur="this.rows=1;"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="edo-section">
                        <div class="edo-section-title"><span class="edo-section-numero">4</span> Firma Digital Avanzada SDI</div>
                        <div class="edo-section-body">
                            <div class="form-row">
                                <div class="col-12">
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
                            </div>
                        </div>
                    </section>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');">Cancelar</button>
                    <button type="button" class="btn btn-info" id="edo_btn_guardar">
                        <i class="feather icon-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-outline-info" id="edo_btn_pdf">
                        <i class="feather icon-file-text"></i> Ver PDF
                    </button>
                    <button type="button" class="btn btn-outline-info" id="edo_btn_email">
                        <i class="feather icon-mail"></i> Enviar por email
                    </button>
                    <button type="button" class="btn btn-success" id="edo_btn_sag">
                        <i class="feather icon-send"></i> Enviar al SAG
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal_edo_pdf" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_edo_pdf_titulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="modal_edo_pdf_titulo">Notificación al SAG · Vista previa PDF</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <iframe id="edo_pdf_marco" class="w-100 border-0 d-block" style="height: 68vh;" title="PDF de notificación al SAG"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-arrow-left"></i> Volver</button>
                <a id="edo_pdf_descargar" class="btn btn-info" download="notificacion-sag.pdf"><i class="feather icon-download"></i> Descargar PDF</a>
            </div>
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

    const $formularioEdo = $('#modal_enfermedades_denuncia_obligatoria');
    const $visorEdo = $('#modal_edo_pdf').appendTo('body');
    let urlPdfEdo;
    let volviendoDelPdf = false;

    $visorEdo.on('hidden.bs.modal', function () {
        $('#edo_pdf_marco').removeAttr('src');
        $('#edo_pdf_descargar').removeAttr('href');
        URL.revokeObjectURL(urlPdfEdo);
        volviendoDelPdf = true;
        $formularioEdo.modal('show');
    });

    async function abrirDocumentoEdo(datos) {
        const boton = $('#edo_btn_pdf').prop('disabled', true);
        try {
            const respuesta = await fetch(@json(route('veterinaria.edo.pdf', [], false)), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token()),
                },
                body: JSON.stringify({
                    ...datos,
                    id_ficha_atencion: Number($('#id_fc').val()) || {{ (int) ($hora_medica->id_ficha_atencion ?? $id_ficha_atencion ?? 0) }},
                    id_lugar_atencion: {{ (int) (request('lugar_atencion_id') ?? ($lugar_atencion->id ?? 0)) }},
                }),
            });

            if (!respuesta.ok || !respuesta.headers.get('Content-Type')?.includes('application/pdf')) {
                throw new Error('No se pudo generar el PDF de la notificación.');
            }
            if (!$formularioEdo.hasClass('show')) return;

            const desplazamiento = $formularioEdo.scrollTop();
            urlPdfEdo = URL.createObjectURL(await respuesta.blob());
            if (!$formularioEdo.hasClass('show')) {
                URL.revokeObjectURL(urlPdfEdo);
                return;
            }
            $formularioEdo.one('hidden.bs.modal', function () {
                $('#edo_pdf_marco').attr('src', urlPdfEdo);
                $('#edo_pdf_descargar').attr('href', urlPdfEdo);
                $formularioEdo.one('shown.bs.modal', function () {
                    $formularioEdo.scrollTop(desplazamiento);
                    boton.trigger('focus');
                });
                $visorEdo.modal('show');
            }).modal('hide');
        } catch (error) {
            if (window.Swal) Swal.fire('PDF no disponible', 'No se pudo generar el PDF de la notificación.', 'error');
            else window.alert('No se pudo generar el PDF de la notificación.');
        } finally {
            boton.prop('disabled', false);
        }
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
            timeout: 15000,
            data: {
                id_ficha_atencion: Number($('#id_fc').val()) || {{ (int) ($hora_medica->id_ficha_atencion ?? $id_ficha_atencion ?? 0) }},
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
        if (volviendoDelPdf) {
            volviendoDelPdf = false;
            return;
        }
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
