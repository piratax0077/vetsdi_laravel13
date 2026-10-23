<!--Modal Vincular profesional: búsqueda por RUT, profesional encontrado o formulario para uno nuevo-->
<div id="asociar_profesional_cm" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="asociar_profesional_cm_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="asociar_profesional_cm_titulo">Vincular profesional</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                {{--  PASO 1: BÚSQUEDA DEL PROFESIONAL POR RUT  --}}
                <div id="div_agregar_profesional_busqueda">
                    <p class="vincular-prof-ayuda">Ingrese el RUT del profesional y elija la sucursal donde atenderá. Si no está registrado, podrá completar sus datos para enviarle la invitación.</p>

                    <div class="vincular-prof-bloque">
                        <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-search"></i>Buscar profesional</h6>
                        <div class="vincular-prof-busqueda">
                            <div class="form-group fill vincular-prof-busqueda-campo">
                                <label class="floating-label-activo-sm" for="agregar_profesional_int_rut">RUT</label>
                                <input type="text" class="form-control form-control-sm" name="agregar_profesional_int_rut" id="agregar_profesional_int_rut" placeholder="Ej: 12345678-9">
                            </div>
                            <button type="button" class="btn btn-info" id="agregar_profesional_btn_buscar_rut" onclick="buscar_profesional();"><i class="feather icon-search mr-1"></i>Buscar</button>
                        </div>
                    </div>

                    <div class="vincular-prof-bloque">
                        <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-map-pin"></i>Lugar y cargo</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group fill">
                                    @if($institucion->sucursales == 0)
                                        <label class="floating-label-activo-sm">Casa matriz</label>
                                        <input type="text" class="form-control form-control-sm" value="{{ $institucion->LugarAtencion()->first()->nombre }}" readonly>
                                        <input type="hidden" name="agregar_profesional_int_id_lugar_atencion" id="agregar_profesional_int_id_lugar_atencion" value="{{ $institucion->LugarAtencion()->first()->id }}">
                                    @else
                                        <label class="floating-label-activo-sm" for="agregar_profesional_int_id_lugar_atencion">Sucursal</label>
                                        <select class="form-control form-control-sm" name="agregar_profesional_int_id_lugar_atencion" id="agregar_profesional_int_id_lugar_atencion">
                                            <option>Seleccione una opción</option>
                                            <option value="">Sucursal</option>
                                            <option value="1">Sucursal Demo</option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_cargo_int">En el cargo de</label>
                                    <select class="form-control form-control-sm" name="agregar_profesional_cargo_int" id="agregar_profesional_cargo_int" onchange="registrar_nuevo_servicio(event)">
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
                            <div class="col-md-6 d-none" id="div_nuevo_servicio">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_servicio_int">Nombre del servicio</label>
                                    <input type="text" class="form-control form-control-sm" name="agregar_profesional_servicio_int" id="agregar_profesional_servicio_int">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vincular-prof-acciones">
                        <button type="button" class="btn btn-danger" onclick="$(this).closest('.modal').modal('hide');">Cerrar</button>
                        <button type="button" class="btn btn-info"><i class="feather icon-save mr-1"></i>Guardar</button>
                    </div>
                </div>

                {{--  PASO 2A: PROFESIONAL YA REGISTRADO  --}}
                <div id="div_agregar_profesional_ver_info_prof" style="display:none;">
                    <input type="hidden" name="agregar_profesional_ver_nombre_profesional" id="agregar_profesional_ver_nombre_profesional">
                    <input type="hidden" name="agregar_profesional_ver_telefono" id="agregar_profesional_ver_telefono">
                    <input type="hidden" name="agregar_profesional_ver_email" id="agregar_profesional_ver_email">
                    <input type="hidden" name="agregar_profesional_id_profesional" id="agregar_profesional_id_profesional">

                    <!--Ficha del profesional encontrado-->
                    <div class="vincular-prof-ficha">
                        <span class="vincular-prof-ficha-icono"><i class="feather icon-user-check"></i></span>
                        <div class="vincular-prof-ficha-datos">
                            <span class="vincular-prof-ficha-etiqueta">Profesional encontrado</span>
                            <div class="vincular-prof-ficha-nombre" name="agregar_profesional_texto_ver_nombre_profesional" id="agregar_profesional_texto_ver_nombre_profesional"></div>
                            <div class="vincular-prof-ficha-contacto">
                                <span><i class="feather icon-phone"></i><span name="agregar_profesional_texto_ver_telefono" id="agregar_profesional_texto_ver_telefono"></span></span>
                                <span><i class="feather icon-mail"></i><span name="agregar_profesional_texto_ver_email" id="agregar_profesional_texto_ver_email"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="vincular-prof-bloque">
                        <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-file-text"></i>Convenio</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_id_tipo_convenio_institucion">Tipo de convenio</label>
                                    <select class="form-control form-control-sm" name="agregar_profesional_id_tipo_convenio_institucion" id="agregar_profesional_id_tipo_convenio_institucion" onchange="mostar_div_montos_tipo_convenio('agregar_profesional_id_tipo_convenio_institucion');">
                                        <option value="">Seleccione</option>
                                        @if(isset($tipo_convenio))
                                            @foreach ($tipo_convenio as $tc )
                                                <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 div_fijo" style="display: none;">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_fijo">Cobro único</label>
                                    <input type="number" class="form-control form-control-sm" name="agregar_profesional_fijo" id="agregar_profesional_fijo" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row div_variable" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_atencion">Porcentaje por atenciones</label>
                                    <input type="number" class="form-control form-control-sm" min="0" max="100" step="0.1" name="agregar_profesional_atencion" id="agregar_profesional_atencion" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_confirmacion_agenda">Cobro por confirmación de agenda</label>
                                    <input type="number" class="form-control form-control-sm" min="0" step="1" name="agregar_profesional_confirmacion_agenda" id="agregar_profesional_confirmacion_agenda" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_ggcc">Cobro por GGCC</label>
                                    <input type="number" class="form-control form-control-sm" min="0" step="1" name="agregar_profesional_ggcc" id="agregar_profesional_ggcc" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_box">Cobro arriendo de box</label>
                                    <input type="number" class="form-control form-control-sm" min="0" step="1" name="agregar_profesional_box" id="agregar_profesional_box" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vincular-prof-acciones">
                        <button type="button" class="btn btn-outline-secondary" onclick="regresar_a_busqueda();"><i class="feather icon-arrow-left mr-1"></i>Regresar a búsqueda</button>
                        <button type="button" class="btn btn-success" onclick="asociar_profesional_existente();"><i class="feather icon-send mr-1"></i>Enviar invitación</button>
                    </div>
                </div>

                {{--  PASO 2B: PROFESIONAL NUEVO  --}}
                <div id="div_agregar_profesional_formulario_nuevo_prof" style="display:none;">
                    <p class="vincular-prof-ayuda">El RUT no está registrado. Complete los datos del profesional para enviarle la invitación. Los campos con <span class="vincular-prof-obligatorio">*</span> son obligatorios.</p>

                    <div class="vincular-prof-bloque">
                        <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-user"></i>Datos personales</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_rut">RUT<span class="vincular-prof-obligatorio">*</span></label>
                                    <input type="text" required class="form-control form-control-sm" name="agregar_profesional_nuevo_rut" id="agregar_profesional_nuevo_rut">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_sexo">Sexo<span class="vincular-prof-obligatorio">*</span></label>
                                    <select name="agregar_profesional_sexo" id="agregar_profesional_sexo" class="form-control form-control-sm">
                                        <option value="0">Seleccione</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_nombre">Nombres<span class="vincular-prof-obligatorio">*</span></label>
                                    <input type="text" required class="form-control form-control-sm" name="agregar_profesional_nuevo_nombre" id="agregar_profesional_nuevo_nombre">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_apellido_uno">Primer apellido<span class="vincular-prof-obligatorio">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="agregar_profesional_nuevo_apellido_uno" id="agregar_profesional_nuevo_apellido_uno">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_apellido_dos">Segundo apellido<span class="vincular-prof-obligatorio">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="agregar_profesional_nuevo_apellido_dos" id="agregar_profesional_nuevo_apellido_dos">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vincular-prof-bloque">
                        <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-phone"></i>Contacto</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_correo">Correo electr&oacute;nico<span class="vincular-prof-obligatorio">*</span></label>
                                    <input type="text" class="form-control form-control-sm" onblur="validar_email_agenda()" name="agregar_profesional_nuevo_correo" id="agregar_profesional_nuevo_correo">
                                    <span id="mensaje_email_reserva" style="display:none"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_telefono_uno">Tel&eacute;fono<span class="vincular-prof-obligatorio">*</span></label>
                                    <input type="tel" class="form-control form-control-sm" name="agregar_profesional_nuevo_telefono_uno" id="agregar_profesional_nuevo_telefono_uno">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vincular-prof-bloque">
                        <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-briefcase"></i>Profesi&oacute;n</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_profesion">Profesi&oacute;n<span class="vincular-prof-obligatorio">*</span></label>
                                    <select class="form-control form-control-sm" id="agregar_profesional_nuevo_profesion" name="agregar_profesional_nuevo_profesion" onchange="buscar_tipo_especialidad()">
                                        <option selected value="0">Seleccione</option>
                                        @if (isset($especialidad))
                                            @foreach ($especialidad as $esp)
                                                <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_especialidad">Especialidad<span class="vincular-prof-obligatorio">*</span></label>
                                    <select class="form-control form-control-sm" id="agregar_profesional_nuevo_especialidad" name="agregar_profesional_nuevo_especialidad" onchange="buscar_sub_tipo_especialidad()">
                                        <option selected value="0">Seleccione</option>
                                        <option>-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_sub_tipo_especialidad">Tipo de especialidad<span class="vincular-prof-obligatorio">*</span></label>
                                    <select class="form-control form-control-sm" id="agregar_profesional_nuevo_sub_tipo_especialidad" name="agregar_profesional_nuevo_sub_tipo_especialidad">
                                        <option selected value="0">Seleccione</option>
                                        <option>-</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vincular-prof-bloque">
                        <h6 class="vincular-prof-bloque-titulo"><i class="feather icon-file-text"></i>Convenio</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_id_tipo_convenio_institucion">Tipo de convenio</label>
                                    <select class="form-control form-control-sm" name="agregar_profesional_nuevo_id_tipo_convenio_institucion" id="agregar_profesional_nuevo_id_tipo_convenio_institucion" onchange="mostar_div_montos_tipo_convenio('agregar_profesional_nuevo_id_tipo_convenio_institucion');">
                                        <option value="">Seleccione</option>
                                        @if(isset($tipo_convenio))
                                            @foreach ($tipo_convenio as $tc )
                                                <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 div_fijo" style="display: none;">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_fijo">Cobro único</label>
                                    <input type="number" class="form-control form-control-sm" name="agregar_profesional_nuevo_fijo" id="agregar_profesional_nuevo_fijo" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row div_variable" style="display: none;">
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_atencion">Porcentaje por atenciones</label>
                                    <input type="number" class="form-control form-control-sm" min="0" max="100" step="0.1" name="agregar_profesional_nuevo_atencion" id="agregar_profesional_nuevo_atencion" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_confirmacion_agenda">Cobro por confirmación de agenda</label>
                                    <input type="number" class="form-control form-control-sm" min="0" step="1" name="agregar_profesional_nuevo_confirmacion_agenda" id="agregar_profesional_nuevo_confirmacion_agenda" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_ggcc">Cobro por GGCC</label>
                                    <input type="number" class="form-control form-control-sm" min="0" step="1" name="agregar_profesional_nuevo_ggcc" id="agregar_profesional_nuevo_ggcc" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm" for="agregar_profesional_nuevo_box">Cobro arriendo de box</label>
                                    <input type="number" class="form-control form-control-sm" min="0" step="1" name="agregar_profesional_nuevo_box" id="agregar_profesional_nuevo_box" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vincular-prof-acciones">
                        <button type="button" class="btn btn-outline-secondary" onclick="regresar_a_busqueda();"><i class="feather icon-arrow-left mr-1"></i>Regresar a búsqueda</button>
                        <button type="button" class="btn btn-success" onclick="asociar_nuevo_profesional();"><i class="feather icon-send mr-1"></i>Enviar invitación</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        {{--  FORMATEO DE RUT AGREGAR NUEVO PROFESIONAL   --}}
        $("#agregar_profesional_int_rut").rut({
            formatOn: 'keyup',
            minimumLength: 2,
            validateOn: 'change',
            useThousandsSeparator : false
        });
        $("#agregar_profesional_nuevo_rut").rut({
            formatOn: 'keyup',
            minimumLength: 2,
            validateOn: 'change',
            useThousandsSeparator : false
        });

    });

    function mostar_div_montos_tipo_convenio(select)
    {
        console.log('mostar_div_montos_tipo_convenio');
        var valor = $('#'+select).val();
        $('.div_fijo').hide();
        $('.div_variable').hide();
        console.log(valor);
        if(valor == 1)
        {
            $('.div_fijo').show();
            $('.div_variable').hide();
            $('#agregar_profesional_fijo').val(0);

        }
        else if(valor == 2)
        {
            $('.div_fijo').hide();
            $('.div_variable').show();
            $('#agregar_profesional_atencion').val(0);
            $('#agregar_profesional_confirmacion_agenda').val(0);
            $('#agregar_profesional_ggcc').val(0);
            $('#agregar_profesional_box').val(0);
        }
        else
        {
            $('#div_fijo').hide();
            $('#div_variable').hide();
        }
    }

    function asociar_profesional() {
        $('#agregar_profesional_btn_buscar_rut').removeAttr('disabled');
        $('#div_agregar_profesional_busqueda').show();
        $('#div_agregar_profesional_ver_info_prof').hide();
        $('#div_agregar_profesional_formulario_nuevo_prof').hide();
        $('#asociar_profesional_cm').modal('show');
    }

    function regresar_a_busqueda()
    {
        $('#agregar_profesional_btn_buscar_rut').removeAttr('disabled');
        $('#agregar_profesional_int_rut').val('');

        $('#div_agregar_profesional_busqueda').show();
        $('#div_agregar_profesional_ver_info_prof').hide();
        $('#div_agregar_profesional_formulario_nuevo_prof').hide();

        $('#agregar_profesional_id_profesional').val('');
        $('#agregar_profesional_texto_ver_nombre_profesional').html('');
        $('#agregar_profesional_texto_ver_telefono').html('');
        $('#agregar_profesional_texto_ver_email').html('');
        $('#agregar_profesional_ver_nombre_profesional').val('');
        $('#agregar_profesional_ver_telefono').val('');
        $('#agregar_profesional_ver_email').val('');

        $('#agregar_profesional_nuevo_nombre').val('');
        $('#agregar_profesional_nuevo_apellido_p').val('');
        $('#agregar_profesional_nuevo_apellido_m').val('');
        $('#agregar_profesional_nuevo_telefono').val('');
        $('#agregar_profesional_nuevo_email').val('');
    }

    {{--  BUSQUEDA EN EL MODAL DE ASOCIAR NUEVO PROFESIONAL  --}}
    function buscar_profesional()
    {

        let id_lugar_atencion = $('#agregar_profesional_int_id_lugar_atencion').val();

        if(id_lugar_atencion == '')
        {
            swal({
                title: "Debe seleccionar una sucursal",
                icon: "error",
            });
            return false;
        }

        $('#agregar_profesional_btn_buscar_rut').attr('disabled', 'disabled');
        var rut = $('#agregar_profesional_int_rut').val();
        if(rut == ''){
            swal({
                title: "Debe ingresar un RUT",
                icon: "error",
            });
            return false;
        }
        if(!$.validateRut(rut))
        {
            swal({
                title: "Debe ingresar un RUT valido",
                icon: "error",
            });
            return false;
        }

        {{--  busqueda  --}}
        let profesional_inter = $('#agregar_profesional_nuevo_profesional_inter');
        profesional_inter.find('option').remove();

        let url = "{{ route('profesional.buscador') }}";
        $.ajax({
            url: url,
            type: "get",
            data: {
                rut: rut
            },
        })
        .done(function(data) {
            console.log(data);
            if (data.estado == 1)
            {
                /** encontrado */
                $('#agregar_profesional_texto_ver_nombre_profesional').html(data.registros[0].profesionales_nombre+' '+data.registros[0].profesionales_apellido_uno+' '+data.registros[0].profesionales_apellido_dos);
                $('#agregar_profesional_texto_ver_telefono').html(data.registros[0].profesional_telefono_uno);
                $('#agregar_profesional_texto_ver_email').html(data.registros[0].profesional_email);
                $('#agregar_profesional_ver_nombre_profesional').val(data.registros[0].profesionales_nombre+' '+data.registros[0].profesionales_apellido_uno+' '+data.registros[0].profesionales_apellido_dos);
                $('#agregar_profesional_ver_telefono').val(data.registros[0].profesional_telefono_uno);
                $('#agregar_profesional_ver_email').val(data.registros[0].profesional_email);

                $('#agregar_profesional_id_profesional').val(data.registros[0].profesionales_id);

                $('#div_agregar_profesional_busqueda').hide();
                $('#div_agregar_profesional_ver_info_prof').show();
                $('#div_agregar_profesional_formulario_nuevo_prof').hide();
            }
            else
            {
                /** no encontrado */
                /** REALIZAR BUSQUEDA TABLA DE INVITACIONES */
                let url = "{{ route('invitaciones.buscar.info') }}";
                $.ajax({
                    url: url,
                    type: "get",
                    data: {
                        rut: rut
                    },
                })
                .done(function(data3) {
                    if (data3.estado == 1)
                    {
                        /** encontrado */
                        $('#agregar_profesional_nuevo_rut').val(rut);
                        $('#agregar_profesional_nuevo_nombre').val(data3.registro.nombre);
                        $('#agregar_profesional_nuevo_apellido_uno').val(data3.registro.apellido_uno);
                        $('#agregar_profesional_nuevo_apellido_dos').val(data3.registro.apellido_dos);
                        $('#agregar_profesional_nuevo_correo').val(data3.registro.email);
                        $('#agregar_profesional_nuevo_telefono_uno').val(data3.registro.telefono);
                        $('#agregar_profesional_nuevo_profesion').val(data3.registro.id_especialidad);

                        buscar_tipo_especialidad(data3.registro.id_tipo_especialidad);
                        // $('#agregar_profesional_nuevo_especialidad').val(data3.registro.id_tipo_especialidad);
                        setTimeout(function(){
                            buscar_sub_tipo_especialidad(data3.registro.id_sub_tipo_especialidad);
                        }, 1000);
                        // $('#agregar_profesional_nuevo_sub_tipo_especialidad').val(data3.registro.id_sub_tipo_especialidad);

                        $('#agregar_profesional_nuevo_id_tipo_convenio_institucion').val(data3.registro.convenio.id_tipo_convenio_institucion);
                        mostar_div_montos_tipo_convenio('agregar_profesional_nuevo_id_tipo_convenio_institucion');
                        $('#agregar_profesional_nuevo_fijo').val(data3.registro.convenio.fijo);
                        $('#agregar_profesional_nuevo_atencion').val(data3.registro.convenio.atencion);
                        $('#agregar_profesional_nuevo_confirmacion_agenda').val(data3.registro.convenio.confirmacion_agenda);
                        $('#agregar_profesional_nuevo_ggcc').val(data3.registro.convenio.ggcc);
                        $('#agregar_profesional_nuevo_box').val(data3.registro.convenio.box);

                        $('#div_agregar_profesional_busqueda').hide();
                        $('#div_agregar_profesional_ver_info_prof').hide();
                        $('#div_agregar_profesional_formulario_nuevo_prof').show();

                    }
                    else
                    {
                        $.ajax({
                            url: url,
                            type: "get",
                            data: {
                                rut: rut
                            },
                        })
                        .done(function(data2) {
                            if (data2.estado == 1)
                            {
                                /** encontrado */
                                $('#agregar_profesional_nuevo_rut').val(rut);
                                $('#agregar_profesional_nuevo_nombre').val( data2.registros.nombre);
                                $('#agregar_profesional_nuevo_apellido_uno').val( data2.registros.appaterno );
                                $('#agregar_profesional_nuevo_apellido_dos').val( data2.registros.apmaterno );
                                $('#agregar_profesional_nuevo_correo').val('');
                                $('#agregar_profesional_nuevo_telefono_uno').val('');

                                $('#agregar_profesional_nuevo_profesion').val('');
                                $('#agregar_profesional_nuevo_especialidad').val('');
                                $('#agregar_profesional_nuevo_sub_tipo_especialidad').val('');

                                $('#agregar_profesional_nuevo_id_tipo_convenio_institucion').val('');
                                $('#agregar_profesional_nuevo_fijo').val('');
                                $('#agregar_profesional_nuevo_atencion').val('');
                                $('#agregar_profesional_nuevo_confirmacion_agenda').val('');
                                $('#agregar_profesional_nuevo_ggcc').val('');
                                $('#agregar_profesional_nuevo_box').val('');

                                $('#div_agregar_profesional_busqueda').hide();
                                $('#div_agregar_profesional_ver_info_prof').hide();
                                $('#div_agregar_profesional_formulario_nuevo_prof').show();
                            }
                            else
                            {
                                /** no encontrado */
                                $('#agregar_profesional_nuevo_rut').val( rut );
                                $('#agregar_profesional_nuevo_nombre').val( '' );
                                $('#agregar_profesional_nuevo_apellido_uno').val( '' );
                                $('#agregar_profesional_nuevo_apellido_dos').val( '' );
                                $('#agregar_profesional_nuevo_correo').val('');
                                $('#agregar_profesional_nuevo_telefono_uno').val('');

                                $('#agregar_profesional_nuevo_profesion').val('');
                                $('#agregar_profesional_nuevo_especialidad').val('');
                                $('#agregar_profesional_nuevo_sub_tipo_especialidad').val('');

                                $('#agregar_profesional_nuevo_id_tipo_convenio_institucion').val('');
                                $('#agregar_profesional_nuevo_fijo').val('');
                                $('#agregar_profesional_nuevo_atencion').val('');
                                $('#agregar_profesional_nuevo_confirmacion_agenda').val('');
                                $('#agregar_profesional_nuevo_ggcc').val('');
                                $('#agregar_profesional_nuevo_box').val('');


                                $('#div_agregar_profesional_busqueda').hide();
                                $('#div_agregar_profesional_ver_info_prof').hide();
                                $('#div_agregar_profesional_formulario_nuevo_prof').show();

                            }

                        })
                        .fail(function(jqXHR, ajaxOptions, thrownError) {
                            console.log(jqXHR, ajaxOptions, thrownError)
                        });
                    }

                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function asociar_profesional_existente()
    {
        let id_lugar_atencion = $('#agregar_profesional_int_id_lugar_atencion').val();
        let id_profesional = $('#agregar_profesional_id_profesional').val();
        let id_tipo_convenio_institucion = $('#agregar_profesional_id_tipo_convenio_institucion').val();
        let fijo = $('#agregar_profesional_fijo').val();
        let atencion = $('#agregar_profesional_atencion').val();
        let confirmacion_agenda = $('#agregar_profesional_confirmacion_agenda').val();
        let ggcc = $('#agregar_profesional_ggcc').val();
        let box = $('#agregar_profesional_box').val();

        let url = "{{ route('adm_cm.asociar_profesional_existente')}}";

        $.ajax({
            url: url,
            type: "post",
            data: {
                _token: CSRF_TOKEN,
                id_lugar_atencion : id_lugar_atencion,
                id_profesional : id_profesional,
                id_tipo_convenio_institucion : id_tipo_convenio_institucion,
                fijo : fijo,
                atencion : atencion,
                confirmacion_agenda : confirmacion_agenda,
                ggcc : ggcc,
                box : box,
                observacion : '',
            },
        })
        .done(function(data) {
            console.log(data);
            if (data.estado == 1)
            {

                swal({
                    title: "Invitación al Profesional Realizada con Exito.",
                    text: "Profesional pendiente por confirmar.",
                    icon: "success",
                });

                $('#asociar_profesional_cm').modal('hide');

                $('#agregar_profesional_btn_buscar_rut').removeAttr('disabled');
                $('#agregar_profesional_int_rut').val('');

                $('#div_agregar_profesional_busqueda').show();
                $('#div_agregar_profesional_ver_info_prof').hide();
                $('#div_agregar_profesional_formulario_nuevo_prof').hide();

                $('#agregar_profesional_id_profesional').val('');
                $('#agregar_profesional_texto_ver_nombre_profesional').html('');
                $('#agregar_profesional_texto_ver_telefono').html('');
                $('#agregar_profesional_texto_ver_email').html('');
                $('#agregar_profesional_ver_nombre_profesional').val('');
                $('#agregar_profesional_ver_telefono').val('');
                $('#agregar_profesional_ver_email').val('');

                $('#agregar_profesional_nuevo_nombre').val('');
                $('#agregar_profesional_nuevo_apellido_p').val('');
                $('#agregar_profesional_nuevo_apellido_m').val('');
                $('#agregar_profesional_nuevo_telefono').val('');
                $('#agregar_profesional_nuevo_email').val('');
            }
            else
            {
                swal({
                    title: "Invitación al Profesional Fallida.",
                    text: "Profesional pendiente por confirmar.",
                    icon: "error",
                });
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });

    }

    function asociar_nuevo_profesional()
    {
        let id_lugar_atencion = $('#agregar_profesional_int_id_lugar_atencion').val();

        let sexo = $('#agregar_profesional_sexo').val();
        let rut = $('#agregar_profesional_nuevo_rut').val();
        let nombre = $('#agregar_profesional_nuevo_nombre').val();
        let apellido_uno = $('#agregar_profesional_nuevo_apellido_uno').val();
        let apellido_dos = $('#agregar_profesional_nuevo_apellido_dos').val();
        let correo = $('#agregar_profesional_nuevo_correo').val();
        let telefono_uno = $('#agregar_profesional_nuevo_telefono_uno').val();

        let profesion = $('#agregar_profesional_nuevo_profesion').val();
        let especialidad = $('#agregar_profesional_nuevo_especialidad').val();
        let sub_tipo_especialidad = $('#agregar_profesional_nuevo_sub_tipo_especialidad').val();

        let id_tipo_convenio_institucion = $('#agregar_profesional_nuevo_id_tipo_convenio_institucion').val();
        let fijo = $('#agregar_profesional_nuevo_fijo').val();
        let atencion = $('#agregar_profesional_nuevo_atencion').val();
        let confirmacion_agenda = $('#agregar_profesional_nuevo_confirmacion_agenda').val();
        let ggcc = $('#agregar_profesional_nuevo_ggcc').val();
        let box = $('#agregar_profesional_nuevo_box').val();

        let valido = 1;
        let mensaje = '';

        if (id_lugar_atencion == '') {
            valido = 0;
            mensaje += '<li>Lugar de atención</li>';
        }
        if(sexo == 0){
            valido = 0;
            mensaje += '<li>Sexo</li>';
        }
        if (rut == '') {
            valido = 0;
            mensaje += '<li>RUT</li>';
        }
        if (nombre == '') {
            valido = 0;
            mensaje += '<li>Nombre</li>';
        }
        if (apellido_uno == '') {
            valido = 0;
            mensaje += '<li>Primer apellido</li>';
        }
        if (apellido_dos == '') {
            valido = 0;
            mensaje += '<li>Segundo apellido</li>';
        }
        if (correo == '') {
            valido = 0;
            mensaje += '<li>Correo electrónico</li>';
        }
        if (telefono_uno == '') {
            valido = 0;
            mensaje += '<li>Teléfono</li>';
        }
        if (profesion == '') {
            valido = 0;
            mensaje += '<li>Profesión</li>';
        }
        if (especialidad == '') {
            valido = 0;
            mensaje += '<li>Especialidad</li>';
        }
        if (sub_tipo_especialidad == '') {
            valido = 0;
            mensaje += '<li>Sub tipo especialidad</li>';
        }
        if (id_tipo_convenio_institucion == '') {
            valido = 0;
            mensaje += '<li>Tipo de convenio con institución</li>';
        }
        if (fijo == '') {
            valido = 0;
            mensaje += '<li>Valor fijo</li>';
        }
        // if (atencion == '') {
        //     valido = 0;
        //     mensaje += '<li>Valor atención</li>';
        // }
        // if (confirmacion_agenda == '') {
        //     valido = 0;
        //     mensaje += '<li>Confirmación agenda</li>';
        // }
        // if (ggcc == '') {
        //     valido = 0;
        //     mensaje += '<li>GGCC</li>';
        // }
        // if (box == '') {
        //     valido = 0;
        //     mensaje += '<li>Box</li>';
        // }

        if (valido == 0) {
            swal({
                title: 'Faltan campos por completar',
                content:{
                    element: "ul",
                    attributes: {
                        innerHTML: mensaje
                    }
                },
                icon: 'warning'
            });
            return;
        }


        let url = "{{ route('adm_cm.asociar_profesional_nuevo')}}";

        $.ajax({
            url: url,
            type: "post",
            data: {
                _token: CSRF_TOKEN,
                id_lugar_atencion : id_lugar_atencion,
                sexo: sexo,
                rut : rut,
                nombre : nombre,
                apellido_uno : apellido_uno,
                apellido_dos : apellido_dos,
                correo : correo,
                telefono_uno : telefono_uno,
                profesion : profesion,
                especialidad : especialidad,
                sub_tipo_especialidad : sub_tipo_especialidad,
                id_tipo_convenio_institucion : id_tipo_convenio_institucion,
                fijo : fijo,
                atencion : atencion,
                confirmacion_agenda : confirmacion_agenda,
                ggcc : ggcc,
                box : box,
                observacion : '',
            },
        })
        .done(function(data) {
            console.log(data);
            if (data.estado == 1)
            {

                swal({
                    title: "Invitación al Profesional Realizada con Exito.",
                    text: "Profesional pendiente por confirmar.",
                    icon: "success",
                });

                $('#asociar_profesional_cm').modal('hide');

                $('#agregar_profesional_btn_buscar_rut').removeAttr('disabled');
                $('#agregar_profesional_int_rut').val('');

                $('#div_agregar_profesional_busqueda').show();
                $('#div_agregar_profesional_ver_info_prof').hide();
                $('#div_agregar_profesional_formulario_nuevo_prof').hide();

                // $('#agregar_profesional_int_id_lugar_atencion').val('');
                $('#agregar_profesional_nuevo_rut').val('');
                $('#agregar_profesional_nuevo_nombre').val('');
                $('#agregar_profesional_nuevo_apellido_uno').val('');
                $('#agregar_profesional_nuevo_apellido_dos').val('');
                $('#agregar_profesional_nuevo_correo').val('');
                $('#agregar_profesional_nuevo_telefono_uno').val('');
                $('#agregar_profesional_nuevo_profesion').val('');
                $('#agregar_profesional_nuevo_especialidad').val('');
                $('#agregar_profesional_nuevo_sub_tipo_especialidad').val('');
                $('#agregar_profesional_nuevo_id_tipo_convenio_institucion').val('');
                $('#agregar_profesional_nuevo_fijo').val('');
                $('#agregar_profesional_nuevo_atencion').val('');
                $('#agregar_profesional_nuevo_confirmacion_agenda').val('');
                $('#agregar_profesional_nuevo_ggcc').val('');
                $('#agregar_profesional_nuevo_box').val('');
            }
            else
            {
                swal({
                    title: "Invitación al Profesional Fallida.",
                    text: "Profesional pendiente por confirmar.",
                    icon: "error",
                });
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function buscar_ciudad(select_region, select_ciudad, id_ciudad=0) {

        let region = $('#'+select_region).val();
        let url = "{{ route('home.buscar_ciudad_region') }}";
        $.ajax({
            url: url,
            type: "get",
            data: {
                region: region,
            },
        })
        .done(function(data) {
            if (data != null) {
                data = JSON.parse(data);

                let ciudades = $('#'+select_ciudad);

                ciudades.find('option').remove();
                ciudades.append('<option value="0">Seleccione Ciudad</option>');
                $(data).each(function(i, v) { // indice, valor
                    ciudades.append('<option value="' + v.id + '">' + v.nombre + '</option>');
                })

                if(id_ciudad != 0)
                    ciudades.val(id_ciudad);

            } else {

                swal({
                    title: "Error",
                    text: "Error al cargar las ciudades",
                    icon: "error",
                    buttons: "Aceptar",
                    DangerMode: true,
                })
                // alert('No se pudo Cargar las ciudades');
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    };


    function buscar_tipo_especialidad(id='')
    {

        let tipo_especialidad_registro = $('#agregar_profesional_nuevo_especialidad');
        tipo_especialidad_registro.find('option').remove();

        let sub_tipo_especialidad_registro = $('#agregar_profesional_nuevo_sub_tipo_especialidad');
        sub_tipo_especialidad_registro.find('option').remove();

        let especialidad = $('#agregar_profesional_nuevo_profesion').val();
        let url = "{{ route('home.buscar_especialidad') }}";
        $.ajax({
            url: url,
            type: "get",
            data: {
                //_token: _token,
                especialidad: especialidad,
            },
        })
        .done(function(data) {
            if (data != null) {
                data = JSON.parse(data);
                console.log(data);
                let especialidades = $('#agregar_profesional_nuevo_especialidad');

                especialidades.find('option').remove();
                especialidades.append('<option value="">Seleccione</option>');
                if(data.length > 0)
                {
                    $(data).each(function(i, v) { // indice, valor
                        especialidades.append('<option value="' + v.id + '">' + v.nombre + '</option>');
                    })
                }
                else
                {
                    especialidades.append('<option value="0">No Aplica</option>');
                    especialidades.val(0);

                    let sub_especialidades = $('#agregar_profesional_nuevo_sub_tipo_especialidad');
                    sub_especialidades.append('<option value="0">No Aplica</option>');
                    sub_especialidades.val(0);
                }
                if(id != '')
                    especialidades.val(id);


            } else {
                alert('No se pudo Cargar los tipos de especialidad');
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });

    }

    function buscar_sub_tipo_especialidad(id='')
    {
        let sub_tipo_especialidad_registro = $('#agregar_profesional_nuevo_sub_tipo_especialidad');
        sub_tipo_especialidad_registro.find('option').remove();

        let especialidad = $('#agregar_profesional_nuevo_especialidad').val();
        let url = "{{ route('home.buscar_sub_tipo_especialidad') }}";
        $.ajax({
            url: url,
            type: "get",
            data: {
                //_token: _token,
                especialidad: especialidad,
            },
        })
        .done(function(data) {
            if (data != null) {
                data = JSON.parse(data);
                console.log(data);
                console.log(data.length);
                let sub_especialidades = $('#agregar_profesional_nuevo_sub_tipo_especialidad');

                sub_especialidades.find('option').remove();
                sub_especialidades.append('<option value="">Seleccione</option>');
                if(data.length > 0)
                {
                    $(data).each(function(i, v) { // indice, valor
                        sub_especialidades.append('<option value="' + v.id + '">' + v.nombre + '</option>');
                    })
                }
                else
                {
                    sub_especialidades.append('<option value="0">No Aplica</option>');
                    sub_especialidades.val(0);
                }
                if(id != '')
                    sub_especialidades.val(id);

            } else {
                alert('No se pudo Cargar los tipos de especialidad');
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function registrar_nuevo_servicio(event){
        let id_nuevo_servicio = event.target.value;
        if(id_nuevo_servicio == 2 || id_nuevo_servicio == 3)
        {
            $('#div_nuevo_servicio').removeClass('d-none');
        }
        else
        {
            $('#div_nuevo_servicio').addClass('d-none');
        }
    }



</script>
