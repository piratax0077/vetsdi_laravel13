@extends('template.profesional.template')

@section('content')

    <!--Container Completo-->

    <div class="pcoded-main-container">

        <div class="pcoded-content">

            <!--Header-->

            <div class="row">
                <div class="col-md-12 mb-3">
                    <h5 class="f-26 d-inline">Mis documentos e indicaciones</h5>
                </div>
            </div>

            <!--Cierre: Header-->

            <div class="row">

                <div class="col-sm-12">

                    <div class="card">


                        <div class="card-body">

                            <div class="row">

                                <div class="col-sm-6 col-md-12">

                                    <table id="tabla_recetas_paciente_ro"

                                        class="display table table-striped dt-responsive nowrap table-xs"

                                        style="width:100%">

                                        <thead>

                                            <tr>

                                                <th class="text-center align-middle">Fecha</th>

                                                <th class="text-center align-middle">Paciente</th>

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
                                                <tr>
                                                    <td class="text-wrap text-center align-middle">{{ optional($documento->updated_at)->format('d/m/Y') }}</td>
                                                    <td class="align-middle text-center">
                                                        {{ optional($documento->paciente)->nombres }} {{ optional($documento->paciente)->apellido_uno }} {{ optional($documento->paciente)->apellido_dos }}
                                                    </td>
                                                    <td class="align-middle text-center">{{ $documento->observacion ?: ($esConsentimiento ? 'Consentimiento informado veterinario' : 'Presupuesto veterinario') }}</td>
                                                    <td class="align-middle text-center">
                                                        @if($firmaTutorDocumento)
                                                            <span class="badge badge-success">Firmado por tutor</span><br>
                                                            <small>{{ $firmaTutorDocumento['fecha'] ?? '' }}</small>
                                                        @else
                                                            <span class="badge badge-warning">Pendiente de firma</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <a href="{{ asset($documento->url) }}" target="_blank" class="btn btn-info-light-c btn-xxs">
                                                            <i class="feather icon-file-text"></i> {{ $esConsentimiento ? 'Ver consentimiento' : 'Ver presupuesto' }}
                                                        </a>
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

                                                                {{ $f->Paciente()->first()->nombres }}

                                                                {{ $f->Paciente()->first()->apellido_uno }}

                                                                {{ $f->Paciente()->first()->apellido_dos }}

                                                            </td>

                                                            <td class="align-middle text-center">{{ $f->hipotesis_diagnostico }}

                                                            </td>

                                                            <td class="align-middle text-center">Enviado</td>

                                                            <td class="text-center align-middle">

                                                                <button href="#!" class="btn btn-danger-light-c btn-xxs" data-toggle="modal" data-target="#m_cons_receta"><i class="feather icon-file-plus"></i> Ver </button>

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

        $(document).ready(function() {

            $('#tabla_recetas_paciente_ro').DataTable({

                responsive: true,

            });

        });

    </script>

@endsection

