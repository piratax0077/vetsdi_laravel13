@extends('sitio.layout')

@section('title', $sitio->titulo.' · Vet SDI')
@section('body-class', 'sw-page-clinica')

@section('content')
<section class="sw-profile">
    <div class="sw-wrap sw-profile-grid">
        <div class="sw-profile-main">
            <p class="sw-url">vet-sdi.cl/{{ $sitio->slug }}</p>
            <div class="sw-profile-top">
                <div class="sw-avatar sw-avatar-clinic">CM</div>
                <div>
                    <span class="sw-chip sw-chip-gold">Clínica veterinaria</span>
                    <h1>{{ $sitio->titulo }}</h1>
                    <p>{{ $sitio->slogan }}</p>
                </div>
            </div>
            @if($sitio->descripcion)
                <p class="sw-muted">{{ $sitio->descripcion }}</p>
            @endif
            @include('sitio.partials.redes')

            <div class="sw-places">
                @forelse($lugares as $lugar)
                    <article class="sw-place">
                        <strong>{{ $lugar->nombre }}</strong>
                        <span>{{ \App\Http\Controllers\SitioWebController::direccionLugar($lugar) }}</span>
                        @if($lugar->telefono)
                            <span>{{ $lugar->telefono }}</span>
                        @endif
                        <span>{{ \App\Http\Controllers\SitioWebController::textoHorario($horarios, $lugar->id) }}</span>
                    </article>
                @empty
                    <p class="sw-muted">Sin lugares de atención vinculados a esta clínica.</p>
                @endforelse
            </div>

            @if($equipo->isNotEmpty())
                <div class="sw-team">
                    <h2>Equipo</h2>
                    <div class="sw-team-list">
                        @foreach($equipo as $pro)
                            @php
                                $sitioPro = optional(optional($pro->Usuario)->sitioWeb);
                            @endphp
                            <div class="sw-team-item">
                                <img src="{{ \App\Http\Controllers\SitioWebController::fotoProfesional($pro) }}" alt="{{ $pro->nombreCompleto() }}">
                                <div>
                                    <strong>
                                        @if($sitioPro && $sitioPro->publicado)
                                            <a href="{{ route('sitio.ficha', $sitioPro->slug) }}">{{ $pro->nombreCompleto() }}</a>
                                        @else
                                            {{ $pro->nombreCompleto() }}
                                        @endif
                                    </strong>
                                    <span>{{ optional($pro->TipoEspecialidad)->nombre ?: optional($pro->Especialidad)->nombre ?: 'Médico veterinario' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @include('sitio.partials.reserva', ['ocultarProfesional' => false, 'agendaAyuda' => 'Elige un profesional del centro y su lugar de atención.'])
    </div>
</section>
@include('sitio.partials.reserva_modal')
@endsection
