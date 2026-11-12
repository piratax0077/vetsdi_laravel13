<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 18mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color: #272727; }
        table { width: 100%; border-collapse: collapse; }
        .cabecera { border-bottom: 1.5pt solid #7b35a5; margin-bottom: 16.5pt; }
        .cabecera td { padding-bottom: 9pt; vertical-align: middle; }
        .logo { max-width: 180pt; max-height: 45pt; }
        .fecha { color: #6c757d; font-size: 9.75pt; text-align: right; }
        h1 { font-size: 16.5pt; margin: 0 0 12pt; }
        .centrado { text-align: center; }
        .contenido { line-height: 1.6; overflow-wrap: break-word; }
        .contenido-consentimiento { min-height: 158mm; }
        p { margin: 0 0 6pt; }
        ul, ol { margin: 0 0 7.5pt; padding-left: 16.5pt; }
        .firmas { margin-top: 32pt; page-break-inside: avoid; }
        .firmas td { width: 50%; padding: 0 8pt; vertical-align: bottom; }
        .firma { border-top: 1pt solid #6c757d; padding-top: 5pt; }
        .detalle { color: #6c757d; font-size: 8.5pt; }
        .huella { font-size: 8pt; }
        .qr { width: 57pt; height: 57pt; margin-bottom: 5pt; }
    </style>
</head>
<body>
    <table class="cabecera"><tr>
        <td><img class="logo" src="{{ $logo }}" alt="Veterchile"></td>
        <td class="fecha">{{ $fecha }}</td>
    </tr></table>
    <h1 @class(['centrado' => $consentimiento])>{{ $titulo }}</h1>
    <div @class(['contenido', 'contenido-consentimiento' => $consentimiento])>
        {!! $contenido !!}
        @if($adjunto)
            <p>Archivo original: {{ $adjunto['nombre'] }}</p>
            <p class="huella">SHA-256 del archivo original: {{ $adjunto['sha256'] }}</p>
        @endif
    </div>
    @if ($consentimiento || $firmaProfesional)
        <table class="firmas"><tr>
            @foreach ([
                ['Firma del tutor / responsable', $firmaTutor, $qrTutor, $tutor],
                ['Firma del profesional veterinario', $firmaProfesional, $qrProfesional, $firmaProfesional],
            ] as [$etiqueta, $firma, $qr, $persona])
                @continue(!$consentimiento && $loop->first)
                <td>
                    @if($qr)
                        <img class="qr" src="{{ $qr }}" alt="QR de verificación">
                    @endif
                    <div class="firma">{{ $etiqueta }}</div>
                    <div>{{ $persona['nombre'] ?? '' }}</div>
                    <div class="detalle">
                        @if($vistaPrevia && $firma)
                            RUT {{ $persona['rut'] ?? '' }} · QR verificable al emitir el documento
                        @elseif($firma)
                            Firmado electrónicamente el {{ $firma['fecha'] }} · RUT {{ $firma['rut'] }}
                        @else
                            RUT {{ $persona['rut'] ?? '' }} · Pendiente de firma
                        @endif
                    </div>
                </td>
            @endforeach
        </tr></table>
    @endif
</body>
</html>
