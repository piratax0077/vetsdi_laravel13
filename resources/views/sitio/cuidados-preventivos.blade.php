@extends('sitio.layout')

@section('title', 'Cuidados preventivos por '.$sitio->titulo.' · VeterChile')
@section('body-class', 'sw-page-article')

@section('content')
<article>
    <header class="sw-article-hero">
        <div class="sw-wrap sw-article-hero-grid">
            <div>
                <nav class="sw-breadcrumb" aria-label="Navegación">
                    <a href="{{ route('sitio.inicio') }}">Inicio</a><span>›</span><a href="{{ route('sitio.ficha', $sitio->slug) }}">{{ $sitio->titulo }}</a><span>›</span><span>Consejos</span>
                </nav>
                <p class="sw-kicker">Bienestar y prevención</p>
                <h1>Una rutina preventiva puede cambiar toda su vida</h1>
                <p class="sw-lead">Una guía sencilla para conversar con tu médico veterinario y acompañar mejor a tu perro o gato en cada etapa.</p>
                <div class="sw-article-author">
                    <img src="{{ \App\Http\Controllers\SitioWebController::fotoProfesional($profesional) }}" alt="{{ $sitio->titulo }}">
                    <p><span>Contenido presentado por</span><strong>{{ $sitio->titulo }}</strong><small>{{ $sitio->slogan ?: 'Médico veterinario' }}</small></p>
                </div>
            </div>
            <div class="sw-pet-illustration" aria-hidden="true">
                <span class="sw-pet-heart">♥</span>
                <span class="sw-pet-dog">◖ᴥ◗</span>
                <small>Cuidar también es prevenir</small>
            </div>
        </div>
    </header>

    <div class="sw-wrap sw-article-layout">
        <main class="sw-article-body">
            <p class="sw-article-intro">La prevención no consiste solamente en vacunas. Una evaluación integral considera la edad, especie, estilo de vida, alimentación, conducta, salud dental y riesgos individuales de cada mascota.</p>

            <section class="sw-article-section">
                <span class="sw-step-number">01</span>
                <div>
                    <h2>Programa controles periódicos</h2>
                    <p>Incluso cuando parece estar sana, una mascota se beneficia de evaluaciones veterinarias regulares. La frecuencia debe definirse individualmente según su edad, especie, raza, condición de salud y ambiente.</p>
                    <div class="sw-tip"><strong>Para tu próxima visita</strong><span>Lleva anotados cambios en apetito, peso, sueño, actividad, deposiciones o comportamiento.</span></div>
                </div>
            </section>

            <section class="sw-article-section">
                <span class="sw-step-number">02</span>
                <div>
                    <h2>Mantén un plan individual de protección</h2>
                    <p>Vacunación y control de parásitos deben ajustarse al riesgo real. No todos los animales necesitan exactamente el mismo esquema: vivir dentro o fuera de casa, viajar y convivir con otras mascotas cambia la recomendación.</p>
                </div>
            </section>

            <section class="sw-article-section">
                <span class="sw-step-number">03</span>
                <div>
                    <h2>Observa peso, boca y conducta</h2>
                    <p>El peso corporal, la masa muscular, los dientes, las encías y el comportamiento forman parte del control preventivo. Los cambios graduales suelen pasar inadvertidos en casa, por eso conviene registrarlos y comentarlos.</p>
                    <ul class="sw-check-list">
                        <li>Revisa si come y bebe como acostumbra.</li>
                        <li>Observa movilidad, juego y tolerancia al ejercicio.</li>
                        <li>Presta atención al mal aliento, dolor o dificultad para comer.</li>
                        <li>No cambies medicamentos o dieta clínica sin indicación profesional.</li>
                    </ul>
                </div>
            </section>

            <section class="sw-warning-card">
                <span>!</span>
                <div><strong>Consulta sin esperar</strong><p>Dificultad para respirar, convulsiones, sangrado importante, abdomen muy distendido, intoxicación sospechada, incapacidad para orinar o decaimiento intenso requieren evaluación veterinaria urgente.</p></div>
            </section>

            <section class="sw-article-sources">
                <h2>Fuentes y alcance</h2>
                <p>Contenido educativo basado en las guías preventivas AAHA/AVMA. No reemplaza la evaluación de un médico veterinario ni entrega un esquema terapéutico individual.</p>
                <a href="https://www.aaha.org/resources/2011-aaha-avma-preventive-healthcare-guidelines/" target="_blank" rel="noopener noreferrer">Revisar guía AAHA/AVMA ↗</a>
            </section>
        </main>

        <aside class="sw-article-aside">
            <div class="sw-article-cta">
                <span class="sw-chip">Agenda VeterChile</span>
                <h2>¿Es momento de su control con {{ $sitio->titulo }}?</h2>
                <p>Encuentra un profesional y revisa horas disponibles en sus lugares de atención.</p>
                <a class="sw-btn sw-btn-solid sw-btn-block" href="{{ route('sitio.ficha', $sitio->slug) }}#agenda">Revisar horas disponibles</a>
            </div>
            <div class="sw-related-pros">
                <h3>Sobre el profesional</h3>
                <a href="{{ route('sitio.ficha', $sitio->slug) }}">
                    <img src="{{ \App\Http\Controllers\SitioWebController::fotoProfesional($profesional) }}" alt="">
                    <p><strong>{{ $sitio->titulo }}</strong><small>{{ $sitio->slogan ?: 'Médico veterinario' }}</small></p>
                </a>
                @if($sitio->telefono || $sitio->email)
                    <p class="sw-prof-contact">{{ $sitio->telefono }}@if($sitio->telefono && $sitio->email) · @endif{{ $sitio->email }}</p>
                @endif
            </div>
        </aside>
    </div>
</article>
@endsection
