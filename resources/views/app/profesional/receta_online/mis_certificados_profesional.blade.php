@extends('template.profesional.template')
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h5 class="f-26 d-inline">Mis Documentos emitidos</h5>
                </div>
            </div>
            <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                            <div class="card-body">
                                <table id="tabla_certificado_profesional_ro"
                                    class="display table table-striped dt-responsive nowrap table-xs"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="align-middle">Fecha</th>
                                            <th>Tutor</th>
                                            <th>Mascota</th>
                                            <th>Tipo</th>
                                            <th>Documento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($reposo)
                                            @foreach ($reposo as $rep)
                                                <tr>
                                                    <td class="text-wrap">{{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $rep->created_at) }}
                                                    </td>
                                                    <td>
                                                        {{ $rep->Paciente()->first()->nombres }}<br>
                                                        {{ $rep->Paciente()->first()->rut }}
                                                    </td>
                                                    <td>{{ $rep->mascota_descripcion ?? '-' }}</td>
                                                    <td>Certificado Reposo</td>
                                                    <!--
                                                    <td class="align-middle text-center">
                                                        <button type="button" class="btn  btn-icon btn-success" data-toggle="tooltip" data-placement="top" title="Enviar Certificado a Paciente"><i class="feather icon-navigation"></i></button>
                                                    </td>
                                                    <td class="align-middle text-center">Enviado</td>
                                                    -->
                                                    <td class="align-middle"> <div onclick="ver_pdf_certificado_reposo('{{ $rep->id_ficha_atencion }}')"><img src="{{ asset('images/documento.png') }}" alt="Documento" height="35px"></div></td>
                                                    <!--
                                                    <td class="align-middle text-center"><a href="#" download="Certificado_NombrePaciente"><img src="{{ asset('images/documento.svg') }}" alt="Documento" height="20px"> Ver</a></td>-->
                                                </tr>
                                            @endforeach
                                        @endif

                                        @if ($interconsulta)
                                            @foreach ($interconsulta as $inter)
                                                <tr>
                                                    <td class="text-wrap">
                                                        {{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $inter->created_at) }}
                                                    </td>
                                                    <td class="">
                                                        {{ $inter->Paciente()->first()->nombres }}<br>
                                                        {{ $inter->Paciente()->first()->rut }}
                                                    </td>
                                                    <td>{{ $inter->mascota_descripcion ?? '-' }}</td>
                                                    <td>Interconsulta</td>
                                                    <!--<td class="align-middle text-center">
                                                    <button type="button" class="btn  btn-icon btn-success" data-toggle="tooltip" data-placement="top" title="Enviar Certificado a Paciente"><i class="feather icon-navigation"></i></button>
                                                </td>
                                                <td class="align-middle text-center">Enviado</td>-->
                                                    <td> <div onclick="ver_pdf_interconsulta('{{ $inter->id }}')" class="btn btn-secondary-light-c btn-xxs"><i class="feather icon-activity"></i> Ver Receta</div></td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        @if ($informesMedicos)
                                            @foreach ($informesMedicos as $informe)
                                                <tr>
                                                    <td>
                                                        {{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $informe->created_at) }}
                                                    </td>
                                                    <td>
                                                        {{ $informe->Paciente()->first()->nombres }}<br>
                                                        {{ $informe->Paciente()->first()->rut }}
                                                    </td>
                                                    <td>{{ $informe->mascota_descripcion ?? '-' }}</td>
                                                    <td>Informe Médico</td>
                                                    <!--<td class="align-middle text-center">
                                                    <button type="button" class="btn  btn-icon btn-success" data-toggle="tooltip" data-placement="top" title="Enviar Certificado a Paciente"><i class="feather icon-navigation"></i></button>
                                                </td>
                                                <td class="align-middle text-center">Enviado</td>-->
                                                    <td class="align-middle"> <a href="{{ route('pdf.informe_medico', ['id_informe_medico'=>$informe->id]) }}" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm"><i class="feather icon-file"></i> Ver PDF</a></td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        {{--  @if ($controlObesidad)
                                            @foreach ($controlObesidad as $obesidad)
                                                <tr>
                                                    <td>
                                                        {{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $obesidad->created_at) }}
                                                    </td>
                                                    <td>
                                                        {{ $obesidad->Paciente()->first()->nombres }}<br>
                                                        {{ $obesidad->Paciente()->first()->rut }}
                                                    </td>
                                                    <td>Control Obesidad</td>
                                                    <!--<td class="align-middle text-center">
                                                    <button type="button" class="btn  btn-icon btn-success" data-toggle="tooltip" data-placement="top" title="Enviar Certificado a Paciente"><i class="feather icon-navigation"></i></button>
                                                </td>
                                                <td class="align-middle text-center">Enviado</td>-->
                                                    <td class="align-middle"><a href="#" download="Certificado_NombrePaciente"><img src="{{ asset('images/documento.png') }}" alt="Documento" height="35px"> Ver </a></td>
                                                </tr>
                                            @endforeach
                                        @endif  --}}

                                        {{--  @if ($hipertensiones)
                                            @foreach ($hipertensiones as $hipertension)
                                                <tr>
                                                    <td>
                                                        {{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $hipertension->created_at) }}
                                                    </td>
                                                    <td>
                                                        {{ $hipertension->Paciente()->first()->nombres }}<br>
                                                        {{ $hipertension->Paciente()->first()->rut }}
                                                    </td>
                                                    <td>Control Hipertensión</td>
                                                    <!--
                                                    <td class="align-middle text-center">
                                                        <button type="button" class="btn  btn-icon btn-success" data-toggle="tooltip" data-placement="top" title="Enviar Certificado a Paciente"><i class="feather icon-navigation"></i></button>
                                                    </td>
                                                    <td class="align-middle text-center">Enviado</td>
                                                    -->
                                                    <td class="align-middle"><a href="#" download="Certificado_NombrePaciente"><img src="{{ asset('images/documento.png') }}" alt="Documento" height="35px"></a></td>
                                                </tr>
                                            @endforeach
                                        @endif  --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Cierre: Container Completo-->
@endsection
