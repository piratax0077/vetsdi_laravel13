<div id="permisos_rol" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="permisos_rol_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="permisos_rol_titulo">Permisos del asistente</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <input type="hidden" name="permisos_rol_id" id="permisos_rol_id" value="">
            </div>
            <div class="modal-body">
                <!--Lista de tipos: el asistente queda con uno solo activo-->
                <div id="permisos_rol_lista">
                    <p class="text-muted mb-3">Elige el tipo de asistente. Solo puede tener uno activo; al activar otro, el anterior se desactiva.</p>
                    <div class="form-row mb-n2">
                        @if(isset($lista_tipo_asistente))
                            @foreach ($lista_tipo_asistente as $tipo_asistente)
                                <div class="col-md-6 mb-2">
                                    <div class="ficha-profesional__dato py-2 h-100">
                                        <div class="flex-grow-1">
                                            <span class="ficha-profesional__valor">{{ $tipo_asistente->nombre }}</span>
                                        </div>
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" data-rol="{{ str_replace([' ', 'Publico', 'Consulta','Administrativo', 'ManejodeAgenda'], ['', 'Caja','','Adm', 'ManejoAgenda'], $tipo_asistente->nombre) }}" data-id="{{ $tipo_asistente->id }}" data-id_tipo_movimiento="Asistente" data-nombre="{{ $tipo_asistente->nombre }}" id="rol_permiso_{{ $tipo_asistente->id }}" onchange="confirmar_modificar_rol({{ $tipo_asistente->id }}, 'Asistente', 'rol_permiso_{{ $tipo_asistente->id }}' )">
                                            <label class="custom-control-label" for="rol_permiso_{{ $tipo_asistente->id }}"><span class="sr-only">{{ $tipo_asistente->nombre }}</span></label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!--Confirmación del cambio: aparece en el mismo modal, en vez de abrir otro encima-->
                <div id="permisos_rol_aviso" class="d-none">
                    <input type="hidden" name="permisos_rol_confirmacion_id_tipo" id="permisos_rol_confirmacion_id_tipo" value="">
                    <input type="hidden" name="permisos_rol_confirmacion_id_tipo_movimiento" id="permisos_rol_confirmacion_id_tipo_movimiento" value="">
                    <input type="hidden" name="permisos_rol_confirmacion_id_checkbox" id="permisos_rol_confirmacion_id_checkbox" value="">
                    <div class="aviso-sin-registros">
                        <span class="aviso-sin-registros-icono"><i class="feather icon-alert-circle"></i></span>
                        <div>
                            <p class="aviso-sin-registros-titulo">&iquest;Confirmas el cambio de tipo de usuario?</p>
                            <p class="aviso-sin-registros-texto" id="permisos_rol_aviso_texto"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="permisos_rol_botones_lista">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cerrar</button>
                </div>
                <div id="permisos_rol_botones_aviso" class="d-none">
                    <button type="button" class="btn btn-outline-dark" onclick="cancelar_modificacion($('#permisos_rol_confirmacion_id_checkbox').val())">Cancelar</button>
                    <button type="button" class="btn btn-info" onclick="confirmar_modificacion($('#permisos_rol_confirmacion_id_tipo').val(), $('#permisos_rol_confirmacion_id_tipo_movimiento').val(), $('#permisos_rol_confirmacion_id_checkbox').val());">Continuar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    function roles_permisos(tipo, id_asistente, roles)
    {
        var mensaje = '';
        if(tipo == '')
        {
            mensaje = 'Rol Actual del Asistente requerido.';
             swal({
                title: "Campos requeridos",
                text: mensaje,
                icon: "error",
                buttons: "Aceptar",
                DangerMode: true,
            });
            return false;
        }
        if(id_asistente == '')
        {
            mensaje = 'ID Asistente requerido';
             swal({
                title: "Campos requeridos",
                text: mensaje,
                icon: "error",
                buttons: "Aceptar",
                DangerMode: true,
            });
            return false;
        }

        var temp = roles.split(',');

        /** limpieza */
        $('#permisos_rol').find('input,textarea,select,checkbox').each(function(key, element){
            if($(element).attr('type') == 'checkbox')
            {
                $(element).prop('checked', '');
            }
            else if($(element).attr('type') == 'hidden')
            {
                $(element).val('');
            }
        });

        /** activar */
        $('#permisos_rol').find('input,textarea,select,checkbox').each(function(key, element){
            if($(element).attr('type') == 'checkbox')
            {
                temp.forEach(element2 => {
                    if(element2 == $(element).attr('data-rol'))
                        $(element).prop('checked', true);
                });
            }
        });

        permisos_rol_mostrar_lista();
        $('#permisos_rol').modal('show');
        $('#permisos_rol_id').val(id_asistente);
    }

    // Alterna entre la lista de tipos y la confirmación dentro del mismo modal
    function permisos_rol_mostrar_lista()
    {
        $('#permisos_rol_lista, #permisos_rol_botones_lista').removeClass('d-none');
        $('#permisos_rol_aviso, #permisos_rol_botones_aviso').addClass('d-none');
    }

    function permisos_rol_mostrar_aviso()
    {
        $('#permisos_rol_lista, #permisos_rol_botones_lista').addClass('d-none');
        $('#permisos_rol_aviso, #permisos_rol_botones_aviso').removeClass('d-none');
    }

    function modificar_rol(id_tipo, id_tipo_movimiento, id_checkbox )
    {
        permisos_rol_mostrar_lista();
        let _token = CSRF_TOKEN;
        let url = "{{ route('adm_cm.personal.asistente.actualizar.rol') }}";

        var movimiento = 0;
        if($('#'+id_checkbox).prop('checked'))
            movimiento = 1;
        else
            movimiento = 0;


        $.ajax({
            url: url,
            type: "POST",
            data: {
                _token: _token,
                id_user : $('#permisos_rol_id').val(),
                id_tipo : $('#'+id_checkbox).attr('data-rol'),
                id_tipo_movimiento : id_tipo_movimiento,
                movimiento : movimiento,
            },
        })
        .done(function(data) {
            console.log(data);
            if (data != null) {
                if(data.estado == 1)
                {
                    swal({
                        title: "Actualización de permiso",
                        text: 'Registro exitoso.',
                        icon: "success",
                        buttons: "Aceptar",
                    });
                    $('#permisos_rol').modal('hide');
                    cargar_tabla_asistentes();
                }
                else
                {
                    var mensaje = '';
                    if(data.error)
                    {
                        $.each(data.error, function (indexInArray, valueOfElement)
                        {
                            mensaje += valueOfElement+'\n';
                        });
                    }
                    else
                    {
                        mensaje += 'Intente nuevamente.';
                    }

                    swal({
                        title: "Actualización de permiso",
                        text: mensaje,
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
                    text: "Error al cargar ingresar personal",
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

    function confirmar_modificar_rol( id_tipo, id_tipo_movimiento, id_checkbox )
    {
        $('#permisos_rol_confirmacion_id_tipo').val(id_tipo);
        $('#permisos_rol_confirmacion_id_tipo_movimiento').val(id_tipo_movimiento);
        $('#permisos_rol_confirmacion_id_checkbox').val(id_checkbox);

        let check = $('#'+id_checkbox);
        let nombre = $('<strong>').text(check.attr('data-nombre'));
        let texto = $('#permisos_rol_aviso_texto').empty();
        if(check.prop('checked'))
            texto.append('El asistente quedará como ', nombre, '. Si tenía otro tipo activo, se desactivará.');
        else
            texto.append('Se quitará el tipo ', nombre, ' al asistente.');

        permisos_rol_mostrar_aviso();
    }

    function cancelar_modificacion(id_checkbox)
    {
        if( $('#'+id_checkbox).prop('checked') )
        {
            $('#'+id_checkbox).prop('checked', false);
        }
        else
        {
            $('#'+id_checkbox).prop('checked', true);
        }
        permisos_rol_mostrar_lista();
    }

    function confirmar_modificacion( id_tipo, id_tipo_movimiento, id_checkbox )
    {
        $('#permisos_rol .modal-body').find('input').each(function(key, element){
            if( $(element).attr('type') == 'checkbox')
            {
                if(id_checkbox != $(element).attr('id') )
                {
                    if( $(element).prop('checked') )
                    {
                        var id_tipo2 = $(element).attr('data-id');
                        var id_tipo_movimiento2 = $(element).attr('data-id_tipo_movimiento');
                        var id_checkbox2 = $(element).attr('id');

                        $(element).prop('checked', false);
                        modificar_rol(id_tipo2, id_tipo_movimiento2, id_checkbox2 );
                    }
                }
            }
        });

        modificar_rol(id_tipo, id_tipo_movimiento, id_checkbox );
    }

    // Si se cierra el modal sin confirmar, el switch vuelve a como estaba
    $(document).on('hidden.bs.modal', '#permisos_rol', function () {
        if(!$('#permisos_rol_aviso').hasClass('d-none'))
            cancelar_modificacion($('#permisos_rol_confirmacion_id_checkbox').val());
    });

</script>
