<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 28px 34px 34px; }
        body { font-family: DejaVu Sans, sans-serif; color: #27364a; font-size: 10px; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .header { border-bottom: 4px solid #168f86; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { color: #168f86; font-size: 24px; font-weight: bold; }
        .title { color: #75429a; font-size: 17px; font-weight: bold; margin-top: 3px; }
        .controlled { display: inline-block; margin-top: 6px; padding: 4px 9px; color: #a21b36; border: 1px solid #dc7185; border-radius: 4px; font-weight: bold; }
        .meta, .items, .signature { width: 100%; border-collapse: collapse; }
        .meta { margin-bottom: 15px; }
        .meta td { width: 50%; padding: 6px 8px; border: 1px solid #dce4ea; vertical-align: top; }
        .label { color: #697789; font-size: 8px; text-transform: uppercase; }
        .value { font-size: 11px; font-weight: bold; margin: 2px 0; }
        .items th { background: #168f86; color: #fff; padding: 7px; text-align: left; }
        .items td { padding: 7px; border-bottom: 1px solid #dce4ea; vertical-align: top; }
        .items tr:nth-child(even) td { background: #f5f8fa; }
        .signature { margin-top: 24px; }
        .signature td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .qr { width: 78px; height: 78px; }
        .line { border-top: 1px solid #64748b; padding-top: 5px; margin: 0 18px; }
        .token { margin-top: 10px; color: #718096; font-size: 8px; word-break: break-all; }
        .footer { margin-top: 18px; border-top: 1px solid #cbd6dd; padding-top: 6px; color: #738092; font-size: 8px; }
    </style>
</head>
<body>
@forelse($cuerpo['recomendacion'] as $receta)
    @php
        $mascota = $cuerpo['mascota'];
        $tutor = $receta->paciente;
        $profesional = $receta->profesional;
        $lugar = $receta->lugar_atencion;
        $controlId = (int) $receta->control;
        $controlNombre = $cuerpo['controles_receta'][$controlId] ?? ('Tipo de receta '.$controlId);
        $esControlada = in_array($controlId, [1, 2, 3, 4, 5], true);
    @endphp
    <section class="page">
        <div class="header">
            <div class="brand">VET SDI</div>
            <div class="title">Receta veterinaria</div>
            @if($esControlada)
                <div class="controlled">RECETA CONTROLADA · {{ mb_strtoupper($controlNombre) }}</div>
            @else
                <div>{{ $controlNombre }}</div>
            @endif
        </div>

        <table class="meta">
            <tr>
                <td>
                    <div class="label">Mascota</div>
                    <div class="value">{{ $mascota->nombre ?? 'Sin registro' }}</div>
                    <div>{{ optional($mascota->especieMascota)->nombre ?? $mascota->especie ?? 'Especie sin registro' }}</div>
                    <div>Raza: {{ optional($mascota->razaMascota)->nombre ?? 'Sin registro' }} · Sexo: {{ $mascota->sexo ?? 'Sin registro' }}</div>
                    @if(!empty($mascota->chip))<div>Chip: {{ $mascota->chip }}</div>@endif
                </td>
                <td>
                    <div class="label">Tutor responsable</div>
                    <div class="value">{{ trim(($tutor->nombres ?? '').' '.($tutor->apellido_uno ?? '').' '.($tutor->apellido_dos ?? '')) }}</div>
                    <div>RUT: {{ $tutor->rut ?? 'Sin registro' }}</div>
                    <div>Teléfono: {{ $tutor->telefono_uno ?? 'Sin registro' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Médico/a veterinario/a</div>
                    <div class="value">{{ trim(($profesional->nombre ?? '').' '.($profesional->apellido_uno ?? '').' '.($profesional->apellido_dos ?? '')) }}</div>
                    <div>RUT: {{ $profesional->rut ?? 'Sin registro' }}@if(!empty($profesional->num_colegio)) · Registro: {{ $profesional->num_colegio }}@endif</div>
                </td>
                <td>
                    <div class="label">Centro y fecha de emisión</div>
                    <div class="value">{{ $lugar->nombre ?? 'Centro veterinario' }}</div>
                    <div>{{ optional($cuerpo['fecha_atencion'])->format('d-m-Y H:i') ?? date('d-m-Y H:i') }}</div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 24%">Medicamento</th>
                    <th style="width: 16%">Presentación</th>
                    <th>Indicación / posología</th>
                    <th style="width: 15%">Periodo</th>
                    <th style="width: 13%">Cantidad</th>
                </tr>
            </thead>
            <tbody>
            @forelse($receta->detalle as $detalle)
                <tr>
                    <td><strong>{{ $detalle['producto'] ?? 'Sin registro' }}</strong>@if(!empty($detalle['farmaco']))<br><small>{{ $detalle['farmaco'] }}</small>@endif</td>
                    <td>{{ $detalle['presentacion'] ?? 'Sin registro' }}</td>
                    <td>{{ $detalle['posologia'] ?? 'Sin registro' }}@if(!empty($detalle['via_administracion']))<br>Vía: {{ $detalle['via_administracion'] }}@endif</td>
                    <td>{{ $detalle['periodo'] ?? 'Sin registro' }}</td>
                    <td>{{ $detalle['cantidad_compra'] ?? $detalle['cantidad'] ?? 'Sin registro' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center">No hay medicamentos registrados en esta receta.</td></tr>
            @endforelse
            </tbody>
        </table>

        <table class="signature">
            <tr>
                <td>
                    <img class="qr" src="data:image/svg+xml;base64,{{ base64_encode($receta->qr->documento) }}">
                    <div>Validación del documento</div>
                </td>
                <td><div class="line">Firma médico/a veterinario/a</div></td>
                <td>
                    <img class="qr" src="data:image/svg+xml;base64,{{ base64_encode($receta->qr_prof->profesional) }}">
                    <div>Firma digital profesional</div>
                </td>
            </tr>
        </table>

        <div class="token">Código de validación: {{ $receta->qr->token }}</div>
        <div class="footer">Documento veterinario asociado a la Ficha Veterinaria Única N.º {{ $receta->ficha_atencion->id }}.</div>
    </section>
@empty
    <p>No existen recetas veterinarias registradas para esta atención.</p>
@endforelse
</body>
</html>
