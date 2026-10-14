@extends('template.asistente_unificado.template')


{{--  ESTILOS PROPIOD DE LA VISTA   --}}

@section('page-styles')

    <link href='{{ asset('css/perfiles_usuarios.css') }}' rel='stylesheet' />

@endsection



{{--  CONTENIDO  --}}

@section('content')

	<!--Container Completo-->

	<div class="pcoded-main-container">

		<div class="pcoded-content">

			<div class="user-profile user-card mb-4">

				<div class="card-body py-0">

					<div class="user-about-block m-0">

						<div class="row">

							<div class="col-md-12 text-center mt-n4">

								<div class="change-profile text-center">

									<div class="dropdown w-auto d-inline-block">

										<a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

											<div class="profile-dp">

												<div class="position-relative d-inline-block">

													<img id="foto-asistente" class="img-radius img-fluid wid-100" src="{{ storage_public_url($asistente->foto_perfil) ?: asset('images/iconos/usuario_asistente.svg') }}" alt="User image"><input id="foto-asistente-input" type="file" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
												</div>

												<div class="overlay">

													<span>Actualizar</span>

												</div>

											</div>

										</a>

										<div class="dropdown-menu">

											<a class="dropdown-item" href="#" onclick="document.getElementById('foto-asistente-input').click(); return false;"><i class="feather icon-upload-cloud mr-2"></i>Cambiar foto de perfil</a>
											<a class="dropdown-item" href="#" id="eliminar-foto-asistente"><i class="feather icon-trash-2 mr-2"></i>Eliminar fotografía</a>
										</div>

									</div>

								</div>

							</div>

							<div class="col-md-12 mt-md-2">

								<ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">

									<li class="nav-item">

										<a class="nav-link text-reset active" id="personal-tab" data-toggle="tab" href="#info_personal" role="tab" aria-controls="info_personal" aria-selected="true">Información personal</a>

									</li>

                                    <li class="nav-item">

                                        <a class="nav-link text-reset" id="liquidacion-tab" data-toggle="tab" href="#info_liquidacion" role="tab" aria-controls="info_liquidacion" aria-selected="false">Manejo de Liquidaciones</a>

                                    </li>

									<li class="nav-item">

										<a class="nav-link text-reset" id="emergencia-tab" data-toggle="tab" href="#emergencia" role="tab" aria-controls="emergencia" aria-selected="false">Contacto de emergencia</a>

									</li>

									<li class="nav-item">

										<a class="nav-link text-reset" id="pass-tab" data-toggle="tab" href="#pass" role="tab" aria-controls="pass" aria-selected="false">Mi cuenta</a>

									</li>

								</ul>

							</div>

						</div>

					</div>

				</div>

			</div>

			<div class="row">

				<div class="col-md-12">

					<div class="tab-content" id="myTabContent">

						<!--Información Personal-->

						<div class="tab-pane fade show active" id="info_personal" role="tabpanel" aria-labelledby="personal-tab">

							<div class="row">

								<div class="col-md-6">

									<!--Card Información Básica-->

									<div class="card">

										<div class="card-header d-flex align-items-center justify-content-between bg-white">

											<h5 class="mb-0"><i class="feather icon-user icono-purple"></i> Datos personales</h5>

											<button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".info_basica" aria-expanded="false" aria-controls="info_basica-1 info_basica-2">

												<i class="feather icon-edit"></i>

											</button>

										</div>

										<!--Datos Personales-->

										<div class="card-body info_basica collapse show" id="info_basica-1">

											<form>

												<div class="form-row">
													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">RUT</label>

														<div id="ver_rut"> {{ $asistente->rut }} </div>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Nombres</label>

														<div id="ver_nombre">{{ $asistente->nombres }}</div>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Primer Apellido</label>

														<div id="ver_apellido_uno">{{ $asistente->apellido_uno }}</div>

													</div>

	                                                <div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Segundo Apellido</label>

														<div id="ver_apellido_dos">{{ $asistente->apellido_dos }}</div>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Sexo</label>

														<div id="ver_sexo">

	                                                        @if ($asistente->sexo == 'M')

	                                                            Masculino

	                                                        @elseif ($asistente->sexo == 'F')

	                                                            Femenino

	                                                        @else

	                                                            Otro

	                                                        @endif

	                                                    </div>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Nacimiento</label>

														<div id="ver_nacimiento">{{ \Carbon\Carbon::parse($asistente->fecha_nac)->format('d-m-Y') }}</div>

													</div>

												</div>
											</form>

										</div>

										<!--Cierre: Datos Personales-->





										<!--(Editar)Datos Personales-->

										<div class="card-body info_basica collapse" id="info_basica-2">

											<form>

												<div class="form-row">
													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">RUT</label>

														<input type="text" class="form-control form-control-sm" placeholder="RUT" id="editar_rut" name="editar_rut"value="{{ $asistente->rut }} ">

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Nombres</label>

														<input type="text" class="form-control form-control-sm" placeholder="Nombre" id="editar_nombre" name="editar_nombre" value="{{ $asistente->nombres }}">

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Primer Apellido</label>

														<input type="text" class="form-control form-control-sm" placeholder="Primer Apellido" id="editar_apellido_uno" name="editar_apellido_uno" value="{{ $asistente->apellido_uno }}">

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Segundo Apellido</label>

														<input type="text" class="form-control form-control-sm" placeholder="Segundo Apellido" id="editar_apellido_dos" name="editar_apellido_dos" value="{{ $asistente->apellido_dos }}">

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Sexo</label>

														{{--  <div class="form-check form-check-inline">

															<input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1">

															<label class="form-check-label" for="M">Hombre</label>

														</div>  --}}

	                                                    <select class="form-control form-control-sm" id="editar_sexo" name="editar_sexo">

	                                                        <option>Seleccione su Sexo</option>

	                                                        <option value="M"

	                                                            @if ($asistente->sexo == 'M') selected @endif>Masculino

	                                                        </option>

	                                                        <option value="F"

	                                                            @if ($asistente->sexo == 'F') selected @endif>Femenino

	                                                        </option>

	                                                        <option value="O"

	                                                            @if ($asistente->sexo == 'O') selected @endif>Otro

	                                                        </option>

	                                                    </select>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Nacimiento</label>

														<input type="date" class="form-control form-control-sm" id="editar_nacimiento" name="editar_nacimiento" value="{{ \Carbon\Carbon::parse($asistente->fecha_nac)->format('Y-m-d') }}">

													</div>

												</div>
												<div class="form-row">
													<div class="col-sm-12 d-flex justify-content-end">

														<button type="button" class="btn btn-outline-dark btn-sm mr-2" data-toggle="collapse" data-target=".info_basica" aria-expanded="false" aria-controls="info_basica-1 info_basica-2" ><i class="feather icon-x"></i> Cancelar</button>

														<button type="button" class="btn btn-info btn-sm" onclick="editar_asistente_datos_personales('{{ $asistente->id }}')"><i class="feather icon-save"></i> Guardar cambios</button>

													</div>

												</div>

											</form>

										</div>

										<!--Cierre: (Editar)Datos Personales-->

									</div>

									<!--Cierre: Card Datos Personales-->

								</div>



								<div class="col-md-6">

									<!--Card Contacto-->

									<div class="card">

										<div class="card-header d-flex align-items-center justify-content-between bg-white">

											<h5 class="mb-0"><i class="feather icon-phone icono-purple"></i> Contacto</h5>

											<button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".info_contacto" aria-expanded="false" aria-controls="info_contacto_1 info_contacto_2">

												<i class="feather icon-edit"></i>

											</button>

										</div>

										<!--Contacto-->

										<div class="card-body info_contacto collapse show" id="info_contacto_1">

											<form>

												<div class="form-row">
													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Correo Electrónico</label>

														<div id="ver_email" name="ver_email">{{ $asistente->email }}</div>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Teléfono</label>

														<div id="ver_telefono_uno" name="ver_telefono_uno">{{ $asistente->telefono_uno }}</div>

													</div>

												</div>
											</form>

										</div>

										<!--Cierre: Contacto-->

										<!--(Editar) Contacto-->

										<div class="card-body info_contacto collapse" id="info_contacto_2">

											<form>

												<div class="form-row">
													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Correo electrónico</label>

														<input type="text" class="form-control form-control-sm" placeholder="Correo Electrónico" id="editar_email" name="editar_email"  value="{{ $asistente->email }}">

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Teléfono</label>

														<input type="text" class="form-control form-control-sm" placeholder="Teléfono"  id="editar_telefono_uno" name="editar_telefono_uno" value="{{ $asistente->telefono_uno }}">

													</div>

												</div>
												<div class="form-row">
													<div class="col-sm-12 d-flex justify-content-end">

														<button type="button" class="btn btn-outline-dark btn-sm mr-2" data-toggle="collapse" data-target=".info_contacto" aria-expanded="false" aria-controls="info_contacto_1 info_contacto_2"><i class="feather icon-x"></i> Cancelar</button>

														<button type="button" class="btn btn-info btn-sm" onclick="editar_asistente_datos_contacto('{{ $asistente->id }}');" ><i class="feather icon-save"></i> Guardar cambios</button>

													</div>

												</div>

											</form>

										</div>

										<!--(Editar) Contacto-->

									</div>

									<!--Cierre: Card Contacto-->

									<!--Card Residencia-->

									<div class="card">

										<div class="card-header d-flex align-items-center justify-content-between bg-white">

											<h5 class="mb-0"><i class="feather icon-map-pin icono-purple"></i> Residencia</h5>

											<button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".info_residencial" aria-expanded="false" aria-controls="info_residencial_1 info_residencial_2">

												<i class="feather icon-edit"></i>

											</button>

										</div>

										<!--Residencia-->

										<div class="card-body info_residencial collapse show" id="info_residencial_1">

											<form>

												<div class="form-row">
													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Región</label>

														<div id="ver_region">{{ $direccion_region_nombre }}</div>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Ciudad</label>

														@if($asistente->Direccion()->first())

															<div id="ver_ciudad"> {{ $asistente->Direccion()->first()->Ciudad()->first()->nombre }} </div>

														@else

															<div id="ver_ciudad"></div>

														@endif

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Dirección</label>

														@if($asistente->Direccion()->first())

															<div id="ver_direccion"> {{ $asistente->Direccion()->first()->direccion . ' #' . $asistente->Direccion()->first()->numero_dir }} </div>

														@else

															<div id="ver_direccion"></div>

														@endif

													</div>

												</div>
											</form>

										</div>

										<!--Cierre: Residencia-->

										<!--(Editar) Residencia-->

										<div class="card-body info_residencial collapse " id="info_residencial_2">

											<form>

                                                <div class="form-row">
	                                                <div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Región</label>

														<select class="form-control form-control-sm" id="region_agregar" name="region_agregar" onchange="buscar_ciudad();">

															@foreach ($regiones as $reg)

	                                                            @if ($direccion_region == $reg->id)

	                                                                <option value="{{ $reg->id }}" selected>{{ $reg->nombre }}</option>

	                                                            @else

	                                                                <option value="{{ $reg->id }}">{{ $reg->nombre }}</option>

	                                                            @endif

	                                                        @endforeach

														</select>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Ciudad</label>

														<select class="form-control form-control-sm" id="ciudad_agregar" name="ciudad_agregar">

															@foreach ($ciudades as $ciudad)

	                                                            @if ($direccion_ciudad == $ciudad->id)

	                                                                <option value="{{ $ciudad->id }}" selected>{{ $ciudad->nombre }}</option>

	                                                            @else

	                                                                <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>

	                                                            @endif

	                                                        @endforeach

														</select>

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Dirección</label>

														@if($asistente->Direccion()->first())

															<input type="text" class="form-control form-control-sm" placeholder="Dirección" id="direccion" name="direccion" value="{{ $asistente->Direccion()->first()->direccion }}">

														@else

															<input type="text" class="form-control form-control-sm" placeholder="Dirección" id="direccion" name="direccion" value="">

														@endif

													</div>

	                                                <div class="form-group col-sm-12 col-md-6">
														<label class="floating-label-activo">Número</label>

														@if($asistente->Direccion()->first())

															<input type="text" class="form-control form-control-sm" placeholder="Número" id="numero_dir" name="numero_dir" value="{{ $asistente->Direccion()->first()->numero_dir }}">

														@else

															<input type="text" class="form-control form-control-sm" placeholder="Número" id="numero_dir" name="numero_dir" value="">

														@endif

													</div>

                                                </div>
												<div class="form-row">
													<div class="col-sm-12 d-flex justify-content-end">

														<button type="button" class="btn btn-outline-dark btn-sm mr-2" data-toggle="collapse" data-target=".info_residencial" aria-expanded="false" aria-controls="info_residencial_1 info_residencial_2"><i class="feather icon-x"></i> Cancelar</button>

														<button type="button" class="btn btn-info btn-sm" onclick="editar_asistente_datos_residencia('{{ $asistente->id }}')"><i class="feather icon-save"></i> Guardar cambios</button>

													</div>

												</div>

											</form>

										</div>

										<!--(Editar) Residencia-->

									</div>

									<!--Cierre: Card Residencia-->

								</div>

							</div>

						</div>

						<!--Cierre: Tab Información Personal-->



                        <!--Tab info de Liquidacion -->

                        @include('general.seccion_perfil.seccion_liquidacion')



						<!--Tab Contactos de Emergencia-->

						<div class="tab-pane fade" id="emergencia" role="tabpanel" aria-labelledby="emergencia-tab">

							<div class="row">

                                <div class="col-md-12">

                                    <div class="card">

                                        <div class="card-header d-flex align-items-center justify-content-between bg-white">

                                            <h5 class="mb-0"><i class="feather icon-user-plus icono-purple"></i> Contactos de emergencia</h5>

                                            <button type="button" onclick="modal_agregar_contacto_emergencia();" class="btn btn-info btn-sm m-0">

                                                <i class="feather icon-plus"></i> Agregar contacto

                                            </button>

                                        </div>

                                        <div class="card-body">

                                            <div class="row">

                                                <div class="col-md-12">

                                                    <div style="overflow-x:auto;">

                                                        <table id="contactos_emergencia" class="display table table-striped table-hover dt-responsive nowrap table-sm" style="width:100%">



                                                            @if ($contacto != null)

                                                                <thead>

                                                                    <tr>

                                                                        <th class="align-middle text-center">Prioridad</th>

                                                                        <th class="align-middle text-center">Nombre</th>

                                                                        <th class="align-middle text-center">Parentesco</th>

                                                                        <th class="align-middle text-center">Acción</th>

                                                                    </tr>

                                                                </thead>

                                                                <tbody>

                                                                @foreach ($contacto as $c)



                                                                        <tr>

                                                                            <td class="align-middle text-center">

                                                                                {{ $c->prioridad }}

                                                                            </td>

                                                                            <td class="align-middle text-center">

                                                                                {{ $c->nombre }}

                                                                                <br>{{ $c->apellido_uno . ' ' . $c->apellido_dos }}

                                                                            </td>

                                                                            <td class="align-middle text-center">

                                                                                {{ $c->parentezco }}

                                                                            </td>

                                                                            <td class="align-middle text-center">



                                                                                <button id="btn_info_contacto"

                                                                                    onclick="cargar_datos_contacto({{ $c->id }})"

                                                                                    class="btn btn-outline-purple btn-icon"

                                                                                    data-toggle="modal"

                                                                                    data-target="#info_contacto_emergencia"

                                                                                    title="Información de contacto"

                                                                                    data-placement="top"><i

                                                                                        class="feather icon-phone-call"></i>

                                                                                </button>



                                                                                <button id="btn_editar_contacto"

                                                                                    onclick="cargar_datos_contacto({{ $c->id }})"

                                                                                    class="btn btn-outline-purple btn-icon"

                                                                                    data-toggle="modal"

                                                                                    data-target="#editar_contacto_emergencia"

                                                                                    title="Editar contacto"

                                                                                    data-placement="top"><i

                                                                                        class="feather icon-edit"></i>

                                                                                </button>



                                                                                <button

                                                                                    class="btn btn-danger btn-icon"

                                                                                    onclick="eliminar_contacto_asistente({{ $c->id . ',' . $asistente->id }})"

                                                                                    data-toggle="tooltip"

                                                                                    title="Eliminar contacto">

                                                                                    <i class="feather icon-x"></i>

                                                                                </button>



                                                                            </td>

                                                                        </tr>

                                                                    @endforeach

                                                                </tbody>

                                                            @else

                                                                <tbody>

                                                                    <tr>

                                                                        <td><span>NO EXISTEN REGISTROS</span></td>



                                                                    </tr>

                                                                </tbody>

                                                            @endif

                                                        </table>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

						</div>





						<!--Tab mi cuenta-->

						<div class="tab-pane fade" id="pass" role="tabpanel" aria-labelledby="pass-tab">

							<div class="row">

								<div class="col-md-6">

									<!--Card Contraseña Personal-->

									<div class="card">

										<div class="card-header d-flex align-items-center justify-content-between bg-white">

											<h5 class="mb-0"><i class="feather icon-lock icono-purple"></i> Cambiar contraseña</h5>

											<button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".pass_personal" aria-expanded="false" aria-controls="pass_personal_1 pass_personal_2">

												<i class="feather icon-edit"></i>

											</button>

										</div>

										<!--Contraseña Personal-->

										<div class="card-body pass_personal collapse show" id="pass_personal_1">

											<form>

												<div class="form-row">
													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Contraseña actual</label>

														<div> •••••••• </div>

													</div>

												</div>
											</form>

										</div>

										<!--Cierre: Contraseña Personal-->

										<!--(Editar)Contraseña Personal-->

										<div class="card-body pass_personal collapse" id="pass_personal_2">

											<form method="get" action="{{ route('perfil.cambio_contrasena')}}" id="form_cambio_contrasena_perfil" name="form_cambio_contrasena_perfil">

                                                @csrf

                                                <div class="form-row">
	                                                <div class="form-group col-12">
	                                                    <label class="floating-label-activo">Contraseña Actual</label>

	                                                    <div class="campo-clave">
	                                                    	<input type="password" class="form-control form-control-sm" placeholder="Contraseña Actual" id="contrasena_actual" name="contrasena_actual">
	                                                    	<button type="button" class="campo-clave-ver" data-ver-clave="contrasena_actual" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
	                                                    </div>
	                                                </div>

	                                                <div class="form-group col-12">
	                                                    <label class="floating-label-activo">Nueva Contraseña</label>

	                                                    <div class="campo-clave">
	                                                    	<input type="password" class="form-control form-control-sm" placeholder="Nueva Contraseña" id="password_registro" name="password_registro">
	                                                    	<button type="button" class="campo-clave-ver" data-ver-clave="password_registro" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
	                                                    </div>
	                                                </div>

	                                                <div class="form-group col-12">
	                                                    <label class="floating-label-activo">Repita su Contraseña</label>

	                                                    <div class="campo-clave">
	                                                    	<input type="password" class="form-control form-control-sm" placeholder="Repita la Contraseña" id="password_confirmacion_registro" name="password_confirmacion_registro">
	                                                    	<button type="button" class="campo-clave-ver" data-ver-clave="password_confirmacion_registro" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
	                                                    </div>
	                                                </div>

                                                </div>
                                                <div class="form-row">
                                                    <div class="col-sm-12 d-flex justify-content-end">

                                                        <div class="btn btn-outline-dark btn-sm mr-2"><i class="feather icon-x"></i> Cancelar</div>

                                                        <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>

                                                    </div>

                                                </div>

                                            </form>

										</div>

										<!--Cierre: (Editar)Contraseña Personal-->

									</div>

									<!--Cierre: Card Contraseña Personal-->

								</div>

								<div class="col-md-6">

									<!--Card Contraseña Personal-->

									<div class="card">

										<div class="card-header d-flex align-items-center justify-content-between bg-white">

											<h5 class="mb-0"><i class="feather icon-search icono-purple"></i> Configuración motores de búsqueda</h5>

											<button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".buscadores" aria-expanded="false" aria-controls="buscadores_1 buscadores_2">

												<i class="feather icon-edit"></i>

											</button>

										</div>

										<!--Contraseña Personal-->

										<div class="card-body buscadores collapse show" id="buscadores_1">

											<form>

												<div class="form-row">
													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Aparecer en buscador de asistente</label>

	                                                    @if($asistente->buscador == 1)

														<div id="mensaje_buscador" name="mensaje_buscador"> Si </div>

	                                                    @else

	                                                    <div id="mensaje_buscador" name="mensaje_buscador"> NO </div>

	                                                    @endif

													</div>

													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Modalidad de trabajo</label>

	                                                    @if ($asistente->id_modalidad == 1)

	                                                    <div id="mensaje_modalidad" name="mensaje_modalidad"> Online </div>

	                                                    @elseif($asistente->id_modalidad == 2)

	                                                    <div id="mensaje_modalidad" name="mensaje_modalidad"> Presencial </div>

	                                                    @elseif($asistente->id_modalidad == 3)

	                                                    <div id="mensaje_modalidad" name="mensaje_modalidad"> Online - Presencial </div>

	                                                    @else

	                                                    <div id="mensaje_modalidad" name="mensaje_modalidad"> No informado </div>

	                                                    @endif

													</div>

	                                                <div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Curriculum</label>

	                                                    <div>

	                                                        <button type="bottom" class="btn btn-sm btn-success">Descarga</button>

	                                                    </div>

	                                                </div>

												</div>
											</form>

										</div>

										<!--Cierre: Contraseña Personal-->

										<!--(Editar)Contraseña Personal-->

										<div class="card-body buscadores collapse" id="buscadores_2">

                                            {{--  <form method="POST" enctype="multipart/form-data" id="upload-file" action="{{ route('asistente.editar_configuracion_busqueda') }}" >  --}}

                                                {{--  @csrf  --}}

                                                <div class="form-row">
	                                                <div class="form-group col-12">
	                                                    <label class="font-weight-bolder d-block ml-0 mb-1">Aparecer en buscador de asistente</label>

	                                                    <div>

	                                                        @if($asistente->buscador == 1)

	                                                            <div class="form-check form-check-inline">

	                                                                <input class="form-check-input" type="radio" name="buscador" id="buscador_activo" value="1" checked>

	                                                                <label class="form-check-label" for="buscador_activo">Si</label>

	                                                            </div>

	                                                            <div class="form-check form-check-inline">

	                                                                <input class="form-check-input" type="radio" name="buscador" id="buscador_inactivo" value="2">

	                                                                <label class="form-check-label" for="buscador_inactivo">No</label>

	                                                            </div>

	                                                        @else

	                                                            <div class="form-check form-check-inline">

	                                                                <input class="form-check-input" type="radio" name="buscador" id="buscador_activo" value="1">

	                                                                <label class="form-check-label" for="buscador_activo">Si</label>

	                                                            </div>

	                                                            <div class="form-check form-check-inline">

	                                                                <input class="form-check-input" type="radio" name="buscador" id="buscador_inactivo" value="2" checked>

	                                                                <label class="form-check-label" for="buscador_inactivo">No</label>

	                                                            </div>

	                                                        @endif

	                                                    </div>

	                                                </div>

	                                                <div class="form-group col-sm-12 col-md-6">
	                                                    <label class="floating-label-activo">Modalidad de trabajo</label>

	                                                    <select class="form-control form-control-sm" id="modalidad" name="modalidad">

	                                                        <option value="">Seleccionar</option>

	                                                        <option value="1" @if( $asistente->id_modalidad == 1 ) {{ 'selected' }} @endif >Online</option>

	                                                        <option value="2" @if( $asistente->id_modalidad == 2 ) {{ 'selected' }} @endif >Presencial</option>

	                                                        <option value="3" @if( $asistente->id_modalidad == 3 ) {{ 'selected' }} @endif >Online - Presencial</option>

	                                                    </select>

	                                                </div>

	                                                <div class="form-group col-sm-12 col-md-6">
	                                                    <label class="floating-label-activo"><h6>Curriculum</h6></label>

	                                                    <input type="file" name="cv" id="cv" placeholder="Seleccione archivo" onchange="cargar_archivo_asistente();">

	                                                </div>

                                                </div>
                                                <div class="form-row">
                                                    <div class="col-sm-12 d-flex justify-content-end">

                                                        <button type="button" class="btn btn-outline-dark btn-sm mr-2"><i class="feather icon-x"></i> Cancelar</button>

                                                        {{--  <button type="submit" class="btn btn-sm btn-info">Guardar cambios</button>  --}}

                                                        <button type="button" class="btn btn-info btn-sm" onclick="editar_configuracion_busqueda();"><i class="feather icon-save"></i> Guardar cambios</button>

                                                    </div>

                                                </div>

                                            {{--  </form>  --}}

										</div>

										<!--Cierre: (Editar)Contraseña Personal-->

									</div>

									<!--Cierre: Card Contraseña Personal-->

								</div>

							</div>

						</div>

						<!--Cierre: Tab mi cuenta-->

					</div>

				</div>

			</div>

		</div>

	</div>

	<!--Cierre: Container Completo-->





    {{--  @include( 'app.asistente.modales.modal_perfil' )  --}}

    {{--  CONTACTO DE EMERGENCIA  --}}

    @include( 'app.asistente.modales.agregar_contacto_emergencia' )

    @include( 'app.profesional.modales.informacion_contacto_emergencia' )

    @include( 'app.profesional.modales.editar_contacto_emergencia' )



