@extends('template.adm_cm.template')

@section('content')
    @php
        $cantidad = fn($valor) => $valor === null ? 'No disponible' : number_format($valor,0,',','.');
        $moneda = fn($valor) => $valor === null ? 'No disponible' : '$'.number_format($valor,0,',','.');
        $porcentaje = fn($valor,$total) => $valor === null || $total === null ? '—' : ($total > 0 ? round($valor/$total*100,1) : 0);
        $serieAsistenciaJson = json_encode(array_map(fn($valor)=>(int)$valor,[$asistieron,$noAsistieron,$canceladasPaciente,$canceladasLugar,$canceladasSinOrigen,$pendientesAgenda]));
        $serieAtencionesJson = json_encode($atencionesMes->pluck('total')->all());
        $categoriasAtencionesJson = json_encode($atencionesMes->pluck('mes')->all(),JSON_UNESCAPED_UNICODE);
        $nombresMeses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    @endphp

    <!--****Container Completo****-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="mb-3"><a class="btn btn-outline-info btn-sm" href="{{ route('vet.pdf.index',['id_institucion'=>$institucion->id]) }}">Archivo de reportes PDF del centro</a> <a class="btn btn-info btn-sm" target="_blank" rel="noopener" href="{{ route('adm_cm.reporte_estadisticas',request()->except('formato')+['id_institucion'=>$institucion->id,'formato'=>'pdf']) }}">Generar reporte PDF</a></div>

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
                                    <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
                                    @if($contexto)<input type="hidden" name="contexto" value="{{ $contexto }}">@endif
                                    <div class="filtro-item filtro-item-icon">
                                        <i class="feather icon-calendar filtro-icon"></i>
                                        <select class="form-control select-filtro-reportes" id="filtro_mes_reportes" name="mes" onchange="this.form.submit()">
                                            <option value="actual" @selected($mes === 'actual')>Mes actual</option>
                                            @foreach($nombresMeses as $numero => $nombreMes)
                                                <option value="{{ $numero + 1 }}" @selected((string) $mes === (string) ($numero + 1))>{{ $nombreMes }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="filtro-item">
                                        <label class="floating-label-activo-sm sr-only" for="filtro_anio_reportes">Año</label>
                                        <select id="filtro_anio_reportes" class="form-control select-filtro-reportes" name="anio" onchange="this.form.submit()">
                                            @for($opcionAnio=max(now()->year,$anio);$opcionAnio>=min(now()->year-5,$anio);$opcionAnio--)
                                            <option value="{{ $opcionAnio }}" @selected($anio === $opcionAnio)>{{ $opcionAnio }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="filtro-item filtro-item-icon">
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

            <div class="alert alert-light border mb-3">
                <strong>{{ $institucion->nombre }}</strong> · Período: {{ $inicio->format('d/m/Y') }} a {{ $fin->subDay()->format('d/m/Y') }}.
                Recaudación de bonos de consulta con pago confirmado, sin devoluciones; el precio de una reserva no acredita pago.
                «Hoy» corresponde al {{ $fechaHoy }}. Las atenciones incluyen consultas finalizadas y servicios completados.
                Pacientes nuevos: mascotas con su primera vinculación o actividad registrada en el ámbito seleccionado.
                Hospitalización, plazas, reservas de hoy, obligaciones y convocatorias muestran su estado actual.
            </div>
            @if($idLugar)
            <div class="alert alert-info">Filtrando por sucursal: los servicios e inventario de hospitalización no tienen sucursal registrada y quedan sin desglose. Las obligaciones y convocatorias corresponden al establecimiento completo.</div>
            @endif
            @if($fuentesNoDisponibles)
            <div class="alert alert-warning">Algunas fuentes no están disponibles: {{ implode(', ',$fuentesNoDisponibles) }}. Sus indicadores no se sustituyen por cero.</div>
            @endif
            <!-- Métricas calculadas para el establecimiento y período seleccionados -->
            @php
                $metricasPrincipales = [
                    ['icono' => 'credit-card', 'valor' => $moneda($recaudadoHoy), 'etiqueta' => 'Recaudado hoy'],
                    ['icono' => 'trending-up', 'valor' => $moneda($ingresoTotal), 'etiqueta' => 'Recaudado en el mes'],
                    ['icono' => 'check-circle', 'valor' => $cantidad($atencionesRealizadas), 'etiqueta' => 'Atenciones completadas'],
                    ['icono' => 'user-plus', 'valor' => $cantidad($pacientesNuevos), 'etiqueta' => 'Pacientes nuevos'],
                    ['icono' => 'activity', 'valor' => $cantidad($resumenVeterinario['Mascotas hospitalizadas']), 'etiqueta' => 'Mascotas hospitalizadas'],
                    ['icono' => 'grid', 'valor' => $cantidad($resumenVeterinario['Plazas habilitadas']), 'etiqueta' => 'Plazas habilitadas'],
                    ['icono' => 'calendar', 'valor' => $cantidad($resumenVeterinario['Reservas de hoy']), 'etiqueta' => 'Reservas de hoy'],
                    ['icono' => 'x-circle', 'valor' => $cantidad($totalAgenda === null ? null : $canceladasPaciente + $canceladasLugar + $canceladasSinOrigen), 'etiqueta' => 'Citas anuladas'],
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
                                title="Citas clínicas y reservas de servicios del período. Las tasas se calculan solo sobre asistencias e inasistencias registradas."></i>
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
                                        <span class="font-weight-bolder mr-2">{{ $cantidad($asistieron) }}</span>
                                        <span class="text-muted small">({{ $porcentaje($asistieron, $totalAgenda) }}%)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="kpi-dot bg-c-red"></span>
                                        <span class="flex-grow-1">No asistieron</span>
                                        <span class="font-weight-bolder mr-2">{{ $cantidad($noAsistieron) }}</span>
                                        <span class="text-muted small">({{ $porcentaje($noAsistieron, $totalAgenda) }}%)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="kpi-dot bg-c-yellow"></span>
                                        <span class="flex-grow-1">Canceladas por tutor</span>
                                        <span class="font-weight-bolder mr-2">{{ $cantidad($canceladasPaciente) }}</span>
                                        <span class="text-muted small">({{ $porcentaje($canceladasPaciente, $totalAgenda) }}%)</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="kpi-dot bg-c-green"></span>
                                        <span class="flex-grow-1">Canceladas por el centro</span>
                                        <span class="font-weight-bolder mr-2">{{ $cantidad($canceladasLugar) }}</span>
                                        <span class="text-muted small">({{ $porcentaje($canceladasLugar, $totalAgenda) }}%)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2"><div class="col-12 text-muted small">
                                Canceladas sin origen registrado: {{ $cantidad($canceladasSinOrigen) }} · Pendientes o sin cierre: {{ $cantidad($pendientesAgenda) }}.
                                Las citas pasadas sin cierre no se marcan automáticamente como inasistencias.
                            </div></div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="stat-mini-card">
                                        <p class="text-muted mb-1 small">Tasa de asistencia</p>
                                        <h3 class="text-c-green">{{ $porcentaje($asistieron, $totalEvaluadas) }}%</h3>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-mini-card">
                                        <p class="text-muted mb-1 small">Tasa de ausentismo</p>
                                        <h3 class="text-c-red">{{ $porcentaje($noAsistieron, $totalEvaluadas) }}%</h3>
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
                                    <span class="font-weight-bolder">{{ $cantidad($cantidadDocumento) }}</span>
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
                                    ['icono' => 'credit-card', 'color' => 'bg-c-purple', 'etiqueta' => 'Cobros sin confirmación en el período', 'valor' => $moneda($cobrosPendientes)],
                                    ['icono' => 'file-text', 'color' => 'bg-c-purple', 'etiqueta' => 'Obligaciones pendientes', 'valor' => $moneda($resumenVeterinario['Obligaciones pendientes (CLP)'])],
                                    ['icono' => 'alert-circle', 'color' => 'bg-c-red', 'etiqueta' => 'Obligaciones vencidas', 'valor' => $cantidad($resumenVeterinario['Obligaciones vencidas'])],
                                    ['icono' => 'users', 'color' => 'bg-c-teal', 'etiqueta' => 'Convocatorias abiertas', 'valor' => $cantidad($resumenVeterinario['Convocatorias abiertas'])],
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
                if (!contenedor) return;
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
                ['Asistieron', 'No asistieron', 'Canceladas por tutor', 'Canceladas por el centro', 'Canceladas sin origen', 'Pendientes o sin cierre'],
                ['#A06CC1', '#ff5252', '#ffba57', '#72B02C', '#8b93a7', '#17a2b8'],
                'Total agendadas',
                @json($totalAgenda === null ? 'No disponible' : $totalAgenda),
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


        });
    </script>
@endsection
