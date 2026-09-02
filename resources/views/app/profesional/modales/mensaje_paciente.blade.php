<style>
  #modalMensajePaciente .modal-content {
    border: 0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15, 48, 57, .3);
  }
  #modalMensajePaciente .modal-header {
    align-items: center;
    padding: 15px 20px;
    color: #fff;
    background: linear-gradient(120deg, #147a70, #12b8b6);
    border: 0;
  }
  #modalMensajePaciente .modal-title { font-weight: 700; }
  #modalMensajePaciente .modal-close-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    margin: -4px -4px -4px 14px;
    padding: 0;
    color: #fff;
    font-size: 27px;
    line-height: 1;
    background: rgba(0, 71, 72, .42);
    border: 0;
    border-radius: 50%;
    cursor: pointer;
  }
  #modalMensajePaciente .modal-close-button:hover,
  #modalMensajePaciente .modal-close-button:focus {
    color: #fff;
    background: rgba(0, 55, 58, .68);
    outline: 0;
  }
</style>

<!-- Modal -->
  <div class="modal fade" id="modalMensajePaciente" tabindex="-1" aria-labelledby="modalMensajePacienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalMensajePacienteLabel">Mensaje a paciente</h5>
          <button type="button" class="modal-close-button" data-dismiss="modal" aria-label="Cerrar">
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
                    <!-- [ Main Content ] start -->
                    <div class="dropzone" id="mis-archivos-paciente" action="{{ route('profesional.imagen.carga') }}">
                    </div>
                    <!-- [ file-upload ] end -->
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
    document.addEventListener('DOMContentLoaded', function(){
        // Dropzone
        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone(".dropzone", {
            url: "{{ route('profesional.imagen.carga') }}",
            addRemoveLinks: true,
            uploadMultiple: true,
            parallelUploads: 5,
            maxFiles: 5,
            maxFilesize: 1,
            acceptedFiles: "image/*",
            autoProcessQueue: false,
            dictDefaultMessage: '<div class="text-center"><i class="fas fa-cloud-upload-alt fa-4x"></i><h3>Arrastra aquí tus archivos</h3></div>',
            dictRemoveFile: 'Eliminar',
            dictCancelUpload: 'Cancelar',
            dictMaxFilesExceeded: 'No puedes subir más archivos',
            dictFileTooBig: 'El archivo es muy grande',
            dictInvalidFileType: 'Tipo de archivo no permitido',
            dictResponseError: 'Error al subir el archivo',
            dictCancelUploadConfirmation: '¿Estás seguro de cancelar la subida del archivo?',
            dictRemoveFileConfirmation: '¿Estás seguro de eliminar el archivo?',
            init: function(){
                var submitButton = document.querySelector("#submit-all");
                myDropzone = this;
                submitButton.addEventListener("click", function(){
                    myDropzone.processQueue();
                });
                this.on("addedfile", function(file){
                    console.log('file', file);
                });
                this.on("complete", function(file){
                    console.log('complete', file);
                });
                this.on("success", function(file, response){
                    console.log('success', response);
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
        $.ajax({
            url: "{{ route('enviar_mensaje_paciente') }}",
            type: 'POST',
            data: {
                asunto: asunto,
                mensaje: mensaje,
                id_paciente: id_paciente,
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
