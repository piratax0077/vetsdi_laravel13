@php
    use Illuminate\Support\Str;

    $nombreEspecieMascota = strtolower(trim(
        (string) (
            optional(optional($mascota ?? $paciente ?? null)->especieMascota)->nombre
            ?? optional($mascota ?? $paciente ?? null)->especie
            ?? ''
        )
    ));

    $esCanino = Str::contains($nombreEspecieMascota, ['canin', 'perro']);
    $esFelino = Str::contains($nombreEspecieMascota, ['felin', 'gato']);

    $arcadaSuperiorMascota = $esCanino
        ? [111, 110, 109, 108, 107, 106, 105, 104, 103, 102, 101, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211]
        : [109, 108, 107, 106, null, 104, 103, 102, 101, 201, 202, 203, 204, null, 206, 207, 208, 209];
    $arcadaInferiorMascota = $esCanino
        ? [411, 410, 409, 408, 407, 406, 405, 404, 403, 402, 401, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311]
        : [409, 408, 407, null, 404, 403, 402, 401, 301, 302, 303, 304, null, 307, 308, 309];

    $baseImagenMascota = $esCanino
        ? 'images/dental/odontograma_canino'
        : 'images/dental/odontograma_felino/dientes';
    $tituloOdontogramaMascota = $esCanino ? 'Odontograma Canino' : 'Odontograma Felino';
    $piezasCaninasAnchas = ['104', '204', '304', '404'];

    $piezasEstadoMascota = [];
    $fuenteHistorialMascota = collect(($odontograma_veterinario ?? collect())->isNotEmpty()
        ? $odontograma_veterinario : ($odontograma_historial ?? $odontograma ?? []));

    foreach ($fuenteHistorialMascota as $registro) {
        $piezaCodigo = (string) data_get($registro, 'pieza', '');
        if ($piezaCodigo === '') {
            continue;
        }

        $diagnostico = Str::lower((string) (data_get($registro, 'hallazgo') ?: data_get($registro, 'diagnostico', '')));
        $tratamiento = Str::lower((string) data_get($registro, 'tratamiento', ''));

        if (!isset($piezasEstadoMascota[$piezaCodigo])) {
            $piezasEstadoMascota[$piezaCodigo] = [
                'carie' => false,
                'implante' => false,
                'hallazgo' => data_get($registro, 'hallazgo', 'Sin alteraciones'),
                'tratamiento' => data_get($registro, 'tratamiento', ''),
                'caras' => data_get($registro, 'caras', '[]'),
                'observaciones' => data_get($registro, 'observaciones', ''),
            ];
        }

        if (Str::contains($diagnostico, 'carie') || Str::contains($tratamiento, 'carie')) {
            $piezasEstadoMascota[$piezaCodigo]['carie'] = true;
        }

        if (Str::contains($tratamiento, 'implante') || Str::contains($diagnostico, 'implante')) {
            $piezasEstadoMascota[$piezaCodigo]['implante'] = true;
        }
    }
@endphp

