@extends($template)
@section('content')
@php
    // Color del vencimiento: vencido, por vencer (60 días) o vigente
    $hoy = now()->toDateString();
    $enSesentaDias = now()->addDays(60)->toDateString();
    $tiposMovimiento = ['entrada' => 'success', 'salida' => 'info', 'baja' => 'danger'];
    // Pestaña abierta: la del formulario que volvió con errores, o la de la tabla que se está paginando
    $pestanaActiva = match (true) {
        $errors->any() && old('_formulario') === 'factura' => 'facturas',
        request('vista') === 'facturas' => 'facturas',
        $errors->any() && old('operacion') !== null => 'movimientos',
        $errors->any() => 'registro',
        request()->has('movimientos') => 'historial',
        request()->has('page') => 'productos',
        default => 'registro',
    };
@endphp
<div class="pcoded-main-container"><div class="pcoded-content inventario-veterinario">

    @include('template.partials.boton_volver')

    {{-- Título de la pantalla con la descarga de movimientos a la derecha --}}
    <div class="row">
        <div class="col-md-12 mb-3 encabezado-pagina">
            <div>
                <h5 class="titulo-modulo f-26 d-block mb-0">Medicamentos, vacunas y alimentos</h5>
            </div>
            <a class="btn btn-info" href="{{ route('vet.inventario.exportar', request()->only('id_institucion')) }}">
                <i class="feather icon-download mr-1" aria-hidden="true"></i>Descargar movimientos CSV
            </a>
        </div>
    </div>

    <ul class="nav pill-moderno pill-moderno-white" role="tablist" aria-label="Secciones del inventario">
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'registro' ? 'active' : '' }}" data-toggle="pill" href="#pestana_registro" role="tab">Registro</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'productos' ? 'active' : '' }}" data-toggle="pill" href="#pestana_productos" role="tab">Productos</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'movimientos' ? 'active' : '' }}" data-toggle="pill" href="#pestana_movimientos" role="tab">Movimientos</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'historial' ? 'active' : '' }}" data-toggle="pill" href="#pestana_historial" role="tab">Historial de movimientos</a></li>
        <li class="nav-item"><a class="nav-link {{ $pestanaActiva === 'facturas' ? 'active' : '' }}" data-toggle="pill" href="#pestana_facturas" role="tab">Documentos de compras</a></li>
    </ul>

    @if(session('success') || $errors->any())
    <div class="alertas-flotantes">
        @if(session('success'))
        <div class="alert alert-inverse alert-dismissible fade show alerta-flotante" role="status">
            <i class="feather icon-check-circle alerta-flotante-icono alerta-flotante-exito"></i>
            <div class="alerta-flotante-texto">{{ session('success') }}</div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-inverse alert-dismissible fade show alerta-flotante" role="alert">
            <i class="feather icon-alert-circle alerta-flotante-icono alerta-flotante-error"></i>
            <div class="alerta-flotante-texto"><strong>No se guardaron los cambios.</strong> {{ $errors->first() }}</div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
        </div>
        @endif
    </div>
    @endif

    <p class="inventario-ayuda">
        <i class="feather icon-info" aria-hidden="true"></i>
        Las recetas oficiales y las autorizaciones se gestionan en los canales del SAG. Registra aquí su referencia para la trazabilidad del inventario.
    </p>

    <div class="tab-content">
    <div class="tab-pane fade {{ $pestanaActiva === 'registro' ? 'show active' : '' }}" id="pestana_registro" role="tabpanel">
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-package"></i></span>Registrar producto y lote</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('vet.inventario.lote', request()->only('id_institucion')) }}">@csrf
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="floating-label-activo-sm" for="inventario-categoria">Categoría</label>
                        <select class="form-control form-control-sm" id="inventario-categoria" name="categoria">
                            @foreach(['medicamento','vacuna','alimento','insumo'] as $categoria)<option value="{{ $categoria }}" @selected(old('categoria') === $categoria)>{{ ucfirst($categoria) }}</option>@endforeach
                        </select>
                    </div>
                    @foreach(['producto'=>'Producto / presentación','registro_sag'=>'Registro o autorización SAG','lote'=>'Lote / serie','unidad'=>'Unidad de control (frasco, dosis, kg…)','ubicacion'=>'Bodega / ubicación','conservacion'=>'Condiciones de conservación del fabricante'] as $campo=>$etiqueta)
                    <div class="form-group col-md-4">
                        <label class="floating-label-activo-sm" for="inventario-{{ $campo }}">{{ $etiqueta }}</label>
                        <input class="form-control form-control-sm" id="inventario-{{ $campo }}" name="{{ $campo }}" value="{{ old($campo) }}" maxlength="{{ $campo === 'conservacion' ? 250 : ($campo === 'producto' ? 180 : ($campo === 'unidad' ? 40 : 100)) }}" @required($campo !== 'registro_sag')>
                    </div>
                    @endforeach
                    <div class="form-group col-md-4">
                        <label class="floating-label-activo-sm" for="inventario-vencimiento">Vencimiento</label>
                        <input class="form-control form-control-sm" id="inventario-vencimiento" name="vencimiento" type="date" value="{{ old('vencimiento') }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="floating-label-activo-sm" for="inventario-condicion-venta">Condición de venta</label>
                        <select class="form-control form-control-sm" id="inventario-condicion-venta" name="condicion_venta">
                            @foreach(['libre'=>'Venta libre','receta'=>'Receta veterinaria','retenida'=>'Receta retenida','control_saldo'=>'Receta retenida con control de saldo','uso_veterinario'=>'Uso exclusivo veterinario'] as $clave=>$texto)<option value="{{ $clave }}" @selected(old('condicion_venta') === $clave)>{{ $texto }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="floating-label-activo-sm" for="inventario-cantidad-inicial">Número de unidades a ingresar</label>
                        <input class="form-control form-control-sm" id="inventario-cantidad-inicial" name="cantidad_inicial" type="number"
                            min="0.001" max="999999999" step="0.001" value="{{ old('cantidad_inicial') }}" required>
                        <small class="form-text text-muted">Cantidad expresada en la unidad de control del lote.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="floating-label-activo-sm" for="inventario-proveedor-inicial">Proveedor / procedencia</label>
                        <input class="form-control form-control-sm" id="inventario-proveedor-inicial" name="proveedor_inicial" maxlength="200" value="{{ old('proveedor_inicial') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="floating-label-activo-sm" for="inventario-documento-inicial">Factura / guía de ingreso</label>
                        <input class="form-control form-control-sm" id="inventario-documento-inicial" name="documento_inicial" maxlength="150" value="{{ old('documento_inicial') }}">
                    </div>
                </div>
                <div class="inventario-pie">
                    <button type="submit" class="btn btn-info">Guardar lote</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <div class="tab-pane fade {{ $pestanaActiva === 'productos' ? 'show active' : '' }}" id="pestana_productos" role="tabpanel">
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-layers"></i></span>Lotes y existencias</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Producto</th><th>Lote</th><th>Ubicación</th><th>Vencimiento</th><th>Saldo</th></tr></thead>
                    <tbody>
                    @forelse($lotes as $lote)
                        <tr>
                            <td>{{ $lote->producto }}<small class="d-block text-muted">{{ ucfirst($lote->categoria) }} · {{ $lote->conservacion }}</small></td>
                            <td>{{ $lote->lote }}</td>
                            <td>{{ $lote->ubicacion }}</td>
                            <td><span class="badge {{ $lote->vencimiento < $hoy ? 'badge-light-danger' : ($lote->vencimiento <= $enSesentaDias ? 'badge-light-warning' : 'badge-light-success') }}">{{ \Illuminate\Support\Carbon::parse($lote->vencimiento)->format('d-m-Y') }}</span></td>
                            <td>{{ $lote->saldo }} {{ $lote->unidad }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Aún no hay lotes registrados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $lotes->links('pagination::bootstrap-4') }}
        </div>
    </div>
    </div>

    <div class="tab-pane fade {{ $pestanaActiva === 'movimientos' ? 'show active' : '' }}" id="pestana_movimientos" role="tabpanel">
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-repeat"></i></span>Registrar entrada, salida o baja</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('vet.inventario.movimiento', request()->only('id_institucion')) }}">@csrf
                <input type="hidden" name="operacion" value="{{ old('operacion', (string) \Illuminate\Support\Str::uuid()) }}">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="floating-label-activo-sm" for="movimiento-lote">Lote</label>
                        <select class="form-control form-control-sm" id="movimiento-lote" name="lote_id" required>
                            <option value="">Seleccione</option>
                            @foreach($lotesMovimiento as $lote)<option value="{{ $lote->id }}" @selected(old('lote_id') == $lote->id)>{{ $lote->producto }} · {{ $lote->lote }} · {{ $lote->ubicacion }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="floating-label-activo-sm" for="movimiento-tipo">Movimiento</label>
                        <select class="form-control form-control-sm" id="movimiento-tipo" name="tipo">
                            @foreach(['entrada','salida','baja'] as $tipo)<option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="floating-label-activo-sm" for="movimiento-cantidad">Cantidad</label>
                        <input class="form-control form-control-sm" id="movimiento-cantidad" type="number" name="cantidad" step="0.001" min="0.001" value="{{ old('cantidad') }}" required>
                    </div>
                    @foreach(['contraparte'=>'Proveedor / destino / paciente','documento'=>'Factura, guía o referencia del movimiento','receta'=>'Referencia receta SAG / autorización','observaciones'=>'Motivo / observaciones'] as $campo=>$etiqueta)
                    <div class="form-group col-md-6">
                        <label class="floating-label-activo-sm" for="movimiento-{{ $campo }}">{{ $etiqueta }}</label>
                        <input class="form-control form-control-sm" id="movimiento-{{ $campo }}" name="{{ $campo }}" value="{{ old($campo) }}" @required(in_array($campo,['contraparte','documento']))>
                    </div>
                    @endforeach
                </div>
                <div class="inventario-pie">
                    <button type="submit" class="btn btn-info">Guardar movimiento</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <div class="tab-pane fade {{ $pestanaActiva === 'historial' ? 'show active' : '' }}" id="pestana_historial" role="tabpanel">
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-clock"></i></span>Historial de movimientos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Fecha</th><th>Producto / lote</th><th>Tipo</th><th>Cantidad</th><th>Saldo</th><th>Procedencia / destino</th><th>Documento / receta</th><th>Registrado por</th><th>Indicado por</th><th>Administrado por</th></tr></thead>
                    <tbody>
                    @forelse($movimientos as $m)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($m->created_at)->format('d-m-Y H:i') }}</td>
                            <td>{{ $m->producto }} · {{ $m->lote }}</td>
                            <td><span class="badge badge-light-{{ $tiposMovimiento[$m->tipo] ?? 'secondary' }}">{{ ucfirst($m->tipo) }}</span></td>
                            <td>{{ $m->cantidad }}</td>
                            <td>{{ $m->saldo }} {{ $m->unidad }}</td>
                            <td>{{ $m->contraparte }}</td>
                            <td>{{ $m->documento }} {{ $m->receta }}</td>
                            <td>{{ $m->usuario_id }}</td>
                            <td>{{ $m->indicado_por ?: '—' }}</td>
                            <td>{{ $m->administrado_por ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted">Aún no hay movimientos.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $movimientos->links('pagination::bootstrap-4') }}
        </div>
    </div>
    </div>

    <div class="tab-pane fade {{ $pestanaActiva === 'facturas' ? 'show active' : '' }}" id="pestana_facturas" role="tabpanel">
        @include('app.veterinaria.inventario.partials.facturas')
    </div>
    </div>

    {{-- Los modales de facturas van fuera de las pestañas para que abran aunque la pestaña esté oculta --}}
    @include('app.veterinaria.inventario.partials.facturas_modales')

</div></div>
@endsection
@section('page-script')
<script>
    // Las alertas flotantes se cierran solas a los 6 segundos, como en las demás vistas veterinarias
    if (window.jQuery) {
        setTimeout(function () { window.jQuery('.inventario-veterinario .alerta-flotante').alert('close'); }, 6000);
    }
</script>
<script src="{{ asset('js/facturas_compra.js') }}?v=20261119-2"></script>
@endsection
