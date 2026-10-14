@extends('template.profesional.template')
@section('content')

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-2">
                    <h5 class="f-26 d-inline">Configuracion de Diagnósticos Frecuentes</h5>
                </div>
            </div>
            <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h4 class="text-white f-20 text-center mb-0">Diagnósticos Frecuentes</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-12">
                                    <table id="tabla_configuracion_diagnosticos_frecuentes"
                                        class="display table table-striped table-hover dt-responsive nowrap table-xs"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-wrap text-center align-middle" style="width: 6rem;">Código
                                                </th>
                                                <th class="text-center align-middle">Descripción</th>
                                                <th class="text-center align-middle">Tipo</th>
                                                <th class="text-center align-middle">Activar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-wrap text-center align-middle">1</td>
                                                <td class="align-middle text-center">Hematrocrito</td>
                                                <td class="align-middle text-center">Sangre</td>
                                                <td class="align-middle text-center">
                                                    <div class="switch switch-success d-inline m-r-10">
                                                        <input type="checkbox" id="examen_1">
                                                        <label for="examen_1" class="cr"></label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-wrap text-center align-middle">Nº</td>
                                                <td class="align-middle text-center">Nombre del examen</td>
                                                <td class="align-middle text-center">Imagenes</td>
                                                <td class="align-middle text-center">
                                                    <div class="switch switch-success d-inline m-r-10">
                                                        <input type="checkbox" id="examen_2">
                                                        <label for="examen_2" class="cr"></label>
                                                    </div>
                                                </td>
                                            </tr>
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
