@extends('template.paciente.template')
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10 font-weight-bold">Mis documentos e indicaciones</h5>
                            </div>
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ ROUTE('paciente.home') }}" data-toggle="tooltip"
                                        data-placement="top" title="Volver a mi escritorio"><i
                                            class="feather icon-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="{{ ROUTE('paciente.receta') }}" data-toggle="tooltip"
                                        data-placement="top" title="Volver a inicio de receta online">Receta Online</a></li>
                                <li class="breadcrumb-item"><a href="#">Mis documentos e indicaciones</a></li>
                                      
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                         <div class="card-header">
                            <h4 class="text-c-blue f-20 d-inline ml-4 my-1 py-1">Mis documentos e indicaciones</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-12">
                                    <table id="tabla_recetas_paciente_ro"
                                        class="display table table-striped dt-responsive nowrap table-xs"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle">Fecha</th>
                                                <th class="text-center align-middle">Profesional o servicio</th>
                                                <th class="text-center align-middle">Diagnóstico</th>
                                                <th class="text-center align-middle">Tipo</th>
                                                <th class="text-center align-middle">ver documento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($documentos ?? [] as $documento)
                                                @php
                                                    $cuerpoDocumento = json_decode((string) $documento->cuerpo, true) ?: [];
                                                    $firmaTutorDocumento = $cuerpoDocumento['firma_tutor'] ?? null;
                                                    $esConsentimiento = $documento->otro === 'consentimiento_informado_veterinario';
                                                @endphp
                                                <tr id="documento-{{ $documento->id }}">
                                                    <td class="text-wrap text-center align-middle">{{ optional($documento->updated_at)->format('d/m/Y') }}</td>
                                                    <td class="align-middle text-center">
                                                        <strong>{{ optional($documento->profesional)->nombre }} {{ optional($documento->profesional)->apellido_uno }} {{ optional($documento->profesional)->apellido_dos }}</strong>
                                                        <br>{{ $documento->profesional ? (optional($documento->profesional->SubTipoEspecialidad()->first())->nombre ?: 'Medicina veterinaria') : 'Medicina veterinaria' }}
                                                    </td>
                                                    <td class="align-middle text-center">{{ $documento->observacion ?: ($esConsentimiento ? 'Consentimiento informado veterinario' : 'Presupuesto veterinario') }}</td>
                                                    <td class="align-middle text-center">
                                                        @if($firmaTutorDocumento)
                                                            <span class="badge badge-success">Firmado</span><br>
                                                            <small>{{ $firmaTutorDocumento['fecha'] ?? '' }}</small>
                                                        @else
                                                            <span class="badge badge-warning">Pendiente de firma</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <a href="{{ asset($documento->url) }}" target="_blank" class="btn btn-info-light-c btn-xxs">
                                                            <i class="feather icon-file-text"></i> {{ $esConsentimiento ? 'Ver consentimiento' : 'Ver presupuesto' }}
                                                        </a>
                                                        @if(!$firmaTutorDocumento)
                                                            @if($esConsentimiento)
                                                                <button type="button" class="btn btn-purple btn-xxs" onclick="firmarConsentimientoTutor({{ $documento->id }})">
                                                                    <i class="fas fa-qrcode"></i> Firmar y autorizar
                                                                </button>
                                                            @else
                                                                <button type="button" class="btn btn-purple btn-xxs" onclick="firmarPresupuestoTutor({{ $documento->id }})">
                                                                    <i class="fas fa-qrcode"></i> Firmar
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if (isset($fichas))
                                                @foreach ($fichas as $f)
                                                    @foreach ($f->Recetas()->get() as $r)
                                                        <tr>
                                                            <td class="text-wrap text-center align-middle">
                                                                {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }}
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <strong>{{ $f->Profesional()->first()->nombre }}
                                                                    {{ $f->Profesional()->first()->apellido_uno }}
                                                                    {{ $f->Profesional()->first()->apellido_dos }}
                                                                </strong>
                                                                <br>
                                                                {{ $f->Profesional()->first()->especialidad()->first()->txt_esp }}
                                                            </td>
                                                            <td class="align-middle text-center">{{ $f->diagnostico }}
                                                            </td>
                                                            <td class="align-middle text-center">Enviado</td>
                                                            <td class="text-center align-middle">
                                                                <button href="#!" class="btn btn-danger-light-c btn-xxs"
                                                                    data-toggle="modal" data-target="#m_cons_receta"><i
                                                                        class="feather icon-file-plus"></i> Ver
                                                                  </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            @endif
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
    <!--Cierre: Container Completo-->
@endsection
@section('page-script')
    <script>
        function firmarConsentimientoTutor(idDocumento) {
            swal({
                title: 'Firmar y autorizar consentimiento',
                text: 'Declaro que revisé el consentimiento y autorizo el procedimiento veterinario. Se generará una firma electrónica con QR y se enviará al profesional.',
                icon: 'warning',
                buttons: ['Cancelar', 'Firmar y autorizar']
            }).then(function(acepta) {
                if (!acepta) return;

                $.ajax({
                    url: "{{ route('paciente.documentos.firmar_consentimiento', ['documento' => '__ID__']) }}".replace('__ID__', idDocumento),
                    type: 'POST',
                    data: { acepta: 1, _token: '{{ csrf_token() }}' },
                    beforeSend: function() {
                        swal({
                            title: 'Generando firma...',
                            text: 'Por favor, espere.',
                            icon: 'info',
                            buttons: false,
                            closeOnClickOutside: false
                        });
                    },
                    success: function(resp) {
                        if (resp.estado == 1) {
                            swal('Consentimiento autorizado', resp.msj, 'success').then(function() {
                                window.location.reload();
                            });
                        } else {
                            swal('Error', resp.msj || 'No fue posible firmar el consentimiento.', 'error');
                        }
                    },
                    error: function(xhr) {
                        const mensaje = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'No fue posible firmar el consentimiento.';
                        swal('Error', mensaje, 'error');
                    }
                });
            });
        }

        function firmarPresupuestoTutor(idDocumento) {
            swal({
                title: 'Firmar presupuesto',
                text: 'Declaro que revisé y acepto el presupuesto. Se generará una firma electrónica con QR y se notificará al odontólogo.',
                icon: 'warning',
                buttons: ['Cancelar', 'Firmar y enviar']
            }).then(function(acepta) {
                if (!acepta) return;

                $.ajax({
                    url: "{{ route('paciente.documentos.firmar_presupuesto', ['documento' => '__ID__']) }}".replace('__ID__', idDocumento),
                    type: 'POST',
                    data: { acepta: 1, _token: '{{ csrf_token() }}' },
                    beforeSend: function() {
                        swal({
                            title: 'Generando firma...',
                            text: 'Por favor, espere.',
                            icon: 'info',
                            buttons: false,
                            closeOnClickOutside: false
                        });
                    },
                    success: function(resp) {
                        if (resp.estado == 1) {
                            swal('Presupuesto firmado', resp.msj, 'success').then(function() {
                                window.location.reload();
                            });
                        } else {
                            swal('Error', resp.msj || 'No fue posible firmar el presupuesto.', 'error');
                        }
                    },
                    error: function(xhr) {
                        const mensaje = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'No fue posible firmar el presupuesto.';
                        swal('Error', mensaje, 'error');
                    }
                });
            });
        }

        $(document).ready(function() {
            $('#tabla_recetas_paciente_ro').DataTable({
                responsive: true,
            });
        });
    </script>
@endsection
