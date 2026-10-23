{{-- Funciones de la página Contratos (antes pestaña dentro de Personal) --}}
<script>
    $(document).ready(function() {
        $("#agregar_profesional_int_rut").rut({
            formatOn: 'keyup',
            minimumLength: 2,
            validateOn: 'change',
            useThousandsSeparator : false
        });
        $(".js-example-basic-multiple").select2();
    });

    /* ===== Contratos ===== */

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
                    // La respuesta no trae la tabla armada, así que se recarga para verla con el contrato nuevo
                    $('#registrar_contratoprofesional').modal('hide');
                    swal({
                        title: "Registro exitoso",
                        text: data.msj,
                        icon: "success",
                        buttons: "Aceptar",
                    }).then(function() { location.reload(); });
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
                $('#contacto_prof_rut').text(data.registro.rut || 'No informado');
                $('#contacto_prof_email').text(data.registro.email || 'No informado');
                $('#contacto_prof_telefono1').text(data.registro.telefono_uno || 'No informado');
                $('#contacto_prof_telefono2').text(data.registro.telefono_dos || 'No informado');
                $('#contacto_prof_direccion').text(data.direccion || 'No informado');
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

</script>
