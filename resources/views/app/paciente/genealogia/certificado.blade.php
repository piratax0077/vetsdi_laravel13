<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificado genealógico - {{ $mascota->nombre }}</title>
    {{-- misma tipografía e íconos que el resto del sistema --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('css/genealogia.css') }}?v={{ @filemtime(public_path('css/genealogia.css')) }}">
</head>
<body class="certificado-pagina">
@php
    $g = $mascota->genealogia;
@endphp
<main class="certificado genealogy-shell">
    <header class="certificado-cabecera">
        <span class="certificado-marca">VET SDI &middot; VETERCHILE</span>
        <h1>Certificado genealógico</h1>
        <p>Pedigrí digital y registro familiar de {{ $mascota->nombre }}</p>
    </header>

    <section class="certificado-datos">
        <div><span>Mascota</span><strong>{{ $mascota->nombre }}</strong></div>
        <div><span>Especie</span><strong>{{ $mascota->tipo_especie }}</strong></div>
        <div><span>N° de registro</span><strong>{{ optional($g)->numero_registro ?: 'Sin registro' }}</strong></div>
        <div><span>Criador</span><strong>{{ optional($g)->criador ?: 'No informado' }}</strong></div>
    </section>

    @include('app.paciente.genealogia.partials.arbol', ['editable' => false, 'etiquetaPrincipal' => 'Mascota certificada'])

    <section class="certificado-observaciones">
        <h2>Observaciones</h2>
        <p>{{ optional($g)->observaciones ?: 'Sin observaciones.' }}</p>
    </section>

    <footer class="certificado-pie">
        Emitido digitalmente por VET SDI &middot; VETERCHILE el {{ now()->format('d/m/Y') }}
    </footer>
</main>

<div class="certificado-acciones no-imprimir">
    <button type="button" class="certificado-boton es-destacado" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / Guardar PDF</button>
    <a class="certificado-boton" href="{{ route('mascotas.genealogia.show', $mascota) }}"><i class="fas fa-arrow-left"></i> Volver</a>
</div>
</body>
</html>
