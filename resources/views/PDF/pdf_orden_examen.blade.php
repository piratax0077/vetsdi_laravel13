<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 28px 38px 30px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #263445;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }
        table { width: 100%; border-collapse: collapse; }
        .orden-pagina {
            position: relative;
            height: 970px;
            page-break-after: always;
        }
        .orden-pagina:last-child { page-break-after: auto; }
        .cabecera { border-bottom: 2px solid #168c83; padding-bottom: 9px; }
        .logo-celda { width: 31%; vertical-align: middle; }
        .marca-vet {
            color: #168c83;
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
            border-left: 3px solid #168c83;
            line-height: 1.35;
        }
        .profesional strong { font-size: 13px; color: #137c74; }
        .titulo {
            margin: 16px 0 10px;
            color: #168c83;
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
            background: #168c83;
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
                    <td class="logo-celda">
                        <div class="marca-vet">VET SDI</div>
                        <div class="marca-sub">Salud veterinaria digital</div>
                    </td>
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
                </tr>
            </table>
        </div>

        <h1 class="titulo">Orden de exámenes veterinarios</h1>
        <h2 class="tipo">{{ $tipoExamen }}</h2>

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

        <div class="fecha">Fecha: {{ $cuerpo['array_ficha_atencion']['created_at'] }}</div>
        <p class="instruccion">Ruego practicar los siguientes exámenes:</p>

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

        <div class="pie-contenedor">
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

            <div class="validacion">
                Documento verificable mediante el código QR. Identificador:
                {{ $cuerpo['array_ficha_atencion']['token'] }}
            </div>
        </div>
    </section>
@endforeach
</body>
</html>
