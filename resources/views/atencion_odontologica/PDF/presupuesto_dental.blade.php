<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto odontológico veterinario</title>
    <style>
        @page { margin: 28px 38px 138px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #263445;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
        }
        table { width: 100%; border-collapse: collapse; }
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
            padding-left: 18px;
            border-left: 3px solid #168c83;
            line-height: 1.4;
            vertical-align: top;
        }
        .profesional strong { color: #137c74; font-size: 13px; }
        .titulo {
            margin: 15px 0 4px;
            color: #168c83;
            font-size: 19px;
            text-align: center;
            text-transform: uppercase;
        }
        .referencia {
            margin-bottom: 12px;
            color: #65328c;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }
        .datos {
            margin-bottom: 12px;
            border: 1px solid #d9e2e8;
            background: #f7fafb;
        }
        .datos td { width: 50%; padding: 7px 9px; vertical-align: top; }
        .datos tr + tr td { border-top: 1px solid #d9e2e8; }
        .datos td + td { border-left: 1px solid #d9e2e8; }
        .etiqueta {
            color: #657789;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .valor { margin-top: 2px; color: #263445; font-size: 10px; font-weight: bold; }
        .secundario { margin-top: 2px; color: #526477; line-height: 1.35; }
        .detalle { margin-top: 3px; }
        .detalle thead { display: table-header-group; }
        .detalle tr { page-break-inside: avoid; }
        .detalle th {
            padding: 7px 6px;
            color: #fff;
            background: #168c83;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }
        .detalle td {
            padding: 7px 6px;
            border-bottom: 1px solid #d9e2e8;
            vertical-align: top;
        }
        .detalle tbody tr:nth-child(even) { background: #f7fafb; }
        .centro { text-align: center; }
        .numero { text-align: right; white-space: nowrap; }
        .tipo-item {
            display: inline-block;
            margin-top: 3px;
            padding: 1px 5px;
            color: #65328c;
            border: 1px solid #d8cae4;
            border-radius: 7px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .totales { width: 270px; margin: 13px 0 0 auto; }
        .totales td { padding: 6px 8px; border: 1px solid #d9e2e8; }
        .total-final td {
            color: #fff;
            background: #65328c;
            border-color: #65328c;
            font-size: 12px;
            font-weight: bold;
        }
        .nota {
            margin-top: 12px;
            padding: 8px 10px;
            color: #526477;
            border-left: 3px solid #168c83;
            background: #f7fafb;
            font-size: 8px;
            line-height: 1.45;
        }
        .firma-tutor {
            margin-top: 12px;
            padding: 9px 10px;
            border: 1px solid #d9e2e8;
            page-break-inside: avoid;
        }
        .firma-tutor-titulo {
            margin-bottom: 6px;
            color: #65328c;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .firma-tutor-qr { width: 76px; text-align: center; vertical-align: middle; }
        .firma-tutor-qr img { width: 66px; height: 66px; }
        .firma-tutor-datos { padding-left: 12px; vertical-align: middle; line-height: 1.5; }
        .firma-pendiente {
            padding: 12px;
            color: #657789;
            border: 1px dashed #aebdc7;
            background: #f7fafb;
            text-align: center;
        }
        .sin-items {
            padding: 20px;
            color: #657789;
            border: 1px solid #d9e2e8;
            text-align: center;
        }
        .pie-contenedor {
            position: fixed;
            right: 0;
            bottom: -118px;
            left: 0;
            height: 108px;
        }
        .pie { border-top: 1px solid #c9d5db; padding-top: 7px; }
        .pie td { width: 50%; text-align: center; vertical-align: top; }
        .qr { width: 70px; height: 70px; }
        .pie-titulo { margin-top: 1px; font-size: 7px; font-weight: bold; }
        .pie-detalle { margin-top: 2px; color: #657789; font-size: 6.5px; line-height: 1.3; }
        .token {
            margin-top: 3px;
            color: #657789;
            font-size: 6px;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
@php
    $filasPresupuesto = [];

    foreach ($odontograma ?? [] as $odonto) {
        if ((int) ($odonto->presupuesto ?? 0) !== 1) continue;
        $valor = (float) ($odonto->valor ?? 0);
        $filasPresupuesto[] = [
            'ubicacion' => $odonto->pieza ?: 'General',
            'diagnostico' => $odonto->diagnostico ?: 'Sin diagnóstico registrado',
            'tratamiento' => $odonto->tratamiento ?: ($odonto->descripcion ?: 'Tratamiento odontológico'),
            'cantidad' => 1,
            'valor' => $valor,
            'total' => $valor,
            'tipo' => (int) ($odonto->urgencia ?? 0) === 1 ? 'Urgencia' : 'Odontología general',
            'avance' => max(0, min(100, (int) ($odonto->grado_avance ?? 0))),
        ];
    }

    $gruposPresupuesto = [
        ['datos' => $maxilar_superior_gral_diagnostico ?? [], 'tipo' => 'Maxilar superior'],
        ['datos' => $maxilar_superior_gral_tratamiento ?? [], 'tipo' => 'Maxilar superior'],
        ['datos' => $maxilar_superior_gral_diagnosticos_endo ?? [], 'tipo' => 'Maxilar superior'],
        ['datos' => $maxilar_superior_gral_tratamientos_endo ?? [], 'tipo' => 'Maxilar superior'],
        ['datos' => $maxilar_inferior_gral_diagnostico ?? [], 'tipo' => 'Maxilar inferior'],
        ['datos' => $maxilar_inferior_gral_tratamiento ?? [], 'tipo' => 'Maxilar inferior'],
        ['datos' => $maxilar_inferior_gral_diagnosticos_endo ?? [], 'tipo' => 'Maxilar inferior'],
        ['datos' => $maxilar_inferior_gral_tratamientos_endo ?? [], 'tipo' => 'Maxilar inferior'],
        ['datos' => $boca_completa_gral_diagnostico ?? [], 'tipo' => 'Boca completa'],
        ['datos' => $boca_completa_gral_tratamiento ?? [], 'tipo' => 'Boca completa'],
        ['datos' => $boca_completa_gral_diagnostico_endo ?? [], 'tipo' => 'Boca completa'],
        ['datos' => $boca_completa_gral_tratamiento_endo ?? [], 'tipo' => 'Boca completa'],
    ];

    foreach ($gruposPresupuesto as $grupo) {
        foreach ($grupo['datos'] as $registro) {
            if ((int) ($registro->presupuesto ?? 0) !== 1) continue;
            $valor = (float) ($registro->valor ?? 0);
            $filasPresupuesto[] = [
                'ubicacion' => $registro->localizacion ?: $grupo['tipo'],
                'diagnostico' => 'Prestación por grupo dental',
                'tratamiento' => $registro->diagnostico_tratamiento ?: 'Tratamiento odontológico',
                'cantidad' => 1,
                'valor' => $valor,
                'total' => $valor,
                'tipo' => $grupo['tipo'],
                'avance' => null,
            ];
        }
    }

    foreach ($insumos ?? [] as $insumo) {
        if ((int) ($insumo->presupuesto ?? 0) !== 1) continue;
        $cantidad = max(1, (int) ($insumo->cantidad ?? 1));
        $valor = (float) ($insumo->valor ?? 0);
        $filasPresupuesto[] = [
            'ubicacion' => 'Insumo',
            'diagnostico' => $insumo->tipo_insumo ?: 'Insumo clínico',
            'tratamiento' => trim(($insumo->insumos ?? '').' '.($insumo->nombre_marca ?? '')),
            'cantidad' => $cantidad,
            'valor' => $valor,
            'total' => $valor * $cantidad,
            'tipo' => (int) ($insumo->urgencia ?? 0) === 1 ? 'Insumo de urgencia' : 'Insumo',
            'avance' => null,
        ];
    }

    $totalPresupuesto = collect($filasPresupuesto)->sum('total');
    $nombreTutor = trim(($paciente->nombres ?? '').' '.($paciente->apellido_uno ?? '').' '.($paciente->apellido_dos ?? ''));
    $nombreMascota = $mascota->nombre ?? 'Sin registro';
    $especieMascota = optional(optional($mascota)->especieMascota)->nombre ?? 'Especie sin registro';
    $razaMascota = optional(optional($mascota)->razaMascota)->nombre ?? null;
@endphp

<header class="cabecera">
    <table>
        <tr>
            <td class="logo-celda">
                <div class="marca-vet">VET SDI</div>
                <div class="marca-sub">Salud veterinaria digital</div>
            </td>
            <td class="profesional">
                <strong>{{ $array_profesional['nombre'] }}</strong><br>
                Médico veterinario
                @if(!empty($array_profesional['especialidad']))
                    - {{ $array_profesional['especialidad'] }}
                @endif
                <br>
                RUT: {{ $array_profesional['rut'] ?: 'No informado' }}<br>
                {{ $lugarAtencion->nombre ?? 'Lugar de atención no informado' }}
            </td>
        </tr>
    </table>
</header>

<h1 class="titulo">Presupuesto odontológico veterinario</h1>
<div class="referencia">Ficha N° {{ $ficha->id }} - Emitido el {{ date('d/m/Y H:i') }}</div>

<table class="datos">
    <tr>
        <td>
            <div class="etiqueta">Tutor responsable</div>
            <div class="valor">{{ $nombreTutor ?: 'Sin registro' }}</div>
            <div class="secundario">RUT: {{ $paciente->rut ?: 'No informado' }}</div>
        </td>
        <td>
            <div class="etiqueta">Paciente veterinario</div>
            <div class="valor">{{ $nombreMascota }}</div>
            <div class="secundario">
                {{ $especieMascota }}
                @if($razaMascota) - {{ $razaMascota }} @endif
                @if(!empty(optional($mascota)->chip))<br>Chip: {{ $mascota->chip }}@endif
            </div>
        </td>
    </tr>
</table>

@if(count($filasPresupuesto))
    <table class="detalle">
        <thead>
            <tr>
                <th style="width:5%;" class="centro">N°</th>
                <th style="width:13%;">Pieza / sector</th>
                <th>Diagnóstico y prestación</th>
                <th style="width:8%;" class="numero">Cant.</th>
                <th style="width:14%;" class="numero">Valor unit.</th>
                <th style="width:14%;" class="numero">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filasPresupuesto as $fila)
                <tr>
                    <td class="centro">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $fila['ubicacion'] }}</strong><br>
                        <span class="tipo-item">{{ $fila['tipo'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $fila['diagnostico'] }}</strong><br>
                        {{ $fila['tratamiento'] }}
                        @if($fila['avance'] !== null)
                            <br><span style="color:#657789; font-size:8px;">Avance registrado: {{ $fila['avance'] }}%</span>
                        @endif
                    </td>
                    <td class="numero">{{ $fila['cantidad'] }}</td>
                    <td class="numero">${{ number_format($fila['valor'], 0, ',', '.') }}</td>
                    <td class="numero"><strong>${{ number_format($fila['total'], 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr class="total-final">
            <td>TOTAL PRESUPUESTO</td>
            <td class="numero">${{ number_format($totalPresupuesto, 0, ',', '.') }}</td>
        </tr>
    </table>
@else
    <div class="sin-items">No existen prestaciones incorporadas al presupuesto.</div>
@endif

<section class="firma-tutor">
    <div class="firma-tutor-titulo">Aceptación y firma del tutor responsable</div>
    @if(!empty($firmaTutor) && ($firmaTutor['estado'] ?? '') === 'firmado')
        <table>
            <tr>
                <td class="firma-tutor-qr">
                    <img src="data:image/svg+xml;base64,{{ base64_encode($firmaTutor['qr']) }}" alt="QR de firma del tutor">
                </td>
                <td class="firma-tutor-datos">
                    <strong>Firmado electrónicamente por {{ $firmaTutor['nombre'] }}</strong><br>
                    RUT: {{ $firmaTutor['rut'] ?: 'No informado' }}<br>
                    Fecha y hora: {{ $firmaTutor['fecha'] }}<br>
                    La autenticidad de esta aceptación puede verificarse escaneando el código QR.
                </td>
            </tr>
        </table>
    @else
        <div class="firma-pendiente">
            Pendiente de firma del tutor. La aceptación se realiza desde el escritorio del tutor,
            en la sección Mis documentos.
        </div>
    @endif
</section>

<div class="nota">
    Este presupuesto es informativo y corresponde a las prestaciones registradas en la ficha indicada.
    Los valores pueden variar si durante la atención se requieren procedimientos o insumos adicionales.
</div>

<footer class="pie-contenedor">
    <table class="pie">
        <tr>
            <td>
                <img class="qr"
                    src="data:image/svg+xml;base64,{{ base64_encode($array_ficha_atencion['qr']) }}"
                    alt="Código QR de validación del documento">
                <div class="pie-titulo">Validar presupuesto</div>
                <div class="pie-detalle">Escanee el código para comprobar la autenticidad del documento.</div>
            </td>
            <td>
                <img class="qr"
                    src="data:image/svg+xml;base64,{{ base64_encode($array_profesional['qr']) }}"
                    alt="Firma digital del profesional">
                <div class="pie-titulo">Firma Digital Avanzada SDI</div>
                <div class="pie-detalle">Dr(a). {{ $array_profesional['nombre'] }}</div>
            </td>
        </tr>
    </table>
    <div class="token">Identificador de validación: {{ $array_ficha_atencion['token'] }}</div>
</footer>
</body>
</html>
