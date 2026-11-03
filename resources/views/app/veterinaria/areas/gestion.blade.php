@extends('template.adm_cm.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content configuracion-administrador">
    <div class="d-flex justify-content-between align-items-center mb-3"><h4>Gestión del área · {{ $titulo }}</h4><a class="btn btn-outline-info btn-sm" href="{{ route('vet.areas.index',['area'=>$area,'id_institucion'=>$institucion->id]) }}">Volver al área</a></div>
    @if($area === 'hospitalizacion')
    <a class="btn btn-info mb-3" href="{{ route('vet.hospital.equipo',['id_institucion'=>$institucion->id]) }}">Equipo de profesionales y enfermería</a>
    @endif
    @if(session('gestion_guardada'))<div class="alert alert-success">{{ session('gestion_guardada') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="card"><div class="card-header"><h5>Responsable del servicio</h5></div><div class="card-body">
        <form method="post" action="{{ route('vet.areas.gestion.guardar',['area'=>$area]) }}">
            @csrf
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="accion" value="responsable">
            <div class="form-row">
                <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="responsable-nombre">Nombre del responsable</label><input id="responsable-nombre" name="nombre" class="form-control" maxlength="150" value="{{ old('accion') === 'responsable' ? old('nombre') : ($responsable->nombre ?? '') }}" required></div>
                <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="responsable-contacto">Contacto (opcional)</label><input id="responsable-contacto" name="contacto" class="form-control" maxlength="150" value="{{ old('accion') === 'responsable' ? old('contacto') : ($responsable->contacto ?? '') }}"></div>
            </div>
            <button class="btn btn-info" type="submit">Guardar responsable</button>
        </form>
    </div></div>
    <div class="row">
        <div class="col-md-6"><div class="card"><div class="card-body"><h6>Compras de insumos</h6><strong>${{ number_format($totales->get('insumos',0),0,',','.') }}</strong></div></div></div>
        <div class="col-md-6"><div class="card"><div class="card-body"><h6>Otros gastos</h6><strong>${{ number_format($totales->get('otros',0),0,',','.') }}</strong></div></div></div>
    </div>
    <div class="card"><div class="card-header"><h5>Registrar compra de insumos u otro gasto</h5></div><div class="card-body">
        <form method="post" action="{{ route('vet.areas.gestion.guardar',['area'=>$area]) }}">
            @csrf
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="accion" value="gasto">
            <div class="form-row">
                <div class="form-group col-md-4"><label class="floating-label-activo-sm" for="gasto-tipo">Tipo</label><select id="gasto-tipo" name="tipo" class="form-control" required><option value="insumos" @selected(old('tipo') === 'insumos')>Compra de insumos</option><option value="otros" @selected(old('tipo') === 'otros')>Otros gastos</option></select></div>
                <div class="form-group col-md-4"><label class="floating-label-activo-sm" for="gasto-fecha">Fecha</label><input id="gasto-fecha" type="date" name="fecha" class="form-control" value="{{ old('fecha',now('America/Santiago')->toDateString()) }}" required></div>
                <div class="form-group col-md-4"><label class="floating-label-activo-sm" for="gasto-monto">Monto en CLP</label><input id="gasto-monto" type="number" name="monto" class="form-control" min="1" max="1000000000" step="1" value="{{ old('monto') }}" required></div>
                <div class="form-group col-md-12"><label class="floating-label-activo-sm" for="gasto-concepto">Insumos adquiridos o descripción del gasto</label><input id="gasto-concepto" name="concepto" class="form-control" maxlength="200" value="{{ old('concepto') }}" required></div>
                <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="gasto-proveedor">Proveedor (opcional)</label><input id="gasto-proveedor" name="proveedor" class="form-control" maxlength="150" value="{{ old('proveedor') }}"></div>
                <div class="form-group col-md-6"><label class="floating-label-activo-sm" for="gasto-comprobante">Factura o comprobante (opcional)</label><input id="gasto-comprobante" name="comprobante" class="form-control" maxlength="100" value="{{ old('comprobante') }}"></div>
            </div>
            <button class="btn btn-info" type="submit">Guardar gasto</button>
        </form>
    </div></div>
    <div class="card"><div class="card-header"><h5>Compras y gastos del servicio</h5></div><div class="card-body">
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Fecha</th><th>Tipo</th><th>Concepto</th><th>Proveedor</th><th>Comprobante</th><th>Monto CLP</th></tr></thead><tbody>
            @forelse($gastos as $gasto)<tr><td>{{ $gasto->fecha }}</td><td>{{ $gasto->tipo === 'insumos' ? 'Compra de insumos' : 'Otros gastos' }}</td><td>{{ $gasto->concepto }}</td><td>{{ $gasto->proveedor ?: '—' }}</td><td>{{ $gasto->comprobante ?: '—' }}</td><td>${{ number_format($gasto->monto,0,',','.') }}</td></tr>
            @empty<tr><td colspan="6" class="text-center text-muted">No hay registros.</td></tr>@endforelse
        </tbody></table></div>
        {{ $gastos->links('pagination::bootstrap-4') }}
    </div></div>
</div></div>
@endsection