@php
    $renderPiezaMascota = function ($piezaCodigo, $mostrarCodigoArriba = false, $indice = 0, $total = 1) use ($piezasEstadoMascota, $baseImagenMascota, $piezasCaninasAnchas) {
        $piezaCodigo = (string) $piezaCodigo;
        $estadoPieza = $piezasEstadoMascota[$piezaCodigo] ?? ['carie' => false, 'implante' => false, 'hallazgo' => 'Sin alteraciones', 'tratamiento' => '', 'caras' => '[]', 'observaciones' => ''];
        $rutaImagen = asset("{$baseImagenMascota}/d{$piezaCodigo}.png");
        $claseAncho = in_array($piezaCodigo, $piezasCaninasAnchas, true)
            ? 'odonto-mascota-img-canine'
            : 'odonto-mascota-img-regular';
        $centroArcada = max(($total - 1) / 2, 1);
        $distanciaCentro = abs($indice - $centroArcada) / $centroArcada;
        // La curvatura es sutil para no acercar los códigos de ambas arcadas.
        $desplazamientoArcada = (int) round(pow($distanciaCentro, 1.65) * 10);

        ob_start();
@endphp
<div class="odonto-mascota-pieza" style="--arc-offset: {{ $desplazamientoArcada }}px">
    @if ($mostrarCodigoArriba)
        <span class="odonto-mascota-codigo odonto-mascota-codigo-top">{{ $piezaCodigo }}</span>
    @endif
    <div class="odonto-mascota-tooth" id="t{{ $piezaCodigo }}">
        <img
            src="{{ $rutaImagen }}"
            class="img-fluid {{ $claseAncho }}"
            alt="Pieza {{ $piezaCodigo }}"
            role="button"
            data-pieza="{{ $piezaCodigo }}"
            data-caries="{{ $estadoPieza['carie'] ? '1' : '0' }}"
            data-implante="{{ $estadoPieza['implante'] ? '1' : '0' }}"
            data-hallazgo="{{ $estadoPieza['hallazgo'] }}"
            data-tratamiento="{{ $estadoPieza['tratamiento'] }}"
            data-caras='{{ is_string($estadoPieza['caras']) ? $estadoPieza['caras'] : json_encode($estadoPieza['caras']) }}'
            data-observaciones="{{ $estadoPieza['observaciones'] }}"
            onclick="mostrarDetallePiezaMascota(this)"
            onmouseenter="programarDetallePiezaMascota(this)"
            onmouseleave="cancelarDetallePiezaMascota()"
        >
        @if ($estadoPieza['carie'])
            <span class="odonto-mascota-mark odonto-mascota-caries"></span>
        @endif
        @if ($estadoPieza['implante'])
            <span class="odonto-mascota-mark odonto-mascota-implante"></span>
        @endif
    </div>
    @if (!$mostrarCodigoArriba)
        <span class="odonto-mascota-codigo">{{ $piezaCodigo }}</span>
    @endif
</div>
@php
        return ob_get_clean();
    };
@endphp

<div class="row odonto-mascota-heading">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mb-3">
        <div class="odonto-mascota-heading-copy d-inline-flex align-items-center">
            <span class="odonto-mascota-heading-icon"><i class="fas fa-tooth"></i></span>
            <span>
                <h1 class="mt-0 mb-0 f-22 odonto-mascota-title">{{ $tituloOdontogramaMascota }}</h1>
                <small class="odonto-mascota-subtitle">Seleccione una pieza para revisar su estado clínico</small>
            </span>
        </div>
        <button type="button" data-toggle="modal" data-target="#exampleModalMascota" class="btn btn-purple d-inline float-md-right mr-2">
            Ver simbología
        </button>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <div class="card-informacion odonto-mascota-card">
            <div class="card-body odonto-mascota-body">
                <div class="odonto-mascota-statusbar">
                    <span><i class="fas fa-circle odonto-dot odonto-dot-normal"></i> Pieza sin hallazgos</span>
                    <span><i class="fas fa-circle odonto-dot odonto-dot-caries"></i> Caries</span>
                    <span><i class="fas fa-caret-down odonto-dot odonto-dot-implant"></i> Implante</span>
                </div>
                <div class="odonto-mascota-mouth">
                    <span class="odonto-mascota-arc-label odonto-mascota-arc-label-top">Arcada superior</span>
                <div class="odonto-mascota-arcada odonto-mascota-arcada-top">
                    @foreach ($arcadaSuperiorMascota as $indice => $pieza)
                        @if ($pieza === null)
                            <div class="odonto-mascota-gap" aria-hidden="true"></div>
                        @else
                            {!! $renderPiezaMascota($pieza, false, $indice, count($arcadaSuperiorMascota)) !!}
                        @endif
                    @endforeach
                </div>
                <div class="odonto-mascota-occlusion"><span>Línea de oclusión</span></div>
                <span class="odonto-mascota-arc-label odonto-mascota-arc-label-bottom">Arcada inferior</span>
                <div class="odonto-mascota-arcada odonto-mascota-arcada-bottom">
                    @foreach ($arcadaInferiorMascota as $indice => $pieza)
                        @if ($pieza === null)
                            <div class="odonto-mascota-gap" aria-hidden="true"></div>
                        @else
                            {!! $renderPiezaMascota($pieza, true, $indice, count($arcadaInferiorMascota)) !!}
                        @endif
                    @endforeach
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModalMascota" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Simbología del odontograma</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <div class="media align-middle">
                            <div class="odonto-mascota-legend-box mr-2">
                                <img src="{{ asset("{$baseImagenMascota}/d101.png") }}" class="odonto-mascota-legend-img" alt="Diente">
                            </div>
                            <div class="media-body">
                                <strong>Pieza normal</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <div class="media align-middle">
                            <div class="odonto-mascota-legend-box mr-2 position-relative">
                                <img src="{{ asset("{$baseImagenMascota}/d101.png") }}" class="odonto-mascota-legend-img" alt="Caries">
                                <span class="odonto-mascota-mark odonto-mascota-caries"></span>
                            </div>
                            <div class="media-body">
                                <strong>Caries</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <div class="media align-middle">
                            <div class="odonto-mascota-legend-box mr-2 position-relative">
                                <img src="{{ asset("{$baseImagenMascota}/d101.png") }}" class="odonto-mascota-legend-img" alt="Implante">
                                <span class="odonto-mascota-mark odonto-mascota-implante"></span>
                            </div>
                            <div class="media-body">
                                <strong>Implante</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($presupuesto) && (float) $presupuesto->valor_total > 0)
