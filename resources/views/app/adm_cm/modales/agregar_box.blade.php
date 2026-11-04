<div id="agreg_box_ad" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="agreg_box_ad" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1">Agregar box o sala</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="modal-body">
                        <form>
                            <div class="form-row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Nombre del box o sala</label>
                                        <input type="text" name="numero_box" id="numero_box" placeholder="Ej: N° o Nombre" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="col-sm-12" id="contenedor_tpo_especializacion">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Tipo</label>
                                        <select class="form-control form-control-sm" name="tpo_especializacion" id="tpo_especializacion" onchange="mostrar_otro_tipo_box();">
                                            <option value="0">Seleccione</option>
                                            <option>Recepción y sala de espera</option>
                                            <option>Consulta general</option>
                                            <option>Baño o área de higiene</option>
                                            <option>Sala de procedimientos o curaciones</option>
                                            <option>Farmacia</option>
                                            <option>Bodega</option>
                                            <option>Peluquería o grooming</option>
                                            <option>Laboratorio clínico</option>
                                            <option>Sala de ecografía</option>
                                            <option>Sala de rayos X</option>
                                            <option>Sala de preparación preanestésica</option>
                                            <option>Pabellón quirúrgico</option>
                                            <option>Sala de esterilización</option>
                                            <option>Recuperación postanestésica</option>
                                            <option>Aislamiento o infectocontagiosos</option>
                                            <option>Sala de felinos</option>
                                            <option>UCI</option>
                                            <option>Tomografía o resonancia</option>
                                            <option>Salas de especialidad</option>
                                            <option>Sala de eutanasia o despedida</option>
                                            <option>Urgencia</option>
                                            <option>Otro</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="contenedor_tpo_especializacion_otro">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Especifique el tipo</label>
                                        <input type="text" name="tpo_especializacion_otro" id="tpo_especializacion_otro" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="floating-label-activo-sm">Ubicación</label>
                                        <select class="form-control form-control-sm" name="tpo_equip_servicio" id="tpo_equip_servicio">
                                            <option value="0">Seleccione</option>
                                            <option value="1">Piso 1</option>
                                            <option value="2">Piso 2</option>
                                            <option value="3">Piso 3</option>
                                            <option value="4">Piso 4</option>
                                            <option value="5">Piso 5</option>
                                            <option value="6">Piso 6</option>
                                            <option value="7">Piso 7</option>
                                            <option value="8">Piso 8</option>
                                            <option value="9">Piso 9</option>
                                            <option value="10">Piso 10</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

					<div class="col-sm-12 text-center">
                        <button type="button" onclick="guardar_box_servicio()" class="btn btn-info mx-auto">
                            <i class="feather icon-plus"></i> Agregar
                        </button>
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

    function agregar_box()  {
        $('#agreg_box_ad').modal('show');
    }

    function guardar_box_servicio() {
        window.boxesCentroVeterinario.guardar(false);
    }

    // Muestra el campo de texto solo cuando el tipo elegido es "Otro"
    function mostrar_otro_tipo_box() {
        let esOtro = $('#tpo_especializacion').val() === 'Otro';
        $('#contenedor_tpo_especializacion_otro').toggleClass('d-none', !esOtro);
        if (!esOtro) {
            $('#tpo_especializacion_otro').val('');
        }
    }

    // Igual que en agregar, pero para el modal de editar box o sala
    function mostrar_otro_tipo_box_editar() {
        let esOtro = $('#editar_tpo_especializacion').val() === 'Otro';
        $('#editar_contenedor_tpo_especializacion_otro').toggleClass('d-none', !esOtro);
        if (!esOtro) {
            $('#editar_tpo_especializacion_otro').val('');
        }
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

<script>
window.boxesCentroVeterinarioConfig = {
    guardar: @json(route('adm_cm.guardar_box_servicio')),
    editar: @json(route('adm_cm.editar_box_servicio')),
    cargar: @json(url('dame_box')),
    institucion: @json($institucion->id),
    tipoInstitucion: @json($tipo_institucion ?? 'institucion'),
    lugar: @json(count($scopeLugarIds ?? []) === 1 ? collect($scopeLugarIds)->first() : $institucion->id_lugar_atencion),
    token: @json(csrf_token())
};
</script>
<script src="{{ asset('js/boxes-centro-veterinario.js') }}?v={{ filemtime(public_path('js/boxes-centro-veterinario.js')) }}"></script>
