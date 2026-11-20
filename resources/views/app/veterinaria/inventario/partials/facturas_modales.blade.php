{{--
    Modales de facturas de compra: agregar, ver detalle y eliminar.
    El formulario envía estos campos: tipo_documento, folio, fecha_emision, proveedor, rut_proveedor,
    documento_referencia_id (solo notas 61 y 56), neto, exento, iva, total, observaciones y archivo_factura
    (XML, PDF, JPG o PNG, máximo 10 MB). El total, el signo de la nota de crédito y el RUT se revisan también en el servidor.

    Lectura del archivo:
    - El XML del SII se lee en el navegador y completa los campos (no necesita servidor).
    - Para PDF o imagen se usa la ruta vet.inventario.facturas.leer (POST, campo archivo_factura) si existe.
      Debe responder JSON con los campos que haya podido leer: tipo_documento, folio, fecha_emision (Y-m-d),
      proveedor, rut_proveedor, neto, exento, iva y folio_referencia. Lo que no venga se completa a mano.
--}}
@php
    $facturasReferencia = collect($facturas ?? [])->whereIn('tipo_documento', ['33', '34']);
    $tiposDocumentoModal = [
        '33' => 'Factura electrónica (33)',
        '34' => 'Factura exenta (34)',
        '61' => 'Nota de crédito (61)',
        '56' => 'Nota de débito (56)',
    ];
    $rutaGuardarFactura = Route::has('vet.inventario.facturas.guardar')
        ? route('vet.inventario.facturas.guardar', request()->only('id_institucion'))
        : '#';
    $reabrirFactura = $errors->any() && old('_formulario') === 'factura';
    $rutaLeerFactura = Route::has('vet.inventario.facturas.leer')
        ? route('vet.inventario.facturas.leer', request()->only('id_institucion'))
        : '';
@endphp

