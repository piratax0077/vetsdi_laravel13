<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
    <div class="form-row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <ul class="nav nav-tabs-aten nav-fill mb-10" id="enf_urg" role="tablist">
                <li class="nav-item">
                    <a class="nav-link-aten text-reset active" id="tto_ingreso_tab_2" data-toggle="tab" href="#tto_ingreso_2" role="tab"aria-controls="tto_ingreso_2" aria-selected="true">Tratamientos Administrar</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link-aten text-reset" id="resumen_tto_tab" data-toggle="tab" href="#resumen_tto" role="tab" aria-controls="resumen_tto" aria-selected="flase">Resumen </a>
                </li>
            </ul>
        </div>
        <div class="form-row w-100">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="tab-content w-100 mt-2" id="v-pills-tabContent">
                    <div class="tab-pane fade show active" id="tto_ingreso_2"
                        role="tabpanel" aria-labelledby="tto_ingreso_tab_2">
                        <div class="form-row">

                        </div>
                        <div class="col-sm-12 col-md-12 m-t-5">
                            <div class="form-row">
                                <table id="tabla_tratamientos_servicio" class="display table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle">Fecha y Hora</th>
                                            <th class="text-center align-middle">Medicamento</th>
                                            <th class="text-center align-middle">V/A</th>
                                            <th class="text-center align-middle">Tolerancia</th>
                                            <th class="text-center align-middle">Acción</th>
                                            <th class="text-center align-middle">hora</th>
                                            <th class="text-center align-middle">materiales</th>
                                            <th class="text-center align-middle">editar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($enfermera)
                                        @foreach($recetas as $r)
                                        <input type="hidden" name="hora_tratamiento" id="hora_tratamiento_{{ $r->id }}" value="{{ $r->hora }}">
                                        <input type="hidden" name="fecha_tratamiento" id="fecha_tratamiento_{{ $r->id }}" value="{{ $r->fecha }}">
                                        <tr>
                                            <td>{{ $r->fecha }} <span id="hora_tratamiento_creado_{{ $r->id }}">{{ $r->hora }}</span> <br> {{ $r->responsable }}</td>
                                            <td>{{ $r->nombre_medicamento }}</td>
                                            <td>{{ $r->via_administracion }}</td>
                                            <td> <input type="text" id="observaciones_{{ $r->id }}" @if($r->estado_tratamiento == 0) value="{{ $r->observaciones }}" disabled @endif> </td>
                                            <td class="p-0"><div class="switch switch-success d-inline">
                                                <input type="checkbox"
                                                    id="tratamiento_listo_{{ $r->id }}"
                                                    disabled @if($r->estado_tratamiento == 0) checked disabled @endif>
                                                <label for="tratamiento_listo_{{ $r->id }}"
                                                    class="cr"></label>
                                            </div><br>
                                            <label id="leyenda_check_{{ $r->id }}">@if($r->estado_tratamiento == 0) Listo @else Pendiente @endif</label></td>
                                            <td><span id="hora_{{ $r->id }}">{{ $r->otros_2 }}</span></td>
                                            <td><button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#modalAgregarInsumos">Insumos</button></td>
                                            <td><button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#modalAgregarObservaciones" onclick="dame_tratamiento({{ $r->id }})"><i class="fas fa-edit" aria-hidden="true"> </i></button></td>
                                        </tr>
                                        @endforeach
                                        @else
                                        @foreach($recetas as $r)
                                        <tr>
                                            <td>{{ $r->fecha }} {{ $r->hora }} <br> {{ $r->responsable }}</td>
                                            <td>{{ $r->nombre_medicamento }}</td>
                                            <td>{{ $r->via_administracion }}</td>
                                            <td> <input type="text" @if($r->estado_tratamiento == 0) value="{{ $r->observaciones }}" @endif disabled> </td>
                                            <td class="p-0"><div class="switch switch-success d-inline">
                                                <input type="checkbox"
                                                    id="tratamiento_listo{{ $r->id }}"
                                                     disabled @if($r->estado_tratamiento == 0) checked @endif>
                                                <label for="tratamiento_listo{{ $r->id }}"
                                                    class="cr"></label>
                                            </div><br>
                                            <label>@if($r->estado_tratamiento == 0) Listo @else Pendiente @endif</label></td>
                                            <td>{{ $r->otros_2 }}</td>
                                            <td><button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#modalAgregarInsumos">Insumos</button></td>
                                            <td><button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#modalAgregarObservaciones" onclick="dame_tratamiento({{ $r->id }})"><i class="fas fa-edit"> </i></button></td>
                                        </tr>
                                        @endforeach
                                        @endif


                                    </tbody>
                                </table>

                            </div>

                        </div>
                    </div>

                    <div id="contenedor_tratamiento">
                        <div id="tratamiento" class="row">
                        </div>
                    </div>
                    <!--PAGINACIÓN-->
                    <!--Programar paginación para las evoluciones, ejemplo: Se muestran 8 y para ver las otras se usa la paginación-->

                    <div class="tab-pane fade " id="resumen_tto" role="tabpanel"
                        aria-labelledby="resumen_tto_tab">
                        <h6>Resumen de tratamiento y control Enfermería</h6>
                        <div class="col-sm-12 col-md-12 m-t-20">
                            {!! $resumen_recetas !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="id_tratamiento" id="id_tratamiento" value="">

<script>



    function dame_tratamiento(id){
        $('#id_tratamiento').val(id);
    }

</script>

<!-- FIN MODAL AGREGAR INSUMOS -->
