<!--Mensaje de difusión: mismo diseño que "Mensaje al profesional"-->
<div id="mensaje_difusion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mensaje_difusion_titulo" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header modal-header-purple">
				<h5 class="modal-title d-inline mt-1" id="mensaje_difusion_titulo">Mensaje de difusión</h5>
				<button type="button" class="close text-white" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
                <form id="mensajeDifusionForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <div class="form-group fill col-md-5">
                            <label class="floating-label-activo-sm" for="de_difusion">De</label>
                            <input type="text" class="form-control form-control-sm" id="de_difusion" name="de_difusion" value="{{ Auth::user()->name }}" readonly>
                        </div>
                        <div class="form-group fill col-md-7">
                            <label class="floating-label-activo-sm" for="msj_para_difusion">Para</label>
                            <select class="form-control form-control-sm" name="msj_para_difusion" id="msj_para_difusion" multiple="multiple">
                                <option value="0">A todos los roles</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}">{{ $rol->alias }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group fill">
                        <label class="floating-label-activo-sm" for="titulo_msj_difusion">Título</label>
                        <input type="text" class="form-control form-control-sm" id="titulo_msj_difusion" name="titulo_msj_difusion" required>
                    </div>
                    <div class="form-group fill">
                        <label class="floating-label-activo-sm" for="detalle_msj_difusion">Asunto</label>
                        <textarea class="form-control form-control-sm" rows="2" id="detalle_msj_difusion" name="detalle_msj_difusion" required></textarea>
                    </div>
                    <div class="form-group fill">
                        <label class="floating-label-activo-sm" for="mensaje_msj_difusion">Mensaje</label>
                        <textarea class="form-control form-control-sm" rows="5" id="mensaje_msj_difusion" name="mensaje_msj_difusion" required></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="mensaje-profesional-adjunto-titulo">Adjuntar archivo</label>
                        <div class="dropzone" id="misArchivosGes" action="{{ route('profesional.archivo.carga') }}">
                            <div class="dz-message">
                                <i class="feather icon-upload-cloud"></i>
                                <span>Arrastre aquí sus archivos o haga clic para seleccionarlos</span>
                            </div>
                        </div>
                    </div>

                    <script>
                        var myDropzone_ges; // Define la variable globalmente
                        document.addEventListener("DOMContentLoaded", function() {
                            // Verifica si Dropzone ya está inicializado en el elemento
                            if (Dropzone.instances.length > 0) {
                                Dropzone.instances.forEach(instance => {
                                    if (instance.element.id === "misArchivosGes") {
                                        instance.destroy();
                                    }
                                });
                            }
                            // Inicializa Dropzone en el elemento con el ID "misArchivosGes"
                            myDropzone_ges = new Dropzone("#misArchivosGes", {
                                url: "{{ route('profesional.archivo.carga') }}",
                                method: 'post',
                                createImageThumbnails: true,
                                addRemoveLinks: true,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                },
                                acceptedFiles: "application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*",
                                maxFilesize: 4, // Tamaño máximo en MiB
                                maxFiles: 4,
                                dictDefaultMessage: "Arrastre Archivo al recuadro para subirlo.",
                                dictFallbackMessage: "Su navegador no admite la carga de archivos mediante arrastrar y soltar.",
                                dictFallbackText: "Utilice el formulario alternativo a continuación para cargar sus archivos como en los viejos tiempos.",
                                dictFileTooBig: "El archivo es demasiado grande. Max tamaño de archivo: 4 MiB.",
                                dictInvalidFileType: "No puedes subir archivos de este tipo.",
                                dictCancelUpload: "Cancelar carga",
                                dictUploadCanceled: "Subida cancelada.",
                                dictCancelUploadConfirmation: "¿Está seguro de que desea cancelar esta carga?",
                                dictRemoveFile: "Eliminar archivo",
                                dictMaxFilesExceeded: "No puede cargar más archivos.",
                                autoProcessQueue: false, // Desactiva el procesamiento automático
                                init: function() {
                                    this.on("sending", function(file, xhr, formData) {
                                        formData.append("id", "{{ Auth::user()->id }}");
                                    });
                                    this.on("success", function(file, response) {
                                        // Manejar la respuesta de éxito
                                        console.log(response);
                                    });
                                    this.on("error", function(file, message) {
                                        // Manejar el error
                                        console.error(message);
                                    });
                                    this.on("removedfile", function(file) {
                                        // Manejar la eliminación del archivo
                                        console.log("Archivo eliminado");
                                    });
                                    this.on("canceled", function(file) {
                                        // Manejar la cancelación de la carga
                                        console.log("Carga cancelada");
                                    });
                                }
                            });

                            window.enviarMensajeDifusion = function() {
                                var formData = new FormData(document.getElementById("mensajeDifusionForm"));
                                myDropzone_ges.getAcceptedFiles().forEach(function(file) {
                                    formData.append('misArchivosGes[]', file);
                                });

                                formData.append('_token', '{{ csrf_token() }}');
                                formData.append('de', document.getElementById('de').value);
                                formData.append('para', document.getElementById('para').value);
                                formData.append('titulo', document.getElementById('titulo').value);
                                formData.append('detalle', document.getElementById('detalle').value);
                                formData.append('mensaje', document.getElementById('mensaje').value);

                                fetch("{{ route('mensaje_difusion') }}", {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    console.log(data);
                                    // Manejar la respuesta del servidor
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                });
                            };
                        });
                    </script>
                </form>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-dark" onclick="$(this).closest('.modal').modal('hide');">Cerrar</button>
				<button type="button" class="btn btn-info" onclick="enviar_mensaje_difusion_confirmar()"><i class="feather icon-message-square mr-1"></i>Enviar</button>
			</div>
		</div>
	</div>
