{{-- Funciones de las pestañas Asistentes y Contratos (traídas de las páginas Personal y Contratos) --}}
<script>
    $(document).ready(function() {
        $("#agregar_profesional_int_rut").rut({
            formatOn: 'keyup',
            minimumLength: 2,
            validateOn: 'change',
            useThousandsSeparator : false
        });
        $(".js-example-basic-multiple").select2();
        $('#edit_dias_laborales').select2({
            placeholder: "Seleccione los días laborales",
            allowClear: true
        });
    });

    /* ===== Contratos ===== */
    function datoscuenta(){
        $('#datoscuenta').modal('show');
    }

    function editar_datosprofesionalc(id){
        $('#editar_profesional_cm').modal('show');
        let url = "{{ url('Laboratorio/Profesionales/buscar') }}"+"/"+id;
        $.ajax({
            url: url,
            type: "get",
        })
        .done(async function(data) {
            console.log(data);
            if (data != null)
            {
                if(data.estado == 1)
                {
                    console.log(data.registro.contrato);
                    $('#id_profesional_edit').val(data.registro.id);
                    $('#id_contrato_edit').val(data.registro.contrato.id);

                    $('#edit_profesion_nuevo_profesional').val(data.registro.contrato.id_especialidad);
                   cargar_tipos_especialidad(data.registro.contrato.id_especialidad, data.registro.contrato.id_tipo_especialidad);
                    // data.direccion;
                    cargar_subtipo_especialidad(data.registro.contrato.id_tipo_especialidad, data.registro.contrato.id_subtipo_especialidad);

                    $('#edit_rut_nuevo_profesional').val(data.registro.rut);
                    $('#edit_f_ingreso_nuevo_profesional').val(data.registro.contrato.fecha_inicio);
                    $('#nombre_nuevo_profesional_edit').val(data.registro.nombre);
                    $('#edit_apellido1_nuevo_profesional').val(data.registro.apellido_uno);
                    $('#edit_apellido2_nuevo_profesional').val(data.registro.apellido_dos);
                    $('#edit_prof_sexo').val(data.registro.sexo);
                    $('#edit_fecha_nacimiento').val(data.registro.fecha_nac);
                    $('#edit_email_nuevo_profesional').val(data.registro.email);
                    $('#edit_telefono1_nuevo_profesional').val(data.registro.telefono_uno);
                    $('#edit_telefono2_nuevo_profesional').val(data.registro.telefono_dos);
                    $('#edit_direccion_nuevo_profesional').val(data.direccion);
                    $('#edit_numero_nuevo_profesional').val(data.registro.direccion.numero_dir);

                    // data.registro.direccion.id;
                    // $('#edit_region_nuevo_profesional').val(data.registro.direccion.ciudad.id_region);
                    console.log(data.registro.direccion.ciudad.id_region);
                    $('#edit_fecha_nacimiento').val(data.registro.fecha_nacimiento);

                    console.log(data.registro.direccion.ciudad.id_region);

                    $('#edit_region_nuevo_profesional').val(data.registro.direccion.ciudad.id_region);
                    try {
                            await buscar_ciudad_editar_prof(data.registro.direccion.ciudad.id_region);
                            $('#edit_comuna_nuevo_profesional').val(data.registro.direccion.id_ciudad);
                        } catch (error) {
                            console.error("Error al actualizar la ciudad:", error);
                        }

                    $('#edit_prof_monto_imponible').val(data.registro.contrato.monto_imponible);
                    $('#edit_banco_nuevo_profesional').val(data.registro.contrato.id_banco);
                    $('#edit_n_cta_nuevo_profesional').val(data.registro.contrato.numero_cuenta);
                    $('#edit_sucursal_nuevo_profesional').val(data.registro.contrato.sucursal);

                    console.log(data.registro.contrato.dias_laborales);
                    $('#edit_dias_laborales').val(data.registro.contrato.dias_laborales.split(",")).select2();
                    $('#edit_hora_entrada').val(data.registro.contrato.hora_ingreso);
                    $('#edit_hora_salida').val(data.registro.contrato.hora_salida);
                    $('#edit_hora_entrada_colacion').val(data.registro.contrato.hora_inicio_colacion);
                    $('#edit_hora_salida_colacion').val(data.registro.contrato.hora_termino_colacion);

                    $('#edit_prof_id_prof').val(data.registro.id);


                    $('#edit_prof_direccion').val(data.registro.direccion.direccion);
                    $('#edit_prof_numero').val(data.registro.direccion.numero_dir);


                    // data.registro.direccion.ciudad.id
                    // data.registro.direccion.ciudad.nombre

                    $('#edit_prof_id_contrato').val(data.registro.contrato.id);
                    $('#edit_prof_tipo_contrato').val(data.registro.contrato.tipo_prof);
                    // data.registro.contrato.id_prof;
                    $('#edit_prof_rut').val(data.registro.contrato.rut);
                    $('#edit_prof_nombre').val(data.registro.contrato.nombres);
                    $('#edit_prof_apellido_uno').val(data.registro.contrato.apellido_uno);
                    $('#edit_prof_apellido_dos').val(data.registro.contrato.apellido_dos);
                    $('#edit_prof_telefono').val(data.registro.contrato.telefono);
                    $('#edit_prof_email').val(data.registro.contrato.email);
                    // data.registro.contrato.id_institucion;
                    // data.registro.contrato.id_lugar_atencion;
                    // data.registro.contrato.tipo_contrato;

                    $('#edit_prof_check_contrato_indef').val(data.registro.contrato.tipo_contrato);
                    if(data.registro.contrato.tipo_contrato == 1)
                        $('#edit_prof_check_contrato_indef').prop('checked', true);
                    else
                        $('#edit_prof_check_contrato_indef').prop('checked', false);
                    contrato_indefinido('edit_prof_check_contrato_indef', 'edit_prof_cont_indefinido', 'edit_prof_fecha_termino');

                    $('#edit_prof_fecha_inicio').val(data.registro.contrato.fecha_inicio);
                    $('#edit_prof_fecha_termino').val(data.registro.contrato.fecha_termino);
                    $('#edit_prof_monto_imponible').val(data.registro.contrato.monto_imponible);

                    if(data.registro.contrato.locomocion == 1)
                        $('#edit_prof_check_locomocion').prop('checked', true);
                    else
                        $('#edit_prof_check_locomocion').prop('checked', false);
                    activar_check('edit_prof_check_locomocion', 'edit_prof_locomocion', 'edit_prof_locomocion_porcentaje');
                    $('#edit_prof_locomocion_porcentaje').val(data.registro.contrato.locomocion_porcentaje);

                    if(data.registro.contrato.colacion == 1)
                        $('#edit_prof_check_colacion').prop('checked', true);
                    else
                        $('#edit_prof_check_colacion').prop('checked', false);
                    activar_check('edit_prof_check_colacion', 'edit_prof_colacion', 'edit_prof_colacion_porcentaje');
                    $('#edit_prof_colacion_porcentaje').val(data.registro.contrato.colacion_porcentaje);

                    if(data.registro.contrato.asignacion_familiar == 1)
                        $('#edit_prof_check_asignacion_familiar').prop('checked', true);
                    else
                        $('#edit_prof_check_asignacion_familiar').prop('checked', false);
                    activar_check('edit_prof_check_asignacion_familiar', 'edit_prof_asignacion_familiar', 'edit_prof_asignacion_familiar_cantidad');
                    $('#edit_prof_asignacion_familiar_cantidad').val(data.registro.contrato.asignacion_familiar_cantidad);

                    if(data.registro.contrato.caja_compensacion == 1)
                        $('#edit_prof_check_caja_compensacion').prop('checked', true);
                    else
                        $('#edit_prof_check_caja_compensacion').prop('checked', false);
                    activar_check('edit_prof_check_caja_compensacion', 'edit_prof_caja_compensacion', 'edit_prof_caja_compensacion_porcentaje');
                    $('#edit_prof_caja_compensacion_porcentaje').val(data.registro.contrato.caja_compensacion_porcentaje);


                }
                else
                {

                }

            }
            else
            {
                swal({
                    title: "Error",
                    text: "Error al cargar las ciudades",
                    icon: "error",
                    buttons: "Aceptar",
                    DangerMode: true,
                });
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function editar_datosprofesionalc_convenio(id){
        $('#editar_profesional_cm_convenio').modal('show');
        let url = "{{ url('Laboratorio/Profesionales/buscar') }}"+"/"+id;
        $.ajax({
            url: url,
            type: "get",
        })
        .done(async function(data) {
            console.log(data);
            if (data != null)
            {
                if(data.estado == 1)
                {
                    console.log(data.registro.contrato);
                    $('#editar_profesion_nuevo_profesional_convenio').val(data.registro.contrato.id_especialidad);
                    cargar_tipos_especialidad_convenio(data.registro.contrato.id_especialidad, data.registro.contrato.id_tipo_especialidad);
                    // data.direccion;
                    cargar_subtipo_especialidad_convenio(data.registro.contrato.id_tipo_especialidad, data.registro.contrato.id_subtipo_especialidad);
                    $('#editar_rut_nuevo_profesional_convenio').val(data.registro.contrato.rut);
                    $('#editar_nombre_nuevo_profesional_convenio').val(data.registro.contrato.nombres);
                    $('#editar_apellido1_nuevo_profesional_convenio').val(data.registro.contrato.apellido_uno);
                    $('#editar_apellido2_nuevo_profesional_convenio').val(data.registro.contrato.apellido_dos);
                    $('#editar_empleado_sexo_convenio').val(data.registro.sexo);
                    $('#editar_fecha_nacimiento_convenio').val(data.registro.fecha_nacimiento);
                    $('#editar_email_nuevo_profesional_convenio').val(data.registro.email);
                    $('#editar_telefono1_nuevo_profesional_convenio').val(data.registro.telefono_uno);
                    $('#editar_telefono2_nuevo_profesional_convenio').val(data.registro.telefono_dos);
                    $('#editar_direccion_nuevo_profesional_convenio').val(data.registro.direccion.direccion);
                    $('#editar_n_dpto_nuevo_profesional_convenio').val(data.registro.direccion.numero_dir);
                    $('#editar_region_nuevo_profesional_convenio').val(data.registro.direccion.ciudad.id_region);
                    try {
                        await buscar_ciudad_editar_prof_convenio(data.registro.direccion.ciudad.id_region);
                        $('#editar_comuna_nuevo_profesional_convenio').val(data.registro.direccion.id_ciudad);
                    } catch (error) {
                        console.error("Error al actualizar la ciudad:", error);
                    }
                    $('#editar_banco_nuevo_profesional_convenio').val(data.registro.contrato.id_banco);
                    $('#editar_n_cta_nuevo_profesional_convenio').val(data.registro.contrato.numero_cuenta);
                    $('#editar_sucursal_nuevo_profesional_convenio').val(data.registro.contrato.sucursal);
                    // dias laborales
                    $('#editar_dias_laborales_convenio').val(data.registro.contrato.dias_laborales.split(",")).select2();
                    $('#editar_hora_entrada_convenio').val(data.registro.contrato.hora_ingreso);
                    $('#editar_hora_salida_convenio').val(data.registro.contrato.hora_salida);
                    $('#editar_hora_entrada_colacion_convenio').val(data.registro.contrato.hora_inicio_colacion);
                    $('#editar_hora_salida_colacion_convenio').val(data.registro.contrato.hora_termino_colacion);
                }
            }else{

            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function cargar_tipos_especialidad(id_especialidad, id_tipo_especialidad = null){
        let url = "{{ route('web.profesional.buscar_tipo_especialidad') }}";
        $.ajax({
            url: url,
            type: "get",
            data:{
                id_especialidad: id_especialidad
            },
        })
        .done(function(data) {
            console.log(data);
            if (data != null)
            {
                if(data.estado == 1)
                {
                    $('#edit_especialidad_nuevo_profesional').empty();
                    $('#edit_especialidad_nuevo_profesional').append('<option value="">Seleccione el tipo de especialidad</option>');
                    data.registros.forEach(tipo => {
                        $('#edit_especialidad_nuevo_profesional').append('<option value="'+tipo.id+'">'+tipo.nombre+'</option>');
                    });

                    if(id_tipo_especialidad != null)
                        $('#edit_especialidad_nuevo_profesional').val(id_tipo_especialidad);
                }
                else
                {
                    swal({
                        title: "Error",
                        text: "Error al cargar los tipos de especialidad",
                        icon: "error",
                        buttons: "Aceptar",
                        DangerMode: true,
                    });
                }
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Error al cargar los tipos de especialidad",
                    icon: "error",
                    buttons: "Aceptar",
                    DangerMode: true,
                });
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function cargar_subtipo_especialidad(id_tipo_especialidad, id_subtipo_especialidad = null){
        let url = "{{ route('web.profesional.buscar_sub_tipo_especialidad') }}";
        $.ajax({
            url: url,
            type: "get",
            data:{
                id_tipo_especialidad: id_tipo_especialidad
            },
        })
        .done(function(data) {
            console.log(data);
            if (data != null)
            {
                if(data.estado == 1)
                {
                    $('#edit_sub_especialidad_nuevo_profesional').empty();
                    $('#edit_sub_especialidad_nuevo_profesional').append('<option value="">Seleccione el subtipo de especialidad</option>');
                    data.registros.forEach(subtipo => {
                        $('#edit_sub_especialidad_nuevo_profesional').append('<option value="'+subtipo.id+'">'+subtipo.nombre+'</option>');
                    });

                    if(id_subtipo_especialidad != null)
                        $('#edit_sub_especialidad_nuevo_profesional').val(id_subtipo_especialidad);
                }
                else
                {
                    swal({
                        title: "Error",
                        text: "Error al cargar los subtipos de especialidad",
                        icon: "error",
                        buttons: "Aceptar",
                        DangerMode: true,
                    });
                }
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Error al cargar los subtipos de especialidad",
                    icon: "error",
                    buttons: "Aceptar",
                    DangerMode: true,
                });
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function cargar_tipos_especialidad_convenio(id_especialidad, id_tipo_especialidad = null){
        let url = "{{ route('web.profesional.buscar_tipo_especialidad') }}";
        $.ajax({
            url: url,
            type: "get",
            data:{
                id_especialidad: id_especialidad
            },
        })
        .done(function(data) {
            console.log(data);
            if (data != null)
            {
                if(data.estado == 1)
                {
                    $('#editar_especialidad_nuevo_profesional_convenio').empty();
                    $('#editar_especialidad_nuevo_profesional_convenio').append('<option value="">Seleccione el tipo de especialidad</option>');
                    data.registros.forEach(tipo => {
                        $('#editar_especialidad_nuevo_profesional_convenio').append('<option value="'+tipo.id+'">'+tipo.nombre+'</option>');
                    });

                    if(id_tipo_especialidad != null)
                        $('#editar_especialidad_nuevo_profesional_convenio').val(id_tipo_especialidad);
                }
                else
                {
                    swal({
                        title: "Error",
                        text: "Error al cargar los tipos de especialidad",
                        icon: "error",
                        buttons: "Aceptar",
                        DangerMode: true,
                    });
                }
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Error al cargar los tipos de especialidad",
                    icon: "error",
                    buttons: "Aceptar",
                    DangerMode: true,
                });
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    function cargar_subtipo_especialidad_convenio(id_tipo_especialidad, id_subtipo_especialidad = null){
        let url = "{{ route('web.profesional.buscar_sub_tipo_especialidad') }}";
        $.ajax({
            url: url,
            type: "get",
            data:{
                id_tipo_especialidad: id_tipo_especialidad
            },
        })
        .done(function(data) {
            console.log(data);
            if (data != null)
            {
                if(data.estado == 1)
                {
                    $('#editar_sub_especialidad_nuevo_profesional_convenio').empty();
                    $('#editar_sub_especialidad_nuevo_profesional_convenio').append('<option value="">Seleccione el subtipo de especialidad</option>');
                    data.registros.forEach(subtipo => {
                        $('#editar_sub_especialidad_nuevo_profesional_convenio').append('<option value="'+subtipo.id+'">'+subtipo.nombre+'</option>');
                    });

                    if(id_subtipo_especialidad != null)
                        $('#editar_sub_especialidad_nuevo_profesional_convenio').val(id_subtipo_especialidad);
                }
                else
                {
                    swal({
                        title: "Error",
                        text: "Error al cargar los subtipos de especialidad",
                        icon: "error",
                        buttons: "Aceptar",
                        DangerMode: true,
                    });
                }
            }
            else
            {
                swal({
                    title: "Error",
                    text: "Error al cargar los subtipos de especialidad",
                    icon: "error",
                    buttons: "Aceptar",
                    DangerMode: true,
                });
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }


    function registrar_nuevo_profesional(){

        let valido = 1;
        let mensaje = '';

        let id_institucion = $('#id_institucion').val();
        let id_lugar_atencion = $('#id_lugar_atencion').val();
        let id_admin_creador = $('#id_admin_creador').val();
        let id_tipo_admin_creador = $('#id_tipo_admin_creador').val();
        let tipo_contrato = $('#tipo_contrato').val();

        let rut = $('#rut_nuevo_profesional').val();
        let f_ingreso = $('#f_ingreso_nuevo_profesional').val();
        let nombre = $('#nombre_nuevo_profesional').val();
        let apellido1 = $('#apellido1_nuevo_profesional').val();
        let apellido2 = $('#apellido2_nuevo_profesional').val();
        let sexo = $('#empleado_sexo').val();
        let fecha_nacimiento = $('#fecha_nacimiento').val();
        let email = $('#email_nuevo_profesional').val();

        let fecha_inicio = $('#empleado_fecha_inicio').val();
        let fecha_termino = $('#empleado_fecha_termino').val();
        let monto_imponible = $('#empleado_monto_imponible').val();

        let locomocion = ( $('#empleado_locomocion').val() == ''?'0':$('#empleado_locomocion').val() );
        var locomocion_porcentaje = '';
        if(locomocion == 1)
            locomocion_porcentaje = $('#empleado_locomocion_porcentaje').val();
        else
            locomocion_porcentaje = '0';

        let colacion = ( $('#empleado_colacion').val() == ''?'0':$('#empleado_colacion').val() );
        var colacion_porcentaje = '';
        if(colacion == 1)
            colacion_porcentaje = $('#empleado_colacion_porcentaje').val();
        else
            colacion_porcentaje = '0';

        let asignacion_familiar = ( $('#empleado_asignacion_familiar').val() == ''?'0':$('#empleado_asignacion_familiar').val() );
        var asignacion_familiar_cantidad = '';
        if(asignacion_familiar == 1)
            asignacion_familiar_cantidad = $('#empleado_asignacion_familiar_cantidad').val();
        else
            asignacion_familiar_cantidad = '0';

        let caja_compensacion = ( $('#empleado_caja_compensacion').val() == ''?'0':$('#empleado_caja_compensacion').val() );
        var caja_compensacion_porcentaje = '';
        if(caja_compensacion == 1)
            caja_compensacion_porcentaje = $('#empleado_caja_compensacion_porcentaje').val();
        else
            caja_compensacion_porcentaje = '0';

        let telefono1 = $('#telefono1_nuevo_profesional').val();
        let telefono2 = $('#telefono2_nuevo_profesional').val();
        let direccion = $('#direccion_nuevo_profesional').val();
        let numero = $('#n_dpto_nuevo_profesional').val();
        let region = $('#region_nuevo_profesional').val();
        let comuna = $('#comuna_nuevo_profesional').val();
        let dias_laborales = $('#dias_laborales').val();
        let hora_entrada = $('#hora_entrada').val();
        let hora_salida = $('#hora_salida').val();
        let hora_entrada_colacion = $('#hora_entrada_colacion').val();
        let hora_salida_colacion = $('#hora_salida_colacion').val();
        let cargo = $('#cargo_nuevo_profesional').val();
        let profesion = $('#profesion_nuevo_profesional').val();
        let especialidad = $('#especialidad_nuevo_profesional').val();
        let sub_especialidad = $('#sub_especialidad_nuevo_profesional').val();
        let dias_atencion = $('#dias_atencion_nuevo_profesional').val();
        let horario = $('#horario_nuevo_profesional').val();
        let p_hora = $('#p_hora_nuevo_profesional').val();
        let correo_cont = $('#correo-cont').is(':checked');
        let banco = $('#banco_nuevo_profesional').val();
        let n_cta = $('#n_cta_nuevo_profesional').val();
        let sucursal = $('#sucursal_nuevo_profesional').val();



        if(rut == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el rut del profesional</li>';
        }
        if(f_ingreso == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la fecha de ingreso del profesional</li>';
        }
        if(nombre == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el nombre del profesional</li>';
        }
        if(apellido1 == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el primer apellido del profesional</li>';
        }
        if(apellido2 == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el segundo apellido del profesional</li>';
        }
        if(sexo == 0){
            valido = 0;
            mensaje += '<li>Debe seleccionar el sexo del profesional</li>';
        }
        if(fecha_nacimiento == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la fecha de nacimiento del profesional</li>';
        }
        if(email == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el correo electr&oacute;nico del profesional</li>';
        }
        if(fecha_inicio == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la fecha de inicio del contrato del profesional</li>';
        }
        if(monto_imponible == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el monto imponible del profesional</li>';
        }
        if(telefono1 == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el tel&eacute;fono del profesional</li>';
        }
        if(direccion == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la direcci&oacute;n del profesional</li>';
        }
        if(cargo == 0){
            valido = 0;
            mensaje += '<li>Debe seleccionar el cargo del profesional</li>';
        }
        if(region == 0){
            valido = 0;
            mensaje += '<li>Debe seleccionar la regi&oacute;n del profesional</li>';
        }
        if(comuna == 0){
            valido = 0;
            mensaje += '<li>Debe seleccionar la comuna del profesional</li>';
        }
        if(dias_laborales == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar los d&iacute;as laborales del profesional</li>';
        }
        if(hora_entrada == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la hora de entrada del profesional</li>';
        }
        if(hora_salida == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la hora de salida del profesional</li>';
        }
        if(hora_entrada_colacion == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la hora de entrada de colaci&oacute;n del profesional</li>';
        }
        if(hora_salida_colacion == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la hora de salida de colaci&oacute;n del profesional</li>';
        }
        if(cargo == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el cargo del profesional</li>';
        }
        if(profesion == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la profesi&oacute;n del profesional</li>';
        }
        if(especialidad == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la especialidad del profesional</li>';
        }
        if(sub_especialidad == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la sub-especialidad del profesional</li>';
        }
        if(dias_atencion == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar los d&iacute;as de atenci&oacute;n del profesional</li>';
        }
        if(horario == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el horario del profesional</li>';
        }
        if(p_hora == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la cantidad de pacientes por hora del profesional</li>';
        }
        if(banco == 0){
            valido = 0;
            mensaje += '<li>Debe seleccionar el banco para el dep&oacute;sito del profesional</li>';
        }
        if(n_cta == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar el n&uacute;mero de cuenta para el dep&oacute;sito del profesional</li>';
        }
        if(sucursal == ''){
            valido = 0;
            mensaje += '<li>Debe ingresar la sucursal para el dep&oacute;sito del profesional</li>';
        }


        if(valido == 0){
            swal({
                title: "Error",
                content:{
                    element: "div",
                    attributes: {
                        innerHTML: mensaje
                    }
                },
                icon: "error",
                buttons: "Aceptar",
                DangerMode: true,
            })
            return false;
        }


        let data = {
            _token: "{{ csrf_token() }}",
            id_institucion: id_institucion,
            id_lugar_atencion: id_lugar_atencion,
            id_admin_creador: id_admin_creador,
            id_tipo_admin_creador: id_tipo_admin_creador,
            tipo_contrato: tipo_contrato,
            rut: rut,
            f_ingreso: f_ingreso,
            nombre: nombre,
            apellido1: apellido1,
            apellido2: apellido2,
            sexo: sexo,
            fecha_nacimiento: fecha_nacimiento,
            email: email,
            fecha_inicio: fecha_inicio,
            fecha_termino: fecha_termino,
            monto_imponible: monto_imponible,
            locomocion: locomocion,
            locomocion_porcentaje: locomocion_porcentaje,
            colacion: colacion,
            colacion_porcentaje: colacion_porcentaje,
            asignacion_familiar: asignacion_familiar,
            asignacion_familiar_cantidad: asignacion_familiar_cantidad,
            caja_compensacion: caja_compensacion,
            caja_compensacion_porcentaje: caja_compensacion_porcentaje,
            telefono1: telefono1,
            telefono2: telefono2,
            direccion: direccion,
            numero: numero,
            region: region,
            comuna: comuna,
            dias_laborales: dias_laborales,
            hora_entrada: hora_entrada,
            hora_salida: hora_salida,
            hora_entrada_colacion: hora_entrada_colacion,
            hora_salida_colacion: hora_salida_colacion,
            cargo: cargo,
            profesion: profesion,
            especialidad: especialidad,
            sub_especialidad: sub_especialidad,
            dias_atencion: dias_atencion,
            horario: horario,
            p_hora: p_hora,
            correo_cont: correo_cont,
            banco: banco,
            n_cta: n_cta,
            sucursal: sucursal,
        }


        let url = "{{ route('adm_cm.registrar_profesional') }}";
        $.ajax({
            url: url,
            type: "post",
            data: data,
        })
        .done(function(data) {
             console.log(data);
            if (data != null) {
                if(data.estado == 1){
                    swal({
                        title: "Registro Exitoso",
                        text: data.message,
                        icon: "success",
                        buttons: "Aceptar",
                        DangerMode: true,
                    });
                    $('#card_body_profesionales_contratados').empty();
                    $('#card_body_profesionales_contratados').append(data.v);
                }else{
                    swal({
                        title: "Error",
                        text: data.msj,
                        icon: "error",
                        buttons: "Aceptar",
                        DangerMode: true,
                    })
                }
            } else {
                swal({
                    title: "Error",
                    text: "Error al registrar profesional",
                    icon: "error",
                    buttons: "Aceptar",
                    DangerMode: true,
                })
            }
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }

    /* Contacto de un profesional asociado sin contrato (el contacto(tipo, id) es el de asistentes) */
    function contacto_profesional_contrato(id)
    {
        let url = "{{ route('laboratorio.profesional_buscar', ['id_profesional' => '__id__']) }}";
        url = url.replace('__id__', id);

        $.ajax({
            url: url,
            type: "get",
        })
        .done(function(data) {
            console.log(data);
            if (data.estado == 1)
            {
                /** encontrado */
                $('#contacto_prof_rut').html(data.registro.rut);
                $('#contacto_prof_email').html(data.registro.email);
                $('#contacto_prof_telefono1').html(data.registro.telefono_uno);
                $('#contacto_prof_telefono2').html(data.registro.telefono_dos);
                $('#contacto_prof_direccion').html(data.direccion);
                $('#contacto_usuario').modal('show');
            }
            else
            {
                /** no encontrado */
                swal({
                    title: "Problema al cargar informacion del Profesional.",
                    icon: "error",
                });
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });

    }

    /* ===== Asistentes ===== */
    // Se recarga la página: la pestaña activa queda en la URL (?tab=asistentes) y se vuelve a abrir
    function cargar_tabla_asistentes()
    {
        location.reload();
    }
</script>
