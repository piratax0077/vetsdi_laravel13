@extends('template.profesional.template')
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h5 class="f-26 d-inline">Mis exámenes emitidos</h5>
                </div>
            </div>
            <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="tabla_examenes_profesional_ro"
                                class="display table table-striped dt-responsive nowrap table-xs"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        {{--  <th class="text-wrap text-center align-middle" hidden="hidden">Nº de Orden</th>  --}}
                                        <th class="text-wrap text-center align-middle">Fecha</th>
                                        <th class=" align-middle">Tutor</th>
                                        <th class=" align-middle">Mascota</th>
                                        <th class=" align-middle">Nombre del examen</th>
                                        <th class=" align-middle">Tipo</th>
                                        <th class=" align-middle">Documento</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @if (isset($examenes) && count($examenes) > 0)
                                        @foreach ($examenes as $examen)
                                            <tr>
                                                {{--  <td class="text-wrap align-middle">{{ $examen->id }}</td>  --}}
                                                <td class="text-wrap text-center align-middle" style="font-size:12px">
                                                    {{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $examen['created_at']) }}
                                                </td>

                                                <td class="align-middle" style="font-size:12px">
                                                    {{ $examen['Paciente']['nombre'] }}<br>
                                                    {{ $examen['Paciente']['rut'] }}
                                                </td>
                                                <td class="align-middle" style="font-size:12px">{{ $examen['mascota'] ?? '-' }}</td>
                                                <td class="align-middle text-wrap" style="font-size:10px"><label>{{ $examen['examen'] }}</label></td>
                                                <td class="align-middle text-wrap" style="font-size:10px"><label>{{ $examen['tipo_examen'] }}</label></td>
                                                <!--<td class="align-middle text-center">
                                                    <button type="button" class="btn  btn-icon btn-success" data-toggle="tooltip" data-placement="top" title="Enviar Examen a Paciente"><i class="feather icon-navigation"></i></button>
                                                </td>-->
                                                 <!--<td class="align-middle text-center">Enviado</td>-->
                                               <td class="align-middle"> <div onclick="ver_pdf_orden_examenes('{{ $examen['id_ficha_atencion']  }}')" class="btn btn-success-light-c btn-xxs"><i class="feather icon-activity"></i> Ver examen</div></td>
                                            </tr>
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
    <!--Cierre: Container Completo-->
@endsection