@endsection



{{-- JS ADICIONALES PROPIOS DE LA VISTA  --}}

@section('page-script')
@include('app.asistente.foto_perfil_script')
    <script src="{{ asset('js/tabla_contactos_emergencia.js') }}"></script>

	<script src="{{ asset('js/tooltip_contacto_emergencia.js') }}"></script>

    <script>

        $(document).ready(function(){

            $( "#editar_telefono_uno" ).focus(function() {

				if($("#editar_telefono_uno").val() == '')

                {

                    $("#editar_telefono_uno").val("+569");

                    $("#editar_telefono_uno").unbind();

                }

            });

        })

        {{--  **** PERFIL DE ASISTENTE  --}}



        {{--  REGISTROS DATOS PERSONALES  --}}

        function editar_asistente_datos_personales(id) {



            let id_asistente = id;



            let rut = $('#editar_rut').val();

            let nombre = $('#editar_nombre').val();

            let apellido_uno = $('#editar_apellido_uno').val();

            let apellido_dos = $('#editar_apellido_dos').val();

            let sexo = $('#editar_sexo').val();

            let nacimiento = $('#editar_nacimiento').val();

            let url = "{{ route('asistentejcm.editar_datos_personales_perfil') }}";



            $.ajax({

                    url: url,

                    type: 'POST',

                    dataType: 'json',

                    data: {

                        _token: CSRF_TOKEN,

                        id_asistente: id_asistente,

                        rut: rut,

                        nombres: nombre,

                        apellido_uno: apellido_uno,

                        apellido_dos: apellido_dos,

                        sexo: sexo,

                        nacimiento: nacimiento

                    },

                })

                .done(function(response) {



                    if (response.success) {

                        swal({

                            title: "Datos del personales editados correctamente",

                            icon: "success",

                            buttons: "Aceptar",

                            DangerMode: true,

                        })



                        var text_sexo = '';

                        if(response.asistente.sexo == 'F')

                            text_sexo = 'Mujer';

                        else if(response.asistente.sexo == 'M')

                            text_sexo = 'Hombre';

                        else

                            text_sexo = 'Otro';



                        var fecha_nacimiento = moment(response.asistente.fecha_nac,'YYYY/MM/DD').format('DD/MM/YYYY');

                        $('#ver_rut').html(response.asistente.rut);

                        $('#ver_nombre').html(response.asistente.nombres);

                        $('#ver_apellido_uno').html(response.asistente.apellido_uno);

                        $('#ver_apellido_dos').html(response.asistente.apellido_dos);

                        $('#ver_sexo').html(text_sexo);

                        $('#ver_nacimiento').html(fecha_nacimiento);



                        $('#info_basica-1').addClass('show');

                        $('#info_basica-2').removeClass('show');





                    } else {

                        swal({

                            title: "Error al Editar los datos personales",

                            icon: "error",

                            buttons: "Aceptar",

                            DangerMode: true,

                        })

                    }

                })

                .fail(function() {

                    console.log("error");

                });

        }



        {{--  REGISTROS CONTACTO  --}}

        function editar_asistente_datos_contacto(id) {



            let id_asistente = id;

            let email = $('#editar_email').val();

            let telefono_uno = $('#editar_telefono_uno').val();

            let url = "{{ route('asistentejcm.editar_datos_contacto_perfil') }}";



            $.ajax({

                    url: url,

                    type: 'POST',

                    dataType: 'json',

                    data: {

                        _token: CSRF_TOKEN,

                        id_asistente: id_asistente,

                        email: email,

                        telefono_uno: telefono_uno,

                    },

                })

                .done(function(response) {



                    if (response.success) {

                        swal({

                            title: "Datos de contacto editados correctamente",

                            icon: "success",

                            buttons: "Aceptar",

                            DangerMode: true,

                        })



                        $('#ver_email').html(response.asistente.email);

                        $('#ver_telefono_uno').html(response.asistente.telefono_uno);



                        $('#info_contacto_1').addClass('show');

                        $('#info_contacto_2').removeClass('show');



                    } else {

                        swal({

                            title: "Error al Editar los datos de contacto",

                            icon: "error",

                            buttons: "Aceptar",

                            DangerMode: true,

                        })

                    }

                })

                .fail(function() {

                    console.log("error");

                })



        }



        {{--  REGISTROS RESIDENCIA  --}}

        function editar_asistente_datos_residencia(id) {



            let id_asistente = id;

            var id_region = $('#region_agregar').val();

            var nombre_region = $('#region_agregar option:selected').text();

            var id_ciudad = $('#ciudad_agregar').val();

            var nombre_ciudad = $('#ciudad_agregar option:selected').text();

            var direccion = $('#direccion').val();

            var numero_dir = $('#numero_dir').val();





            let email = $('#editar_email').val();

            let telefono_uno = $('#editar_telefono_uno').val();

            let url = "{{ route('asistentejcm.editar_datos_direccion_perfil') }}";



            $.ajax({

                    url: url,

                    type: 'POST',

                    dataType: 'json',

                    data: {

                        _token: CSRF_TOKEN,

                        id_asistente: id_asistente,

                        id_ciudad: id_ciudad,

                        direccion: direccion,

                        numero_dir: numero_dir,

                    },

                })

                .done(function(response) {



                    if (response.success) {

                        swal({

                            title: "Datos de Residencia editados correctamente",

                            icon: "success",

                            buttons: "Aceptar",

                            DangerMode: true,

                        })





                        $('#ver_region').html(nombre_region);

                        $('#ver_ciudad').html(nombre_ciudad);

                        $('#ver_direccion').html(direccion+', #'+numero_dir);



                        $('#info_residencial_1').addClass('show');

                        $('#info_residencial_2').removeClass('show');



                    } else {

                        swal({

                            title: "Error al Editar los datos de Residencia",

                            icon: "error",

                            buttons: "Aceptar",

                            DangerMode: true,

                        })

                    }

                })

                .fail(function() {

                    console.log("error");

                })



        }



        {{--  ***** CONTACTO DE EMERGENCIA  --}}

        {{--  CARGA LISTA DE CONTACTOS DE EMERGENCIA  --}}

        function cargar_lista_contacto() {



            $('#contactos_emergencia tbody').empty();

            url = "{{ route('asistentejcm.cargar_contacto_emergencia') }}";

            $.ajax({

                    url: url,

                    type: "get",

                    data: {}

                })

                .done(function(data) {



                    if (data.estado == 1) {

                        for (i = 0; i < data.registros.length; i++) {

                            var id = data.registros[i].id;

                            var prioridad = data.registros[i].prioridad;

                            var nombre = data.registros[i].nombre;

                            var apellido_uno = data.registros[i].apellido_uno;

                            var apellido_dos = data.registros[i].apellido_dos;

                            var parentezco = data.registros[i].parentezco;

                            var id_asistente = data.id_asistente;



                            var fila = '';

                            fila += '<tr>';

                            fila += '    <td class="align-middle text-center">';

                            fila += '        '+prioridad+'';

                            fila += '    </td>';

                            fila += '    <td class="align-middle text-center">';

                            fila += '        '+nombre+'';

                            fila += '        <br>'+apellido_uno+' '+apellido_dos+'';

                            fila += '    </td>';

                            fila += '    <td class="align-middle text-center">';

                            fila += '        '+parentezco+'';

                            fila += '    </td>';

                            fila += '    <td class="align-middle text-center">';

                            fila += '        <button id="btn_info_contacto"';

                            fila += '            onclick="cargar_datos_contacto('+id+')"';

                            fila += '            class="btn btn-info btn-sm rounded-circle"';

                            fila += '            data-toggle="modal"';

                            fila += '            data-target="#info_contacto_emergencia"';

                            fila += '            title="Información de contacto"';

                            fila += '            data-placement="top"><i';

                            fila += '                class="feather icon-phone-call"></i>';

                            fila += '        </button>';

                            fila += '        <button id="btn_editar_contacto"';

                            fila += '            onclick="cargar_datos_contacto('+id+')"';

                            fila += '            class="btn btn-warning btn-sm rounded-circle"';

                            fila += '            data-toggle="modal"';

                            fila += '            data-target="#editar_contacto_emergencia"';

                            fila += '            title="Editar contacto"';

                            fila += '            data-placement="top"><i';

                            fila += '                class="feather icon-edit"></i>';

                            fila += '        </button>';

                            fila += '        <button';

                            fila += '            class="btn btn-danger btn-sm rounded-circle"';

                            fila += '            onclick="eliminar_contacto_asistente('+id+', '+id_asistente+')"';

                            fila += '            data-toggle="tooltip"';

                            fila += '            title="Eliminar contacto">';

                            fila += '            <i class="feather icon-x"></i>';

                            fila += '        </button>';

                            fila += '    </td>';

                            fila += '</tr>';



                            $('#contactos_emergencia tbody').append(fila);

                        }

                    }

                    else

                    {

                        $('#contactos_emergencia tbody').html('');

                        var fila = '<tr><td colspan="4"><span><h5>no existen registros</h5></span></td></tr>'

                        $('#contactos_emergencia tbody').append(fila);

                    }

                })

                .fail(function(jqXHR, ajaxOptions, thrownError) {

                    console.log(jqXHR, ajaxOptions, thrownError)

                });

        }



        {{--  CARGA DATOS DE CONTACTO DE EMERGENCIA  --}}

        function cargar_datos_contacto(id) {

            let id_contacto = id;

            url = "{{ route('asistentejcm.cargar_datos_contacto') }}";

            $.ajax({

                    url: url,

                    type: "get",

                    data: {

                        id_contacto: id_contacto,



                    }



                })

                .done(function(data) {



                    if (data != null) {







                        $('#ver_rut_contacto').text(data.rut);

                        $('#ver_nombre_contacto').text(data.nombre + ' ' + data.apellido_uno + ' ' + data

                            .apellido_dos);

                        $('#ver_telefono_contacto').text(data.telefono);



                        $('#ver_direccion_contacto').text(data.direccion.direccion + ' ' + data.direccion.numero_dir + ' Región de ' + data.region.nombre + ', ' + data.ciudad.nombre);

                        //$('#info_contacto_emergencia').modal('show');

                        $('#ver_email_contacto').text(data.email);



                        $('#id_contacto').val(data.id);

                        $('#rut_contacto').val(data.rut);

                        $('#label_rut_contacto').addClass('floating-label-activo');



                        $('#nombres_contacto').val(data.nombre);

                        $('#label_nombres_contacto').addClass('floating-label-activo');





                        $('#apellido_uno_contacto').val(data.apellido_uno);

                        $('#label_apellido_uno_contacto').addClass('floating-label-activo');



                        $('#apellido_dos_contacto').val(data.apellido_dos);

                        $('#label_apellido_dos_contacto').addClass('floating-label-activo');



                        $('#telefono_contacto').val(data.telefono);

                        $('#label_telefono_contacto').addClass('floating-label-activo');



                        $('#direccion_contacto').val(data.direccion.direccion);

                        $('#label_direccion_contacto').addClass('floating-label-activo');



                        $('#numero_dir_contacto').val(data.direccion.numero_dir);

                        $('#label_numero_dir_contacto').addClass('floating-label-activo');





                        $('#region_agregar').val('');

                        $('#region_contacto_modificar').val(data.region.id);



                        buscar_ciudad(data.ciudad.id);



                        {{--  $("#ciudad_contacto_modificar[value=" + data.region.id + "]").attr("selected", true);  --}}

                        //$('#ciudad_contacto_modificar').text(data.ciudad.nombre);





                        //$('#info_contacto_emergencia').modal('show');

                        $('#email_contacto').val(data.email);

                        $('#label_email_contacto').addClass('floating-label-activo');



                        $('#parentezco_contacto').val(data.parentezco);

                        $('#label_parentesco_contacto').addClass('floating-label-activo');



                        $('#prioridad_contacto').val(data.prioridad);

                        $('#label_prioridad_contacto').addClass('floating-label-activo');







                    }

                })

                .fail(function(jqXHR, ajaxOptions, thrownError) {

                    console.log(jqXHR, ajaxOptions, thrownError)

                });

        }



        {{--  ELIMINAR CONTACTO EMERGENCIA  --}}

        function eliminar_contacto_asistente(contacto, asistente) {





            let id_contacto = contacto;

            let id_asistente = asistente



            let url = "{{ route('asistentejcm.eliminar_contacto_asistente') }}";



            $.ajax({

                    url: url,

                    type: "get",

                    data: {

                        id_contacto: id_contacto,

                        id_asistente: id_asistente

                    }



                })

                .done(function(data) {

                    if (data != 'error') {

                        swal({

                            title: "Contacto eliminado de forma exitosa",

                            icon: "success",

                            buttons: "Aceptar",

                            // DangerMode: true,

                        })

                        cargar_lista_contacto();

                    }

                })

                .fail(function(jqXHR, ajaxOptions, thrownError) {

                    console.log(jqXHR, ajaxOptions, thrownError)

                });

        };



        {{--  EDITAR CONTACTO DE EMERGENCIA  --}}

        function editar_contacto_emergencia() {



            let id_contacto = $('#id_contacto').val();



            let rut = $('#rut_contacto').val();

            let nombres = $('#nombres_contacto').val();

            let apellido_uno = $('#apellido_uno_contacto').val();

            let apellido_dos = $('#apellido_dos_contacto').val();

            let email = $('#email_contacto').val();

            let direccion = $('#direccion_contacto').val();

            let numero_dir = $('#numero_dir_contacto').val();



            let telefono = $('#telefono_contacto').val();

            let id_ciudad = $("#ciudad_contacto_modificar").val();

            let prioridad = $("#prioridad_contacto").val();

            let parentezco = $("#parentezco_contacto").val();

            let url = "{{ route('asistentejcm.editar_contacto') }}";



            $.ajax({

                    url: url,

                    type: "get",

                    data: {

                        id_contacto: id_contacto,

                        rut: rut,

                        nombres: nombres,

                        apellido_uno: apellido_uno,

                        apellido_dos: apellido_dos,

                        email: email,

                        direccion: direccion,

                        numero_dir: numero_dir,

                        telefono: telefono,

                        id_ciudad: id_ciudad,

                        prioridad: prioridad,

                        parentezco: parentezco

                    }



                })

                .done(function(data) {

                    if (data != null) {



                        swal({

                            title: "Contacto editado de forma exitosa",

                            icon: "success",

                            buttons: "Aceptar",

                            // DangerMode: true,

                        })

                        cargar_lista_contacto();

                        $('#editar_contacto_emergencia').modal('hide');

                    }

                })

                .fail(function(jqXHR, ajaxOptions, thrownError) {

                    console.log(jqXHR, ajaxOptions, thrownError)

                });

        };





        {{--  ABRIR MODAL AGREGAR CONTACTO EMERGENCIA  --}}

        function modal_agregar_contacto_emergencia() {

            $('#agregar_contacto_emergencia').modal('show');

        }



        {{--  BUSCCAR INFORMACION DE CONTACTO DE EMERGENCIA  --}}

        function buscar_contacto() {



            let rut_contacto = $('#rut_nuevo_contacto').val();

            let url = "{{ route('asistentejcm.buscar_contacto') }}"



            $.ajax({



                    url: url,

                    type: "get",

                    data: {

                        rut_contacto: rut_contacto,

                    },

                })

                .done(function(data) {

                    if (data !== 'vacio') {

                        if (data == 'existe') {

                            swal({

                                title: "Ya Existe el contacto emergencia en su lista",

                                icon: "error",

                                buttons: "Aceptar",

                                //SuccessMode: true,

                            })

                            $('#rut_nuevo_contacto').val('');



                        } else {

                            data = JSON.parse(data);

                            for (let i = 0; i < data.region.length; i++) {

                                if (data.region[i].id == data.ciudad.id_region) {

                                    $('#region_agregar').val(data.region[i].id);

                                    buscar_ciudad(data.ciudad.id);

                                }

                            }

                            $('#form_contacto_nuevo').show();

                            $('#nombres_contacto_emergencia').val(data.nombres);

                            $('#apellido_uno_contacto_emergencia').val(data.apellido_uno);

                            $('#apellido_dos_contacto_emergencia').val(data.apellido_dos);

                            $('#email_contacto_emergencia').val(data.email);

                            $('#telefono_contacto_emergencia').val(data.telefono_uno);

                            $('#direccion_contacto_emergencia').val(data.direccion);

                            $('#numero_dir_contacto_emergencia').val(data.numero_dir);

                            $('#fecha_nac_contacto_emergencia').val(data.fecha_nac);



                            let ciudad = data.ciudad.id;

                            {{--  $("#ciudad_agregar option[value=" + ciudad + "]").attr("selected", true);  --}}

                            $('#ciudad_agregar').val(ciudad);

                        }

                    } else {



                        swal({

                            title: "RUT no encontrado en el sistema, complete registro",

                            icon: "warning",

                            buttons: "Aceptar",

                            //SuccessMode: true,

                        })



                        // alert('RUT no encontrado en el sistema, complete registro');

                        $('#form_contacto_nuevo').show();



                    }

                })

                .fail(function(jqXHR, ajaxOptions, thrownError) {

                    console.log(jqXHR, ajaxOptions, thrownError)

                });

        }



        {{--  AGREGAR CONTACTO EMERGENCIA  --}}

        function registrar_contacto_emergencia() {



            let url = "{{ route('asistentejcm.registrar_contacto_emergencia') }}";



            let rut = $('#rut_nuevo_contacto').val();

            let nombres = $('#nombres_contacto_emergencia').val();

            let apellido_uno = $('#apellido_uno_contacto_emergencia').val();

            let apellido_dos = $('#apellido_dos_contacto_emergencia').val();

            let fecha = $('#fecha_nac_contacto_emergencia').val();

            let direccion = $('#direccion_contacto_emergencia').val();

            let id_ciudad = $('#ciudad_agregar').val();

            let email = $('#email_contacto_emergencia').val();

            let telefono = $('#telefono_contacto_emergencia').val();

            let parentezco = $('#parentezco_contacto_emergencia').val();

            let prioridad = $('#prioridad_contacto_emergencia').val();



            // let direccion = $('#direccion_contacto_emergencia').val();

            let numero_dir = $('#numero_dir_contacto_emergencia').val();

            //let ciudad_agregar = $('#ciudad_agregar').val();



            var valido = 1;

            var mensaje = ''

            if(rut == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar rut.\n';

            }

            if(nombres == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar nombres.\n';

            }

            if(apellido_uno == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar apellido paterno.\n';

            }

            if(apellido_dos == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar apellido materno.\n';

            }

            if(fecha == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar fecha.\n';

            }

            if(direccion == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar direccion.\n';

            }

            if(id_ciudad == '' || id_ciudad == '0')

            {

                valido = 0;

                mensaje += 'Debe ingresar ciudad.\n';

            }

            if(email == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar email.\n';

            }

            if(telefono == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar telefono.\n';

            }

            if(parentezco == '' || parentezco == '0')

            {

                valido = 0;

                mensaje += 'Debe ingresar parentezco.\n';

            }

            if(prioridad == '' || prioridad == '0')

            {

                valido = 0;

                mensaje += 'Debe ingresar prioridad.\n';

            }

            if(numero_dir == '')

            {

                valido = 0;

                mensaje += 'Debe ingresar numero direccion.\n';

            }



            if(valido == 1)

            {

                $.ajax({



                        url: url,

                        type: "get",

                        data: {

                            rut: rut,

                            nombres: nombres,

                            apellido_uno: apellido_uno,

                            apellido_dos: apellido_dos,

                            fecha: fecha,

                            direccion: direccion,

                            numero_dir: numero_dir,

                            id_ciudad: id_ciudad,

                            email: email,

                            telefono: telefono,

                            parentezco: parentezco,

                            prioridad: prioridad



                        },

                    })

                    .done(function(data) {

                        if (data != null) {

                            data = JSON.parse(data);

                            // console.log(data);



                            $('#agregar_contacto_emergencia').modal('hide');



                            swal({

                                title: "Se Registro Contacto de emergencia de forma correcta",

                                icon: "success",

                                // buttons: "Aceptar",

                                //SuccessMode: true,

                            })

                            cargar_lista_contacto()





                        } else {

                            swal({

                                title: "No se pudo registrar al contacto de emergencia",

                                icon: "Danger",

                                buttons: "Aceptar",

                                dangerMode: true,

                            });



                        }



                    })

                    .fail(function(jqXHR, ajaxOptions, thrownError) {

                        console.log(jqXHR, ajaxOptions, thrownError)

                    });

            }

            else

            {

                swal({

                    title: "Registro Contacto de Emergencia.",

                    text: mensaje,

                    icon: "error",

                    // buttons: "Aceptar",

                    //SuccessMode: true,

                });

            }

        };



        {{--  EDITAR CONFIGURACION MOTOR BUSQUEDA  --}}

        function editar_configuracion_busqueda()

        {

            let buscador = $('input:radio[name=buscador]:checked').val();

            let modalidad = $('#modalidad').val();

            let text_modalidad = $('#modalidad option:selected').text();

            let cv = $('#cv').val();



            if(buscador == 1)

            {

                if(modalidad == '')

                {

                    swal({

                        title: "Seleccione Modalidad de trabajo",

                        icon: "error",

                        buttons: "Aceptar",

                        // DangerMode: true,

                    });

                    return false;

                }

            }



            let url = "{{ route('asistente.editar_configuracion_busqueda') }}";



            $.ajax({

                url: url,

                type: "get",

                data: {

                    buscador: buscador,

                    modalidad: modalidad,

                    cv: cv,

                }



            })

            .done(function(data) {

                if (data.estado == 1)

                {



                    swal({

                        title: "Datos de contacto editados correctamente",

                        icon: "success",

                        buttons: "Aceptar",

                        DangerMode: true,

                    })



                    var text_buscador = '';

                    if(buscador)

                        text_buscador = 'SI';

                    else

                        text_buscador = 'NO';





                    $('#mensaje_buscador').html(text_buscador);

                    $('#mensaje_modalidad').html(text_modalidad);



                    $('#buscadores_1').addClass('show');

                    $('#buscadores_2').removeClass('show');



                }

                else

                {

                    swal({

                        title: "Configuración motores de búsqueda no registrada",

                        icon: "error",

                        buttons: "Aceptar",

                        // DangerMode: true,

                    })

                }

            })

            .fail(function(jqXHR, ajaxOptions, thrownError) {

                console.log(jqXHR, ajaxOptions, thrownError)

            });



        }



        function cargar_archivo_asistente()

        {



        }





        {{--  ***** FIN  FUNCIONES ******  --}}



        {{--  BUSCANDO CIUDAD --}}

        function buscar_ciudades(id_ciudad=0) {

            buscar_ciudad(id_ciudad);

        }

        function buscar_ciudad(id_ciudad=0) {





            var region = $('#region_agregar').val();



            if (region == null) {

                region = $('#region_contacto_modificar').val();

            }

            let url = "{{ route('home.buscar_ciudad_region') }}";

            $.ajax({



                    url: url,

                    type: "get",

                    data: {

                        //_token: _token,

                        region: region,

                    },

                })

                .done(function(data) {

                    if (data != null) {

                        data = JSON.parse(data);



                        let ciudades = $('#ciudad_lugar_atencion_modificar');

                        let ciudades_contacto = $('#ciudad_contacto_modificar');

                        let ciudades_agregar = $('#ciudad_agregar');



                        ciudades.find('option').remove();

                        ciudades.append('<option value="0">seleccione</option>');

                        $(data).each(function(i, v) { // indice, valor

                            ciudades.append('<option value="' + v.id + '">' + v.nombre +

                                '</option>');

                        })



                        ciudades_contacto.find('option').remove();

                        ciudades_contacto.append('<option value="0">seleccione</option>');

                        $(data).each(function(i, v) { // indice, valor

                            ciudades_contacto.append('<option value="' + v.id + '">' + v.nombre +

                                '</option>');

                        })



                        ciudades_agregar.find('option').remove();

                        ciudades_agregar.append('<option value="0">seleccione</option>');

                        $(data).each(function(i, v) { // indice, valor

                            ciudades_agregar.append('<option value="' + v.id + '">' + v.nombre +

                                '</option>');

                        })



                        if(id_ciudad != 0)

                        {

                            ciudades.val(id_ciudad);

                            ciudades_contacto.val(id_ciudad);

                            ciudades_agregar.val(id_ciudad);



                        }



                    } else {



                        swal({

                            title: "Error",

                            text: "Error al cargar las ciudades",

                            icon: "error",

                            buttons: "Aceptar",

                            DangerMode: true,

                        })

                        // alert('No se pudo Cargar las ciudades');

                    }



                })

                .fail(function(jqXHR, ajaxOptions, thrownError) {

                    console.log(jqXHR, ajaxOptions, thrownError)

                });



        };

    </script>

@endsection