@php $saldoDentalVet=max(0,(float)$presupuesto->valor_total-(float)$presupuesto->valor_abonado); @endphp
<div class="card mt-3 border-0 shadow-sm"><div class="card-body d-flex flex-wrap align-items-center justify-content-between">
    <div><h5 class="mb-1"><i class="fas fa-file-invoice-dollar text-info"></i> Presupuesto dental N.º {{ $presupuesto->id }}</h5>
        <span class="badge badge-secondary mr-2">Total ${{ number_format($presupuesto->valor_total,0,',','.') }}</span>
        <span class="badge badge-success mr-2">Abonado ${{ number_format($presupuesto->valor_abonado,0,',','.') }}</span>
        <span class="badge {{ $saldoDentalVet > 0 ? 'badge-warning' : 'badge-primary' }}">Saldo ${{ number_format($saldoDentalVet,0,',','.') }}</span>
    </div>
    @if($saldoDentalVet > 0)<button type="button" class="btn btn-info" onclick="registrarAbonoDentalVet({{ $presupuesto->id }},{{ $saldoDentalVet }})"><i class="fas fa-hand-holding-usd"></i> Registrar abono</button>@else<span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Pagado</span>@endif
</div></div>
@endif

<div class="modal fade" id="modalDetallePiezaMascota" tabindex="-1" role="dialog"
    aria-labelledby="modalDetallePiezaMascotaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content odonto-mascota-detail-modal">
            <div class="modal-header bg-purple">
                <h5 class="modal-title" id="modalDetallePiezaMascotaTitulo">
                    Detalle de la pieza
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <img id="detallePiezaMascotaImagen" src="" alt="" class="odonto-mascota-detail-img">
                        <h5 class="mt-3">Pieza <strong id="detallePiezaMascotaCodigo"></strong></h5>
                        <span class="badge badge-info">{{ $esCanino ? 'Canino' : 'Felino' }}</span>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group">
                            <label><i class="fas fa-coins text-info"></i> Prestación odontológica del tarifario UCOV</label>
                            <select id="odontoVetTarifaUco" class="form-control">
                                <option value="">Cargando catálogo odontológico...</option>
                            </select>
                            <small id="odontoVetDetalleUco" class="form-text text-muted">También puede ingresar un tratamiento manual si la prestación aún no está en el catálogo.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6"><label>Hallazgo</label><select id="odontoVetHallazgo" class="form-control">
                                @foreach(['Sin alteraciones','Placa o sarro','Gingivitis','Enfermedad periodontal','Caries','Fractura','Desgaste','Movilidad','Pieza ausente','Persistencia de deciduo','Lesión resortiva','Maloclusión','Fístula o absceso','Lesión oral','Implante'] as $h)<option>{{ $h }}</option>@endforeach
                            </select></div>
                            <div class="form-group col-md-6"><label>Tratamiento</label><select id="odontoVetTratamiento" class="form-control">
                                <option value="">Seleccione</option>@foreach(['Observación y control','Profilaxis y destartraje','Pulido','Extracción','Endodoncia','Restauración','Tratamiento periodontal','Analgesia y antibiótico','Biopsia','Cirugía oral','Implante o prótesis'] as $t)<option>{{ $t }}</option>@endforeach
                            </select></div>
                        </div>
                        <label>Caras afectadas</label><div class="mb-2" id="odontoVetCaras">
                            @foreach(['Vestibular','Palatina/Lingual','Mesial','Distal','Oclusal'] as $cara)<label class="mr-3"><input type="checkbox" value="{{ $cara }}"> {{ $cara }}</label>@endforeach
                        </div>
                        <div class="form-group"><label>Observaciones</label><textarea id="odontoVetObservaciones" class="form-control" rows="2"></textarea></div>
                        <div class="form-row align-items-end">
                            <div class="form-group col-md-5"><label>Valor para presupuesto</label><input id="odontoVetValor" type="number" min="0" class="form-control" placeholder="$"></div>
                            <div class="form-group col-md-7 text-right">
                                <button type="button" class="btn btn-outline-info" onclick="guardarPeriodontoVet()"><i class="fas fa-chart-line"></i> Periodontograma</button>
                                <button type="button" class="btn btn-success" onclick="guardarPiezaVet(false)"><i class="fas fa-save"></i> Guardar pieza</button>
                                <button type="button" class="btn btn-primary" onclick="guardarPiezaVet(true)"><i class="fas fa-file-invoice-dollar"></i> Guardar y presupuestar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <style>
        .odonto-mascota-title {
            color: #263b50;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .odonto-mascota-heading-copy { gap: .75rem; }
        .odonto-mascota-heading-icon {
            width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 13px; color: #fff; background: linear-gradient(145deg, #18b7b7, #14877f);
            box-shadow: 0 8px 18px rgba(20,135,127,.2);
        }
        .odonto-mascota-subtitle { color: #718096; font-weight: 500; }

        .odonto-mascota-card {
            border: 1px solid #d9e5e7;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(42, 66, 78, .08);
        }

        .odonto-mascota-body {
            padding: .75rem 1rem 1rem;
            background: #fff;
        }

        .odonto-mascota-statusbar {
            display: flex; justify-content: center; flex-wrap: wrap; gap: .75rem 1.35rem;
            padding: .45rem .75rem .9rem; color: #647487; font-size: .78rem; font-weight: 600;
        }
        .odonto-dot { margin-right: .25rem; font-size: .65rem; }
        .odonto-dot-normal { color: #b9c8cc; }
        .odonto-dot-caries { color: #dc3545; }
        .odonto-dot-implant { color: #17a2b8; }

        .odonto-mascota-mouth {
            position: relative;
            width: 100%;
            max-width: 1180px;
            min-width: 860px;
            margin: 0 auto;
            padding: 1.9rem 1.5rem 1.6rem;
            border: 1px solid #dce7e9;
            border-radius: 16px;
            background:
                linear-gradient(180deg, #f7fbfb 0 49.6%, #d3e3e5 49.6% 50.4%, #f7fbfb 50.4% 100%);
            box-shadow: inset 0 1px 0 #fff;
            overflow: hidden;
        }

        .odonto-mascota-mouth::before,
        .odonto-mascota-mouth::after {
            content: ''; position: absolute; left: 6%; right: 6%; height: 1px;
            background: rgba(34, 139, 139, .1); pointer-events: none;
        }
        .odonto-mascota-mouth::before { top: 47%; }
        .odonto-mascota-mouth::after { top: 52%; }

        .odonto-mascota-arc-label {
            position: absolute; z-index: 2; left: 1.15rem; color: #58717b;
            font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        }
        .odonto-mascota-arc-label-top { top: .55rem; }
        .odonto-mascota-arc-label-bottom { bottom: .55rem; }

        .odonto-mascota-arcada {
            position: relative; z-index: 2;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: .22rem;
            flex-wrap: nowrap;
        }

        .odonto-mascota-arcada-top {
            padding-bottom: .35rem;
        }

        .odonto-mascota-arcada-bottom {
            align-items: flex-start;
            padding-top: .35rem;
        }

        .odonto-mascota-arcada-top .odonto-mascota-pieza { transform: translateY(var(--arc-offset)); }
        .odonto-mascota-arcada-bottom .odonto-mascota-pieza { transform: translateY(calc(var(--arc-offset) * -1)); }

        .odonto-mascota-occlusion {
            position: relative; z-index: 3; height: 34px; margin: .55rem 5%;
            border-top: 1px dashed rgba(53, 130, 134, .3);
            border-bottom: 1px dashed rgba(53, 130, 134, .18);
            text-align: center;
        }
        .odonto-mascota-occlusion span {
            position: relative; top: -.62rem; padding: .15rem .65rem; border-radius: 999px;
            background: #fff; color: #607d86; font-size: .62rem; font-weight: 700;
        }

        .odonto-mascota-pieza {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-width: 40px;
            flex: 0 0 auto;
        }

        .odonto-mascota-arcada-top .odonto-mascota-pieza {
            min-height: 101px;
        }

        .odonto-mascota-arcada-bottom .odonto-mascota-pieza {
            min-height: 101px;
        }

        .odonto-mascota-gap {
            width: 16px;
            min-width: 16px;
            flex: 0 0 16px;
        }

        .odonto-mascota-tooth {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 76px;
            padding: .18rem .15rem;
            border: 1px solid #e5edef;
            border-radius: 11px;
            background: #fff;
        }

        .odonto-mascota-tooth img,
        .odonto-mascota-legend-img {
            display: block;
            opacity: 1;
            filter: contrast(1.9) brightness(.8) saturate(.55) drop-shadow(0 3px 2px rgba(39,58,62,.18));
        }

        .odonto-mascota-tooth img {
            cursor: pointer;
            transition: transform .2s ease, filter .2s ease;
        }

        .odonto-mascota-tooth img:hover {
            transform: scale(1.14);
            filter: contrast(2.05) brightness(.82) saturate(.72) drop-shadow(0 5px 3px rgba(25,126,122,.25));
        }

        .odonto-mascota-img-regular {
            max-height: 67px;
            width: auto;
            max-width: 34px;
        }

        .odonto-mascota-img-canine {
            max-height: 72px;
            width: auto;
            max-width: 48px;
        }

        .odonto-mascota-codigo {
            position: relative;
            z-index: 5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            color: #516777;
            font-size: .7rem;
            line-height: 1.15;
            margin-top: .35rem;
            font-weight: 700;
            background: #eef6f6; padding: .16rem .34rem; border-radius: 999px;
            white-space: nowrap;
        }

        .odonto-mascota-codigo-top {
            margin-top: 0;
            margin-bottom: .45rem;
        }

        .odonto-mascota-mark {
            position: absolute;
            inset: 50% auto auto 50%;
            transform: translate(-50%, -50%);
            border-radius: 999px;
            pointer-events: none;
        }

        .odonto-mascota-caries {
            width: 14px;
            height: 14px;
            background: rgba(220, 53, 69, .88);
            box-shadow: 0 0 0 4px rgba(220, 53, 69, .18);
        }

        .odonto-mascota-implante {
            width: 0;
            height: 0;
            border-left: 9px solid transparent;
            border-right: 9px solid transparent;
            border-top: 18px solid rgba(23, 162, 184, .9);
            border-radius: 2px;
        }

        .odonto-mascota-legend-box {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            background: #f4f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .odonto-mascota-legend-img {
            max-height: 46px;
            width: auto;
            max-width: 46px;
        }

        .odonto-mascota-detail-modal {
            border: 0;
            border-radius: 14px;
            overflow: hidden;
        }

        .odonto-mascota-detail-img {
            display: block;
            width: auto;
            max-width: 105px;
            max-height: 150px;
            margin: 0 auto;
            filter: contrast(2.4) brightness(.72);
        }

        .odonto-mascota-state {
            display: flex;
            align-items: center;
            padding: .65rem .8rem;
            margin-bottom: .5rem;
            border-radius: 9px;
            background: #f4f7fb;
            color: #364a63;
        }

        .odonto-mascota-state i {
            width: 24px;
            margin-right: .45rem;
            text-align: center;
        }

        @media (max-width: 1199px) {
            .odonto-mascota-body {
                overflow-x: auto;
            }

            .odonto-mascota-arcada {
                justify-content: flex-start;
                min-width: max-content;
                padding-bottom: .35rem;
            }
        }
    </style>

    <script>
        var detallePiezaMascotaTimer = null, piezaDentalVetActual = null;
        var odontoVetContexto = {
            mascota: {{ (int) data_get($mascota ?? null, 'id', 0) }},
            paciente: {{ (int) data_get($paciente ?? null, 'id', 0) }},
            ficha: {{ (int) ($id_ficha ?? data_get($ficha_atencion ?? null, 'id', 0)) }},
            lugar: {{ (int) (request('lugar_atencion_id') ?: request('lugar_atencion') ?: data_get($hora_medica ?? null, 'id_lugar_atencion', 0)) }},
            csrf: '{{ csrf_token() }}'
        };
        var catalogoUcoOdontoVet = [];

        function cargarCatalogoUcoOdontoVet() {
            var selector=document.getElementById('odontoVetTarifaUco');
            if(!selector) return;
            fetch('{{ route('veterinaria.odontologia.tarifario.index') }}?id_lugar_atencion='+encodeURIComponent(odontoVetContexto.lugar), {headers:{'Accept':'application/json'}})
                .then(function(r){if(!r.ok)throw new Error();return r.json();}).then(function(resp){
                    catalogoUcoOdontoVet=resp.prestaciones||[];
                    var categoria='', html='<option value="">Seleccione una prestación UCOV o use tratamiento manual</option>';
                    catalogoUcoOdontoVet.forEach(function(item){
                        if(item.categoria!==categoria){if(categoria)html+='</optgroup>';categoria=item.categoria;html+='<optgroup label="'+String(categoria).replace(/"/g,'&quot;')+'">';}
                        html+='<option value="'+item.id+'" data-nombre="'+String(item.nombre).replace(/"/g,'&quot;')+'" data-valor="'+item.valor_total+'" data-uco="'+item.uco+'" data-valor-uco="'+item.valor_uco+'">'+item.nombre+' · '+Number(item.uco).toLocaleString('es-CL')+' UCOV · $'+Math.round(item.valor_total).toLocaleString('es-CL')+'</option>';
                    });
                    if(categoria)html+='</optgroup>';selector.innerHTML=html;
                }).catch(function(){selector.innerHTML='<option value="">Catálogo UCOV no disponible; use ingreso manual</option>';});
        }

        document.addEventListener('change',function(e){
            if(e.target.id!=='odontoVetTarifaUco')return;
            var opcion=e.target.options[e.target.selectedIndex];
            if(!opcion||!opcion.value)return;
            var tratamiento=document.getElementById('odontoVetTratamiento'), nombre=opcion.dataset.nombre;
            if(tratamiento && !Array.from(tratamiento.options).some(function(o){return o.value===nombre;})) tratamiento.add(new Option(nombre,nombre));
            if(tratamiento)tratamiento.value=nombre;
            document.getElementById('odontoVetValor').value=opcion.dataset.valor||0;
            document.getElementById('odontoVetDetalleUco').textContent=opcion.dataset.uco+' UCOV × $'+Math.round(opcion.dataset.valorUco||0).toLocaleString('es-CL')+' = $'+Math.round(opcion.dataset.valor||0).toLocaleString('es-CL');
        });
        cargarCatalogoUcoOdontoVet();

        function cancelarDetallePiezaMascota() {
            if (detallePiezaMascotaTimer) {
                clearTimeout(detallePiezaMascotaTimer);
                detallePiezaMascotaTimer = null;
            }
        }

        function programarDetallePiezaMascota(elemento) {
            cancelarDetallePiezaMascota();
            detallePiezaMascotaTimer = setTimeout(function () {
                mostrarDetallePiezaMascota(elemento);
            }, 450);
        }

        function mostrarDetallePiezaMascota(elemento) {
            cancelarDetallePiezaMascota();

            var codigo = elemento.getAttribute('data-pieza') || '';
            piezaDentalVetActual = elemento;
            var tieneCaries = elemento.getAttribute('data-caries') === '1';
            var tieneImplante = elemento.getAttribute('data-implante') === '1';
            var estados = [];

            if (tieneCaries) {
                estados.push('<div class="odonto-mascota-state"><i class="fas fa-circle text-danger"></i><strong>Caries registrada</strong></div>');
            }
            if (tieneImplante) {
                estados.push('<div class="odonto-mascota-state"><i class="fas fa-tooth text-info"></i><strong>Implante registrado</strong></div>');
            }
            if (!estados.length) {
                estados.push('<div class="odonto-mascota-state"><i class="fas fa-check-circle text-success"></i><strong>Pieza sin alteraciones registradas</strong></div>');
            }

            document.getElementById('detallePiezaMascotaCodigo').textContent = codigo;
            document.getElementById('detallePiezaMascotaImagen').src = elemento.src;
            document.getElementById('detallePiezaMascotaImagen').alt = 'Pieza dental ' + codigo;
            document.getElementById('detallePiezaMascotaEstados').innerHTML = estados.join('');
            document.getElementById('odontoVetHallazgo').value = elemento.dataset.hallazgo || 'Sin alteraciones';
            document.getElementById('odontoVetTratamiento').value = elemento.dataset.tratamiento || '';
            document.getElementById('odontoVetObservaciones').value = elemento.dataset.observaciones || '';
            document.querySelectorAll('#odontoVetCaras input').forEach(function(c){ c.checked = false; });
            try { JSON.parse(elemento.dataset.caras || '[]').forEach(function(v){ var c = document.querySelector('#odontoVetCaras input[value="'+v+'"]'); if(c)c.checked=true; }); } catch(e) {}

            if (window.jQuery && jQuery.fn.modal) {
                jQuery('#modalDetallePiezaMascota').modal('show');
            }
        }

        function odontoVetFetch(url, data) {
            return fetch(url, {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':odontoVetContexto.csrf}, body:JSON.stringify(data)})
                .then(async function(r){ var d=await r.json().catch(function(){return {};}); if(!r.ok) throw new Error(d.message || d.msj || 'No fue posible guardar'); return d; });
        }

        function avisoOdontoVet(icon, text) {
            if (window.Swal) return Swal.fire({icon:icon,title:text,timer:1800,showConfirmButton:false});
            alert(text);
        }

        function datosPiezaVet() {
            return {mascota_id:odontoVetContexto.mascota, paciente_id:odontoVetContexto.paciente,
                ficha_atencion_id:odontoVetContexto.ficha, lugar_atencion_id:odontoVetContexto.lugar,
                pieza:piezaDentalVetActual.dataset.pieza, hallazgo:document.getElementById('odontoVetHallazgo').value,
                tratamiento:document.getElementById('odontoVetTratamiento').value,
                caras:Array.from(document.querySelectorAll('#odontoVetCaras input:checked')).map(function(x){return x.value;}),
                observaciones:document.getElementById('odontoVetObservaciones').value};
        }

        function guardarPiezaVet(presupuestar) {
            if (!piezaDentalVetActual || !odontoVetContexto.mascota) return avisoOdontoVet('error','No se identificó la mascota.');
            var datos=datosPiezaVet();
            odontoVetFetch('{{ route('veterinaria.odontologia.pieza.guardar') }}',datos).then(function(){
                if (!presupuestar) { avisoOdontoVet('success','Pieza dental guardada'); setTimeout(function(){location.reload();},700); return; }
                datos.valor=parseFloat(document.getElementById('odontoVetValor').value||0);
                datos.tarifa_uco_id=parseInt(document.getElementById('odontoVetTarifaUco').value||0)||null;
                if (!datos.tratamiento || datos.valor<=0) throw new Error('Seleccione tratamiento e ingrese su valor.');
                return odontoVetFetch('{{ route('veterinaria.odontologia.presupuesto.guardar') }}',datos).then(function(r){
                    avisoOdontoVet('success',r.msj+' Total: $'+Math.round(r.presupuesto.total).toLocaleString('es-CL')+' · Saldo: $'+Math.round(r.presupuesto.saldo).toLocaleString('es-CL'));
                    setTimeout(function(){location.reload();},1000);
                });
            }).catch(function(e){ avisoOdontoVet('error',e.message); });
        }

        function guardarPeriodontoVet() {
            if (!piezaDentalVetActual) return;
            var contenido='<div class="text-left"><label>Profundidad (mm)</label><input id="pvProf" type="number" min="0" max="15" class="swal2-input" value="0">'+
                '<label>Recesión (mm)</label><input id="pvRec" type="number" min="0" max="15" class="swal2-input" value="0">'+
                '<label><input id="pvSang" type="checkbox"> Sangrado</label> &nbsp; <label><input id="pvPlaca" type="checkbox"> Placa</label></div>';
            if (!window.Swal) return alert('Se requiere SweetAlert para editar el periodontograma.');
            Swal.fire({title:'Periodontograma '+('{{ $esCanino ? 'canino' : 'felino' }}')+' · pieza '+piezaDentalVetActual.dataset.pieza,html:contenido,showCancelButton:true,confirmButtonText:'Guardar',preConfirm:function(){return {profundidad:+document.getElementById('pvProf').value,recesion:+document.getElementById('pvRec').value,sangrado:document.getElementById('pvSang').checked,placa:document.getElementById('pvPlaca').checked};}})
            .then(function(r){if(!r.isConfirmed)return; var d=r.value; d.mascota_id=odontoVetContexto.mascota;d.ficha_atencion_id=odontoVetContexto.ficha;d.pieza=piezaDentalVetActual.dataset.pieza;return odontoVetFetch('{{ route('veterinaria.odontologia.periodonto.guardar') }}',d).then(function(x){avisoOdontoVet('success',x.msj);});}).catch(function(e){avisoOdontoVet('error',e.message);});
        }

        function registrarAbonoDentalVet(id, saldo) {
            Swal.fire({title:'Registrar abono',html:'<p class="text-muted">Saldo actual: $'+Math.round(saldo).toLocaleString('es-CL')+'</p><input id="abonoVetMonto" type="number" min="1" max="'+saldo+'" class="swal2-input" placeholder="Monto"><select id="abonoVetMetodo" class="swal2-select"><option>Efectivo</option><option>Débito</option><option>Crédito</option><option>Transferencia</option><option>Bono veterinario</option></select>',showCancelButton:true,confirmButtonText:'Registrar',preConfirm:function(){return {monto:+document.getElementById('abonoVetMonto').value,metodo_pago:document.getElementById('abonoVetMetodo').value};}})
            .then(function(r){if(!r.isConfirmed)return; return odontoVetFetch('{{ url('Profesional/Paciente/Odontologia_veterinaria/presupuesto') }}/'+id+'/abono',r.value).then(function(x){avisoOdontoVet('success',x.msj+' Saldo: $'+Math.round(x.presupuesto.saldo).toLocaleString('es-CL'));setTimeout(function(){location.reload();},900);});}).catch(function(e){avisoOdontoVet('error',e.message);});
        }
    </script>
@endonce
