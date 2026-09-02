@extends('sitio.layout')

@section('title', 'Bienestar animal · VeterChile')
@section('body-class', 'sw-page-wellness')

@section('content')
<header class="sw-wellness-hero">
    <div class="sw-wrap">
        <nav class="sw-breadcrumb" aria-label="Navegación"><a href="{{ route('sitio.inicio') }}">Inicio</a><span>›</span><span>Bienestar animal</span></nav>
        <span class="sw-chip sw-chip-light">Comunidad VeterChile</span>
        <h1>Pequeños cuidados,<br>grandes historias juntos</h1>
        <p>Información clara para cuidar mejor a perros y gatos, reconocer cambios importantes y llegar más preparado a cada consulta.</p>
    </div>
</header>

<section class="sw-section">
    <div class="sw-wrap">
        <div class="sw-section-head sw-section-head-centered">
            <p class="sw-kicker">Guía destacada</p>
            <h2>Cuidados preventivos durante toda su vida</h2>
            <p>Un punto de partida para conversar con el profesional que conoce a tu mascota.</p>
        </div>
        <div class="sw-wellness-grid">
            <article><span>01</span><h3>Control periódico</h3><p>Evaluaciones ajustadas a su edad, especie, estilo de vida y condición de salud.</p></article>
            <article><span>02</span><h3>Protección individual</h3><p>Vacunas y control de parásitos definidos según sus riesgos reales.</p></article>
            <article><span>03</span><h3>Nutrición y peso</h3><p>Seguimiento de condición corporal, masa muscular, alimentación y actividad.</p></article>
            <article><span>04</span><h3>Boca y conducta</h3><p>Atención a dientes, encías, dolor y cambios en su comportamiento cotidiano.</p></article>
        </div>
    </div>
</section>

<section class="sw-section sw-wellness-pros">
    <div class="sw-wrap">
        <div class="sw-section-head">
            <p class="sw-kicker">Voces profesionales</p>
            <h2>Lee la guía de tu médico veterinario</h2>
            <p>Cada versión incluye la identidad del profesional y un acceso directo a su agenda.</p>
        </div>
        <div class="sw-pro-guide-grid">
            @forelse($profesionales as $sitio)
                <a href="{{ route('sitio.profesional.cuidados-preventivos', $sitio->slug) }}">
                    <img src="{{ \App\Http\Controllers\SitioWebController::fotoProfesional($sitio->usuario?->profesional) }}" alt="{{ $sitio->titulo }}">
                    <div><small>Guía presentada por</small><h3>{{ $sitio->titulo }}</h3><p>{{ $sitio->slogan ?: 'Médico veterinario' }}</p><strong>Leer y revisar agenda →</strong></div>
                </a>
            @empty
                <div class="sw-empty-state"><strong>Próximamente</strong><p>Los profesionales publicados aparecerán aquí con sus contenidos.</p></div>
            @endforelse
        </div>
    </div>
</section>

<section class="sw-wellness-note">
    <div class="sw-wrap"><span>♡</span><p><strong>La información orienta; la consulta individual decide.</strong> Ante síntomas o cambios importantes, contacta a un médico veterinario.</p><a class="sw-btn sw-btn-solid" href="{{ route('sitio.inicio') }}#profesionales">Ver profesionales</a></div>
</section>
@if(($articulos ?? collect())->isNotEmpty())<section class="sw-section"><div class="sw-wrap"><div class="sw-section-head"><p class="sw-kicker">Publicado por profesionales</p><h2>Consejos y artículos recientes</h2></div><div class="sw-public-articles">@foreach($articulos as $articulo)<a href="{{ route('sitio.articulos.show',$articulo->slug) }}"><small>{{ $articulo->sitio->titulo }}</small><strong>{{ $articulo->titulo }}</strong><span>{{ $articulo->resumen }}</span><b>Leer artículo →</b></a>@endforeach</div></div></section>@endif
@endsection
