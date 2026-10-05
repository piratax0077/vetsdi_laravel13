@extends('template.profesional.template')
@section('content')

    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h5 class="f-26 d-inline">Examenes Transcritos</h5>
                </div>
            </div>
            <!--Cierre: Header-->

            <!--Tabla Examenes transcritos-->
            <div class="row m-b-30">
                <div class="col-md-12">
                    <div class="card h-100 pb-0">
                        <div class="card-header text-center bg-c-info">
                            <div class="row">
                                <div class="col-sm-4 d-inline text-left">
                                    <h5 class="text-white my-2" style="font-size: 1.1rem;">Examenes</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pb-0 pt-4">
                            <div class="dt-responsive table-responsive align-middle pb-0">
                                <table id="table_examenes_transcritos" class="table table-striped table-bordered nowrap table-sm" style="height: 100px">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-left">Fecha Examen</th>
                                            <th class="text-center align-left">Paciente</th>
                                            <th class="text-center align-left">Examen</th>
                                            <th class="text-center align-middle">Asistente</th>
                                            <th class="text-center align-middle">Accion</th>
                                        </tr>
                                    </thead>
                                   <tbody>
                                        @if (isset($examenes_transcritos))
                                            @foreach ($examenes_transcritos as $ex_t)
                                                <tr>
                                                    <td class="text-center align-left">
                                                        {{ date('d-m-Y', strtotime($ex_t->horas_medicas_fecha_consulta)) }}
                                                    </td>
                                                    <td class="text-center align-left">
                                                        {{ $ex_t->paciente_nombres.' '.$ex_t->paciente_apellido_uno }}<br/>
                                                        {{ $ex_t->paciente_rut }}
                                                    </td>
                                                    <td class="text-center align-left bg-estado-light-amarillo">
                                                        {{ $ex_t->examen_especialidad_nombre }}
                                                    </td>
                                                    <td class="text-center align-left bg-estado-light-amarillo">
                                                        {{ $ex_t->asistentes_nombres.' '.$ex_t->asistentes_apellido_uno }}<br/>
                                                        {{ $ex_t->asistentes_rut }}
                                                    </td>
                                                    <td class="text-center align-left bg-estado-light-amarillo">
                                                        {{-- boton de ver  --}}
                                                        <button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Ver examen" onclick="abrir_transcripcion_examen('{{ $ex_t->horas_medicas_id }}')"><i class="feather icon-eye"></i> VER - CONFIRMAR</button>
                                                        {{-- aprobar --}}
                                                        {{-- <button type="button" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Aprobar"><i class="feather icon-check"></i>APROBAR</button> --}}
                                                    </td>
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
    </div>
    <!--Cierre: Container Completo-->

    @include('app.profesional.modales.transcribir_examen')
@endsection
