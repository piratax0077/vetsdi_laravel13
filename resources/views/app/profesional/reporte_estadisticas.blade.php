@extends('template.profesional.template')
@section('sin_boton_volver', '1')
@section('content')
    @php
        $porcentaje = fn ($valor, $total) => $total > 0 ? round(($valor / $total) * 100, 1) : 0;
        $convenioTotal = (int) $convenios->sum('total');
        $coloresConvenio = ['#A06CC1', '#17a2b8', '#6f42c1', '#ffba57', '#72B02C', '#ff5252'];
        $serieAsistenciaJson = json_encode([(int) $asistieron, (int) $noAsistieron, (int) $canceladasPaciente, (int) $canceladasLugar]);
        $serieAtencionesJson = json_encode($atencionesMes->pluck('total')->map(function ($total) { return (int) $total; })->values()->all());
        $categoriasAtencionesJson = json_encode($atencionesMes->pluck('mes')->values()->all(), JSON_UNESCAPED_UNICODE);
        $serieConvenioJson = json_encode($convenios->pluck('total')->map(function ($total) { return (int) $total; })->values()->all());
        $etiquetasConvenioJson = json_encode($convenios->pluck('nombre')->values()->all(), JSON_UNESCAPED_UNICODE);
        $coloresConvenioJson = json_encode($coloresConvenio);
        $nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $textoMes = $mes === 'actual' ? 'Este mes' : $nombresMeses[(int) $mes - 1];
    @endphp

    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!--TÍTULO Y FILTROS-->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-md-center justify-content-between">
                                <div class="mb-3 mb-md-0">
                                    <h3 class="reportes-titulo">Estadísticas y Reportes</h3>
                                    <p class="reportes-subtitulo mb-0">Resumen general de la actividad veterinaria y clínica</p>
                                </div>
                                <form method="GET" action="{{ route('profesional.reporte_estadisticas') }}" class="d-flex flex-wrap filtros-reportes" id="form_filtros_reportes">
                                    <div class="filtro-item filtro-item-icon">
                                        <i class="feather icon-calendar filtro-icon"></i>
                                        <select class="form-control select-filtro-reportes" id="filtro_mes_reportes" name="mes" onchange="this.form.submit()">
                                            <option value="actual" @selected($mes === 'actual')>Mes actual</option>
                                            @foreach($nombresMeses as $numero => $nombreMes)
                                                <option value="{{ $numero + 1 }}" @selected((string) $mes === (string) ($numero + 1))>{{ $nombreMes }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="filtro-item filtro-item-icon">
                                        <i class="feather icon-map-pin filtro-icon"></i>
                                        <select class="form-control select-filtro-reportes" id="filtro_lugar_reportes" name="lugar" onchange="this.form.submit()">
                                            <option value="todos" @selected(!$idLugar)>Todos los lugares</option>
                                            @foreach($lugares as $lugar)
                                                <option value="{{ $lugar->id }}" @selected((int) $idLugar === (int) $lugar->id)>{{ $lugar->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" name="formato" value="csv" class="btn btn-outline-info">Descargar reporte CSV</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--CARDS PRINCIPALES-->
            <div class="row">
                <div class="col-sm-12 col-md-6 col-xl-3 mb-4">
                    <div class="card kpi-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-c-purple text-white mr-3">
                                <i class="feather icon-users"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small">Mascotas atendidas</p>
                                <h3 class="mb-1">{{ number_format($totalPacientes, 0, ',', '.') }}</h3>
                                <span class="small text-c-purple"><span class="kpi-dot bg-c-purple"></span>En el período</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-xl-3 mb-4">
                    <div class="card kpi-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-c-teal text-white mr-3">
                                <i class="feather icon-activity"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small">Atenciones realizadas</p>
                                <h3 class="mb-1">{{ number_format($atencionesRealizadas, 0, ',', '.') }}</h3>
                                <span class="small text-c-teal"><span class="kpi-dot bg-c-teal"></span><span class="mes-caption-text">{{ $textoMes }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-xl-3 mb-4">
                    <div class="card kpi-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-c-green text-white mr-3">
                                <i class="feather icon-credit-card"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small">Ingresos confirmados</p>
                                <h3 class="mb-1">${{ number_format($ingresoTotal, 0, ',', '.') }}</h3>
                                <small class="text-muted d-block">Sin confirmación de pago: ${{ number_format($cobrosPendientes, 0, ',', '.') }}</small>
                                <span class="small text-c-green"><span class="kpi-dot bg-c-green"></span><span class="mes-caption-text">{{ $textoMes }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-6 col-xl-3 mb-4">
                    <div class="card kpi-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="kpi-icon bg-c-yellow text-white mr-3">
                                <i class="feather icon-file-text"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small">Documentos emitidos</p>
                                <h3 class="mb-1">{{ number_format(array_sum($documentos), 0, ',', '.') }}</h3>
                                <span class="small text-c-yellow"><span class="kpi-dot bg-c-yellow"></span><span class="mes-caption-text">{{ $textoMes }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--AGENDA: ASISTENCIA VS AUSENTISMO / ATENCIONES POR MES-->
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-4">
                    <div class="card reportes-card h-100">
                        <div class="card-header-new px-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 f-18 text-dark">Agenda: Asistencia vs Ausentismo</h6>
                            <i class="feather icon-info text-muted" data-toggle="tooltip" data-placement="top"
                                title="Resumen de asistencia de las horas agendadas en el período seleccionado."></i>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-sm-12 col-md-6">
                                    <div id="grafico_asistencia_ausentismo"></div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="kpi-dot bg-c-purple"></span>
                                        <span class="flex-grow-1">Asistieron</span>
                                        <span class="font-weight-bolder mr-2">{{ $asistieron }}</span>
                                        <span class="text-muted small">({{ $porcentaje($asistieron, $totalAgenda) }}%)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="kpi-dot bg-c-red"></span>
                                        <span class="flex-grow-1">No asistieron</span>
                                        <span class="font-weight-bolder mr-2">{{ $noAsistieron }}</span>
                                        <span class="text-muted small">({{ $porcentaje($noAsistieron, $totalAgenda) }}%)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="kpi-dot bg-c-yellow"></span>
                                        <span class="flex-grow-1">Canceladas por tutor</span>
                                        <span class="font-weight-bolder mr-2">{{ $canceladasPaciente }}</span>
                                        <span class="text-muted small">({{ $porcentaje($canceladasPaciente, $totalAgenda) }}%)</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="kpi-dot bg-c-green"></span>
                                        <span class="flex-grow-1">Canceladas por el lugar de atención</span>
                                        <span class="font-weight-bolder mr-2">{{ $canceladasLugar }}</span>
                                        <span class="text-muted small">({{ $porcentaje($canceladasLugar, $totalAgenda) }}%)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="stat-mini-card">
                                        <p class="text-muted mb-1 small">Tasa de asistencia</p>
                                        <h3 class="text-c-green">{{ $porcentaje($asistieron, $totalAgenda) }}%</h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-mini-card">
                                        <p class="text-muted mb-1 small">Tasa de ausentismo</p>
                                        <h3 class="text-c-red">{{ $porcentaje($noAsistieron, $totalAgenda) }}%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-4">
                    <div class="card reportes-card h-100">
                        <div class="card-header-new px-3 d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 f-18 text-dark">Atenciones por mes</h6>
                            <span class="badge badge-light">6 meses hasta el período seleccionado</span>
                        </div>
                        <div class="card-body">
                            <div id="grafico_atenciones_mes"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!--DOCUMENTOS EMITIDOS-->
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-4">
                    <div class="card reportes-card h-100">
                        <div class="card-header-new px-3">
                            <h6 class="mb-0 f-18 text-dark">Documentos emitidos</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex text-muted small mb-2">
                                <span class="flex-grow-1">Documento</span>
                                <span>Cantidad</span>
                            </div>
                            @foreach($documentos as $nombreDocumento => $cantidadDocumento)
                                <div class="doc-list-item">
                                    <div class="d-flex align-items-center">
                                        <span class="doc-icon bg-c-purple text-white"><i class="feather icon-file-text"></i></span>
                                        {{ $nombreDocumento }}
                                    </div>
                                    <span class="font-weight-bolder">{{ number_format($cantidadDocumento, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-4">
                    <div class="card reportes-card h-100">
                        <div class="card-header-new px-3">
                            <h6 class="mb-0 f-18 text-dark">Mascotas según convenio</h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-sm-12 col-md-6">
                                    <div id="grafico_convenio"></div>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    @forelse($convenios as $indice => $convenio)
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="kpi-dot" style="background:{{ $coloresConvenio[$indice % count($coloresConvenio)] }}"></span>
                                            <span class="flex-grow-1">{{ $convenio->nombre }}</span>
                                            <span class="font-weight-bolder mr-2">{{ number_format($convenio->total, 0, ',', '.') }}</span>
                                            <span class="text-muted small">({{ $porcentaje($convenio->total, $convenioTotal) }}%)</span>
                                        </div>
                                    @empty
                                        <span class="text-muted">Sin pacientes en el período seleccionado.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--Cierre: Container Completo-->

@endsection

@section('page-script')
    <script src="{{ asset('js/plugins/apexcharts.min.js') }}"></script>
    <script>
        $(document).ready(function () {

            // Donut: Asistencia vs Ausentismo
            new ApexCharts(document.querySelector('#grafico_asistencia_ausentismo'), {
                chart: { type: 'donut', height: 230 },
                series: {!! $serieAsistenciaJson !!},
                labels: ['Asistieron', 'No asistieron', 'Canceladas por tutor', 'Canceladas por el lugar de atención'],
                colors: ['#A06CC1', '#ff5252', '#ffba57', '#72B02C'],
                legend: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total agendadas',
                                    fontSize: '13px',
                                    color: '#8b93a7',
                                    formatter: function () {
                                        return '{{ $totalAgenda }}';
                                    }
                                },
                                value: {
                                    fontSize: '22px',
                                    fontWeight: 700,
                                    color: '#272727'
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: function (val) { return val + ' horas'; } } }
            }).render();

            // Barras: Atenciones por mes (el mes en curso va destacado)
            new ApexCharts(document.querySelector('#grafico_atenciones_mes'), {
                chart: { type: 'bar', height: 260, toolbar: { show: false } },
                series: [{ name: 'Atenciones', data: {!! $serieAtencionesJson !!} }],
                xaxis: { categories: {!! $categoriasAtencionesJson !!} },
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        borderRadius: 6,
                        distributed: true,
                        dataLabels: { position: 'top' }
                    }
                },
                colors: ['#e3d3ee', '#e3d3ee', '#e3d3ee', '#e3d3ee', '#e3d3ee', '#A06CC1'],
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: { fontSize: '12px', colors: ['#5b6577'] }
                },
                legend: { show: false },
                grid: { borderColor: '#f0f1f5' },
                yaxis: { min: 0, forceNiceScale: true }
            }).render();

            // Donut: Mascotas según convenio
            new ApexCharts(document.querySelector('#grafico_convenio'), {
                chart: { type: 'donut', height: 230 },
                series: {!! $serieConvenioJson !!},
                labels: {!! $etiquetasConvenioJson !!},
                colors: {!! $coloresConvenioJson !!},
                legend: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '13px',
                                    color: '#8b93a7',
                                    formatter: function () {
                                        return '{{ $convenioTotal }}';
                                    }
                                },
                                value: {
                                    fontSize: '22px',
                                    fontWeight: 700,
                                    color: '#272727'
                                }
                            }
                        }
                    }
                }
            }).render();

        });
    </script>
@endsection
