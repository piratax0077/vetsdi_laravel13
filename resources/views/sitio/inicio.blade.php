@extends('sitio.layout')

@section('title', 'Vet SDI · Profesionales y clínicas')

@section('content')
<section class="sw-hero">
    <div class="sw-wrap sw-hero-grid">
        <div>
            <p class="sw-kicker">VeterChile · Salud veterinaria conectada</p>
            <h1>Encuentra atención confiable para quien siempre está contigo</h1>
            <p class="sw-lead">Conoce profesionales y clínicas, revisa sus lugares de atención y reserva una hora en línea de manera simple y segura.</p>
            <div class="sw-hero-actions">
                <a class="sw-btn sw-btn-light" href="#profesionales">Buscar profesional</a>
                <a class="sw-btn sw-btn-outline-light" href="{{ route('sitio.cuidados-preventivos') }}">Consejos de cuidado</a>
            </div>
        </div>
        <div class="sw-hero-stats">
            <div>
                <strong>Perfiles verificados</strong>
                <span>Información profesional</span>
            </div>
            <div>
                <strong>Atención cercana</strong>
                <span>Clínicas y consultas</span>
            </div>
            <div>
                <strong>Reserva en línea</strong>
                <span>Agenda conectada</span>
            </div>
        </div>
    </div>
</section>

<section class="sw-trust-strip" aria-label="Beneficios de VeterChile">
    <div class="sw-wrap sw-trust-grid">
        <div><span class="sw-trust-icon">✓</span><p><strong>Datos actualizados</strong><small>Directamente desde el sistema</small></p></div>
        <div><span class="sw-trust-icon">⌁</span><p><strong>Atención localizada</strong><small>Elige dónde quieres atenderte</small></p></div>
        <div><span class="sw-trust-icon">♡</span><p><strong>Cuidado continuo</strong><small>Prevención durante toda su vida</small></p></div>
    </div>
</section>

<section class="sw-section" id="profesionales">
    <div class="sw-wrap">
        <div class="sw-section-head">
            <p class="sw-kicker">Ficha profesional</p>
            <h2>El doctor y su agenda</h2>
            <p>Página pública con los lugares donde atiende y reserva en línea.</p>
        </div>
        @if($profesional)
            <article class="sw-feature">
                <div class="sw-feature-photo">
                    <img src="{{ \App\Http\Controllers\SitioWebController::fotoProfesional(optional($profesional->usuario)->profesional) }}" alt="{{ $profesional->titulo }}">
                </div>
                <div class="sw-feature-body">
                    <span class="sw-chip">Médico veterinario</span>
                    <h3>{{ $profesional->titulo }}</h3>
                    <p>{{ $profesional->slogan }}</p>
                    <p class="sw-muted">{{ $profesional->descripcion }}</p>
                    <div class="sw-tags">
                        @forelse($profesional->lugares as $lugar)
                            <span>{{ $lugar->nombre }}</span>
                        @empty
                            <span>Sin lugares de atención vinculados</span>
                        @endforelse
                    </div>
                    <a class="sw-btn sw-btn-solid" href="{{ route('sitio.ficha', $profesional->slug) }}">Ver ficha y agenda</a>
                </div>
            </article>
        @endif
    </div>
</section>

<section class="sw-section sw-section-alt" id="clinicas">
    <div class="sw-wrap">
        <div class="sw-section-head">
            <p class="sw-kicker">Centro médico</p>
            <h2>La clínica y su equipo</h2>
            <p>El centro publica su página, el equipo y las agendas de cada profesional en ese lugar.</p>
        </div>
        @if($clinica)
            <article class="sw-feature sw-feature-flip">
                <div class="sw-feature-body">
                    <span class="sw-chip sw-chip-gold">Centro médico</span>
                    <h3>{{ $clinica->titulo }}</h3>
                    <p>{{ $clinica->slogan }}</p>
                    <p class="sw-muted">{{ $clinica->descripcion }}</p>
                    <div class="sw-tags">
                        @forelse($clinica->lugares as $lugar)
                            <span>{{ $lugar->nombre }} · {{ \App\Http\Controllers\SitioWebController::direccionLugar($lugar) }}</span>
                        @empty
                            <span>Sin lugares de atención vinculados</span>
                        @endforelse
                    </div>
                    <a class="sw-btn sw-btn-solid" href="{{ route('sitio.ficha', $clinica->slug) }}">Ver clínica y reservar</a>
                </div>
                <div class="sw-clinic-panel">
                    <p class="sw-clinic-name">{{ $clinica->lugares->first()->nombre ?? $clinica->titulo }}</p>
                    <p>Los Andes, Valparaíso</p>
                    <p>{{ $clinica->telefono }}</p>
                </div>
            </article>
        @endif
    </div>
</section>

@if($sitios->count() > 2)
<section class="sw-section">
    <div class="sw-wrap">
        <div class="sw-section-head">
            <h2>Directorio</h2>
        </div>
        <div class="sw-dir">
            @foreach($sitios as $item)
                <a class="sw-dir-card" href="{{ route('sitio.ficha', $item->slug) }}">
                    <small>{{ $item->tipo === 'clinica' ? 'Clínica' : 'Profesional' }}</small>
                    <strong>{{ $item->titulo }}</strong>
                    <span>{{ $item->lugares->pluck('nombre')->filter()->join(' · ') }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
