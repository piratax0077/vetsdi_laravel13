@extends('template.asistente_unificado.template')
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

                                    <a class="nav-link text-reset active" id="personal-tab" data-toggle="tab" href="#info_personal" role="tab" aria-controls="info_personal" aria-selected="true">Información Personal</a>

                                </li>

                                <li class="nav-item">

                                    <a class="nav-link text-reset" id="emergencia-tab" data-toggle="tab" href="#emergencia" role="tab" aria-controls="emergencia" aria-selected="false">Contacto de Emergencia</a>

                                </li>

                                <li class="nav-item">

                                    <a class="nav-link text-reset" id="pass-tab" data-toggle="tab" href="#pass" role="tab" aria-controls="pass" aria-selected="false">Cambiar Contraseñas</a>

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

                    <!--Tab Información Personal-->

                    <div class="tab-pane fade show active" id="info_personal" role="tabpanel" aria-labelledby="personal-tab">

                        <div class="row">

                            <div class="col-md-6">

                                <!--Card Información Básica-->

                                <div class="card">

                                    <div class="card-body d-flex align-items-center justify-content-between bg-info">

                                        <h5 class="mb-0"><i class="feather icon-user icono-purple"></i> Datos Personales</h5>

                                        <button type="button" class="btn btn-light btn-sm rounded m-0 float-right" data-toggle="collapse" data-target=".info_basica" aria-expanded="false" aria-controls="info_basica-1 info_basica-2">

                                            <i class="feather icon-edit"></i>

                                        </button>

                                    </div>

                                    <!--Datos Personales-->

                                    <div class="card-body border-top info_basica collapse show" id="info_basica-1">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">RUT</label>

	                                                <div> {{ $asistente->rut }} </div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Nombres</label>

	                                                <div> {{ $asistente->nombres }} </div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Primer Apellido</label>

	                                                <div> {{ $asistente->apellido_uno }} </div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Segundo Apellido</label>

	                                                <div>{{ $asistente->apellido_dos }}</div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Sexo</label>

	                                                <div> 

	                                                    @if($asistente->sexo == 'F') Mujer @elseif ($asistente->sexo == 'M') Hombre @endif 

	                                                </div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Nacimiento</label>

	                                                <div> {{ \Carbon\Carbon::parse($asistente->fecha_nac)->format('d-m-Y') }} </div>

	                                            </div>

                                            </div>
                                        </form>

                                    </div>

                                    <!--Cierre: Datos Personales-->

                                    <!--(Editar)Datos Personales-->

                                    <div class="card-body border-top info_basica collapse" id="pinfo_basica_2">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">RUT</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="RUT" value="{{ $asistente->rut }}">

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Nombre</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Nombre" value="{{ $asistente->nombres }}">

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Primer Apellido</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Primer Apellido" value="{{ $asistente->apellido_uno }}">

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Segundo Apellido</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Primer Apellido" value="{{ $asistente->apellido_dos }}">

	                                            </div>

	                                            <div class="form-group col-12">
	                                                <label class="font-weight-bolder d-block ml-0 mb-1">Sexo</label>

	                                                <div>

	                                                    <div class="form-check form-check-inline">

	                                                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1" @if($asistente->sexo == 'M') checked @endif>

	                                                        <label class="form-check-label" for="inlineRadio1">Hombre</label>

	                                                    </div>

	                                                    <div class="form-check form-check-inline">

	                                                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="option2" @if($asistente->sexo == 'F') checked @endif>

	                                                        <label class="form-check-label" for="inlineRadio2">Mujer</label>

	                                                    </div>

	                                                </div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Nacimiento</label>

	                                                <input type="date" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($asistente->fecha_nac)->format('Y-m-d') }}">

	                                            </div>

                                            </div>
                                            <div class="form-row">
                                                <div class="col-sm-12 d-flex justify-content-end">

                                                    <button type="submit" class="btn btn-outline-dark btn-sm mr-2"><i class="feather icon-x"></i> Cancelar</button>

                                                    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>

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

                                    <div class="card-body d-flex align-items-center justify-content-between bg-info">

                                        <h5 class="mb-0"><i class="feather icon-phone icono-purple"></i> Contacto</h5>

                                        <button type="button" class="btn btn-light btn-sm rounded m-0 float-right" data-toggle="collapse" data-target=".info_contacto" aria-expanded="false" aria-controls="info_contacto_1 info_contacto_2">

                                            <i class="feather icon-edit"></i>

                                        </button>

                                    </div>

                                    <!--Contacto-->

                                    <div class="card-body border-top info_contacto collapse show" id="info_contacto_1">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Correo Electrónico</label>

	                                                <div> {{ $asistente->email }} </div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Teléfono</label>

	                                                <div> {{ $asistente->telefono_uno }}</div>

	                                            </div>

                                            </div>
                                        </form>

                                    </div>

                                    <!--Cierre: Contacto-->

                                    <!--(Editar) Contacto-->

                                    <div class="card-body border-top info_contacto collapse" id="info_contacto_2">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Correo Electrónico</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Correo Electrónico" value="{{ $asistente->email }}">

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Teléfono</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Teléfono" value="{{ $asistente->telefono_uno }}">

	                                            </div>

                                            </div>
                                            <div class="form-row">
                                                <div class="col-sm-12 d-flex justify-content-end">

                                                    <button type="submit" class="btn btn-outline-dark btn-sm mr-2"><i class="feather icon-x"></i> Cancelar</button>

                                                    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>

                                                </div>

                                            </div>

                                        </form>

                                    </div>

                                    <!--(Editar) Contacto-->

                                </div>

                                <!--Cierre: Card Contacto-->

                                <!--Card Residencia-->

                                <div class="card">

                                    <div class="card-body d-flex align-items-center justify-content-between bg-info">

                                        <h5 class="mb-0"><i class="feather icon-map-pin icono-purple"></i> Residencia</h5>

                                        <button type="button" class="btn btn-light btn-sm rounded m-0 float-right" data-toggle="collapse" data-target=".info_residencial" aria-expanded="false" aria-controls="info_residencial_1 info_residencial_2">

                                            <i class="feather icon-edit"></i>

                                        </button>

                                    </div>

                                    <!--Residencia-->

                                    <div class="card-body border-top info_residencial collapse show" id="info_residencial_1">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Comuna</label>

	                                                <div>{{ $asistente->Direccion()->first()->Ciudad()->first()->nombre }}</div>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Dirección</label>

	                                                <div>{{ $asistente->Direccion()->first()->direccion }}</div>

	                                            </div>

                                            </div>
                                        </form>

                                    </div>

                                    <!--Cierre: Residencia-->

                                    <!--(Editar) Residencia-->

                                    <div class="card-body border-top info_residencial collapse " id="info_residencial_2">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Comuna</label>

	                                                <select class="form-control form-control-sm" id="exampleFormControlSelect1">

	                                                    <option>Seleccione su comuna</option>

	                                                    <option selected>Viña del Mar</option>

	                                                </select>

	                                            </div>

	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="floating-label-activo">Dirección</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Dirección" value="Calle Nº Depto..">

	                                            </div>

                                            </div>
                                            <div class="form-row">
                                                <div class="col-sm-12 d-flex justify-content-end">

                                                    <button type="submit" class="btn btn-outline-dark btn-sm mr-2"><i class="feather icon-x"></i> Cancelar</button>

                                                    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>

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

                    <!--Tab Contactos de Emergencia-->

                    <div class="tab-pane fade" id="emergencia" role="tabpanel" aria-labelledby="emergencia-tab">

                        <div class="row">

                            <div class="col-md-12">

                                <div class="card">

                                    <div class="card-header d-flex align-items-center justify-content-between bg-white">

                                        <h5 class="mb-0"><i class="feather icon-user-plus icono-purple"></i> Contactos de emergencia</h5>

                                        <button type="button" class="btn btn-info btn-sm m-0" data-toggle="modal" data-target="#agregar_contacto_emergencia">

                                            <i class="feather icon-plus"></i> Agregar contacto

                                        </button>

                                    </div>

                                    <div class="card-body">

                                        <div class="row">

                                            <div class="col-md-12">

                                                <div style="overflow-x:auto;">

                                                    <table id="contactos_emergencia" class="display table table-striped table-hover dt-responsive nowrap table-sm" style="width:100%">

                                                <thead>

                                                    <tr>

                                                        <th class="align-middle text-center">Prioridad</th>

                                                        <th class="align-middle text-center">Nombre</th>

                                                        <th class="align-middle text-center">Parentezco</th>

                                                        <th class="align-middle text-center">Acción</th>

                                                    </tr>

                                                </thead>

                                                <tbody>

                                                    @foreach ($asistente->ContactosEmergencia()->get() as $co)

                                                    <tr>

                                                        <td class="align-middle text-center">{{ $co->prioridad }}</td>

                                                        <td class="align-middle text-center">

                                                            {{ $co->nombre }}

                                                            <br>

                                                            {{ $co->apellido_uno }}

                                                            {{ $co->apellido_dos }}

                                                        </td>

                                                        <td class="align-middle text-center">{{ $co->pivot->relacion }}</td>

                                                        <td class="align-middle text-center">

                                                            <button id="btn_info_contacto" class="btn btn-outline-purple btn-icon" data-toggle="modal" data-target="#info_contacto_emergencia" title="Información de contacto" data-placement="top"><i class="feather icon-phone-call"></i></button>

                                                              <button id="btn_editar_contacto" class="btn btn-outline-purple btn-icon" data-toggle="modal" data-target="#editar_contacto_emergencia" title="Editar contacto" data-placement="top"><i class="feather icon-edit"></i></button>

                                                              <button class="btn btn-danger btn-icon" data-toggle="tooltip" title="Eliminar contacto"><i class="feather icon-x"></i></button>

                                                        </td>

                                                    </tr>

                                                    @endforeach

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

                    <!--Tab Cambiar Contraseñas-->

                    <div class="tab-pane fade" id="pass" role="tabpanel" aria-labelledby="pass-tab">

                        <div class="row">

                            <div class="col-md-6">

                                <!--Card Contraseña Personal-->

                                <div class="card">

                                    <div class="card-body d-flex align-items-center justify-content-between bg-info">

                                        <h5 class="mb-0"><i class="feather icon-lock icono-purple"></i> Cambie su Contraseña Personal</h5>

                                        <button type="button" class="btn btn-light btn-sm rounded m-0 float-right" data-toggle="collapse" data-target=".pass_personal" aria-expanded="false" aria-controls="pass_personal_1 pass_personal_2">

                                            <i class="feather icon-edit"></i>

                                        </button>

                                    </div>

                                    <!--Contraseña Personal-->

                                    <div class="card-body border-top pass_personal collapse show" id="pass_personal_1">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-sm-12 col-md-6">
	                                                <label class="font-weight-bolder ml-0 mb-0">Contraseña Actual</label>

	                                                <div> •••••••• </div>

	                                            </div>

                                            </div>
                                        </form>

                                    </div>

                                    <!--Cierre: Contraseña Personal-->

                                    <!--(Editar)Contraseña Personal-->

                                    <div class="card-body border-top pass_personal collapse" id="pass_personal_2">

                                        <form>

                                            <div class="form-row">
	                                            <div class="form-group col-12">
	                                                <label class="floating-label-activo">Contraseña Actual</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Contraseña Actual">

	                                            </div>

	                                            <div class="form-group col-12">
	                                                <label class="floating-label-activo">Nueva Contraseña</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Nueva Contraseña">

	                                            </div>

	                                            <div class="form-group col-12">
	                                                <label class="floating-label-activo">Repita su Contraseña</label>

	                                                <input type="text" class="form-control form-control-sm" placeholder="Repita la Contraseña">

	                                            </div>

                                            </div>
                                            <div class="form-row">
                                                <div class="col-sm-12 d-flex justify-content-end">

                                                    <button type="submit" class="btn btn-outline-dark btn-sm mr-2"><i class="feather icon-x"></i> Cancelar</button>

                                                    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>

                                                </div>

                                            </div>

                                        </form>

                                    </div>

                                    <!--Cierre: (Editar)Contraseña Personal-->

                                </div>

                                <!--Cierre: Card Contraseña Personal-->

                            </div>

                        </div>

                    </div>

                    <!--Cierre: Tab Cambiar Contraseñas-->

                    <div class="tab-pane fade" id="gallery" role="tabpanel" aria-labelledby="gallery-tab">

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!--Cierre: Container Completo-->



