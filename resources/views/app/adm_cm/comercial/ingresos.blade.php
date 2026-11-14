@extends('template.adm_cm.template')
@section('content')
<!--Container Completo-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="row">
            <div class="col-md-12 mb-3 encabezado-pagina">
                <h5 class="f-26 d-inline">Mis Ingresos</h5>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header encabezado-card">
                    <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-tag"></i></span>Bonos</h5>
                </div>
                <div class="card-body">
                    <table id="tabla_bonos_ingresos" class="display table table-striped table-hover dt-responsive nowrap table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-wrap text-center align-middle">Convenio</th>
                                <th class="text-center align-middle">Codigo</th>
                                <th class="text-center align-middle">Tipo</th>
                                <th class="text-center align-middle">Fecha de Atención</th>
                                <th class="text-center align-middle">Paciente</th>
                                <th class="text-center align-middle">Valor Total</th>
                                <th class="text-center align-middle">Estado Consulta</th>
                                <th class="text-center align-middle">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if( isset($bonos) )
                                @foreach($bonos as $key_br => $value_br)
                                    <tr >
                                        <td class="align-middle text-center">{{ $value_br->Convenio()->first()->nombre }}</td>
                                        <td class="align-middle text-center">{{ $value_br->numero_bono }}</td>
                                        <td class="align-middle text-center">{{ $value_br->TipoBono()->first()->nombre }}</td>
                                        <td class="align-middle text-center">{{ $value_br->fecha_atencion }}</td>
                                        <td class="align-middle text-center">
                                            <span>{{ $value_br->Paciente()->first()->nombres }} {{ $value_br->Paciente()->first()->apellido_uno }} {{ $value_br->Paciente()->first()->apellido_dos }}</span><br>
                                            <span>{{ rut_formatear($value_br->Paciente()->first()->rut) ?? ($value_br->Paciente()->first()->rut) }}</span>
                                        </td>
                                        <td class="align-middle text-center">${{ number_format($value_br->valor_atencion, 2, ",", ".") }}</td>
                                        <td class="align-middle text-center">{{ $value_br->estado_consulta }}</td>
                                        <td class="align-middle text-center">
                                            <div class="form-group">
                                                <div class="switch switch-success d-inline m-r-10">
                                                    <input type="checkbox" id="rendir_caja_programa_{{ $value_br->id }}">
                                                    <label for="rendir_caja_{{ $value_br->id }}"
                                                        class="floating-label-activo-sm cr"></label>
                                                </div>
                                            </div>
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
@endsection
