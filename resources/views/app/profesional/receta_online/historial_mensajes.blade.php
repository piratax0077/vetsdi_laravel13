@extends('template.profesional.template')

@push('external-libraries')
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
@endpush
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-3 encabezado-pagina">
                    <h5 class="f-26 d-inline">Mis mensajes</h5>
                    <button class="btn btn-info btn-sm mb-1" onclick="enviar_mensaje_a_profesional()"><i class="feather icon-mail"></i> Enviar mensaje</button>
                </div>
            </div>
            <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="historial_mensajes"
                                class="display table table-striped dt-responsive nowrap table-xs"
                                style="width:100%">
                                <thead>
                                    <tr>
                                        {{--  <th class="text-wrap align-middle" hidden="hidden">Nº de Orden</th>  --}}
                                        <th class="align-middle">Fecha</th>
                                        <th class="align-middle">Remitente</th>
                                        <th class="align-middle">Título</th>
                                        <th class="align-middle">Tipo</th>
                                        <th class="align-middle">Estado</th>
                                        <th class="align-middle">Ver Mensaje</th>
                                        <th class="align-middle">Eliminar</th>
                                        <th class="align-middle">Descargar</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_mensajes_a_profesional">

                                    @if (isset($mis_mensajes) && count($mis_mensajes) > 0)
                                        @foreach ($mis_mensajes as $mensaje)
                                            <tr>
                                                {{--  <td class="text-wrap align-middle">{{ $mensaje->id }}</td>  --}}
                                                <td class="text-wrap align-middle" style="font-size:12px">
                                                    {{ preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $mensaje->created_at) }}
                                                </td>

                                                <td class="align-middle" style="font-size:12px">
                                                    {{ $mensaje->remitente }}
                                                </td>
                                                <td class="align-middle text-wrap" style="font-size:10px"><label>{{ $mensaje->datos_mensaje->mensaje }}</label></td>
                                                <td class="align-middle text-wrap" style="font-size:10px"><label>{{ $mensaje->tipo_mensaje }}</label></td>
                                                <!--<td class="align-middle text-center">
                                                    <button type="button" class="btn  btn-icon btn-success" data-toggle="tooltip" data-placement="top" title="Enviar mensaje a Paciente"><i class="feather icon-navigation"></i></button>
                                                </td>-->
                                                 <!--<td class="align-middle text-center">Enviado</td>-->


                                                <td class="align-middle"><span id="estado-{{ $mensaje->id }}">{{ $mensaje->estado }}</span></td>
                                                <td class="align-middle"> <button class="btn btn-icon btn-warning"><i class="feather icon-mail" onclick="ver_mensaje({{ $mensaje->id  }})"></i></button><!--<img src="{{ asset('images/talk.png') }}" alt="Documento" height="35px">--></div></td>
                                                <td class="align-middle"><button class="btn btn-danger btn-icon btn-icon" onclick="eliminar_mensaje({{ $mensaje->id }})"><i class="feather icon-x"></i></button></td>
                                                <td class="align-middle"><button class="btn btn-icon btn-primary" onclick="descargar_mensaje({{ $mensaje->id }})"><i class="feather icon-download"></i></button></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL A PROFESIONAL -->
    <div class="modal fade" id="modal_mensaje_a_profesional" tabindex="-1" role="dialog" aria-labelledby="modal_mensaje_a_profesional" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title">Mensaje</h5>
                    <button type="button" class="close text-white" aria-label="Cerrar" onclick="cerrar_modal_mensaje()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Lectura del mensaje con el mismo diseño del historial de mensajes -->
                    <div class="ver-mensaje">
                        <div class="ver-mensaje-icono"><i class="feather icon-mail"></i></div>
                        <div class="ver-mensaje-contenido">
                            <div class="ver-mensaje-encabezado">
                                <h6 class="ver-mensaje-titulo" id="titulo_msj"></h6>
                                <span class="ver-mensaje-fecha"><i class="feather icon-calendar"></i> <span id="fecha_msj"></span></span>
                            </div>
                            <p class="ver-mensaje-remitente"><i class="feather icon-user"></i> <span id="remitente_msj"></span></p>
                            <p class="ver-mensaje-asunto" id="asunto_msj"></p>
                            <div class="ver-mensaje-texto" id="mensaje_msj"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" onclick="cerrar_modal_mensaje();">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL MENSAJE A PROFESIONAL DE PROFESIONAL -->
    <div class="modal fade" id="modal_mensaje_a_profesional_de_profesional" tabindex="-1" role="dialog" aria-labelledby="modal_mensaje_a_profesional_de_profesional" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-purple">
                    <h5 class="modal-title">Enviar mensaje</h5>
                    <button type="button" class="close text-white" aria-label="Cerrar" onclick="cerrar_modal_mensaje_de_profesional()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Solo hay un formulario, así que va directo y sin pestañas -->
                    <div class="form-row">
                        <div class="form-group col-sm-12 col-md-4">
                            <label class="floating-label-activo-sm">Fecha</label>
                            <input type="date" class="form-control form-control-sm" id="fecha_msj_de_profesional" name="fecha_msj_de_profesional" value="<?php echo date('Y-m-d') ?>">
                        </div>
                        <div class="form-group col-sm-12 col-md-8">
                            <label class="floating-label-activo-sm">RUT del profesional</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm" name="agregar_profesional_int_rut" id="agregar_profesional_int_rut" placeholder="Ej: 12345678-9" onkeyup="enter_buscar_profesional(event)">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="agregar_profesional_btn_buscar_rut" onclick="buscar_profesional();" title="Buscar profesional"><i class="feather icon-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="floating-label-activo-sm">Remitente</label>
                            @if(isset($profesional))
                            <input type="text" class="form-control form-control-sm" id="remitente_a_profesional" name="remitente_a_profesional" value="{{  $profesional->nombre }} {{ $profesional->apellido_uno }} {{ $profesional->apellido_dos }}" disabled>
                            @else
                            <input type="text" class="form-control form-control-sm" id="remitente_a_profesional" name="remitente_a_profesional" value="{{ Auth::user()->name }}" disabled>
                            @endif
                        </div>
                        <div class="form-group col-sm-12 col-md-6">
                            <label class="floating-label-activo-sm">Receptor</label>
                            <input type="text" class="form-control form-control-sm" id="receptor_a_profesional" name="receptor_a_profesional" placeholder="Busque al profesional por su RUT" disabled>
                        </div>
                        <input type="hidden" id="id_profesional" name="id_profesional" value="">
                        <div class="form-group col-sm-12">
                            <label class="floating-label-activo-sm">Título</label>
                            <input type="text" class="form-control form-control-sm" id="titulo_msj_de_profesional" name="titulo_msj_de_profesional">
                        </div>
                        <div class="form-group col-sm-12">
                            <label class="floating-label-activo-sm">Asunto</label>
                            <input type="text" class="form-control form-control-sm" id="asunto_msj_de_profesional" name="asunto_msj_de_profesional">
                        </div>
                        <div class="form-group col-sm-12 mb-0">
                            <label class="floating-label-activo-sm">Mensaje</label>
                            <textarea class="form-control caja-texto form-control-sm" id="mensaje_msj_de_profesional" name="mensaje_msj_de_profesional" rows="5"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" onclick="cerrar_modal_mensaje_de_profesional()">Cancelar</button>
                    <button type="button" class="btn btn-info" onclick="enviar_mensaje_confirmar()"><i class="feather icon-send mr-1"></i>Enviar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
     $(document).ready(function()
        {


            $("#agregar_profesional_int_rut").rut({
                formatOn: 'keyup',
                minimumLength: 2,
                validateOn: 'change',
                useThousandsSeparator : false
            });



        });
    function ver_mensaje(id){
        // mostrar el modal
        $.ajax({
            url: "{{ URL('Profesional/ver_mensaje') }}"+"/"+id,
            type: "get",
            success: function(response){
                let mensaje = response;
                console.log(mensaje);
                // abrir modal
                $('#modal_mensaje_a_profesional').modal('show');
                // Se asigna con .text() para que el contenido no se interprete como HTML
                let datos = mensaje.datos_mensaje || {};
                $('#fecha_msj').text(mensaje.fecha_emision || '');
                $('#remitente_msj').text(mensaje.remitente || '');
                $('#titulo_msj').text(datos.titulo || 'Sin título');
                $('#asunto_msj').text(datos.asunto || '').toggle(!!datos.asunto);
                $('#mensaje_msj').text(datos.mensaje || '');
                $('#estado-'+id).text('LEIDO');
            },
            error: function(error){
                console.log(error);
            }
        });
    }

    function cerrar_modal_mensaje(){
        $('#modal_mensaje_a_profesional').modal('hide');
    }

    function cerrar_modal_mensaje_de_profesional(){
        $('#modal_mensaje_a_profesional_de_profesional').modal('hide');
    }

    function eliminar_mensaje(id){
        swal({
            title: "¿Estás seguro?",
            text: "Una vez eliminado, no podrás recuperar este mensaje",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: "{{ URL('Profesional/eliminar_mensaje') }}"+"/"+id,
                    type: "get",
                    success: function(response){
                        console.log(response);
                        if(response.estado == 1){
                            let mensajes = response.mensajes;
                            console.log(mensajes);
                            $('#tbody_mensajes_a_profesional').empty();
                            mensajes.forEach(mensaje => {
                                $('#tbody_mensajes_a_profesional').append(`
                                    <tr>
                                        <td class="text-wrap text-center align-middle" style="font-size:12px">${String(mensaje.created_at || "").replace(/(\d{2}:\d{2}):\d{2}(\.\d+)?/, "$1")}</td>
                                        <td class="align-middle" style="font-size:12px">${mensaje.remitente}</td>
                                        <td class="align-middle text-wrap" style="font-size:10px"><label>${mensaje.datos_mensaje.mensaje}</label></td>
                                        <td class="align-middle text-wrap" style="font-size:10px"><label>${mensaje.tipo_mensaje}</label></td>
                                        <td class="align-middle"><span id="estado-${mensaje.id}">${mensaje.estado}</span></td>
                                        <td class="align-middle"> <div style="cursor: pointer;" onclick="ver_mensaje(${mensaje.id})"><img src="{{ asset('images/talk.png') }}" alt="Documento" height="35px"></div></td>
                                        <td class="align-middle"><button class="btn btn-outline-danger btn-sm btn-icon" onclick="eliminar_mensaje(${mensaje.id})"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                `);
                            });
                            swal("El mensaje ha sido eliminado", {
                                icon: "success",
                            });
                            //location.reload();
                        }else{
                            swal("Error al eliminar el mensaje", {
                                icon: "error",
                            });
                        }
                    },
                    error: function(error){
                        console.log(error);
                    }
                });
            } else {
                swal("El mensaje no ha sido eliminado");
            }
        });
    }

    function descargar_mensaje(id){
        // confirmar la descarga con swal
        swal({
            title: "¿Estás seguro?",
            text: "Una vez descargado, no podrás recuperar este mensaje",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((descargar) => {
            if (descargar) {
                descargar_mensaje_confirmar(id);
            } else {
                console.log("No se descargó el mensaje");
            }
        });
    }

    function descargar_mensaje_confirmar(id) {
        $.ajax({
            url: "{{ URL('Profesional/descargar_mensaje') }}"+"/"+id,
            type: "get",
            success: function(response) {
                console.log(response);
                if (response.estado == 1) {
                    let mensaje = response.mensaje;
                    let { jsPDF } = window.jspdf;
                    let doc = new jsPDF();

                    // Añadir el mensaje al documento PDF
                    doc.text(mensaje.datos_mensaje.mensaje, 10, 10,{
                        align: "left",
                        maxWidth: 190
                    });


                    // Descargar el PDF
                    doc.save(mensaje.datos_mensaje.titulo + ".pdf");
                } else {
                    swal("Error al descargar el mensaje", {
                        icon: "error",
                    });
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function enviar_mensaje_a_profesional(){
        // abrir modale
        $('#modal_mensaje_a_profesional_de_profesional').modal('show');
    }

    function enviar_mensaje_confirmar(){
        let fecha = $('#fecha_msj_de_profesional').val();
        let remitente = $('#remitente_a_profesional').val();
        let titulo = $('#titulo_msj_de_profesional').val();
        let asunto = $('#asunto_msj_de_profesional').val();
        let msj = $('#mensaje_msj_de_profesional').val();

        let id_profesional = $('#id_profesional').val();

        let valido = 1;
        let mensaje = '';

        if(fecha == ''){
            valido = 0;
            mensaje += "<li>Debe ingresar fecha</li>";
        }
        if(remitente == ''){
            valido = 0;
            mensaje += "<li>Debe ingresar remitente</li>";
        }
        if(titulo == ''){
            valido = 0;
            mensaje += "<li>Debe ingresar título</li>";
        }
        if(asunto == ''){
            valido = 0;
            mensaje += "<li>Debe ingresar asunto</li>";
        }
        if(msj ==''){
            valido = 0;
            mensaje += "<li>Debe ingresar mensaje</li>";
        }

        if(valido == 0){
            return swal({
                title: "Campos requeridos",
                content:{
                    element: "div",
                    attributes:{
                        innerHTML: mensaje,
                    },
                },
                icon: "error",
                buttons: "Aceptar",
                DangerMode: true,
            });
        }

        let data = {
            id_profesional_mensaje: id_profesional,
            fecha: fecha,
            remitente: remitente,
            titulo: titulo,
            detalle: asunto,
            mensaje: msj,
            _token: CSRF_TOKEN,
        }

        console.log(data);

        let url = "{{ ROUTE('mensaje_profesional') }}";

        $.ajax({
            type:'post',
            url: url,
            data: data,
            success: function(resp){
                console.log(resp);
                if(resp.estado == 1){
                    swal({
                        title:'Éxito',
                        text:'Su mensaje se ha enviado con éxito.',
                        icon:'success'
                    })
                    $('#modal_mensaje_a_profesional_de_profesional').modal('hide');
                }else{
                    swal({
                        title:'Error',
                        text:'No fue posible enviar el mensaje.',
                        icon:'error'
                    })
                }
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }

    function enter_buscar_profesional(event){
        if(event.keyCode == 13)
            buscar_profesional();
    }



        function buscar_profesional(){

            let id_lugar_atencion = $('#agregar_profesional_int_id_lugar_atencion').val();

            if(id_lugar_atencion == '')
            {
                swal({
                    title: "Debe seleccionar una sucursal",
                    icon: "error",
                });
                return false;
            }

            //$('#agregar_profesional_btn_buscar_rut').attr('disabled', 'disabled');
            var rut = $('#agregar_profesional_int_rut').val();
            if(rut == ''){
                swal({
                    title: "Debe ingresar un RUT",
                    icon: "error",
                });
                return false;
            }

            // Verificar si el RUT ingresado contiene un guion
            if (!rut.includes('-')) {
                swal({
                    title: "El RUT debe contener un guion antes del dígito verificador",
                    icon: "error",
                });
                return false;
            }


            // Suponiendo que 'rut' es tu variable que contiene el RUT ingresado
            rut = rut.replace(/\./g, ''); // Limpiar el RUT de puntos
            console.log(rut);
            // agregar digito verificador


            if(!$.validateRut(rut))
            {
                swal({
                    title: "Debe ingresar un RUT válido",
                    icon: "error",
                });
                return false;
            }

            let profesional_inter = $('#profesional_inter');
            profesional_inter.find('option').remove();

            let url = "{{ route('profesional.buscar') }}";
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
                    $('#id_profesional').val(data.registros[0].id);
                    $('#receptor_a_profesional').val(data.registros[0].nombre+' '+data.registros[0].apellido_uno+' '+data.registros[0].apellido_dos);
                    /** encontrado */
                    $('#agregar_profesional_texto_ver_nombre_profesional').html(data.registros[0].profesionales_nombre+' '+data.registros[0].profesionales_apellido_uno+' '+data.registros[0].profesionales_apellido_dos);
                    $('#agregar_profesional_texto_ver_telefono').html(data.registros[0].profesional_telefono_uno);
                    $('#agregar_profesional_texto_ver_email').html(data.registros[0].profesional_email);
                    $('#agregar_profesional_texto_ver_especialidad').html(data.registros[0].especialidad);
                    $('#agregar_profesional_ver_nombre_profesional').val(data.registros[0].profesionales_nombre+' '+data.registros[0].profesionales_apellido_uno+' '+data.registros[0].profesionales_apellido_dos);
                    $('#agregar_profesional_ver_telefono').val(data.registros[0].profesional_telefono_uno);
                    $('#agregar_profesional_ver_email').val(data.registros[0].profesional_email);
                    $('#agregar_profesional_id_profesional').val(data.registros[0].id);


                    $('#div_agregar_profesional_busqueda').hide();
                    $('#div_agregar_profesional_ver_info_prof').show();
                    $('#div_agregar_profesional_formulario_nuevo_prof').hide();
                }
                else
                {
                    /** no encontrado */
                    /** REALIZAR BUSQUEDA TABLA DE PROFESIONALES EXISTENTES EXTERNOS (POR HACER) */
                    let url = "{{ route('personas.buscador') }}";
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
                            $('#agregar_profesional_nuevo_apellido_p').val( data2.registros.appaterno );
                            $('#agregar_profesional_nuevo_apellido_m').val( data2.registros.apmaterno );
                            $('#agregar_profesional_nuevo_telefono').val( '' );
                            $('#agregar_profesional_nuevo_email').val( '' );

                            $('#div_agregar_profesional_busqueda').hide();
                            $('#div_agregar_profesional_ver_info_prof').hide();
                            $('#div_agregar_profesional_formulario_nuevo_prof').show();
                        }
                        else
                        {
                            /** no encontrado */
                            $('#agregar_profesional_nuevo_nombre').val();
                            $('#agregar_profesional_nuevo_apellido_p').val();
                            $('#agregar_profesional_nuevo_apellido_m').val();
                            $('#agregar_profesional_nuevo_telefono').val();
                            $('#agregar_profesional_nuevo_email').val();

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
</script>
@endsection
