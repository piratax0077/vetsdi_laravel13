<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Notificación al SAG</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #263238; font-size: 11px; }
        h1 { color: #168f86; font-size: 20px; }
        .bloque { border: 1px solid #ccd5dd; padding: 12px; margin: 12px 0; overflow-wrap: break-word; }
        .firma { margin-top: 24px; text-align: right; page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Enfermedades de Denuncia Obligatoria (EDO)</h1>
    <div class="bloque">
        <strong>Denunciante:</strong> {{ $firma['profesional'] }}<br>
        <strong>RUT:</strong> {{ $firma['rut'] }}<br>
        <strong>Calidad:</strong> {{ $datos['calidad'] ?? '' }}
    </div>
    <div class="bloque">
        <strong>Ubicación:</strong> {{ implode(', ', array_filter([$datos['direccion'] ?? '', $datos['comuna'] ?? '', $datos['region'] ?? ''])) }}
    </div>
    <div class="bloque">
        <strong>Animal:</strong> {{ $datos['animal'] ?? 'Sin identificación' }}<br>
        <strong>Especie:</strong> {{ $datos['especie'] ?? '' }}<br>
        <strong>Enfermedad sospechada:</strong> {{ $datos['enfermedad'] ?? '' }}<br>
        <strong>Fecha:</strong> {{ $datos['fecha'] ?? '' }}<br>
        <strong>Animales enfermos:</strong> {{ $datos['enfermos'] ?? 0 }}<br>
        <strong>Animales muertos:</strong> {{ $datos['muertos'] ?? 0 }}<br>
        <strong>Signos clínicos y antecedentes epidemiológicos:</strong><br>
        {!! nl2br(e($datos['sintomas'] ?? '')) !!}
    </div>
    <div class="firma">
        @if(!empty($firma['qr']))
            <img width="120" height="120" alt="QR de firma" src="data:image/svg+xml;base64,{{ base64_encode($firma['qr']) }}"><br>
            <strong>Firma Digital Avanzada SDI</strong><br>
            {{ $firma['profesional'] }}
        @else
            <strong>Firma digital pendiente</strong>
        @endif
    </div>
</body>
</html>