<!-- Modal agregar contacto emergencia-->

<div id="agregar_contacto_emergencia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="agregar_contacto_emergencia" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header modal-header-purple">

               <h5 class="modal-title text-center">Agregar contacto de emergencia</h5>

               <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>

            </div>

            <div class="modal-body">

                <form>

                    <div class="row">

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <h6 class="text-c-blue ml-1 mb-3">Ingrese los datos de su contacto de emergencia</h6>

                            </div>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">RUT</label>

                                <input type="person" class="form-control" name="rut_paciente" id="rut_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Nombres</label>

                                <input type="text" class="form-control" name="nombres_paciente" id="nombres_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Apellidos</label>

                                <input type="text" class="form-control" name="apellidos_paciente" id="apellidos_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Dirección</label>

                                <input type="address" class="form-control" name="direccion_paciente" id="direccion_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Comuna</label>

                                <select id="comuna_paciente" name="comuna_paciente" class="form-control">

                                    <option>Seleccione una opción</option>

                                    <optgroup label="Valparaíso">

                                        <option>Viña del Mar</option>

                                        <option>La Calera</option>

                                        <option>Valparaíso</option>

                                    </optgroup> 

                                </select>

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Correo Electrónico</label>

                                <input type="email" class="form-control" name="email_paciente" id="email_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Teléfono</label>

                                <input type="tel" class="form-control" name="telefono_paciente" id="telefono_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Parentezco</label>

                                <select id="parentezco" name="parentezco" class="form-control">

                                    <option>Seleccione una opción</option>

                                    <option>Pareja</option>

                                    <option>Padre</option>

                                    <option>Madre</option>

                                    <option>Hermano/a</option>

                                    <option>Abuelo/a</option>  

                                    <option>Tío/a</option> 

                                    <option>Primo/a</option> 

                                    <option>Amigo/a</option> 

                                    <option>Otro</option><!--Si se selecciona "otro"

                                    deberia abrirse un input text para que el usuario escriba

                                    el parentezco-->   

                                </select>

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Prioridad</label>

                                <select id="prioridad" name="prioridad" class="form-control">

                                    <option>Seleccione una opción</option>

                                    <option>1</option>

                                    <option>2</option>

                                    <option>3</option>

                                    <option>4</option>

                                    <option>5</option>

                                    <option>6</option>

                                    <option>7</option>

                                    <option>8</option>

                                    <option>9</option>

                                    <option>10</option>   

                                </select>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <div class="modal-footer">

               <button type="button" class="btn btn-outline-dark" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');">Cancelar</button>

               <button type="submit" class="btn btn-info">Guardar Contacto</button>

            </div>

        </div>

   </div>

