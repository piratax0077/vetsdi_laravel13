@extends('template.adm_cm.template')
@section('content')
    @php
        $centroActivo = $contextoActivo['label'] ?? 'el centro';
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
        $maximoFilas = 15;
        $totalFirmados = $documentosFirmados->count() + $consentimientos->count();
        $consentimientosVisibles = $consentimientos->take(max(0, $maximoFilas - $documentosFirmados->count()));
        $pestanasTutor = [
            'contacto' => 'Contacto',
            'consentimientos' => 'Consentimientos',
            'mascotas' => 'Mascotas',
            'firmados' => 'Lo que firmó',
            'compras' => 'Sus compras',
            'cuenta' => 'Su cuenta',
        ];
    @endphp

    <div class="pcoded-main-container">
        <div class="pcoded-content">

            <div class="row">
                <div class="col-md-12 mb-3 encabezado-pagina">
                    <div class="mb-2 mb-md-0">
                        <a href="{{ route('adm_cm.pacientes') }}" class="text-muted f-12 d-print-none">
                            <i class="feather icon-arrow-left" aria-hidden="true"></i> Mascotas y tutores
                        </a>
                        <h5 class="f-26 d-block mb-0">{{ $nombreTutor }}</h5>
                        <span class="text-muted">
                            @if ($telefonoTutor)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $telefonoTutor) }}" class="text-muted">{{ $telefonoTutor }}</a> ·
                            @endif
                            @if ($tutor->rut)
                                {{ $tutor->rut }}
                            @else
                                <span class="text-danger">falta el RUT</span>
                            @endif
                        </span>
                        <small class="d-block text-muted">{{ $centroActivo }}</small>
                    </div>
                    <div class="ml-md-auto d-print-none">
                        <button type="button" class="btn btn-icon btn-warning js-tooltip-accion" onclick="abrirMensajeTutor()"
                            title="Envíe un mensaje al tutor" aria-label="Envíe un mensaje al tutor">
                            <i class="feather icon-mail"></i>
                        </button>
                        <button type="button" class="btn btn-icon btn-purple js-tooltip-accion" onclick="window.print()"
                            title="Copia de todos sus datos" aria-label="Copia de todos sus datos" data-placement="left">
                            <i class="feather icon-printer"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <!--Una card por pestaña; al imprimir salen todas-->
                    <ul class="nav nav-tabs-secciones d-print-none" id="tutor-secciones" role="tablist">
                        @foreach ($pestanasTutor as $clave => $nombrePestana)
                            <li class="nav-item-secciones">
                                <a @class(['nav-secciones', 'active' => $loop->first]) id="tutor-{{ $clave }}-tab" data-toggle="pill"
                                    href="#tutor-{{ $clave }}" role="tab" aria-controls="tutor-{{ $clave }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $nombrePestana }}</a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane d-print-block active" id="tutor-contacto" role="tabpanel" aria-labelledby="tutor-contacto-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="ficha-profesional__titulo"><i class="fas fa-address-book mr-1" aria-hidden="true"></i> Contacto</h6>
                                    <div class="form-row mb-n2">
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Correo</span>
                                                    <span class="ficha-profesional__valor">
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
                                                    <span class="ficha-profesional__valor">
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
                                                    <span class="ficha-profesional__valor">{{ $convenio ?: 'Sin registro' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-8 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="fas fa-home" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Dirección</span>
                                                    <span class="ficha-profesional__valor">{{ $direccionTexto ?: 'Sin registro' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="ficha-profesional__dato py-2 h-100">
                                                <span class="ficha-profesional__icono"><i class="far fa-calendar-alt" aria-hidden="true"></i></span>
                                                <div>
                                                    <span class="ficha-profesional__etiqueta">Atenciones en el centro</span>
                                                    <span class="ficha-profesional__valor">
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

                        <div class="tab-pane d-print-block" id="tutor-consentimientos" role="tabpanel" aria-labelledby="tutor-consentimientos-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="ficha-profesional__titulo"><i class="fas fa-user-check mr-1" aria-hidden="true"></i> Consentimientos</h6>
                                    <p class="text-muted mb-2">Lo que esta persona aceptó desde su perfil, y cuándo.</p>
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

                        <div class="tab-pane d-print-block" id="tutor-mascotas" role="tabpanel" aria-labelledby="tutor-mascotas-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="ficha-profesional__titulo"><i class="fas fa-paw mr-1" aria-hidden="true"></i> Mascotas</h6>
                                    @if ($mascotas->isEmpty())
                                        <div class="ficha-profesional__vacio">No tiene mascotas en este centro.</div>
                                    @else
                                        <div class="form-row mb-n2">
                                            @foreach ($mascotas as $mascota)
                                                @php
                                                    $fotoRespaldoMascota = $mascota->sexo === 'F'
                                                        ? asset('images/iconos/paciente-f.svg')
                                                        : asset('images/iconos/paciente-m.svg');
                                                    $fotoMascota = $mascota->foto_url ?: (mascota_imagenes_publicas($mascota)[0] ?? $fotoRespaldoMascota);
                                                    $especie = optional($mascota->especieMascota)->nombre ?? $mascota->especie ?? 'Sin especie';
                                                    $raza = optional($mascota->razaMascota)->nombre ?? $mascota->otra_especie;
                                                @endphp
                                                <div class="col-md-6 mb-2">
                                                    <div class="ficha-profesional__dato py-2 h-100">
                                                        <span class="traspaso-mascota-foto">
                                                            <img src="{{ $fotoMascota }}" alt="Foto de {{ $mascota->nombre }}"
                                                                onerror="this.onerror=null;this.src='{{ $fotoRespaldoMascota }}';">
                                                        </span>
                                                        <div class="flex-grow-1">
                                                            <span class="ficha-profesional__valor">{{ $mascota->nombre ?? 'Mascota' }}</span>
                                                            <span class="ficha-profesional__etiqueta">{{ $especie }}{{ $raza ? ' · ' . $raza : '' }}{{ $mascota->fallecida ? ' · Fallecida' : '' }}</span>
                                                        </div>
                                                        <div class="d-flex flex-shrink-0 d-print-none">
                                                            <a href="{{ route('profesional.mascota.ficha_veterinaria', ['mascota' => $mascota->id]) }}"
                                                                class="btn btn-icon btn-purple js-tooltip-accion"
                                                                title="Ver ficha veterinaria" aria-label="Ver ficha veterinaria">
                                                                <i class="feather icon-file-text"></i>
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

                        <div class="tab-pane d-print-block" id="tutor-firmados" role="tabpanel" aria-labelledby="tutor-firmados-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="ficha-profesional__titulo"><i class="fas fa-file-signature mr-1" aria-hidden="true"></i> Lo que firmó</h6>
                                    <p class="text-muted mb-2">Consentimientos y autorizaciones a su nombre. Se emiten desde la ficha de cada mascota.</p>
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

                        <div class="tab-pane d-print-block" id="tutor-compras" role="tabpanel" aria-labelledby="tutor-compras-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="ficha-profesional__titulo"><i class="fas fa-receipt mr-1" aria-hidden="true"></i> Sus compras</h6>
                                    <p class="text-muted mb-2">Lo que se le ha cobrado en las atenciones del centro. Un cobro devuelto queda tachado y no se esconde.</p>
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
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($cobros->take($maximoFilas) as $cobro)
                                                        <tr @class(['text-muted' => $cobro->devuelto])>
                                                            <td>{{ $cobro->fecha_atencion ? \Carbon\Carbon::parse($cobro->fecha_atencion)->format('d-m-Y') : '-' }}</td>
                                                            <td>
                                                                {{ optional($cobro->TipoBono)->nombre ?? 'Atención' }}
                                                                @if ($cobro->devuelto)
                                                                    <span class="badge badge-light-danger ml-1">Devuelto</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $cobro->numero_bono ?: '-' }}</td>
                                                            @if ($cobro->devuelto)
                                                                <td class="text-right"><del>{{ $pesos($cobro->valor_atencion) }}</del></td>
                                                                <td class="text-right"><del>{{ $pesos($cobro->a_pagar) }}</del></td>
                                                            @else
                                                                <td class="text-right">{{ $pesos($cobro->valor_atencion) }}</td>
                                                                <td class="text-right">{{ $pesos($cobro->a_pagar) }}</td>
                                                            @endif
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

                        <div class="tab-pane d-print-block" id="tutor-cuenta" role="tabpanel" aria-labelledby="tutor-cuenta-tab">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="ficha-profesional__titulo"><i class="fas fa-wallet mr-1" aria-hidden="true"></i> Su cuenta</h6>
                                    <p class="text-muted mb-3">Lo que pagó y lo que se le devolvió.</p>
                                    <div class="form-row mb-n2">
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="tarjeta-indicador tarjeta-indicador--morado">
                                                <span class="tarjeta-indicador-icono"><i class="feather icon-file-text"></i></span>
                                                <strong class="tarjeta-indicador-valor">{{ $pesos($cuenta['cobrado']) }}</strong>
                                                <span class="tarjeta-indicador-etiqueta">Valor de sus atenciones</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-2">
                                            <div class="tarjeta-indicador">
                                                <span class="tarjeta-indicador-icono"><i class="feather icon-check-circle"></i></span>
                                                <strong class="tarjeta-indicador-valor">{{ $pesos($cuenta['pagado']) }}</strong>
                                                <span class="tarjeta-indicador-etiqueta">Pagado por el tutor</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-lg-4 mb-2">
                                            <div class="tarjeta-indicador tarjeta-indicador--rojo">
                                                <span class="tarjeta-indicador-icono"><i class="feather icon-rotate-ccw"></i></span>
                                                <strong class="tarjeta-indicador-valor">{{ $pesos($cuenta['devuelto']) }}</strong>
                                                <span class="tarjeta-indicador-etiqueta">Devuelto</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade d-print-none" id="modalMensajeTutor" tabindex="-1" role="dialog" aria-labelledby="modalMensajeTutorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title" id="modalMensajeTutorLabel">Mensaje a {{ $nombreTutor }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
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
@endsection

@section('page-script')
<script>
    function abrirMensajeTutor() {
        $('#modalMensajeTutor').modal('show');
    }

    function enviarMensajeTutor() {
        var asunto = $.trim($('#mensaje_tutor_asunto').val());
        var mensaje = $.trim($('#mensaje_tutor_texto').val());
        if (asunto === '' || mensaje === '') {
            swal({ title: 'Error', text: asunto === '' ? 'Ingrese el asunto' : 'Ingrese el mensaje', icon: 'error', button: 'Aceptar' });
            return;
        }

        $.ajax({
            url: "{{ route('adm_cm.pacientes.mensaje') }}",
            type: 'POST',
            data: { _token: "{{ csrf_token() }}", id_responsable: {{ $tutor->id }}, asunto: asunto, mensaje: mensaje },
            success: function (response) {
                if (response.estado == 1) {
                    swal({ title: 'Mensaje enviado', text: 'El mensaje ha sido enviado correctamente', icon: 'success', button: 'Aceptar' });
                    $('#modalMensajeTutor').modal('hide');
                    $('#mensaje_tutor_asunto, #mensaje_tutor_texto').val('');
                } else {
                    swal({ title: 'Error', text: response.mensaje || 'Ocurrió un error al enviar el mensaje', icon: 'error', button: 'Aceptar' });
                }
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                swal({ title: 'Error', text: response.mensaje || 'Ocurrió un error al enviar el mensaje', icon: 'error', button: 'Aceptar' });
            }
        });
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
</script>
@endsection
