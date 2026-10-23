@extends('template.paciente_dependiente.template')
@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        @include('template.partials.boton_volver', ['url' => route('paciente.dependiente.receta', ['id_dependiente_activo' => $id_dependiente_activo])])
        <!--Header-->
        <div class="row">
            <div class="col-md-12 mb-3">
                <h5 class="f-26 d-inline">Mis exámenes</h5>
            </div>
        </div>
        <!--Cierre: Header-->
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 col-md-12">
                                <table id="tabla_examenes_paciente_ro"
                                    class="display table table-striped table-hover dt-responsive nowrap table-sm"
                                    style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-left align-middle">Fecha</th>
                                            <th class="text-left align-middle">Nº de Orden</th>
                                            <th class="text-left align-middle">Profesional</th>
                                            <th class="text-left align-middle">Nombre del examen</th>
                                            <th class="text-left align-middle">Comentarios</th>
                                            <th class="text-left align-middle">Estado</th>
                                            <th class="text-left align-middle">Examen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (($fichas ?? collect()) as $ficha)
                                            @foreach ($ficha->OrdenesExamenes as $orden)
                                                <tr>
                                                    <td>{{ optional($orden->created_at)->format('d/m/Y') }}</td>
                                                    <td>{{ $orden->id }}</td>
                                                    <td>{{ trim(($ficha->Profesional->nombre ?? '').' '.($ficha->Profesional->apellido_uno ?? '').' '.($ficha->Profesional->apellido_dos ?? '')) }}</td>
                                                    <td>{{ $orden->examen }}</td>
                                                    <td>{{ $orden->observacion }}</td>
                                                    <td>Emitida</td>
                                                    <td><a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('mascota.documento_emitido.ver', ['tipo' => 'examenes', 'id_ficha_atencion' => $ficha->id]) }}">Ver orden</a></td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        <!-- Pendiente
                                        <tr>
                                            <td class="text-wrap text-left align-middle">11/09/2020</td>
                                            <td class="text-wrap text-left align-middle">12344434</td>
                                            <td class="align-middle text-left">
                                                <strong>Jaime Kriman Astorga</strong><br>
                                                Otorrinolaringólogo</td>
                                            <td class="align-middle text-left">Hemograma Completo</td>
                                            <td class="align-middle text-left">Comentarios
                                            <td class="align-middle text-left">Enviado</td>
                                            <td class="text-left align-middle">
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    data-toggle="modal" data-target="#m_cons_ex"><i
                                                        class="feather icon-file-plus"></i> Ver Examen</button>
                                            </td>
                                        </tr>
                                        -->
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
<!-- Modal examen profesional-->
<div id="agregar_examen_profesional_ro" class="modal fade" tabindex="-1" role="dialog"
    aria-labelledby="agregar_receta_profesional_ro" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title text-center">Agregar examen</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <form id="recetas_profesional">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <h6 class="text-c-blue ml-2 mb-3">Ingrese los datos del examen</h6>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <label class="floating-label">RUT</label>
                                <input type="person" class="form-control" name="rut_paciente" id="rut_paciente">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <label class="floating-label">Nombres</label>
                                <input type="text" class="form-control" name="nombres_paciente" id="nombres_paciente">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <label class="floating-label">Apellidos</label>
                                <input type="text" class="form-control" name="apellidos_paciente"
                                    id="apellidos_paciente">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <label class="floating-label">Nombre del Examen</label>
                                <select id="nombre_examen" name="nombre_examen" class="form-control">
                                    <option selected value="0">Seleccione una opción </option>
                                    <option>..</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <label class="floating-label-activo">Fecha</label>
                                <input type="date" class="form-control" name="fecha_paciente" id="fecha_paciente">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <h6 class="ml-2 mt-2">Adjuntar examen</h6>
                                <input type="file" class="aform-control-file pb-3" id="adjuntar_examen_receta_online">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12">
                            <div class="form-group">
                                <label class="floating-label">Comentarios</label>
                                <textarea class="form-control" rows="1" name="comentarios_examen"
                                    id="comentarios_examen"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('page-script')
<script>
    $(document).ready(function () {
        $('#tabla_examenes_paciente_ro').DataTable({
            responsive: true,
        });
    });
</script>
@endsection
