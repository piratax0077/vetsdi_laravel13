{{--
    Facturas de compra del inventario (pantalla lista; falta el guardado).

    Lo que espera esta vista desde el controlador:
    - $facturas: colección de documentos, cada uno con id, tipo_documento ('33', '34', '61' o '56'), folio,
      fecha_emision (Y-m-d), proveedor, rut_proveedor, neto, exento, iva, total, estado_pago
      ('Pendiente', 'Pagada' o 'Aplicada'), documento_referencia (texto del documento que modifica una nota),
      url_respaldo y observaciones.
    - $resumenFacturas (opcional): documentos, pendientes, por_pagar y compras_mes. Si no viene, se calcula aquí.

    Rutas con las que se conectan los botones (mientras no existan, los formularios no envían nada):
    - vet.inventario.facturas.guardar   (POST)
    - vet.inventario.facturas.pago      (POST, {factura}) cambia entre Pendiente y Pagada
    - vet.inventario.facturas.eliminar  (DELETE, {factura})
    - vet.inventario.facturas.exportar  (GET) descarga facturas.csv
--}}
@php
    $facturas = collect($facturas ?? []);
    $tiposDocumento = [
        '33' => 'Factura electrónica (33)',
        '34' => 'Factura exenta (34)',
        '61' => 'Nota de crédito (61)',
        '56' => 'Nota de débito (56)',
    ];
    $coloresPago = ['Pendiente' => 'warning', 'Pagada' => 'success', 'Aplicada' => 'secondary'];
    $pesos = fn ($monto) => ($monto < 0 ? '-' : '').'$'.number_format(abs((float) $monto), 0, ',', '.');
    $contexto = request()->only('id_institucion');
    $mesActual = now()->format('Y-m');
    $resumen = ($resumenFacturas ?? null) ?: [
        'documentos' => $facturas->count(),
        'pendientes' => $facturas->where('estado_pago', 'Pendiente')->count(),
        'por_pagar' => $facturas->where('estado_pago', 'Pendiente')->sum('total'),
        'compras_mes' => $facturas->filter(fn ($f) => str_starts_with((string) $f->fecha_emision, $mesActual))->sum('total'),
    ];
@endphp

<div class="row">
    @foreach ([
        ['morado', 'icon-file-text', $resumen['documentos'], 'Documentos registrados'],
        ['naranja', 'icon-clock', $resumen['pendientes'], 'Pendientes de pago'],
        ['rojo', 'icon-credit-card', $pesos($resumen['por_pagar']), 'Monto por pagar'],
        ['azul', 'icon-shopping-cart', $pesos($resumen['compras_mes']), 'Compras del mes'],
    ] as $indicador)
        <div class="col-6 col-lg-3 mb-4">
            <div class="tarjeta-indicador tarjeta-indicador--{{ $indicador[0] }}">
                <strong class="tarjeta-indicador-valor">{{ $indicador[2] }}</strong>
                <span class="tarjeta-indicador-etiqueta">{{ $indicador[3] }}</span>
                <span class="tarjeta-indicador-icono"><i class="feather {{ $indicador[1] }}" aria-hidden="true"></i></span>
            </div>
        </div>
    @endforeach
</div>

