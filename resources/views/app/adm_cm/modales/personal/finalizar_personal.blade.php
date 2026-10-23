<div id="modal_finalizar_asistente" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editar_asistente_cm" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title mt-1 f-18"><i class="feather icon-user-x"></i> Finalizar personal</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="modal_finalizar_asistente_id_empleado" id="modal_finalizar_asistente_id_empleado" value="">
                <input type="hidden" name="modal_finalizar_asistente_id_contrato" id="modal_finalizar_asistente_id_contrato" value="">
                <p class="mb-1 text-muted">Persona a desvincular</p>
                <h5 class="mb-3 font-weight-bold" id="modal_finalizar_asistente_nombre"></h5>
                <div class="aviso-sin-registros aviso-sin-registros--peligro">
                    <span class="aviso-sin-registros-icono"><i class="feather icon-alert-triangle"></i></span>
                    <div>
                        <p class="aviso-sin-registros-titulo">Esta acción es definitiva</p>
                        <p class="aviso-sin-registros-texto">Se finalizará el contrato y la persona quedará desvinculada del centro. Si está seguro, presione "Finalizar / Desvincular".</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="finalizar_personal();"><i class="feather icon-user-x"></i> Finalizar / Desvincular</button>
            </div>
        </div>
    </div>
</div>

<script>
    function modal_desactivar_asistente(id, id_contrato, nombre)
    {
        $('#modal_finalizar_asistente').modal('show');
        $('#modal_finalizar_asistente_nombre').html(nombre);
        $('#modal_finalizar_asistente_id_empleado').val(id);
        $('#modal_finalizar_asistente_id_contrato').val(id_contrato);
    }

    function finalizar_personal()
    {
        let id_empleado = $('#modal_finalizar_asistente_id_empleado').val();
        let id_contrato = $('#modal_finalizar_asistente_id_contrato').val();
        let _token = CSRF_TOKEN;

        let url = "{{ route('adm_cm.personal.finalizar') }}";
        $.ajax({
            url: url,
            type: "post",
            data: {
                _token:_token,
                id_empleado:id_empleado,
                id_contrato:id_contrato,
            },
        })
        .done(function(data) {
            if (data != null)
            {
                if(data.estado == 1)
                {
                    // Se recarga para que la persona deje de aparecer en la tabla (la pestaña queda en la URL)
                    $('#modal_finalizar_asistente').modal('hide');
                    swal({
                        title: "Finalizar Contrato / Desvincular Personal",
                        text: 'Proceso Exitoso',
                        icon: "success",
                    }).then(function() { location.reload(); });
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
                        title: "Finalizar Contrato / Desvincular Personal",
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
                    title: "Finalizar Contrato / Desvincular Personal",
                    text: "Se presenteo problema en el momento, Intente de nuevo.",
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
</script>
