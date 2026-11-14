{{-- Pestaña Asistentes dentro de Personal: misma tabla y botones de la página Administrador/Personal --}}
{{-- Igual que en Administrador/Personal, el administrador médico no registra empleados --}}
@php($esAdministradorMedico = Auth::user()->roles()->where('name', 'AdministradorMedico')->exists())
<!--Tab asistentes-->
<div class="tab-pane fade" id="asistentes" role="tabpanel" aria-labelledby="asistentes-tab">
    <div class="row mb-n4">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header encabezado-card">
                    <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-users"></i></span>Asistentes</h5>
                    <div class="encabezado-card-acciones">
                        <div class="btn-group">
                        @if(!$esAdministradorMedico)
                        <button type="button" class="btn btn-info btn-sm" onclick="registrar_personal();"><i class="feather icon-plus"></i> Registrar nuevo/a empleado/a</button>
                        <button type="button" class="btn btn-info btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>
                        @endif
                        <div class="dropdown-menu">
                        <button class="dropdown-item" type="button" onclick="Asociar_personal();">Asociar asistente</button>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="contenedor_asistentes_personal">
                    <table id="asistentes_personal" class="display table table-striped dt-responsive nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th class="align-middle">Nombre / RUT</th>
                                <th class="align-middle">Tipo</th>
                                <th class="align-middle">Sucursales</th>
                                <th class="align-middle">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($lista_asistente)
                                @foreach ( $lista_asistente as $asistente)
                                <tr>
                                    <td class="align-middle">
                                        <span><strong>{{ $asistente->nombres.' '.$asistente->apellido_uno.' '.$asistente->apellido_dos }}</strong></span><br>
                                        <span>{{ rut_formatear($asistente->rut) ?? $asistente->rut }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span><strong>{{ $asistente->asistente_tipo->nombre }}</strong></span>
                                    </td>
                                    <td class="align-middle">
                                        {{ $asistente->direccion()->first()->direccion }} #{{ $asistente->direccion()->first()->numero_dir }}, {{ $asistente->direccion()->first()->ciudad()->first()->nombre }}
                                    </td>
                                    <td class="align-middle">
                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-info btn-sm btn-icon" onclick="contacto('asistente publico',{{ $asistente->id }});" data-toggle="tooltip" data-placement="top" title="Contacto"><i class="feather icon-phone"></i></button>

                                        <!--Botón Modal: autorizaciones de este asistente-->
                                        <button type="button" class="btn btn-info btn-sm btn-icon" onclick="abrir_autorizaciones_asistente({{ $asistente->id }});" data-toggle="tooltip" data-placement="top" title="Autorizaciones"><i class="feather icon-shield"></i></button>

                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datos_depositos('asistente publico',{{ $asistente->id_usuario }});" data-toggle="tooltip" data-placement="top" title="Datos bancarios"><i class="feather icon-credit-card"></i></button>
                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-purple btn-sm btn-icon" onclick="horario_profesional_cm('{{ $asistente->asistente_tipo->nombre }}',{{ $asistente->id }}, {{ $institucion->id_lugar_atencion }});" data-toggle="tooltip" data-placement="top" title="Horario y días de atención"><i class="feather icon-clock"></i></button>
                                 
                                        <!--Botón Modal-->
                                        <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="roles_permisos({{ $asistente->asistente_tipo->id }}, {{ $asistente->id_usuario }}, '{{ $asistente->roles }}');" data-toggle="tooltip" data-placement="top" title="Roles y permisos"><i class="feather icon-settings"></i></button>
                            
                                        {{-- Editar y Desvincular con el mismo botón redondo del resto de las pestañas --}}
                                        <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="editar_datos_asistente({{ $asistente->id }});" data-toggle="tooltip" data-placement="top" title="Editar"><i class="feather icon-edit"></i></button>
                                        @if($asistente->contrato !== null)
                                        <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="modal_desactivar_asistente({{ $asistente->id}}, {{ $asistente->contrato->id }}, '{{ $asistente->nombres.' '.$asistente->apellido_uno.' '.$asistente->apellido_dos }}');" data-toggle="tooltip" data-placement="top" title="Desvincular"><i class="feather icon-x"></i></button>
                                        @endif
                                    </td>
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

<!--Cierre: Tab asistentes-->

<!--Modal Autorizaciones de un asistente-->
<div id="modal_autorizaciones_asistente" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_autorizaciones_asistente_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="modal_autorizaciones_asistente_titulo">Autorizaciones del asistente</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="cuerpo_autorizaciones_asistente"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-info" id="guardar_autorizaciones_asistente" onclick="guardar_autorizaciones_asistente();"><i class="feather icon-save mr-1"></i>Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Abre el modal y carga el formulario de autorizaciones del asistente elegido
    function abrir_autorizaciones_asistente(id_asistente) {
        $('#cuerpo_autorizaciones_asistente').html('<p class="text-muted mb-0">Cargando...</p>');
        $('#guardar_autorizaciones_asistente').prop('disabled', true);
        $('#modal_autorizaciones_asistente').modal('show');
        cargar_autorizaciones_asistente(id_asistente, '');
    }

    // ambito llega como "lugar|profesional"; vacío carga el primero disponible
    function cargar_autorizaciones_asistente(id_asistente, ambito) {
        var partes = (ambito || '').split('|');
        $.ajax({
            url: "{{ url('Autorizaciones/Asistentes/asistente') }}/" + id_asistente,
            type: 'GET',
            data: { id_lugar_atencion: partes[0] || '', id_profesional: partes[1] || '' },
            success: function (html) {
                $('#cuerpo_autorizaciones_asistente').html(html);
                $('#guardar_autorizaciones_asistente').prop('disabled', false);
            },
            error: function (xhr) {
                var mensaje = (xhr.responseJSON && xhr.responseJSON.message) || 'No fue posible cargar las autorizaciones.';
                $('#cuerpo_autorizaciones_asistente').html('<div class="alert alert-danger mb-0">' + $('<div>').text(mensaje).html() + '</div>');
            }
        });
    }

    function guardar_autorizaciones_asistente() {
        var boton = $('#guardar_autorizaciones_asistente').prop('disabled', true);
        $.ajax({
            url: "{{ route('asistentes.autorizaciones.guardar') }}",
            type: 'POST',
            dataType: 'json',
            data: $('#form_autorizaciones_asistente').serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function (resp) {
                $('#modal_autorizaciones_asistente').modal('hide');
                swal({ title: 'Autorizaciones', text: resp.mensaje, icon: 'success', buttons: 'Aceptar' });
            },
            error: function (xhr) {
                var mensaje = (xhr.responseJSON && xhr.responseJSON.message) || 'No fue posible guardar las autorizaciones.';
                swal({ title: 'Error', text: mensaje, icon: 'error', buttons: 'Aceptar' });
            },
            complete: function () { boton.prop('disabled', false); }
        });
    }

    function quitar_politica_autorizaciones_asistente(id_asistente, id_lugar) {
        $.ajax({
            url: "{{ route('asistentes.autorizaciones.quitar_politica') }}",
            type: 'POST',
            dataType: 'json',
            data: { _method: 'DELETE', _token: $('meta[name="csrf-token"]').attr('content'), id_asistente: id_asistente, id_lugar_atencion: id_lugar },
            success: function (resp) {
                swal({ title: 'Autorizaciones', text: resp.mensaje, icon: 'success', buttons: 'Aceptar' });
                cargar_autorizaciones_asistente(id_asistente, id_lugar + '|0');
            },
            error: function (xhr) {
                var mensaje = (xhr.responseJSON && xhr.responseJSON.message) || 'No fue posible realizar el cambio.';
                swal({ title: 'Error', text: mensaje, icon: 'error', buttons: 'Aceptar' });
            }
        });
    }
</script>
