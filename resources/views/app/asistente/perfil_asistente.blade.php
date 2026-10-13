@extends('template.asistente.template')
@section('sin_boton_volver', '1')

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
							@include('template.partials.perfil_cabecera', [
								'nombre' => trim(($asistente->nombres ?? '') . ' ' . ($asistente->apellido_uno ?? '') . ' ' . ($asistente->apellido_dos ?? '')),
								'rol' => 'Asistente',
								'foto' => $asistente->foto_perfil ?? null,
								'foto_base' => 'images/iconos/usuario_asistente.svg',
								'datos_perfil' => [
									$asistente->rut ?? '', $asistente->nombres ?? '', $asistente->apellido_uno ?? '',
									$asistente->email ?? '', $asistente->telefono_uno ?? '', $asistente->foto_perfil ?? '',
									$direccion_region_nombre ?? '', optional($asistente->Direccion()->first())->direccion,
								],
							])
							<div class="col-md-12 mt-md-2 m-0">
								<ul class="nav nav-tabs profile-tabs nav-fill mt-1" id="myTab" role="tablist">
									<li class="nav-item">
										<a class="nav-link text-reset active" id="personal-tab" data-toggle="tab" href="#info_personal" role="tab" aria-controls="info_personal" aria-selected="true">Información personal</a>
									</li>
                                    <li class="nav-item">
                                        <a class="nav-link text-reset" id="info-liquidacion-tab" data-toggle="tab" href="#info-liquidacion" role="tab" aria-controls="info-liquidacion" aria-selected="false">Manejo de Cuentas</a>
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
	                                                        @elseif ($asistente->sexo == 'O')
	                                                            Otro
	                                                        @endif
	                                                    </div>
													</div>
													<div class="form-group col-sm-12 col-md-6">
														<label class="font-weight-bolder ml-0 mb-0">Nacimiento</label>
														<div id="ver_nacimiento">{{ $asistente->fecha_nac ? \Carbon\Carbon::parse($asistente->fecha_nac)->format('d-m-Y') : '' }}</div>
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
														<input type="text" class="form-control form-control-sm" placeholder="RUT" id="editar_rut" name="editar_rut" value="{{ $asistente->rut }}">
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
	                                                        <option value="">Seleccione su Sexo</option>
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
														<input type="date" class="form-control form-control-sm" id="editar_nacimiento" name="editar_nacimiento" value="{{ $asistente->fecha_nac ? \Carbon\Carbon::parse($asistente->fecha_nac)->format('Y-m-d') : '' }}">
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
															<div id="ver_direccion"> {{ $asistente->Direccion()->first()->direccion . ($asistente->Direccion()->first()->numero_dir ? ' #' . $asistente->Direccion()->first()->numero_dir : '') }} </div>
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
	                                                <div class="form-group col-12">
	                                                    <label class="font-weight-bolder d-block ml-0 mb-1"><h6>Curriculum</h6></label>
	                                                    <div>
	                                                        <input type="hidden" name="input_lista_archivo" id="input_lista_archivo" value="{{ $asistente->curriculum }}">
	                                                        <div>
	                                                            <label class="col-sm-7 col-form-label font-weight-bolder"><h6>Carga nuevo archivo</h6></label>
	                                                            <!-- [ Main Content ] start -->
	                                                            <div class="dropzone" id="mis-asistente-archivo" action="{{ route('asistente.archivo.carga') }}">
	                                                            </div>
	                                                            <!-- [ file-upload ] end -->
	                                                            {{-- <input type="file" name="cv" id="cv" placeholder="Seleccione archivo" onchange="cargar_archivo_asistente();"> --}}
	                                                        </div>
	                                                    </div>
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
    @include('app.asistente.modales.agregar_contacto_emergencia')
    @include('app.profesional.modales.informacion_contacto_emergencia')
    @include('app.profesional.modales.editar_contacto_emergencia')

@endsection

