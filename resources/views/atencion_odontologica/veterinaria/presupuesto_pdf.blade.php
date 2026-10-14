<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#243849}h1{font-size:20px}table{width:100%;border-collapse:collapse;margin:15px 0}th,td{padding:8px;border-bottom:1px solid #ddd;text-align:left}th{background:#e9f5f5}.right{text-align:right}</style></head><body>
<h1>Presupuesto odontológico veterinario N.º {{ $presupuesto['id'] }}</h1>
<p>Mascota: <strong>{{ $mascota->nombre }}</strong> · Especie: {{ \App\Support\DenticionVeterinaria::especie($mascota) }}</p>
<p>Profesional: {{ $profesional->nombre }} {{ $profesional->apellido_uno }} {{ $profesional->apellido_dos }}</p>
<p>Ficha N.º {{ $presupuesto['ficha_atencion_id'] }} · {{ $presupuesto['aprobado'] ? 'Aprobado' : 'Pendiente de aprobación' }}</p>
<table><thead><tr><th>Pieza</th><th>Tratamiento</th><th>Estado del trabajo</th><th class="right">Valor</th></tr></thead><tbody>
@foreach($presupuesto['trabajos'] as $trabajo)<tr><td>{{ $trabajo['pieza'] }}</td><td>{{ $trabajo['tratamiento'] }}</td><td>{{ str_replace('_',' ',$trabajo['estado_trabajo']) }}</td><td class="right">${{ number_format($trabajo['valor'],0,',','.') }}</td></tr>@endforeach
</tbody></table>
<p class="right">Total: <strong>${{ number_format($presupuesto['total'],0,',','.') }}</strong><br>Pagado: ${{ number_format($presupuesto['abonado'],0,',','.') }}<br>Saldo: <strong>${{ number_format($presupuesto['saldo'],0,',','.') }}</strong></p>
<h3>Pagos registrados</h3><table><thead><tr><th>Fecha</th><th>Medio de pago</th><th class="right">Monto</th></tr></thead><tbody>@forelse($presupuesto['pagos'] as $pago)<tr><td>{{ $pago->fecha_pago }}</td><td>{{ $pago->metodo_pago }}</td><td class="right">${{ number_format($pago->total,0,',','.') }}</td></tr>@empty<tr><td colspan="3">Sin pagos registrados.</td></tr>@endforelse</tbody></table>
</body></html>
