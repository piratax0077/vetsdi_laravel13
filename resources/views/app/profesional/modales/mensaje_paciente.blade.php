<!-- Mensaje al tutor -->
  <div class="modal fade" id="modalMensajePaciente" tabindex="-1" role="dialog" aria-labelledby="modalMensajePacienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header modal-header-purple">
          <h5 class="modal-title" id="modalMensajePacienteLabel">Mensaje al tutor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm" for="exampleFormControlInput1">Asunto</label>
                        <input type="text" class="form-control form-control-sm" id="exampleFormControlInput1">
                    </div>
                </div>
                <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <label class="floating-label-activo-sm" for="exampleFormControlTextarea1">Mensaje</label>
                    <textarea class="form-control form-control-sm" id="exampleFormControlTextarea1" rows="3"></textarea>
                </div>
                <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <p class="small text-muted mb-2">Puede adjuntar hasta 5 imágenes de 6 MB cada una:</p>
                    <div class="dropzone" id="mis-archivos-paciente" action="{{ route('profesional.imagen.carga') }}"></div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                    <button type="button" class="btn btn-info" onclick="enviar_mensaje_paciente_confirmar()"><i class="feather icon-mail"></i> Enviar mensaje</button>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" id="id_paciente_mensaje" name="id_paciente_mensaje" value="">
  <script>
    var dropzoneMensajePaciente = null;
    // Imágenes ya subidas que se van a adjuntar al mensaje
    var archivosMensajePaciente = [];

    document.addEventListener('DOMContentLoaded', function(){
        Dropzone.autoDiscover = false;
        dropzoneMensajePaciente = new Dropzone('#mis-archivos-paciente', {
            url: "{{ route('profesional.imagen.carga') }}",
            method: 'post',
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
            paramName: 'file',
            acceptedFiles: 'image/jpeg,image/png,image/gif,image/webp',
            maxFiles: 5,
            maxFilesize: 6,
            addRemoveLinks: true,
            init: function(){
                this.on('success', function(file, respuesta){
                    if (typeof respuesta === 'string') {
                        try { respuesta = JSON.parse(respuesta); } catch (e) { respuesta = null; }
                    }
                    if (!respuesta || !respuesta.img) return;
                    file.nombreSubido = respuesta.img.nombre_img;
                    archivosMensajePaciente.push({
                        nombre_img: respuesta.img.nombre_img,
                        original_file_name: respuesta.img.original_file_name
                    });
                });
                this.on('removedfile', function(file){
                    archivosMensajePaciente = archivosMensajePaciente.filter(function(archivo){
                        return archivo.nombre_img !== file.nombreSubido;
                    });
                });
                // Laravel responde la validación como objeto; se deja el texto del error en la miniatura
                this.on('error', function(file, mensaje){
                    if (!mensaje || typeof mensaje === 'string' || !file.previewElement) return;
                    var texto = mensaje.errors && mensaje.errors.file ? mensaje.errors.file.join(' ') : (mensaje.message || 'No se pudo subir el archivo.');
                    $(file.previewElement).find('[data-dz-errormessage]').text(texto);
                });
            }
        });
    });

    function enviar_mensaje_paciente_confirmar(){
        var asunto = $('#exampleFormControlInput1').val();
        var mensaje = $('#exampleFormControlTextarea1').val();
        var id_paciente = $('#id_paciente_mensaje').val();
        // validar el ingreso de datos
        if(asunto == ''){
            swal({
                title: "Error",
                text: "Ingrese el asunto",
                icon: "error",
                button: "Aceptar",
            });
            return false;
        }

        if(mensaje == ''){
            swal({
                title: "Error",
                text: "Ingrese el mensaje",
                icon: "error",
                button: "Aceptar",
            });
            return false;
        }

        if(dropzoneMensajePaciente && (dropzoneMensajePaciente.getUploadingFiles().length > 0 || dropzoneMensajePaciente.getQueuedFiles().length > 0)){
            swal({
                title: "Espere un momento",
                text: "Los archivos todavía se están subiendo",
                icon: "info",
                button: "Aceptar",
            });
            return false;
        }
        $.ajax({
            url: "{{ route('enviar_mensaje_paciente') }}",
            type: 'POST',
            data: {
                asunto: asunto,
                mensaje: mensaje,
                id_paciente: id_paciente,
                archivos: archivosMensajePaciente,
                _token: "{{ csrf_token() }}"
            },
            success: function(response){
                console.log(response);
                if(response.estado == 1){
                    swal({
                        title: "Mensaje enviado",
                        text: "El mensaje ha sido enviado correctamente",
                        icon: "success",
                        button: "Aceptar",
                    });
                    $('#modalMensajePaciente').modal('hide');
                    // se limpia para que el próximo mensaje no salga con lo anterior
                    $('#exampleFormControlInput1, #exampleFormControlTextarea1').val('');
                    if(dropzoneMensajePaciente){
                        dropzoneMensajePaciente.removeAllFiles(true);
                    }
                    archivosMensajePaciente = [];
                }else{
                    swal({
                        title: "Error",
                        text: "Ocurrió un error al enviar el mensaje",
                        icon: "error",
                        button: "Aceptar",
                    });
                }
            }
        });
    }
  </script>