{{-- JS ADICIONALES PROPIOS DE LA VISTA  --}}
@section('page-script')
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


        /** mis-asistente-archivo */
        var myDropzone_CV ;
        Dropzone.options.misAsistenteArchivo = {
            init:function()
            {
                myDropzone_CV = this;
            },
            url: "{{ route('asistente.archivo.carga') }}",
            method: 'post',
            createImageThumbnails: true,
            addRemoveLinks: true,
            headers:{
                'X-CSRF-TOKEN' : CSRF_TOKEN,
                // 'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content'),
            },

            acceptedFiles: "application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*",
            maxFilesize: 6,
            maxFiles: 1,
            /** El texto utilizado antes de que se eliminen los archivos. */
            dictDefaultMessage: "Arrastre elarchivo al recuadro para subirlo.",

            /** El texto que reemplaza el texto del mensaje predeterminado si el navegador no es compatible. */
            dictFallbackMessage: "Su navegador no admite la carga de archivos mediante arrastrar y soltar.",

            /**
             * El texto que se agregará antes del formulario alternativo.
             * Si usted mismo proporciona un elemento alternativo, o si esta opción es `nula`, esto
             * ser ignorado.
             */
            dictFallbackText: "Utilice el formulario alternativo a continuación para cargar sus archivos como en los viejos tiempos.",

            /**
             * Si el tamaño del archivo es demasiado grande.
             * `{ {filesize} }` y `{ {maxFilesize} }` serán reemplazados con los respectivos valores de configuración.
             */
             dictFileTooBig: "El archivo es demasiado grande. Max tamaño de archivo: 6 MiB.",

            /** Si el archivo no coincide con el tipo de archivo. */
            dictInvalidFileType: "No puedes subir archivos de este tipo.",

            /** Si `addRemoveLinks` es verdadero, el texto que se usará para cancelar el enlace de carga. */
            dictCancelUpload: "Cancelar carga",

            /** El texto que se muestra si una carga se canceló manualmente */
            dictUploadCanceled: "Subida cancelada.",

            /** Si `addRemoveLinks` es verdadero, el texto que se utilizará para la confirmación al cancelar la carga. */
            dictCancelUploadConfirmation: "¿Está seguro de que desea cancelar esta carga?",

            /** Si `addRemoveLinks` es verdadero, el texto que se usará para eliminar un archivo. */
            dictRemoveFile: "Eliminar archivo",

            /**
             * Se muestra si `maxFiles` es st y se excede.
             */
            dictMaxFilesExceeded: "No puede cargar más archivos.",

            // accept(file, done) {
            //     console.log('-------------accept-----------------------');
            //     cargar_lista_archivo();
            //     return done();
            // },
            success: function(file, response){
                // console.log('-------------success-----------------------');
                cargar_lista_archivo(myDropzone_CV,'cv');

                if (file.previewElement) {
                    return file.previewElement.classList.add("dz-success");
                }
            },
            error(file, message) {
                // console.log('-------------error-----------------------');
                if (file.previewElement) {
                    file.previewElement.classList.add("dz-error");
                    if (typeof message !== "string" && message.error)
                    {
                        message = message.error;
                    }
                    else
                    {
                        message = message.message;
                    }
                    for (let node of file.previewElement.querySelectorAll( "[data-dz-errormessage]" )) {
                        node.textContent = message;
                    }
                }
            },
            removedfile(file) {
                // console.log('-------------removedfile-----------------------');
                if (file.previewElement != null && file.previewElement.parentNode != null) {
                    file.previewElement.parentNode.removeChild(file.previewElement);
                }
                cargar_lista_archivo(myDropzone_CV,'cv');
                return this._updateMaxFilesReachedClass();
            },
            canceled: function canceled(file) {
                cargar_lista_archivo(myDropzone_CV,'cv');
                return this.emit("error", file, this.options.dictUploadCanceled);
            },
        };

        var lista_archivo = {};
        function cargar_lista_archivo(obj_dropzone, alias_archivo)
        {
            // console.log('--------------cargar_lista_archivo----------------------');
            lista_archivo = [];
            let temp  = obj_dropzone.getAcceptedFiles();
            $.each(temp, function( index, value )
            {
                if(value.status == "success")
                {
                    if(value.xhr !== undefined)
                    {
                        var archivo_temp = JSON.parse(value.xhr.response);
                        lista_archivo[index] = [
                            url = archivo_temp.archivo.url,
                            nombre_original = archivo_temp.archivo.original_file_name,
                            nombre_archivo = archivo_temp.archivo.nombre_archivo,
                            file_extension = archivo_temp.archivo.file_extension,
                        ];
                        $('#input_lista_archivo').val('');
                        $('#input_lista_archivo').val(JSON.stringify(lista_archivo));
                    }
                }
            });
        }

        function abrirCV(url)
        {
            if(url != '')
            {
                var win = window.open(url, '_blank');
                win.focus();
            }
        }
    </script>
@endsection

