<div id="m_aconsentcirm" class="modal fade " tabindex="-1" role="dialog" aria-labelledby="m_aconsentcirm" aria-hidden="true">

	<div class="modal-dialog modal-dialog-centered modal-xl" role="document">

        <div class="modal-content">

			<div class="modal-header bg-info">

				<h5 class="modal-title text-white text-center">Consentimiento informado</h5>

				<button type="button" class="close"  data-dismiss="modal"  aria-label="Close" onclick="$('#m_aconsentcirm').modal('hide');">

				<span aria-hidden="true">&times;</span>

				</button>

            </div>

			<div class="modal-body px-4">

                <div id="div_informacion_pasos_cons">

                    <div class="row">

                        <div class="col-md-12">

                            <div class="alert alert-primary text-c-blue" role="alert">

                                 Complete el Diagnóstico, Cirugía a realizar y luego busque el Consentimiento Informado que necesita.

                            </div>

                        </div>

                    </div>

                </div>

                <input type="hidden" name="id_consentimiento" id="id_consentimiento" value="">

                <div class="form-row">

                    <div class="col-12">

                    <div class="card-informacion">

                        <div class="card-body">

                                <div class="form-row">

                					<div class="form-group fill col-sm-12 col-md-6 col-lg-6 col-xl-6">

                						<label class="floating-label-activo-sm">Diagnóstico *</label>

                						<input type="text" class="form-control form-control-sm" data-input_igual="lic_descripcion_hipotesis,descripcion_hipotesis" id="diagnostico_cons" name="diagnostico_cons" value="" required onchange="validarDiagnostico('diagnostico_cons','consentimiento');cargarIgual('diagnostico_cons');" >

                					</div>

                                    <div class="form-group fill col-sm-12 col-md-6 col-lg-6 col-xl-6">

                						<label class="floating-label-activo-sm">Cirugía o procedimiento *</label>

                						<input type="text" class="form-control form-control-sm" id="cirugia_cons" name="cirugia_cons" value="" required>

                					</div>

                                    <div class="form-group fill col-sm-12 col-md-12 col-lg-12 col-xl-12">

                						<label class="floating-label-activo-sm"> Buscar por nombre del consentimiento *</label>

                						<input type="text" class="form-control form-control-sm"
                                                    id="consentimiento" name="consentimiento" value=""
                                                    list="lista_consentimientos_veterinarios"
                                                    autocomplete="off"
                                                    placeholder="Escriba al menos 2 letras">
                                                <datalist id="lista_consentimientos_veterinarios"></datalist>
                                                <div id="resultados_consentimientos_veterinarios"
                                                    class="list-group position-absolute w-100 shadow"
                                                    style="display:none; z-index:2055; max-height:220px; overflow-y:auto;"></div>

                                        {{-- <span style="color:red; font-size: 10px" id="msj_consentimiento"></span> --}}

                					</div>

                				</div>

                                

                                <div id="div_informacion_general_cons" style="display: none;">

                                    <div class="form-row">

                                        <div class="col-12">

                                            <div class="alert alert-secondary">

                                                <div class="form-row">

                                                    <div class="col-md-12">

                                                        <p>1. He consultado con el profesional veterinario <strong>Dr.{{ $profesional->nombre }} {{ $profesional->apellido_uno }} {{ $profesional->apellido_dos }} </strong> quien me ha explicado e informado, sobre el motivo, los objetivos y potenciales riesgos para la salud de mi mascota y las consecuencias, que este procedimiento conlleva.</p>

                                                    </div>
                                                  <br>
                                                </div>

                                                <div class="row text-justify" id='m_aconsentcirm_contenido'>

                                                    {{-- texto de consentimiento  --}}

                                                </div>

                                                <div class="form-row mt-3">

                                                    <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12">

                                                        <label class="floating-label-activo-sm">Situaciones especiales del paciente</label>

                                                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=2" onblur="this.rows=1;" name="situaciones_especiales_del_paciente" id="situaciones_especiales_del_paciente"></textarea>

                                                    </div>

                                                </div>

                                                <input type="hidden" name="esperando_aprobacion" id="esperando_aprobacion" value="0">



                                                <div class="form-row" id="div_btn_aprobacion_solicitud" style="display:;">

                                                    <div class="form-group col-sm-12">

                                                        <button type="button" id="btn_solicitar_consentimiento_app" class="btn btn-info btn-sm btn-block" onclick="registar_solicitar_autorizacion_cons('documentos');"><i class="feather icon-file-text"></i> Enviar a Documentos del tutor para firma</button>

                                                    </div>

                                                </div>



                                                <div class="form-row" id="div_btn_aprobacion_espera" style="display: none;">

                                                    <div class="d-flex justify-content-center">

                                                        <div class="spinner-border text-c-blue mt-1 mb-3 mr-3," role="status">

                                                            <span class="sr-only"> </span>

                                                        </div>

                                                        <h5 class="tit-gen pt-2"> Solicitando Autorización</h5>

                                                    </div>

                                                </div>



                                                <div class="form-row" id="div_btn_aprobacion_ok" style="display: none;">

                                                    <div class="form-group col-sm-6">

                                                        <button type="button" class="btn btn-danger-light btn-sm btn-block" id="btn_ver_pdf_cons_activa">Ver PDF</button>

                                                    </div>



                                                   <div class="form-group col-sm-6">

                                                        <button type="button" class="btn btn-danger-light btn-sm btn-block" id="btn_enviar_cons_activa">Ver PDF</button>

                                                    </div>



                                                    <div class="form-group col-sm-12">

                                                        <button type="button" class="btn btn-info btn-sm btn-block" onclick="limpiar_consentimiento_informado()">Crear nuevo Consentimiento</button>

                                                    </div>

                                                </div>



                                                <div class="row">

                                                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">

                                                        <p>2. El consentimiento quedará pendiente en Documentos del tutor. Al firmarlo se generará un QR de validación y se enviará firmado al escritorio del profesional.</p>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!--TABLA-->

                <h6 class="tit-gen mt-3">Consentimientos informados</h6>

                <div class="form-row">

                    <div class="col-12">

                        <div class="card-informacion">

                            <div class="card-body">

                                 <div class="form-row">

                                    <div class="col-12">

                                        <table class="display table table-striped table-xs dt-responsive datatable" style="width:100%" id="m_aconsentcirm_table">

                                            <thead>

                                                <tr>

                                                    <th>ID</th>

                                                    <th>Sol. por</th>

                                                    <th>Consentimiento</th>

                                                    <th>Diagnóstico</th>

                                                    <th>Cirugía</th>

                                                    <th>F. Creación</th>

                                                    <th>F. Aprobación</th>

                                                    <th>Estado</th>

                                                    <th>PDF</th>

                                                </tr>

                                            </thead>

                                            <tbody>



                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

		</div>

	</div>

