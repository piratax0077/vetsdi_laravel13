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
                    <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-map-pin"></i>Lugar y cargo</h6>
                    <div class="form-row">
                        <div class="col-md-12">
                            <div class="form-group fill">
                                @if($institucion->sucursales == 0)
                                    <label class="floating-label-activo-sm">Casa matriz</label>
                                    <input type="text" class="form-control form-control-sm" value="{{ $institucion->LugarAtencion()->first()->nombre }}" readonly>
                                    <input type="hidden" name="editar_prof_id_lugar_atencion" id="editar_prof_id_lugar_atencion" value="{{ $institucion->LugarAtencion()->first()->id }}">
                                @else
                                    <label class="floating-label-activo-sm" for="editar_prof_id_lugar_atencion">Sucursal</label>
                                    <select class="form-control form-control-sm" name="editar_prof_id_lugar_atencion" id="editar_prof_id_lugar_atencion">
                                        <option>Seleccione una opción</option>
                                        <option value="">Sucursal</option>
                                        <option value="1">Sucursal Demo</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm" for="editar_prof_cargo">En el cargo de</label>
                                <select class="form-control form-control-sm" name="editar_prof_cargo" id="editar_prof_cargo">
                                    <option value="0">Seleccione una opción</option>
                                    <option value="1">Profesional</option>
                                    <option value="2">Jefe de servicio</option>
                                    <option value="3">Subjefe de servicio</option>
                                    <option value="4">Director médico</option>
                                    <option value="5">TENS</option>
                                    <option value="6">TONS</option>
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
                    <button type="button" class="btn btn-danger" onclick="$(this).closest('.modal').modal('hide');">Cerrar</button>
                    <button type="button" class="btn btn-info" onclick="guardar_edicion_profesional_vet();"><i class="feather icon-save mr-1"></i>Guardar</button>
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
        $('#editar_prof_especialidad').val(d.especialidad || '0');
        $('#editar_prof_id_tipo_convenio_institucion').val('');
        editar_prof_mostrar_montos();
        $('#editar_profesional_vet').modal('show');
    }

    function guardar_edicion_profesional_vet()
    {
        let faltan = [];
        if (['', '0', null].indexOf($('#editar_prof_especialidad').val()) !== -1) faltan.push('Especialidad');

        if (faltan.length) {
            alert('Faltan campos obligatorios: ' + faltan.join(', '));
            return;
        }
        const modal = $('#editar_profesional_vet');
        const boton = modal.find('button[onclick="guardar_edicion_profesional_vet();"]');
        if (boton.prop('disabled')) return;
        boton.prop('disabled', true);
        $.ajax({
            url: @js(route('adm_cm.profesional.especialidad_vet', ['id' => 'PROF_ID'])).replace('PROF_ID', $('#editar_prof_id').val()),
            type: 'PATCH',
            headers: {Accept: 'application/json'},
            data: {
                _token: CSRF_TOKEN,
                id_institucion: @json($institucion->id),
                tipo_institucion: @json($tipo_institucion),
                especialidad: $('#editar_prof_especialidad').val(),
                rut: $('#editar_prof_rut').val(),
                sexo: $('#editar_prof_sexo').val() === '0' ? null : $('#editar_prof_sexo').val(),
                nombre: $('#editar_prof_nombre').val(),
                apellido_uno: $('#editar_prof_apellido_uno').val(),
                apellido_dos: $('#editar_prof_apellido_dos').val(),
                email: $('#editar_prof_correo').val(),
                telefono_uno: $('#editar_prof_telefono_uno').val()
            }
        }).done(function () {
            modal.modal('hide');
            window.location.reload();
        }).fail(function (xhr) {
            const respuesta = xhr.responseJSON || {};
            swal({title: 'No se pudo guardar la especialidad', text: Object.values(respuesta.errors || {}).flat().join('\n') || respuesta.message || 'Intente nuevamente.', icon: 'error'});
        }).always(function () {
            boton.prop('disabled', false);
        });
    }
</script>