{{-- Agregar factura --}}
<div class="modal fade" id="modal_factura_compra" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_factura" aria-hidden="true" data-reabrir="{{ $reabrirFactura ? '1' : '0' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $rutaGuardarFactura }}" id="form_factura_compra" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="_formulario" value="factura">
                {{-- El archivo elegido en la zona de carga se copia aquí al guardar --}}
                <input type="file" name="archivo_factura" id="factura_archivo" accept=".xml,.pdf,.jpg,.jpeg,.png" hidden>
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="titulo_modal_factura">Agregar documento de compra</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 class="facturas-subtitulo">Archivo del documento</h6>
                    <div class="dropzone dropzone-atencion dropzone-factura mb-3" id="dropzone_factura" data-url-lectura="{{ $rutaLeerFactura }}">
                        <div class="dz-message" data-dz-message>
                            <i class="feather icon-upload-cloud dropzone-factura-icono" aria-hidden="true"></i>
                            <strong>Arrastra el documento aquí o haz clic para elegirlo</strong>
                            <small>XML del SII, PDF o imagen (JPG o PNG), máximo 10 MB. Con el XML los datos se completan solos.</small>
                        </div>
                    </div>
                    <p class="inventario-ayuda" id="factura_lectura" hidden></p>

                    <h6 class="facturas-subtitulo">Datos del documento</h6>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="floating-label-activo-sm" for="factura_tipo">Tipo de documento</label>
                            <select class="form-control form-control-sm @error('tipo_documento') is-invalid @enderror" id="factura_tipo" name="tipo_documento">
                                @foreach ($tiposDocumentoModal as $codigo => $texto)
                                    <option value="{{ $codigo }}" @selected(old('tipo_documento', '33') === $codigo)>{{ $texto }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="floating-label-activo-sm" for="factura_folio">Folio <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm @error('folio') is-invalid @enderror" id="factura_folio" name="folio" value="{{ old('folio') }}" inputmode="numeric" maxlength="10" autocomplete="off" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="floating-label-activo-sm" for="factura_fecha">Fecha de emisión <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm @error('fecha_emision') is-invalid @enderror" id="factura_fecha" name="fecha_emision" type="date" value="{{ old('fecha_emision', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="floating-label-activo-sm" for="factura_proveedor">Proveedor <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm @error('proveedor') is-invalid @enderror" id="factura_proveedor" name="proveedor" value="{{ old('proveedor') }}" maxlength="150" placeholder="Razón social" autocomplete="off" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="floating-label-activo-sm" for="factura_rut">RUT del proveedor <span class="text-danger">*</span></label>
                            <input class="form-control form-control-sm @error('rut_proveedor') is-invalid @enderror" id="factura_rut" name="rut_proveedor" value="{{ old('rut_proveedor') }}" maxlength="12" placeholder="76.123.456-7" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-row" id="grupo_factura_referencia" hidden>
                        <div class="form-group col-md-8">
                            <label class="floating-label-activo-sm" for="factura_referencia">Documento que modifica <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm @error('documento_referencia_id') is-invalid @enderror" id="factura_referencia" name="documento_referencia_id">
                                <option value="">Seleccione</option>
                                @foreach ($facturasReferencia as $referencia)
                                    <option value="{{ $referencia->id }}" data-folio="{{ $referencia->folio }}" @selected(old('documento_referencia_id') == $referencia->id)>
                                        {{ $referencia->tipo_documento === '34' ? 'Factura exenta' : 'Factura' }} N° {{ $referencia->folio }} · {{ $referencia->proveedor }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p class="inventario-ayuda" id="nota_factura_ajuste" hidden>
                        <i class="feather icon-info" aria-hidden="true"></i>
                        Ingresa los montos en positivo: la nota de crédito se guarda restando y la de débito sumando.
                    </p>

                    <h6 class="facturas-subtitulo">Montos</h6>
                    <div class="form-row">
                        <div class="form-group col-6 col-md-3">
                            <label class="floating-label-activo-sm" for="factura_neto">Neto</label>
                            <input class="form-control form-control-sm @error('neto') is-invalid @enderror" id="factura_neto" name="neto" type="number" min="0" step="1" value="{{ old('neto', 0) }}">
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label class="floating-label-activo-sm" for="factura_exento">Exento</label>
                            <input class="form-control form-control-sm @error('exento') is-invalid @enderror" id="factura_exento" name="exento" type="number" min="0" step="1" value="{{ old('exento', 0) }}">
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label class="floating-label-activo-sm" for="factura_iva">IVA 19%</label>
                            <input class="form-control form-control-sm @error('iva') is-invalid @enderror" id="factura_iva" name="iva" type="number" min="0" step="1" value="{{ old('iva', 0) }}">
                            <small class="form-text text-muted">Se calcula solo; puedes corregirlo.</small>
                        </div>
                        <div class="form-group col-6 col-md-3">
                            <label class="floating-label-activo-sm" for="factura_total_texto">Total</label>
                            <div class="facturas-total" id="factura_total_texto">$0</div>
                            <input type="hidden" name="total" id="factura_total" value="{{ old('total', 0) }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-12">
                            <label class="floating-label-activo-sm" for="factura_observaciones">Observaciones</label>
                            <input class="form-control form-control-sm @error('observaciones') is-invalid @enderror" id="factura_observaciones" name="observaciones" value="{{ old('observaciones') }}" maxlength="250" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Guardar documento</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Detalle de un documento: lo completa facturas_compra.js con los datos de la fila --}}
<div class="modal fade" id="modal_detalle_factura" tabindex="-1" role="dialog" aria-labelledby="titulo_detalle_factura" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="titulo_detalle_factura">Detalle del documento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="facturas-detalle">
                    <div><span>Proveedor</span><p data-campo="proveedor"></p></div>
                    <div><span>RUT del proveedor</span><p data-campo="rut"></p></div>
                    <div><span>Fecha de emisión</span><p data-campo="fecha"></p></div>
                    <div><span>Estado de pago</span><p data-campo="pago"></p></div>
                    <div><span>Neto</span><p data-campo="neto"></p></div>
                    <div><span>Exento</span><p data-campo="exento"></p></div>
                    <div><span>IVA 19%</span><p data-campo="iva"></p></div>
                    <div><span>Total</span><p><strong data-campo="total"></strong></p></div>
                    <div data-opcional="referencia"><span>Modifica al documento</span><p data-campo="referencia"></p></div>
                    <div data-opcional="url"><span>Archivo</span><p><a data-campo="url" href="#" target="_blank" rel="noopener">Abrir PDF/XML</a></p></div>
                    <div data-opcional="observaciones"><span>Observaciones</span><p data-campo="observaciones"></p></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cerrar</button>
                <form method="POST" action="#" id="form_pago_factura" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-info" id="boton_pago_factura">Marcar como pagada</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Confirmación para eliminar --}}
<div class="modal fade" id="modal_eliminar_factura" tabindex="-1" role="dialog" aria-labelledby="titulo_eliminar_factura" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="#" id="form_eliminar_factura">
                @csrf
                @method('DELETE')
                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title" id="titulo_eliminar_factura">Eliminar documento</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Se eliminará <strong id="eliminar_factura_descripcion"></strong>. Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
