@extends('template.profesional.template')
@section('content')
    @php
        $nombreTutor = trim(collect([$tutor->nombres, $tutor->apellido_uno, $tutor->apellido_dos])->filter()->implode(' ')) ?: 'Tutor sin nombre';
        $telefonoTutor = $tutor->telefono_uno ?: $tutor->telefono_dos;
        $direccion = $tutor->Direccion;
        $direccionTexto = trim(collect([
            trim((optional($direccion)->direccion ?? '') . ' ' . (optional($direccion)->numero_dir ?? '')),
            optional(optional($direccion)->Ciudad)->nombre,
        ])->filter()->implode(', '));
        $convenio = optional($tutor->Prevision)->nombre;
        $fechaAutorizacion = $tutor->fecha_autorizacion ? \Carbon\Carbon::parse($tutor->fecha_autorizacion)->format('d-m-Y') : null;
        $autorizaciones = [
            'Autorizó compartir su ficha veterinaria única' => $tutor->auto_fmu,
            'Autorizó informar a los profesionales de turno' => $tutor->auto_inf_turno,
            'Autorizó el acceso a su información confidencial' => $tutor->auto_inf_confd,
        ];
        $pesos = fn ($monto) => '$' . number_format((float) $monto, 0, ',', '.');
        // Las tablas muestran solo lo más reciente; los totales consideran todo
        $maximoFilas = 15;
        $totalFirmados = $documentosFirmados->count() + $consentimientos->count();
        $consentimientosVisibles = $consentimientos->take(max(0, $maximoFilas - $documentosFirmados->count()));
        $pestanasTutor = [
            'contacto' => 'Contacto',
            'consentimientos' => 'Consentimientos',
            'mascotas' => 'Mascotas',
            'firmados' => 'Lo que firmó',
            'compras' => 'Transacciones',
            'notas' => 'Notas internas',
        ];
    @endphp

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <!--Perfil del tutor: nombre, datos principales y acciones-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card tarjeta-datos-tutor">
                        <div class="card-body">
                            <div class="tarjeta-datos-tutor__cuerpo">
                                <h5 class="tarjeta-datos-tutor__nombre">
                                    {{ $nombreTutor }}
                                    <span @class(["badge badge-light-danger f-12 align-middle ml-1", "d-none" => !$bloqueoTutor]) id="etiqueta_tutor_bloqueado"
                                        title="{{ $bloqueoTutor ? "Motivo: " . $bloqueoTutor->motivo : "" }}">
                                        <i class="feather icon-lock mr-1" aria-hidden="true"></i>Bloqueado
                                    </span>
                                </h5>
                                <ul class="tarjeta-datos-tutor__datos">
                                    <li>
                                        <i class="feather icon-credit-card" aria-hidden="true"></i>
                                        <span class="tarjeta-datos-tutor__etiqueta">RUT</span>
                                        @if ($tutor->rut)
                                            {{ strtoupper($tutor->rut) }}
                                        @else
                                            <span class="text-danger">Sin registro</span>
                                        @endif
                                    </li>
                                    <li>
                                        <i class="feather icon-phone" aria-hidden="true"></i>
                                        <span class="tarjeta-datos-tutor__etiqueta">Teléfono</span>
                                        @if ($telefonoTutor)
                                            <a href="tel:{{ preg_replace("/[^0-9+]/", "", $telefonoTutor) }}">{{ $telefonoTutor }}</a>
                                        @else
                                            Sin registro
                                        @endif
                                    </li>
                                    <li>
                                        <i class="feather icon-map-pin" aria-hidden="true"></i>
                                        <span class="tarjeta-datos-tutor__etiqueta">Dirección</span>
                                        {{ $direccionTexto ?: "Sin registro" }}
                                    </li>
                                </ul>
                            </div>
                            <div class="tarjeta-datos-tutor__acciones d-print-none">
                                <button type="button" class="btn btn-sm btn-warning mb-1" onclick="enviar_mensaje_paciente({{ $tutor->id }})">
                                    <i class="feather icon-mail mr-1"></i>Mensaje
                                </button>
                                <button type="button" class="btn btn-sm btn-purple mb-1" onclick="window.print()">
                                    <i class="feather icon-printer mr-1"></i>Imprimir
                                </button>
                                <button type="button" @class(['btn btn-sm btn-danger mb-1', 'd-none' => $bloqueoTutor]) id="boton_abrir_bloquear_tutor"
                                    onclick="abrir_bloquear_tutor(false)">
                                    <i class="feather icon-lock mr-1"></i>Bloquear tutor
                                </button>
                                <button type="button" @class(['btn btn-sm btn-danger mb-1', 'd-none' => !$bloqueoTutor]) id="boton_desbloquear_tutor"
                                    onclick="desbloquear_tutor()">
                                    <i class="feather icon-unlock mr-1"></i>Desbloquear tutor
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <!--Una card por pestaña; al imprimir salen todas-->
                    <ul class="nav pill-moderno pill-moderno-white pill-sin-subrayado pill-con-flechas d-print-none" id="tutor-secciones" role="tablist">
                        @foreach ($pestanasTutor as $clave => $nombrePestana)
                            <li class="nav-item">
                                <a @class(['nav-link', 'active' => $loop->first]) id="tutor-{{ $clave }}-tab" data-toggle="tab"
                                    href="#tutor-{{ $clave }}" role="tab" aria-controls="tutor-{{ $clave }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $nombrePestana }}</a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        <!--Contacto-->
                        <div class="tab-pane d-print-block active" id="tutor-contacto" role="tabpanel" aria-labelledby="tutor-contacto-tab">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 text-dark">Contacto</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-row mb-n2">
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Correo</span>
                                                    <span class="ficha-profesional__valor font-weight-normal">
                                                        @if ($tutor->email)
                                                            <a href="mailto:{{ $tutor->email }}">{{ $tutor->email }}</a>
                                                        @else
                                                            Sin registro
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="fas fa-phone-alt" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Teléfono</span>
                                                    <span class="ficha-profesional__valor font-weight-normal">
                                                        @if ($telefonoTutor)
                                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefonoTutor) }}">{{ $telefonoTutor }}</a>
                                                        @else
                                                            Sin registro
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="fas fa-shield-alt" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Convenio</span>
                                                    <span class="ficha-profesional__valor font-weight-normal">{{ $convenio ?: 'Sin registro' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-8 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="fas fa-home" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Dirección</span>
                                                    <span class="ficha-profesional__valor font-weight-normal">{{ $direccionTexto ?: 'Sin registro' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="far fa-calendar-alt" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Atenciones contigo</span>
                                                    <span class="ficha-profesional__valor font-weight-normal">
                                                        {{ $totalAtenciones }}
                                                        @if ($ultimaAtencion)
                                                            · última el {{ \Carbon\Carbon::parse($ultimaAtencion)->format('d-m-Y') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--Autorizaciones que el tutor dio desde su perfil-->
                        <div class="tab-pane d-print-block" id="tutor-consentimientos" role="tabpanel" aria-labelledby="tutor-consentimientos-tab">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 text-dark">Consentimientos</h5>
                                    <p class="text-muted font-weight-normal mb-0 mt-1">Lo que esta persona aceptó desde su perfil, y cuándo.</p>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($autorizaciones as $textoAutorizacion => $aceptada)
                                            <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center px-0 py-2">
                                                <span>{{ $textoAutorizacion }}</span>
                                                @if ($aceptada)
                                                    <span class="text-purple font-weight-bold">Sí{{ $fechaAutorizacion ? ' · ' . $fechaAutorizacion : '' }}</span>
                                                @else
                                                    <span class="text-muted">No</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!--Mascotas del tutor que el profesional ha atendido-->
                        <div class="tab-pane d-print-block" id="tutor-mascotas" role="tabpanel" aria-labelledby="tutor-mascotas-tab">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 text-dark">Mascotas</h5>
                                </div>
                                <div class="card-body">
                                    @if ($mascotas->isEmpty())
                                        <div class="ficha-profesional__vacio">Todavía no has atendido mascotas de este tutor.</div>
                                    @else
                                        <div class="form-row mb-n2">
                                            @foreach ($mascotas as $mascota)
                                                @php
                                                    $fotoRespaldoMascota = $mascota->sexo === 'F'
                                                        ? asset('images/iconos/paciente-f.svg')
                                                        : asset('images/iconos/paciente-m.svg');
                                                    $fotoMascota = $mascota->foto_url ?: asset('images/iconos/mascotas.svg');
                                                    $especie = optional($mascota->especieMascota)->nombre ?? $mascota->especie ?? 'Sin especie';
                                                    $raza = optional($mascota->razaMascota)->nombre ?? $mascota->otra_especie;
                                                @endphp
                                                <div class="col-md-6 mb-2">
                                                    <div class="ficha-profesional__dato py-2 h-100">
                                                        <span @class(['traspaso-mascota-foto foto-mascota-tutor', 'foto-mascota-tutor--sin-foto' => !$mascota->foto_url])>
                                                            <img src="{{ $fotoMascota }}" alt="Foto de {{ $mascota->nombre }}"
                                                                onerror="this.onerror=null;this.src='{{ $fotoRespaldoMascota }}';this.parentNode.classList.add('foto-mascota-tutor--sin-foto');">
                                                        </span>
                                                        <div class="flex-grow-1">
                                                            <span class="ficha-profesional__valor">{{ $mascota->nombre ?? 'Mascota' }}</span>
                                                            <span class="ficha-profesional__etiqueta">Especie: {{ $especie }} · Sexo: {{ in_array($mascota->sexo, ['F', 'M'], true) ? $mascota->sexo : '-' }}</span>
                                                        </div>
                                                        @if ($mascota->fallecida)
                                                            <span class="badge badge-light-secondary f-12 px-2 py-1 mr-2 flex-shrink-0">
                                                                <i class="fas fa-cross mr-1" aria-hidden="true"></i>Fallecida
                                                            </span>
                                                        @endif
                                                        <div class="d-flex flex-shrink-0 d-print-none">
                                                            <a href="{{ route('profesional.mascota.ficha_veterinaria', ['mascota' => $mascota->id]) }}"
                                                                target="_blank" rel="noopener"
                                                                class="btn btn-icon btn-purple js-tooltip-accion mr-1"
                                                                title="FVU" aria-label="FVU">
                                                                <i class="feather icon-file-text"></i>
                                                            </a>
                                                            <a href="{{ route('profesional.atenciones_previas_paciente', ['id' => $tutor->id, 'id_mascota' => $mascota->id]) }}"
                                                                target="_blank" rel="noopener"
                                                                class="btn btn-icon btn-primary js-tooltip-accion"
                                                                title="Historial de atenciones" aria-label="Historial de atenciones">
                                                                <i class="feather icon-clock"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!--Consentimientos y documentos firmados-->
                        <div class="tab-pane d-print-block" id="tutor-firmados" role="tabpanel" aria-labelledby="tutor-firmados-tab">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 text-dark">Lo que firmó</h5>
                                    <p class="text-muted font-weight-normal mb-0 mt-1">Consentimientos y autorizaciones a su nombre. Se emiten desde la ficha de cada mascota.</p>
                                </div>
                                <div class="card-body">
                                    @if ($consentimientos->isEmpty() && $documentosFirmados->isEmpty())
                                        <div class="ficha-profesional__vacio">Todavía no ha firmado ninguno.</div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-striped table-xs mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Documento</th>
                                                        <th>Fecha</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($documentosFirmados->take($maximoFilas) as $documento)
                                                        <tr>
                                                            <td>
                                                                @if ($documento->firmado && $documento->url)
                                                                    <a href="{{ $documento->url }}" target="_blank" rel="noopener" class="text-purple">{{ $documento->nombre }}</a>
                                                                @else
                                                                    {{ $documento->nombre }}
                                                                @endif
                                                            </td>
                                                            <td>{{ $documento->fecha_firma ?: optional($documento->fecha)->format('d-m-Y') }}</td>
                                                            <td>
                                                                @if ($documento->firmado)
                                                                    <span class="badge badge-light-success">Firmado</span>
                                                                @else
                                                                    <span class="badge badge-light-warning">Pendiente de firma</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    @foreach ($consentimientosVisibles as $consentimiento)
                                                        <tr>
                                                            <td>
                                                                {{ optional($consentimiento->Consentimiento)->nombre ?? 'Consentimiento' }}
                                                                @if ($consentimiento->cirugia_cons)
                                                                    <br><small class="text-muted">{{ $consentimiento->cirugia_cons }}</small>
                                                                @endif
                                                            </td>
                                                            <td>{{ $consentimiento->fecha_cons ? \Carbon\Carbon::parse($consentimiento->fecha_cons)->format('d-m-Y') : optional($consentimiento->created_at)->format('d-m-Y') }}</td>
                                                            <td>
                                                                @if ($consentimiento->revocacion)
                                                                    <span class="badge badge-light-danger">Revocado</span>
                                                                @elseif ($consentimiento->confirmacion)
                                                                    <span class="badge badge-light-success">Firmado</span>
                                                                @else
                                                                    <span class="badge badge-light-warning">Pendiente de firma</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if ($totalFirmados > $maximoFilas)
                                            <p class="text-muted f-12 mt-2 mb-0">Se muestran los {{ $maximoFilas }} más recientes de {{ $totalFirmados }}.</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!--Cobros registrados a nombre del tutor-->
                        <div class="tab-pane d-print-block" id="tutor-compras" role="tabpanel" aria-labelledby="tutor-compras-tab">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0 text-dark">Transacciones</h5>
                                    <p class="text-muted font-weight-normal mb-0 mt-1">Lo que se le ha cobrado en tus atenciones. Un cobro devuelto queda tachado y no se esconde.</p>
                                </div>
                                <div class="card-body">
                                    @if ($cobros->isEmpty())
                                        <div class="ficha-profesional__vacio">Todavía no se le ha cobrado nada.</div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-striped table-xs mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Detalle</th>
                                                        <th>N° documento</th>
                                                        <th class="text-right">Valor</th>
                                                        <th class="text-right">Pagó</th>
                                                        <th>Medio de pago</th>
                                                        <th>Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($cobros->take($maximoFilas) as $cobro)
                                                        <tr @class(['text-muted' => $cobro->devuelto])>
                                                            <td>{{ $cobro->fecha_atencion ? \Carbon\Carbon::parse($cobro->fecha_atencion)->format('d-m-Y') : '-' }}</td>
                                                            <td>
                                                                {{ optional($cobro->TipoBono)->nombre ?? 'Atención' }}
                                                            </td>
                                                            <td>{{ $cobro->numero_bono ?: '-' }}</td>
                                                            @if ($cobro->devuelto)
                                                                <td class="text-right"><del>{{ $pesos($cobro->valor_atencion) }}</del></td>
                                                                <td class="text-right"><del>{{ $pesos($cobro->a_pagar) }}</del></td>
                                                            @else
                                                                <td class="text-right">{{ $pesos($cobro->valor_atencion) }}</td>
                                                                <td class="text-right">{{ $pesos($cobro->a_pagar) }}</td>
                                                            @endif
                                                            {{-- La recepción del pago todavía no guarda el medio de pago --}}
                                                            <td class="text-muted">Sin registro</td>
                                                            <td>
                                                                @if ($cobro->devuelto)
                                                                    <span class="badge badge-light-danger">Devuelto</span>
                                                                @elseif ($cobro->pago_confirmado_en)
                                                                    <span class="badge badge-light-success">Pagado</span>
                                                                @else
                                                                    <span class="badge badge-light-warning">Pendiente de pago</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if ($cobros->count() > $maximoFilas)
                                            <p class="text-muted f-12 mt-2 mb-0">Se muestran los {{ $maximoFilas }} más recientes de {{ $cobros->count() }}.</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!--Notas internas: solo las ve el profesional-->
                        <div class="tab-pane d-print-block" id="tutor-notas" role="tabpanel" aria-labelledby="tutor-notas-tab">
                            <div class="card">
                                @include('app.profesional.notas_tutor')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('app.profesional.modales.mensaje_paciente')
    @include('app.profesional.modales.bloquear_tutor')
@endsection

@section('page-script')
<script src="{{ asset('js/pestanas_desplazables.js') }}?v=20261113"></script>
<script>
    function enviar_mensaje_paciente(id_paciente){
        $('#modalMensajePaciente').modal('show');
        $('#id_paciente_mensaje').val(id_paciente);
    }

    // Notas internas y bloqueo del tutor
    var tutor_bloqueado = {{ $bloqueoTutor ? 'true' : 'false' }};
    var notas_conflictivas_para_bloquear = 3;

    function texto_veces_conflictivo(veces) {
        return veces === 1 ? 'vez conflictivo' : 'veces conflictivo';
    }

    function mensaje_error_ajax(xhr, por_defecto) {
        var datos = xhr && xhr.responseJSON ? xhr.responseJSON : {};
        if (datos.errors) {
            var primero = Object.keys(datos.errors)[0];
            return datos.errors[primero][0];
        }
        return datos.msj || por_defecto;
    }

    // con_aviso = true cuando se abre solo por las notas conflictivas
    function abrir_bloquear_tutor(con_aviso) {
        $('#aviso_tres_conflictivas').toggleClass('d-none', !con_aviso);
        $('#bloquear_tutor_motivo').val('').removeClass('is-invalid');
        $('#modal_bloquear_tutor').modal('show');
    }

    function marcar_tutor_bloqueado(bloqueado) {
        tutor_bloqueado = bloqueado;
        $('#etiqueta_tutor_bloqueado').toggleClass('d-none', !bloqueado);
        $('#boton_abrir_bloquear_tutor').toggleClass('d-none', bloqueado);
        $('#boton_desbloquear_tutor').toggleClass('d-none', !bloqueado);
    }

    $('#nota_tutor_conflictivo').on('change', function () {
        $('label[for="nota_tutor_conflictivo"]').text(this.checked ? 'Sí' : 'No');
    });

    $('#form_nota_tutor').on('submit', function (evento) {
        evento.preventDefault();
        var formulario = this;
        var $nota = $('#nota_tutor_texto');
        $nota.val($.trim($nota.val()));
        if (!formulario.checkValidity()) {
            $(formulario).addClass('was-validated');
            return;
        }

        var $boton = $('#boton_agregar_nota_tutor').prop('disabled', true);
        $.ajax({
            url: "{{ route('profesional.tutor.notas.guardar', ['tutor' => $tutor->id]) }}",
            type: 'POST',
            data: {
                nota: $nota.val(),
                conflictivo: $('#nota_tutor_conflictivo').is(':checked') ? 1 : 0,
                _token: "{{ csrf_token() }}"
            }
        }).done(function (respuesta) {
            var nota = respuesta.nota;
            var $fila = $('<tr></tr>').append(
                $('<td class="text-nowrap"></td>').text(nota.fecha),
                $('<td style="white-space: pre-line;"></td>').text(nota.texto),
                $('<td></td>').append(nota.conflictivo
                    ? '<span class="badge badge-light-danger">Sí</span>'
                    : '<span class="badge badge-light-secondary">No</span>')
            );
            $('#lista_notas_tutor').prepend($fila);
            $('#notas_tutor_vacio').addClass('d-none');
            $('#tabla_notas_tutor').removeClass('d-none');

            var veces = parseInt(respuesta.veces_conflictivo, 10) || 0;
            $('.js-veces-conflictivo').text(veces);
            $('.js-veces-conflictivo-texto').text(texto_veces_conflictivo(veces));

            formulario.reset();
            $(formulario).removeClass('was-validated');
            $('label[for="nota_tutor_conflictivo"]').text('No');
            marcar_tutor_bloqueado(!!respuesta.bloqueado);

            // Desde la tercera nota conflictiva se propone bloquearlo
            if (nota.conflictivo && veces >= notas_conflictivas_para_bloquear && !tutor_bloqueado) {
                abrir_bloquear_tutor(true);
            }
        }).fail(function (xhr) {
            swal({ title: 'Error', text: mensaje_error_ajax(xhr, 'No se pudo guardar la nota.'), icon: 'error', button: 'Aceptar' });
        }).always(function () {
            $boton.prop('disabled', false);
        });
    });

    $('#boton_bloquear_tutor').on('click', function () {
        var $motivo = $('#bloquear_tutor_motivo');
        $motivo.val($.trim($motivo.val()));
        if (!$motivo.val()) {
            $motivo.addClass('is-invalid');
            return;
        }
        $motivo.removeClass('is-invalid');

        var $boton = $(this).prop('disabled', true);
        $.ajax({
            url: "{{ route('profesional.tutor.bloquear', ['tutor' => $tutor->id]) }}",
            type: 'POST',
            data: { motivo: $motivo.val(), _token: "{{ csrf_token() }}" }
        }).done(function () {
            $('#etiqueta_tutor_bloqueado').attr('title', 'Motivo: ' + $motivo.val());
            marcar_tutor_bloqueado(true);
            $('#modal_bloquear_tutor').modal('hide');
            swal({ title: 'Tutor bloqueado', text: 'Ya no podrá tomar horas contigo.', icon: 'success', button: 'Aceptar' });
        }).fail(function (xhr) {
            swal({ title: 'Error', text: mensaje_error_ajax(xhr, 'No se pudo bloquear al tutor.'), icon: 'error', button: 'Aceptar' });
        }).always(function () {
            $boton.prop('disabled', false);
        });
    });

    function desbloquear_tutor() {
        swal({
            title: '¿Desbloquear al tutor?',
            text: 'Podrá volver a tomar horas contigo.',
            icon: 'warning',
            buttons: ['Cancelar', 'Desbloquear'],
            dangerMode: true
        }).then(function (confirmado) {
            if (!confirmado) {
                return;
            }
            $.ajax({
                url: "{{ route('profesional.tutor.desbloquear', ['tutor' => $tutor->id]) }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}" }
            }).done(function () {
                marcar_tutor_bloqueado(false);
                swal({ title: 'Tutor desbloqueado', text: 'Ya puede volver a tomar horas contigo.', icon: 'success', button: 'Aceptar' });
            }).fail(function (xhr) {
                swal({ title: 'Error', text: mensaje_error_ajax(xhr, 'No se pudo desbloquear al tutor.'), icon: 'error', button: 'Aceptar' });
            });
        });
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
</script>
@endsection
