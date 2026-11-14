<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    @php
        // Formato elegido por el profesional en "Plantilla de exámenes"; sin plantilla guardada sale como siempre
        $plantilla = $cuerpo['plantilla'] ?? \App\Models\PlantillaPdfProfesional::predeterminada('examenes');
        $color = $plantilla['color'];
        $mostrar = $plantilla['mostrar'];
        $alineacionMarca = ['izquierda' => 'left', 'centro' => 'center', 'derecha' => 'right'][$plantilla['posicion_logo']] ?? 'left';
        $letra = \App\Models\PlantillaPdfProfesional::tamanoLetraPx($plantilla);
    @endphp
    <style>
        @page { margin: 28px 38px 30px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #263445;
            font-family: DejaVu Sans, sans-serif;
            font-size: {{ $letra }}px;
        }
        table { width: 100%; border-collapse: collapse; }
        .orden-pagina {
            position: relative;
            height: 970px;
            page-break-after: always;
        }
        .orden-pagina:last-child { page-break-after: auto; }
        .cabecera { border-bottom: 2px solid {{ $color }}; padding-bottom: 9px; }
        .logo-celda { width: 31%; vertical-align: middle; text-align: {{ $alineacionMarca }}; }
        .logo { max-height: 60px; max-width: 200px; }
        .marca-vet {
            color: {{ $color }};
            font-size: 26px;
            font-weight: bold;
            letter-spacing: .6px;
        }
        .marca-sub {
            margin-top: 2px;
            color: #65328c;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .profesional {
            width: 69%;
            vertical-align: top;
            padding-left: 18px;
            border-left: 3px solid {{ $color }};
            line-height: 1.35;
        }
        .profesional strong { font-size: 13px; color: {{ $color }}; }
        .titulo {
            margin: 16px 0 10px;
            color: {{ $color }};
            font-size: 20px;
            text-align: center;
            text-transform: uppercase;
        }
        .tipo {
            margin: 0 0 12px;
            color: #65328c;
            font-size: 14px;
            text-align: center;
        }
        .datos {
            margin-bottom: 13px;
            border: 1px solid #d9e2e8;
            background: #f7fafb;
        }
        .datos td { padding: 6px 8px; vertical-align: top; }
        .datos .etiqueta { width: 15%; color: #53657a; font-weight: bold; }
        .fecha { margin: 7px 0 12px; text-align: right; font-weight: bold; }
        .instruccion { margin: 0 0 7px; font-size: 11px; }
        .examenes th {
            padding: 7px 8px;
            color: #fff;
            background: {{ $color }};
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        .examenes td {
            padding: 8px;
            border-bottom: 1px solid #d9e2e8;
            vertical-align: top;
        }
        .examenes tbody tr:nth-child(even) { background: #f7fafb; }
        .examen-item { padding: 8px 0 8px 10px; border-left: 3px solid {{ $color }}; margin-bottom: 9px; }
        .examen-item-nombre { font-size: {{ $letra + 2 }}px; font-weight: bold; }
        .examen-item-dato { margin-top: 3px; }
        .examen-item-dato span { color: #53657a; }
        .pie-texto { margin-top: 4px; color: #657789; font-size: 8px; }
        .pie-contenedor {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
        }
        .pie {
            border-top: 1px solid #c9d5db;
            padding-top: 10px;
        }
        .qr-celda,
        .firma-qr-celda {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .qr { width: 92px; height: 92px; }
        .firma-qr {
            display: block;
            width: 92px;
            height: 92px;
            margin: 0 auto;
        }
        .firma-detalle {
            margin-top: 3px;
            font-size: 8px;
            line-height: 1.45;
            text-align: center;
        }
        .firma-detalle strong {
            display: block;
            font-size: 9px;
        }
        .firma-linea {
            margin: 23px auto 5px;
            width: 230px;
            border-top: 1px solid #53657a;
        }
        .validacion {
            margin-top: 4px;
            color: #657789;
            font-size: 8px;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
@foreach ($cuerpo['detalle_orden'] as $tipoExamen => $detalle)
    <section class="orden-pagina">
        <div class="cabecera">
            <table>
                <tr>
                    <td class="logo-celda" @if(empty($mostrar['profesional'])) style="width:100%;" @endif>
                        @if(!empty($plantilla['logo']))
                            <img class="logo" src="{{ $plantilla['logo'] }}" alt="Logo">
                        @elseif($plantilla['texto_marca'] !== '')
                            <div class="marca-vet">{{ $plantilla['texto_marca'] }}</div>
                            <div class="marca-sub">Salud veterinaria digital</div>
                        @endif
                    </td>
                    @if(!empty($mostrar['profesional']))
                    <td class="profesional">
                        <strong>{{ $cuerpo['array_profesional']['nombre'] }}</strong><br>
                        Médico veterinario
                        @if(!empty($cuerpo['array_profesional']['especialidad']))
                            · {{ $cuerpo['array_profesional']['especialidad'] }}
                        @endif
                        <br>
                        RUT: {{ $cuerpo['array_profesional']['rut'] }}<br>
                        RCV: {{ $cuerpo['array_profesional']['num_colegio'] ?: 'No informado' }}<br>
                        {{ $cuerpo['array_lugar_atencion']['nombre'] }}<br>
                        {{ $cuerpo['array_lugar_atencion']['direccion'] }}
                    </td>
                    @endif
                </tr>
            </table>
        </div>

        <h1 class="titulo">{{ $plantilla['titulo'] }}</h1>
        <h2 class="tipo">{{ $tipoExamen }}</h2>

        @if(!empty($mostrar['paciente']))
        <table class="datos">
            <tr>
                <td class="etiqueta">Paciente:</td>
                <td>{{ $cuerpo['array_paciente']['nombre'] }}</td>
                <td class="etiqueta">Identificación:</td>
                <td>{{ $cuerpo['array_paciente']['rut'] }}</td>
            </tr>
            <tr>
                <td class="etiqueta">Sexo:</td>
                <td>{{ $cuerpo['array_paciente']['sexo'] ?: 'No informado' }}</td>
                <td class="etiqueta">Dirección:</td>
                <td>{{ $cuerpo['array_paciente']['direccion'] }}</td>
            </tr>
        </table>
        @endif

        @if(!empty($mostrar['fecha']))
            <div class="fecha">Fecha: {{ $cuerpo['array_ficha_atencion']['created_at'] }}</div>
        @endif
        <p class="instruccion">Ruego practicar los siguientes exámenes:</p>

        @if($plantilla['formato'] === 'lista')
            {{-- Formato lista: un examen debajo de otro, sin tabla --}}
            @foreach ($detalle as $examen)
                <div class="examen-item">
                    <div class="examen-item-nombre">{{ $examen['examen'] }}</div>
                    @if(!empty($examen['sospecha']))
                        <div class="examen-item-dato"><span>Sospecha diagnóstica:</span> {!! nl2br(e($examen['sospecha'])) !!}</div>
                    @endif
                    @if(!empty($examen['contraste']))
                        <div class="examen-item-dato">Con contraste</div>
                    @endif
                    <div class="examen-item-dato">
                        <span>Prioridad:</span> {{ $examen['prioridad'] }}
                        · <span>Lado:</span> {{ $examen['otro'] ?: 'No aplica' }}
                        · <span>Código:</span> {{ !empty($examen['codigo']) && $examen['codigo'] !== 'NULL' ? $examen['codigo'] : '-' }}
                    </div>
                </div>
            @endforeach
        @else
        <table class="examenes">
            <thead>
                <tr>
                    <th style="width:52%;">Examen</th>
                    <th style="width:18%;">Prioridad</th>
                    <th style="width:15%;">Lado</th>
                    <th style="width:15%;">Código</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalle as $examen)
                    <tr>
                        <td>
                            <strong>{{ $examen['examen'] }}</strong>
                            @if(!empty($examen['sospecha']))
                                <br>Sospecha diagnóstica: {!! nl2br(e($examen['sospecha'])) !!}
                            @endif
                            @if(!empty($examen['contraste']))
                                <br>Con contraste
                            @endif
                        </td>
                        <td>{{ $examen['prioridad'] }}</td>
                        <td>{{ $examen['otro'] ?: 'No aplica' }}</td>
                        <td>{{ !empty($examen['codigo']) && $examen['codigo'] !== 'NULL' ? $examen['codigo'] : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div class="pie-contenedor">
            @if(!empty($mostrar['firmas']))
            <table class="pie">
                <tr>
                    <td class="qr-celda">
                        <img class="qr"
                            src="data:image/svg+xml;base64,{{ base64_encode($cuerpo['array_ficha_atencion']['qr']) }}"
                            alt="Código QR de validación">
                        <div style="font-size:8px;">Validar documento</div>
                    </td>
                    <td class="firma-qr-celda">
                        @if(!empty($cuerpo['array_profesional']['qr']))
                            <img class="firma-qr"
                                src="data:image/svg+xml;base64,{{ base64_encode($cuerpo['array_profesional']['qr']) }}"
                                alt="Firma SDI del profesional">
                        @else
                            <div class="firma-linea"></div>
                        @endif
                        <div class="firma-detalle">
                            <strong>Firma Digital Avanzada SDI</strong>
                            Dr(a). {{ $cuerpo['array_profesional']['nombre'] }}<br>
                            Médico veterinario
                        </div>
                    </td>
                </tr>
            </table>
            @endif

            @if(!empty($mostrar['codigo']))
            <div class="validacion">
                Documento verificable mediante el código QR. Identificador:
                {{ $cuerpo['array_ficha_atencion']['token'] }}
            </div>
            @endif
            @if($plantilla['pie'] !== '')
                <div class="pie-texto">{{ $plantilla['pie'] }}</div>
            @endif
        </div>
    </section>
@endforeach
</body>
</html>
