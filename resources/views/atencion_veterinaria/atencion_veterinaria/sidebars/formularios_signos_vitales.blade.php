<!--Signos vitales-->
<div id="formularios_signos_vitales" class="bs-canvas bs-canvas-anim bs-canvas-right position-fixed bg-light h-100 "
    data-width="300px" data-offset="true">
    <header class="bs-canvas-header p-3 d-flex justify-content-between bg-info overflow-auto">
        <button type="button" class="bs-canvas-close close" aria-label="Close"><span aria-hidden="true"
                class="text-light">&times;</span></button>
        <h5 class="d-inline-block text-light mb-0 float-right mt-1">Signos vitales veterinarios</h5>
    </header>
    <div class="bs-canvas-content">
        <div class="accordion" id="accordionExample">
            <div class="card-sidebar mb-0 rounded-0">
                <div class="card-header-sidebar" id="headingOne">
                    <button class="btn btn-light btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"><i  class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        Temperatura, pulso y respiración
                    </button>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body-sidebar">
                        <div class="form-row pt-3 px-2">
                            <div class="form-group col-sm-12 col-md-3 col-lg-3 col-xl-3">
                                <label class="floating-label-activo-sm">Tº</label>
                                <input type="text" class="form-control form-control-sm" name="temperatura_sig_vit" id="temperatura_sig_vit" value="{{ isset($fichaAtencion) ? $fichaAtencion->temperatura : '' }}">
                            </div>
                            <div class="form-group col-sm-12 col-md-3 col-lg-3 col-xl-3">
                                <label class="floating-label-activo-sm">Pulso</label>
                                <input type="text" class="form-control form-control-sm" name="pulso_sig_vit" id="pulso_sig_vit" value="{{ isset($fichaAtencion) ? $fichaAtencion->pulso : ''}}">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                <label class="floating-label-activo-sm">Frec. respiratoria</label>
                                <input type="text" class="form-control form-control-sm" name="frec_reposo_sig_vit" id="frec_reposo_sig_vit" value="{{ isset($fichaAtencion) ? $fichaAtencion->frecuencia_reposo : '' }}">
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-xxs btn-success-light-c" onclick="guardar_signos_vitales_sidebar()"><i class="feather icon-save"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-sidebar">
                <div class="card-header-sidebar" id="heading_utilidades">
                    <h2 class="mb-0">
                    <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_utilidades" aria-expanded="false" aria-controls="collapse_utilidades"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        Condición corporal
                    </button>
                    </h2>
                </div>
                <div id="collapse_utilidades" class="collapse" aria-labelledby="heading_utilidades" data-parent="#accordion_side_bar">
                    <div class="card-body-sidebar">
                        <div class="form-row pt-3 px-2">
                            <div class="form-group col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                <label class="floating-label-activo-sm">Peso</label>
                               <input type="text" class="form-control form-control-sm" name="peso_sidebar" id="peso_sidebar" value="{{ isset($fichaAtencion) ? $fichaAtencion->peso : '' }}" onkeydown="calcularIMC_sidebar()">
                            </div>
                            <div class="form-group col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                <label class="floating-label-activo-sm">BCS (1 a 9)</label>
                                <input type="number" min="1" max="9" step="1" class="form-control form-control-sm" name="talla_sidebar" id="talla_sidebar" value="{{ isset($fichaAtencion) ? $fichaAtencion->talla : '' }}" oninput="calcularIMC_sidebar()">
                            </div>
                            <div class="form-group col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                <label class="floating-label-activo-sm">Hidratación</label>
                                <select class="form-control form-control-sm" name="imc_sidebar" id="imc_sidebar">
                                    <option value="">Seleccione</option>
                                    <option value="Normal">Normal</option>
                                    <option value="Leve (5%)">Deshidratación leve (5%)</option>
                                    <option value="Moderada (6-8%)">Deshidratación moderada (6-8%)</option>
                                    <option value="Severa (9-12%)">Deshidratación severa (9-12%)</option>
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <label class="floating-label-activo-sm">Evaluación nutricional</label>
                                <input type="text" class="form-control form-control-sm" name="estado_nutri_sidebar" id="estado_nutri_sidebar" value="{{ isset($fichaAtencion) ? $fichaAtencion->estado_nutricional : '' }}">
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-xxs btn-success-light-c" onclick="guardar_estado_nutri_sidebar()"><i class="feather icon-save"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-sidebar">
                <div class="card-header-sidebar" id="heading_recom">
                    <h2 class="mb-0">
                    <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_recom" aria-expanded="false" aria-controls="collapse_recom"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        Presión Arterial
                    </button>
                    </h2>
                </div>
                <div id="collapse_recom" class="collapse" aria-labelledby="heading_recom" data-parent="#accordion_side_bar">
                    <div class="card-body-sidebar">
                        <div class="form-row pt-3 px-2">
                            <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                <label class="floating-label-activo-sm">Sistólica (PAS)</label>
                                <input type="text" class="form-control form-control-sm" name="presion_bi_sidebar" id="presion_bi_sidebar" value="">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                <label class="floating-label-activo-sm">Diastólica (PAD)</label>
                                <input type="text" class="form-control form-control-sm" name="presion_bd_sidebar" id="presion_bd_sidebar" value="">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                <label class="floating-label-activo-sm">Media (PAM)</label>
                                <input type="text" class="form-control form-control-sm" name="presion_pie_sidebar" id="presion_pie_sidebar" value="">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                                <label class="floating-label-activo-sm">Sitio, manguito y método</label>
                                <input type="text" class="form-control form-control-sm" name="presion_sentado_sidebar" id="presion_sentado_sidebar" value="">
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-xxs btn-success-light-c" onclick="guardar_presion_arterial_sidebar()"><i class="feather icon-save"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-sidebar">
                <div class="card-header-sidebar" id="heading_hosp">
                    <h2 class="mb-0">
                    <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_hosp" aria-expanded="false" aria-controls="collapse_hosp"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                    Perfusión, neurología y movilidad
                    </button>
                    </h2>
                </div>
                <div id="collapse_hosp" class="collapse" aria-labelledby="headinghospm" data-parent="#accordion_side_bar">
                    <div class="card-body-sidebar">
                        <div class="form-row pt-3 px-2">
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <label class="floating-label-activo-sm">Estado de conciencia</label>
                                <textarea class="form-control form-control-sm" rows="2" onfocus="this.rows=4" onblur="this.rows=3;" id="conciencia_sidebar" name="conciencia_sidebar"></textarea>
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <label class="floating-label-activo-sm">Mucosas y TRC</label>
                                <textarea class="form-control form-control-sm" rows="2" onfocus="this.rows=4" onblur="this.rows=3;" id="lenguaje_sidebar" name="lenguaje_sidebar"></textarea>
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <label class="floating-label-activo-sm">Movilidad / locomoción</label>
                                <textarea class="form-control form-control-sm" rows="2" onfocus="this.rows=4" onblur="this.rows=3;" id="traslado_sidebar" name="traslado_sidebar"></textarea>
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-xxs btn-success-light-c" onclick="guardar_comunicacion_conciencia()"><i class="feather icon-save"></i> Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#imc_sidebar').val(@json(isset($fichaAtencion) ? $fichaAtencion->imc : ''));
    });

     function calcularIMC_sidebar() {
        const bcs = parseInt($('#talla_sidebar').val(), 10);
        if (!bcs || bcs < 1 || bcs > 9) {
            $('#estado_nutri_sidebar').val('');
            return;
        }

        let estado = 'Condición corporal ideal';
        if (bcs <= 3) estado = 'Condición corporal baja';
        if (bcs >= 6) estado = 'Sobrepeso';
        if (bcs >= 8) estado = 'Obesidad';
        $('#estado_nutri_sidebar').val(estado);
    }

    function guardar_signos_vitales_sidebar() {
        let temperatura = $('#temperatura_sig_vit').val();
        let pulso = $('#pulso_sig_vit').val();
        let frec_reposo = $('#frec_reposo_sig_vit').val();

        let valido = 1;
        let mensaje = '';

        if (temperatura == '') {
            mensaje += 'Debe ingresar la temperatura.\n';
            valido = 0;
        }
        if (pulso == '') {
            mensaje += 'Debe ingresar el pulso.\n';
            valido = 0;
        }

        if(valido == 0){
            swal({
                title: "Error",
                text: mensaje,
                icon: "error",
                button: "Aceptar",
            });
            return;
        }

        let url = "{{ route('profesional.guardar_signos_vitales_sidebar') }}";
        let data = {
            temperatura: temperatura,
            pulso: pulso,
            frec_reposo: frec_reposo,
            id_ficha_atencion: $('#id_fc').val(),
            _token: CSRF_TOKEN
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                console.log(response);
                if (response.mensaje == 'ok') {
                    $('#temperatura').val(temperatura);
                    $('#pulso').val(pulso);
                    $('#frecuencia_reposo').val(frec_reposo);
                    swal({
                        title: "Éxito",
                        text: "Signos vitales guardados correctamente.",
                        icon: "success",
                        button: "Aceptar",
                    });
                    // Aquí puedes agregar lógica adicional si es necesario
                } else {
                    swal({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        button: "Aceptar",
                    });
                }
            },
            error: function(xhr, status, error) {
                swal({
                    title: "Error",
                    text: "Ocurrió un error al guardar los signos vitales.",
                    icon: "error",
                    button: "Aceptar",
                });
            }
        });
    }

    function guardar_estado_nutri_sidebar(){
        let peso = $('#peso_sidebar').val();
        let talla = $('#talla_sidebar').val();
        let imc = $('#imc_sidebar').val();
        let estado_nutri = $('#estado_nutri_sidebar').val();


        let valido = 1;
        let mensaje = '';

        if (peso == '') {
            mensaje += 'Debe ingresar el peso.\n';
            valido = 0;
        }
        if (talla == '') {
            mensaje += 'Debe ingresar la condición corporal BCS (1 a 9).\n';
            valido = 0;
        }
        if (imc == '') {
            mensaje += 'Debe seleccionar el estado de hidratación.\n';
            valido = 0;
        }
        if (estado_nutri == '') {
            mensaje += 'Debe ingresar el estado nutricional.\n';
            valido = 0;
        }

        if(valido == 0){
            swal({
                title: "Error",
                text: mensaje,
                icon: "error",
                button: "Aceptar",
            })
            return;
        }

        let url = "{{ route('profesional.guardar_estado_nutricional_sidebar') }}";
        let data = {
            peso: peso,
            talla: talla,
            imc: imc,
            estado_nutri: estado_nutri,
            id_ficha_atencion: $('#id_fc').val(),
            _token: CSRF_TOKEN
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                console.log(response);
                if (response.mensaje == 'ok') {
                    $('#peso').val(peso);
                    $('#talla').val(talla);
                    $('#imc').val(imc);
                    $('#estado_nutricional').val(estado_nutri);
                    swal({
                        title: "Éxito",
                        text: "Estado nutricional guardado correctamente.",
                        icon: "success",
                        button: "Aceptar",
                    });
                    // Aquí puedes agregar lógica adicional si es necesario
                } else {
                    swal({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        button: "Aceptar",
                    });
                }
            },
            error: function(xhr, status, error) {
                swal({
                    title: "Error",
                    text: "Ocurrió un error al guardar el estado nutricional.",
                    icon: "error",
                    button: "Aceptar",
                });
            }
        });
    }

    function guardar_presion_arterial_sidebar(){
        let presion_bi = $('#presion_bi_sidebar').val();
        let presion_bd = $('#presion_bd_sidebar').val();
        let presion_pie = $('#presion_pie_sidebar').val();
        let presion_sentado = $('#presion_sentado_sidebar').val();

        let valido = 1;
        let mensaje = '';

        if (presion_bi == '') {
            mensaje += 'Debe ingresar la presión sistólica (PAS).\n';
            valido = 0;
        }
        if (presion_bd == '') {
            mensaje += 'Debe ingresar la presión diastólica (PAD).\n';
            valido = 0;
        }
        if (presion_pie == '') {
            mensaje += 'Debe ingresar la presión arterial media (PAM).\n';
            valido = 0;
        }
        if (presion_sentado == '') {
            mensaje += 'Debe ingresar el sitio, manguito y método.\n';
            valido = 0;
        }

        if(valido == 0){
            swal({
                title: "Error",
                text: mensaje,
                icon: "error",
                button: "Aceptar",
            });
            return;
        }

        let url = "{{ route('profesional.guardar_presion_arterial_sidebar') }}";
        let data = {
            presion_bi: presion_bi,
            presion_bd: presion_bd,
            presion_pie: presion_pie,
            presion_sentado: presion_sentado,
            id_ficha_atencion: $('#id_fc').val(),
            _token: CSRF_TOKEN
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                console.log(response);
                if (response.mensaje == 'ok') {
                    $('#presion_bi').val(presion_bi);
                    $('#presion_bd').val(presion_bd);
                    $('#presion_de_pie').val(presion_pie);
                    $('#presion_sentado').val(presion_sentado);
                    swal({
                        title: "Éxito",
                        text: "Presión arterial guardada correctamente.",
                        icon: "success",
                        button: "Aceptar",
                    });
                    // Aquí puedes agregar lógica adicional si es necesario
                } else {
                    swal({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        button: "Aceptar",
                    });
                }
            },
            error: function(xhr, status, error) {
                swal({
                    title: "Error",
                    text: "Ocurrió un error al guardar la presión arterial.",
                    icon: "error",
                });

            }
        });
    }

     function guardar_comunicacion_conciencia(){
        let conciencia = $('#conciencia_sidebar').val();
        let lenguaje = $('#lenguaje_sidebar').val();
        let traslado = $('#traslado_sidebar').val();

        let valido = 1;
        let mensaje = '';

        if (conciencia == '') {
            mensaje += 'Debe ingresar el estado de conciencia.\n';
            valido = 0;
        }
        if (lenguaje == '') {
            mensaje += 'Debe ingresar mucosas y TRC.\n';
            valido = 0;
        }
        if (traslado == '') {
            mensaje += 'Debe ingresar movilidad o locomoción.\n';
            valido = 0;
        }

        if(valido == 0){
            swal({
                title: "Error",
                text: mensaje,
                icon: "error",
                button: "Aceptar",
            });
            return;
        }

        let url = "{{ route('profesional.guardar_comunicacion_conciencia_sidebar') }}";
        let data = {
            conciencia: conciencia,
            lenguaje: lenguaje,
            traslado: traslado,
            id_ficha_atencion: $('#id_fc').val(),
            _token: CSRF_TOKEN
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                console.log(response);
                if (response.mensaje == 'ok') {
                    $('#ct_estado_conciencia').val(conciencia);
                    $('#ct_lenguaje').val(lenguaje);
                    $('#ct_traslado').val(traslado);
                    swal({
                        title: "Éxito",
                        text: "Evaluación veterinaria guardada correctamente.",
                        icon: "success",
                        button: "Aceptar",
                    });
                    // Aquí puedes agregar lógica adicional si es necesario
                } else {
                    swal({
                        title: "Error",
                        text: response.message,
                        icon: "error",
                        button: "Aceptar",
                    });
                }
            },
            error: function(xhr, status, error) {
                swal({
                    title: "Error",
                    text: "Ocurrió un error al guardar la evaluación veterinaria.",
                    icon: "error",
                    button: "Aceptar",
                });
            }
        });
    }

</script>