</div>



<!-- Modal información de contacto emergencia-->

<div id="info_contacto_emergencia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="info_contacto_emergencia" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header modal-header-purple">

               <h5 class="modal-title text-center">Información de contacto</h5>

               <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>

            </div>

            <div class="modal-body">

                <div class="row">

                    <div class="col-md-12">

                        <table class="table table-borderless">

                          <tbody>

                              <tr>

                              <th scope="row">RUT</th>

                              <td>12.345.546-0</td>

                            </tr>

                            <tr>

                              <th scope="row">Nombre</th>

                              <td>Alvaro Alejandro<br>

                                    Cortés Díaz

                                </td>

                            </tr>

                            <tr>

                              <th scope="row">Dirección</th>

                              <td>Las Maravillas #123,<br>

                              Viña del Mar</td>

                            </tr>

                            <tr>

                              <th scope="row">Teléfono</th>

                              <td>78237838</td>

                            </tr>

                            <tr>

                              <th scope="row">Email</th>

                              <td>contacto@dominio.cl</td>

                            </tr>

                          </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <div class="modal-footer">

               <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');">Cerrar</button>

            </div>

        </div>

   </div>

</div>



<!-- Modal Editar contacto emergencia-->

<div id="editar_contacto_emergencia" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editar_contacto_emergencia" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">

        <div class="modal-content">

            <div class="modal-header modal-header-purple">

               <h5 class="modal-title text-center">Editar contacto de emergencia</h5>

               <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>

            </div>

            <div class="modal-body">

                <form>

                    <div class="row">

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">RUT</label>

                                <input type="person" class="form-control" name="rut_paciente" id="rut_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Nombres</label>

                                <input type="text" class="form-control" name="nombres_paciente" id="nombres_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Apellidos</label>

                                <input type="text" class="form-control" name="apellidos_paciente" id="apellidos_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Dirección</label>

                                <input type="address" class="form-control" name="direccion_paciente" id="direccion_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Comuna</label>

                                <select id="comuna_paciente" name="comuna_paciente" class="form-control">

                                    <option>Seleccione una opción</option>

                                    <optgroup label="Valparaíso">

                                        <option>Viña del Mar</option>

                                        <option>La Calera</option>

                                        <option>Valparaíso</option>

                                    </optgroup> 

                                </select>

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Correo Electrónico</label>

                                <input type="email" class="form-control" name="email_paciente" id="email_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Teléfono</label>

                                <input type="tel" class="form-control" name="telefono_paciente" id="telefono_paciente">

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Parentezco</label>

                                <select id="parentezco" name="parentezco" class="form-control">

                                    <option>Seleccione una opción</option>

                                    <option>Pareja</option>

                                    <option>Padre</option>

                                    <option>Madre</option>

                                    <option>Hermano/a</option>

                                    <option>Abuelo/a</option>  

                                    <option>Tío/a</option> 

                                    <option>Primo/a</option> 

                                    <option>Amigo/a</option> 

                                    <option>Otro</option><!--Si se selecciona "otro"

                                    deberia abrirse un input text para que el usuario escriba

                                    el parentezco-->   

                                </select>

                            </div>

                        </div>

                        <div class="col-sm-12 col-md-12">

                            <div class="form-group">

                                <label class="floating-label">Prioridad</label>

                                <select id="prioridad" name="prioridad" class="form-control">

                                    <option>Seleccione una opción</option>

                                    <option>1</option>

                                    <option>2</option>

                                    <option>3</option>

                                    <option>4</option>

                                    <option>5</option>

                                    <option>6</option>

                                    <option>7</option>

                                    <option>8</option>

                                    <option>9</option>

                                    <option>10</option>   

                                </select>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <div class="modal-footer">

               <button type="button" class="btn btn-outline-dark" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');">Cancelar</button>

               <button type="submit" class="btn btn-info">Guardar Cambios</button>

            </div>

        </div>

   </div>

</div>

@endsection
@section('page-script')
@include('app.asistente.foto_perfil_script')
@endsection
