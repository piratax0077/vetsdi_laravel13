@extends($template)
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
    <h5 class="titulo-modulo f-26 mb-3">Medicamentos, vacunas y alimentos</h5>
    <p>{{ $nombre }}</p>
    <p class="text-muted">Las recetas oficiales y las autorizaciones se gestionan en los canales del SAG. Registra aquí su referencia para la trazabilidad del inventario.</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <a class="btn btn-info mb-3" href="{{ route('vet.inventario.exportar', request()->only('id_institucion')) }}">Descargar movimientos CSV</a>
    <div class="card"><div class="card-header"><h5>Registrar producto y lote</h5></div><div class="card-body">
        <form method="POST" action="{{ route('vet.inventario.lote', request()->only('id_institucion')) }}">@csrf
            <div class="form-row">
                <div class="form-group col-md-4"><label>Categoría</label><select class="form-control" name="categoria">@foreach(['medicamento','vacuna','alimento','insumo'] as $categoria)<option value="{{ $categoria }}">{{ ucfirst($categoria) }}</option>@endforeach</select></div>
                @foreach(['producto'=>'Producto / presentación','registro_sag'=>'Registro o autorización SAG','lote'=>'Lote / serie','unidad'=>'Unidad de control (frasco, dosis, kg…)','ubicacion'=>'Bodega / ubicación','conservacion'=>'Condiciones de conservación del fabricante'] as $campo=>$etiqueta)
                    <div class="form-group col-md-4"><label for="inventario-{{ $campo }}">{{ $etiqueta }}</label><input class="form-control" id="inventario-{{ $campo }}" name="{{ $campo }}" value="{{ old($campo) }}" maxlength="{{ $campo === 'conservacion' ? 250 : ($campo === 'producto' ? 180 : ($campo === 'unidad' ? 40 : 100)) }}" @required($campo !== 'registro_sag')></div>
                @endforeach
                <div class="form-group col-md-4"><label>Vencimiento</label><input class="form-control" name="vencimiento" type="date" value="{{ old('vencimiento') }}" required></div>
                <div class="form-group col-md-4"><label>Condición de venta</label><select class="form-control" name="condicion_venta">@foreach(['libre'=>'Venta libre','receta'=>'Receta veterinaria','retenida'=>'Receta retenida','control_saldo'=>'Receta retenida con control de saldo','uso_veterinario'=>'Uso exclusivo veterinario'] as $clave=>$texto)<option value="{{ $clave }}">{{ $texto }}</option>@endforeach</select></div>
                <div class="form-group col-md-4">
                    <label for="inventario-cantidad-inicial">Número de unidades a ingresar</label>
                    <input class="form-control" id="inventario-cantidad-inicial" name="cantidad_inicial" type="number"
                        min="0.001" max="999999999" step="0.001" value="{{ old('cantidad_inicial') }}" required>
                    <small class="form-text text-muted">Cantidad expresada en la unidad de control del lote.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="inventario-proveedor-inicial">Proveedor / procedencia</label>
                    <input class="form-control" id="inventario-proveedor-inicial" name="proveedor_inicial" maxlength="200" value="{{ old('proveedor_inicial') }}">
                </div>
                <div class="form-group col-md-4">
                    <label for="inventario-documento-inicial">Factura / guía de ingreso</label>
                    <input class="form-control" id="inventario-documento-inicial" name="documento_inicial" maxlength="150" value="{{ old('documento_inicial') }}">
                </div>
            </div><button class="btn btn-info">Guardar lote</button>
        </form>
    </div></div>
    <div class="card"><div class="card-header"><h5>Lotes y existencias</h5></div><div class="card-body table-responsive">
        <table class="table table-striped"><thead><tr><th>Producto</th><th>Lote</th><th>Ubicación</th><th>Vencimiento</th><th>Saldo</th></tr></thead><tbody>
        @forelse($lotes as $lote)<tr><td>{{ $lote->producto }}<small class="d-block">{{ ucfirst($lote->categoria) }} · {{ $lote->conservacion }}</small></td><td>{{ $lote->lote }}</td><td>{{ $lote->ubicacion }}</td><td><span class="badge {{ $lote->vencimiento < now()->toDateString() ? 'badge-danger' : ($lote->vencimiento <= now()->addDays(60)->toDateString() ? 'badge-warning' : 'badge-success') }}">{{ $lote->vencimiento }}</span></td><td>{{ $lote->saldo }} {{ $lote->unidad }}</td></tr>
        @empty<tr><td colspan="5">Aún no hay lotes registrados.</td></tr>@endforelse
        </tbody></table>{{ $lotes->links() }}
    </div></div>
    <div class="card"><div class="card-header"><h5>Registrar entrada, salida o baja</h5></div><div class="card-body">
        <form method="POST" action="{{ route('vet.inventario.movimiento', request()->only('id_institucion')) }}">@csrf
            <input type="hidden" name="operacion" value="{{ old('operacion', (string) \Illuminate\Support\Str::uuid()) }}">
            <div class="form-row">
                <div class="form-group col-md-6"><label>Lote</label><select class="form-control" name="lote_id" required><option value="">Seleccione</option>@foreach($lotesMovimiento as $lote)<option value="{{ $lote->id }}" @selected(old('lote_id') == $lote->id)>{{ $lote->producto }} · {{ $lote->lote }} · {{ $lote->ubicacion }}</option>@endforeach</select></div>
                <div class="form-group col-md-3"><label>Movimiento</label><select class="form-control" name="tipo">@foreach(['entrada','salida','baja'] as $tipo)<option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></div>
                <div class="form-group col-md-3"><label>Cantidad</label><input class="form-control" type="number" name="cantidad" step="0.001" min="0.001" value="{{ old('cantidad') }}" required></div>
                @foreach(['contraparte'=>'Proveedor / destino / paciente','documento'=>'Factura, guía o referencia del movimiento','receta'=>'Referencia receta SAG / autorización','observaciones'=>'Motivo / observaciones'] as $campo=>$etiqueta)
                <div class="form-group col-md-6"><label>{{ $etiqueta }}</label><input class="form-control" name="{{ $campo }}" value="{{ old($campo) }}" @required(in_array($campo,['contraparte','documento']))></div>
                @endforeach
            </div><button class="btn btn-info">Guardar movimiento</button>
        </form>
    </div></div>
    <div class="card"><div class="card-header"><h5>Historial de movimientos</h5></div><div class="card-body table-responsive">
        <table class="table table-striped"><thead><tr><th>Fecha</th><th>Producto / lote</th><th>Tipo</th><th>Cantidad</th><th>Saldo</th><th>Procedencia / destino</th><th>Documento / receta</th><th>Registrado por</th><th>Indicado por</th><th>Administrado por</th></tr></thead><tbody>
        @forelse($movimientos as $m)<tr><td>{{ $m->created_at }}</td><td>{{ $m->producto }} · {{ $m->lote }}</td><td>{{ ucfirst($m->tipo) }}</td><td>{{ $m->cantidad }}</td><td>{{ $m->saldo }} {{ $m->unidad }}</td><td>{{ $m->contraparte }}</td><td>{{ $m->documento }} {{ $m->receta }}</td><td>{{ $m->usuario_id }}</td><td>{{ $m->indicado_por ?: "—" }}</td><td>{{ $m->administrado_por ?: "—" }}</td></tr>@empty<tr><td colspan="10">Aún no hay movimientos.</td></tr>@endforelse
        </tbody></table>{{ $movimientos->links() }}
    </div></div>
</div></div>
@endsection
