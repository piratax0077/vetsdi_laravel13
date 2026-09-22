@extends('template.paciente.template')
@section('content')

<div class="pcoded-main-container">
    <div class="pcoded-content">

        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
<ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ ROUTE('paciente.home') }}" data-toggle="tooltip"
                                    data-placement="top" title="Volver a mi escritorio"><i
                                        class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="#">Suscripciones y facturación</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <style>

            @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap');

            #vetbilling{
                --bg:#F5F8F7;
                --surface:#FFFFFF;
                --ink:#0E211E;
                --muted:#65786F;
                --border:#E4EAE7;
                --primary:#0F6B62;
                --primary-dark:#0A4B45;
                --primary-light:#E6F2F0;
                --accent:#FF7A50;
                --accent-dark:#E15F37;
                --success:#2F9E68;
                --success-light:#E7F7EE;
                --warning:#C98A22;
                --warning-light:#FBF1DF;
                --danger:#D9483F;
                --danger-light:#FBEAE9;
                --radius:16px;
                --shadow:0 6px 24px rgba(14,33,30,.06);
                --shadow-lift:0 16px 40px rgba(14,33,30,.12);
                font-family:'Nunito',sans-serif;
                color:var(--ink);
            }

            #vetbilling *{ box-sizing:border-box; }

            #vetbilling .mono{ font-family:'JetBrains Mono',monospace; }

            #vetbilling h1,#vetbilling h2,#vetbilling h3,#vetbilling h4,#vetbilling h5{
                font-family:'Nunito',sans-serif;
                color:var(--ink);
                letter-spacing:-.01em;
            }

            /* ---------- layout shell ---------- */
            #vetbilling .vb-shell{
                background:var(--bg);
                border-radius:20px;
                padding:24px;
            }

            /* ---------- sidebar ---------- */
            #vetbilling .vb-sidebar{
                background:var(--surface);
                border-radius:var(--radius);
                box-shadow:var(--shadow);
                padding:14px;
                position:sticky;
                top:16px;
            }

            #vetbilling .vb-sidebar .vb-eyebrow{
                font-size:11px;
                text-transform:uppercase;
                letter-spacing:.08em;
                color:var(--muted);
                font-weight:600;
                padding:10px 14px 6px;
            }

            #vetbilling .vb-nav{ display:flex; flex-direction:column; gap:4px; }

            #vetbilling .vb-nav-link{
                display:flex;
                align-items:center;
                gap:12px;
                padding:12px 14px;
                border-radius:12px;
                color:var(--muted);
                font-weight:600;
                font-size:14.5px;
                text-decoration:none;
                border:none;
                background:transparent;
                width:100%;
                text-align:left;
                position:relative;
                transition:background .2s ease, color .2s ease;
                cursor:pointer;
            }

            #vetbilling .vb-nav-link .vb-ico{
                width:34px;height:34px;border-radius:9px;
                display:flex;align-items:center;justify-content:center;
                background:var(--bg);
                color:var(--primary);
                flex-shrink:0;
                transition:background .2s ease, color .2s ease, transform .2s ease;
            }

            #vetbilling .vb-nav-link:hover{ color:var(--ink); background:var(--bg); }
            #vetbilling .vb-nav-link:hover .vb-ico{ transform:translateY(-1px); }

            #vetbilling .vb-nav-link.active{
                color:var(--primary-dark);
                background:var(--primary-light);
            }

            #vetbilling .vb-nav-link.active .vb-ico{
                background:var(--primary);
                color:#fff;
            }

            #vetbilling .vb-side-note{
                margin-top:10px;
                padding:14px;
                border-radius:12px;
                background:linear-gradient(135deg,var(--primary-dark),var(--primary));
                color:#fff;
            }
            #vetbilling .vb-side-note p{ font-size:12.5px; opacity:.85; margin:2px 0 10px; }
            #vetbilling .vb-side-note strong{ font-size:14px; font-family:'Nunito',sans-serif; }
            #vetbilling .vb-side-note a{ color:#fff; font-size:12.5px; font-weight:700; text-decoration:underline; }

            /* ---------- panel card ---------- */
            #vetbilling .vb-panel{
                background:var(--surface);
                border-radius:var(--radius);
                box-shadow:var(--shadow);
                padding:26px;
                opacity:0;
                transform:translateY(8px);
                animation:vbFadeUp .45s ease forwards;
            }
            @keyframes vbFadeUp{ to{ opacity:1; transform:translateY(0); } }

            #vetbilling .vb-panel-head{
                display:flex;justify-content:space-between;align-items:flex-start;
                margin-bottom:20px; gap:12px; flex-wrap:wrap;
            }
            #vetbilling .vb-panel-head h5{ font-size:19px; font-weight:700; margin:0; }
            #vetbilling .vb-panel-head p{ color:var(--muted); font-size:13px; margin:4px 0 0; }

            /* ---------- buttons ---------- */
            #vetbilling .vb-btn{
                border:none; border-radius:10px; font-weight:600; font-size:13.5px;
                padding:10px 18px; cursor:pointer; transition:transform .15s ease, box-shadow .15s ease, background .15s ease, opacity .15s ease;
                display:inline-flex; align-items:center; gap:8px;
            }
            #vetbilling .vb-btn:active{ transform:scale(.97); }
            #vetbilling .vb-btn[disabled]{ opacity:.55; cursor:not-allowed; transform:none; }
            #vetbilling .vb-btn-primary{ background:var(--primary); color:#fff; }
            #vetbilling .vb-btn-primary:hover{ background:var(--primary-dark); box-shadow:0 8px 18px rgba(15,107,98,.25); }
            #vetbilling .vb-btn-accent{ background:var(--accent); color:#fff; }
            #vetbilling .vb-btn-accent:hover{ background:var(--accent-dark); box-shadow:0 8px 18px rgba(255,122,80,.3); }
            #vetbilling .vb-btn-ghost{ background:var(--bg); color:var(--ink); }
            #vetbilling .vb-btn-ghost:hover{ background:var(--border); }
            #vetbilling .vb-btn-outline{ background:transparent; color:var(--primary); border:1.5px solid var(--primary); }
            #vetbilling .vb-btn-outline:hover{ background:var(--primary-light); }
            #vetbilling .vb-btn-danger-outline{ background:transparent; color:var(--danger); border:1.5px solid var(--danger); }
            #vetbilling .vb-btn-danger-outline:hover{ background:var(--danger-light); }
            #vetbilling .vb-btn-block{ width:100%; justify-content:center; }
            #vetbilling .vb-btn-dark{ background:var(--ink); color:#fff; }

            /* ---------- subscriptions list ---------- */
            #vetbilling .vb-sub-card{
                border:1px solid var(--border);
                border-radius:14px;
                padding:16px 18px;
                display:flex;
                align-items:center;
                gap:16px;
                margin-bottom:12px;
                transition:border-color .2s ease, box-shadow .2s ease;
            }
            #vetbilling .vb-sub-card:hover{ border-color:var(--primary); box-shadow:var(--shadow); }

            #vetbilling .vb-check{
                width:20px;height:20px;border-radius:6px;border:2px solid var(--border);
                cursor:pointer; flex-shrink:0; accent-color:var(--primary);
            }

            #vetbilling .vb-sub-icon{
                width:44px;height:44px;border-radius:11px;background:var(--primary-light);
                color:var(--primary); display:flex;align-items:center;justify-content:center;flex-shrink:0;
            }

            #vetbilling .vb-sub-body{ flex:1; min-width:180px; }
            #vetbilling .vb-sub-body h6{ margin:0 0 2px; font-family:'Nunito',sans-serif; font-weight:700; font-size:15px; }
            #vetbilling .vb-sub-dates{ font-size:12px; color:var(--muted); }

            #vetbilling .vb-pill{
                display:inline-flex; align-items:center; gap:6px;
                font-size:11.5px; font-weight:700; padding:4px 10px; border-radius:999px;
            }
            #vetbilling .vb-pill-success{ background:var(--success-light); color:var(--success); }
            #vetbilling .vb-pill-success::before{ content:''; width:6px;height:6px;border-radius:50%;background:var(--success); }

            #vetbilling .vb-progress-wrap{ width:140px; flex-shrink:0; }
            #vetbilling .vb-progress-label{ font-size:10.5px; color:var(--muted); margin-bottom:4px; display:flex; justify-content:space-between; }
            #vetbilling .vb-progress-track{ height:6px; border-radius:99px; background:var(--border); overflow:hidden; }
            #vetbilling .vb-progress-fill{ height:100%; border-radius:99px; background:linear-gradient(90deg,var(--primary),var(--accent)); width:0%; transition:width 1s cubic-bezier(.22,1,.36,1); }

            /* ---------- billing / card mockup ---------- */
            #vetbilling .vb-vcard{
                position:relative;
                width:100%; max-width:340px;
                aspect-ratio:1.586/1;
                border-radius:18px;
                padding:22px;
                color:#fff;
                background:radial-gradient(120% 140% at 0% 0%, #17847A 0%, var(--primary-dark) 55%, #071F1C 100%);
                box-shadow:var(--shadow-lift);
                display:flex; flex-direction:column; justify-content:space-between;
                overflow:hidden;
                transform-style:preserve-3d;
                transition:transform .25s ease;
            }
            #vetbilling .vb-vcard:hover{ transform:translateY(-4px); }
            #vetbilling .vb-vcard::after{
                content:'';
                position:absolute; right:-30px; bottom:-30px; width:160px; height:160px;
                background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><g fill='white' fill-opacity='0.08'><ellipse cx='32' cy='40' rx='16' ry='13'/><ellipse cx='14' cy='22' rx='7' ry='9'/><ellipse cx='50' cy='22' rx='7' ry='9'/><ellipse cx='24' cy='10' rx='5.5' ry='7'/><ellipse cx='40' cy='10' rx='5.5' ry='7'/></g></svg>") no-repeat center/contain;
            }
            #vetbilling .vb-vcard .vb-chip{
                width:38px;height:28px;border-radius:6px;
                background:linear-gradient(135deg,#E9D9A8,#C9A94D);
            }
            #vetbilling .vb-vcard .vb-vnum{ font-family:'JetBrains Mono',monospace; font-size:17px; letter-spacing:2px; }
            #vetbilling .vb-vcard .vb-vfoot{ display:flex; justify-content:space-between; align-items:flex-end; font-size:11px; }
            #vetbilling .vb-vcard .vb-vfoot span{ display:block; opacity:.65; font-size:9px; text-transform:uppercase; letter-spacing:.06em; }
            #vetbilling .vb-vcard .vb-brand{ font-family:'Nunito',sans-serif; font-weight:700; font-size:13px; }

            #vetbilling .vb-billing-row{ display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start; }
            #vetbilling .vb-billing-meta{ flex:1; min-width:220px; }
            #vetbilling .vb-billing-meta .vb-pill-success{ margin-bottom:10px; }

            #vetbilling .vb-empty-card{
                border:1.5px dashed var(--border);
                border-radius:14px;
                padding:26px;
                text-align:center;
                color:var(--muted);
                background:var(--bg);
            }

            /* ---------- pricing cards ---------- */
            #vetbilling .vb-plans-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
            @media(max-width:900px){ #vetbilling .vb-plans-grid{ grid-template-columns:1fr; } }

            #vetbilling .vb-plan{
                border:1.5px solid var(--border);
                border-radius:18px;
                background:var(--surface);
                padding:22px 20px;
                display:flex; flex-direction:column;
                position:relative;
                transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease;
            }
            #vetbilling .vb-plan:hover{ transform:translateY(-6px); box-shadow:var(--shadow-lift); border-color:var(--primary); }

            #vetbilling .vb-plan.is-active{ border-color:var(--primary); background:linear-gradient(180deg,var(--primary-light),var(--surface) 40%); }
            #vetbilling .vb-plan.is-featured{ border-color:var(--accent); }

            #vetbilling .vb-ribbon{
                position:absolute; top:-11px; left:20px;
                background:var(--accent); color:#fff; font-size:11px; font-weight:700;
                padding:4px 12px; border-radius:999px; box-shadow:0 4px 10px rgba(255,122,80,.35);
            }

            #vetbilling .vb-plan-name{ font-size:16px; font-weight:700; margin:6px 0 2px; }
            #vetbilling .vb-plan-price{ font-family:'Nunito',sans-serif; font-size:32px; font-weight:800; line-height:1; margin-top:6px; }
            #vetbilling .vb-plan-price sup{ font-size:14px; font-weight:600; top:-14px; }
            #vetbilling .vb-plan-period{ font-size:11.5px; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; }

            #vetbilling .vb-capacity{ margin:16px 0; }
            #vetbilling .vb-capacity-label{ font-size:11px; color:var(--muted); margin-bottom:6px; display:flex; justify-content:space-between; }
            #vetbilling .vb-paws{ display:flex; gap:5px; }
            #vetbilling .vb-paw{ width:20px; height:20px; opacity:.18; transform:scale(.7); transition:opacity .3s ease, transform .3s ease; }
            #vetbilling .vb-paw.filled{ opacity:1; transform:scale(1); }
            #vetbilling .vb-paw svg{ width:100%; height:100%; fill:var(--primary); }
            #vetbilling .vb-plan.is-featured .vb-paw.filled svg{ fill:var(--accent); }

            #vetbilling .vb-features{ list-style:none; padding:0; margin:6px 0 18px; flex:1; }
            #vetbilling .vb-features li{ display:flex; gap:8px; align-items:flex-start; font-size:13px; color:var(--ink); margin-bottom:9px; }
            #vetbilling .vb-features li svg{ flex-shrink:0; margin-top:2px; width:15px;height:15px; }
            #vetbilling .vb-features li svg path{ stroke:var(--success); }

            /* ---------- modal / toast ---------- */
            #vetbilling .vb-field label{ font-size:12.5px; font-weight:600; color:var(--muted); margin-bottom:4px; display:block; }
            #vetbilling .vb-field input{
                border:1.5px solid var(--border); border-radius:10px; padding:10px 12px; width:100%;
                font-family:'Nunito',sans-serif; font-size:14px; transition:border-color .2s ease, box-shadow .2s ease;
            }
            #vetbilling .vb-field input:focus{ outline:none; border-color:var(--primary); box-shadow:0 0 0 3px var(--primary-light); }

            .vb-toast-stack{ position:fixed; right:20px; bottom:20px; z-index:2000; display:flex; flex-direction:column; gap:10px; }
            .vb-toast{
                background:var(--ink,#0E211E); color:#fff; padding:12px 16px; border-radius:12px;
                font-family:'Nunito',sans-serif; font-size:13.5px; display:flex; align-items:center; gap:10px;
                box-shadow:0 10px 30px rgba(0,0,0,.25); min-width:220px;
                animation:vbToastIn .3s ease forwards;
            }
            .vb-toast.success{ background:#123B30; }
            .vb-toast.error{ background:#4A1E1B; }
            @keyframes vbToastIn{ from{ opacity:0; transform:translateY(12px);} to{opacity:1; transform:translateY(0);} }
            .vb-toast.out{ animation:vbToastOut .25s ease forwards; }
            @keyframes vbToastOut{ to{ opacity:0; transform:translateX(12px); } }

            .vb-confirm-backdrop{
                position:fixed; inset:0; background:rgba(10,20,18,.45); backdrop-filter:blur(2px);
                display:flex; align-items:center; justify-content:center; z-index:2000; opacity:0;
                transition:opacity .2s ease;
            }
            .vb-confirm-backdrop.show{ opacity:1; }
            .vb-confirm-box{
                background:#fff; border-radius:16px; padding:24px; width:340px; max-width:90vw;
                transform:translateY(10px) scale(.97); transition:transform .2s ease;
                font-family:'Nunito',sans-serif; box-shadow:var(--shadow-lift);
            }
            .vb-confirm-backdrop.show .vb-confirm-box{ transform:translateY(0) scale(1); }
            .vb-confirm-box h6{ font-family:'Nunito',sans-serif; font-size:17px; margin:0 0 6px; }
            .vb-confirm-box p{ font-size:13.5px; color:var(--muted,#65786F); margin:0 0 18px; }
            .vb-confirm-actions{ display:flex; gap:10px; justify-content:flex-end; }

            @media (prefers-reduced-motion: reduce){
                #vetbilling *{ animation-duration:.001ms !important; transition-duration:.001ms !important; }
            }
        </style>

        <div id="vetbilling">
            @php
                // Simulación backend (reemplazar por tu lógica real)
                $hasCard = true;
                $activePlan = 'basic'; // free | basic | plus
                $cardBrand = 'Visa';
                $cardLast4 = '4242';
                $cardHolder = 'Nombre del titular';

                $plans = [
                    'free' => [
                        'label' => 'Plan Gratuito',
                        'price' => '0',
                        'period' => null,
                        'pets' => 1,
                        'features' => ['Agenda básica de citas', 'Recordatorios por correo', 'Soporte por chat'],
                    ],
                    'basic' => [
                        'label' => 'Plan Básico',
                        'price' => '2.990',
                        'period' => 'año',
                        'pets' => 2,
                        'features' => ['Historial clínico digital', 'Cartilla de vacunas al día', 'Recordatorios inteligentes'],
                    ],
                    'plus' => [
                        'label' => 'Vet Plus',
                        'price' => '5.990',
                        'period' => 'año',
                        'pets' => 5,
                        'features' => ['Historial clínico completo', 'Exámenes y recetas online', 'Línea prioritaria 24/7'],
                        'featured' => true,
                    ],
                ];
                $maxPets = max(array_column($plans, 'pets'));
            @endphp

    
                <div class="row">

                    <!-- SIDEBAR -->
                    <div class="col-md-4 mb-3">
                        <div class="vb-sidebar">
                            <div class="vb-eyebrow">Cuenta</div>
                            <div class="vb-nav" id="vbNav">
                                <a class="vb-nav-link active" data-toggle="pill" href="#suscripciones">
                                    <span class="vb-ico"><i class="feather icon-repeat" style="width:16px;height:16px;"></i></span>
                                    Suscripciones
                                </a>
                                <a class="vb-nav-link" data-toggle="pill" href="#facturacion">
                                    <span class="vb-ico"><i class="feather icon-credit-card" style="width:16px;height:16px;"></i></span>
                                    Método de pago
                                </a>
                                <a class="vb-nav-link" data-toggle="pill" href="#planes">
                                    <span class="vb-ico"><i class="feather icon-layers" style="width:16px;height:16px;"></i></span>
                                    Planes
                                </a>
                            </div>

                            <div class="vb-side-note">
                                <strong>¿Necesitas más mascotas?</strong>
                                <p>Vet Plus cubre hasta 5 mascotas con historial clínico completo.</p>
                                <a data-toggle="pill" href="#planes" role="button">Ver planes →</a>
                            </div>
                        </div>
                    </div>

                    <!-- CONTENT -->
                    <div class="col-md-8">
                        <div class="tab-content">

                            <!-- ================= SUSCRIPCIONES ================= -->
                            <div class="tab-pane fade show active" id="suscripciones">
                                <div class="vb-panel">
                                    <div class="vb-panel-head">
                                        <div>
                                            <h5>Mis suscripciones</h5>
                                            <p>Revisa el estado y la vigencia de tus planes contratados.</p>
                                        </div>
                                        <button class="vb-btn vb-btn-primary" id="btnPagarSeleccionadas">
                                            <i class="feather icon-credit-card" style="width:15px;height:15px;"></i>
                                            Pagar seleccionadas
                                        </button>
                                    </div>

                                    <div class="vb-sub-card" data-start="2026-06-01" data-end="2027-06-01">
                                        <input type="checkbox" class="vb-check">
                                        <div class="vb-sub-icon">
                                            <i class="feather icon-shield" style="width:20px;height:20px;"></i>
                                        </div>
                                        <div class="vb-sub-body">
                                            <h6>Plan Básico</h6>
                                            <div class="vb-sub-dates mono">01-06-2026 &nbsp;→&nbsp; 01-06-2027</div>
                                        </div>
                                        <span class="vb-pill vb-pill-success">Activo</span>
                                        <div class="vb-progress-wrap">
                                            <div class="vb-progress-label"><span>Vigencia</span><span class="vb-progress-pct">—</span></div>
                                            <div class="vb-progress-track"><div class="vb-progress-fill"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ================= FACTURACION ================= -->
                            <div class="tab-pane fade" id="facturacion">
                                <div class="vb-panel">
                                    <div class="vb-panel-head">
                                        <div>
                                            <h5>Método de pago</h5>
                                            <p>Gestiona la tarjeta usada para cobrar tus suscripciones.</p>
                                        </div>
                                    </div>

                                    @if($hasCard)
                                        <div class="vb-billing-row">
                                            <div class="vb-vcard">
                                                <div class="vb-vfoot" style="align-items:center;">
                                                    <div class="vb-chip"></div>
                                                    <span class="vb-brand">{{ $cardBrand }}</span>
                                                </div>
                                                <div class="vb-vnum">•••• •••• •••• {{ $cardLast4 }}</div>
                                                <div class="vb-vfoot">
                                                    <div><span>Titular</span>{{ $cardHolder }}</div>
                                                    <div><span>Expira</span>08/29</div>
                                                </div>
                                            </div>

                                            <div class="vb-billing-meta">
                                                <span class="vb-pill vb-pill-success">Tarjeta activa</span>
                                                <p style="font-size:13px;color:var(--muted);margin:0 0 16px;">
                                                    Se usará automáticamente para renovar tu plan y cualquier compra dentro de la app.
                                                </p>
                                                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                                    <button class="vb-btn vb-btn-outline" data-toggle="modal" data-target="#cardModal">
                                                        <i class="feather icon-repeat" style="width:14px;height:14px;"></i>
                                                        Cambiar tarjeta
                                                    </button>
                                                    <form method="POST" action="/paciente/billing/delete-card" id="deleteCardForm" style="margin:0;">
                                                        @csrf
                                                        <button type="button" class="vb-btn vb-btn-danger-outline" id="btnDeleteCard">
                                                            <i class="feather icon-trash-2" style="width:14px;height:14px;"></i>
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="vb-empty-card">
                                            <i class="feather icon-credit-card" style="width:26px;height:26px;opacity:.5;"></i>
                                            <p style="margin:10px 0 16px;">Todavía no registras una tarjeta de pago.</p>
                                            <button class="vb-btn vb-btn-primary" data-toggle="modal" data-target="#cardModal">
                                                + Agregar tarjeta
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- ================= PLANES ================= -->
                            <div class="tab-pane fade" id="planes">
                                <div class="vb-panel">
                                    <div class="vb-panel-head">
                                        <div>
                                            <h5>Elige el plan de tu mascota</h5>
                                            <p>Puedes cambiar de plan cuando quieras, se ajusta en tu próximo cobro.</p>
                                        </div>
                                    </div>

                                    <div class="vb-plans-grid">
                                        @foreach($plans as $key => $plan)
                                            @php
                                                $isActive = $activePlan === $key;
                                                $isFeatured = !empty($plan['featured']);
                                            @endphp
                                            <div class="vb-plan {{ $isActive ? 'is-active' : '' }} {{ $isFeatured ? 'is-featured' : '' }}">
                                                @if($isFeatured)
                                                    <span class="vb-ribbon">Recomendado</span>
                                                @endif

                                                <div class="vb-plan-name">{{ $plan['label'] }}</div>
                                                <div class="vb-plan-price">
                                                    @if($plan['price'] === '0')
                                                        $0
                                                    @else
                                                        <sup>$</sup>{{ $plan['price'] }}
                                                    @endif
                                                </div>
                                                @if($plan['period'])
                                                    <div class="vb-plan-period">por {{ $plan['period'] }}</div>
                                                @else
                                                    <div class="vb-plan-period">siempre gratis</div>
                                                @endif

                                                <div class="vb-capacity">
                                                    <div class="vb-capacity-label">
                                                        <span>Mascotas incluidas</span>
                                                        <span class="mono">{{ $plan['pets'] }}/{{ $maxPets }}</span>
                                                    </div>
                                                    <div class="vb-paws" data-filled="{{ $plan['pets'] }}">
                                                        @for($i = 0; $i < $maxPets; $i++)
                                                            <span class="vb-paw {{ $i < $plan['pets'] ? 'filled' : '' }}">
                                                                <svg viewBox="0 0 64 64"><g><ellipse cx="32" cy="42" rx="16" ry="13"/><ellipse cx="14" cy="22" rx="7" ry="9"/><ellipse cx="50" cy="22" rx="7" ry="9"/><ellipse cx="24" cy="9" rx="5.5" ry="7"/><ellipse cx="40" cy="9" rx="5.5" ry="7"/></g></svg>
                                                            </span>
                                                        @endfor
                                                    </div>
                                                </div>

                                                <ul class="vb-features">
                                                    @foreach($plan['features'] as $feature)
                                                        <li>
                                                            <svg viewBox="0 0 20 20" fill="none"><path d="M4 10.5L8 14.5L16 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                            {{ $feature }}
                                                        </li>
                                                    @endforeach
                                                </ul>

                                                @if($isActive)
                                                    <button class="vb-btn vb-btn-dark vb-btn-block" disabled>
                                                        Plan activo
                                                    </button>
                                                @else
                                                    <button class="vb-btn {{ $isFeatured ? 'vb-btn-accent' : 'vb-btn-outline' }} vb-btn-block change-plan" data-plan="{{ $key }}" data-label="{{ $plan['label'] }}" data-has-card="{{ $hasCard ? '1' : '0' }}">
                                                        {{ !$hasCard ? 'Contratar plan' : 'Cambiar plan' }}
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

        </div>

    </div>
</div>

<!-- ================= MODAL TARJETA ================= -->
<div class="modal fade" id="cardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; overflow:hidden;">

            <div class="modal-header" style="border-bottom:1px solid #E4EAE7;">
                <h5 class="modal-title" style="font-family:'Nunito',sans-serif;">Tarjeta de pago</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body" id="vetbillingModalBody" style="padding:22px;">

                <p id="cardModalContext" style="display:none; font-size:13px; color:var(--muted); background:var(--primary-light); padding:10px 12px; border-radius:10px; margin:0 0 16px;"></p>

                <div class="vb-vcard" id="livePreviewCard" style="margin:0 auto 22px; transform:none;">
                    <div class="vb-vfoot" style="align-items:center;">
                        <div class="vb-chip"></div>
                        <span class="vb-brand" id="previewBrand">Vet Pay</span>
                    </div>
                    <div class="vb-vnum" id="previewNumber">•••• •••• •••• ••••</div>
                    <div class="vb-vfoot">
                        <div><span>Titular</span><span id="previewName">Nombre del titular</span></div>
                        <div><span>Expira</span><span id="previewExpiry">MM/AA</span></div>
                    </div>
                </div>

                <div class="vb-field" style="margin-bottom:14px;">
                    <label>Número de tarjeta</label>
                    <input class="mono" id="inputCardNumber" placeholder="4242 4242 4242 4242" maxlength="19">
                </div>

                <div class="vb-field" style="margin-bottom:14px;">
                    <label>Nombre titular</label>
                    <input id="inputCardName" placeholder="Como aparece en la tarjeta">
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="vb-field">
                            <label>Expira</label>
                            <input class="mono" id="inputCardExpiry" placeholder="MM/AA" maxlength="5">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="vb-field">
                            <label>CVC</label>
                            <input class="mono" id="inputCardCvc" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer" style="border-top:1px solid #E4EAE7;">
                <button class="vb-btn vb-btn-ghost" data-dismiss="modal">Cancelar</button>
                <button class="vb-btn vb-btn-primary" id="btnSaveCard">Guardar tarjeta</button>
            </div>

        </div>
    </div>
</div>

<div class="vb-toast-stack" id="vbToastStack"></div>

<script>
(function () {
    /* ---------------- toast ---------------- */
    function toast(message, type) {
        var stack = document.getElementById('vbToastStack');
        var el = document.createElement('div');
        el.className = 'vb-toast ' + (type || '');
        el.innerHTML = message;
        stack.appendChild(el);
        setTimeout(function () {
            el.classList.add('out');
            setTimeout(function () { el.remove(); }, 250);
        }, 3200);
    }

    /* ---------------- custom confirm (replaces window.confirm) ---------------- */
    function vbConfirm(title, description) {
        return new Promise(function (resolve) {
            var backdrop = document.createElement('div');
            backdrop.className = 'vb-confirm-backdrop';
            backdrop.innerHTML =
                '<div class="vb-confirm-box">' +
                    '<h6>' + title + '</h6>' +
                    '<p>' + description + '</p>' +
                    '<div class="vb-confirm-actions">' +
                        '<button class="vb-btn vb-btn-ghost" data-act="cancel">Cancelar</button>' +
                        '<button class="vb-btn vb-btn-primary" data-act="ok">Confirmar</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(backdrop);
            requestAnimationFrame(function () { backdrop.classList.add('show'); });

            function close(result) {
                backdrop.classList.remove('show');
                setTimeout(function () { backdrop.remove(); }, 200);
                resolve(result);
            }
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) close(false);
                var act = e.target.closest('[data-act]');
                if (act) close(act.dataset.act === 'ok');
            });
        });
    }

    /* ---------------- renewal progress bar ---------------- */
    document.querySelectorAll('.vb-sub-card').forEach(function (card) {
        var start = new Date(card.dataset.start);
        var end = new Date(card.dataset.end);
        var now = new Date();
        var pct = Math.min(100, Math.max(0, ((now - start) / (end - start)) * 100));
        var daysLeft = Math.max(0, Math.ceil((end - now) / (1000 * 60 * 60 * 24)));
        var fill = card.querySelector('.vb-progress-fill');
        var label = card.querySelector('.vb-progress-pct');
        label.textContent = daysLeft + ' días restantes';
        requestAnimationFrame(function () {
            setTimeout(function () { fill.style.width = pct + '%'; }, 150);
        });
    });

    /* ---------------- pay selected ---------------- */
    var payBtn = document.getElementById('btnPagarSeleccionadas');
    if (payBtn) {
        payBtn.addEventListener('click', function () {
            var checked = document.querySelectorAll('.vb-sub-card .vb-check:checked').length;
            if (!checked) {
                toast('Selecciona al menos una suscripción para pagar.', 'error');
                return;
            }
            toast(checked + ' suscripción(es) enviada(s) a pago.', 'success');
        });
    }

    /* ---------------- delete card ---------------- */
    var btnDeleteCard = document.getElementById('btnDeleteCard');
    if (btnDeleteCard) {
        btnDeleteCard.addEventListener('click', function () {
            vbConfirm('¿Eliminar tarjeta?', 'No podrás renovar tus planes hasta registrar una nueva tarjeta.')
                .then(function (ok) {
                    if (ok) document.getElementById('deleteCardForm').submit();
                });
        });
    }

    /* ---------------- change plan (with add-card fallback) ---------------- */
    var pendingPlan = null; // { plan, label, button, originalHtml } — set when a card must be added first

    function submitPlanChange(plan, label, btn) {
        var originalHtml = btn ? btn.innerHTML : null;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Actualizando…';
        }

        fetch("/paciente/suscripcion/cambiar-plan", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ plan: plan })
        })
        .then(function (r) { return r.json(); })
        .then(function () {
            toast('Cambiaste al ' + label + ' correctamente.', 'success');
            setTimeout(function () { location.reload(); }, 900);
        })
        .catch(function () {
            toast('No pudimos actualizar tu plan. Intenta de nuevo.', 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    document.querySelectorAll('.change-plan').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var plan = this.dataset.plan;
            var label = this.dataset.label;
            var hasCard = this.dataset.hasCard === '1';
            var self = this;

            if (!hasCard) {
                // No hay tarjeta registrada: pedirla primero y retomar el cambio de plan después.
                pendingPlan = { plan: plan, label: label, button: self };
                var ctx = document.getElementById('cardModalContext');
                if (ctx) {
                    ctx.textContent = 'Agrega una tarjeta para contratar "' + label + '". Se usará para tu próximo cobro.';
                    ctx.style.display = 'block';
                }
                if (typeof $ !== 'undefined') {
                    $('#cardModal').modal('show');
                } else {
                    document.getElementById('cardModal').classList.add('show');
                }
                return;
            }

            vbConfirm('Cambiar de plan', 'Vas a cambiar a "' + label + '". El nuevo monto se prorrateará en tu próximo cobro.')
                .then(function (ok) {
                    if (!ok) return;
                    submitPlanChange(plan, label, self);
                });
        });
    });

    /* ---------------- live card preview in modal ---------------- */
    var inNumber = document.getElementById('inputCardNumber');
    var inName = document.getElementById('inputCardName');
    var inExpiry = document.getElementById('inputCardExpiry');
    var pvNumber = document.getElementById('previewNumber');
    var pvName = document.getElementById('previewName');
    var pvExpiry = document.getElementById('previewExpiry');

    if (inNumber) {
        inNumber.addEventListener('input', function () {
            var digits = this.value.replace(/\D/g, '').slice(0, 16);
            this.value = digits.replace(/(.{4})/g, '$1 ').trim();
            var grouped = digits.padEnd(16, '•').replace(/(.{4})/g, '$1 ').trim();
            pvNumber.textContent = grouped;
        });
    }
    if (inName) {
        inName.addEventListener('input', function () {
            pvName.textContent = this.value.trim() || 'Nombre del titular';
        });
    }
    if (inExpiry) {
        inExpiry.addEventListener('input', function () {
            var digits = this.value.replace(/\D/g, '').slice(0, 4);
            if (digits.length > 2) digits = digits.slice(0, 2) + '/' + digits.slice(2);
            this.value = digits;
            pvExpiry.textContent = digits || 'MM/AA';
        });
    }

    var btnSaveCard = document.getElementById('btnSaveCard');
    if (btnSaveCard) {
        btnSaveCard.addEventListener('click', function () {
            if (!inNumber.value || !inName.value || !inExpiry.value) {
                toast('Completa los datos de la tarjeta.', 'error');
                return;
            }
            var self = this;
            self.disabled = true;
            self.textContent = 'Guardando…';
            setTimeout(function () {
                self.disabled = false;
                self.textContent = 'Guardar tarjeta';
                toast('Tarjeta guardada correctamente.', 'success');
                if (typeof $ !== 'undefined') { $('#cardModal').modal('hide'); }

                // Si la tarjeta se agregó para poder contratar un plan, continuamos con ese cambio.
                if (pendingPlan) {
                    var p = pendingPlan;
                    pendingPlan = null;
                    setTimeout(function () { submitPlanChange(p.plan, p.label, p.button); }, 300);
                }
            }, 700);
        });
    }

    // Limpia el mensaje de contexto cuando el modal se cierra o se abre desde "Agregar/Cambiar tarjeta".
    var cardModalEl = document.getElementById('cardModal');
    if (cardModalEl && typeof $ !== 'undefined') {
        $(cardModalEl).on('hidden.bs.modal', function () {
            var ctx = document.getElementById('cardModalContext');
            if (ctx && !pendingPlan) { ctx.style.display = 'none'; ctx.textContent = ''; }
        });
        $(cardModalEl).on('show.bs.modal', function (e) {
            var opener = e.relatedTarget;
            var ctx = document.getElementById('cardModalContext');
            if (ctx && opener && !opener.classList.contains('change-plan')) {
                ctx.style.display = 'none';
                ctx.textContent = '';
            }
        });
    }

    /* ---------------- pill tabs (forces Bootstrap's tab plugin on every click) ---------------- */
    function showPill(link) {
        var target = link.getAttribute('href');
        var pane = document.querySelector(target);
        if (!pane) return;

        if (typeof $ !== 'undefined' && typeof $.fn.tab === 'function') {
            $(link).tab('show');
        } else {
            // Fallback manual toggle if Bootstrap's JS isn't available for some reason.
            document.querySelectorAll('#vetbilling .tab-pane').forEach(function (p) {
                p.classList.remove('show', 'active');
            });
            pane.classList.add('show', 'active');
        }

        document.querySelectorAll('.vb-nav-link').forEach(function (l) { l.classList.remove('active'); });
        var trigger = document.querySelector('.vb-nav-link[href="' + target + '"]');
        if (trigger) trigger.classList.add('active');

        var panel = pane.querySelector('.vb-panel');
        if (panel) {
            panel.style.animation = 'none';
            void panel.offsetWidth;
            panel.style.animation = 'vbFadeUp .45s ease forwards';
        }

        if (typeof feather !== 'undefined') { feather.replace(); }
    }

    document.querySelectorAll('[data-toggle="pill"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            showPill(this);
        });
    });

    if (typeof feather !== 'undefined') { feather.replace(); }
})();
</script>

@endsection