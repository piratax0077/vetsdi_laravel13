@extends('template.adm_cm.template')
@section('style')
<style>
    .acciones-mascota{display:flex;flex-wrap:nowrap;margin:-3px}
    .acciones-mascota .btn.btn-icon{margin:3px;flex:0 0 auto}
</style>
@endsection
@section('content')
    @php
        $contextosCentro = $contextosCentro ?? collect();
        $contextoActivo = $contextoActivo ?? [];
        $search = $search ?? '';
        $lugares_atencion = $lugares_atencion ?? collect();
        $centroActivo = $contextoActivo['label'] ?? ($institucion->nombre ?? 'Sin contexto');
    @endphp

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="row">
                <div class="col-md-12 mb-3 encabezado-pagina">
                    <div>
                        <h5 class="f-26 d-block mb-0">Mascotas y tutores</h5>
                        <small class="text-muted">{{ $centroActivo }}</small>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="btn btn-purple js-tooltip-accion" onclick="abrirMensajeDifusionResponsables()"
                            title="Enviar mensaje de difusión" aria-label="Enviar mensaje de difusión" data-placement="left">
                            <i class="feather icon-mail"></i> Enviar mensaje de difusión
                        </button>
                    </div>
                </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('adm_cm.pacientes') }}" class="mb-4">
                            <div class="form-row align-items-end">
                                <div class="col-md-4 mb-2">
                                    <label class="floating-label-activo-sm mb-0">Institución / Sucursal</label>
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
                                    <label class="floating-label-activo-sm mb-0">Buscar</label>
                                    <input
                                        type="text"
                                        name="q"
                                        value="{{ $search }}"
                                        class="form-control form-control-sm"
                                        placeholder="Buscar por tutor, RUT o nombre de mascota">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button class="btn btn-sm btn-block btn-info js-tooltip-accion" type="submit" title="Buscar" aria-label="Buscar">
                                        <i class="feather icon-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="table-responsive">
                                    <table class="display table table-striped dt-responsive nowrap table-xs"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Mascota</th>
                                                <th>Tutor</th>
                                                <th>Especie</th>
                                                <th>Raza</th>
                                                <th>Convenio</th>
                                                <th>Chip-Tatuaje</th>
                                                <th>Lugar</th>
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
                                                    $especie = optional($mascota->especieMascota)->nombre ?? $mascota->especie ?? '-';
                                                    $raza = optional($mascota->razaMascota)->nombre ?? $mascota->otra_especie ?? '-';
                                                    $convenio = optional(optional($responsable)->Prevision)->nombre ?? '-';
                                                    $chip = $mascota->tiene_chip ? ($mascota->chip ?: 'Si') : 'No';
                                                    $imagenesMascota = mascota_imagenes_publicas($mascota);
                                                    $fotoRespaldoMascota = $mascota->sexo === 'F'
                                                        ? asset('images/iconos/paciente-f.svg')
                                                        : asset('images/iconos/paciente-m.svg');
                                                    $fotoMascota = $mascota->foto_url ?: ($imagenesMascota[0] ?? $fotoRespaldoMascota);
                                                    $sexoMascota = $mascota->sexo === 'M' ? 'Macho' : ($mascota->sexo === 'F' ? 'Hembra' : ($mascota->sexo ?? 'Sin registro'));
                                                    $tamanoMascota = optional($mascota->tamanoMascota)->nombre ?? $mascota->tamano ?? 'Sin registro';

                                                    // si la mascota solo llegó por una ficha no tiene sucursal guardada, se deja el centro
                                                    $lugaresMascota = $mascota->lugaresAtencion
                                                        ->pluck('id_lugar_atencion')
                                                        ->map(fn ($id) => optional($lugares_atencion->get((int) $id))->nombre)
                                                        ->filter()
                                                        ->unique();
                                                    $lugarTexto = $lugaresMascota->isNotEmpty() ? $lugaresMascota->implode('<br>') : e($centroActivo);
                                                @endphp
                                                <tr>
                                                    <td>{{ $mascota->nombre ?? '-' }}</td>
                                                    <td>
                                                        @if ($nombreResponsable !== '')
                                                            {{ $nombreResponsable }}
                                                            @if ($rutResponsable)<br>{{ $rutResponsable }}@endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ $especie }}</td>
                                                    <td>{{ $raza }}</td>
                                                    <td>{{ $convenio }}</td>
                                                    <td>{{ $chip }}</td>
                                                    <td>{!! $lugarTexto !!}</td>
                                                    <td>
                                                        <div class="acciones-mascota">
                                                            <button type="button" class="btn btn-icon btn-info js-ver-mascota js-tooltip-accion"
                                                                data-toggle="modal" data-target="#modalMascotaDetalle"
                                                                title="Ver mascota y tutor" aria-label="Ver mascota y tutor"
                                                                data-nombre="{{ $mascota->nombre ?? 'Mascota' }}"
                                                                data-especie="{{ $especie }}"
                                                                data-raza="{{ $raza }}"
                                                                data-tamano="{{ $tamanoMascota }}"
                                                                data-sexo="{{ $sexoMascota }}"
                                                                data-fecha="{{ $mascota->fecha_nacimiento ?? 'Sin registro' }}"
                                                                data-chip="{{ $chip }}"
                                                                data-esterilizado="{{ $mascota->esterilizado ? 'Si' : 'No' }}"
                                                                data-esterilizacion="{{ $mascota->fecha_esterilizacion ?? 'Sin registro' }}"
                                                                data-enfermedad="{{ $mascota->enfermedad_cronica ?? 'Sin registro' }}"
                                                                data-foto="{{ $fotoMascota }}"
                                                                data-foto-respaldo="{{ $fotoRespaldoMascota }}"
                                                                data-galeria='@json($imagenesMascota)'
                                                                data-tutor="{{ $nombreResponsable ?: '-' }}"
                                                                data-tutor-rut="{{ $rutResponsable ?: '-' }}"
                                                                data-tutor-email="{{ optional($responsable)->email ?: '-' }}"
                                                                data-tutor-telefono="{{ optional($responsable)->telefono_uno ?: optional($responsable)->telefono_dos ?: '-' }}"
                                                                data-tutor-convenio="{{ $convenio }}"
                                                                data-tutor-url="{{ $responsable ? route('adm_cm.tutor.informacion', ['tutor' => $responsable->id]) : '' }}">
                                                                <i class="fas fa-paw"></i>
                                                            </button>
                                                            @if ($responsable)
                                                                <a href="{{ route('adm_cm.tutor.informacion', ['tutor' => $responsable->id]) }}"
                                                                    class="btn btn-icon btn-warning js-tooltip-accion"
                                                                    title="Ver información del tutor" aria-label="Ver información del tutor">
                                                                    <i class="feather icon-user"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-icon btn-purple js-tooltip-accion"
                                                                    onclick="abrirMensajeTutor({{ $responsable->id }}, '{{ e(addslashes($nombreResponsable ?: 'Tutor')) }}')"
                                                                    title="Envíe un mensaje al tutor" aria-label="Envíe un mensaje al tutor">
                                                                    <i class="feather icon-mail"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No hay mascotas ni tutores para el centro seleccionado.</td>
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
        </div>
    </div>

    <div class="modal fade ficha-profesional" id="modalMascotaDetalle" tabindex="-1" role="dialog" aria-labelledby="modalMascotaDetalleLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="ficha-profesional__cabecera">
                    <button type="button" class="ficha-profesional__cerrar" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');">
                        <i class="feather icon-x" aria-hidden="true"></i>
                    </button>
                    <img id="modal_mascota_img" class="ficha-profesional__foto"
                        src="{{ asset('images/iconos/paciente-m.svg') }}" alt="Foto de la mascota">
                    <div class="ficha-profesional__identidad">
                        <h5 class="ficha-profesional__nombre" id="modal_mascota_nombre">Mascota</h5>
                        <span class="ficha-profesional__registro"><span id="modal_mascota_especie">-</span> · <span id="modal_mascota_sexo">-</span></span>
                        <span class="ficha-profesional__especialidad"><i class="fas fa-paw mr-1" aria-hidden="true"></i> Ficha veterinaria</span>
                    </div>
                </div>

                <div class="modal-body ficha-profesional__cuerpo">
                    <section class="ficha-profesional__seccion">
                        <h6 class="ficha-profesional__titulo" id="modalMascotaDetalleLabel"><i class="fas fa-paw mr-1" aria-hidden="true"></i> Información de la mascota</h6>
                        <div class="form-row mb-n2">
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-dog" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Raza</span>
                                        <span class="ficha-profesional__valor" id="modal_mascota_raza">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-ruler-combined" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Tamaño</span>
                                        <span class="ficha-profesional__valor" id="modal_mascota_tamano">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="far fa-calendar-alt" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Fecha de nacimiento</span>
                                        <span class="ficha-profesional__valor" id="modal_mascota_fecha">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-microchip" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Chip o tatuaje</span>
                                        <span class="ficha-profesional__valor" id="modal_mascota_chip">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-notes-medical" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Esterilización</span>
                                        <span class="ficha-profesional__valor"><span id="modal_mascota_esterilizado">-</span> · <span id="modal_mascota_esterilizacion">-</span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-heartbeat" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Enfermedad crónica</span>
                                        <span class="ficha-profesional__valor" id="modal_mascota_enfermedad">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="ficha-profesional__seccion">
                        <div class="d-flex flex-wrap justify-content-between align-items-baseline">
                            <h6 class="ficha-profesional__titulo"><i class="fas fa-user mr-1" aria-hidden="true"></i> Datos del tutor</h6>
                            <a id="modal_tutor_url" href="#" class="text-purple f-12 mb-2 d-none">
                                <i class="feather icon-info" aria-hidden="true"></i> Ver más información
                            </a>
                        </div>
                        <div class="form-row mb-n2">
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-user" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Nombre</span>
                                        <span class="ficha-profesional__valor" id="modal_tutor_nombre">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-id-card" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">RUT</span>
                                        <span class="ficha-profesional__valor" id="modal_tutor_rut">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-phone-alt" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Teléfono</span>
                                        <span class="ficha-profesional__valor"><a id="modal_tutor_telefono" href="#">-</a></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-4 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-shield-alt" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Convenio</span>
                                        <span class="ficha-profesional__valor" id="modal_tutor_convenio">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-8 mb-2">
                                <div class="ficha-profesional__dato py-2 h-100">
                                    <span class="ficha-profesional__icono"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                    <div>
                                        <span class="ficha-profesional__etiqueta">Correo electrónico</span>
                                        <span class="ficha-profesional__valor"><a id="modal_tutor_email" href="#">-</a></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="ficha-profesional__seccion" id="modal_mascota_galeria_wrapper" style="display:none;">
                        <h6 class="ficha-profesional__titulo"><i class="far fa-images mr-1" aria-hidden="true"></i> Galería de fotos</h6>
                        <div class="d-flex flex-wrap mb-n2" id="modal_mascota_galeria"></div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMensajeTutor" tabindex="-1" role="dialog" aria-labelledby="modalMensajeTutorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="modalMensajeTutorLabel">Mensaje al tutor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="mensaje_tutor_id">
                    <p class="mb-3"><i class="fas fa-user mr-1"></i> <span id="mensaje_tutor_nombre">-</span></p>
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Asunto</label>
                        <input type="text" class="form-control form-control-sm" id="mensaje_tutor_asunto">
                    </div>
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Mensaje</label>
                        <textarea class="form-control form-control-sm" id="mensaje_tutor_texto" rows="4"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-info" onclick="enviarMensajeTutor()">
                            <i class="feather icon-mail"></i> Enviar mensaje
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMensajeDifusionResponsables" tabindex="-1" role="dialog" aria-labelledby="modalMensajeDifusionResponsablesLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="modalMensajeDifusionResponsablesLabel">Mensaje de difusión a tutores</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-muted f-12">Se enviará a todos los tutores de {{ $centroActivo }}.</p>
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Asunto</label>
                        <input type="text" class="form-control form-control-sm" id="difusion_asunto">
                    </div>
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Mensaje</label>
                        <textarea class="form-control form-control-sm" id="difusion_mensaje" rows="4"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-info" onclick="enviarMensajeDifusionResponsables()">
                            <i class="feather icon-mail"></i> Enviar mensaje
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
    function mostrarError(texto) {
        swal({ title: 'Error', text: texto, icon: 'error', button: 'Aceptar' });
    }

    function enviarMensaje(url, datos, $modal, campos) {
        $.ajax({
            url: url,
            type: 'POST',
            data: $.extend({ _token: "{{ csrf_token() }}" }, datos),
            success: function (response) {
                if (response.estado == 1) {
                    swal({ title: 'Mensaje enviado', text: 'El mensaje ha sido enviado correctamente', icon: 'success', button: 'Aceptar' });
                    $modal.modal('hide');
                    $(campos).val('');
                } else {
                    mostrarError(response.mensaje || 'Ocurrió un error al enviar el mensaje');
                }
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                mostrarError(response.mensaje || 'Ocurrió un error al enviar el mensaje');
            }
        });
    }

    function abrirMensajeDifusionResponsables() {
        $('#modalMensajeDifusionResponsables').modal('show');
    }

    function enviarMensajeDifusionResponsables() {
        var asunto = $.trim($('#difusion_asunto').val());
        var mensaje = $.trim($('#difusion_mensaje').val());
        if (asunto === '') return mostrarError('Ingrese el asunto');
        if (mensaje === '') return mostrarError('Ingrese el mensaje');

        enviarMensaje("{{ route('adm_cm.pacientes.difusion') }}", { asunto: asunto, mensaje: mensaje },
            $('#modalMensajeDifusionResponsables'), '#difusion_asunto, #difusion_mensaje');
    }

    function abrirMensajeTutor(idTutor, nombreTutor) {
        $('#mensaje_tutor_id').val(idTutor);
        $('#mensaje_tutor_nombre').text(nombreTutor || 'Tutor');
        $('#modalMensajeTutor').modal('show');
    }

    function enviarMensajeTutor() {
        var asunto = $.trim($('#mensaje_tutor_asunto').val());
        var mensaje = $.trim($('#mensaje_tutor_texto').val());
        if (asunto === '') return mostrarError('Ingrese el asunto');
        if (mensaje === '') return mostrarError('Ingrese el mensaje');

        enviarMensaje("{{ route('adm_cm.pacientes.mensaje') }}",
            { id_responsable: $('#mensaje_tutor_id').val(), asunto: asunto, mensaje: mensaje },
            $('#modalMensajeTutor'), '#mensaje_tutor_asunto, #mensaje_tutor_texto');
    }

    $(function () {
        // en esta plantilla jQuery UI pisa el tooltip de Bootstrap; sin Bootstrap queda el title normal
        if (!$.fn.tooltip || !$.fn.tooltip.Constructor) return;
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

    $(document).on('click', '.js-ver-mascota', function () {
        var $btn = $(this);
        $('#modal_mascota_nombre').text($btn.data('nombre') || 'Mascota');
        $('#modal_mascota_especie').text($btn.data('especie') || '-');
        $('#modal_mascota_raza').text($btn.data('raza') || '-');
        $('#modal_mascota_tamano').text($btn.data('tamano') || '-');
        $('#modal_mascota_sexo').text($btn.data('sexo') || '-');
        $('#modal_mascota_fecha').text($btn.data('fecha') || '-');
        $('#modal_mascota_chip').text($btn.data('chip') || '-');
        $('#modal_mascota_esterilizado').text($btn.data('esterilizado') || '-');
        $('#modal_mascota_esterilizacion').text($btn.data('esterilizacion') || '-');
        $('#modal_mascota_enfermedad').text($btn.data('enfermedad') || '-');

        var telefonoTutor = String($btn.data('tutor-telefono') || '-');
        var emailTutor = String($btn.data('tutor-email') || '-');
        $('#modal_tutor_nombre').text($btn.data('tutor') || '-');
        $('#modal_tutor_rut').text($btn.data('tutor-rut') || '-');
        $('#modal_tutor_convenio').text($btn.data('tutor-convenio') || '-');
        $('#modal_tutor_telefono').text(telefonoTutor).attr('href', telefonoTutor !== '-' ? 'tel:' + telefonoTutor.replace(/[^0-9+]/g, '') : '#');
        $('#modal_tutor_email').text(emailTutor).attr('href', emailTutor !== '-' ? 'mailto:' + emailTutor : '#');
        var urlTutor = $btn.data('tutor-url') || '';
        $('#modal_tutor_url').attr('href', urlTutor || '#').toggleClass('d-none', urlTutor === '');

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
                $galeria.append(
                    $('<button>', {
                        type: 'button',
                        class: 'traspaso-mascota-foto p-0 mr-2 mb-2 js-foto-galeria',
                        title: 'Usar como foto principal',
                        'aria-label': 'Usar como foto principal'
                    }).append($('<img>', { src: url, alt: 'Foto de la mascota' }))
                );
            });
            $('#modal_mascota_galeria_wrapper').show();
        } else {
            $('#modal_mascota_galeria_wrapper').hide();
        }
    });

    $(document).on('click', '.js-foto-galeria', function () {
        $('#modal_mascota_img').attr('src', $(this).find('img').attr('src'));
    });
</script>
@endsection
