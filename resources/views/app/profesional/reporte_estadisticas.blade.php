@extends('template.profesional.template')

@section('content')
<style>
    .vet-report-card{border:0;border-radius:14px;box-shadow:0 4px 16px rgba(31,45,61,.08)}
    .vet-kpi{padding:20px;min-height:118px;display:flex;align-items:center;gap:16px}
    .vet-kpi-icon{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:23px;background:#e5f8f7;color:#16aaa8}
    .vet-kpi strong{font-size:25px;color:#34445c;display:block}.vet-kpi small{color:#778398}
    .vet-bar{height:10px;border-radius:10px;background:#edf1f5;overflow:hidden}.vet-bar span{height:100%;display:block;border-radius:10px;background:linear-gradient(90deg,#13b6b3,#6b3a91)}
    .vet-filter{background:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 4px 16px rgba(31,45,61,.08)}
    #contenido_reporte{-webkit-transition:opacity .2s ease;transition:opacity .2s ease}
    #contenido_reporte.vet-cargando{opacity:.5;pointer-events:none}
</style>
<div class="pcoded-main-container"><div class="pcoded-content">
    <div class="row">
        <div class="col-md-12 mb-2">
            <h5 class="f-26 d-inline">Reportes y estadísticas veterinarias</h5>
        </div>
    </div>

    @php
        // Últimos 12 meses para el filtro rápido por mes
        $mesesFiltro = collect(range(0, 11))->map(function ($retroceso) {
            $mes = now()->startOfMonth()->subMonths($retroceso);
            return ['valor' => $mes->format('Y-m'), 'etiqueta' => ucfirst($mes->locale('es')->translatedFormat('F Y'))];
        });
        // El mes queda marcado solo cuando el rango corresponde al mes completo (o al mes en curso hasta hoy)
        $mesSeleccionado = ($desde->day === 1 && $desde->isSameMonth($hasta)
            && ($hasta->isSameDay($hasta->copy()->endOfMonth()) || $hasta->isToday()))
            ? $desde->format('Y-m') : '';
    @endphp

    <form method="GET" action="{{ url()->current() }}" class="vet-filter mb-4" id="form_filtro_reporte">
        <div class="form-row align-items-end">
            <div class="form-group col-sm-6 col-md-3 mb-2 mb-md-0">
                <label class="floating-label-activo-sm" for="filtro_mes">Mes</label>
                <select id="filtro_mes" class="form-control form-control-sm">
                    <option value="">Rango personalizado</option>
                    @foreach($mesesFiltro as $mesFiltro)
                        <option value="{{ $mesFiltro['valor'] }}" @if($mesSeleccionado === $mesFiltro['valor']) selected @endif>{{ $mesFiltro['etiqueta'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-sm-6 col-md-3 mb-2 mb-md-0">
                <label class="floating-label-activo-sm" for="filtro_desde">Desde</label>
                <input type="date" name="desde" id="filtro_desde" value="{{ $desde->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div class="form-group col-sm-6 col-md-3 mb-2 mb-md-0">
                <label class="floating-label-activo-sm" for="filtro_hasta">Hasta</label>
                <input type="date" name="hasta" id="filtro_hasta" value="{{ $hasta->toDateString() }}" class="form-control form-control-sm">
            </div>
            <div class="form-group col-sm-6 col-md-3 mb-0">
                <button type="submit" class="btn btn-info btn-sm btn-block" id="btn_actualizar_reporte"><i class="feather icon-filter"></i> Actualizar reporte</button>
            </div>
        </div>
    </form>

    <div id="contenido_reporte">
    <div class="row">
        @foreach([
            ['icon-clipboard','Atenciones',$resumen['atenciones']], ['icon-check-circle','Finalizadas',$resumen['finalizadas']],
            ['icon-heart','Mascotas atendidas',$resumen['mascotas']], ['icon-calendar','Horas agendadas',$resumen['horas_agendadas']],
            ['icon-dollar-sign','Ingresos registrados','$'.number_format($resumen['ingresos'],0,',','.')]
        ] as $kpi)
        <div class="col-sm-6 col-xl mb-3"><div class="card vet-report-card h-100"><div class="vet-kpi"><span class="vet-kpi-icon"><i class="feather {{ $kpi[0] }}"></i></span><div><strong>{{ $kpi[2] }}</strong><small>{{ $kpi[1] }}</small></div></div></div></div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4"><div class="card vet-report-card h-100"><div class="card-header"><h5>Atenciones de los últimos 6 meses</h5></div><div class="card-body">
            @php $maxMes = max(1, $meses->max('total')); @endphp
            @foreach($meses as $mes)<div class="mb-3"><div class="d-flex justify-content-between mb-1"><span>{{ $mes->etiqueta }}</span><strong>{{ $mes->total }}</strong></div><div class="vet-bar"><span style="width:{{ ($mes->total/$maxMes)*100 }}%"></span></div></div>@endforeach
        </div></div></div>
        <div class="col-lg-5 mb-4"><div class="card vet-report-card h-100"><div class="card-header"><h5>Estados de agenda</h5></div><div class="card-body">
            @forelse($estados as $estado)<div class="d-flex justify-content-between align-items-center border-bottom py-2"><span><i class="fas fa-circle mr-2" style="color:{{ $estado->color }}"></i>{{ $estado->nombre }}</span><strong>{{ $estado->total }}</strong></div>@empty<p class="text-muted text-center">Sin horas en el período.</p>@endforelse
        </div></div></div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4"><div class="card vet-report-card h-100"><div class="card-header"><h5>Atenciones por servicio</h5></div><div class="card-body">
            @forelse($servicios as $servicio)<div class="d-flex justify-content-between border-bottom py-2"><span>{{ $servicio->nombre }}</span><strong>{{ $servicio->total }}</strong></div>@empty<p class="text-muted text-center">Sin servicios registrados.</p>@endforelse
        </div></div></div>
        <div class="col-lg-8 mb-4"><div class="card vet-report-card"><div class="card-header"><h5>Atenciones recientes</h5></div><div class="card-body table-responsive">
            <table class="table table-hover table-sm"><thead><tr><th>Fecha</th><th>Mascota</th><th>Diagnóstico</th><th>Estado</th></tr></thead><tbody>
            @forelse($ultimas as $fila)<tr><td>{{ \Carbon\Carbon::parse($fila->created_at)->format('d-m-Y H:i') }}</td><td>{{ $fila->mascota ?: 'Sin mascota' }}</td><td>{{ $fila->hipotesis_diagnostico ?: 'Sin diagnóstico' }}</td><td><span class="badge badge-{{ $fila->finalizada ? 'success' : 'warning' }}">{{ $fila->finalizada ? 'Finalizada' : 'En curso' }}</span></td></tr>@empty<tr><td colspan="4" class="text-center text-muted">No existen atenciones en este período.</td></tr>@endforelse
            </tbody></table>
        </div></div></div>
    </div>
    </div>
</div></div>
@endsection

@push('page-scripts')
<script>
    $(function () {
        var $formulario = $('#form_filtro_reporte');
        var $mes = $('#filtro_mes');
        var $desde = $('#filtro_desde');
        var $hasta = $('#filtro_hasta');
        var $boton = $('#btn_actualizar_reporte');
        var consulta = null;

        $mes.select2({ width: '100%', language: { noResults: function () { return 'Sin resultados'; } } });

        // Pide el reporte con el rango elegido y reemplaza solo los resultados, sin recargar la página
        function actualizarReporte() {
            if (consulta) { consulta.abort(); }

            var parametros = { desde: $desde.val(), hasta: $hasta.val() };
            $boton.prop('disabled', true);
            $('#contenido_reporte').addClass('vet-cargando');

            consulta = $.get($formulario.attr('action'), parametros)
                .done(function (html) {
                    var $respuesta = $('<div>').append($.parseHTML(html));
                    var $nuevo = $respuesta.find('#contenido_reporte');

                    if ($nuevo.length) {
                        $('#contenido_reporte').html($nuevo.html());
                        if (window.history && window.history.replaceState) {
                            window.history.replaceState(null, '', $formulario.attr('action') + '?' + $.param(parametros));
                        }
                    }
                })
                .fail(function (xhr, estado) {
                    if (estado !== 'abort') {
                        alert('No fue posible actualizar el reporte. Intente nuevamente.');
                    }
                })
                .always(function (respuesta, estado) {
                    if (estado === 'abort') { return; }
                    $boton.prop('disabled', false);
                    $('#contenido_reporte').removeClass('vet-cargando');
                });
        }

        $formulario.on('submit', function (evento) {
            evento.preventDefault();
            actualizarReporte();
        });

        // Al elegir un mes se completa el rango con su primer y último día
        $mes.on('change', function () {
            var valor = $(this).val();
            if (!valor) { return; }

            var partes = valor.split('-');
            var ultimoDia = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10), 0).getDate();
            $desde.val(valor + '-01');
            $hasta.val(valor + '-' + (ultimoDia < 10 ? '0' + ultimoDia : ultimoDia));
            actualizarReporte();
        });

        // Si se cambian las fechas a mano, el filtro deja de corresponder a un mes
        $desde.add($hasta).on('change', function () {
            if ($mes.val()) { $mes.val('').trigger('change.select2'); }
        });
    });
</script>
@endpush
