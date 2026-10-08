@extends('template.profesional.template')
@section('page-styles')
<style>
    #modalMascotaDetalle .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 65px rgba(10,45,58,.3)}
    #modalMascotaDetalle .modal-header{padding:16px 22px;background:#6f42c1!important;border:0}
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
    .pet-detail-seccion{display:flex;align-items:center;margin:8px 22px 0;padding-top:16px;border-top:1px solid #e4eaef;color:#272727;font-family:'Nunito',sans-serif;font-size:1rem;font-weight:700}
    .pet-detail-seccion i{margin-right:8px;color:#6f42c1}
    .pet-detail-grid--tutor{padding-top:12px;padding-bottom:20px}
    .pet-detail-item--ancho{grid-column:1/-1}
    a.pet-detail-value:hover{color:#6f42c1;text-decoration:none}
    .acciones-mascota{display:flex;flex-wrap:nowrap;margin:-3px}
    .acciones-mascota .btn.btn-icon{margin:3px;flex:0 0 auto}
    @media(max-width:575.98px){.pet-detail-hero{align-items:flex-start;padding:18px}.pet-detail-photo{width:96px;height:96px;flex-basis:96px;border-radius:18px}.pet-detail-heading{margin-left:15px}.pet-detail-heading h4{font-size:1.25rem}.pet-detail-grid{grid-template-columns:1fr;padding:16px}}
    @media(max-width:575.98px){.pet-detail-seccion{margin:8px 16px 0}.pet-detail-item--ancho{grid-column:auto}}
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

            <div class="row">
                <div class="col-md-12 mb-3 encabezado-pagina">
                    <div>
                        <h5 class="f-26 d-block mb-0">Mascotas y tutores</h5>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-icon btn-purple js-tooltip-accion" onclick="enviar_difusion_pacientes()"
                            title="Enviar mensaje de difusión" aria-label="Enviar mensaje de difusión" data-placement="left">
                            <i class="feather icon-mail"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla mis clientes -->
            <!--Este formulario muestra los pacientes que alguna vez atendió el profesional (relacion: id_paciente/id_profesional)-->
            <div class="col-sm-12">
                <div class="card">
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
                                    <button class="btn btn-icon btn-info js-tooltip-accion" type="submit" title="Buscar" aria-label="Buscar">
                                        <i class="feather icon-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="table-responsive">
                                    <table id="" class="display table table-striped dt-responsive nowrap table-xs"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Mascota</th>
                                                <th>Tutor</th>
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
                                                    <td>
                                                        <div class="acciones-mascota">
                                                            <button type="button" class="btn btn-icon btn-info js-ver-mascota js-tooltip-accion"
                                                                data-toggle="modal" data-target="#modalMascotaDetalle"
                                                                title="Ver mascota y tutor" aria-label="Ver mascota y tutor"
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
                                                                data-galeria='@json($galeriaMascota)'
                                                                data-tutor="{{ $nombreResponsable ?: '-' }}"
                                                                data-tutor-rut="{{ $rutResponsable ?: '-' }}"
                                                                data-tutor-email="{{ optional($responsable)->email ?: '-' }}"
                                                                data-tutor-telefono="{{ optional($responsable)->telefono_uno ?: optional($responsable)->telefono_dos ?: '-' }}"
                                                                data-tutor-convenio="{{ $convenio }}">
                                                                <i class="fas fa-paw"></i>
                                                            </button>
                                                            <a href="{{ route('profesional.mascota.ficha_veterinaria', ['mascota' => $mascota->id]) }}"
                                                                class="btn btn-icon btn-purple js-tooltip-accion"
                                                                title="Ver ficha veterinaria" aria-label="Ver ficha veterinaria">
                                                                <i class="feather icon-file-text"></i>
                                                            </a>
                                                            @if ($responsable)
                                                                <a href="{{ route('profesional.atenciones_previas_paciente', ['id' => $responsable->id, 'id_mascota' => $mascota->id]) }}"
                                                                    class="btn btn-icon btn-primary js-tooltip-accion"
                                                                    title="Revisar consultas anteriores" aria-label="Revisar consultas anteriores">
                                                                    <i class="feather icon-clock"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-icon btn-warning js-tooltip-accion"
                                                                    onclick="enviar_mensaje_paciente({{ $responsable->id }})"
                                                                    title="Envíe un mensaje al tutor" aria-label="Envíe un mensaje al tutor">
                                                                    <i class="feather icon-mail"></i>
                                                                </button>
                                                            @endif
                                                        </div>
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

    <!--Modal envio de correo-->
    <div class="modal fade" id="modal_correo" tabindex="-1" role="dialog" aria-labelledby="enviar_email"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header text-center modal-header-purple">
                    <h4 class="modal-title w-100 font-weight-bold">Nuevo Correo</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>
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
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title mt-1" id="modalMascotaDetalleLabel">Información de la mascota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span
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
                        <div class="pet-detail-item pet-detail-item--ancho"><i class="fas fa-heartbeat"></i><div><span class="pet-detail-label">Enfermedad crónica</span><span class="pet-detail-value" id="modal_mascota_enfermedad">-</span></div></div>
                    </div>
                    <h6 class="pet-detail-seccion"><i class="fas fa-user"></i> Datos del tutor</h6>
                    <div class="pet-detail-grid pet-detail-grid--tutor">
                        <div class="pet-detail-item"><i class="fas fa-user"></i><div><span class="pet-detail-label">Nombre</span><span class="pet-detail-value" id="modal_tutor_nombre">-</span></div></div>
                        <div class="pet-detail-item"><i class="fas fa-id-card"></i><div><span class="pet-detail-label">RUT</span><span class="pet-detail-value" id="modal_tutor_rut">-</span></div></div>
                        <div class="pet-detail-item"><i class="fas fa-phone-alt"></i><div><span class="pet-detail-label">Teléfono</span><a class="pet-detail-value" id="modal_tutor_telefono" href="#">-</a></div></div>
                        <div class="pet-detail-item"><i class="fas fa-shield-alt"></i><div><span class="pet-detail-label">Convenio</span><span class="pet-detail-value" id="modal_tutor_convenio">-</span></div></div>
                        <div class="pet-detail-item pet-detail-item--ancho"><i class="fas fa-envelope"></i><div><span class="pet-detail-label">Correo electrónico</span><a class="pet-detail-value" id="modal_tutor_email" href="#">-</a></div></div>
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
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title">Historial de Presupuestos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');">
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
                <div class="modal-header modal-header-purple">
                    <h4 class="modal-title w-100 font-weight-bold">Emitir documentos</h4>
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

    // Tooltips de los botones de icono: en pantallas táctiles se muestran al tocar y se ocultan solos
    $(function () {
        var $botones = $('.js-tooltip-accion');
        $botones.tooltip({ container: 'body', trigger: 'hover focus', boundary: 'window' });
        $botones.on('touchstart', function () {
            var $boton = $(this);
            $botones.not($boton).tooltip('hide');
            $boton.tooltip('show');
            clearTimeout($boton.data('temporizadorTooltip'));
            $boton.data('temporizadorTooltip', setTimeout(function () { $boton.tooltip('hide'); }, 2500));
        });
        $botones.on('click', function () { $(this).tooltip('hide'); });
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

        // Datos del tutor
        var telefonoTutor = String($btn.data('tutor-telefono') || '-');
        var emailTutor = String($btn.data('tutor-email') || '-');
        $('#modal_tutor_nombre').text($btn.data('tutor') || '-');
        $('#modal_tutor_rut').text($btn.data('tutor-rut') || '-');
        $('#modal_tutor_convenio').text($btn.data('tutor-convenio') || '-');
        $('#modal_tutor_telefono').text(telefonoTutor).attr('href', telefonoTutor !== '-' ? 'tel:' + telefonoTutor.replace(/[^0-9+]/g, '') : '#');
        $('#modal_tutor_email').text(emailTutor).attr('href', emailTutor !== '-' ? 'mailto:' + emailTutor : '#');

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
