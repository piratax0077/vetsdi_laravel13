@extends('sitio.layout')

@section('title', 'Configurar sitio web · Vet SDI')
@section('body-class', 'sw-page-config')

@section('content')
<section class="sw-config">
    <div class="sw-wrap">
        <header class="sw-config-hero">
            <div class="sw-section-head">
                <p class="sw-kicker">Página pública</p>
                <h1>Configurar mi sitio</h1>
                <p>Define cómo se ve tu ficha: dirección corta, lugares con agenda y redes.</p>
            </div>
            <aside class="sw-config-preview">
                <span>Tu dirección</span>
                <strong>vet-sdi.cl/{{ $sitio->slug }}</strong>
                <em class="sw-status {{ $sitio->publicado ? '' : 'sw-status-off' }}">
                    {{ $sitio->publicado ? 'Página visible' : 'Aún no publicada' }}
                </em>
            </aside>
        </header>

        @if(session('mensaje'))
            <p class="sw-flash">{{ session('mensaje') }}</p>
        @endif
        <div class="sw-editor-shortcut"><div><strong>Contenido profesional</strong><span>Publica consejos, novedades y artículos para tutores de mascotas.</span></div><a class="sw-btn sw-btn-solid" href="{{ route('sitio.articulos.index') }}">Administrar artículos</a></div>
        <div class="sw-editor-shortcut"><div><strong>Contenido profesional</strong><span>Publica consejos, novedades y artículos para tutores de mascotas.</span></div><a class="sw-btn sw-btn-solid" href="{{ route('sitio.articulos.index') }}">Administrar artículos</a></div>

        <form method="POST" action="{{ route('sitio.configurar.guardar') }}" class="sw-config-form">
            @csrf

            <section class="sw-card">
                <header>
                    <h2>Identidad</h2>
                    <p>Lo primero que ve quien abre tu página.</p>
                </header>
                <div class="sw-form-grid">
                    <label>Título público
                        <input name="titulo" value="{{ old('titulo', $sitio->titulo) }}" required maxlength="160">
                    </label>
                    <label>Tipo
                        <input value="{{ $sitio->tipo === 'clinica' ? 'Clínica' : 'Profesional' }}" disabled>
                    </label>
                    <label class="sw-span-2">Dirección pública
                        <span class="sw-url-field">
                            <em>vet-sdi.cl/</em>
                            <input name="slug" value="{{ old('slug', $sitio->slug) }}" required maxlength="80" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="dr-jaimekriman">
                        </span>
                    </label>
                    <label class="sw-span-2">Frase corta
                        <input name="slogan" value="{{ old('slogan', $sitio->slogan) }}" maxlength="180">
                    </label>
                    <label class="sw-span-2">Descripción
                        <textarea name="descripcion" rows="3" maxlength="400">{{ old('descripcion', $sitio->descripcion) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="sw-card">
                <header>
                    <h2>Contacto y visibilidad</h2>
                    <p>Datos de contacto y si la página aparece en público.</p>
                </header>
                <div class="sw-form-grid">
                    <label>Teléfono<input name="telefono" value="{{ old('telefono', $sitio->telefono) }}"></label>
                    <label>Email<input type="email" name="email" value="{{ old('email', $sitio->email) }}"></label>
                    <label>WhatsApp<input name="whatsapp" value="{{ old('whatsapp', $sitio->whatsapp) }}" placeholder="56995474660"></label>
                    <label class="sw-check">
                        <input type="checkbox" name="publicado" value="1" @checked(old('publicado', $sitio->publicado))>
                        Hacer visible la página pública
                    </label>
                </div>
            </section>

            <section class="sw-card">
                <header>
                    <h2>Redes sociales</h2>
                    <p>Puedes pegar el usuario o la URL completa. Vacío no se muestra.</p>
                </header>
                <div class="sw-form-grid">
                    <label>Instagram<input name="instagram" value="{{ old('instagram', $sitio->instagram) }}" placeholder="@tuusuario"></label>
                    <label>Facebook<input name="facebook" value="{{ old('facebook', $sitio->facebook) }}" placeholder="facebook.com/tu-pagina"></label>
                    <label>TikTok<input name="tiktok" value="{{ old('tiktok', $sitio->tiktok) }}" placeholder="@tuusuario"></label>
                    <label>YouTube<input name="youtube" value="{{ old('youtube', $sitio->youtube) }}" placeholder="youtube.com/@canal"></label>
                    <label>LinkedIn<input name="linkedin" value="{{ old('linkedin', $sitio->linkedin) }}" placeholder="linkedin.com/in/tu-perfil"></label>
                    <label>Sitio web<input name="web" value="{{ old('web', $sitio->web) }}" placeholder="https://tuclinica.cl"></label>
                </div>
                <div class="sw-social-promo-callout">
                    <div><strong>¿Quieres promocionar tu perfil?</strong><span>Elige redes, duración y un plan para aumentar alcance y reservas.</span></div>
                    <a class="sw-btn sw-btn-solid" href="{{ route('sitio.promocion.index') }}">Ver planes de promoción</a>
                </div>
            </section>

            <section class="sw-card">
                <header>
                    <h2>Lugares de atención</h2>
                    <p>Solo estos lugares exponen agenda en tu página.</p>
                </header>
                <div class="sw-places">
                    @forelse($lugares as $lugar)
                        <label class="sw-place sw-place-check">
                            <input type="checkbox" name="lugares[]" value="{{ $lugar->id }}" @checked(in_array($lugar->id, $seleccionados, true))>
                            <span>
                                <strong>{{ $lugar->nombre }}</strong>
                                <em>{{ \App\Http\Controllers\SitioWebController::direccionLugar($lugar) }}</em>
                            </span>
                        </label>
                    @empty
                        <p class="sw-muted">No hay lugares asociados a tu usuario. Configúralos en el escritorio primero.</p>
                    @endforelse
                </div>
            </section>

            <div class="sw-config-actions">
                <button type="submit" class="sw-btn sw-btn-solid">Guardar cambios</button>
                @if($sitio->publicado && $sitio->slug)
                    <a class="sw-btn sw-btn-ghost" href="{{ route('sitio.ficha', $sitio->slug) }}" target="_blank">Ver página pública</a>
                @endif
            </div>
        </form>
    </div>
</section>
@endsection
