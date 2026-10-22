<div id="mensaje_profesional" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mensaje_profesional_titulo" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header modal-header-purple">
				<h5 class="modal-title d-inline mt-1" id="mensaje_profesional_titulo">Mensaje al profesional</h5>
				<button type="button" class="close text-white" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
                <div class="form-row">
                    <div class="form-group fill col-md-6">
                        <label class="floating-label-activo-sm" for="de">De</label>
                        <input type="text" class="form-control form-control-sm" id="de" name="de" value="{{ Auth::user()->name }}" readonly>
                    </div>
                    <div class="form-group fill col-md-6">
                        <label class="floating-label-activo-sm" for="para_destino">Para</label>
                        <input type="text" class="form-control form-control-sm" id="para_destino" name="para_destino" value="" readonly>
                    </div>
                </div>
				<div class="form-group fill">
					<label class="floating-label-activo-sm" for="titulo_a_profesional">Título</label>
					<input type="text" class="form-control form-control-sm" id="titulo_a_profesional" name="titulo_a_profesional" required>
				</div>
				<div class="form-group fill">
					<label class="floating-label-activo-sm" for="detalle_a_profesional">Asunto</label>
					<textarea class="form-control form-control-sm" rows="2" id="detalle_a_profesional" name="detalle_a_profesional" required></textarea>
				</div>
				<div class="form-group fill">
					<label class="floating-label-activo-sm" for="mensaje_a_profesional">Mensaje</label>
					<textarea class="form-control form-control-sm" rows="5" id="mensaje_a_profesional" name="mensaje_a_profesional" required></textarea>
				</div>
                <div class="form-group mb-0">
                    <label class="mensaje-profesional-adjunto-titulo">Adjuntar archivo</label>
                    <div class="dropzone" id="mis-archivos-a-profesional" action="{{ route('profesional.archivo.carga') }}">
                        <div class="dz-message">
                            <i class="feather icon-upload-cloud"></i>
                            <span>Arrastre aquí sus archivos o haga clic para seleccionarlos</span>
                        </div>
                    </div>
                </div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-dark" onclick="$(this).closest('.modal').modal('hide');">Cerrar</button>
				<button type="button" id="submit-all-profesional" class="btn btn-info" onclick="enviar_mensaje_a_profesional()"><i class="feather icon-message-square mr-1"></i>Enviar</button>
			</div>
		</div>
	</div>
</div>

<script>
    function enviar_mensaje_a_profesional(){
        var de = $('#de').val();
        var para = $('#para_destino').val();
        var titulo = $('#titulo_a_profesional').val();
        var detalle = $('#detalle_a_profesional').val();
        var mensaje = $('#mensaje_a_profesional').val();

        var valido = 1;
        var msj = '';

        if(titulo == ''){
            valido = 0;
            msj += 'Debe ingresar un título <br>';
        }

        if(detalle == ''){
            valido = 0;
            msj += 'Debe ingresar un asunto <br>';
        }

        if(mensaje == ''){
            valido = 0;
            msj += 'Debe ingresar un mensaje <br>';
        }

        if (valido == 0) {
            swal({
                title: "Error",
                content: {
                    element: "div",
                    attributes: {
                        innerHTML: msj
                    },
                },
                icon: "error",
            });
            return;
        }

        // validar que al menos exista un archivo
        var files = $('#mis-archivos-a-profesional').get(0).dropzone.files;

        if(files.length == 0){
            swal({
                title: "Error",
                text: "Debe adjuntar al menos un archivo",
                icon: "error",
            });
            return;
        }
        $.ajax({
            url: "{{ route('mensaje_profesional') }}",
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                de: de,
                para: para,
                titulo: titulo,
                detalle: detalle,
                mensaje: mensaje,
                id_profesional_mensaje: $('#id_profesional_mensaje').val(),
            },
            success: function(response){
                console.log(response);
                if(response.estado == 1){
                    swal({
                        title: "Mensaje Enviado",
                        text: "El mensaje ha sido enviado correctamente",
                        icon: "success",
                    })
                    $('#mensaje_profesional').modal('hide');
                }else{
                    swal({
                        title: "Error",
                        text: "Ha ocurrido un error al enviar el mensaje",
                        icon: "error",
                    })
                }
            }
        });
    }
</script>
