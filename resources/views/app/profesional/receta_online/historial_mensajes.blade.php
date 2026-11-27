@extends('template.profesional.template')

@push('external-libraries')
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
@endpush
@section('content')
    @php
        // Mismos datos que usa la bandeja del tutor, del más nuevo al más antiguo
        $mensajesProfesional = collect($mis_mensajes ?? []);
        $remitentesBandeja = \App\Models\User::whereIn('id', $mensajesProfesional->pluck('id_usuario')->filter()->unique())->get()->keyBy('id');

        $mensajesBandeja = $mensajesProfesional
            ->sortByDesc(fn ($mensaje) => (string) $mensaje->fechaConHora())
            ->map(function ($mensaje) use ($remitentesBandeja) {
                $datos = $mensaje->datos_mensaje;
                $fecha = $mensaje->fechaConHora();
                $titulo = trim((string) data_get($datos, 'titulo', ''));
                $asunto = trim((string) data_get($datos, 'asunto', ''));

                return [
                    'id' => $mensaje->id,
                    'remitente' => $mensaje->remitente ?: 'VetSDI',
                    'foto' => \App\Models\Mensajes::fotoDelRemitente($remitentesBandeja->get($mensaje->id_usuario)),
                    'asunto' => $titulo ?: ($asunto ?: 'Sin título'),
                    'resumen' => (string) data_get($datos, 'mensaje', ''),
                    'tipo' => $mensaje->tipo_mensaje === 'DIRECTO' ? 'Mensaje directo' : 'Mensaje de difusión',
                    'lugar' => trim((string) data_get($datos, 'lugar', '')),
                    'fecha' => $fecha ? $fecha->format('d-m-Y H:i') : '',
                    'leido' => $mensaje->estado !== 'NO LEIDO',
                ];
            })
            ->values();
    @endphp
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            @if ($mensajesBandeja->isEmpty())
                <div class="card">
                    <div class="card-header encabezado-card">
                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-mail"></i></span>Mensajes</h5>
                        <div class="encabezado-card-acciones">
                            <button type="button" class="btn btn-info btn-sm" onclick="enviar_mensaje_a_profesional()"><i class="feather icon-edit"></i> Enviar mensaje</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="aviso-sin-registros">
                            <span class="aviso-sin-registros-icono"><i class="feather icon-inbox"></i></span>
                            <div>
                                <p class="aviso-sin-registros-titulo">No tienes mensajes</p>
                                <p class="aviso-sin-registros-texto">Cuando otro profesional o tu clínica te escriba, lo verás aquí y en el ícono de sobre de arriba.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @include('template.partials.bandeja_mensajes', [
                    'mensajesBandeja' => $mensajesBandeja,
                    'sinLeerBandeja' => $mensajesBandeja->where('leido', false)->count(),
                    'descripcionBandeja' => 'Mensajes de tu clínica y de otros profesionales.',
                    'botonNuevoBandeja' => 'Enviar mensaje',
                    'accionesBandeja' => true,
                ])
            @endif
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
<script src="{{ asset('js/bandeja_mensajes.js') }}?v={{ filemtime(public_path('js/bandeja_mensajes.js')) }}"></script>
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

    // Lectura del mensaje en el panel derecho de la bandeja
    var bandejaMensajes = BandejaMensajes.iniciar({
        cargar: function (id) {
            return $.get("{{ URL('Profesional/ver_mensaje') }}" + "/" + id).then(function (mensaje) {
                if (!mensaje || typeof mensaje !== 'object') {
                    return $.Deferred().reject().promise();
                }

                var datos = mensaje.datos_mensaje || {};
                var titulo = $.trim(datos.titulo || '');
                var asunto = $.trim(datos.asunto || '');

                return {
                    // El asunto va bajo el título solo si dice algo distinto
                    subtitulo: titulo && asunto !== titulo ? asunto : '',
                    texto: datos.mensaje,
                    archivos: (datos.archivos || []).map(function (archivo) {
                        return { nombre: archivo.nombre_original || archivo.nombre || 'Archivo adjunto', url: archivo.url };
                    })
                };
            });
        }
    });

    $('#bandeja_mensajes').on('click', '[data-accion="nuevo-mensaje"]', function () {
        enviar_mensaje_a_profesional();
    });

    $('#bandeja_mensajes').on('click', '[data-accion="descargar"]', function () {
        if (bandejaMensajes && bandejaMensajes.abierto()) {
            descargar_mensaje(bandejaMensajes.abierto());
        }
    });

    $('#bandeja_mensajes').on('click', '[data-accion="eliminar"]', function () {
        if (bandejaMensajes && bandejaMensajes.abierto()) {
            eliminar_mensaje(bandejaMensajes.abierto());
        }
    });

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
                            if (bandejaMensajes) {
                                bandejaMensajes.quitar(id);
                            }
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
