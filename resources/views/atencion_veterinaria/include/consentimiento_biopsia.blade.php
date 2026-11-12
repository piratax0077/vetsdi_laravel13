@php
    $esPersonalizado = $consentimientoPersonalizado ?? false;
    $esIndicacion = $indicacionPersonalizada ?? false;
    [$modalConsentimiento, $rutaConsentimiento, $tituloModal] = match (true) {
        $esIndicacion => ['modal_indicacion_personalizada_vet', 'profesional.plantillas.indicacion_personalizada', 'Indicaciones personalizadas'],
        $esPersonalizado => ['modal_consentimiento_personalizado_vet', 'profesional.plantillas.consentimiento_personalizado', 'Consentimiento informado personalizado'],
        default => ['modal_consentimiento_biopsia', 'profesional.plantillas.biopsia', 'Consentimiento informado para biopsia'],
    };
@endphp
@once($modalConsentimiento)
<div class="modal fade" id="{{ $modalConsentimiento }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalConsentimiento }}_titulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <form class="modal-content" action="{{ route($rutaConsentimiento) }}" method="post">
            @csrf
            <input type="hidden" name="id_hora_medica" value="{{ $hora_medica->id }}">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="{{ $modalConsentimiento }}_titulo">{{ $tituloModal }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    @if($esIndicacion)
                        Revise el PDF antes de enviarlo. Quedará disponible en Mis documentos del tutor y en los documentos de la mascota.
                    @else
                        Complete los datos del procedimiento. El documento quedará disponible en Mis documentos del tutor para su firma.
                    @endif
                </div>
                <div class="mb-3">
                    <div><strong>Veterinario:</strong> {{ $profesional->nombreCompleto() }}</div>
                    <div><strong>Mascota:</strong> {{ $mascota->nombre }} · {{ $mascota->especieMascota?->nombre ?? $mascota->otra_especie }}</div>
                    <div><strong>Ficha clínica:</strong> {{ $hora_medica->id_ficha_atencion }}</div>
                    @unless($esPersonalizado || $esIndicacion)
                        <div><strong>Peso:</strong> <span data-biopsia-campo="peso">Sin registro</span></div>
                    @endunless
                </div>
                @if($esPersonalizado || $esIndicacion)
                    <div class="form-group">
                        <label class="floating-label-activo-sm" for="{{ $modalConsentimiento }}_campo_titulo">{{ $esIndicacion ? 'Título de las indicaciones' : 'Título del consentimiento' }}</label>
                        <input class="form-control form-control-sm" id="{{ $modalConsentimiento }}_campo_titulo" name="titulo" maxlength="180" required>
                    </div>
                @endif
                <div class="alert alert-secondary">
                    @if($esIndicacion)
                        <h5 class="text-break" data-biopsia-campo="titulo" data-texto-inicial="{{ $tituloModal }}">{{ $tituloModal }}</h5>
                        <p class="text-break mb-0" style="white-space: pre-wrap" data-biopsia-campo="indicaciones"></p>
                    @elseif($esPersonalizado)
                        <h5 class="text-break" data-biopsia-campo="titulo" data-texto-inicial="Consentimiento informado personalizado">Consentimiento informado personalizado</h5>
                        @include('PDF.contenido_consentimiento_personalizado_vet', ['vistaPrevia' => true, 'datos' => []])
                    @else
                        <h5>CONSENTIMIENTO INFORMADO VETERINARIO PARA BIOPSIA</h5>
                        @include('PDF.contenido_consentimiento_biopsia', ['vistaPrevia' => true, 'datos' => []])
                    @endif
                </div>
                <div class="form-row">
                    @if($esIndicacion)
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="indicacion-personalizada-contenido">Indicaciones para el tutor</label>
                            <textarea class="form-control form-control-sm caja-texto" id="indicacion-personalizada-contenido" name="indicaciones" rows="6" maxlength="10000" required></textarea>
                        </div>
                    @elseif($esPersonalizado)
                        @foreach(config('consentimiento_veterinario_personalizado') as $campo => $detalle)
                            <div class="form-group col-12">
                                <label class="floating-label-activo-sm" for="consentimiento-personalizado-{{ $campo }}">{{ $detalle['etiqueta'] }}</label>
                                <textarea class="form-control form-control-sm caja-texto" id="consentimiento-personalizado-{{ $campo }}" name="{{ $campo }}" rows="2" maxlength="5000" @required($detalle['requerido'])></textarea>
                            </div>
                        @endforeach
                    @else
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="biopsia-localizacion">Zona anatómica a muestrear</label>
                            <input class="form-control form-control-sm" id="biopsia-localizacion" name="localizacion" maxlength="500" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="biopsia-tecnica">Tipo de biopsia propuesta</label>
                            <select class="form-control form-control-sm" id="biopsia-tecnica" name="tecnica" required>
                                <option value="">Seleccione</option>
                                @foreach(['Incisional' => 'Incisional (parcial)', 'Excisional' => 'Escisional (extirpación completa)', 'Tru-cut' => 'Punción / Tru-Cut', 'Endoscópica' => 'Endoscópica', 'Médula ósea' => 'Médula ósea', 'Otra' => 'Otra'] as $tecnica => $etiqueta)
                                    <option value="{{ $tecnica }}">{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-12 d-none" id="biopsia-otra-tecnica-grupo">
                            <label class="floating-label-activo-sm" for="biopsia-otra-tecnica">Especifique la técnica</label>
                            <input class="form-control form-control-sm" id="biopsia-otra-tecnica" name="otra_tecnica" maxlength="255" disabled>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="biopsia-peso">Peso (kg)</label>
                            <input class="form-control form-control-sm" id="biopsia-peso" name="peso" type="number" step="any" min="0.001" value="{{ str_replace(',', '.', (string) data_get($valoresDocumento ?? [], 'peso')) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="biopsia-registro">Número de registro profesional</label>
                            <input class="form-control form-control-sm" id="biopsia-registro" name="registro_profesional" maxlength="100">
                        </div>
                        <fieldset class="form-group col-12">
                            <legend class="f-14 mb-2">Protocolo de inmovilización</legend>
                            @foreach(['Anestesia local', 'Sedación', 'Anestesia general'] as $opcion)
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input class="custom-control-input" type="checkbox" id="biopsia-inmovilizacion-{{ $loop->index }}" name="inmovilizacion[]" value="{{ $opcion }}">
                                    <label class="custom-control-label" for="biopsia-inmovilizacion-{{ $loop->index }}">{{ $opcion }}</label>
                                </div>
                            @endforeach
                        </fieldset>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="biopsia-laboratorio">Laboratorio de destino</label>
                            <input class="form-control form-control-sm" id="biopsia-laboratorio" name="laboratorio" maxlength="255" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="floating-label-activo-sm" for="biopsia-presupuesto">Presupuesto estimado ($ CLP)</label>
                            <input class="form-control form-control-sm" id="biopsia-presupuesto" name="presupuesto" type="number" min="0" max="999999999999" step="1" required>
                        </div>
                        @foreach(['descripcion' => 'Descripción del procedimiento', 'riesgos' => 'Riesgos adicionales informados (opcional)', 'alternativas' => 'Alternativas adicionales informadas (opcional)'] as $campo => $etiqueta)
                            <div class="form-group col-12">
                                <label class="floating-label-activo-sm" for="biopsia-{{ $campo }}">{{ $etiqueta }}</label>
                                <textarea class="form-control form-control-sm caja-texto" id="biopsia-{{ $campo }}" name="{{ $campo }}" rows="2" maxlength="5000" @required($campo === 'descripcion')></textarea>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="alert alert-danger d-none" data-consentimiento-error role="alert"></div>
                <div class="alert alert-success d-none" data-consentimiento-confirmacion role="status">
                    <span></span> <a target="_blank" rel="noopener">{{ $esIndicacion ? 'Ver indicaciones' : 'Ver consentimiento' }}</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-outline-info btn-sm" data-consentimiento-ver-pdf>Ver PDF</button>
                <button type="submit" class="btn btn-info btn-sm">{{ $esIndicacion ? 'Enviar indicaciones al tutor' : 'Generar y solicitar firma al tutor' }}</button>
            </div>
        </form>
    </div>
</div>
<script>
$(function () {
    const modal = $(document.getElementById(@json($modalConsentimiento))).appendTo('body');
    const form = modal.find('form');
    const error = form.find('[data-consentimiento-error]');
    const confirmacion = form.find('[data-consentimiento-confirmacion]');
    const verPdf = form.find('[data-consentimiento-ver-pdf]');
    const tipoBiopsia = form.find('#biopsia-tecnica');
    const acciones = form.find('[type="submit"]').add(verPdf);
    const formatoPresupuesto = new Intl.NumberFormat('es-CL');
    let comprobanteVistaPrevia = null;

    function actualizarTexto() {
        const datos = new FormData(form.get(0));
        const tecnica = tipoBiopsia.find(':selected').text();
        const valores = Object.fromEntries(datos);
        valores.tecnica = datos.get('tecnica') === 'Otra'
            ? ['Otra', datos.get('otra_tecnica')?.trim()].filter(Boolean).join(': ')
            : (datos.get('tecnica') ? tecnica : '');
        valores.inmovilizacion = datos.getAll('inmovilizacion[]').join(', ');
        valores.presupuesto = valores.presupuesto ? formatoPresupuesto.format(Number(valores.presupuesto)) : '';
        valores.peso = valores.peso ? valores.peso + ' kg' : 'Sin registro';

        form.find('[data-biopsia-campo]').each(function () {
            const valor = valores[this.dataset.biopsiaCampo]?.trim() || '';
            const opcional = this.hasAttribute('data-biopsia-opcional');
            this.textContent = valor || (opcional ? '' : (this.dataset.textoInicial || '________'));
            if (opcional) $(this).closest('p').toggleClass('d-none', !valor);
        });
    }

    tipoBiopsia.on('change', function () {
        const otra = this.value === 'Otra';
        form.find('#biopsia-otra-tecnica-grupo').toggleClass('d-none', !otra);
        form.find('#biopsia-otra-tecnica').prop({disabled: !otra, required: otra});
    });
    form.on('input change', 'input, select, textarea', actualizarTexto);
    actualizarTexto();

    async function solicitarDocumento(vistaPrevia) {
        if (acciones.prop('disabled') || !form.get(0).reportValidity()) return;
        acciones.prop('disabled', true);
        error.addClass('d-none');
        const mensajeError = vistaPrevia
            ? 'No se pudo abrir la vista previa del PDF. Inténtelo nuevamente.'
            : 'No se pudo emitir el documento. Inténtelo nuevamente.';
        const mostrarError = mensaje => error.removeClass('d-none').text(mensaje).get(0).scrollIntoView({block: 'nearest'});
        let pestaña;
        let emitido = false;
        try {
            const formulario = form.get(0);
            const cuerpo = new FormData(formulario);
            if (comprobanteVistaPrevia) cuerpo.set('comprobante_vista_previa', comprobanteVistaPrevia);
            if (vistaPrevia) {
                pestaña = window.open('', '_blank');
                if (!pestaña) {
                    mostrarError('Permita las ventanas emergentes para abrir la vista previa del PDF.');
                    return;
                }
                pestaña.opener = null;
                cuerpo.set('vista_previa', '1');
            }
            const respuesta = await fetch(formulario.action, {method: 'POST', body: cuerpo, headers: {'Accept': 'application/json'}});
            if (vistaPrevia && respuesta.ok && respuesta.headers.get('Content-Type')?.startsWith('application/pdf')) {
                comprobanteVistaPrevia = respuesta.headers.get('X-Vista-Previa-Biopsia');
                if (!pestaña.closed) {
                    const url = URL.createObjectURL(await respuesta.blob());
                    pestaña.location.replace(url);
                    setTimeout(() => URL.revokeObjectURL(url), 300000);
                }
                return;
            }
            const datos = await respuesta.json().catch(() => ({}));
            if (vistaPrevia || !respuesta.ok || datos.estado !== 1) {
                pestaña?.close();
                const detalle = respuesta.status < 500 && (Object.values(datos.errors || {}).flat().join(' ') || datos.msj);
                mostrarError(detalle || mensajeError);
                return;
            }
            emitido = true;
            confirmacion.removeClass('d-none').find('span').text(datos.msj);
            confirmacion.find('a').attr('href', datos.url);
        } catch {
            pestaña?.close();
            mostrarError(mensajeError);
        } finally {
            if (!emitido) acciones.prop('disabled', false);
        }
    }

    verPdf.on('click', () => solicitarDocumento(true));
    form.on('submit', function (evento) {
        evento.preventDefault();
        solicitarDocumento(false);
    });
    modal.on('hidden.bs.modal', function () {
        if (!confirmacion.hasClass('d-none')) {
            comprobanteVistaPrevia = null;
            form.get(0).reset();
            tipoBiopsia.trigger('change');
            actualizarTexto();
            confirmacion.addClass('d-none');
            acciones.prop('disabled', false);
        }
    });
});
</script>
@endonce
