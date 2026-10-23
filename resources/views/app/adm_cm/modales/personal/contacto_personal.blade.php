<!--Datos de contacto-->
<div id="contacto_usuario" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="contacto_usuario_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="contacto_usuario_titulo">Informaci&oacute;n de contacto</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="ficha-profesional__datos">
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-credit-card" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">RUT</span>
                            <span class="ficha-profesional__valor" id="contacto_prof_rut"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-mail" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">Correo electr&oacute;nico</span>
                            <span class="ficha-profesional__valor" id="contacto_prof_email"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-phone" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">Tel&eacute;fono</span>
                            <span class="ficha-profesional__valor" id="contacto_prof_telefono1"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-smartphone" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">Tel&eacute;fono secundario</span>
                            <span class="ficha-profesional__valor" id="contacto_prof_telefono2"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato ficha-profesional__dato--ancho">
                        <span class="ficha-profesional__icono"><i class="feather icon-map-pin" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">Direcci&oacute;n</span>
                            <span class="ficha-profesional__valor" id="contacto_prof_direccion"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function contacto(tipo,id)
    {
        var url = "";
        if(tipo == 'profesional')
            url = "{{ url('/Profesionales/buscar') }}"+"/"+id;
        else if(tipo == 'asistente' || tipo == 'asistente publico')
            url = "{{ route('adm_cm.asistente_buscar') }}";
        else if(tipo == 'administrativo')
            url = "{{ url('/Profesionales/buscar') }}"+"/"+id;
        else if(tipo == 'limpieza')
            url = "{{ url('/Profesionales/buscar') }}"+"/"+id;
        $.ajax({
            url: url,
            type: "get",
            data: {
                id: id
            },
        })
        .done(function(data) {
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
                    title: "Problema al cargar informacion del usuario.",
                    icon: "error",
                });
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });

    }
</script>