</div>

<script>


    function enviar_mensaje_difusion_confirmar() {
        var formData = new FormData(document.getElementById("mensajeDifusionForm"));
        myDropzone_ges.getAcceptedFiles().forEach(function(file) {
            formData.append('misArchivosGes[]', file);
        });

        // validar los campos del formulario
        if (document.getElementById('de_difusion').value === '') {
            swal("¡Error!", "El campo 'De' es obligatorio", "error");
            return;
        }

        if (document.getElementById('msj_para_difusion').selectedOptions.length === 0) {
            swal("¡Error!", "El campo 'Para' es obligatorio", "error");
            return;
        }

        if (document.getElementById('titulo_msj_difusion').value === '') {
            swal("¡Error!", "El campo 'Título' es obligatorio", "error");
            return;
        }

        if (document.getElementById('detalle_msj_difusion').value === '') {
            swal("¡Error!", "El campo 'Asunto' es obligatorio", "error");
            return;
        }

        if (document.getElementById('mensaje_msj_difusion').value === '') {
            swal("¡Error!", "El campo 'Mensaje' es obligatorio", "error");
            return;
        }

        formData.append('_token', '{{ csrf_token() }}');
        formData.append('de_difusion', document.getElementById('de_difusion').value);
        
        // Manejar el select múltiple correctamente
        var selectElement = document.getElementById('msj_para_difusion');
        var selectedValues = Array.from(selectElement.selectedOptions).map(option => option.value);
        
        // Agregar cada valor seleccionado como elemento de array
        selectedValues.forEach(function(value) {
            formData.append('msj_para_difusion[]', value);
        });
        
        formData.append('titulo_msj_difusion', document.getElementById('titulo_msj_difusion').value);
        formData.append('detalle_msj_difusion', document.getElementById('detalle_msj_difusion').value);
        formData.append('mensaje_msj_difusion', document.getElementById('mensaje_msj_difusion').value);

        fetch("{{ route('mensaje_difusion') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            // Manejar la respuesta del servidor
            if(data.estado == 1){
                swal({
                    title: "¡Éxito!",
                    text: "El mensaje de difusión se ha enviado correctamente.",
                    icon: "success"
                });
            }else{
                swal({
                    title: "¡Error!",
                    text: "No se pudo enviar el mensaje de difusión.",
                    icon: "error"
                });
            }
            // cerrar modal
            $('#mensaje_difusion').modal('hide');
        })
        .catch(error => {
            console.error('Error:', error.responseText);
        });
    };

</script>