</div>
<script>

    /*
     * Buscador nativo de consentimientos veterinarios.
     * No depende de jQuery UI: consulta directamente con_consentimientos
     * mediante consentimiento.ver_autocomplete.
     */
    var consentimientosVeterinariosDisponibles = [];
    var temporizadorBusquedaConsentimiento = null;

    function normalizarTextoConsentimiento(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim()
            .toLowerCase();
    }

    function cargarPlantillaConsentimientoVeterinario(registro) {
        if (!registro || !registro.value) {
            return;
        }

        $('#consentimiento').val(registro.label);
        $('#id_consentimiento').val(registro.value);

        $.ajax({
            url: "{{ route('consentimiento.cargar_consentimiento') }}",
            type: 'get',
            dataType: 'json',
            data: { id: registro.value },
            success: function(data) {
                if (data.estado == 1) {
                    var texto = data.registro.texto || '';
                    texto = texto.replaceAll('{nombre_raza}', '<strong>' + ($('#raza_mascota_cons').val() || '') + '</strong>');
                    texto = texto.replaceAll('{cirugia}', '<strong>' + ($('#cirugia_cons').val() || '') + '</strong>');
                    texto = texto.replaceAll('{nombre_mascota}', '<strong>' + ($('#nombre_mascota_cons').val() || '') + '</strong>');
                    texto = texto.replaceAll('{nombre_especie}', '<strong>' + ($('#especie_mascota_cons').val() || '') + '</strong>');
                    texto = texto.replaceAll('{numero_chip}', '<strong>' + ($('#chip_mascota_cons').val() || '') + '</strong>');

                    $('#m_aconsentcirm_contenido').html(texto);
                    $('#div_informacion_pasos_cons').hide();
                    $('#div_informacion_general_cons').show();
                } else {
                    swal({
                        title: 'Problema al cargar el consentimiento',
                        text: data.msj || 'No fue posible cargar el registro.',
                        icon: 'warning'
                    });
                }
            },
            error: function() {
                swal({
                    title: 'Problema al consultar la base de datos',
                    text: 'No fue posible cargar el consentimiento seleccionado.',
                    icon: 'error'
                });
            }
        });
    }

    $(document).ready(function() {
        $('#consentimiento').on('input', function() {
            var termino = $(this).val().trim();
            $('#id_consentimiento').val('');
            clearTimeout(temporizadorBusquedaConsentimiento);

            if (termino.length < 2) {
                consentimientosVeterinariosDisponibles = [];
                $('#lista_consentimientos_veterinarios').empty();
                $('#resultados_consentimientos_veterinarios').empty().hide();
                return;
            }

            temporizadorBusquedaConsentimiento = setTimeout(function() {
                $.ajax({
                    url: "{{ route('consentimiento.ver_autocomplete') }}",
                    type: 'get',
                    dataType: 'json',
                    data: { search: termino },
                    success: function(data) {
                        consentimientosVeterinariosDisponibles = Array.isArray(data) ? data : [];
                        var lista = $('#lista_consentimientos_veterinarios').empty();
                        var resultados = $('#resultados_consentimientos_veterinarios').empty();

                        consentimientosVeterinariosDisponibles.forEach(function(registro) {
                            $('<option>')
                                .attr('value', registro.label)
                                .attr('data-id', registro.value)
                                .appendTo(lista);

                            $('<button type="button">')
                                .addClass('list-group-item list-group-item-action py-2')
                                .attr('data-id', registro.value)
                                .text(registro.label)
                                .appendTo(resultados);
                        });

                        resultados.toggle(consentimientosVeterinariosDisponibles.length > 0);
                    }
                });
            }, 180);
        });

        $('#resultados_consentimientos_veterinarios').on('click', 'button', function() {
            var id = String($(this).data('id'));
            var registro = consentimientosVeterinariosDisponibles.find(function(item) {
                return String(item.value) === id;
            });

            $('#resultados_consentimientos_veterinarios').hide();
            cargarPlantillaConsentimientoVeterinario(registro);
        });

        $('#consentimiento').on('change', function() {
            var buscado = normalizarTextoConsentimiento($(this).val());
            var registro = consentimientosVeterinariosDisponibles.find(function(item) {
                return normalizarTextoConsentimiento(item.label) === buscado;
            });

            if (registro) {
                $('#resultados_consentimientos_veterinarios').hide();
                cargarPlantillaConsentimientoVeterinario(registro);
            }
        });

        $(document).on('click', function(evento) {
            if (!$(evento.target).closest('#consentimiento, #resultados_consentimientos_veterinarios').length) {
                $('#resultados_consentimientos_veterinarios').hide();
            }
        });
    });

    $(document).ready(function () {

        if ($.fn.autocomplete) {
            $("#consentimiento").autocomplete({

            source: function(request, response) {

                // console.log(request);

                var longitud = request.term.length;

                if(longitud>2)

                {

                    // Fetch data

                    $.ajax({

                        url: "{{ route('consentimiento.ver_autocomplete') }}",

                        type: 'get',

                        dataType: "json",

                        data: {

                            search: request.term

                        },

                        success: function(data) {

                            // console.log(data);

                            response(data);

                        }

                    });

                }

            },

            select: function(event, ui) {



                // console.log(ui);

                $('#consentimiento').val(ui.item.label);

                $('#id_consentimiento').val(ui.item.value);

                // Set selection

                $.ajax({

                    url: "{{ route('consentimiento.cargar_consentimiento') }}",

                    type: 'get',

                    dataType: "json",

                    data: {

                        id: ui.item.value

                    },

                    success: function(data) {

                        // console.log(data);

                        if(data.estado == 1)

                        {

                            // console.log(data.registro.texto);

                            var texto = data.registro.texto;

                            texto = texto.replace('{nombre_raza}', '<span style="font-size: 25px;font-weight: bold;">'+$('#raza_mascota_cons').val()+'</span>');

                            texto = texto.replace('{cirugia}', '<span style="font-size: 15px;font-weight: bold;">'+$('#cirugia_cons').val()+'</span>');

                            texto = texto.replace('{nombre_mascota}', '<span style="font-size: 25px;font-weight: bold;">'+$('#nombre_mascota_cons').val()+'</span>');

                            texto = texto.replace('{nombre_especie}', '<span style="font-size: 15px;font-weight: bold;">'+$('#especie_mascota_cons').val()+'</span>');

                            texto = texto.replace('{numero_chip}', '<span style="font-size: 15px;font-weight: bold;">'+$('#chip_mascota_cons').val()+'</span>');

                            $('#m_aconsentcirm_contenido').html('');

                            $('#m_aconsentcirm_contenido').html(texto);



                            $('#div_informacion_pasos_cons').hide();

                            $('#div_informacion_general_cons').show();

                        }

                        else

                        {

                            swal({

                                title: "Problema al Cargar Información de Consentimiento.",

                                text: data.msj,

                                icon: "warning",

                            })

                            $('#div_informacion_pasos_cons').show();

                            $('#div_informacion_general_cons').hide();

                        }

                    }

                });



                return false;

            }

            });
        }



    });



    /** consentimientos informados**/

    function cons_tto() {

        $('#m_aconsentcirm').modal('show');

        validarDiagnostico('diagnostico_cons','consentimiento');

        mostrar_consentimientos_paciente();

        $('#div_informacion_pasos_cons').show();

        $('#div_informacion_general_cons').hide();

    }

    function validarDiagnostico(diagnostico_cons,consentimiento)

    {

        if($('#'+diagnostico_cons).val() == '')

        {

            $('#'+consentimiento).attr('disabled', true);

            // $('#msj_consentimiento').html('Debe ingresar Diagnostico en la Ficha.');

        }

        else

        {

            $('#'+consentimiento).attr('disabled', false);

            // $('#msj_consentimiento').html('');

        }

    }



    var consentimientoEnProceso = false;

    function registar_solicitar_autorizacion_cons(canal)

    {
        canal = canal || 'app';
        if (consentimientoEnProceso) {
            return;
        }



        var id_ficha_atencion = $('#id_fc').val();

        var id_profesional = $('#id_profesional_fc').val();

        var id_paciente = $('#id_paciente_fc').val();

        var diagnostico_cons = $('#diagnostico_cons').val();

        var cirugia_cons = $('#cirugia_cons').val();

        var consentimiento = $('#consentimiento').val();

        var id_consentimiento = $('#id_consentimiento').val();

        var num_consentimiento = 0;

        var observaciones_con = $('#situaciones_especiales_del_paciente').val();

        var otro = '';

        var token = CSRF_TOKEN;

        var camposFaltantes = [];
        if (!String(diagnostico_cons || '').trim()) {
            camposFaltantes.push('Diagnóstico');
        }
        if (!String(cirugia_cons || '').trim()) {
            camposFaltantes.push('Cirugía o procedimiento');
        }
        if (!String(id_consentimiento || '').trim()) {
            camposFaltantes.push('Consentimiento informado');
        }
        if (!String(id_ficha_atencion || '').trim() ||
            !String(id_profesional || '').trim() ||
            !String(id_paciente || '').trim()) {
            camposFaltantes.push('Datos de la ficha de atención');
        }

        if (camposFaltantes.length > 0) {
            swal({
                title: "Faltan campos obligatorios",
                text: "Complete: " + camposFaltantes.join(', ') + ".",
                icon: "warning",
            });
            return;
        }



        var datos = {};

        datos._token = token;

        datos.id_ficha_atencion = id_ficha_atencion;

        datos.id_profesional = id_profesional;

        datos.id_paciente = id_paciente;

        datos.id_mascota = $('#id_mascota_fc').val() || "{{ $mascota->id ?? request('id_mascota') ?? '' }}";

        datos.diagnostico_cons = diagnostico_cons;

        datos.cirugia_cons = cirugia_cons;

        datos.consentimiento = consentimiento;

        datos.id_consentimiento = id_consentimiento;

        datos.num_consentimiento = num_consentimiento;

        datos.observaciones_con = observaciones_con;

        datos.otro = otro;



        consentimientoEnProceso = true;
        $('#btn_solicitar_consentimiento_app').prop('disabled', true);

        $.ajax({

            url: "{{ route('consentimiento.registrar.autorizacion') }}",

            type: 'post',

            dataType: "json",

            data: datos,

            success: function(data) {

                console.log(data);

                if(data.estado == 1)

                {

                    swal({

                        title: "Consentimiento enviado al tutor",

                        text: data.msj || 'Quedó disponible en Documentos del tutor para firma y autorización.',

                        icon: "success",

                    });

                    $('#id_consentimiento').val(data.last_id);
                    $('#div_btn_aprobacion_solicitud').show();
                    $('#div_btn_aprobacion_espera, #div_btn_aprobacion_ok').hide();
                    mostrar_consentimientos_paciente();
                    limpiar_consentimiento_informado();

                }

                else

                {

                    swal({

                        title: "Problema al generar Consentimiento Informado.",

                        text: data.error
                            ? Object.keys(data.error).map(function(campo) {
                                return campo + ': ' + data.error[campo];
                            }).join('\n')
                            : data.msj,

                        icon: "warning",

                    });

                }

                consentimientoEnProceso = false;
                $('#btn_solicitar_consentimiento_app').prop('disabled', false);

            },
            error: function(xhr) {
                consentimientoEnProceso = false;
                $('#btn_solicitar_consentimiento_app').prop('disabled', false);
                var respuesta = xhr.responseJSON || {};
                swal({
                    title: "No fue posible generar el consentimiento",
                    text: respuesta.msj || "Revise los datos obligatorios e inténtelo nuevamente.",
                    icon: "error",
                });
            }

        });

    }



    function checkToken(input_token, div_mostrar, div_ocultar, div_solicitud)

    {

        let url = "{{ route('check_sdi_token') }}";

        var _token = $('input[name=_token]').val();

        var token = $('#'+input_token).val();

        $.ajax({

            url: url,

            type: "GET",

            data: {

                _token: _token,

                token:token

            },

            success: (resp)=>{

                if(resp.estado==1)

                {

                    if(resp.registro.estado==1)

                    {

                        $('#'+div_mostrar).show();

                        $('#'+div_ocultar).hide();

                        aceptarAprobacion(resp.registro.estado);

                        $('#btn_ver_pdf_cons_activa').click(function (e) {

                            e.preventDefault();

                            ver_pdf_consentimiento($('#id_consentimiento').val(), $('#id_fc').val());

                        });

                        $('#btn_enviar_cons_activa').click(function (e) {

                            e.preventDefault();

                            enviar_consentimiento($('#id_consentimiento').val(), $('#id_fc').val());

                        });

                        mostrar_consentimientos_paciente();

                        $('#diagnostico_cons').attr('disabled', true);

                        $('#cirugia_cons').attr('disabled', true);

                        $('#consentimiento').attr('disabled', true);

                    }

                    else if(resp.registro.estado==2)

                    {

                        $('#'+div_mostrar).hide();

                        $('#'+div_ocultar).hide();

                        $('#'+div_solicitud).show();

                        aceptarAprobacion(resp.registro.estado);

                        $('#btn_ver_pdf_cons_activa').click(function (e) {

                            e.preventDefault();

                        });

                        $('#btn_enviar_cons_activa').click(function (e) {

                            e.preventDefault();

                            //enviar_consentimiento($('#id_consentimiento').val(), $('#id_fc').val());

                        });



                        swal({

                            title: "Consentimiento Informado.",

                            text: 'Consentimiento Informado Rechazado\n Debe solicitar aprobación.',

                            icon: "warning",

                        });

                    }

                    else

                    {

                        setTimeout(function () {
                            checkToken(input_token, div_mostrar, div_ocultar, div_solicitud);
                        }, 3000);

                    }

                }

                else

                {

                    setTimeout(function () {
                        checkToken(input_token, div_mostrar, div_ocultar, div_solicitud);
                    }, 3000);

                }

            },

            error: (resp)=>{

                console.warn(resp);

            }

        });

    }



    function enviar_consentimiento(id_consentimiento, id_ficha_atencion)

    {

        let url = "{{ route('consentimiento.enviar') }}";

        var _token = $('input[name=_token]').val();

        var token = $('#esperando_aprobacion').val();

        $.ajax({

            url: url,

            type: "POST",

            data: {

                id_consentimiento : id_consentimiento,

                id_ficha_atencion : id_ficha_atencion,

                id_paciente : $('#id_paciente_fc').val(),

                id_lugar_atencion : $('#id_lugar_atencion').val(),

                id_profesional : $('#id_profesional_fc').val(),

                _token : _token,

                token : token,

            },

            success: (resp)=>{

                console.log(resp);

                if(resp.estado==1)

                {

                    console.log('Consentimiento enviado exitosamente');

                    swal({

                        title: "Consentimiento Informado.",

                        text: 'Enviado exitosamente.',

                        icon: "success",

                    });

                }

                else

                {

                    console.log('Falla al enviar consentimiento');

                    swal({

                        title: "Consentimiento Informado.",

                        text: 'Falla al enviar consentimiento.',

                        icon: "error",

                    });

                }

            },

            error: (resp)=>{

                console.warn(resp);

            }

        });

    }



    function aceptarAprobacion(estado)

    {

        var id_consentimiento = $('#id_consentimiento').val();

        var token = $('#esperando_aprobacion').val();

        let url = "{{ route('consentimiento.estado.autorizacion') }}";

        var _token = $('input[name=_token]').val();

        $.ajax({

            url: url,

            type: "POST",

            data: {

                id_consentimiento : id_consentimiento,

                token : token,

                estado : estado,

                _token : _token,



            },

            success: (resp)=>{

                console.log(resp);

                if(resp.estado==1)

                {

                    console.log('registro actualizado');

                }

                else

                {

                    console.log('falla en actualizacion');

                }

            },

            error: (resp)=>{

                console.warn(resp);

            }

        });

    }



    function mostrar_consentimientos_paciente()

    {

        var id_paciente_fc = $('#id_paciente_fc').val();

        var id_lugar_atencion = $('#id_lugar_atencion').val();

        var id_ficha_atencion = $('#id_fc').val();



        let url = "{{ route('consentimiento.paciente.ver') }}";

        var _token = $('input[name=_token]').val();

        $('#m_aconsentcirm_table tbody').html('');

        $.ajax({

            url: url,

            type: "GET",

            data: {

                id_paciente : id_paciente_fc,

                id_lugar_atencion : id_lugar_atencion,

                id_ficha_atencion: id_ficha_atencion,

            },

            success: (resp)=>{

                console.log(resp);

                html = '';

                if(resp.estado==1)

                {



                    let estado_log = ['Pendiente de firma del tutor', 'Firmado y autorizado', 'Rechazado'];

                    $.each(resp.registros, function (key, value) {



                        var estado_log_valor = Number(value.confirmacion || 0);

                        var fecha_log = value.updated_at || '-';

                        if(value.log_users_devices != null){

                            estado_log_valor = value.log_users_devices.estado;

                            fecha_log = value.log_users_devices.updated_at;

                        }



                        html += '<tr>';

                        html += '    <td>'+value.id+'</td>';

                        html += '    <td>'+value.profesional.nombre+' '+value.profesional.apellido_uno+' '+value.profesional.apellido_dos+'</td>';

                        html += '    <td>'+value.consentimiento.nombre+'</td>';

                        html += '    <td>'+value.diagnostico_cons+'</td>';

                        html += '    <td>'+value.cirugia_cons+'</td>';

                        html += '    <td>'+value.fecha_cons+'</td>';

                        html += '    <td>'+fecha_log+'</td>';

                        html += '    <td>'+estado_log[estado_log_valor]+'</td>';

                        html += '    <td><button class="btn btn-danger btn-xs" role="button" onclick="ver_pdf_consentimiento(\''+value.id+'\', \''+$('#id_fc').val()+'\');">Ver PDF</button></td>';

                        html += '</tr>';

                    });

                }



                $('#m_aconsentcirm_table').DataTable().destroy();

                $('#m_aconsentcirm_table tbody').html(html);

                $('#m_aconsentcirm_table').DataTable({

                    responsive: true,

                });



            },

            error: (resp)=>{

                console.warn(resp);

            }

        });

    }



    function ver_pdf_consentimiento(id_consentimiento_pcte, id_ficha_atencion)

    {

        Fancybox.close();

        Fancybox.show(

            [

                {

                src: '{{ route("consentimiento.pdf") }}?id_consentimiento='+id_consentimiento_pcte+'&id_ficha_atencion='+id_ficha_atencion,

                type: "iframe",

                preload: false,

                },

            ]

        );

        // cerrar modal

        $('#m_aconsentcirm').modal('hide');

    }





    function limpiar_consentimiento_informado()

    {

        mostrar_consentimientos_paciente();



        $('#diagnostico_cons').attr('disabled', false);

        $('#cirugia_cons').attr('disabled', false);

        $('#consentimiento').attr('disabled', false);



        $('#div_informacion_pasos_cons').show();

        $('#div_informacion_general_cons').hide();



        $('#diagnostico_cons').val($('#descripcion_hipotesis').val());

        $('#id_consentimiento').val('');

        $('#cirugia_cons').val('');

        $('#consentimiento').val('');

        $('#id_consentimiento').val('');

        $('#m_aconsentcirm_contenido').html('');

        $('#situaciones_especiales_del_paciente').val('');

        $('#esperando_aprobacion').val('');

        $('#div_btn_aprobacion_solicitud').show();

        $('#div_btn_aprobacion_espera').hide();

        $('#div_btn_aprobacion_ok').hide();

    }

</script>

