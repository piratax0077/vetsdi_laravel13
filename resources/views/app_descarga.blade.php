<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Descarga la app · VeterChile</title>

    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    {{-- Tipografia de la marca --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/descarga_app.css') }}">
</head>

<body>
    @php
        // Enlace que se codifica en el QR: descarga directa del instalador.
        $enlaceApp = asset('app/download/sdipass.apk');
    @endphp

    <div class="descarga-pantalla">
        <div class="descarga-tarjeta">

            {{-- Panel izquierdo: presentacion de la marca --}}
            <div class="descarga-marca">
                <a href="{{ url()->previous() }}" class="descarga-volver">
                    <span>&larr;</span> <span>Volver</span>
                </a>

                <div class="descarga-logo">
                    <img src="{{ asset('images/logo_pais_vertical.png') }}" alt="VeterChile">
                </div>

                <h1 class="descarga-marca-nombre">VeterChile</h1>
                <p class="descarga-marca-texto">Gestiona a tus mascotas y accede al escritorio desde cualquier lugar.</p>
                <p class="descarga-marca-lema">La salud de tu mascota, ahora en tu bolsillo.</p>

                {{-- Linea de pulso decorativa --}}
                <div class="descarga-pulso" aria-hidden="true">
                    <svg viewBox="0 0 420 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 20 H120 L135 20 L150 6 L168 34 L184 12 L198 26 L212 20 H420"
                              stroke="rgba(255,255,255,.65)" stroke-width="2.5"
                              stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            {{-- Panel derecho: descarga y pasos --}}
            <div class="descarga-detalle">
                <h2 class="descarga-titulo">Descarga la app</h2>
                <p class="descarga-bajada">Escanea el código con la cámara de tu teléfono o descárgala directamente desde Google Play.</p>

                <div class="descarga-medios">
                    {{-- Codigo QR --}}
                    <div class="descarga-qr-caja">
                        <div class="descarga-qr" id="descargaQr"></div>
                        <span class="descarga-qr-pie">Apunta la cámara de tu celular al código</span>
                    </div>

                    {{-- Botones de tiendas --}}
                    <div class="descarga-tiendas">
                        <a href="{{ $enlaceApp }}" class="descarga-tienda descarga-tienda-activa">
                            <svg class="descarga-tienda-icono" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path fill="#00d3ff" d="M47 30 296 256 47 482c-9 6-19 1-19-11V41c0-12 10-17 19-11z"/>
                                <path fill="#00f076" d="M47 30c-4-2-8-2-11 0l224 214 40-40z"/>
                                <path fill="#ff3a44" d="M47 482l253-174-40-40-224 214c3 2 7 2 11 0z"/>
                                <path fill="#ffd500" d="M420 224l-60-33-60 65 60 65 60-33c17-10 17-54 0-64z"/>
                            </svg>
                            <span class="descarga-tienda-texto">
                                <small>Disponible en</small>
                                <strong>Google Play</strong>
                            </span>
                        </a>

                        <span class="descarga-tienda descarga-tienda-inactiva">
                            <svg class="descarga-tienda-icono" viewBox="0 0 384 512" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor">
                                <path d="M318 268c-1-58 47-86 49-88-27-39-68-45-83-45-35-4-68 21-86 21s-45-20-74-20c-38 1-73 22-93 56-40 68-10 170 28 226 19 27 41 58 70 57 28-1 39-18 73-18s44 18 74 17c30 0 49-28 68-55 21-31 30-61 30-63-1-1-58-22-59-88zM261 74c15-19 26-45 23-71-22 1-49 15-65 34-14 16-27 43-24 68 25 2 50-13 66-31z"/>
                            </svg>
                            <span class="descarga-tienda-texto">
                                <small>Próximamente en</small>
                                <strong>App Store</strong>
                            </span>
                        </span>
                    </div>
                </div>

                {{-- Pasos de instalacion --}}
                <ol class="descarga-pasos">
                    <li class="descarga-paso">Escanea el código QR o pulsa Google Play desde tu celular.</li>
                    <li class="descarga-paso">Instala la app VeterChile en tu dispositivo Android.</li>
                    <li class="descarga-paso">Ábrela e inicia sesión con tu usuario y contraseña de siempre.</li>
                </ol>
            </div>

        </div>
    </div>

    {{-- Generacion del codigo QR --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var contenedor = document.getElementById('descargaQr');
            if (contenedor && window.QRCode) {
                new QRCode(contenedor, {
                    text: @json($enlaceApp),
                    width: 300,
                    height: 300,
                    colorDark: '#272727',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });
            }
        });
    </script>
</body>

</html>
