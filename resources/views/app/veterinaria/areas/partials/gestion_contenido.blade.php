{{-- Gestión del área: responsable, registro de gastos y listado.
     La usan la página de gestión de cada área y la pestaña "Gestión del área" de Hospitalización ($enArea). --}}
@php
    $enArea = $enArea ?? false;
    $totalInsumos = (int) $totales->get('insumos',0);
    $totalOtros = (int) $totales->get('otros',0);
    $fechaGasto = fn ($fecha) => $fecha ? \Carbon\Carbon::parse($fecha)->format('d-m-Y') : '';
@endphp
<div class="card">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Responsable del servicio</h5></div>
    <div class="card-body">
        <form method="post" action="{{ route('vet.areas.gestion.guardar',['area'=>$area]) }}">
            @csrf
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="accion" value="responsable">
            @if($enArea)<input type="hidden" name="_volver" value="area"><input type="hidden" name="_formulario" value="gestion-responsable">@endif
            <div class="form-row">
                <div class="col-md-6"><div class="form-group"><label class="floating-label-activo-sm" for="responsable-nombre">Nombre del responsable</label><input id="responsable-nombre" name="nombre" class="form-control form-control-sm" maxlength="150" value="{{ old('accion') === 'responsable' ? old('nombre') : ($responsable->nombre ?? '') }}" required></div></div>
                <div class="col-md-6"><div class="form-group"><label class="floating-label-activo-sm" for="responsable-contacto">Contacto (opcional)</label><input id="responsable-contacto" name="contacto" class="form-control form-control-sm" maxlength="150" value="{{ old('accion') === 'responsable' ? old('contacto') : ($responsable->contacto ?? '') }}"></div></div>
            </div>
            <button class="btn btn-info btn-sm" type="submit"><i class="feather icon-save mr-1"></i>Guardar responsable</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Registrar compra de insumos u otro gasto</h5></div>
    <div class="card-body">
        <form method="post" action="{{ route('vet.areas.gestion.guardar',['area'=>$area]) }}">
            @csrf
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="accion" value="gasto">
            @if($enArea)<input type="hidden" name="_volver" value="area"><input type="hidden" name="_formulario" value="gestion-gasto">@endif
            <div class="form-row">
                <div class="col-md-4"><div class="form-group"><label class="floating-label-activo-sm" for="gasto-tipo">Tipo</label><select id="gasto-tipo" name="tipo" class="form-control form-control-sm" required><option value="insumos" @selected(old('tipo') === 'insumos')>Compra de insumos</option><option value="otros" @selected(old('tipo') === 'otros')>Otros gastos</option></select></div></div>
                <div class="col-md-4"><div class="form-group"><label class="floating-label-activo-sm" for="gasto-fecha">Fecha</label><input id="gasto-fecha" type="date" name="fecha" class="form-control form-control-sm" value="{{ old('fecha',now('America/Santiago')->toDateString()) }}" required></div></div>
                <div class="col-md-4"><div class="form-group"><label class="floating-label-activo-sm" for="gasto-monto">Monto en CLP</label><input id="gasto-monto" type="number" name="monto" class="form-control form-control-sm" min="1" max="1000000000" step="1" value="{{ old('monto') }}" required></div></div>
                <div class="col-md-12"><div class="form-group"><label class="floating-label-activo-sm" for="gasto-concepto">Insumos adquiridos o descripción del gasto</label><input id="gasto-concepto" name="concepto" class="form-control form-control-sm" maxlength="200" value="{{ old('concepto') }}" required></div></div>
                <div class="col-md-6"><div class="form-group"><label class="floating-label-activo-sm" for="gasto-proveedor">Proveedor (opcional)</label><input id="gasto-proveedor" name="proveedor" class="form-control form-control-sm" maxlength="150" value="{{ old('proveedor') }}"></div></div>
                <div class="col-md-6"><div class="form-group"><label class="floating-label-activo-sm" for="gasto-comprobante">Factura o comprobante (opcional)</label><input id="gasto-comprobante" name="comprobante" class="form-control form-control-sm" maxlength="100" value="{{ old('comprobante') }}"></div></div>
            </div>
            <button class="btn btn-info btn-sm" type="submit"><i class="feather icon-save mr-1"></i>Guardar gasto</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Compras y gastos del servicio</h5></div>
    <div class="card-body">
        <div class="resumen-area" aria-label="Resumen de compras y gastos">
            <div class="resumen-area-dato"><span class="resumen-area-etiqueta">Compras de insumos</span><span class="resumen-area-valor">${{ number_format($totalInsumos,0,',','.') }}</span></div>
            <div class="resumen-area-dato"><span class="resumen-area-etiqueta">Otros gastos</span><span class="resumen-area-valor">${{ number_format($totalOtros,0,',','.') }}</span></div>
            <div class="resumen-area-dato"><span class="resumen-area-etiqueta">Total</span><span class="resumen-area-valor">${{ number_format($totalInsumos + $totalOtros,0,',','.') }}</span></div>
        </div>
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Fecha</th><th>Tipo</th><th>Concepto</th><th>Proveedor</th><th>Comprobante</th><th>Monto CLP</th></tr></thead><tbody>
            @forelse($gastos as $gasto)<tr><td>{{ $fechaGasto($gasto->fecha) }}</td><td>{{ $gasto->tipo === 'insumos' ? 'Compra de insumos' : 'Otros gastos' }}</td><td>{{ $gasto->concepto }}</td><td>{{ $gasto->proveedor ?: '—' }}</td><td>{{ $gasto->comprobante ?: '—' }}</td><td>${{ number_format($gasto->monto,0,',','.') }}</td></tr>
            @empty<tr><td colspan="6" class="text-center text-muted">No hay registros.</td></tr>@endforelse
        </tbody></table></div>
        {{ $gastos->links('pagination::bootstrap-4') }}
    </div>
</div>
