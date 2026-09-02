@extends('sitio.layout')

@section('title', 'Promocionar en redes · VeterChile')
@section('body-class', 'sw-page-promotion')

@section('content')
<section class="sw-promo-hero">
    <div class="sw-wrap">
        <nav class="sw-breadcrumb"><a href="{{ route('sitio.configurar') }}">Configurar mi página</a><span>›</span><span>Promoción en redes</span></nav>
        <span class="sw-chip sw-chip-light">Difusión profesional</span>
        <h1>Lleva tu perfil VeterChile<br>a más tutores de mascotas</h1>
        <p>Selecciona un objetivo, las redes adecuadas y el tiempo de promoción. Los valores son planes internos de prueba y pueden ajustarse antes de producción.</p>
    </div>
</section>

<section class="sw-section">
    <div class="sw-wrap">
        @if(session('mensaje'))<p class="sw-flash">{{ session('mensaje') }}</p>@endif
        <div class="sw-network-guide">
            <article><b class="sw-network sw-instagram">◎</b><div><strong>Instagram</strong><span>Ideal para imágenes, casos, equipo y consejos breves.</span></div></article>
            <article><b class="sw-network sw-facebook">f</b><div><strong>Facebook</strong><span>Útil para alcance local, comunidad y campañas.</span></div></article>
            <article><b class="sw-network sw-tiktok">♪</b><div><strong>TikTok</strong><span>Videos educativos cortos y mayor descubrimiento.</span></div></article>
            <article><b class="sw-network sw-linkedin">in</b><div><strong>LinkedIn</strong><span>Marca profesional, alianzas y reputación.</span></div></article>
        </div>

        <form method="POST" action="{{ route('sitio.promocion.contratar') }}" class="sw-promo-form">
            @csrf
            <section class="sw-promo-objective">
                <div class="sw-section-head"><p class="sw-kicker">Paso 1</p><h2>¿Qué necesitas promocionar?</h2></div>
                <div class="sw-objective-options">
                    <label><input type="radio" name="objetivo" value="reservas" required><span><strong>Obtener reservas</strong><small>Enviar visitas a tu agenda.</small></span></label>
                    <label><input type="radio" name="objetivo" value="marca"><span><strong>Dar a conocer mi perfil</strong><small>Aumentar visibilidad profesional.</small></span></label>
                    <label><input type="radio" name="objetivo" value="servicio"><span><strong>Promocionar un servicio</strong><small>Destacar consulta o campaña.</small></span></label>
                    <label><input type="radio" name="objetivo" value="educacion"><span><strong>Compartir contenido</strong><small>Difundir tu guía profesional.</small></span></label>
                </div>
            </section>

            <div class="sw-section-head"><p class="sw-kicker">Paso 2</p><h2>Elige un plan</h2><p>Cada plan incluye redes y duración definidas.</p></div>
            <div class="sw-plan-grid">
                @foreach($planes as $codigo => $plan)
                    <label class="sw-plan-card {{ $codigo === 'crecimiento' ? 'sw-plan-featured' : '' }}">
                        @if($codigo === 'crecimiento')<em>Más elegido</em>@endif
                        <input type="radio" name="plan" value="{{ $codigo }}" required>
                        <span class="sw-plan-radio"></span>
                        <small>{{ $plan['dias'] }} días</small>
                        <h3>{{ $plan['nombre'] }}</h3>
                        <p>{{ $plan['ideal'] }}</p>
                        <strong>${{ number_format($plan['monto'], 0, ',', '.') }} <small>CLP</small></strong>
                        <ul>
                            @foreach($plan['redes'] as $red)<li><input type="checkbox" name="redes[]" value="{{ $red }}" checked> {{ ucfirst($red) }}</li>@endforeach
                        </ul>
                        <button type="submit" class="sw-btn sw-btn-solid sw-btn-block">Contratar plan</button>
                    </label>
                @endforeach
            </div>
        </form>

        @if($campanas->isNotEmpty())
            <section class="sw-campaign-history">
                <div class="sw-section-head"><h2>Mis campañas</h2></div>
                @foreach($campanas as $campana)
                    <article><div><strong>{{ $campana->nombre_plan }}</strong><span>{{ collect($campana->redes)->map(fn($r) => ucfirst($r))->join(' · ') }} · {{ $campana->duracion_dias }} días</span></div><b class="sw-campaign-status sw-status-{{ $campana->estado }}">{{ str_replace('_', ' ', ucfirst($campana->estado)) }}</b><span>${{ number_format($campana->monto, 0, ',', '.') }}</span>@if($campana->estado === 'pendiente_pago')<a class="sw-btn sw-btn-ghost" href="{{ route('sitio.promocion.pago', $campana) }}">Pagar</a>@endif</article>
                @endforeach
            </section>
        @endif
    </div>
</section>
@endsection
