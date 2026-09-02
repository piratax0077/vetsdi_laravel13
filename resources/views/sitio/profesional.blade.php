@extends('sitio.layout')

@section('title', $sitio->titulo.' · Vet SDI')
@section('body-class', 'sw-page-profesional')

@section('content')
<section class="sw-profile">
    <div class="sw-wrap sw-profile-grid">
        <div class="sw-profile-main">
            <p class="sw-url">vet-sdi.cl/{{ $sitio->slug }}</p>
            <div class="sw-profile-top">
                <img class="sw-avatar" src="{{ \App\Http\Controllers\SitioWebController::fotoProfesional($profesionalTitular) }}" alt="{{ $sitio->titulo }}">
                <div>
                    <span class="sw-chip">Médico veterinario</span>
                    <h1>{{ $sitio->titulo }}</h1>
                    <p>{{ $sitio->slogan }}</p>
                </div>
            </div>
            @if($sitio->descripcion)
                <p class="sw-muted">{{ $sitio->descripcion }}</p>
            @endif
            @include('sitio.partials.redes')

            <a class="sw-interest-link" href="{{ route('sitio.profesional.cuidados-preventivos', $sitio->slug) }}">
                <span class="sw-interest-icon">♡</span>
                <span><small>Consejo de {{ $sitio->titulo }}</small><strong>Guía de cuidados preventivos para tu mascota</strong></span>
                <b>Leer guía →</b>
            </a>

            <div class="sw-places">
                @forelse($lugares as $lugar)
                    <article class="sw-place">
                        <strong>{{ $lugar->nombre }}</strong>
                        <span>{{ \App\Http\Controllers\SitioWebController::direccionLugar($lugar) }}</span>
                        @if($lugar->telefono)
                            <span>{{ $lugar->telefono }}</span>
                        @endif
                        <span>{{ \App\Http\Controllers\SitioWebController::textoHorario($horarios, $lugar->id, optional($profesionalTitular)->id) }}</span>
                    </article>
                @empty
                    <p class="sw-muted">Sin lugares de atención vinculados a este profesional.</p>
                @endforelse
            </div>
        </div>

        @include('sitio.partials.reserva', ['ocultarProfesional' => true, 'agendaAyuda' => 'Agenda del Dr. en cada lugar de atención.'])
    </div>
</section>
@include('sitio.partials.reserva_modal')
@endsection
