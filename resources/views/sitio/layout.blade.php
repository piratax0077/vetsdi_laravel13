<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vet SDI')</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Nunito+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sitio_web.css') }}?v=20260819-2">
    @yield('page-styles')
</head>
<body class="@yield('body-class')">
    <header class="sw-nav">
        <div class="sw-wrap sw-nav-inner">
            <a class="sw-brand" href="{{ route('sitio.inicio') }}">
                <img src="{{ asset('images/logo_pais_horizontal.svg') }}" alt="Vet SDI">
            </a>
            <nav class="sw-links">
                <a href="{{ route('sitio.inicio') }}#profesionales">Profesionales</a>
                <a href="{{ route('sitio.inicio') }}#clinicas">Clínicas</a>
                <a href="{{ route('sitio.cuidados-preventivos') }}">Consejos para tu mascota</a>
            </nav>
            <div class="sw-nav-actions">
                @auth
                    <a class="sw-btn sw-btn-ghost" href="{{ route('sitio.configurar') }}">Configurar mi página</a>
                    <a class="sw-btn sw-btn-ghost" href="{{ route('sitio.articulos.index') }}">Mis artículos</a>
                    <a class="sw-btn sw-btn-solid" href="{{ route('home.ingreso') }}">Ir al escritorio</a>
                @else
                    <a class="sw-btn sw-btn-ghost" href="{{ route('home.ingreso') }}">Ingresar</a>
                    <a class="sw-btn sw-btn-solid" href="{{ route('home.ingreso') }}" title="Entras con tu usuario y en Configurar mi página armas tu ficha pública">Crear mi página</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="sw-foot">
        <section class="sw-commerce-strip" aria-label="Servicios para tu mascota">
            <div class="sw-wrap">
                <header class="sw-commerce-head">
                    <div><span>Todo para su bienestar</span><h2>El ecosistema VeterChile te acompaña</h2></div>
                    <p>Accede con tu cuenta para encontrar productos y beneficios asociados a tu mascota.</p>
                </header>
                <div class="sw-commerce-grid">
                    <a class="sw-commerce-card sw-commerce-food" href="{{ route('paciente.integraciones.alimentos') }}">
                        <span class="sw-commerce-icon"><img src="{{ asset('images/iconos/alimento.png') }}" alt=""></span>
                        <div><small>Nutrición</small><strong>Alimentos para cada etapa</strong><p>Opciones de alimentación y planes asociados al perfil de tu mascota.</p><b>Explorar alimentos →</b></div>
                    </a>
                    <a class="sw-commerce-card sw-commerce-pharmacy" href="{{ route('paciente.integraciones.farmacia') }}">
                        <span class="sw-commerce-icon"><img src="{{ asset('images/iconos/farmacia-ministerio.png') }}" alt=""></span>
                        <div><small>Salud</small><strong>Farmacia veterinaria</strong><p>Acceso a productos de farmacia vinculados al cuidado veterinario.</p><b>Ir a farmacia →</b></div>
                    </a>
                    <a class="sw-commerce-card sw-commerce-promo" href="{{ route('paciente.mascotas.promociones_especiales') }}">
                        <span class="sw-commerce-icon">%</span>
                        <div><small>Beneficios</small><strong>Promociones y convenios</strong><p>Revisa campañas, servicios y oportunidades disponibles para tus mascotas.</p><b>Ver promociones →</b></div>
                    </a>
                    <a class="sw-commerce-card sw-commerce-contact" href="{{ url('/vetsdinicio/contactosdi.php') }}">
                        <span class="sw-commerce-icon">✉</span>
                        <div><small>Te ayudamos</small><strong>Contacto VeterChile</strong><p>¿Tienes dudas sobre el portal, tu cuenta o alguno de nuestros servicios?</p><b>Contáctanos →</b></div>
                    </a>
                </div>
                <p class="sw-commerce-disclaimer">La indicación de dietas terapéuticas y medicamentos corresponde exclusivamente al médico veterinario tratante.</p>
            </div>
        </section>
        <div class="sw-foot-main">
            <div class="sw-wrap sw-foot-grid">
                <section class="sw-foot-brand">
                    <a href="{{ route('sitio.inicio') }}" aria-label="VeterChile, ir al inicio">
                        <img src="{{ asset('images/logos/logo_pais_horizontal_blanco.svg') }}" alt="VeterChile">
                    </a>
                    <p>Salud Digital Integrada para conectar mascotas, tutores, profesionales y centros veterinarios.</p>
                    <span class="sw-foot-badge"><b>♡</b> Cuidamos su historia contigo</span>
                </section>

                <nav class="sw-foot-column" aria-label="Explorar VeterChile">
                    <h2>Explorar</h2>
                    <a href="{{ route('sitio.inicio') }}#profesionales">Profesionales</a>
                    <a href="{{ route('sitio.inicio') }}#clinicas">Clínicas veterinarias</a>
                    <a href="{{ route('sitio.cuidados-preventivos') }}">Bienestar animal</a>
                    <a href="{{ route('sitio.inicio') }}">Directorio VeterChile</a>
                </nav>

                <nav class="sw-foot-column" aria-label="Servicios VeterChile">
                    <h2>Servicios</h2>
                    <a href="{{ route('sitio.inicio') }}#profesionales">Reserva de horas</a>
                    <a href="{{ route('paciente.integraciones.alimentos') }}">Alimentos</a>
                    <a href="{{ route('paciente.integraciones.farmacia') }}">Farmacia veterinaria</a>
                    <a href="{{ route('paciente.mascotas.promociones_especiales') }}">Promociones</a>
                    @auth
                        <a href="{{ route('sitio.configurar') }}">Configurar mi página</a>
                        <a href="{{ route('home.ingreso') }}">Ir a mi escritorio</a>
                    @else
                        <a href="{{ route('home.ingreso') }}">Acceso de usuarios</a>
                        <a href="{{ route('home.ingreso') }}">Publicar mi perfil</a>
                    @endauth
                </nav>

                <section class="sw-foot-column sw-foot-contact">
                    <h2>VeterChile</h2>
                    <p>Información y herramientas para acompañar la salud de perros y gatos durante todas sus etapas.</p>
                    <a class="sw-foot-contact-link" href="mailto:contacto@veterchile.cl">contacto@veterchile.cl</a>
                    <a href="{{ url('/vetsdinicio/contactosdi.php') }}">Formulario de contacto</a>
                    <small>Chile · Atención digital</small>
                    <nav class="sw-global-social" aria-label="Redes sociales de VeterChile"><a href="https://instagram.com/veterchile" target="_blank" rel="noopener" aria-label="Instagram">◎</a><a href="https://facebook.com/veterchile" target="_blank" rel="noopener" aria-label="Facebook">f</a><a href="https://www.tiktok.com/@veterchile" target="_blank" rel="noopener" aria-label="TikTok">♪</a><a href="https://youtube.com/@veterchile" target="_blank" rel="noopener" aria-label="YouTube">▶</a><a href="https://linkedin.com/company/veterchile" target="_blank" rel="noopener" aria-label="LinkedIn">in</a></nav>
                </section>
            </div>
        </div>
        <div class="sw-foot-bottom">
            <div class="sw-wrap sw-foot-inner">
                <span>© {{ now()->year }} VeterChile · Salud Digital Integrada</span>
                <nav aria-label="Información legal">
                    <a href="{{ url('/vetsdinicio/politicasyprivacidad.php') }}">Privacidad</a>
                    <a href="{{ url('/vetsdinicio/terminosycondiciones.php') }}">Términos y condiciones</a>
                    <a href="{{ route('home.ingreso') }}">Acceso al sistema</a>
                </nav>
            </div>
        </div>
    </footer>
    @yield('page-scripts')
</body>
</html>
