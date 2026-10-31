@extends('template.adm_cm.template')

@section('content')
    @php
        // Valores de respaldo: la pantalla se ve completa aunque todavía no lleguen datos del centro
        $mes = $mes ?? 'actual';
        $idLugar = $idLugar ?? null;
        $lugares = $lugares ?? collect();
        $totalPacientes = $totalPacientes ?? 0;
        $atencionesRealizadas = $atencionesRealizadas ?? 0;
        $ingresoTotal = $ingresoTotal ?? 0;
        $cobrosPendientes = $cobrosPendientes ?? 0;
        $asistieron = $asistieron ?? 0;
        $noAsistieron = $noAsistieron ?? 0;
        $canceladasPaciente = $canceladasPaciente ?? 0;
        $canceladasLugar = $canceladasLugar ?? 0;
        $totalAgenda = $totalAgenda ?? ($asistieron + $noAsistieron + $canceladasPaciente + $canceladasLugar);
        $convenios = $convenios ?? collect();
        $documentos = $documentos ?? [
            'Recetas veterinarias' => 0,
            'Certificados veterinarios' => 0,
            'Consentimientos informados' => 0,
            'Órdenes de examen' => 0,
        ];
        $atencionesMes = $atencionesMes ?? collect(range(5, 0))->map(function ($resta) {
            return ['mes' => ucfirst(str_replace('.', '', now()->subMonths($resta)->locale('es')->shortMonthName)), 'total' => 0];
        });

        // Estadísticas veterinarias: mismos cálculos de la operación del establecimiento (si algo falla, quedan en 0)
        $etiquetasVeterinarias = ['Mascotas hospitalizadas', 'Plazas habilitadas', 'Reservas de hoy', 'Atenciones completadas', 'Obligaciones pendientes (CLP)', 'Obligaciones vencidas', 'Convocatorias abiertas'];
        $resumenVeterinario = $resumenVeterinario ?? null;
        if ($resumenVeterinario === null) {
            try {
                $institucionVet = app(\App\Services\OperacionVeterinaria::class)->institucion(request());
                $baseVet = fn (string $tabla) => \Illuminate\Support\Facades\DB::table($tabla)->where('id_institucion', $institucionVet->id);
                $resumenVeterinario = [
                    'Mascotas hospitalizadas' => $baseVet('vet_internaciones')->where('estado', 'hospitalizada')->count(),
                    'Plazas habilitadas' => $baseVet('vet_plazas')->where('activa', 1)->count(),
                    'Reservas de hoy' => $baseVet('vet_reservas_servicios')->whereDate('inicio', today())->whereNotIn('estado', ['cancelada', 'no_asiste'])->count(),
                    'Atenciones completadas' => $baseVet('vet_reservas_servicios')->where('estado', 'completada')->count(),
                    'Obligaciones pendientes (CLP)' => $baseVet('vet_obligaciones')->where('estado', 'pendiente')->sum('monto'),
                    'Obligaciones vencidas' => $baseVet('vet_obligaciones')->where('estado', 'pendiente')->whereDate('vencimiento', '<', today())->count(),
                    'Convocatorias abiertas' => $baseVet('vet_convocatorias')->where('estado', 'abierta')->count(),
                ];
            } catch (\Throwable $e) {
                $resumenVeterinario = array_fill_keys($etiquetasVeterinarias, 0);
            }
        }
        $iconosVeterinarios = ['activity', 'grid', 'calendar', 'check-circle', 'file-text', 'alert-circle', 'users'];

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

    <!--****Container Completo****-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <a href="{{ route('adm_cm.home') }}" class="text-muted f-12 d-print-none d-inline-block mb-2"
                                onclick="if (window.history.length > 1) { window.history.back(); return false; }">
                                <i class="feather icon-arrow-left" aria-hidden="true"></i> Atrás
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!--TÍTULO Y FILTROS-->
            <div class="row">
                <div class="col-12">
                    <div class="mb-4">
                        <div>
                            <div class="d-flex flex-wrap align-items-md-center justify-content-between">
                                <div class="mb-3 mb-md-0">
                                    <h3 class="reportes-titulo mb-0">Estadísticas y reportes del centro</h3>
                                </div>
                                <form method="GET" action="{{ route('adm_cm.reporte_estadisticas') }}" class="d-flex flex-wrap filtros-reportes" id="form_filtros_reportes">
                                    @if(request()->filled('id_institucion'))
                                        <input type="hidden" name="id_institucion" value="{{ (int) request('id_institucion') }}">
                                    @endif
                                    <div class="filtro-item filtro-item-icon">
                                        <i class="feather icon-calendar filtro-icon"></i>
                                        <select class="form-control select-filtro-reportes" id="filtro_mes_reportes" name="mes" onchange="this.form.submit()">
                                            <option value="actual" @selected($mes === 'actual')>Mes actual</option>
                                            @foreach($nombresMeses as $numero => $nombreMes)
                                                <option value="{{ $numero + 1 }}" @selected((string) $mes === (string) ($numero + 1))>{{ $nombreMes }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="filtro-item filtro-item-icon d-none">
                                        <i class="feather icon-map-pin filtro-icon"></i>
                                        <select class="form-control select-filtro-reportes" id="filtro_lugar_reportes" name="lugar" onchange="this.form.submit()">
                                            <option value="todos" @selected(!$idLugar)>Todas las sucursales</option>
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

            <!--Métricas principales (Recaudado hoy y Recaudado en el mes aún son solo diseño)-->
            @php
                $metricasPrincipales = [
                    ['icono' => 'credit-card', 'valor' => '$0', 'etiqueta' => 'Recaudado hoy'],
                    ['icono' => 'trending-up', 'valor' => '$0', 'etiqueta' => 'Recaudado en el mes'],
                    ['icono' => 'check-circle', 'valor' => number_format($resumenVeterinario['Atenciones completadas'] ?? 0, 0, ',', '.'), 'etiqueta' => 'Atenciones completadas'],
                    ['icono' => 'user-plus', 'valor' => '0', 'etiqueta' => 'Pacientes nuevos'],
                    ['icono' => 'activity', 'valor' => number_format($resumenVeterinario['Mascotas hospitalizadas'] ?? 0, 0, ',', '.'), 'etiqueta' => 'Mascotas hospitalizadas'],
                    ['icono' => 'grid', 'valor' => number_format($resumenVeterinario['Plazas habilitadas'] ?? 0, 0, ',', '.'), 'etiqueta' => 'Plazas habilitadas'],
                    ['icono' => 'calendar', 'valor' => number_format($resumenVeterinario['Reservas de hoy'] ?? 0, 0, ',', '.'), 'etiqueta' => 'Reservas de hoy'],
                    ['icono' => 'x-circle', 'valor' => number_format((int) $canceladasPaciente + (int) $canceladasLugar, 0, ',', '.'), 'etiqueta' => 'Citas anuladas'],
                ];
            @endphp
            <div class="row escritorio-indicadores">
                @foreach ($metricasPrincipales as $metrica)
                    <div class="col-6 col-xl-3">
                        <div class="tarjeta-indicador tarjeta-indicador--morado">
                            <span class="tarjeta-indicador-icono"><i class="feather icon-{{ $metrica['icono'] }}"></i></span>
                            <strong class="tarjeta-indicador-valor">{{ $metrica['valor'] }}</strong>
                            <span class="tarjeta-indicador-etiqueta">{{ $metrica['etiqueta'] }}</span>
                        </div>
                    </div>
                @endforeach
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
                                        <span class="flex-grow-1">Canceladas por el centro</span>
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
                            <h6 class="mb-0 f-18 text-dark">Atenciones realizadas en el mes</h6>
                            <span class="badge badge-light">6 meses hasta el período seleccionado</span>
                        </div>
                        <div class="card-body">
                            <div id="grafico_atenciones_mes"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!--DOCUMENTOS EMITIDOS / MASCOTAS SEGÚN CONVENIO-->
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
                            <h6 class="mb-0 f-18 text-dark">Otras métricas</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex text-muted small mb-2">
                                <span class="flex-grow-1">Métrica</span>
                                <span>Total</span>
                            </div>
                            @php
                                $otrasMetricas = [
                                    ['icono' => 'file-text', 'color' => 'bg-c-purple', 'etiqueta' => 'Obligaciones pendientes', 'valor' => '$' . number_format($resumenVeterinario['Obligaciones pendientes (CLP)'] ?? 0, 0, ',', '.')],
                                    ['icono' => 'alert-circle', 'color' => 'bg-c-red', 'etiqueta' => 'Obligaciones vencidas', 'valor' => number_format($resumenVeterinario['Obligaciones vencidas'] ?? 0, 0, ',', '.')],
                                    ['icono' => 'users', 'color' => 'bg-c-teal', 'etiqueta' => 'Convocatorias abiertas', 'valor' => number_format($resumenVeterinario['Convocatorias abiertas'] ?? 0, 0, ',', '.')],
                                ];
                            @endphp
                            @foreach ($otrasMetricas as $otraMetrica)
                                <div class="doc-list-item">
                                    <div class="d-flex align-items-center">
                                        <span class="doc-icon {{ $otraMetrica['color'] }} text-white"><i class="feather icon-{{ $otraMetrica['icono'] }}"></i></span>
                                        {{ $otraMetrica['etiqueta'] }}
                                    </div>
                                    <span class="font-weight-bolder">{{ $otraMetrica['valor'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--****Cierre Container Completo****-->
@endsection

@section('page-script')
    {{-- ApexCharts ya viene cargado por la plantilla del centro --}}
    <script>
        $(document).ready(function () {

            // Dona con el total al centro. Si no hay datos queda un anillo gris, no un espacio en blanco
            function crearDona(selector, serie, etiquetas, colores, etiquetaTotal, total, sufijoTooltip) {
                var contenedor = document.querySelector(selector);
                var sinDatos = serie.reduce(function (suma, valor) { return suma + valor; }, 0) === 0;

                if (sinDatos) {
                    contenedor.style.pointerEvents = 'none';
                }

                new ApexCharts(contenedor, {
                    chart: { type: 'donut', height: 230 },
                    series: sinDatos ? [1] : serie,
                    labels: sinDatos ? ['Sin datos'] : etiquetas,
                    colors: sinDatos ? ['#eef0f4'] : colores,
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
                                        label: etiquetaTotal,
                                        fontSize: '13px',
                                        color: '#8b93a7',
                                        formatter: function () {
                                            return String(total);
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
                    tooltip: {
                        enabled: !sinDatos,
                        y: { formatter: function (valor) { return valor + sufijoTooltip; } }
                    }
                }).render();
            }

            // Donut: Asistencia vs Ausentismo
            crearDona(
                '#grafico_asistencia_ausentismo',
                {!! $serieAsistenciaJson !!},
                ['Asistieron', 'No asistieron', 'Canceladas por tutor', 'Canceladas por el centro'],
                ['#A06CC1', '#ff5252', '#ffba57', '#72B02C'],
                'Total agendadas',
                {{ (int) $totalAgenda }},
                ' horas'
            );

            // Barras: Atenciones por mes (el mes en curso va destacado)
            var serieAtenciones = {!! $serieAtencionesJson !!};
            var pocasAtenciones = Math.max.apply(null, serieAtenciones) < 4;

            new ApexCharts(document.querySelector('#grafico_atenciones_mes'), {
                chart: { type: 'bar', height: 260, toolbar: { show: false } },
                series: [{ name: 'Atenciones', data: serieAtenciones }],
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
                // Con pocas atenciones (o ninguna) la escala llega a 4 para que los números salgan enteros
                yaxis: {
                    min: 0,
                    max: pocasAtenciones ? 4 : undefined,
                    tickAmount: pocasAtenciones ? 4 : undefined,
                    forceNiceScale: true,
                    labels: { formatter: function (valor) { return Math.round(valor); } }
                }
            }).render();

            // Donut: Mascotas según convenio
            crearDona(
                '#grafico_convenio',
                {!! $serieConvenioJson !!},
                {!! $etiquetasConvenioJson !!},
                {!! $coloresConvenioJson !!},
                'Total',
                {{ $convenioTotal }},
                ''
            );

        });
    </script>
@endsection
