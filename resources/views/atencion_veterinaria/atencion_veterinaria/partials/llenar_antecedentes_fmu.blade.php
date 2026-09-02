<div class="row">
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <ul class="nav nav-tabs-aten nav-fill mb-3" id="ev-crec_des_trauma" role="tablist">
            <li class="nav-item">
                <a class="nav-link-aten text-reset active" id="pat-cro-tab" onclick="cargarRegistrosAntecedentesSidebar(1)" data-toggle="tab" href="#pat-cro" role="tab" aria-controls="pat-cro" aria-selected="true">Patologías crónicas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link-aten text-reset" id="anest-pac-tab" onclick="cargarRegistrosAntecedentesSidebar(2)" data-toggle="tab" href="#anest-pac" role="tab" aria-controls="anest-pac" aria-selected="false">Anestesias paciente</a>
            </li>
            <li class="nav-item">
                <a class="nav-link-aten text-reset" id="cirug-proce-tab" onclick="cargarRegistrosAntecedentesSidebar(3)" data-toggle="tab" href="#cirug-proce" role="tab" aria-controls="cirug-proce" aria-selected="false">Cirugías y procedimientos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link-aten text-reset" id="hemorragias-tab" onclick="cargarRegistrosAntecedentesSidebar(4)" data-toggle="tab" href="#hemorragias" role="tab" aria-controls="hemorragias" aria-selected="false">Hemorragias</a>
            </li>
            <li class="nav-item">
                <a class="nav-link-aten text-reset" id="ant-serv-asistenciales-tab" onclick="cargarRegistrosAntecedentesSidebar(5)" data-toggle="tab" href="#ant-serv-asistenciales" role="tab" aria-controls="ant-serv-asistenciales" aria-selected="false">Solic. de antecedentes de servicios asistenciales</a>
            </li>
            <li class="nav-item">
                <a class="nav-link-aten text-reset" id="alergias-tab" onclick="cargarRegistrosAntecedentesSidebar(6)" data-toggle="tab" href="#alergias" role="tab" aria-controls="alergias" aria-selected="false">Alergias</a>
            </li>
            <li class="nav-item">
                <a class="nav-link-aten text-reset" id="med-cro-tab" onclick="cargarRegistrosAntecedentesSidebar(7)" data-toggle="tab" href="#med-cro" role="tab" aria-controls="med-cro" aria-selected="false">Medicamentos crónicos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link-aten text-reset" id="discapacidad-tab" onclick="cargarRegistrosAntecedentesSidebar(8)" data-toggle="tab" href="#discapacidad" role="tab" aria-controls="discapacidad" aria-selected="false">Discapacidades</a>
            </li>
        </ul>
    </div>
    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
        <div class="tab-content" id="trauma">
            <!--PATOLOGÍAS CRÓNICAS-->
            <div class="tab-pane fade show active" id="pat-cro" role="tabpanel" aria-labelledby="pat-cro-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <h6 class="text-c-blue d-inline">PATOLOGÍAS CRÓNICAS</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-xxs feather icon-plus d-inline" onclick="verModalAgregar('show',1,0)">Añadir</button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Comentario</th>
                                        <th>Profesional</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar1">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--ANESTESIAS PACIENTE-->
            <div class="tab-pane fade show" id="anest-pac" role="tabpanel" aria-labelledby="anest-pac-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 d-inline mb-2">
                        <h6 class="text-c-blue d-inline float-left pt-3 f-16">ANESTESIAS PACIENTE</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-sm float-right d-inline" onclick="verModalAgregar('show',2,0)"><i class="fas fa-plus"></i> Añadir</button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Procedimiento</th>
                                        <th>Incidentes</th>
                                        <th>Profesional</th>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar2">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--CIRUGÍAS Y PROCEDIMIENTOS-->
            <div class="tab-pane fade show" id="cirug-proce" role="tabpanel" aria-labelledby="cirug-proce-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 d-inline">
                        <h6 class="text-c-blue d-inline">CIRUGÍAS Y PROCEDIMIENTOS</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-xxs  feather icon-plus d-inline" onclick="verModalAgregar('show',3,0)">Añadir</button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Procedimiento</th>
                                        <th>Incidente</th>
                                        <th>Profesional</th>
                                        <th>Fecha data</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar3">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--HEMORAGIAS-->
            <div class="tab-pane fade show" id="hemorragias" role="tabpanel" aria-labelledby="hemorragias-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 d-inline">
                        <h6 class="text-c-blue d-inline">HEMORAGIAS</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-xxs  feather icon-plus d-inline" onclick="verModalAgregar('show',4,0)">Añadir</button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Incidente</th>
                                        <th>Detalle</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar4">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--SOLICITUDES DE ANTECEDENTES DE SERVICIOS ASISTENCIALES-->
            <div class="tab-pane fade show" id="ant-serv-asistenciales" role="tabpanel" aria-labelledby="ant-serv-asistenciales-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <h6 class="text-c-blue d-inline">SOLICITUDES DE ANTECEDENTES DE SERVICIOS ASISTENCIALES</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-xxs feather icon-plus d-inline" onclick="verModalAgregar('show',5,0)">Añadir</button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Patología</th>
                                        <th>Clínica o servicio</th>
                                        <th>Fecha Aproximada</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar5">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--ALERGIAS-->
            <div class="tab-pane fade show" id="alergias" role="tabpanel" aria-labelledby="alergias-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <h6 class="text-c-blue d-inline">ALERGIAS</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-xxs  feather icon-plus d-inline" onclick="verModalAgregar('show',6,0)"></button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Nombre Alergia</th>
                                        <th>Comentario</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar6">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!--MEDICAMENTOS CRÓNICOS-->
            <div class="tab-pane fade show" id="med-cro" role="tabpanel" aria-labelledby="med-cro-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <h6 class="text-c-blue d-inline">MEDICAMENTOS CRÓNICOS</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-xxs feather icon-plus d-inline" onclick="verModalAgregar('show',7,0)"></button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Nombre Medicamento Crónico</th>
                                        <th>Dosis</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar7">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!--DISCAPACIDADES-->
            <div class="tab-pane fade show" id="discapacidad" role="tabpanel" aria-labelledby="discapacidad-tab">
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <h6 class="text-c-blue d-inline">DISCAPACIDADES</h6>
                        @if(Auth::user()->hasRole('Profesional'))
                        <button type="button" class="btn btn-info btn-xxs feather icon-plus d-inline" onclick="verModalAgregar('show',8,0)"></button>
                        @endif
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-xs">
                                <thead>
                                    <tr>
                                        <th>Discapacidad</th>
                                        <th>Grado</th>
                                        <th>Reversibilidad</th>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="bloque-registros-sidebar8">
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!--MODAL-->
    <style>
        #modal-ingreso .modal-dialog {
            max-width: 560px;
        }
        #modal-ingreso .modal-content {
            border: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(25, 42, 70, .24);
        }
        #modal-ingreso #body-modal-inputs table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }
        #modal-ingreso #body-modal-inputs td {
            padding: 0;
            vertical-align: top;
        }
        #modal-ingreso #body-modal-inputs td:first-child {
            width: 38%;
            padding: 9px 16px 0 0;
            color: #43536a;
            font-weight: 600;
        }
        #modal-ingreso #body-modal-inputs .form-control {
            width: 100%;
            min-height: 40px;
            border-radius: 8px;
        }
        #modal-ingreso #body-modal-inputs textarea.form-control {
            min-height: 84px;
            resize: vertical;
        }
        #modal-ingreso .modal-footer {
            gap: 8px;
            flex-wrap: wrap;
        }
        #modal-ingreso .modal-footer .btn {
            margin: 0;
            border-radius: 7px;
        }
        @media (max-width: 575.98px) {
            #modal-ingreso #body-modal-inputs table,
            #modal-ingreso #body-modal-inputs tbody,
            #modal-ingreso #body-modal-inputs tr,
            #modal-ingreso #body-modal-inputs td {
                display: block;
                width: 100%;
            }
            #modal-ingreso #body-modal-inputs td:first-child {
                width: 100%;
                padding: 0 0 5px;
            }
        }
    </style>
    <div class="modal" id="modal-ingreso" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header bg-info py-3">
                <h5 class="modal-title text-white" id="title-antecedente"><i class="feather icon-clipboard mr-2"></i>Agregar antecedente veterinario</h5>
                <button type="button" class="close text-white" onclick="verModalAgregar('hide')" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4 py-3" id="body-modal-inputs">

            </div>
            <div class="modal-footer bg-light px-4 py-3">
                <input type="hidden" value="" id="id-antecedente-m">
                <input type="hidden" value="" id="tipo-antecedente-m">
                @if(isset($userData))
                <input type="hidden" value="{{$userData['rut']}}" id="user-rut">
                <input type="hidden" value="{{$userData['profesion']}}" id="user-profesion">
                <input type="hidden" value="{{$userData['nombre']}} {{$userData['apellido_uno']}} {{$userData['apellido_dos']}}" id="user-profesional">
                @endif
                <input type="hidden" value="{{Auth::user()->id}}" id="user-id">
                <button type="button" class="btn btn-sm btn-info" id="agregar-antecedente" onclick="guardarAntecedenteVeterinario()"><i class="feather icon-save"></i> Guardar antecedente</button>
                <button type="button" class="btn btn-sm btn-info" id="modificar-antecedente" onclick="modificarAntecedenteVeterinario()"><i class="feather icon-edit"></i> Guardar cambios</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="verModalAgregar('hide')"><i class="feather icon-x"></i> Cancelar</button>
            </div>
            </div>
        </div>
    </div>
