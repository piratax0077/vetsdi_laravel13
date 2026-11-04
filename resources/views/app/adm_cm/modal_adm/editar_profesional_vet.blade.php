<!--Modal Editar profesional veterinario: mismo formulario que Vincular profesional, con el nombre del profesional en el título-->
<div id="editar_profesional_vet" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editar_profesional_vet_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="editar_profesional_vet_titulo">Editar a: <span id="editar_prof_titulo_nombre"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editar_prof_id" value="">
                <p class="vincular-prof-ayuda">Modifique los datos del profesional. Los campos con <span class="vincular-prof-obligatorio">*</span> son obligatorios.</p>

                <div class="vincular-prof-bloque">
                    <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-user"></i>Datos personales</h6>
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_rut">RUT<span class="vincular-prof-obligatorio">*</span></label>
                                <input type="text" required class="form-control form-control-sm" name="editar_prof_rut" id="editar_prof_rut">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_sexo">Sexo<span class="vincular-prof-obligatorio">*</span></label>
                                <select name="editar_prof_sexo" id="editar_prof_sexo" class="form-control form-control-sm">
                                    <option value="0">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_nombre">Nombres<span class="vincular-prof-obligatorio">*</span></label>
                                <input type="text" required class="form-control form-control-sm" name="editar_prof_nombre" id="editar_prof_nombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_apellido_uno">Primer apellido<span class="vincular-prof-obligatorio">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="editar_prof_apellido_uno" id="editar_prof_apellido_uno">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_apellido_dos">Segundo apellido<span class="vincular-prof-obligatorio">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="editar_prof_apellido_dos" id="editar_prof_apellido_dos">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vincular-prof-bloque">
                    <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-phone"></i>Contacto</h6>
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_correo">Correo electr&oacute;nico<span class="vincular-prof-obligatorio">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="editar_prof_correo" id="editar_prof_correo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_telefono_uno">Tel&eacute;fono<span class="vincular-prof-obligatorio">*</span></label>
                                <input type="tel" class="form-control form-control-sm" name="editar_prof_telefono_uno" id="editar_prof_telefono_uno">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vincular-prof-bloque">
                    <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-briefcase"></i>Especialidad</h6>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_especialidad">Especialidad<span class="vincular-prof-obligatorio">*</span></label>
                                {{-- Mismas especialidades que Configuración > Agregar especialidad --}}
                                <select class="form-control form-control-sm" id="editar_prof_especialidad" name="editar_prof_especialidad">
                                    @include('app.adm_cm.partials.opciones_especialidades')
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vincular-prof-bloque">
                    <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-file-text"></i>Convenio</h6>
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_id_tipo_convenio_institucion">Tipo de convenio</label>
                                <select class="form-control form-control-sm" name="editar_prof_id_tipo_convenio_institucion" id="editar_prof_id_tipo_convenio_institucion" onchange="editar_prof_mostrar_montos();">
                                    <option value="">Seleccione</option>
                                    @if(isset($tipo_convenio))
                                        @foreach ($tipo_convenio as $tc)
                                            <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 editar_prof_div_fijo" style="display: none;">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_fijo">Cobro único</label>
                                <input type="number" class="form-control form-control-sm" name="editar_prof_fijo" id="editar_prof_fijo" value="">
                            </div>
                        </div>
                    </div>
                    <div class="form-row editar_prof_div_variable" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_atencion">Porcentaje por atenciones</label>
                                <input type="number" class="form-control form-control-sm" min="0" max="100" step="0.1" name="editar_prof_atencion" id="editar_prof_atencion" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_confirmacion_agenda">Cobro por confirmación de agenda</label>
                                <input type="number" class="form-control form-control-sm" min="0" step="1" name="editar_prof_confirmacion_agenda" id="editar_prof_confirmacion_agenda" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_ggcc">Cobro por GGCC</label>
                                <input type="number" class="form-control form-control-sm" min="0" step="1" name="editar_prof_ggcc" id="editar_prof_ggcc" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_box">Cobro arriendo de box</label>
                                <input type="number" class="form-control form-control-sm" min="0" step="1" name="editar_prof_box" id="editar_prof_box" value="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vincular-prof-acciones">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardar_edicion_profesional_vet();"><i class="feather icon-save mr-1"></i>Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#editar_prof_rut").rut({
            formatOn: 'keyup',
            minimumLength: 2,
            validateOn: 'change',
            useThousandsSeparator : false
        });
    });

    function editar_prof_mostrar_montos()
    {
        let valor = $('#editar_prof_id_tipo_convenio_institucion').val();
        $('.editar_prof_div_fijo, .editar_prof_div_variable').hide();
        if (valor == 1) {
            $('.editar_prof_div_fijo').show();
        } else if (valor == 2) {
            $('.editar_prof_div_variable').show();
        }
    }

    // Abre el modal con los datos que trae el botón de la fila
    function editar_profesional_vet(boton)
    {
        let d = $(boton).data();
        $('#editar_prof_id').val(d.id);
        $('#editar_prof_titulo_nombre').text((d.nombre + ' ' + d.apellidoUno).trim());
        $('#editar_prof_rut').val(d.rut);
        $('#editar_prof_sexo').val(d.sexo || '0');
        $('#editar_prof_nombre').val(d.nombre);
        $('#editar_prof_apellido_uno').val(d.apellidoUno);
        $('#editar_prof_apellido_dos').val(d.apellidoDos);
        $('#editar_prof_correo').val(d.correo);
        $('#editar_prof_telefono_uno').val(d.telefono);
        $('#editar_prof_especialidad').val('0');
        $('#editar_prof_id_tipo_convenio_institucion').val('');
        editar_prof_mostrar_montos();
        $('#editar_profesional_vet').modal('show');
    }

    function guardar_edicion_profesional_vet()
    {
        let faltan = [];
        if ($('#editar_prof_rut').val() == '') faltan.push('RUT');
        if ($('#editar_prof_sexo').val() == '0') faltan.push('Sexo');
        if ($('#editar_prof_nombre').val() == '') faltan.push('Nombres');
        if ($('#editar_prof_apellido_uno').val() == '') faltan.push('Primer apellido');
        if ($('#editar_prof_apellido_dos').val() == '') faltan.push('Segundo apellido');
        if ($('#editar_prof_correo').val() == '') faltan.push('Correo electrónico');
        if ($('#editar_prof_telefono_uno').val() == '') faltan.push('Teléfono');
        if (['', '0', null].indexOf($('#editar_prof_especialidad').val()) !== -1) faltan.push('Especialidad');

        if (faltan.length) {
            alert('Faltan campos obligatorios: ' + faltan.join(', '));
            return;
        }
        $('#editar_profesional_vet').modal('hide');
    }
</script>
