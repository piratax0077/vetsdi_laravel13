@extends('template.usuario.template')

@section('page-styles')
<style>
    .promos-heading{display:flex;align-items:center;justify-content:flex-start;gap:14px}
    .promos-home{display:inline-flex;align-items:center;justify-content:center;padding:6px;border:0;color:#fff!important;background:transparent;font-size:24px;line-height:1;transition:.2s ease}
    .promos-home:hover{color:#d9fffc!important;background:transparent;transform:translateY(-1px) scale(1.08)}
    .promos-intro{margin-bottom:20px;padding:20px 22px;border:1px solid #dce8eb;border-radius:15px;background:linear-gradient(135deg,#fff,#eefafa);box-shadow:0 5px 18px rgba(31,55,75,.08)}
    .promos-intro h3{margin:0 0 6px;color:#263b50;font-weight:700}
    .promos-intro p{margin:0;color:#6b7d8f}
    .promo-section{height:100%;padding:20px;border:1px solid #dce5eb;border-radius:16px;background:#fff;box-shadow:0 7px 22px rgba(38,59,80,.1)}
    .promo-section-title{display:flex;align-items:center;gap:11px;margin-bottom:17px;padding-bottom:13px;border-bottom:1px solid #e7eef2}
    .promo-section-title i{display:flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:12px;color:#fff;background:linear-gradient(135deg,#1bb9b1,#168c83);font-size:20px}
    .promo-section-title h4{margin:0;color:#304458;font-size:19px;font-weight:700}
    .promo-grid{display:grid;gap:13px}
    .promo-card{display:grid;grid-template-columns:92px minmax(0,1fr);min-height:112px;overflow:hidden;border:1px solid #e0e8ed;border-radius:12px;background:#fbfcfd;transition:.2s ease}
    .promo-card:hover{transform:translateY(-2px);border-color:#20aaa2;box-shadow:0 8px 18px rgba(24,140,131,.12)}
    .promo-card img{width:92px;height:100%;min-height:112px;object-fit:cover;background:#f0f4f6}
    .promo-card-body{display:flex;flex-direction:column;align-items:flex-start;padding:12px}
    .promo-card-body h5{margin:0 0 5px;color:#263b50;font-size:15px;font-weight:700}
    .promo-card-body p{margin:0 0 10px;color:#718096;font-size:12px;line-height:1.4}
    .promo-card-body .btn{margin-top:auto;border:0;border-radius:8px;font-size:11px;font-weight:600}
    .promo-card-body .btn-info{background:#18a9a2}
    .promo-card-body .btn-purple{color:#fff;background:#7252b8}
    .promo-note{margin-top:15px;padding:11px 13px;border-radius:10px;color:#496170;background:#f2f7f8;font-size:12px}
    @media(max-width:575.98px){.promos-heading{align-items:flex-start}.promo-card{grid-template-columns:76px minmax(0,1fr)}.promo-card img{width:76px}}
</style>
@endsection

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title promos-heading">
                            <a href="{{ route('paciente.home') }}" class="promos-home" title="Volver al inicio" aria-label="Volver al inicio"><i class="feather icon-home" aria-hidden="true"></i></a>
                            <div>
                                <h5 class="m-b-5 font-weight-bold text-white">Promociones y beneficios</h5>
                                <span class="text-white">Opciones para el cuidado y bienestar de tus mascotas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="promos-intro">
            <h3>Beneficios disponibles para ti</h3>
            <p>Accede con tu cuenta VET SDI. La tienda reconocerá automáticamente tus mascotas, direcciones y beneficios vigentes.</p>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <section class="promo-section">
                    <div class="promo-section-title"><i class="fas fa-shopping-basket"></i><h4>Alimentos y farmacia</h4></div>
                    <div class="promo-grid">
                        <article class="promo-card">
                            <img src="{{ asset('images/promociones/venta1.jpg') }}" alt="Alimentos para mascotas">
                            <div class="promo-card-body"><h5>Ofertas de alimentos</h5><p>Revisa productos y precios disponibles para cada tipo de mascota.</p><a href="{{ route('paciente.integraciones.alimentos') }}" target="_blank" rel="noopener" class="btn btn-info btn-sm">Ver ofertas</a></div>
                        </article>
                        <article class="promo-card">
                            <img src="{{ asset('images/promociones/promo3.jpg') }}" alt="Farmacia veterinaria">
                            <div class="promo-card-body"><h5>Farmacia y cuidado</h5><p>Encuentra productos de higiene, bienestar y cuidado veterinario.</p><a href="{{ route('paciente.integraciones.farmacia') }}" target="_blank" rel="noopener" class="btn btn-info btn-sm">Ir a farmacia</a></div>
                        </article>
                        <article class="promo-card">
                            <img src="{{ asset('images/promociones/banner2.jpg') }}" alt="Pedidos recurrentes">
                            <div class="promo-card-body"><h5>Pedidos recurrentes</h5><p>Programa entregas de alimento y administra tus direcciones.</p><a href="{{ route('paciente.integraciones.alimentos') }}" target="_blank" rel="noopener" class="btn btn-info btn-sm">Programar pedido</a></div>
                        </article>
                    </div>
                    <div class="promo-note"><i class="feather icon-shield mr-1"></i> Acceso seguro mediante la integración de tu cuenta VET SDI.</div>
                </section>
            </div>

            <div class="col-lg-6 mb-4">
                <section class="promo-section">
                    <div class="promo-section-title"><i class="fas fa-paw"></i><h4>Servicios y otros</h4></div>
                    <div class="promo-grid">
                        <article class="promo-card">
                            <img src="{{ asset('images/promociones/promo1.jpg') }}" alt="Peluquería y cuidado">
                            <div class="promo-card-body"><h5>Peluquería y cuidado</h5><p>Consulta servicios cercanos de higiene y cuidado para tu mascota.</p><a href="{{ route('paciente.mascotas.suscripcion_servicios') }}" class="btn btn-purple btn-sm">Ver servicios</a></div>
                        </article>
                        <article class="promo-card">
                            <img src="{{ asset('images/promociones/promo2.jpg') }}" alt="Hoteles para mascotas">
                            <div class="promo-card-body"><h5>Hoteles y estadías</h5><p>Revisa alternativas de alojamiento y reserva para tus mascotas.</p><a href="{{ route('paciente.mascotas.suscripcion_servicios') }}" class="btn btn-purple btn-sm">Consultar opciones</a></div>
                        </article>
                        <article class="promo-card">
                            <img src="{{ asset('images/promociones/venta2.jpg') }}" alt="Beneficios y convenios">
                            <div class="promo-card-body"><h5>Convenios de atención</h5><p>Consulta convenios registrados y beneficios disponibles para tu cuenta.</p><a href="{{ route('paciente.convenios') }}" class="btn btn-purple btn-sm">Mis convenios</a></div>
                        </article>
                    </div>
                    <div class="promo-note"><i class="feather icon-info mr-1"></i> La disponibilidad depende de los comercios y profesionales asociados.</div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
