<div id="agregar_salas" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="agregar_salas" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white d-inline mt-1">Agregar Salas a servicios</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="modal-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Servicio</label>
                                        <select class="form-control form-control-sm" name="servicio_salas" id="servicio_salas" >
                                            <option value="0">Seleccione</option>
                                            @foreach ($servicios_internos as $servicio)
                                                <option value="{{ $servicio->id }}">{{ $servicio->nombre_servicio }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Cantidad de Salas</label>
                                        <input type="number" name="cantidad_salas" id="cantidad_salas" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Camillas por sala</label>
                                        <input type="number" name="camillas_salas" id="camillas_salas" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group fill">
                                        <label class="floating-label-activo-sm">Observaciones</label>
                                        <textarea class="form-control caja-texto form-control-sm" rows="1"  onfocus="this.rows=4" onblur="this.rows=1;" name="observaciones_salas_servicio" id="observaciones_salas_servicio"></textarea>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <div class="col-sm-12">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    $(document).ready(function() {

        $('#equip_ad').select2();
        // $('#tpo_cub').select2();
        $('#equip_ed').select2();
    });

    function agregar_sala()  {
        $('#agregar_salas').modal('show');
    }



    function dame_especializacion_box(){
        let tipo_box = $('#tpo_box_servicio').val();
        console.log(tipo_box);
        if(tipo_box == 'Especializado'){
            $('#contenedor_tpo_especializacion').removeClass('d-none');
        }else{
            $('#contenedor_tpo_especializacion').addClass('d-none');
        }
    }

    function editar_dame_especializacion_box(){
        let tipo_box = $('#editar_tpo_box_servicio').val();
        console.log(tipo_box);
        if(tipo_box == 'Especializado'){
            $('#editar_contenedor_tpo_especializacion').removeClass('d-none');
        }else{
            $('#editar_contenedor_tpo_especializacion').addClass('d-none');
        }
    }
</script>
