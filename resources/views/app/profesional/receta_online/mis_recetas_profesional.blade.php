@extends('template.profesional.template')
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <h5 class="f-26 d-inline">Mis recetas emitidas</h5>
                </div>
            </div>
            <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="tabla_recetas_profesional_ro"
                                class="display table table-striped dt-responsive nowrap table-xs"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tutor</th>
                                        <th>Mascota</th>
                                        <th>Diagnóstico</th>
                                        <th>Receta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ficha as $fic)
                                        @if(!empty($fic->recetas) && $fic->recetas !=null && count($fic->recetas)>0)
                                        <tr>
                                            <td>
                                                {{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $fic->created_at) }}
                                            </td>
                                            <td>
                                                {{ $fic->Paciente()->first()->nombres }}<br>
                                                {{ $fic->Paciente->first()->rut }}
                                            </td>
                                            <td>{{ $fic->mascota_descripcion }}</td>
                                            <td>{{ $fic->motivo }}</td>
                                            <td>
                                                {{-- <a href="{{ route('profesional.ver_recetas_pdf', $fic->id) }}"><img src="{{ asset('images/documento.png') }}" alt="Documento" height="35px"> Ver receta</a> --}}
                                                <div onclick="ver_pdf_receta('{{ $fic->id }}')" class="btn btn-warning-light-c btn-xxs"><i class="feather icon-activity"></i> Ver Receta</div>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
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

