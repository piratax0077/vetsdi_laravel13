<div id="registrar_contratoprofesional" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="registrar_contratoprofesional_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="registrar_contratoprofesional_titulo">Registrar contrato del profesional</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_institucion" id="id_institucion" value="{{ $institucion->id }}">
                <input type="hidden" name="id_lugar_atencion" id="id_lugar_atencion" value="{{ $institucion->id_lugar_atencion }}">
                <input type="hidden" name="id_admin_creador" id="id_admin_creador" value="{{ Auth::user()->id }}">
                <input type="hidden" name="id_tipo_admin_creador" id="id_tipo_admin_creador" value="{{ Auth::user()->Roles()->first()->id }}">
                <input type="hidden" name="id_profesional_edit" id="id_profesional_edit" value="">
                <input type="hidden" name="id_contrato_edit" id="id_contrato_edit" value="">
                {{-- El mismo modal sirve para asistentes: "asistente" cambia la pestaña Profesión por Tipo de asistente y guarda en sus rutas --}}
                <input type="hidden" id="contrato_persona" value="profesional">
                <input type="hidden" id="id_asistente_edit" value="">
                <input type="hidden" id="id_contrato_asistente_edit" value="">
                <input type="hidden" id="contrato_clave_ingreso" value="{{ rand(11111, 99999) }}">

                <ul class="nav pill-moderno" id="registrar_contrato_pills" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="info-profesion-tab" data-toggle="tab" href="#info-profesion" role="tab" aria-controls="info-profesion" aria-selected="true">Profesi&oacute;n</a></li>
                    <li class="nav-item"><a class="nav-link" id="info_personal_cont-tab" data-toggle="tab" href="#info_personal_cont" role="tab" aria-controls="info_personal_cont" aria-selected="false">Informaci&oacute;n personal</a></li>
                    {{-- Los contratos de asistentes no guardan banco; su cuenta es la de su perfil --}}
                    <li class="nav-item contrato-solo-profesional"><a class="nav-link" id="info_contrato_pers-tab" data-toggle="tab" href="#info_contrato_pers" role="tab" aria-controls="info_contrato_pers" aria-selected="false">Informaci&oacute;n bancaria</a></li>
                    <li class="nav-item"><a class="nav-link" id="leyes_sociales-tab" data-toggle="tab" href="#leyes_sociales" role="tab" aria-controls="leyes_sociales" aria-selected="false">Leyes sociales</a></li>
                    <li class="nav-item"><a class="nav-link" id="horario_contrato-tab" data-toggle="tab" href="#horario_contrato" role="tab" aria-controls="horario_contrato" aria-selected="false">Horario</a></li>
                </ul>

                <div class="tab-content">
                    <!--Profesión-->
                    <div class="tab-pane fade show active" id="info-profesion" role="tabpanel" aria-labelledby="info-profesion-tab">
                        @if(isset($lista_tipo_asistente))
                            <div class="d-none" id="contrato_tipo_asistente_grupo">
                                <h6 class="t-modal">Tipo de asistente</h6>
                                <div class="form-row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm">Tipo de asistente</label>
                                            {{-- El servidor reconoce el tipo por su nombre en mayúsculas (igual que el registro de Personal) --}}
                                            <select class="form-control form-control-sm" id="contrato_tipo_asistente">
                                                <option value="">Seleccione opci&oacute;n</option>
                                                @foreach($lista_tipo_asistente as $tipo_asistente)
                                                    <option value="{{ mb_strtoupper($tipo_asistente->nombre) }}">{{ $tipo_asistente->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <h6 class="t-modal contrato-solo-profesional">Profesi&oacute;n y especialidad</h6>
                        <div class="form-row contrato-solo-profesional">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Profesi&oacute;n</label>
                                    <select name="profesion_nuevo_profesional" id="profesion_nuevo_profesional" class="form-control form-control-sm" onchange="dame_tipo_especialidad()">
                                        <option value="0">Seleccione opci&oacute;n</option>
                                        @foreach($especialidades as $especialidad)
                                            <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Especialidad</label>
                                    <select name="especialidad_nuevo_profesional" id="especialidad_nuevo_profesional" class="form-control form-control-sm" onchange="dame_subtipo_especialidad()">
                                        <option value="0">Seleccione opci&oacute;n</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Subespecialidad</label>
                                    <select name="sub_especialidad_nuevo_profesional" id="sub_especialidad_nuevo_profesional" class="form-control form-control-sm">
                                        <option value="0">Seleccione opci&oacute;n</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tipo de contrato: la pestaña está oculta, pero registrar_nuevo_profesional() sigue leyendo #tipo_contrato --}}
                    <div class="tab-pane fade" id="info-tipo_contrato" role="tabpanel">
                        <div class="form-row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Tipo de contrato</label>
                                    <select class="form-control form-control-sm" name="tipo_contrato" id="tipo_contrato">
                                        <option value="0">Seleccione opci&oacute;n</option>
                                        <option value="1">Fijo</option>
                                        <option value="2">Porcentaje de atenci&oacute;n</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Información personal-->
                    <div class="tab-pane fade" id="info_personal_cont" role="tabpanel" aria-labelledby="info_personal_cont-tab">
                        <h6 class="t-modal">Datos personales</h6>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">RUT</label>
                                    <input type="text" class="form-control form-control-sm" oninput="formatoRut(this)" name="rut_nuevo_profesional" id="rut_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Fecha de ingreso</label>
                                    <input type="date" class="form-control form-control-sm" name="f_ingreso_nuevo_profesional" id="f_ingreso_nuevo_profesional" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Nombre</label>
                                    <input type="text" class="form-control form-control-sm" name="nombre_nuevo_profesional" id="nombre_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Primer apellido</label>
                                    <input type="text" class="form-control form-control-sm" name="apellido1_nuevo_profesional" id="apellido1_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Segundo apellido</label>
                                    <input type="text" class="form-control form-control-sm" name="apellido2_nuevo_profesional" id="apellido2_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Sexo</label>
                                    <select class="form-control form-control-sm" name="empleado_sexo" id="empleado_sexo">
                                        <option value="">Seleccione</option>
                                        <option value="F">Femenino</option>
                                        <option value="M">Masculino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Fecha de nacimiento</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_nacimiento" id="fecha_nacimiento">
                                </div>
                            </div>
                        </div>

                        <h6 class="t-modal">Contacto y residencia</h6>
                        <div class="form-row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Correo electr&oacute;nico</label>
                                    <input type="email" class="form-control form-control-sm" name="email_nuevo_profesional" id="email_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Tel&eacute;fono</label>
                                    <input type="number" class="form-control form-control-sm" name="telefono1_nuevo_profesional" id="telefono1_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Tel&eacute;fono (opcional)</label>
                                    <input type="number" class="form-control form-control-sm" name="telefono2_nuevo_profesional" id="telefono2_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Direcci&oacute;n</label>
                                    <input type="text" class="form-control form-control-sm" name="direccion_nuevo_profesional" id="direccion_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">N&deg;</label>
                                    <input type="text" class="form-control form-control-sm" name="n_dpto_nuevo_profesional" id="n_dpto_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Regi&oacute;n</label>
                                    <select class="form-control form-control-sm" onchange="buscar_ciudad_profesional();" id="region_nuevo_profesional">
                                        <option value="0">Seleccione opci&oacute;n</option>
                                        @foreach($regiones as $region)
                                            <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Comuna</label>
                                    <select class="form-control form-control-sm" id="comuna_nuevo_profesional">
                                        <option value="0">Seleccione opci&oacute;n</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group d-flex align-items-center mb-0">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" id="correo-cont" checked="">
                                        <label for="correo-cont" class="cr"></label>
                                    </div>
                                    <label for="correo-cont" class="mb-0">Notificar por correo electr&oacute;nico</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Información bancaria-->
                    <div class="tab-pane fade" id="info_contrato_pers" role="tabpanel" aria-labelledby="info_contrato_pers-tab">
                        <h6 class="t-modal">Datos bancarios para dep&oacute;sito</h6>
                        <div class="form-row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Banco</label>
                                    <select class="form-control form-control-sm" name="banco_nuevo_profesional" id="banco_nuevo_profesional">
                                        <option value="0">Seleccione opci&oacute;n</option>
                                        @foreach($bancos as $banco)
                                            <option value="{{ $banco->id }}">{{ $banco->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">N&deg; de cuenta</label>
                                    <input type="number" class="form-control form-control-sm" name="n_cta_nuevo_profesional" id="n_cta_nuevo_profesional">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Sucursal</label>
                                    <input type="text" class="form-control form-control-sm" name="sucursal_nuevo_profesional" id="sucursal_nuevo_profesional">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Leyes sociales: cada beneficio se activa con su switch y recién ahí se puede escribir el valor-->
                    <div class="tab-pane fade" id="leyes_sociales" role="tabpanel" aria-labelledby="leyes_sociales-tab">
                        <h6 class="t-modal">Informaci&oacute;n y datos del contrato</h6>
                        <div class="form-row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Fecha de inicio</label>
                                    <input type="date" class="form-control form-control-sm" name="empleado_fecha_inicio" id="empleado_fecha_inicio" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Fecha de t&eacute;rmino</label>
                                    <input type="date" class="form-control form-control-sm" name="empleado_fecha_termino" id="empleado_fecha_termino">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group d-flex align-items-center mt-md-2">
                                    <input type="hidden" id="cont_indefinido" name="cont_indefinido" value="0">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" onchange="activar_check('empleado_check_contrato_indef', 'cont_indefinido', ' ');" id="empleado_check_contrato_indef" name="empleado_check_contrato_indef" value="">
                                        <label for="empleado_check_contrato_indef" class="cr"></label>
                                    </div>
                                    <label for="empleado_check_contrato_indef" class="mb-0">Contrato indefinido</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Monto imponible</label>
                                    <input type="number" class="form-control form-control-sm" name="empleado_monto_imponible" id="empleado_monto_imponible">
                                </div>
                            </div>
                        </div>

                        <h6 class="t-modal">Beneficios</h6>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <input type="hidden" id="empleado_locomocion" name="empleado_locomocion" value="0">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" onchange="activar_check('empleado_check_locomocion', 'empleado_locomocion', 'empleado_locomocion_porcentaje');" id="empleado_check_locomocion" name="empleado_check_locomocion" value="">
                                        <label for="empleado_check_locomocion" class="cr"></label>
                                    </div>
                                    <div class="form-group flex-grow-1">
                                        <label class="floating-label-activo-sm">Locomoci&oacute;n (%)</label>
                                        <input type="number" disabled="disabled" class="form-control form-control-sm" name="empleado_locomocion_porcentaje" id="empleado_locomocion_porcentaje" value="N/A">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <input type="hidden" id="empleado_colacion" name="empleado_colacion" value="0">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" onchange="activar_check('empleado_check_colacion', 'empleado_colacion', 'empleado_colacion_porcentaje');" id="empleado_check_colacion" name="empleado_check_colacion" value="">
                                        <label for="empleado_check_colacion" class="cr"></label>
                                    </div>
                                    <div class="form-group flex-grow-1">
                                        <label class="floating-label-activo-sm">Colaci&oacute;n (%)</label>
                                        <input type="number" disabled="disabled" class="form-control form-control-sm" name="empleado_colacion_porcentaje" id="empleado_colacion_porcentaje" value="N/A">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <input type="hidden" id="empleado_asignacion_familiar" name="empleado_asignacion_familiar" value="0">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" onchange="activar_check('empleado_check_asignacion_familiar', 'empleado_asignacion_familiar', 'empleado_asignacion_familiar_cantidad');" id="empleado_check_asignacion_familiar" name="empleado_check_asignacion_familiar" value="">
                                        <label for="empleado_check_asignacion_familiar" class="cr"></label>
                                    </div>
                                    <div class="form-group flex-grow-1">
                                        <label class="floating-label-activo-sm">Asignaci&oacute;n familiar (cargas)</label>
                                        <input type="number" disabled="disabled" class="form-control form-control-sm" name="empleado_asignacion_familiar_cantidad" id="empleado_asignacion_familiar_cantidad" value="N/A">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start">
                                    <input type="hidden" id="empleado_caja_compensacion" name="empleado_caja_compensacion" value="0">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" onchange="activar_check('empleado_check_caja_compensacion', 'empleado_caja_compensacion', 'empleado_caja_compensacion_porcentaje');" id="empleado_check_caja_compensacion" name="empleado_check_caja_compensacion" value="">
                                        <label for="empleado_check_caja_compensacion" class="cr"></label>
                                    </div>
                                    <div class="form-group flex-grow-1">
                                        <label class="floating-label-activo-sm">Caja de compensaci&oacute;n (%)</label>
                                        <input type="number" disabled="disabled" class="form-control form-control-sm" name="empleado_caja_compensacion_porcentaje" id="empleado_caja_compensacion_porcentaje" value="N/A">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Horario-->
                    <div class="tab-pane fade" id="horario_contrato" role="tabpanel" aria-labelledby="horario_contrato-tab">
                        <h6 class="t-modal">Horario de trabajo</h6>
                        <div class="form-row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">D&iacute;as de trabajo</label>
                                    <select class="form-control form-control-sm js-example-basic-multiple" name="dias_laborales" id="dias_laborales" multiple="multiple" data-width="100%">
                                        <option value="1">Lunes</option>
                                        <option value="2">Martes</option>
                                        <option value="3">Mi&eacute;rcoles</option>
                                        <option value="4">Jueves</option>
                                        <option value="5">Viernes</option>
                                        <option value="6">S&aacute;bado</option>
                                        <option value="7">Domingo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Hora de entrada</label>
                                    <input type="time" class="form-control form-control-sm" id="hora_entrada" name="hora_entrada" value="08:00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Hora de salida</label>
                                    <input type="time" class="form-control form-control-sm" id="hora_salida" name="hora_salida" value="19:00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">Inicio de colaci&oacute;n</label>
                                    <input type="time" class="form-control form-control-sm" id="hora_entrada_colacion" name="hora_entrada_colacion" value="12:00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm">T&eacute;rmino de colaci&oacute;n</label>
                                    <input type="time" class="form-control form-control-sm" id="hora_salida_colacion" name="hora_salida_colacion" value="13:00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="registrar_contrato_guardar" onclick="guardar_contrato_profesional()">Registrar contrato</button>
            </div>
        </div>
    </div>
</div>

<script>

    $(document).ready(function() {
        $('#dias_laborales').select2();
        $('#dias_laborales_convenio').select2();
    });

    /** buscar ciudad */
    function buscar_ciudad_profesional(id_ciudad=0) {

        let region = $('#region_nuevo_profesional').val();
        let url = "{{ route('adm_cm.buscar_ciudad_region') }}";
        $.ajax({

                url: url,
                type: "get",
                data: {
                    //_token: _token,
                    region: region,
                },
            })
            .done(function(data) {
                if (data != null) {
                    data = JSON.parse(data);

                    let ciudades = $('#comuna_nuevo_profesional');

                    ciudades.find('option').remove();
                    ciudades.append('<option value="0">Seleccione</option>');
                    $(data).each(function(i, v) { // indice, valor
                        ciudades.append('<option value="' + v.id + '">' + v.nombre +
                            '</option>');
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


    function formatoRut(rut)
    {
        var valor = rut.value.replace('.','');
        valor = valor.replace('-','');
        cuerpo = valor.slice(0,-1);
        dv = valor.slice(-1).toUpperCase();
        rut.value = cuerpo + '-'+ dv

        if(cuerpo.length < 7) { rut.setCustomValidity("RUT Incompleto"); return false;}

        suma = 0;
        multiplo = 2;

        for(i=1;i<=cuerpo.length;i++)
        {
            index = multiplo * valor.charAt(cuerpo.length - i);
            suma = suma + index;
            if(multiplo < 7) { multiplo = multiplo + 1; } else { multiplo = 2; }
        }

        dvEsperado = 11 - (suma % 11);
        dv = (dv == 'K')?10:dv;
        dv = (dv == 0)?11:dv;

        if(dvEsperado != dv) { rut.setCustomValidity("RUT Inválido"); return false; }

        rut.setCustomValidity('');
    }

    function dame_tipo_especialidad(){
        let profesion = $('#profesion_nuevo_profesional').val();
        let url = "{{ route('web.profesional.buscar_tipo_especialidad') }}";
        $.ajax({

                url: url,
                type: "get",
                data: {
                    //_token: _token,
                    id_especialidad: profesion,
                },
            })
            .done(function(data) {
                console.log(data);
                if (data != null) {
                    registros = data.registros;

                    let especialidades = $('#especialidad_nuevo_profesional');

                    especialidades.find('option').remove();
                    especialidades.append('<option value="0">Seleccione</option>');
                    $(registros).each(function(i, v) { // indice, valor
                        especialidades.append('<option value="' + v.id + '">' + v.nombre +
                            '</option>');
                    })

                } else {

                    swal({
                        title: "Error",
                        text: "Error al cargar las especialidades",
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
    }

    function dame_subtipo_especialidad(){
        let especialidad = $('#especialidad_nuevo_profesional').val();
        let url = "{{ route('web.profesional.buscar_sub_tipo_especialidad') }}";
        $.ajax({

                url: url,
                type: "get",
                data: {
                    //_token: _token,
                    id_tipo_especialidad: especialidad,
                },
            })
            .done(function(data) {
                console.log(data);
                if (data != null) {
                    registros = data.registros;

                    let subespecialidades = $('#sub_especialidad_nuevo_profesional');

                    subespecialidades.find('option').remove();
                    subespecialidades.append('<option value="0">Seleccione</option>');
                    $(registros).each(function(i, v) { // indice, valor
                        subespecialidades.append('<option value="' + v.id + '">' + v.nombre +
                            '</option>');
                    })

                } else {

                    swal({
                        title: "Error",
                        text: "Error al cargar las sub-especialidades",
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
    }

    function activar_check(check, input_base, input_valor)
    {
        if($('#'+check).prop('checked'))
        {
            $('#'+input_base).val(1);
            $('#'+input_valor).val(0);
            $('#'+input_valor).attr('disabled', false);
        }
        else
        {
            $('#'+input_base).val(0);
            $('#'+input_valor).val('N/A');
            $('#'+input_valor).attr('disabled', true);
        }
    }

    /*
     * El modal atiende a profesionales y asistentes. En modo "asistente" la primera pestaña pide el tipo de asistente
     * en vez de la profesión y se oculta la información bancaria (su cuenta es la del perfil, el contrato no la guarda).
     */
    function contrato_modo(persona, editando)
    {
        let asistente = (persona === 'asistente');
        $('#contrato_persona').val(asistente ? 'asistente' : 'profesional');
        $('#contrato_tipo_asistente_grupo').toggleClass('d-none', !asistente);
        $('#registrar_contratoprofesional .contrato-solo-profesional').toggleClass('d-none', asistente);
        $('#info-profesion-tab').html(asistente ? 'Tipo de asistente' : 'Profesi&oacute;n');
        $('#registrar_contratoprofesional_titulo').text((editando ? 'Editar' : 'Registrar') + ' contrato del ' + (asistente ? 'asistente' : 'profesional'));
        $('#registrar_contrato_guardar').text(editando ? 'Guardar cambios' : 'Registrar contrato');
    }

    function registrar_contrato_profesional()
    {
        contrato_modo('profesional', false);
        $('#info-profesion-tab').tab('show');
        $('#registrar_contratoprofesional').modal('show');
    }

    function registrar_contrato_asistente()
    {
        contrato_modo('asistente', false);
        $('#info-profesion-tab').tab('show');
        $('#registrar_contratoprofesional').modal('show');
    }

    /* Editar contrato: se usa este mismo modal con los datos del profesional o asistente ya cargados */
    function guardar_contrato_profesional()
    {
        if($('#contrato_persona').val() === 'asistente')
        {
            if($('#id_contrato_asistente_edit').val() != '')
                guardar_contrato_asistente('editar');
            else
                guardar_contrato_asistente('registrar');
            return;
        }

        if($('#id_profesional_edit').val() != '')
            editar_contrato_guardar();
        else
            registrar_nuevo_profesional();
    }

    function cargar_select_contrato(url, datos, select, valor)
    {
        return $.ajax({ url: url, type: 'get', data: datos })
        .done(function(data) {
            let campo = $('#' + select);
            campo.find('option').remove();
            campo.append('<option value="0">Seleccione opci&oacute;n</option>');
            $(data.registros || []).each(function(i, v) {
                campo.append('<option value="' + v.id + '">' + v.nombre + '</option>');
            });
            if(valor)
                campo.val(valor);
        });
    }

    function marcar_beneficio(check, base, valor, activo, cantidad)
    {
        $('#' + check).prop('checked', activo == 1);
        activar_check(check, base, valor);
        if(activo == 1)
            $('#' + valor).val(cantidad);
    }

    function valor_beneficio(base, valor)
    {
        return $('#' + base).val() == 1 ? $('#' + valor).val() : 0;
    }

    function editar_contrato_profesional(id_profesional)
    {
        let url = "{{ route('laboratorio.profesional_buscar', ['id_profesional' => '__id__']) }}".replace('__id__', id_profesional);
        $.ajax({ url: url, type: 'get' })
        .done(function(data) {
            if(data.estado != 1)
            {
                swal({ title: 'Editar contrato', text: 'No fue posible cargar la información del profesional.', icon: 'error', buttons: 'Aceptar' });
                return;
            }

            let prof = data.registro;
            let contrato = prof.contrato || {};
            let direccion = prof.direccion || {};

            contrato_modo('profesional', true);
            $('#id_profesional_edit').val(prof.id);
            $('#id_contrato_edit').val(contrato.id || '');
            $('#registrar_contratoprofesional_titulo').text('Editar contrato del profesional');
            $('#registrar_contrato_guardar').text('Guardar cambios');

            // Si todavía no tiene contrato se toma la profesión del perfil
            let profesion = contrato.id_especialidad || prof.id_especialidad;
            let especialidad = contrato.id_tipo_especialidad || prof.id_tipo_especialidad;
            let subespecialidad = contrato.id_subtipo_especialidad || prof.id_sub_tipo_especialidad;
            $('#profesion_nuevo_profesional').val(profesion || 0);
            cargar_select_contrato("{{ route('web.profesional.buscar_tipo_especialidad') }}", { id_especialidad: profesion }, 'especialidad_nuevo_profesional', especialidad);
            cargar_select_contrato("{{ route('web.profesional.buscar_sub_tipo_especialidad') }}", { id_tipo_especialidad: especialidad }, 'sub_especialidad_nuevo_profesional', subespecialidad);

            $('#rut_nuevo_profesional').val(prof.rut);
            if(contrato.fecha_inicio)
                $('#f_ingreso_nuevo_profesional').val(contrato.fecha_inicio);
            $('#nombre_nuevo_profesional').val(prof.nombre);
            $('#apellido1_nuevo_profesional').val(prof.apellido_uno);
            $('#apellido2_nuevo_profesional').val(prof.apellido_dos);
            $('#empleado_sexo').val(prof.sexo || '');
            $('#fecha_nacimiento').val(prof.fecha_nacimiento || prof.fecha_nac || '');
            $('#email_nuevo_profesional').val(prof.email);
            $('#telefono1_nuevo_profesional').val(prof.telefono_uno);
            $('#telefono2_nuevo_profesional').val(prof.telefono_dos);
            $('#direccion_nuevo_profesional').val(direccion.direccion || '');
            $('#n_dpto_nuevo_profesional').val(direccion.numero_dir || '');
            if(direccion.ciudad)
            {
                $('#region_nuevo_profesional').val(direccion.ciudad.id_region);
                buscar_ciudad_profesional(direccion.id_ciudad);
            }

            $('#banco_nuevo_profesional').val(contrato.id_banco || 0);
            $('#n_cta_nuevo_profesional').val(contrato.numero_cuenta || '');
            $('#sucursal_nuevo_profesional').val(contrato.sucursal || '');

            if(contrato.fecha_inicio)
                $('#empleado_fecha_inicio').val(contrato.fecha_inicio);
            $('#empleado_fecha_termino').val(contrato.fecha_termino || '');
            $('#empleado_check_contrato_indef').prop('checked', contrato.tipo_contrato == 1);
            $('#cont_indefinido').val(contrato.tipo_contrato == 1 ? 1 : 0);
            $('#empleado_monto_imponible').val(contrato.monto_imponible || '');
            marcar_beneficio('empleado_check_locomocion', 'empleado_locomocion', 'empleado_locomocion_porcentaje', contrato.locomocion, contrato.locomocion_porcentaje);
            marcar_beneficio('empleado_check_colacion', 'empleado_colacion', 'empleado_colacion_porcentaje', contrato.colacion, contrato.colacion_porcentaje);
            marcar_beneficio('empleado_check_asignacion_familiar', 'empleado_asignacion_familiar', 'empleado_asignacion_familiar_cantidad', contrato.asignacion_familiar, contrato.asignacion_familiar_cantidad);
            marcar_beneficio('empleado_check_caja_compensacion', 'empleado_caja_compensacion', 'empleado_caja_compensacion_porcentaje', contrato.caja_compensacion, contrato.caja_compensacion_porcentaje);

            if(contrato.dias_laborales)
                $('#dias_laborales').val(contrato.dias_laborales.split(',')).trigger('change');
            if(contrato.hora_ingreso) $('#hora_entrada').val(contrato.hora_ingreso.substring(0, 5));
            if(contrato.hora_salida) $('#hora_salida').val(contrato.hora_salida.substring(0, 5));
            if(contrato.hora_inicio_colacion) $('#hora_entrada_colacion').val(contrato.hora_inicio_colacion.substring(0, 5));
            if(contrato.hora_termino_colacion) $('#hora_salida_colacion').val(contrato.hora_termino_colacion.substring(0, 5));

            $('#info-profesion-tab').tab('show');
            $('#registrar_contratoprofesional').modal('show');
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError);
        });
    }

    function editar_contrato_guardar()
    {
        let fecha_termino = $('#empleado_fecha_termino').val();
        let datos = {
            _token: "{{ csrf_token() }}",
            id_prof: $('#id_profesional_edit').val(),
            id_contrato: $('#id_contrato_edit').val(),
            id_institucion: $('#id_institucion').val(),
            id_lugar_atencion: $('#id_lugar_atencion').val(),
            tipo_contrato: ($('#cont_indefinido').val() == 1 || fecha_termino == '') ? 1 : 2,
            profesion: $('#profesion_nuevo_profesional').val(),
            especialidad: $('#especialidad_nuevo_profesional').val(),
            sub_especialidad: $('#sub_especialidad_nuevo_profesional').val(),
            rut: $('#rut_nuevo_profesional').val(),
            nombre: $('#nombre_nuevo_profesional').val(),
            apellido1: $('#apellido1_nuevo_profesional').val(),
            apellido2: $('#apellido2_nuevo_profesional').val(),
            sexo: $('#empleado_sexo').val(),
            fecha_nacimiento: $('#fecha_nacimiento').val(),
            email: $('#email_nuevo_profesional').val(),
            telefono1: $('#telefono1_nuevo_profesional').val(),
            telefono2: $('#telefono2_nuevo_profesional').val(),
            direccion: $('#direccion_nuevo_profesional').val(),
            numero: $('#n_dpto_nuevo_profesional').val(),
            region: $('#region_nuevo_profesional').val(),
            ciudad: $('#comuna_nuevo_profesional').val(),
            banco: $('#banco_nuevo_profesional').val(),
            n_cta: $('#n_cta_nuevo_profesional').val(),
            sucursal: $('#sucursal_nuevo_profesional').val(),
            fecha_inicio: $('#empleado_fecha_inicio').val(),
            fecha_termino: fecha_termino,
            monto_imponible: $('#empleado_monto_imponible').val(),
            locomocion: $('#empleado_locomocion').val(),
            locomocion_porcentaje: valor_beneficio('empleado_locomocion', 'empleado_locomocion_porcentaje'),
            colacion: $('#empleado_colacion').val(),
            colacion_porcentaje: valor_beneficio('empleado_colacion', 'empleado_colacion_porcentaje'),
            asignacion_familiar: $('#empleado_asignacion_familiar').val(),
            asignacion_familiar_cantidad: valor_beneficio('empleado_asignacion_familiar', 'empleado_asignacion_familiar_cantidad'),
            caja_compensacion: $('#empleado_caja_compensacion').val(),
            caja_compensacion_porcentaje: valor_beneficio('empleado_caja_compensacion', 'empleado_caja_compensacion_porcentaje'),
            dias_laborales: $('#dias_laborales').val(),
            hora_entrada: $('#hora_entrada').val(),
            hora_salida: $('#hora_salida').val(),
            hora_entrada_colacion: $('#hora_entrada_colacion').val(),
            hora_salida_colacion: $('#hora_salida_colacion').val(),
        };

        let mensaje = '';
        if(datos.profesion == 0 || !datos.profesion) mensaje += '<li>Debe seleccionar la profesi&oacute;n</li>';
        if(datos.rut == '') mensaje += '<li>Debe ingresar el RUT</li>';
        if(datos.nombre == '' || datos.apellido1 == '') mensaje += '<li>Debe ingresar el nombre y el primer apellido</li>';
        if(datos.email == '') mensaje += '<li>Debe ingresar el correo electr&oacute;nico</li>';
        if(datos.telefono1 == '') mensaje += '<li>Debe ingresar el tel&eacute;fono</li>';
        if(datos.fecha_inicio == '') mensaje += '<li>Debe ingresar la fecha de inicio del contrato</li>';
        if(datos.monto_imponible == '') mensaje += '<li>Debe ingresar el monto imponible</li>';
        if(!datos.dias_laborales || datos.dias_laborales.length == 0) mensaje += '<li>Debe seleccionar los d&iacute;as de trabajo</li>';

        if(mensaje != '')
        {
            swal({
                title: 'Faltan datos',
                content: { element: 'div', attributes: { innerHTML: '<ul class="text-left">' + mensaje + '</ul>' } },
                icon: 'error',
                buttons: 'Aceptar',
            });
            return false;
        }

        $.ajax({
            url: "{{ route('adm_cm.editar_profesional') }}",
            type: 'post',
            data: datos,
        })
        .done(function(data) {
            if(data.estado == 1)
            {
                $('#registrar_contratoprofesional').modal('hide');
                swal({ title: 'Contrato actualizado', icon: 'success', buttons: 'Aceptar' })
                .then(function() { location.reload(); });
            }
            else
            {
                swal({ title: 'Error', text: data.msj || 'No fue posible guardar el contrato.', icon: 'error', buttons: 'Aceptar' });
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError);
            swal({ title: 'Error', text: 'No fue posible guardar el contrato.', icon: 'error', buttons: 'Aceptar' });
        });
    }

    // Al cerrar después de editar (o de usarlo para un asistente), el modal vuelve a quedar listo para registrar un profesional.
    // Se registra al cargar la página: la plantilla trae dos jQuery y Bootstrap avisa el cierre con el que queda al final
    $(function () { $(document).on('hidden.bs.modal', '#registrar_contratoprofesional', function () {
        if($('#id_profesional_edit').val() == '' && $('#contrato_persona').val() !== 'asistente')
            return;

        let modal = $(this);
        $('#id_profesional_edit, #id_contrato_edit, #id_asistente_edit, #id_contrato_asistente_edit').val('');
        contrato_modo('profesional', false);
        modal.find('input').not('[type=hidden], [type=checkbox]').each(function () { this.value = this.defaultValue; });
        modal.find('input[type=checkbox]').each(function () { this.checked = this.defaultChecked; });
        // Las fechas quedan ocultas bajo el calendario, por eso se reinician aparte
        $('#fecha_nacimiento, #empleado_fecha_termino').val('');
        $('#f_ingreso_nuevo_profesional, #empleado_fecha_inicio').val('{{ date('Y-m-d') }}');
        $('#cont_indefinido').val(0);
        [['locomocion', 'porcentaje'], ['colacion', 'porcentaje'], ['asignacion_familiar', 'cantidad'], ['caja_compensacion', 'porcentaje']].forEach(function (b) {
            activar_check('empleado_check_' + b[0], 'empleado_' + b[0], 'empleado_' + b[0] + '_' + b[1]);
        });
        modal.find('select').not('#dias_laborales').each(function () { this.selectedIndex = 0; });
        $('#dias_laborales').val(null).trigger('change');
    }); });

    /*
     * Editar contrato de asistente: el contrato llega desde la fila (es el vigente en este lugar)
     * y los datos personales y la dirección se consultan como en la ficha del asistente.
     */
    function editar_contrato_asistente(id_asistente, contrato, tipo_actual)
    {
        contrato = contrato || {};
        $.get("{{ route('adm_cm.asistente_buscar') }}", { id: id_asistente })
        .done(function(data) {
            if(!data || data.estado != 1)
            {
                swal({ title: 'Editar contrato', text: 'No fue posible cargar la información del asistente.', icon: 'error', buttons: 'Aceptar' });
                return;
            }

            let asistente = data.registro;
            let direccion = asistente.direccion || {};

            contrato_modo('asistente', true);
            $('#id_asistente_edit').val(asistente.id);
            $('#id_contrato_asistente_edit').val(contrato.id || '');
            // Parte con el tipo actual del asistente: al guardar, el servidor también le asigna ese tipo y su rol,
            // y el contrato puede haber quedado con un tipo anterior
            $('#contrato_tipo_asistente').val(String(tipo_actual || contrato.tipo_empleado || '').toUpperCase());

            $('#rut_nuevo_profesional').val(contrato.rut || asistente.rut);
            if(contrato.fecha_inicio)
                $('#f_ingreso_nuevo_profesional').val(contrato.fecha_inicio);
            $('#nombre_nuevo_profesional').val(contrato.nombres || asistente.nombres);
            $('#apellido1_nuevo_profesional').val(contrato.apellido_uno || asistente.apellido_uno);
            $('#apellido2_nuevo_profesional').val(contrato.apellido_dos || asistente.apellido_dos);
            $('#empleado_sexo').val(asistente.sexo || '');
            $('#fecha_nacimiento').val(asistente.fecha_nac || '');
            $('#email_nuevo_profesional').val(contrato.email || asistente.email);
            $('#telefono1_nuevo_profesional').val(contrato.telefono || asistente.telefono_uno);
            $('#telefono2_nuevo_profesional').val(asistente.telefono_dos || '');
            $('#direccion_nuevo_profesional').val(direccion.direccion || '');
            $('#n_dpto_nuevo_profesional').val(direccion.numero_dir || '');
            if(direccion.ciudad)
            {
                $('#region_nuevo_profesional').val(direccion.ciudad.id_region);
                buscar_ciudad_profesional(direccion.id_ciudad);
            }

            if(contrato.fecha_inicio)
                $('#empleado_fecha_inicio').val(contrato.fecha_inicio);
            $('#empleado_fecha_termino').val(contrato.fecha_termino || '');
            $('#empleado_check_contrato_indef').prop('checked', contrato.tipo_contrato == 1);
            $('#cont_indefinido').val(contrato.tipo_contrato == 1 ? 1 : 0);
            $('#empleado_monto_imponible').val(contrato.monto_imponible || '');
            marcar_beneficio('empleado_check_locomocion', 'empleado_locomocion', 'empleado_locomocion_porcentaje', contrato.locomocion, contrato.locomocion_porcentaje);
            marcar_beneficio('empleado_check_colacion', 'empleado_colacion', 'empleado_colacion_porcentaje', contrato.colacion, contrato.colacion_porcentaje);
            marcar_beneficio('empleado_check_asignacion_familiar', 'empleado_asignacion_familiar', 'empleado_asignacion_familiar_cantidad', contrato.asignacion_familiar, contrato.asignacion_familiar_cantidad);
            marcar_beneficio('empleado_check_caja_compensacion', 'empleado_caja_compensacion', 'empleado_caja_compensacion_porcentaje', contrato.caja_compensacion, contrato.caja_compensacion_porcentaje);

            if(contrato.dias_laborales)
                $('#dias_laborales').val(String(contrato.dias_laborales).split(',')).trigger('change');
            if(contrato.hora_ingreso) $('#hora_entrada').val(contrato.hora_ingreso.substring(0, 5));
            if(contrato.hora_salida) $('#hora_salida').val(contrato.hora_salida.substring(0, 5));
            if(contrato.hora_inicio_colacion) $('#hora_entrada_colacion').val(contrato.hora_inicio_colacion.substring(0, 5));
            if(contrato.hora_termino_colacion) $('#hora_salida_colacion').val(contrato.hora_termino_colacion.substring(0, 5));

            $('#info-profesion-tab').tab('show');
            $('#registrar_contratoprofesional').modal('show');
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError);
            swal({ title: 'Editar contrato', text: 'No fue posible cargar la información del asistente.', icon: 'error', buttons: 'Aceptar' });
        });
    }

    /* Registra un asistente nuevo con su contrato o guarda el contrato editado; usa las mismas rutas que Personal */
    function guardar_contrato_asistente(accion)
    {
        let fecha_termino = $('#empleado_fecha_termino').val();
        let indefinido = ($('#cont_indefinido').val() == 1 || fecha_termino == '');
        let datos = {
            _token: "{{ csrf_token() }}",
            id_institucion: $('#id_institucion').val(),
            id_lugar_atencion: $('#id_lugar_atencion').val(),
            id_admin_creador: $('#id_admin_creador').val(),
            id_tipo_admin_creador: $('#id_tipo_admin_creador').val(),
            rut: $('#rut_nuevo_profesional').val(),
            nombre: $('#nombre_nuevo_profesional').val(),
            apellido_uno: $('#apellido1_nuevo_profesional').val(),
            apellido_dos: $('#apellido2_nuevo_profesional').val(),
            sexo: $('#empleado_sexo').val(),
            email: $('#email_nuevo_profesional').val(),
            telefono: $('#telefono1_nuevo_profesional').val(),
            telefono_dos: $('#telefono2_nuevo_profesional').val(),
            region: $('#region_nuevo_profesional').val(),
            ciudad: $('#comuna_nuevo_profesional').val(),
            direccion: $('#direccion_nuevo_profesional').val(),
            numero: $('#n_dpto_nuevo_profesional').val(),
            fecha_inicio: $('#empleado_fecha_inicio').val(),
            fecha_termino: indefinido ? '' : fecha_termino,
            monto_imponible: $('#empleado_monto_imponible').val(),
            locomocion: $('#empleado_locomocion').val(),
            locomocion_porcentaje: valor_beneficio('empleado_locomocion', 'empleado_locomocion_porcentaje'),
            colacion: $('#empleado_colacion').val(),
            colacion_porcentaje: valor_beneficio('empleado_colacion', 'empleado_colacion_porcentaje'),
            asignacion_familiar: $('#empleado_asignacion_familiar').val(),
            asignacion_familiar_cantidad: valor_beneficio('empleado_asignacion_familiar', 'empleado_asignacion_familiar_cantidad'),
            caja_compensacion: $('#empleado_caja_compensacion').val(),
            caja_compensacion_porcentaje: valor_beneficio('empleado_caja_compensacion', 'empleado_caja_compensacion_porcentaje'),
            dias_laborales: $('#dias_laborales').val() || [],
            hora_entrada: $('#hora_entrada').val(),
            hora_salida: $('#hora_salida').val(),
            hora_entrada_colacion: $('#hora_entrada_colacion').val(),
            hora_salida_colacion: $('#hora_salida_colacion').val(),
            clave_ingreso: $('#contrato_clave_ingreso').val(),
            otro: '',
            otro_2: '',
        };
        let tipo_asistente = $('#contrato_tipo_asistente').val();

        let mensaje = '';
        if(!tipo_asistente) mensaje += '<li>Debe seleccionar el tipo de asistente</li>';
        if(datos.rut == '') mensaje += '<li>Debe ingresar el RUT</li>';
        if(datos.nombre == '' || datos.apellido_uno == '') mensaje += '<li>Debe ingresar el nombre y el primer apellido</li>';
        if(datos.sexo == '') mensaje += '<li>Debe seleccionar el sexo</li>';
        if(datos.email == '') mensaje += '<li>Debe ingresar el correo electr&oacute;nico</li>';
        if(datos.telefono == '') mensaje += '<li>Debe ingresar el tel&eacute;fono</li>';
        if(datos.direccion == '' || datos.ciudad == 0) mensaje += '<li>Debe ingresar la direcci&oacute;n, regi&oacute;n y comuna</li>';
        if(datos.fecha_inicio == '') mensaje += '<li>Debe ingresar la fecha de inicio del contrato</li>';
        if(datos.monto_imponible == '') mensaje += '<li>Debe ingresar el monto imponible</li>';
        if(!datos.dias_laborales.length) mensaje += '<li>Debe seleccionar los d&iacute;as de trabajo</li>';
        if(!datos.hora_entrada || !datos.hora_salida || !datos.hora_entrada_colacion || !datos.hora_salida_colacion) mensaje += '<li>Debe completar el horario y la colaci&oacute;n</li>';

        if(mensaje != '')
        {
            swal({
                title: 'Faltan datos',
                content: { element: 'div', attributes: { innerHTML: '<ul class="text-left">' + mensaje + '</ul>' } },
                icon: 'error',
                buttons: 'Aceptar',
            });
            return false;
        }

        let url = '';
        if(accion === 'editar')
        {
            // editarPersonal recibe el tipo de asistente en tipo_empleado y el tipo de contrato (1 indefinido, 2 plazo fijo) en tipo_contrato
            url = "{{ route('adm_cm.editar_personal') }}";
            datos.id_empleado = $('#id_asistente_edit').val();
            datos.id_contrato = $('#id_contrato_asistente_edit').val();
            datos.tipo_empleado = tipo_asistente;
            datos.tipo_contrato = indefinido ? 1 : 2;
            datos.fecha_nac = $('#fecha_nacimiento').val();
        }
        else
        {
            // registrarPersonal crea el perfil del asistente y su contrato; reconoce el tipo de asistente en tipo_contrato
            url = "{{ route('adm_cm.registrar_personal') }}";
            datos.tipo_contrato = tipo_asistente;
            datos.fecha_nacimiento = $('#fecha_nacimiento').val();
        }

        let boton = $('#registrar_contrato_guardar').prop('disabled', true);
        $.post(url, datos)
        .done(function(data) {
            if(data && data.estado == 1)
            {
                $('#registrar_contratoprofesional').modal('hide');
                swal({ title: accion === 'editar' ? 'Contrato actualizado' : 'Registro exitoso', icon: 'success', buttons: 'Aceptar' })
                .then(function() { location.reload(); });
                return;
            }
            let detalle = (data && data.error) ? $.map(data.error, function(texto) { return texto; }).join('\n') : '';
            swal({ title: 'Error', text: detalle || (data && data.msj) || 'No fue posible guardar el contrato.', icon: 'error', buttons: 'Aceptar' });
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError);
            swal({ title: 'Error', text: 'No fue posible guardar el contrato.', icon: 'error', buttons: 'Aceptar' });
        })
        .always(function() {
            boton.prop('disabled', false);
        });
    }

</script>
