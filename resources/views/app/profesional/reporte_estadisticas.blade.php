@extends('template.profesional.template')

@section('content')
<style>
    .vet-report-card{border:0;border-radius:14px;box-shadow:0 4px 16px rgba(31,45,61,.08)}
    .vet-kpi{padding:20px;min-height:118px;display:flex;align-items:center;gap:16px}
    .vet-kpi-icon{width:48px;height:48px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:23px;background:#e5f8f7;color:#16aaa8}
    .vet-kpi strong{font-size:25px;color:#34445c;display:block}.vet-kpi small{color:#778398}
    .vet-bar{height:10px;border-radius:10px;background:#edf1f5;overflow:hidden}.vet-bar span{height:100%;display:block;border-radius:10px;background:linear-gradient(90deg,#13b6b3,#6b3a91)}
    .vet-filter{background:#fff;border-radius:14px;padding:16px 20px;box-shadow:0 4px 16px rgba(31,45,61,.08)}
</style>
<div class="pcoded-main-container"><div class="pcoded-content">
    <div class="page-header"><div class="page-block"><div class="row align-items-center"><div class="col-md-12">
        <div class="page-header-title"><h5 class="m-b-10 font-weight-bold">Reportes y estadísticas veterinarias</h5></div>
        <ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('profesional.home') }}"><i class="feather icon-home"></i></a></li><li class="breadcrumb-item">Reportes y estadísticas</li></ul>
    </div></div></div></div>

    <form method="GET" class="vet-filter mb-4">
        <div class="form-row align-items-end">
            <div class="col-md-4"><label>Desde</label><input type="date" name="desde" value="{{ $desde->toDateString() }}" class="form-control"></div>
            <div class="col-md-4"><label>Hasta</label><input type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="form-control"></div>
            <div class="col-md-4"><button class="btn btn-info btn-block"><i class="feather icon-filter"></i> Actualizar reporte</button></div>
        </div>
    </form>

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
</div></div>
@endsection
