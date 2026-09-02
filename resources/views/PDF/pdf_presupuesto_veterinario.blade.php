<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto veterinario</title>
    <style>
        @page { margin: 30px 36px; }
        body { font-family: DejaVu Sans, sans-serif; color: #27364a; font-size: 11px; }
        .header { border-bottom: 3px solid #168f86; padding-bottom: 14px; margin-bottom: 18px; }
        .brand { color: #168f86; font-size: 25px; font-weight: bold; }
        .subtitle { color: #75429a; font-size: 17px; font-weight: bold; margin-top: 4px; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .meta td { width: 50%; padding: 6px 8px; border: 1px solid #dce4ea; }
        .label { color: #697789; font-size: 9px; text-transform: uppercase; }
        .value { font-weight: bold; margin-top: 2px; }
        table.items { width: 100%; border-collapse: collapse; }
        .items th { padding: 8px; background: #168f86; color: white; text-align: left; }
        .items td { padding: 8px; border-bottom: 1px solid #dce4ea; vertical-align: top; }
        .number { text-align: right; white-space: nowrap; }
        .totals { width: 290px; margin-left: auto; margin-top: 16px; border-collapse: collapse; }
        .totals td { padding: 6px 8px; border: 1px solid #dce4ea; }
        .total { color: #75429a; font-size: 14px; font-weight: bold; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; border-top: 1px solid #cbd6dd; padding-top: 7px; color: #738092; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">VET SDI</div>
        <div class="subtitle">Presupuesto veterinario</div>
    </div>

    <table class="meta">
        <tr>
            <td>
                <div class="label">Tutor responsable</div>
                <div class="value">{{ trim($paciente->nombres.' '.$paciente->apellido_uno.' '.$paciente->apellido_dos) }}</div>
                <div>RUT: {{ $paciente->rut }}</div>
            </td>
            <td>
                <div class="label">Mascota</div>
                <div class="value">{{ $mascota->nombre ?? 'Sin registro' }}</div>
                <div>
                    {{ optional(optional($mascota)->especieMascota)->nombre ?? 'Especie sin registro' }}
                    @if(!empty(optional($mascota)->chip)) · Chip: {{ $mascota->chip }} @endif
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Profesional</div>
                <div class="value">{{ trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos) }}</div>
                <div>RUT: {{ $profesional->rut }}</div>
            </td>
            <td>
                <div class="label">Lugar y fecha</div>
                <div class="value">{{ $lugarAtencion->nombre ?? 'Lugar de atención' }}</div>
                <div>{{ $fecha->format('d-m-Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 5%;">Nº</th>
                <th>Diagnóstico / tratamiento</th>
                <th style="width: 13%;" class="number">Valor</th>
                <th style="width: 9%;" class="number">Cantidad</th>
                <th style="width: 15%;" class="number">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    $valorUnitario = (float) ($item->valor_tratamiento ?? $item->valor ?? 0);
                    $cantidad = (int) ($item->cantidad ?: 1);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->diagnostico ?: 'Sin diagnóstico' }}</strong><br>
                        {{ $item->tratamiento ?: 'Sin tratamiento' }}
                    </td>
                    <td class="number">${{ number_format($valorUnitario, 0, ',', '.') }}</td>
                    <td class="number">{{ $cantidad }}</td>
                    <td class="number">${{ number_format($valorUnitario * $cantidad, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="number">${{ number_format($subtotal, 0, ',', '.') }}</td></tr>
        <tr><td>IVA (19%)</td><td class="number">${{ number_format($iva, 0, ',', '.') }}</td></tr>
        <tr><td class="total">TOTAL</td><td class="number total">${{ number_format($total, 0, ',', '.') }}</td></tr>
    </table>

    <div class="footer">
        Presupuesto asociado a la ficha veterinaria Nº {{ $ficha->id }}. Valores sujetos a confirmación clínica.
    </div>
</body>
</html>
