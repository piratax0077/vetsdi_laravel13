@php
    // El esquema de referencia se muestra según la especie; si no es perro ni gato van los dos
    $tipoEspecieVacunas = isset($mascota) && $mascota ? (string) data_get($mascota, 'tipo_especie', '') : '';
    $verEsquemaCanino = $tipoEspecieVacunas !== 'Felino';
    $verEsquemaFelino = $tipoEspecieVacunas !== 'Canino';
@endphp
<div id="form_ges" class="modal fade modal-atencion" tabindex="-1" role="dialog" aria-labelledby="titulo_form_ges" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="titulo_form_ges">Vacunas y desparasitación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-pills modal-atencion-pestanas" id="registros_vac_des" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link-wizard active" id="vac-tab" data-toggle="pill" href="#vac" role="tab" aria-controls="vac" aria-selected="true">Vacunas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-wizard" id="desparasita-tab" data-toggle="pill" href="#desparasita" role="tab" aria-controls="desparasita" aria-selected="false">Desparasitación</a>
                    </li>
                </ul>
                <div class="tab-content" id="registros_vac_desp">
                    <!--Vacunas-->
                    <div class="tab-pane fade show active" id="vac" role="tabpanel" aria-labelledby="vac-tab">
                        <h6 class="t-aten">Registrar vacuna</h6>
                        <p class="modal-atencion-ayuda">Elija la vacuna del esquema según la edad, o escríbala directamente en los campos.</p>

                        <!--Esquema de referencia: al elegir una vacuna se copian su nombre y la edad a los campos de abajo-->
                        <div class="form-row">
                            @if($verEsquemaCanino)
                                <div class="form-group {{ $verEsquemaFelino ? 'col-md-6' : 'col-12' }}">
                                    <label class="floating-label-activo-sm" for="esquema_vacunas_caninas">Esquema de vacunas caninas</label>
                                    <select class="form-control form-control-sm esquema-vacunas" id="esquema_vacunas_caninas">
                                        <option value="">Seleccione vacuna según edad</option>
                                        <optgroup label="6 a 8 semanas">
                                            <option value="sextuple">Séxtuple 1ª Dosis</option>
                                        </optgroup>
                                        <optgroup label="9 a 11 semanas">
                                            <option value="sextuple_1">Séxtuple 2ª Dosis</option>
                                        </optgroup>
                                        <optgroup label="12 a 14 semanas">
                                            <option value="sextuple_2">Séxtuple 3ª Dosis + Antirrábica + KC (Tos)</option>
                                        </optgroup>
                                        <optgroup label="15 a 17 semanas">
                                            <option value="sextuple_3">Refuerzo final (si lo indica el veterinario)</option>
                                        </optgroup>
                                        <optgroup label="Anualmente">
                                            <option value="sextuple_4">Refuerzo Séxtuple + Antirrábica + KC</option>
                                        </optgroup>
                                    </select>
                                </div>
                            @endif
                            @if($verEsquemaFelino)
                                <div class="form-group {{ $verEsquemaCanino ? 'col-md-6' : 'col-12' }}">
                                    <label class="floating-label-activo-sm" for="esquema_vacunas_felinas">Esquema de vacunas felinas</label>
                                    <select class="form-control form-control-sm esquema-vacunas" id="esquema_vacunas_felinas">
                                        <option value="">Seleccione vacuna según edad</option>
                                        <optgroup label="6 a 8 semanas">
                                            <option value="sextuple">Triple Felina 1ª Dosis</option>
                                        </optgroup>
                                        <optgroup label="9 a 11 semanas">
                                            <option value="sextuple_f1">Triple Felina 2ª Dosis</option>
                                        </optgroup>
                                        <optgroup label="12 a 14 semanas">
                                            <option value="sextuple_f2">Triple Felina 3ª Dosis + Leucemia + Antirrábica</option>
                                        </optgroup>
                                        <optgroup label="15 a 17 semanas">
                                            <option value="sextuple_f3">Refuerzo final (si lo indica el veterinario)</option>
                                        </optgroup>
                                        <optgroup label="Cada 1 a 3 años">
                                            <option value="sextuple_f4">Refuerzo Triple Felina + Leucemia + Antirrábica</option>
                                        </optgroup>
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="form-row">
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="vac_edad_modal">Edad</label>
                                <input type="text" class="form-control form-control-sm" id="vac_edad_modal">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="vac_fecha_dosis_modal">Fecha dosis</label>
                                <input type="date" class="form-control form-control-sm" id="vac_fecha_dosis_modal">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="vac_nombre_modal">Vacuna</label>
                                <input type="text" class="form-control form-control-sm" id="vac_nombre_modal">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="vac_proxima_dosis_modal">Próx. dosis</label>
                                <input type="date" class="form-control form-control-sm" id="vac_proxima_dosis_modal">
                            </div>
                        </div>
                        <div class="modal-atencion-acciones">
                            <button type="button" class="btn btn-success btn-sm" id="btn_add_vacuna_modal"><i class="feather icon-plus"></i> Agregar vacuna</button>
                        </div>

                        <div class="titulo-item">Vacunas registradas</div>
                        <div class="table-responsive modal-atencion-tabla">
                            <table id="tabla_vacunas_mascotas" class="table table-sm">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Edad</th>
                                        <th class="align-middle">Fecha dosis</th>
                                        <th class="align-middle">Vacuna</th>
                                        <th class="align-middle">Próx. dosis</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_vacunas_mascotas_body"></tbody>
                            </table>
                        </div>
                    </div>

                    <!--Desparasitación-->
                    <div class="tab-pane fade" id="desparasita" role="tabpanel" aria-labelledby="desparasita-tab">
                        <h6 class="t-aten">Registrar desparasitación</h6>
                        <p class="modal-atencion-ayuda">Anote el antiparasitario aplicado y cuándo corresponde la próxima dosis.</p>

                        <div class="form-row">
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="des_fecha_dosis_modal">Fecha dosis</label>
                                <input type="date" class="form-control form-control-sm" id="des_fecha_dosis_modal">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="des_antiparasitario_modal">Antiparasitario</label>
                                <input type="text" class="form-control form-control-sm" id="des_antiparasitario_modal">
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="des_tipo_modal">Tipo</label>
                                <select class="form-control form-control-sm" id="des_tipo_modal">
                                    <option value="">Seleccione</option>
                                    <option value="Externo">Externo</option>
                                    <option value="Interno">Interno</option>
                                    <option value="Interno y Externo">Interno y Externo</option>
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                <label class="floating-label-activo-sm" for="des_proxima_dosis_modal">Próx. dosis</label>
                                <input type="date" class="form-control form-control-sm" id="des_proxima_dosis_modal">
                            </div>
                        </div>
                        <div class="modal-atencion-acciones">
                            <button type="button" class="btn btn-success btn-sm" id="btn_add_desparasitacion_modal"><i class="feather icon-plus"></i> Agregar desparasitación</button>
                        </div>

                        <div class="titulo-item">Desparasitaciones registradas</div>
                        <div class="table-responsive modal-atencion-tabla">
                            <table id="tabla_desparacitacion" class="table table-sm">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Fecha dosis</th>
                                        <th class="align-middle">Antiparasitario</th>
                                        <th class="align-middle">Tipo</th>
                                        <th class="align-middle">Próx. dosis</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_desparasitacion_body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>

    function formatoFechaCorta(fecha) {
        if (!fecha) {
            return '-';
        }
        var partes = fecha.split('-');
        if (partes.length !== 3) {
            return fecha;
        }
        return partes[2] + '-' + partes[1] + '-' + partes[0];
    }

    function obtenerIdMascotaModalGes() {
        if ($('#id_mascota_fc').length && $('#id_mascota_fc').val()) {
            return $('#id_mascota_fc').val();
        }

        var params = new URLSearchParams(window.location.search || '');
        return params.get('id_mascota') || params.get('id_dependiente_activo') || '';
    }

    function urlConId(baseUrl, idMascota) {
        return baseUrl.replace('__ID__', idMascota);
    }

    function tokenCsrfModalGes() {
        if (typeof CSRF_TOKEN !== 'undefined' && CSRF_TOKEN) {
            return CSRF_TOKEN;
        }
        return $('meta[name="csrf-token"]').attr('content');
    }

    function renderTablaVacunasModal(vacunas) {
        var $tbody = $('#tabla_vacunas_mascotas_body');
        $tbody.html('');
        if (!Array.isArray(vacunas) || vacunas.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>');
            return;
        }

        vacunas.forEach(function (item) {
            var html = '<tr>' +
                '<td class="align-middle">' + (item.edad || '-') + '</td>' +
                '<td class="align-middle"><span class="badge badge-secondary">' + formatoFechaCorta(item.fecha_dosis) + '</span></td>' +
                '<td class="align-middle">' + (item.vacuna || '-') + '</td>' +
                '<td class="align-middle"><span class="badge badge-info">' + formatoFechaCorta(item.proxima_dosis) + '</span></td>' +
                '</tr>';
            $tbody.append(html);
        });
    }

    function renderTablaDesparasitacionModal(desparasitaciones) {
        var $tbody = $('#tabla_desparasitacion_body');
        $tbody.html('');
        if (!Array.isArray(desparasitaciones) || desparasitaciones.length === 0) {
            $tbody.append('<tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>');
            return;
        }

        desparasitaciones.forEach(function (item) {
            var html = '<tr>' +
                '<td class="align-middle"><span class="badge badge-secondary">' + formatoFechaCorta(item.fecha_dosis) + '</span></td>' +
                '<td class="align-middle">' + (item.antiparasitario || '-') + '</td>' +
                '<td class="align-middle">' + (item.tipo || '-') + '</td>' +
                '<td class="align-middle"><span class="badge badge-info">' + formatoFechaCorta(item.proxima_dosis) + '</span></td>' +
                '</tr>';
            $tbody.append(html);
        });
    }

    function limpiarFormularioVacunaModal() {
        $('#vac_edad_modal').val('');
        $('#vac_fecha_dosis_modal').val('');
        $('#vac_nombre_modal').val('');
        $('#vac_proxima_dosis_modal').val('');
        $('#form_ges .esquema-vacunas').val('');
    }

    // El resumen de la mascota del menú lateral muestra la última desparasitación y si las
    // vacunas están al día; se actualiza con lo que se va registrando acá
    function refrescarResumenSanitario(vacunas, desparasitaciones) {
        if (typeof actualizarResumenSanitarioMascota === 'function') {
            actualizarResumenSanitarioMascota(vacunas, desparasitaciones);
        }
    }

    function limpiarFormularioDesparasitacionModal() {
        $('#des_fecha_dosis_modal').val('');
        $('#des_antiparasitario_modal').val('');
        $('#des_tipo_modal').val('');
        $('#des_proxima_dosis_modal').val('');
    }

    function cargarRegistrosSanitariosModal() {
        var idMascota = obtenerIdMascotaModalGes();
        if (!idMascota) {
            renderTablaVacunasModal([]);
            renderTablaDesparasitacionModal([]);
            return;
        }

        var url = urlConId("{{ route('paciente.mascotas.registros_sanitarios', ['mascotaId' => '__ID__']) }}", idMascota);
        $.get(url)
            .done(function (resp) {
                renderTablaVacunasModal(resp.vacunas || []);
                renderTablaDesparasitacionModal(resp.desparasitaciones || []);
                refrescarResumenSanitario(resp.vacunas || [], resp.desparasitaciones || []);
            })
            .fail(function () {
                renderTablaVacunasModal([]);
                renderTablaDesparasitacionModal([]);
            });
    }

    function agregarVacunaDesdeModal() {
        var idMascota = obtenerIdMascotaModalGes();
        if (!idMascota) {
            swal({ title: 'No se encontró la mascota', icon: 'warning' });
            return;
        }

        var data = {
            _token: tokenCsrfModalGes(),
            edad: $('#vac_edad_modal').val(),
            fecha_dosis: $('#vac_fecha_dosis_modal').val(),
            vacuna: $('#vac_nombre_modal').val(),
            proxima_dosis: $('#vac_proxima_dosis_modal').val(),
        };

        var url = urlConId("{{ route('paciente.mascotas.vacunas.guardar', ['mascotaId' => '__ID__']) }}", idMascota);
        $.post(url, data)
            .done(function (resp) {
                renderTablaVacunasModal(resp.vacunas || []);
                refrescarResumenSanitario(resp.vacunas || [], null);
                limpiarFormularioVacunaModal();
            })
            .fail(function (xhr) {
                var msj = 'No se pudo guardar la vacuna';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    var primerCampo = Object.keys(xhr.responseJSON.error)[0];
                    if (primerCampo && xhr.responseJSON.error[primerCampo][0]) {
                        msj = xhr.responseJSON.error[primerCampo][0];
                    }
                }
                swal({ title: msj, icon: 'warning' });
            });
    }

    function agregarDesparasitacionDesdeModal() {
        var idMascota = obtenerIdMascotaModalGes();
        if (!idMascota) {
            swal({ title: 'No se encontró la mascota', icon: 'warning' });
            return;
        }

        var data = {
            _token: tokenCsrfModalGes(),
            fecha_dosis: $('#des_fecha_dosis_modal').val(),
            antiparasitario: $('#des_antiparasitario_modal').val(),
            tipo: $('#des_tipo_modal').val(),
            proxima_dosis: $('#des_proxima_dosis_modal').val(),
        };

        var url = urlConId("{{ route('paciente.mascotas.desparasitaciones.guardar', ['mascotaId' => '__ID__']) }}", idMascota);
        $.post(url, data)
            .done(function (resp) {
                renderTablaDesparasitacionModal(resp.desparasitaciones || []);
                refrescarResumenSanitario(null, resp.desparasitaciones || []);
                limpiarFormularioDesparasitacionModal();
            })
            .fail(function (xhr) {
                var msj = 'No se pudo guardar la desparasitación';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    var primerCampo = Object.keys(xhr.responseJSON.error)[0];
                    if (primerCampo && xhr.responseJSON.error[primerCampo][0]) {
                        msj = xhr.responseJSON.error[primerCampo][0];
                    }
                }
                swal({ title: msj, icon: 'warning' });
            });
    }

    $(document).ready(function () {
        $(document).on('shown.bs.modal', '#form_ges', function () {
            cargarRegistrosSanitariosModal();
        });

        $(document).on('click', '#btn_add_vacuna_modal', function () {
            agregarVacunaDesdeModal();
        });

        $(document).on('click', '#btn_add_desparasitacion_modal', function () {
            agregarDesparasitacionDesdeModal();
        });

        // Al elegir una vacuna del esquema se copian su nombre y la edad a los campos del registro
        $(document).on('change', '#form_ges .esquema-vacunas', function () {
            var $opcion = $(this).find('option:selected');
            if (!$opcion.val()) {
                return;
            }

            $('#form_ges .esquema-vacunas').not(this).val('');
            $('#vac_nombre_modal').val($.trim($opcion.text()));
            $('#vac_edad_modal').val($opcion.closest('optgroup').attr('label') || '');
        });
    });


    /** MANEJO DE IMAGENES GES */

    var lista_archivo_ges = {};

    function cargar_lista_archivo_ges(obj_dropzone, alias_examen)

    {

        // console.log('--------------cargar_lista_archivo_ges----------------------');

        lista_archivo_ges[alias_examen] = [];

        let temp  = obj_dropzone.getAcceptedFiles();

        $.each(temp, function( index, value )

        {

            if(value.status == "success")

            {

                if(value.xhr !== undefined)

                {

                    var archivo_temp = JSON.parse(value.xhr.response);

                    lista_archivo_ges[alias_examen][index] = [

                        url=archivo_temp.archivo.url,

                        nombre_origian= archivo_temp.archivo.original_file_name,

                        nombre_archivo = archivo_temp.archivo.nombre_archivo,

                        file_extension = archivo_temp.archivo.file_extension,

                    ];

                    $('#input_lista_archivo_ges').val('');

                    $('#input_lista_archivo_ges').val(JSON.stringify(lista_archivo_ges));

                }

            }

        });

    }
    /** CIERRE MANEJO DE IMAGENES */



    function registrar_ges_ficha()

    {

        var validar = 0;

        var mensaje ='';



        let nombre_ges = $('#nombre_ges').val();

        let id_ges = $('#id_ges').val();



        let nombre_institucion_ficha_ges = $('#nombre_institucion_ficha_ges').val();

        let direccion_institucion_ficha_ges = $('#direccion_institucion_ficha_ges').val();

        let id_profesional = $('#id_profesional').val();

        let nombre_responsable_ficha_ges = $('#nombre_responsable_ficha_ges').val();

        let rut_responsable_ficha_ges = $('#rut_responsable_ficha_ges').val();



        let id_paciente = $('#id_paciente_fc').val();



        let confirmacion_diagnostica_ficha_ges = $('#confirmacion_diagnostica_ficha_ges').val();

        let paciente_tratamiento_ficha_ges = $('#paciente_tratamiento_ficha_ges').val();



        // lista_archivo_ges

        let lista_archivo_ges = $('#input_lista_archivo_ges').val();



        let id_ficha_atencion = $('#id_fc').val();

        let id_lugar_atencion = $('#id_lugar_atencion').val();

        let hora_medica = $('#hora_medica').val();

        // let codigo_validacion_informe_ges = $('#codigo_validacion_informe_ges').val();





        // if(nombre_institucion_ficha_ges == '')

        // {

        //     $('#nombre_institucion_ficha_ges').focus();

        //     validar = 1;



        // }

        // if(direccion_institucion_ficha_ges == '')

        // {

        //     $('#direccion_institucion_ficha_ges').focus();

        //     validar = 1;



        // }



        // if(nombre_responsable_ficha_ges == '')

        // {

        //     $('#nombre_responsable_ficha_ges').focus();

        //     validar = 1;



        // }

        // if(rut_responsable_ficha_ges == '')

        // {

        //     $('#rut_responsable_ficha_ges').focus();

        //     validar = 1;



        // }



        if(confirmacion_diagnostica_ficha_ges == '')

        {

            $('#confirmacion_diagnostica_ficha_ges').focus();

            mensaje += ' Debe ingresar Confirmación diagnóstica GES.\n' ;

            validar = 1;



        }

        if(paciente_tratamiento_ficha_ges == '')

        {

            $('#paciente_tratamiento_ficha_ges').focus();

            mensaje += ' Debe Confimar si el paciente se encuentra en tratamiento.\n' ;

            validar = 1;



        }

        if(nombre_ges == '')

        {

            $('#nombre_ges').focus();

            mensaje += ' Debe ingresar el Diagnóstico GES.\n' ;

            validar = 1;

        }

        // if(id_paciente == '')

        // {

        //     $('#id_paciente').focus();

        //     validar = 1;

        // }

        // if(id_profesional == '')

        // {

        //     $('#id_profesional').focus();

        //     validar = 1;

        // }

        // if(id_ficha_atencion == '')

        // {

        //     $('#id_ficha_atencion').focus();

        //     validar = 1;

        // }

        // if(id_lugar_atencion == '')

        // {

        //     $('#id_lugar_atencion').focus();

        //     validar = 1;

        // }

        // if(hora_medica == '')

        // {

        //     $('#hora_medica').focus();

        //     validar = 1;

        // }



        if(validar == 1)

        {

            swal({

                title: "Debe ingresar todos los datos requeridos." ,

                text: mensaje,

                icon: "error",

            })

            return false;

        }

        else

        {



            $.ajax({

                url: "{{ route('ficha_atencion.registrar_diagnostico_ges') }}",

                type: 'GET',

                dataType: 'json',

                data: {

                    nombre_institucion_ficha_ges : nombre_institucion_ficha_ges,

                    direccion_institucion_ficha_ges : direccion_institucion_ficha_ges,

                    nombre_responsable_ficha_ges : nombre_responsable_ficha_ges,

                    rut_responsable_ficha_ges : rut_responsable_ficha_ges,

                    confirmacion_diagnostica_ficha_ges : confirmacion_diagnostica_ficha_ges,

                    paciente_tratamiento_ficha_ges : paciente_tratamiento_ficha_ges,

                    id_ges : id_ges,

                    nombre_ges : nombre_ges,

                    id_paciente : id_paciente,

                    id_profesional : id_profesional,

                    id_ficha_atencion : id_ficha_atencion,

                    id_lugar_atencion : id_lugar_atencion,

                    hora_medica : hora_medica,

                    // codigo_verificacion : codigo_validacion_informe_ges,

                    codigo_verificacion : '',

                    lista_archivo_ges : lista_archivo_ges,

                },

            })

            .done(function(resp) {

                console.log(resp);



                if (resp != '')

                {

                    if(resp.estado == 1)

                    {

                        console.log(resp);

                        //$('#form_control_obesidad').trigger("reset");

                        $('#nombre_ges').val('');

                        $('#id_ges').val('');



                        $('#nombre_responsable_ficha_ges').val('');

                        $('#rut_responsable_ficha_ges').val('');

                        $('#confirmacion_diagnostica_ficha_ges').val('');

                        $('#paciente_tratamiento_ficha_ges').val('');

                        $('#input_lista_archivo_ges').val('');

                        $('#codigo_validacion_informe_ges').val('');



                        $('#mensaje').text('Se ha creado Diagnostico GES de forma correcta');

                        $('#mensaje').show();

                        $('#form_ges').modal('hide');



                        swal({

                            title: "Constancia GES (Artículo 24 Ley 19.966).",

                            text: 'Registro Exitoso.\n El paciente ha sido Notificado\n La constancia puede ser recuperada desde su escritorio (Documentos).',

                            icon: "success",

                        });

                    }

                    else

                    {

                        swal({

                            title: "Constancia GES (Artículo 24 Ley 19.966).",

                            text: 'Registro Fallido.',

                            icon: "error",

                        });

                    }

                }

                else

                {

                    swal({

                        title: "Constancia GES (Artículo 24 Ley 19.966).",

                        text: 'Registro Fallido.',

                        icon: "error",

                    });

                }

            })

            .fail(function(e) {

                console.log("error");

                console.log(e);

            })

        }

    };



    function ver_pdf_constancia_ges(id_ficha_atencion)

    {



        var variables = '';

        variables += '?id_ficha_atencion='+$('#id_fc').val();

        variables += '&nombre_institucion_ficha_ges='+$('#nombre_institucion_ficha_ges').val();

        variables += '&direccion_institucion_ficha_ges='+$('#direccion_institucion_ficha_ges').val();

        variables += '&nombre_responsable_ficha_ges='+$('#nombre_responsable_ficha_ges').val();

        variables += '&rut_responsable_ficha_ges='+$('#rut_responsable_ficha_ges').val();

        variables += '&confirmacion_diagnostica_ficha_ges='+$('#confirmacion_diagnostica_ficha_ges').val();

        variables += '&paciente_tratamiento_ficha_ges='+$('#paciente_tratamiento_ficha_ges').val();

        variables += '&fecha_ficha_ges='+$('#fecha_ficha_ges').val();

        variables += '&hora_ficha_ges='+$('#hora_ficha_ges').val();

        variables += '&id_ges_diagnostico='+$('#id_ges_diagnostico').val();

        variables += '&nombre_ges='+$('#nombre_ges').val();

        variables += '&funcionalidad='+$('#funcionalidad').val();



        Fancybox.show(

            [

                {

                src: '{{ route("ficha_atencion.vista.previa.pdf.ges") }}'+variables,

                type: "iframe",

                preload: false,

                },

            ]

        );

    }



</script>

@include('general.secciones_ficha.partials.sanitarios_solo_lectura')