<div class="card facturas-compra">
    <div class="card-header encabezado-card">
        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-file-text"></i></span>Documentos de compras</h5>
        <div class="encabezado-card-acciones">
            @if (Route::has('vet.inventario.facturas.exportar'))
                <a class="btn btn-info btn-sm" href="{{ route('vet.inventario.facturas.exportar', $contexto) }}">
                    <i class="feather icon-download mr-1" aria-hidden="true"></i>Descargar documentos CSV
                </a>
            @endif
            <button type="button" class="btn btn-info btn-sm" id="agregar_factura" data-toggle="modal" data-target="#modal_factura_compra">Agregar documento</button>
        </div>
    </div>
    <div class="card-body">
        {{-- Filtros: buscan sobre las filas ya cargadas, sin recargar la página --}}
        <div class="form-row facturas-filtros">
            <div class="form-group col-md-6">
                <label class="floating-label-activo-sm" for="facturas_buscar">Buscar</label>
                <input type="search" class="form-control form-control-sm" id="facturas_buscar" placeholder="Folio, proveedor o RUT" autocomplete="off">
            </div>
            <div class="form-group col-md-3">
                <label class="floating-label-activo-sm" for="facturas_filtro_tipo">Tipo de documento</label>
                <select class="form-control form-control-sm" id="facturas_filtro_tipo">
                    <option value="">Todos</option>
                    @foreach ($tiposDocumento as $codigo => $texto)
                        <option value="{{ $codigo }}">{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label class="floating-label-activo-sm" for="facturas_filtro_pago">Estado de pago</label>
                <select class="form-control form-control-sm" id="facturas_filtro_pago">
                    <option value="">Todos</option>
                    @foreach (array_keys($coloresPago) as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="tabla_facturas">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha de emisión</th>
                        <th>Proveedor</th>
                        <th class="text-right">Neto</th>
                        <th class="text-right">Exento</th>
                        <th class="text-right">IVA 19%</th>
                        <th class="text-right">Total</th>
                        <th>Pago</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facturas as $factura)
                        @php
                            $fechaEmision = \Illuminate\Support\Carbon::parse($factura->fecha_emision)->format('d-m-Y');
                            $tipoTexto = $tiposDocumento[$factura->tipo_documento] ?? $factura->tipo_documento;
                        @endphp
                        <tr class="fila-factura"
                            data-tipo="{{ $factura->tipo_documento }}"
                            data-pago="{{ $factura->estado_pago }}"
                            data-busqueda="{{ \Illuminate\Support\Str::lower($factura->folio.' '.$factura->proveedor.' '.str_replace(['.', ' '], '', $factura->rut_proveedor)) }}">
                            <td><strong class="d-block">N° {{ $factura->folio }}</strong><small class="text-muted">{{ $tipoTexto }}</small></td>
                            <td>{{ $fechaEmision }}</td>
                            <td>{{ $factura->proveedor }}<small class="d-block text-muted">RUT {{ $factura->rut_proveedor }}</small></td>
                            <td class="text-right">{{ $pesos($factura->neto) }}</td>
                            <td class="text-right">{{ $pesos($factura->exento) }}</td>
                            <td class="text-right">{{ $pesos($factura->iva) }}</td>
                            <td class="text-right"><strong>{{ $pesos($factura->total) }}</strong></td>
                            <td><span class="badge badge-light-{{ $coloresPago[$factura->estado_pago] ?? 'secondary' }}">{{ $factura->estado_pago }}</span></td>
                            <td class="text-right text-nowrap">
                                <button type="button" class="btn btn-info btn-sm ver-factura"
                                    data-titulo="{{ $tipoTexto }} N° {{ $factura->folio }}"
                                    data-proveedor="{{ $factura->proveedor }}"
                                    data-rut="{{ $factura->rut_proveedor }}"
                                    data-fecha="{{ $fechaEmision }}"
                                    data-pago="{{ $factura->estado_pago }}"
                                    data-neto="{{ $pesos($factura->neto) }}"
                                    data-exento="{{ $pesos($factura->exento) }}"
                                    data-iva="{{ $pesos($factura->iva) }}"
                                    data-total="{{ $pesos($factura->total) }}"
                                    data-referencia="{{ $factura->documento_referencia ?? '' }}"
                                    data-url="{{ $factura->url_respaldo ?? '' }}"
                                    data-observaciones="{{ $factura->observaciones ?? '' }}"
                                    data-cambia-pago="{{ $factura->tipo_documento === '61' ? '' : (Route::has('vet.inventario.facturas.pago') ? route('vet.inventario.facturas.pago', ['factura' => $factura->id] + $contexto) : '#') }}">Ver</button>
                                <button type="button" class="btn btn-danger btn-icon eliminar-factura" title="Eliminar" aria-label="Eliminar"
                                    data-descripcion="{{ $tipoTexto }} N° {{ $factura->folio }} de {{ $factura->proveedor }}"
                                    data-url="{{ Route::has('vet.inventario.facturas.eliminar') ? route('vet.inventario.facturas.eliminar', ['factura' => $factura->id] + $contexto) : '#' }}">
                                    <i class="feather icon-x" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="facturas-vacio"><td colspan="9">Aún no hay documentos registrados.</td></tr>
                    @endforelse
                    <tr class="facturas-sin-resultados" hidden><td colspan="9">No hay documentos con esos filtros.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