<script>
    // const activarMedicamentos = (input) => {
	// 	$("#"+input).autocomplete({
	// 		source: function(request, response) {
	// 			$.ajax({
	// 				url: "{{ route('dental.getArticulo') }}",
	// 				type: 'post',
	// 				dataType: "json",
	// 				data: {
	// 					_token: CSRF_TOKEN,
	// 					search: request.term
	// 				},
	// 				success: function(data) {
	// 					console.log(data.length);
	// 					response(data);
	// 				}
	// 			});
	// 		},
	// 		select: function(event, ui) {
	// 			$('#'+input).val(ui.item.label);
	// 			return false;
	// 		}
	// 	});
	// }
    const verModalAgregar = (fun,tipo,id)=>{

        $('#agregar-antecedente').show();
        $('#modificar-antecedente').hide();
        $('#id-antecedente-m').val('');

        var html = '';
        var titulos = {
            1: 'Patología crónica',
            2: 'Antecedente de anestesia',
            3: 'Cirugía o procedimiento',
            4: 'Antecedente de hemorragia',
            5: 'Solicitud de antecedentes asistenciales',
            6: 'Alergia',
            7: 'Medicamento crónico',
            8: 'Discapacidad'
        };
        $('#title-antecedente').html(
            '<i class="feather icon-clipboard mr-2"></i>' +
            (id ? 'Editar ' : 'Agregar ') + (titulos[tipo] || 'antecedente veterinario')
        );

        switch(tipo){
            case 1:
                html+=`
                    <table>
                        <tr>
                            <td>Patología crónica</td>
                            <td><input class="form-control" type="text" id="nombre" placeholder="Nombre de la patología"></td>
                        </tr>
                        <tr>
                            <td>Observaciones</td>
                            <td><textarea class="form-control" id="comentario" placeholder="Antecedentes, evolución o tratamiento"></textarea></td>
                        </tr>
                    </table>
                `;
            break;

            case 2:
                html+=`
                    <table>
                        <tr>
                            <td>Procedimiento anestésico</td>
                            <td><input class="form-control" type="text" id="procedimiento"></td>
                        </tr>
                        <tr>
                            <td>Incidente o reacción</td>
                            <td><textarea class="form-control" id="comentario"></textarea></td>
                        </tr>
                    </table>
                `;
            break;

            case 3:
                html+=`
                    <table>
						<tr>
                            <td>Fecha Cirugía</td>
                            <td><input class="form-control" type="date" id="fecha"></td>
                        </tr>
                        <tr>
                            <td>Procedimiento</td>
                            <td><input class="form-control" type="text" id="procedimiento"></td>
                        </tr>
                        <tr>
                            <td>Incidente</td>
                            <td><textarea class="form-control" id="comentario"></textarea></td>
                        </tr>
                    </table>
                `;
            break;

            case 4:
                html+=`
                    <table>
                        <tr>
                            <td>Procedimiento</td>
                            <td><input class="form-control" type="text" id="procedimiento"></td>
                        </tr>
                        <tr>
                            <td>Detalle</td>
                            <td><textarea class="form-control" id="comentario"></textarea></td>
                        </tr>
                    </table>
                `;
            break;


            case 5:

                html+=`
                    <table>
                        <tr>
                            <td>Nombre antecedente</td>
                            <td><input class="form-control form-control-sm" type="text" id="procedimiento"></td>
                        </tr>
                        <tr>
                            <td>Institución</td>
                            <td><textarea class="form-control form-control-sm" id="institucion"></textarea></td>
                        </tr>
						<tr>
                            <td>Fecha Evento</td>
                            <td><input class="form-control" type="date" id="fecha"></td>
                        </tr>
                    </table>
                `;
            break;

            case 6:
                html+=`
                    <table>
                        <tr>
                            <td>Nombre alergia</td>
                            <td><input class="form-control form-control-sm" type="text" id="nombre"></td>
                        </tr>
                        <tr>
                            <td>Detalle</td>
                            <td><textarea class="form-control form-control-sm" id="comentario"></textarea></td>
                        </tr>
                    </table>
                `;
            break;

            case 7:
                html+=`
                    <table>
                        <tr>
                            <td>Nombre Medicamento</td>
                            <td>
								<div class="form-group">
									<input class="form-control form-control-sm" type="text" id="nombre_medicamento_cronico">
								</div>
							</td>
                        </tr>
                        <tr>
                            <td>Dosis</td>
                            <td><textarea class="form-control" id="dosis"></textarea></td>
                        </tr>

                    </table>
                `;
            break;
		    case 8:
                html+=`
                    <table>
                        <tr>
                            <td>Tipo de Discapacidad</td>
                            <td>
								<select class="form-control form-control-sm" name="discapacidad_tipo" id="discapacidad_tipo">
									<option value="Auditíva">Auditíva</option>
									<option value="Visual">Visual</option>
									<option value="Locomotora">Locomotora </option>
									<option value="Neurológica">Neurológica</option>
									<option value="Fonoarticulatoria">Fonoarticulatoria</option>
									<option value="Cognitiva">Cognitiva</option>
								</select>
							</td>
                        </tr>
                        <tr>
                            <td>Grado</td>
                            <td>
								<input class="form-control form-control-sm" type="text" id="discapacidad_grado">
							</td>
                        </tr>
						<tr>
                            <td>Permanente</td>
                            <td>
								<select class="form-control form-control-sm" name="discapacidad_permanente" id="discapacidad_permanente">
									<option value="si">SI</option>
									<option value="no">NO</option>
								</select>
							</td>
                        </tr>

                    </table>
                `;
            break;
        }

        $('#body-modal-inputs').html(html);
		if (tipo == 7 && typeof activarMedicamentos === 'function')
			activarMedicamentos('nombre_medicamento_cronico');
        $('#tipo-antecedente-m').val(tipo);
        $('#modal-ingreso').modal(fun);

        if(id!=0)
        {
            $('#agregar-antecedente').hide();
            $('#modificar-antecedente').show();
            $('#id-antecedente-m').val(id);
            cargarDatosAntecedente(id);
        }

    }

    /*
     * Guarda los antecedentes creados desde el modal veterinario.
     * Este include antes solo construía el formulario, pero no enviaba sus datos.
     */
    window.guardarAntecedenteVeterinario = function () {
        var tipo = $('#tipo-antecedente-m').val();
        var idPaciente = $('#id_paciente').val() || $('#id_paciente_fc').val();
        var idUsuario = $('#user-id').val();
        var campo = function (selector) {
            var elemento = $(selector);
            return elemento.length ? (elemento.val() || '').toString().trim() : '';
        };

        var data = {
            id_paciente: idPaciente,
            id_tipo_antecedente: tipo,
            id_users: idUsuario,
            rut_responsable: campo('#user-rut'),
            profesion: campo('#user-profesion'),
            profesional: campo('#user-profesional'),
            nombre: campo('#nombre'),
            comentario: campo('#comentario'),
            procedimiento: campo('#procedimiento'),
            nombre_medicamento_cronico: campo('#nombre_medicamento_cronico'),
            fecha: campo('#fecha'),
            dosis: campo('#dosis'),
            institucion: campo('#institucion'),
            discapacidad_tipo: campo('#discapacidad_tipo'),
            discapacidad_grado: campo('#discapacidad_grado'),
            discapacidad_permanente: campo('#discapacidad_permanente'),
            estado: 1
        };

        // El controlador exige "nombre". En estos tipos el nombre visible usa
        // otro campo, por lo que se normaliza antes de enviar.
        if (!data.nombre) {
            data.nombre = data.procedimiento || data.nombre_medicamento_cronico || data.discapacidad_tipo;
        }

        if (!data.id_paciente || !data.id_users) {
            if (window.Swal) {
                Swal.fire('No se pudo guardar', 'No se identificó la mascota o el profesional activo.', 'error');
            } else {
                alert('No se identificó la mascota o el profesional activo.');
            }
            return;
        }

        if (!data.nombre) {
            if (window.Swal) {
                Swal.fire('Dato requerido', 'Complete el nombre o procedimiento del antecedente.', 'warning');
            } else {
                alert('Complete el nombre o procedimiento del antecedente.');
            }
            return;
        }

        var boton = $('#agregar-antecedente');
        boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{Request::root()}}/api/antecedente/registrar',
            type: 'POST',
            data: data,
            dataType: 'json'
        }).done(function (resp) {
            if (Number(resp.estado) !== 1) {
                var detalle = resp.error
                    ? (typeof resp.error === 'string' ? resp.error : Object.values(resp.error).join(', '))
                    : (resp.msg || 'No fue posible guardar el antecedente.');
                if (window.Swal) {
                    Swal.fire('No se pudo guardar', detalle, 'warning');
                } else {
                    alert(detalle);
                }
                return;
            }

            verModalAgregar('hide');
            cargarRegistrosAntecedentesSidebar(tipo);
            if (typeof window.cargarRegistrosAntecedentes === 'function') {
                window.cargarRegistrosAntecedentes(tipo);
            }

            if (window.Swal) {
                Swal.fire({
                    title: 'Antecedente guardado',
                    text: 'El nuevo antecedente veterinario quedó registrado.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false
                });
            } else if (typeof window.msg === 'function') {
                msg('Antecedente', 'Registro ingresado.', 'success');
            }
        }).fail(function (xhr) {
            var mensaje = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Ocurrió un error al guardar el antecedente.';
            if (window.Swal) {
                Swal.fire('Error', mensaje, 'error');
            } else {
                alert(mensaje);
            }
        }).always(function () {
            boton.prop('disabled', false).html('<i class="feather icon-save"></i> Guardar antecedente');
        });
    };

    window.cargarDatosAntecedente = function (id) {
        $.ajax({
            url: '{{Request::root()}}/api/antecedente/ver_registro',
            type: 'GET',
            dataType: 'json',
            data: { id: id }
        }).done(function (resp) {
            if (Number(resp.estado) !== 1 || !resp.registros) {
                if (window.Swal) {
                    Swal.fire('Error', resp.msg || 'No se encontró el antecedente.', 'error');
                }
                return;
            }

            var datos = resp.registros.antecedente_data || {};
            [
                'procedimiento',
                'comentario',
                'nombre',
                'fecha',
                'nombre_medicamento_cronico',
                'dosis',
                'institucion',
                'discapacidad_tipo',
                'discapacidad_grado',
                'discapacidad_permanente'
            ].forEach(function (campo) {
                var elemento = $('#' + campo);
                if (elemento.length) {
                    elemento.val(datos[campo] == null ? '' : datos[campo]);
                }
            });
        }).fail(function () {
            if (window.Swal) {
                Swal.fire('Error', 'No fue posible cargar el antecedente para editarlo.', 'error');
            }
        });
    };

    window.modificarAntecedenteVeterinario = function () {
        var id = $('#id-antecedente-m').val();
        var tipo = $('#tipo-antecedente-m').val();
        var idPaciente = $('#id_paciente').val() || $('#id_paciente_fc').val();
        var campo = function (selector) {
            var elemento = $(selector);
            return elemento.length ? (elemento.val() || '').toString().trim() : '';
        };
        var data = {
            id: id,
            id_paciente: idPaciente,
            id_tipo_antecedente: tipo,
            id_users: $('#user-id').val(),
            rut_responsable: campo('#user-rut'),
            profesion: campo('#user-profesion'),
            profesional: campo('#user-profesional'),
            nombre: campo('#nombre'),
            comentario: campo('#comentario'),
            procedimiento: campo('#procedimiento'),
            nombre_medicamento_cronico: campo('#nombre_medicamento_cronico'),
            fecha: campo('#fecha'),
            dosis: campo('#dosis'),
            institucion: campo('#institucion'),
            discapacidad_tipo: campo('#discapacidad_tipo'),
            discapacidad_grado: campo('#discapacidad_grado'),
            discapacidad_permanente: campo('#discapacidad_permanente'),
            estado: 1
        };

        if (!data.nombre) {
            data.nombre = data.procedimiento || data.nombre_medicamento_cronico || data.discapacidad_tipo;
        }

        if (!data.id || !data.id_paciente || !data.id_users) {
            if (window.Swal) {
                Swal.fire('No se pudo guardar', 'Falta identificar el antecedente, la mascota o el profesional.', 'error');
            }
            return;
        }

        var boton = $('#modificar-antecedente');
        boton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '{{Request::root()}}/api/antecedente/modificar',
            type: 'POST',
            dataType: 'json',
            data: data
        }).done(function (resp) {
            if (Number(resp.estado) !== 1) {
                var detalle = resp.error
                    ? (typeof resp.error === 'string' ? resp.error : Object.values(resp.error).join(', '))
                    : (resp.msg || 'No fue posible modificar el antecedente.');
                if (window.Swal) {
                    Swal.fire('No se pudo guardar', detalle, 'warning');
                }
                return;
            }

            verModalAgregar('hide');
            cargarRegistrosAntecedentesSidebar(tipo);
            if (window.Swal) {
                Swal.fire({
                    title: 'Cambios guardados',
                    text: 'El antecedente veterinario fue actualizado.',
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        }).fail(function (xhr) {
            var mensaje = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Ocurrió un error al actualizar el antecedente.';
            if (window.Swal) {
                Swal.fire('Error', mensaje, 'error');
            }
        }).always(function () {
            boton.prop('disabled', false).html('<i class="feather icon-edit"></i> Guardar cambios');
        });
    };

    function cambiar_antecedente_sidebar()
    {
        if($('#nuevo_antecedente').val() != 'n_C')
        {
            var nombre_enfermedad = $("#nuevo_antecedente option:selected").text();
            var tipo = $("#nuevo_antecedente").val();

            $('#agregar-antecedente').show();
            $('#modificar-antecedente').hide();
            $('#modificar-antecedente-cancelar').hide();

            $('#modal-body-input').html('');
            var html = '';
            console.log(tipo);
            switch(tipo)
            {
                case '2':
                    html+=`
                        <table class="display table  table-borderless dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Procedimiento</td>
                                <td><input class="form-control" type="text" id="procedimiento"></td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Incidente</td>
                                <td><textarea class="form-control" id="comentario"></textarea></td>
                            </tr>
                        </table>
                    `;
                break;
                case '1':
                    html+=`
                        <table class="display table table-borderless  dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Nombre</td>
                                <td>
                                    <select class="form-control form-control-sm" id="nombre" name="nombre" onchange="toggleOtraEnfermedadCronica(this.value)">
                                        <option value="">Seleccione enfermedad crónica</option>
                                        <option value="Obesidad">Obesidad</option>
                                        <option value="Hipertensión arterial">Hipertensión arterial</option>
                                        <option value="Diabetes">Diabetes</option>
                                        <option value="Insuficiencia renal">Insuficiencia renal</option>
                                        <option value="EPOC">EPOC</option>
                                        <option value="Dislipidemias">Dislipidemias</option>
                                        <option value="__otro__">Otra Patología Crónica (Especifique)</option>
                                    </select>
                                    <input type="text" class="form-control form-control-sm mt-2" id="nombre_otra_enfermedad" placeholder="Escriba la patología crónica..." style="display:none;">
                                </td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Comentarios</td>
                                <td><textarea class="form-control" id="comentario"></textarea></td>
                            </tr>
                        </table>
                    `;
                break;
                case '3':
                    html+=`
                        <table class="display table table-borderless dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Fecha Cirugía</td>
                                <td><input class="form-control" type="date" id="fecha"></td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Procedimiento</td>
                                <td><input class="form-control" type="text" id="procedimiento"></td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Incidente</td>
                                <td><textarea class="form-control" id="comentario"></textarea></td>
                            </tr>
                        </table>
                    `;
                break;
                case '4':
                    html+=`
                        <table class="display table table-borderless dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Procedimiento</td>
                                <td><input class="form-control" type="text" id="procedimiento"></td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Detalle</td>
                                <td><textarea class="form-control" id="comentario"></textarea></td>
                            </tr>
                        </table>
                    `;
                break;
                case '5':
                    html+=`
                        <table class="display table table-borderless dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Nombre antecedente</td>
                                <td><input class="form-control form-control-sm" type="text" id="procedimiento"></td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Institución</td>
                                <td><textarea class="form-control form-control-sm" id="institucion"></textarea></td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Fecha Evento</td>
                                <td><input class="form-control" type="date" id="fecha"></td>
                            </tr>
                        </table>
                    `;
                break;
                case '6':
                    html+=`
                        <table class="display table table-borderless dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Nombre alergia</td>
                                <td><input class="form-control form-control-sm" type="text" id="nombre"></td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Detalle</td>
                                <td><textarea class="form-control form-control-sm" id="comentario"></textarea></td>
                            </tr>
                        </table>
                    `;
                break;
                case '7':
                    html+=`
                        <table class="display table table-borderless dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Nombre Medicamento</td>
                                <td>
                                    <div class="form-group">
                                        <input class="form-control form-control-sm" type="text" id="nombre_medicamento_cronico">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Dosis</td>
                                <td><textarea class="form-control" id="dosis"></textarea></td>
                            </tr>

                        </table>
                    `;
                break;
                case '8':
                    html+=`
                        <table class="display table table-borderless  dt-responsive nowrap pb-4 table-sm" style="width:100%">
                            <tr>
                                <td class="f-16 font-weight-bold">Tipo de Discapacidad</td>
                                <td>
                                    <select class="form-control form-control-sm" name="nombre" id="nombre">
                                        <option value="Auditíva">Auditíva</option>
                                        <option value="Visual">Visual</option>
                                        <option value="Locomotora">Locomotora </option>
                                        <option value="Neurológica">Neurológica</option>
                                        <option value="Fonoarticulatoria">Fonoarticulatoria</option>
                                        <option value="Cognitiva">Cognitiva</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Grado</td>
                                <td>
                                    <input class="form-control form-control-sm" type="text" id="discapacidad_grado">
                                </td>
                            </tr>
                            <tr>
                                <td class="f-16 font-weight-bold">Permanente</td>
                                <td>
                                    <select class="form-control form-control-sm" name="discapacidad_permanente" id="discapacidad_permanente">
                                        <option value="si">SI</option>
                                        <option value="no">NO</option>
                                    </select>
                                </td>
                            </tr>

                        </table>
                    `;
                break;
            }
            console.log(tipo);
                cargarRegistrosAntecedentesSidebar(tipo);
            // if(tipo == 1){
            //     cargarRegistrosAntecedentes(tipo);
            // }

            $('#titulo_antecedente').html('Añadir '+nombre_enfermedad);
            $('#modal-body-input').html(html);
            $('#tipo-antecedente-m').val(tipo);
            $('#id-antecedente-m').val('');

            if( tipo == 7)
            {
                activarMedicamentos('nombre_medicamento_cronico');
                // ver_medicamento_cronico();// ver tabla medicamentos cronicos generales
            }

        }
        else
        {
            $('#modal-body-input').html('');
            $('#nuevo_antecedente').val(1);
            cambiar_antecedente();
        }
    }

     const cargarRegistrosAntecedentesSidebar = (tipo) => {

        const headersTabla = {
            1: ['Nombre', 'Comentario', 'Profesional', 'Fecha', 'Acción'],
            2: ['Procedimiento', 'Incidentes', 'Profesional', 'Fecha', 'Acción'],
            3: ['Fecha', 'Procedimiento', 'Incidente', 'Profesional', 'Fecha Registro', 'Acción'],
            4: ['Procedimiento', 'Comentario', 'RUT', 'Profesional', 'Fecha', 'Acción'],
            5: ['Patología', 'Clínica o servicio', 'Fecha Aproximada', 'Acción'],
            6: ['Nombre Alergia', 'Comentario', 'Fecha', 'Acción'],
            7: ['Nombre Medicamento', 'Dosis', 'Fecha', 'Acción'],
            8: ['Discapacidad', 'Grado', 'Reversibilidad', 'Fecha', 'Acción'],
        };

        // El sidebar no debe modificar la cabecera de la tabla modal
        // Solo actualiza el bloque correspondiente

        var data = {};
        var url = '{{Request::root()}}/api/antecedente/ver_registros';
        var id_paciente = $('#id_paciente').val();
        if(id_paciente == undefined || id_paciente == '')
        {
            id_paciente = $('#id_paciente_fc').val();
        }
        data.id_tipo_antecedente = tipo;
        data.estado = 1;
        data.id_paciente = id_paciente;

        // Debug para tipo 7
        if(tipo == 7) {
            console.log('🔍 Cargando medicamentos crónicos (tipo 7)');
            console.log('📋 Datos enviados:', data);
        }

        $.ajax({
            url: url,
            type: "GET",
            data: data,
            success: (resp)=>{
                console.log(`📊 Respuesta para tipo ${tipo}:`, resp);
                console.log('hola:', resp); // Debug adicional
                if(resp.estado==1)
                {
                    var html_ = '';
                    var permiso_ = '';
                    var id_users = parseInt($('#user-id').val());

                    // Debug específico para tipo 7
                    if(tipo == 7) {
                        console.log('💊 Registros de medicamentos encontrados:', resp.registros);
                        console.log('🔢 Cantidad de registros:', resp.registros ? resp.registros.length : 0);
                    }

                    resp.registros.forEach(e => {

                        permiso_ = '';
                        if(e.id_users == id_users)
                        permiso_ = `
                            <buttom class="btn btn-icon btn-info feather icon-edit-2" onclick="verModalAgregar('show',${tipo},${e.id})"></buttom>
                            <buttom class="btn btn-icon btn-danger feather icon-x-square" onclick="verModalDesactivar('show',${tipo},${e.id})"></buttom>
                        `;

                        // Debug para cada registro tipo 7
                        if(tipo == 7) {
                            console.log('💊 Procesando medicamento:', e.antecedente_data);
                        }

                        switch(tipo)
                        {
                            case 1:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.nombre}</td>
                                        <td>${e.antecedente_data.comentario}</td>
                                        <td>${e.antecedente_data.profesional} <br/>${e.antecedente_data.rut_responsable}</td>
                                        <td>${e.antecedente_data.fecha_regitro}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                            case 2:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.procedimiento}</td>
                                        <td>${e.antecedente_data.comentario}</td>
                                        <td>${e.antecedente_data.profesional}<br/>${e.antecedente_data.rut_responsable}</td>
                                        <td>${e.antecedente_data.fecha_regitro}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                            case 3:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.fecha}</td>
                                        <td>${e.antecedente_data.procedimiento}</td>
                                        <td>${e.antecedente_data.comentario}</td>
                                        <td>${e.antecedente_data.profesional} <br/>${e.antecedente_data.rut_responsable}</td>
                                        <td>${e.antecedente_data.fecha_regitro}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                            case 4:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.procedimiento}</td>
                                        <td>${e.antecedente_data.comentario}</td>
                                        <td>${e.antecedente_data.rut_responsable}</td>
                                        <td>${e.antecedente_data.profesional}</td>
                                        <td>${e.antecedente_data.fecha_regitro}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                            case 5:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.procedimiento}</td>
                                        <td>${e.antecedente_data.institucion}</td>
                                        <td>${e.antecedente_data.fecha}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                            case 6:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.nombre}</td>
                                        <td>${e.antecedente_data.comentario}</td>
                                        <td>${e.antecedente_data.fecha_regitro}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                            case 7:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.nombre_medicamento_cronico || 'Sin nombre'}</td>
                                        <td>${e.antecedente_data.dosis || 'Sin dosis'}</td>
                                        <td>${e.antecedente_data.fecha_regitro}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                            case 8:
                                html_ +=`
                                    <tr>
                                        <td>${e.antecedente_data.discapacidad_tipo}</td>
                                        <td>${e.antecedente_data.discapacidad_grado}</td>
                                        <td>${e.antecedente_data.discapacidad_permanente}</td>
                                        <td>${e.antecedente_data.fecha_regitro}</td>
                                        <td>${permiso_}</td>
                                    </tr>
                                `;
                            break;
                        }

                    });

                // Debug final para tipo 7
                if(tipo == 7) {
                    console.log('🎯 HTML generado para medicamentos:', html_);
                    console.log('📍 Insertando en:', '#bloque-registros-sidebar7');
                }

                $('#bloque-registros-sidebar'+tipo).html(html_);
                // No tocar la tabla del modal
                }
                else
                {
                    // No hay registros activos: limpiar la tabla del sidebar
                    $('#bloque-registros-sidebar'+tipo).html('');
                }
            },
            error: (resp)=>{
                console.warn(`❌ Error cargando tipo ${tipo}:`, resp);
            }
        });
    }

    // const cargarDatosAntecedente = (id) => {

    //     var data = {};
    //     var url = '{{Request::root()}}/api/antecedente/ver_registro';

    //     data.id = id;

    //     $.ajax({
    //     url: url,
    //     type: "GET",
    //     data: data,
    //     success: (resp)=>{
    //         if(resp.estado==1)
    //         {
    //             $('#procedimiento').val(resp.registros.antecedente_data.procedimiento);
    //             $('#comentario').val(resp.registros.antecedente_data.comentario);
    //             $('#nombre').val(resp.registros.antecedente_data.nombre);
    //             $('#fecha').val(resp.registros.antecedente_data.fecha);
    //             $('#nombre_medicamento_cronico').val(resp.registros.antecedente_data.nombre_medicamento_cronico);
    //             $('#dosis').val(resp.registros.antecedente_data.dosis);
    //             $('#institucion').val(resp.registros.antecedente_data.institucion);
    //             $('#discapacidad_tipo').val(resp.registros.antecedente_data.discapacidad_tipo);
    //             $('#discapacidad_grado').val(resp.registros.antecedente_data.discapacidad_grado);
    //             $('#discapacidad_permanente').val(resp.registros.antecedente_data.discapacidad_permanente);
    //         }
    //     },
    //     error: (resp)=>{
    //         console.warn(resp);
    //     }
    // });

    // }

    // Carga inicial de la pestaña activa por defecto (Patologías Crónicas = tipo 1)
    $(document).ready(function() {
        cargarRegistrosAntecedentesSidebar(1);
    });

    function toggleOtraEnfermedadCronica(valor) {
        if (valor === '__otro__') {
            $('#nombre_otra_enfermedad').show().focus();
        } else {
            $('#nombre_otra_enfermedad').hide().val('');
        }
    }

    // Intercepta el clic en "Agregar antecedentes" ANTES del onclick inline (capture phase)
    // para inyectar el texto libre en el select cuando se eligió "Otro (especifique)"
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('#agregar-antecedente');
        if (!btn) return;
        var selectNombre = document.getElementById('nombre');
        if (selectNombre && selectNombre.value === '__otro__') {
            var texto = ($('#nombre_otra_enfermedad').val() || '').trim();
            if (texto) {
                // Crear opción temporal solo para el envío
                var opt = document.createElement('option');
                opt.value = texto;
                opt.text  = texto;
                opt.selected = true;
                opt.id    = '_tmp_otro_nombre';
                selectNombre.appendChild(opt);
                // Eliminar la opción temporal después de enviar
                setTimeout(function() {
                    var prev = document.getElementById('_tmp_otro_nombre');
                    if (prev) prev.remove();
                }, 1000);
            }
        }
    }, true);
</script>
