{{-- Saludo de los escritorios: "Hola, Nombre" y debajo el texto de bienvenida.
     Recibe 'texto_saludo' y, opcional, 'nombre_saludo'. Si no llega el nombre
     se usa el del usuario conectado. Va dentro de .page-header.encabezado-escritorio --}}
@php
    $nombreSaludo = trim((string) ($nombre_saludo ?? ''));

    if ($nombreSaludo === '' && Auth::check()) {
        $nombreSaludo = Auth::user()->partesDelNombre()[0];
    }
@endphp
<div class="page-header-title">
    <h3>Hola{{ $nombreSaludo !== '' ? ',' : '' }} <span class="text-purple">{{ $nombreSaludo }}</span></h3>
    <p>{{ $texto_saludo ?? 'Bienvenido/a a tu escritorio' }}</p>
</div>
