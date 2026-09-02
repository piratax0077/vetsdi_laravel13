@extends('template.profesional.template')
@section('page-styles')
<style>
    #modalMascotaDetalle .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 65px rgba(10,45,58,.3)}
    #modalMascotaDetalle .modal-header{padding:16px 22px;background:linear-gradient(120deg,#147a70,#12b8b6)!important;border:0}
    #modalMascotaDetalle .modal-title{font-weight:700;letter-spacing:.1px}
    #modalMascotaDetalle .close{color:#fff;opacity:1;text-shadow:none;background:rgba(0,74,76,.38);border-radius:50%;width:38px;height:38px;padding:0;margin:-2px -4px -2px auto;display:flex;align-items:center;justify-content:center}
    .pet-detail-hero{display:flex;align-items:center;padding:22px;background:linear-gradient(135deg,#eefafa,#f8fbfd);border-bottom:1px solid #e1ecef}
    .pet-detail-photo{width:132px;height:132px;flex:0 0 132px;border-radius:24px;object-fit:cover;background:#fff;border:5px solid #fff;box-shadow:0 10px 24px rgba(20,122,112,.2)}
    .pet-detail-heading{min-width:0;margin-left:22px}
    .pet-detail-heading h4{color:#24364b;font-size:1.55rem;font-weight:750;margin:0 0 8px}
    .pet-detail-subtitle{color:#6d7d8f;margin:0}
    .pet-detail-badge{display:inline-flex;align-items:center;margin-top:12px;padding:5px 10px;border-radius:999px;background:#dff5f2;color:#117c73;font-size:.78rem;font-weight:700}
    .pet-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:20px 22px 8px}
    .pet-detail-item{display:flex;align-items:center;min-height:68px;padding:12px 14px;border:1px solid #e4eaef;border-radius:12px;background:#fff}
    .pet-detail-item i{width:34px;height:34px;flex:0 0 34px;margin-right:11px;border-radius:10px;background:#e8f7f5;color:#168f87;display:flex;align-items:center;justify-content:center;font-size:16px}
    .pet-detail-label{display:block;color:#8491a0;font-size:.72rem;font-weight:700;letter-spacing:.35px;text-transform:uppercase;line-height:1.2}
    .pet-detail-value{display:block;color:#33445a;font-weight:650;margin-top:3px;word-break:break-word}
    .pet-gallery{padding:12px 22px 22px}
    .pet-gallery-title{color:#33445a;font-weight:700;margin-bottom:10px}
    .pet-gallery-list{display:flex;flex-wrap:wrap;gap:10px}
    .pet-gallery-thumb{width:88px;height:88px;object-fit:cover;border-radius:12px;border:2px solid #fff;box-shadow:0 3px 12px rgba(30,55,75,.16);cursor:pointer}
    #modalResumenContacto .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 65px rgba(10,45,58,.3)}
    #modalResumenContacto .modal-header{align-items:center;padding:16px 20px;background:linear-gradient(120deg,#147a70,#12b8b6)!important;border:0}
    #modalResumenContacto .modal-title{font-weight:700;letter-spacing:.1px}
    #modalResumenContacto .close{display:flex;align-items:center;justify-content:center;width:38px;height:38px;padding:0;margin:-3px -3px -3px auto;color:#fff;opacity:1;text-shadow:none;background:rgba(0,69,72,.4);border-radius:50%}
    #modalResumenContacto .modal-body{padding:0;background:#f5f9fa}
    .contact-summary-hero{display:flex;align-items:center;padding:20px;background:linear-gradient(135deg,#e8f8f6,#f8fbfc);border-bottom:1px solid #dce9ec}
    .contact-summary-avatar{display:flex;align-items:center;justify-content:center;width:58px;height:58px;flex:0 0 58px;margin-right:14px;color:#fff;font-size:25px;background:linear-gradient(135deg,#149187,#16bfbd);border-radius:17px;box-shadow:0 8px 20px rgba(20,145,135,.22)}
    .contact-summary-hero h5{margin:0 0 4px;color:#26384d;font-weight:750}
    .contact-summary-hero p{margin:0;color:#718092;font-size:.84rem}
    .contact-summary-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:11px;padding:18px 20px}
    .contact-summary-item{display:flex;align-items:center;min-height:68px;padding:12px 13px;background:#fff;border:1px solid #e1e9ed;border-radius:12px;box-shadow:0 2px 8px rgba(34,61,78,.04)}
    .contact-summary-item--wide{grid-column:1/-1}
    .contact-summary-icon{display:flex;align-items:center;justify-content:center;width:36px;height:36px;flex:0 0 36px;margin-right:11px;color:#168e87;font-size:16px;background:#e7f7f5;border-radius:10px}
    .contact-summary-label{display:block;margin-bottom:3px;color:#8995a2;font-size:.69rem;font-weight:700;letter-spacing:.35px;text-transform:uppercase}
    .contact-summary-value{display:block;color:#34465c;font-weight:650;line-height:1.25;word-break:break-word}
    a.contact-summary-value:hover{color:#137f78;text-decoration:none}
    #modalResumenContacto .modal-footer{padding:12px 20px;background:#fff;border-top:1px solid #e2e9ed}
    @media(max-width:575.98px){.pet-detail-hero{align-items:flex-start;padding:18px}.pet-detail-photo{width:96px;height:96px;flex-basis:96px;border-radius:18px}.pet-detail-heading{margin-left:15px}.pet-detail-heading h4{font-size:1.25rem}.pet-detail-grid{grid-template-columns:1fr;padding:16px}}
    @media(max-width:575.98px){.contact-summary-grid{grid-template-columns:1fr;padding:15px}.contact-summary-item--wide{grid-column:auto}.contact-summary-hero{padding:17px}}
</style>
@endsection
@section('content')
    @php
        $contextosCentro = $contextosCentro ?? [];
        $contextoActivo = $contextoActivo ?? [];
        $search = $search ?? '';
    @endphp

    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!--Header-->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
<ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('profesional.home') }}" data-toggle="tooltip" data-placement="top" title="Volver a mi escritorio"><i class="feather icon-home"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="#">Mascotas y responsables</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--Cierre: Header-->

            <!-- Tabla mis clientes -->
            <!--Este formulario muestra los pacientes que alguna vez atendió el profesional (relacion: id_paciente/id_profesional)-->
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header text-center bg-info">
                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg mb-1 align-botton d-flex justify-content-between">
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('profesional.home') }}" class="btn btn-light btn-sm mr-2" title="Volver al escritorio">
                                        <i class="feather icon-arrow-left"></i> Volver
                                    </a>
                                    <h4 class="text-white f-20 mb-0">Mascotas y responsables</h4>
                                </div>
                                <button class="btn btn-purple btn-sm d-inline float-md-right" onclick="enviar_difusion_pacientes()"><i class="feather icon-mail"></i> Enviar mensaje de difusión</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('profesional.pacientes') }}" class="mb-4">
                            <div class="form-row align-items-end">
                                <div class="col-md-4 mb-2">
                                    <label class="floating-label-activo-sm mb-0">Centro activo</label>
                                    <select name="contexto" class="form-control form-control-sm">
                                        @forelse ($contextosCentro as $contexto)
                                            <option value="{{ $contexto['key'] ?? '' }}" @selected(($contextoActivo['key'] ?? '') === ($contexto['key'] ?? ''))>
                                                {{ $contexto['label'] ?? 'Sin contexto' }}
                                            </option>
                                        @empty
                                            <option value="">Sin contexto</option>
                                        @endforelse
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="floating-label-activo-sm mb-0">Buscador</label>
                                    <input
                                        type="text"
                                        name="q"
                                        value="{{ $search }}"
                                        class="form-control form-control-sm"
                                        placeholder="Buscar por responsable, RUT o nombre de mascota">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button class="btn btn-info btn-sm btn-block" type="submit">
                                        <i class="feather icon-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="table-responsive">
                                    <table id="" class="display table table-striped dt-responsive nowrap table-xs"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Mascota</th>
                                                <th>Responsable</th>
                                                <th>Especie</th>
                                                <th>Raza</th>
                                                <th>Convenio</th>
                                                <th>Chip-Tatuaje</th>
                                                <th>Centro</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($mascotas as $mascota)
                                                @php
                                                    $responsable = $mascota->Responsable;
                                                    $nombreResponsable = trim(collect([
                                                        optional($responsable)->nombres,
                                                        optional($responsable)->apellido_uno,
                                                        optional($responsable)->apellido_dos,
                                                    ])->filter()->implode(' '));
                                                    $rutResponsable = optional($responsable)->rut;
                                                    $responsableTexto = $nombreResponsable !== ''
                                                        ? $nombreResponsable . ($rutResponsable ? '<br>' . $rutResponsable : '')
                                                        : '-';
                                                    $especie = optional($mascota->especieMascota)->nombre ?? $mascota->especie ?? '-';
                                                    $raza = $mascota->otra_especie ?? '-';
                                                    $convenio = optional(optional($responsable)->Prevision)->nombre ?? '-';
                                                    $chip = $mascota->tiene_chip ? ($mascota->chip ?: 'Si') : 'No';
                                                    $imagenesMascota = mascota_imagenes_publicas($mascota);
                                                    $fotoRespaldoMascota = $mascota->sexo === 'F'
                                                        ? asset('images/iconos/paciente-f.svg')
                                                        : asset('images/iconos/paciente-m.svg');
                                                    $fotoMascota = $mascota->foto_url ?: ($imagenesMascota[0] ?? $fotoRespaldoMascota);
                                                    $sexoMascota = $mascota->sexo === 'M' ? 'Macho' : ($mascota->sexo === 'F' ? 'Hembra' : ($mascota->sexo ?? 'Sin registro'));
                                                    $tamanoMascota = optional($mascota->tamanoMascota)->nombre ?? $mascota->tamano ?? 'Sin registro';
                                                    $galeriaMascota = $imagenesMascota;
                                                    $centroActivo = $contextoActivo['label'] ?? 'Sin contexto';
                                                @endphp
                                                <tr>
                                                    <td>{{ $mascota->nombre ?? '-' }}</td>
                                                    <td>{!! $responsableTexto !!}</td>
                                                    <td>{{ $especie }}</td>
                                                    <td>{{ $raza }}</td>
                                                    <td>{{ $convenio }}</td>
                                                    <td>{{ $chip }}</td>
                                                    <td>{{ $centroActivo }}</td>
                                                    <td class="text-nowrap">
                                                        <button type="button" class="btn btn-info btn-xxs js-ver-mascota"
                                                            data-toggle="modal" data-target="#modalMascotaDetalle"
                                                            data-nombre="{{ $mascota->nombre ?? 'Mascota' }}"
                                                            data-especie="{{ $especie }}"
                                                            data-tamano="{{ $tamanoMascota }}"
                                                            data-sexo="{{ $sexoMascota }}"
                                                            data-fecha="{{ $mascota->fecha_nacimiento ?? 'Sin registro' }}"
                                                            data-chip="{{ $chip }}"
                                                            data-esterilizado="{{ $mascota->esterilizado ? 'Si' : 'No' }}"
                                                            data-esterilizacion="{{ $mascota->fecha_esterilizacion ?? 'Sin registro' }}"
                                                            data-enfermedad="{{ $mascota->enfermedad_cronica ?? 'Sin registro' }}"
                                                            data-foto="{{ $fotoMascota }}"
                                                            data-foto-respaldo="{{ $fotoRespaldoMascota }}"
                                                            data-galeria='@json($galeriaMascota)'>
                                                            <i class="feather icon-eye"></i> Ver mascota
                                                        </button>
                                                        <a href="{{ route('profesional.mascota.ficha_veterinaria', ['mascota' => $mascota->id]) }}"
                                                            class="btn btn-purple btn-xxs"><i class="feather icon-file-text"></i> Ver ficha veterinaria
                                                        </a>
                                                        <a href="{{ route('profesional.atenciones_previas_paciente', ['id' => $responsable->id, 'id_mascota' => $mascota->id]) }}"
                                                            class="btn btn-primary btn-xxs" title="Revisar consultas anteriores">
                                                            <i class="feather icon-clock"></i> Consultas anteriores
                                                        </a>
                                                        @if ($responsable)
                                                            <button type="button" class="btn btn-warning btn-xxs"
                                                                onclick="enviar_mensaje_paciente({{ $responsable->id }})" title="Enviar comunicación al responsable">
                                                                <i class="feather icon-message-circle"></i> Comunicación
                                                            </button>
                                                        @endif
                                                        <button type="button" class="btn btn-secondary btn-xxs js-resumen-contacto"
                                                            data-toggle="modal" data-target="#modalResumenContacto"
                                                            data-mascota="{{ $mascota->nombre ?? '-' }}"
                                                            data-responsable="{{ $nombreResponsable ?: '-' }}"
                                                            data-rut="{{ $rutResponsable ?: '-' }}"
                                                            data-email="{{ optional($responsable)->email ?: '-' }}"
                                                            data-telefono="{{ optional($responsable)->telefono_uno ?: optional($responsable)->telefono_dos ?: '-' }}"
                                                            data-centro="{{ $centroActivo }}">
                                                            <i class="feather icon-user-check"></i> Resumen y contacto
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">Sin registros</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Cierre: Tabla mis pacientes -->
    </div>
    <!--Cierre: Container Completo-->

    <div class="modal fade" id="modalResumenContacto" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="feather icon-user-check mr-2"></i>Resumen y contacto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="contact-summary-hero">
                        <div class="contact-summary-avatar"><i class="feather icon-user-check"></i></div>
                        <div>
                            <h5 id="resumen-responsable">Responsable</h5>
                            <p>Tutor y datos de contacto de <strong id="resumen-mascota">la mascota</strong></p>
                        </div>
                    </div>
                    <div class="contact-summary-grid">
                        <div class="contact-summary-item">
                            <span class="contact-summary-icon"><i class="feather icon-credit-card"></i></span>
                            <div><span class="contact-summary-label">RUT</span><span class="contact-summary-value" id="resumen-rut">-</span></div>
                        </div>
                        <div class="contact-summary-item">
                            <span class="contact-summary-icon"><i class="feather icon-phone"></i></span>
                            <div><span class="contact-summary-label">Teléfono</span><a class="contact-summary-value" id="resumen-telefono" href="#">-</a></div>
                        </div>
                        <div class="contact-summary-item contact-summary-item--wide">
                            <span class="contact-summary-icon"><i class="feather icon-mail"></i></span>
                            <div><span class="contact-summary-label">Correo electrónico</span><a class="contact-summary-value" id="resumen-email" href="#">-</a></div>
                        </div>
                        <div class="contact-summary-item contact-summary-item--wide">
                            <span class="contact-summary-icon"><i class="feather icon-map-pin"></i></span>
                            <div><span class="contact-summary-label">Centro de atención</span><span class="contact-summary-value" id="resumen-centro">-</span></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!--Modal envio de correo-->
    <div class="modal fade" id="modal_correo" tabindex="-1" role="dialog" aria-labelledby="enviar_email"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-center">
                    <h4 class="modal-title text-white w-100 font-weight-bold">Nuevo Correo</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body mx-3">
                    <div class="md-form mb-5">
                        <i class="fas fa-user prefix grey-text">
                            <label data-error="wrong" data-success="right" for="form34">
                                @if (isset($p))
                                    {{ $p->nombres . ' ' . $p->apellido_uno . ' ' . $p->apellido_dos }}
                                @endif
                            </label>
                        </i><br>
                        <i class="fas fa-envelope prefix grey-text">
                            <label data-error="wrong" data-success="right" for="form29">
                                @if (isset($p))
                                    {{ $p->email }}
                                @endif

                            </label>
                        </i><br>

                        <i class="fas fa-tag prefix grey-text">
                            <label data-error="wrong" data-success="right" for="form32">
                                Asunto
                            </label>
                        </i>
                        <input type="text" id="titulo_email" name="titulo_email" class="form-control validate"><br>

                        <i class="fas fa-pencil prefix grey-text">
                            <label data-error="wrong" data-success="right" for="form8">
                                Mensaje
                            </label>
                        </i>
                        <textarea type="text" id="mensaje_email" name="mensaje_email" class="md-textarea form-control" rows="4"></textarea>

                    </div>

                </div>
                <div class="modal-footer bg-info d-flex justify-content-center">
                    <button class="btn btn-unique bg-white"
                        @if (isset($p)) onclick="enviar_email({{ $p->id }});" @endif>Enviar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMascotaDetalle" tabindex="-1" role="dialog" aria-labelledby="modalMascotaDetalleLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white mt-1" id="modalMascotaDetalleLabel">Información de la mascota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="pet-detail-hero">
                        <img id="modal_mascota_img" class="pet-detail-photo"
                            src="{{ asset('images/iconos/paciente-m.svg') }}" alt="Foto de la mascota">
                        <div class="pet-detail-heading">
                            <h4 id="modal_mascota_nombre">Mascota</h4>
                            <p class="pet-detail-subtitle"><span id="modal_mascota_especie">-</span> · <span id="modal_mascota_sexo">-</span></p>
                            <span class="pet-detail-badge"><i class="fas fa-paw mr-1"></i> Ficha veterinaria</span>
                        </div>
                    </div>
                    <div class="pet-detail-grid">
                        <div class="pet-detail-item"><i class="fas fa-ruler-combined"></i><div><span class="pet-detail-label">Tamaño</span><span class="pet-detail-value" id="modal_mascota_tamano">-</span></div></div>
                        <div class="pet-detail-item"><i class="far fa-calendar-alt"></i><div><span class="pet-detail-label">Fecha de nacimiento</span><span class="pet-detail-value" id="modal_mascota_fecha">-</span></div></div>
                        <div class="pet-detail-item"><i class="fas fa-microchip"></i><div><span class="pet-detail-label">Chip o tatuaje</span><span class="pet-detail-value" id="modal_mascota_chip">-</span></div></div>
                        <div class="pet-detail-item"><i class="fas fa-notes-medical"></i><div><span class="pet-detail-label">Esterilización</span><span class="pet-detail-value"><span id="modal_mascota_esterilizado">-</span> · <span id="modal_mascota_esterilizacion">-</span></span></div></div>
                        <div class="pet-detail-item" style="grid-column:1/-1"><i class="fas fa-heartbeat"></i><div><span class="pet-detail-label">Enfermedad crónica</span><span class="pet-detail-value" id="modal_mascota_enfermedad">-</span></div></div>
                    </div>
                    <div class="pet-gallery" id="modal_mascota_galeria_wrapper" style="display:none;">
                        <div class="pet-gallery-title"><i class="far fa-images mr-1"></i> Galería de fotos</div>
                        <div class="pet-gallery-list" id="modal_mascota_galeria"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Presupuestos -->
    <div class="modal fade" id="modalPresupuestos" tabindex="-1" role="dialog" aria-labelledby="modalPresupuestosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Presupuestos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Profesional</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPresupuestos">
                            <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!--EMITIR DOCUMENTO-->
    <div class="modal fade" id="modal_emitir_doc" tabindex="-1" role="dialog" aria-labelledby="emitir_documento"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h4 class="modal-title text-white w-100 font-weight-bold">Emitir documentos</h4>
                    <button type="button" class="close" onclick="cerrar_cta_banco_m();" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-row">
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <label class="floating-label-activo">Seleccione documento</label>
                                <select class="form-control form-control-sm">
                                    <option>Seleccione una opción</option>
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <h5>DESPUES DE SELECCIONAR, ACÁ SE CARGA EL FORMULARIO DEL DOCUMENTO.<h5>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-info"><i class="feather icon-check"></i> Emitir</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    @include('app.profesional.modales.autorizacion_ficha_medica_unica')
    @include('app.profesional.modales.mensaje_paciente')
    @include('app.profesional.modales.mensaje_difusion_pacientes')
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('#pacientes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("profesional.mis_pacientes.ajax") }}',
        columns: [
            { data: 'nombre_completo', name: 'nombre_completo' },
            { data: 'fecha_nacimiento', name: 'fecha_nacimiento' },
            { data: 'convenio', name: 'convenio' },
            { data: 'contacto', name: 'contacto' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false },
            { data: 'mensaje', name: 'mensaje', orderable: false, searchable: false },
            { data: 'lugares_atencion', name: 'lugares_atencion', orderable: false, searchable: false },
        ]
    });
});
</script>

<script>

    function enviar_mensaje_paciente(id_paciente){
        $('#modalMensajePaciente').modal('show');
        $('#id_paciente_mensaje').val(id_paciente);
    }

    function enviar_difusion_pacientes(){
        $('#modalMensajeDifusionPacientes').modal('show');
    }

    $(document).on('click', '.js-resumen-contacto', function () {
        var $boton = $(this);
        $('#resumen-mascota').text($boton.data('mascota') || '-');
        $('#resumen-responsable').text($boton.data('responsable') || '-');
        $('#resumen-rut').text($boton.data('rut') || '-');
        var telefono = $boton.data('telefono') || '-';
        var email = $boton.data('email') || '-';
        $('#resumen-telefono').text(telefono).attr('href', telefono !== '-' ? 'tel:' + String(telefono).replace(/[^0-9+]/g, '') : '#');
        $('#resumen-email').text(email).attr('href', email !== '-' ? 'mailto:' + email : '#');
        $('#resumen-centro').text($boton.data('centro') || '-');
    });

    function emitir_doc(){
        $('#modal_emitir_doc').modal('show');
    }

    function verPresupuestos(idPaciente) {
        $('#modalPresupuestos').modal('show');
        $('#tbodyPresupuestos').html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');

        $.ajax({
            url: '{{ route("profesional.presupuestos.paciente") }}',
            method: 'GET',
            data: { id: idPaciente },
            success: function(response) {
                console.log(response);
                if (response.length > 0) {
                    let rows = '';
                    response.forEach((item, index) => {
                        item.valor_total = parseFloat(item.valor_total).toLocaleString('es-CL', {
                            style: 'currency',
                            currency: 'CLP'
                        });
                        if(item.estado == 1){
                            item.estado = 'Pendiente';
                        } else if(item.estado == 0){
                            item.estado = 'Aceptado';
                        } else {
                            item.estado = 'Desconocido';
                        }
                        rows += `<tr>
                            <td>${index + 1}</td>
                            <td>${item.fecha}</td>
                            <td>${item.profesional_nombre} ${item.profesional_apellido_uno} ${item.profesional_apellido_dos}</td>
                            <td>${item.valor_total}</td>
                            <td>${item.estado}</td>
                        </tr>`;
                    });
                    $('#tbodyPresupuestos').html(rows);
                } else {
                    $('#tbodyPresupuestos').html('<tr><td colspan="5" class="text-center">Sin presupuestos</td></tr>');
                }
            },
            error: function() {
                $('#tbodyPresupuestos').html('<tr><td colspan="5" class="text-danger text-center">Error al cargar</td></tr>');
            }
        });
    }

    $(document).on('click', '.js-ver-mascota', function () {
        var $btn = $(this);
        $('#modal_mascota_nombre').text($btn.data('nombre') || 'Mascota');
        $('#modal_mascota_especie').text($btn.data('especie') || '-');
        $('#modal_mascota_tamano').text($btn.data('tamano') || '-');
        $('#modal_mascota_sexo').text($btn.data('sexo') || '-');
        $('#modal_mascota_fecha').text($btn.data('fecha') || '-');
        $('#modal_mascota_chip').text($btn.data('chip') || '-');
        $('#modal_mascota_esterilizado').text($btn.data('esterilizado') || '-');
        $('#modal_mascota_esterilizacion').text($btn.data('esterilizacion') || '-');
        $('#modal_mascota_enfermedad').text($btn.data('enfermedad') || '-');

        var fotoRespaldo = $btn.data('foto-respaldo') || '{{ asset('images/iconos/paciente-m.svg') }}';
        $('#modal_mascota_img')
            .off('error.mascota')
            .on('error.mascota', function () {
                $(this).off('error.mascota').attr('src', fotoRespaldo);
            })
            .attr('src', $btn.data('foto') || fotoRespaldo);

        var galeria = $btn.data('galeria') || [];
        if (typeof galeria === 'string') {
            try {
                galeria = JSON.parse(galeria);
            } catch (e) {
                galeria = [];
            }
        }
        var $galeria = $('#modal_mascota_galeria');
        $galeria.empty();
        if (Array.isArray(galeria) && galeria.length > 0) {
            galeria.forEach(function (url) {
                if (!url) return;
                $galeria.append($('<img>', {
                    class: 'pet-gallery-thumb',
                    src: url,
                    alt: 'Foto de la mascota',
                    title: 'Usar como foto principal'
                }));
            });
            $('#modal_mascota_galeria_wrapper').show();
        } else {
            $('#modal_mascota_galeria_wrapper').hide();
        }
    });

    $(document).on('click', '.pet-gallery-thumb', function () {
        $('#modal_mascota_img').attr('src', this.src);
    });

</script>
@endsection
