@extends('template.usuario.template')

@section('page-styles')
<style>
    .memorial-shell { max-width: 1100px; margin: 0 auto; }
    .memorial-hero {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 14px 40px rgba(40, 52, 71, .14);
        background: linear-gradient(145deg, #f8f9fb 0%, #fff 55%);
    }
    .memorial-hero-header {
        background: linear-gradient(135deg, #5c6370, #868e96);
        color: #fff;
        padding: 28px 24px;
        text-align: center;
    }
    .memorial-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid rgba(255,255,255,.85);
        box-shadow: 0 8px 24px rgba(0,0,0,.18);
        margin-bottom: 12px;
    }
    .memorial-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(255,255,255,.18);
        font-size: 12px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .memorial-body { padding: 24px; }
    .memorial-quote {
        font-size: 18px;
        line-height: 1.6;
        color: #495057;
        font-style: italic;
        text-align: center;
        max-width: 720px;
        margin: 0 auto 24px;
    }
    .memorial-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 28px;
    }
    .memorial-meta-item {
        padding: 14px;
        border-radius: 12px;
        background: #f4f6f8;
        text-align: center;
    }
    .memorial-meta-item small { display: block; color: #6c757d; margin-bottom: 4px; }
    .memorial-album-title {
        font-weight: 700;
        color: #495057;
        margin-bottom: 16px;
    }
    .memorial-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 14px;
    }
    .memorial-grid-item {
        border: 0;
        padding: 0;
        background: transparent;
        border-radius: 12px;
        overflow: hidden;
        cursor: zoom-in;
        box-shadow: 0 6px 18px rgba(31, 45, 61, .12);
        transition: transform .18s ease;
    }
    .memorial-grid-item:hover { transform: translateY(-3px); }
    .memorial-grid-item img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        display: block;
    }
    .memorial-viewer {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1080;
        background: rgba(8, 20, 29, .92);
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .memorial-viewer.open { display: flex; }
    .memorial-viewer img {
        max-width: min(920px, 92vw);
        max-height: 80vh;
        border-radius: 12px;
        object-fit: contain;
    }
    .memorial-viewer-close {
        position: absolute;
        top: 20px;
        right: 24px;
        border: 0;
        background: rgba(255,255,255,.15);
        color: #fff;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
    }
    @media (max-width: 767px) {
        .memorial-meta { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
@php
    $memorial = is_array($mascota->memorial_registro) ? $mascota->memorial_registro : [];
    $imgMascota = $mascota->foto_url ?: asset('images/iconos/paciente-' . ($mascota->sexo === 'M' ? 'm' : 'f') . '.svg');
    $especie = optional($mascota->especieMascota)->nombre ?: ($mascota->otra_especie ?: 'Sin registro');
    $raza = optional($mascota->razaMascota)->nombre ?: 'Sin registro';
    $fechaFallecimiento = optional($mascota->fecha_fallecimiento)->format('d/m/Y');
    $fechaNacimiento = optional($mascota->fecha_nacimiento)->format('d/m/Y');
@endphp
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="memorial-shell">
            <div class="page-header mb-3">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <a href="{{ route('paciente.mis_mascotas') }}" class="text-muted mr-2" title="Volver a Mis Mascotas">
                                    <i class="feather icon-arrow-left"></i>
                                </a>
                                <h5 class="d-inline font-weight-bold mb-0">En memoria de {{ $mascota->nombre }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="memorial-hero">
                <div class="memorial-hero-header">
                    <span class="memorial-badge mb-2 d-inline-block">En memoria</span>
                    <div>
                        <img src="{{ $imgMascota }}" alt="{{ $mascota->nombre }}" class="memorial-photo">
                    </div>
                    <h3 class="mb-1">{{ $mascota->nombre }}</h3>
                    <p class="mb-0 opacity-90">{{ $especie }} · {{ $raza }}</p>
                </div>
                <div class="memorial-body">
                    @if(!empty($memorial['mensaje']))
                        <blockquote class="memorial-quote">“{{ $memorial['mensaje'] }}”</blockquote>
                    @endif

                    <div class="memorial-meta">
                        <div class="memorial-meta-item">
                            <small>Fecha de fallecimiento</small>
                            <strong>{{ $fechaFallecimiento ?: '—' }}</strong>
                        </div>
                        <div class="memorial-meta-item">
                            <small>Fecha de nacimiento</small>
                            <strong>{{ $fechaNacimiento ?: 'No registrada' }}</strong>
                        </div>
                        <div class="memorial-meta-item">
                            <small>Causa</small>
                            <strong>{{ $memorial['causa'] ?? 'No indicada' }}</strong>
                        </div>
                    </div>

                    <h6 class="memorial-album-title"><i class="feather icon-image"></i> Álbum de recuerdos</h6>

                    @if(count($album) > 0)
                        <div class="memorial-grid" id="memorial_album_grid">
                            @foreach($album as $indice => $foto)
                                <button type="button" class="memorial-grid-item" data-index="{{ $indice }}" data-url="{{ $foto['url'] }}" data-titulo="{{ $foto['titulo'] }}">
                                    <img src="{{ $foto['url'] }}" alt="{{ $foto['titulo'] }}">
                                </button>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aún no hay fotos en el álbum memorial.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="memorial-viewer" id="memorial_viewer" aria-hidden="true">
    <button type="button" class="memorial-viewer-close" id="memorial_viewer_close" aria-label="Cerrar">&times;</button>
    <img src="" alt="" id="memorial_viewer_img">
</div>
@endsection

@section('page-script')
<script>
(function($) {
    var $viewer = $('#memorial_viewer');
    var $img = $('#memorial_viewer_img');

    $(document).on('click', '.memorial-grid-item', function() {
        var url = $(this).data('url');
        if (!url) return;
        $img.attr('src', url).attr('alt', $(this).data('titulo') || 'Recuerdo');
        $viewer.addClass('open').attr('aria-hidden', 'false');
    });

    $('#memorial_viewer_close, #memorial_viewer').on('click', function(e) {
        if (e.target === this) {
            $viewer.removeClass('open').attr('aria-hidden', 'true');
            $img.attr('src', '');
        }
    });
})(jQuery);
</script>
@endsection


