<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
    <div class="card-a">
        <div class="card-header-a" id="motivo">
            <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#motivo_c" aria-expanded="false" aria-controls="motivo_c">
                Examen físico
            </button>
        </div>
        <div id="motivo_c" class="collapse show" aria-labelledby="motivo" data-parent="#motivo">
            <div class="card-body-aten-a">
                <div class="form-row">
                   <!-- <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-6 col-xxl-6">
                        <label class="floating-label-activo-sm" for="antecedentes">Antecedentes Especialidad</label>
                        <textarea class="form-control caja-texto form-control-sm"  rows="1"  onfocus="this.rows=5" onblur="this.rows=1;" class="form-control form-control-sm" name="antecedentes" id="antecedentes" placeholder="{{ $placeholder_antecedentes }}"></textarea>
                    </div>-->
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                        <label class="floating-label-activo-sm" for="motivo">Condición corporal</label>
                        <select class="form-control form-control-sm" name="condicion_corporal" id="condicion_corporal" value="">
                            <option value="">Seleccione</option>
                            <option value="1">1 (Caquéctico)</option>
                            <option value="2">2 (Delgado)</option>
                            <option value="3">3 (Óptimo)</option>
                            <option value="4">4 (Sobrepeso)</option>
                            <option value="5">5 (Obeso)</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">T (ºC)</label>
                        <input type="text" class="form-control form-control-sm" name="temperatura" id="temperatura" value="">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">FC (L/min)</label>
                        <input type="text" class="form-control form-control-sm" name="frecuencia_cardiaca" id="frecuencia_cardiaca" value="">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">FR (R/min)</label>
                        <input type="text" class="form-control form-control-sm" name="frecuencia_respiratoria" id="frecuencia_respiratoria" value="">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">TLLC (seg)</label>
                        <input type="text" class="form-control form-control-sm" name="tllc" id="tllc" value="">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">TRPC (seg)</label>
                        <input type="text" class="form-control form-control-sm" name="trpc" id="trpc" value="">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">Pulso</label>
                        <input type="text" class="form-control form-control-sm" name="pulso" id="pulso" value="">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">Mucosas</label>
                        <input type="text" class="form-control form-control-sm" name="mucosas" id="mucosas" value="">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-2 col-xxl-2">
                        <label class="floating-label-activo-sm" for="motivo">% Deshidratación</label>
                        <input type="text" class="form-control form-control-sm" name="deshidratacion" id="deshidratacion" value="">
                    </div>

                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="organos_sentidos">Óganos de los sentidos</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="organos_sentidos" id="organos_sentidos"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="piel_pelaje">Piel y pelaje</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="piel_pelaje" id="piel_pelaje"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="ganglios_linfaticos">Ganglios linfáticos</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="ganglios_linfaticos" id="ganglios_linfaticos"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="sistema_digestivo">Sistema digestivo</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="sistema_digestivo" id="sistema_digestivo"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="sistema_endocrino">Sistema endocrino</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="sistema_endocrino" id="sistema_endocrino"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="sistema_musculo_esqueletico">Sistema músculo esquelético</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="sistema_musculo_esqueletico" id="sistema_musculo_esqueletico"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="sistema_nervioso">Sistema nervioso</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="sistema_nervioso" id="sistema_nervioso"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="sistema_urinario">Sistema urinario</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="sistema_urinario" id="sistema_urinario"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="sistema_reproductivo">Sistema reproductivo</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="sistema_reproductivo" id="sistema_reproductivo"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="palpacion_rectal">Palpación rectal</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="palpacion_rectal" id="palpacion_rectal"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4 col-xxl-4">
                        <label class="floating-label-activo-sm" for="otros_examen_fisico">Otros</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=8" onblur="this.rows=1;" name="otros_examen_fisico" id="otros_examen_fisico"></textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <label class="floating-label-activo-sm" for="mis-imagenes">Imágenes de la atención</label>
                        <div class="dropzone dropzone-atencion" id="mis-imagenes" action="{{ route('profesional.imagen.carga') }}">
                            <div class="dz-message" data-dz-message>
                                <span class="dropzone-atencion-icono" aria-hidden="true">⇧</span>
                                <strong>Arrastre las imágenes aquí o haga clic para seleccionarlas</strong>
                                <small>Formatos permitidos: JPG, JPEG y PNG</small>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>

<style>
    .dropzone.dropzone-atencion {
        min-height: 150px;
        border: 2px dashed #13a6a6 !important;
        border-radius: 10px;
        background: #f0fbfb;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
    }

    .dropzone.dropzone-atencion:hover,
    .dropzone.dropzone-atencion.dz-drag-hover {
        border-color: #087f7f !important;
        background: #e1f7f7;
        box-shadow: 0 0 0 3px rgba(19, 166, 166, .14);
    }

    .dropzone-atencion .dz-message {
        margin: 0 !important;
        color: #075f64;
        text-align: center;
    }

    .dropzone-atencion .dz-message strong,
    .dropzone-atencion .dz-message small {
        display: block;
    }

    .dropzone-atencion .dz-message strong {
        font-size: 15px;
        margin-top: 6px;
    }

    .dropzone-atencion .dz-message small {
        margin-top: 4px;
        color: #4d6d70;
    }

    .dropzone-atencion-icono {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #13a6a6;
        color: #fff;
        font-size: 25px;
        font-weight: 700;
        line-height: 1;
    }
</style>

<script>
    (function traducirDropzoneAtencion() {
        if (typeof Dropzone === 'undefined' || !Dropzone.prototype.defaultOptions) {
            return;
        }

        Object.assign(Dropzone.prototype.defaultOptions, {
            dictDefaultMessage: 'Arrastre los archivos aquí o haga clic para seleccionarlos.',
            dictFallbackMessage: 'Su navegador no permite cargar archivos arrastrándolos.',
            dictFallbackText: 'Utilice el formulario alternativo para cargar sus archivos.',
            dictFileTooBig: 'El archivo es demasiado grande (@{{filesize}} MB). El máximo permitido es @{{maxFilesize}} MB.',
            dictInvalidFileType: 'Este tipo de archivo no está permitido.',
            dictResponseError: 'El servidor respondió con el código @{{statusCode}}.',
            dictCancelUpload: 'Cancelar carga',
            dictCancelUploadConfirmation: '¿Está seguro de que desea cancelar esta carga?',
            dictRemoveFile: 'Eliminar archivo',
            dictMaxFilesExceeded: 'No puede cargar más archivos.'
        });
    })();
</script>
