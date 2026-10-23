@extends('template.adm_cm.template')
@section('content')
@php
    $contextosCentro = $contextosCentro ?? collect();
    $contextoActivo = $contextoActivo ?? [];
    $scopeLugarIds = $scopeLugarIds ?? [$institucion->id_lugar_atencion];
    $lugarAtencionAccion = $contextoActivo['id_lugar_atencion'] ?? ($scopeLugarIds[0] ?? $institucion->id_lugar_atencion);
@endphp

<!--****Container Completo****-->
<style>
    .select2-container--open{
        z-index: 9999999 !important;
    }
</style>
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="row">
            <div class="col-md-12 mb-3 encabezado-pagina">
                <div>
                    <h5 class="f-26 d-block mb-0">Personal</h5>
                </div>
            </div>
        </div>
                @if (false && $contextosCentro->isNotEmpty())
                    <form method="GET" action="{{ route('adm_cm.profesionales_institucion') }}" class="mb-3">
                        <div class="form-row align-items-end">
                            <div class="col-md-5">
                                <label class="floating-label-activo-sm mb-0">Institución / Sucursal activa</label>
                                <select name="contexto" class="form-control form-control-sm">
                                    @foreach ($contextosCentro as $contexto)
                                        <option value="{{ $contexto['key'] ?? '' }}" @selected(($contextoActivo['key'] ?? '') === ($contexto['key'] ?? ''))>
                                            {{ $contexto['label'] ?? 'Sin contexto' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mt-2 mt-md-0">
                                <button type="submit" class="btn btn-info btn-sm">Cambiar contexto</button>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0 text-md-right">
                                <span class="badge badge-light mt-2">{{ $contextoActivo['role_label'] ?? 'Administrador General' }}</span>
                            </div>
                        </div>
                    </form>
                @endif
        @include('app.adm_cm.partials.usuarios_tablas_nav', ['seccionUsuarios' => 'profesionales'])
        <div class="row">
            <div class="col-md-12">
                <!--Card Nav Pills-->
                <div class="card d-none">
                    <div class="card-body">
                        <ul class="nav nav-pills bg-white" id="personal_cm" role="tablist">
                            <li class="nav-item active">
                                <a class="btn btn-outline-info btn-sm mr-1 my-1" id="prof_salud-tab" data-toggle="tab" href="#prof-salud" role="tab" aria-controls="prof_-alud" aria-selected="false">Médicos</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-outline-info btn-sm mr-1 my-1" id="odontologos-tab" data-toggle="tab" href="#odontologos" role="tab" aria-controls="odontologos" aria-selected="false">Odontólogos</a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-outline-info btn-sm mr-1 my-1" id="otros_prof-tab" data-toggle="tab" href="#otros_prof" role="tab" aria-controls="otros_prof" aria-selected="false">Otros Profesionales de la salud</a>
                            </li>
							<!--
                            <li class="nav-item">
                                <a class="btn btn-outline-info btn-sm mr-1 my-1" id="personal_inst-tab" data-toggle="tab" href="#personal_inst" role="tab" aria-controls="otros_prof" aria-selected="false">Personal Institucional</a>
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <!--Cierre: Card Nav Pills-->
                <div class="tab-content" id="Profesionales_cm">
                    <!--Tab profesionales veterinarios-->
                    <div class="tab-pane fade active show" id="prof-vet" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header encabezado-card">
                                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-users"></i></span>Profesionales veterinarios</h5>
                                        <div class="encabezado-card-acciones">
                                            <button type="button" class="btn btn-info btn-sm" onclick="enviar_mensaje_difusion()" @if(!$adm_medico) disabled @endif><i class="feather icon-mail mr-1"></i>Difusión</button>
                                            <button type="button" class="btn btn-info btn-sm" onclick="asociar_profesional();" @if(!$adm_medico) disabled @endif><i class="feather icon-plus mr-1"></i>Vincular profesional</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table id="usuarios_profesionales_vet" class="display table table-striped dt-responsive nowrap" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th class="align-middle">Nombre / RUT</th>
                                                        <th class="align-middle">Especialidad</th>
                                                        <th class="align-middle">Sucursales</th>
                                                        <th class="align-middle">Info</th>
                                                        {{-- Oculto: los convenios ahora se ven en el modal Información --}}
                                                        {{-- <th class="align-middle">Convenio</th> --}}
                                                        <th class="align-middle">Mensaje</th>
                                                        <th class="align-middle">Historial</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        // Todo profesional que atiende va en esta tabla, sin importar si es médico, odontólogo u otro
                                                        $todosLosProfesionales = collect(array_merge(
                                                            $lista_profesionales['MEDICO'] ?? [],
                                                            $lista_profesionales['ODONTOLOG'] ?? [],
                                                            $lista_profesionales['OTROS'] ?? []
                                                        ))->unique('id');
                                                    @endphp
                                                    @if($todosLosProfesionales->isNotEmpty())
                                                        @foreach ($todosLosProfesionales as $prof_vet)
                                                            @php
                                                                $infoProfesionalVet = [
                                                                    'titulo' => 'Información',
                                                                    'id_profesional' => $prof_vet->id,
                                                                    'id_usuario' => $prof_vet->id_usuario,
                                                                    'id_lugar_atencion' => $lugarAtencionAccion,
                                                                    'lugares' => $prof_vet->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get()->map(function ($lugar) {
                                                                        $direccionLugar = $lugar->Direccion()->first();
                                                                        $detalle = $direccionLugar ? $direccionLugar->direccion.', '.($direccionLugar->ciudad->nombre ?? '') : '';
                                                                        return ['id' => $lugar->id, 'nombre' => trim($lugar->nombre.($detalle !== '' ? ' - '.$detalle : ''))];
                                                                    })->values(),
                                                                ];
                                                            @endphp
                                                            <tr>
                                                                <td class="align-middle">
                                                                    <span><strong>{{ $prof_vet->nombre.' '.$prof_vet->apellido_uno.' '.$prof_vet->apellido_dos }}</strong></span><br>
                                                                    <span>{{ $prof_vet->rut }}</span>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <span>{{ $prof_vet->TipoEspecialidad()->first()->nombre }}</span>
                                                                    @if (!empty($prof_vet->id_sub_tipo_especialidad))
                                                                        <br>
                                                                        <span>{{ $prof_vet->SubTipoEspecialidad()->first()->nombre }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="align-middle">
                                                                    @foreach($prof_vet->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get() as $value_lugar)
                                                                        <span>{{ $value_lugar->Direccion()->first()->direccion.', '.$value_lugar->Direccion()->first()->ciudad->nombre }}</span><br>
                                                                    @endforeach
                                                                </td>
                                                                <td class="align-middle">
                                                                    <!--Botón Modal información-->
                                                                    <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="abrir_info_prof_vet({{ \Illuminate\Support\Js::from($infoProfesionalVet) }});" data-toggle="tooltip" data-placement="top" title="Información"><i class="feather icon-info"></i></button>

                                                                    <button type="button" class="btn btn-primary btn-sm btn-icon" onclick="f_profesional({{ $prof_vet->id }});" data-toggle="tooltip" data-placement="top" title="Información profesional"><i class="feather icon-eye"></i></button>

                                                                    {{-- Oculto: el datos bancarios ahora se ve en el modal Información --}}
                                                                    {{-- <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datos_depositos({{ $prof_vet->id_usuario }});" data-toggle="tooltip" data-placement="top" title="Datos bancarios"><i class="feather icon-credit-card"></i></button> --}}

                                                                    {{-- Oculto: el horario ahora se ve en el modal Información --}}
                                                                    {{-- <button type="button" class="btn btn-purple btn-sm btn-icon" onclick="horario_profesional_cm({{ $prof_vet->id }}, {{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Horario y días de atención"><i class="feather icon-clock"></i></button> --}}
                                                                </td>
                                                                {{-- <td class="align-middle text-center">
                                                                    <!--Botón Modal convenios-->
                                                                    <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="convenio_profesional_cm({{ $prof_vet->id }},{{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Convenio"><i class="feather icon-file-text"></i></button>
                                                                </td> --}}
                                                                <td class="align-middle">
                                                                    <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="mensaje({{ $prof_vet->id }});" data-toggle="tooltip" data-placement="top" title="Mensaje" @if(!$adm_medico) disabled @endif><i class="feather icon-mail"></i></button>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="historial({{ $prof_vet->id }});" @if(!$adm_medico) disabled @endif data-toggle="tooltip" data-placement="top" title="Historial"><i class="feather icon-mail"></i></button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Cierre: Tab profesionales veterinarios-->

                    <!--Tab otros trabajadores (mantención, limpieza, contador y cualquier otra categoría)-->
                    <div class="tab-pane fade" id="otros_trabajadores" role="tabpanel">
                        <div class="card">
                            <div class="card-header encabezado-card">
                                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-settings"></i></span>Otros trabajadores</h5>
                                <div class="encabezado-card-acciones">
                                    {{-- Pendiente para programación: abrir el registro de un trabajador y la creación de categorías --}}
                                    <button type="button" class="btn btn-info btn-sm" disabled><i class="feather icon-plus mr-1"></i>Agregar trabajador</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="otros_trabajadores_tabla" class="table table-striped nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Nombre / RUT</th>
                                                <th class="align-middle">Categoría</th>
                                                <th class="align-middle">Sucursales</th>
                                                <th class="align-middle">Info</th>
                                                <th class="align-middle">Mensaje</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Pendiente para programación: listar los trabajadores de otras categorías del centro --}}
                                            <tr>
                                                <td colspan="5" class="p-0 border-0">
                                                    <div class="aviso-sin-registros">
                                                        <span class="aviso-sin-registros-icono"><i class="feather icon-alert-circle"></i></span>
                                                        <div>
                                                            <p class="aviso-sin-registros-titulo">Sin registros</p>
                                                            <p class="aviso-sin-registros-texto">Aquí aparecerán los trabajadores que no son profesionales de la salud ni asistentes, como mantención, limpieza o contabilidad, y cualquier otra categoría que se cree.</p>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--Cierre: Tab otros trabajadores-->

                    @include('app.adm_cm.partials.pestana_asistentes')
                    {{-- Contratos ahora tiene su propia página (adm_cm.contratos) --}}

                    <!--Tab medicos-->
                    <div class="tab-pane fade" id="prof-salud" role="tabpanel" aria-labelledby="prof-salud-tab">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header encabezado-card">
                                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-activity"></i></span>Profesionales médicos</h5>
                                        <div class="encabezado-card-acciones">
                                            <button type="button" class="btn btn-info btn-sm" onclick="enviar_mensaje_difusion()" @if(!$adm_medico) disabled @endif><i class="feather icon-mail mr-1"></i>Difusión</button>
                                            <button type="button" class="btn btn-info btn-sm" onclick="asociar_profesional();" @if(!$adm_medico) disabled @endif><i class="feather icon-plus mr-1"></i>Vincular profesional</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 col-md-12 col-lg-12 col-xl-12">
                                                <div class="table-responsive">
                                                    <table id="profesionales_personal" class="display table table-striped dt-responsive nowrap" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th class="align-middle">Nombre / Rut</th>
                                                                <th class="align-middle">Especialidad</th>
                                                                <th class="align-middle">Sucursales</th>
            													<th class="align-middle">Info</th>
            													{{-- Oculto: los convenios ahora se ven en el modal Información --}}
            													{{-- <th class="align-middle">Convenio</th> --}}
                                                                <th class="align-middle">Mensaje</th>
                                                                <th class="align-middle">Historial</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if($lista_profesionales['MEDICO'])
                                                                @foreach ($lista_profesionales['MEDICO'] as $prof_medico )
                                                                    <tr>
                                                                        <td class="align-middle">
                                                                            <span><strong>{{ $prof_medico->nombre.' '.$prof_medico->apellido_uno.' '.$prof_medico->apellido_dos }}</strong></span><br>
                                                                            <span>{{ $prof_medico->rut }}</span>
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            <span>{{ $prof_medico->TipoEspecialidad()->first()->nombre }}</span>
                                                                            @if (!empty($prof_medico->id_sub_tipo_especialidad))
                                                                                <br>
                                                                                <span>{{ $prof_medico->SubTipoEspecialidad()->first()->nombre }}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            @foreach($prof_medico->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get() as $key_lugar => $value_lugar)
                                                                                    <span>{{ $value_lugar->Direccion()->first()->direccion.', '.$value_lugar->Direccion()->first()->ciudad->nombre  }}</span><br>
                                                                            @endforeach
                                                                        </td>
                                                                            <td class="align-middle">
                                                                                @php
                                                                                    $infoProfesional = [
                                                                                        'titulo' => 'Información',
                                                                                        'id_profesional' => $prof_medico->id,
                                                                                        'id_usuario' => $prof_medico->id_usuario,
                                                                                        'id_lugar_atencion' => $lugarAtencionAccion,
                                                                                        'lugares' => $prof_medico->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get()->map(function ($lugar) {
                                                                                            $direccionLugar = $lugar->Direccion()->first();
                                                                                            $detalle = $direccionLugar ? $direccionLugar->direccion.', '.($direccionLugar->ciudad->nombre ?? '') : '';
                                                                                            return ['id' => $lugar->id, 'nombre' => trim($lugar->nombre.($detalle !== '' ? ' - '.$detalle : ''))];
                                                                                        })->values(),
                                                                                    ];
                                                                                @endphp
                                                                                <!--Botón Modal información-->
                                                                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="abrir_info_prof_vet({{ \Illuminate\Support\Js::from($infoProfesional) }});" data-toggle="tooltip" data-placement="top" title="Información"><i class="feather icon-info"></i></button>

                                                                                <button type="button" class="btn btn-primary btn-sm btn-icon" onclick="f_profesional({{ $prof_medico->id }});" data-toggle="tooltip" data-placement="top" title="Información profesional"><i class="feather icon-eye"></i></button>

                                                                                {{-- Oculto: el datos bancarios ahora se ve en el modal Información --}}
                                                                                {{-- <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datos_depositos({{ $prof_medico->id_usuario }});" data-toggle="tooltip" data-placement="top" title="Datos bancarios"><i class="feather icon-credit-card"></i></button> --}}

                                                                                {{-- Oculto: el horario ahora se ve en el modal Información --}}
                                                                                {{-- <button type="button" class="btn btn-purple btn-sm btn-icon" onclick="horario_profesional_cm({{ $prof_medico->id }}, {{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Horario y días de atención"><i class="feather icon-clock"></i></button> --}}
                                                                            </td>
                                                                            {{-- <td class="align-middle text-center">
                                                                                <!--Botón Modal convenios-->
                                                                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="convenio_profesional_cm({{ $prof_medico->id }},{{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Convenio"><i class="feather icon-file-text"></i></button>
                                                                            </td> --}}
                                                                            <td class="align-middle">
                                                                                <!--Botón Modal roles y permisos-->
                                                                                <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="mensaje({{ $prof_medico->id }});" data-toggle="tooltip" data-placement="top" title="Ver" @if(!$adm_medico) disabled @endif><i class="feather icon-mail"></i></button>
                                                                            </td>
                                                                            <td class="align-middle">
                                                                                <!--Botón Modal roles y permisos-->
                                                                                <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="historial({{ $prof_medico->id }});" @if(!$adm_medico) disabled @endif data-toggle="tooltip" data-placement="top" title="Ver"><i class="feather icon-mail"></i></button>
                                                                            </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif

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
                    <!--Cierre: Tab medicos-->

                    <!--Tab odontologos-->
                    <div class="tab-pane fade" id="odontologos" role="tabpanel" aria-labelledby="odontologos-tab">
                        <div class="row">
                            <div class="col-md-12 col-md-12 col-lg-12 col-xl-12">
                                <div class="card">
                                    <div class="card-header encabezado-card">
                                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-award"></i></span>Profesionales odontólogos</h5>
                                        <div class="encabezado-card-acciones">
                                            <button type="button" class="btn btn-info btn-sm" onclick="asociar_profesional();"><i class="feather icon-plus mr-1"></i>Vincular odontólogo</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
										<div class="row">
                                            <div class="col-md-12 col-md-12 col-lg-12 col-xl-12">
                                                <div class="table-responsive">
        											<table id="profesionales_odonto" class="display table table-striped dt-responsive nowrap" style="width:100%">
        												<thead>
        													<tr>
        														<th class="align-middle">Nombre / Rut</th>
        														<th class="align-middle">Especialidad</th>
        														<th class="align-middle">Sucursales</th>
        														<th class="align-middle">Info</th>
        														{{-- Oculto: los convenios ahora se ven en el modal Información --}}
        														{{-- <th class="align-middle">Convenio</th> --}}
        														<th class="align-middle">Mensajes</th>
                                                                <th class="align-middle">Historial</th>
        													</tr>
        												</thead>
        												<tbody>
        													@if($lista_profesionales['ODONTOLOG'])
                                                                @foreach ($lista_profesionales['ODONTOLOG'] as $prof_medico )
                                                                    <tr>
                                                                        <td class="align-middle">
                                                                            <span><strong>{{ $prof_medico->nombre.' '.$prof_medico->apellido_uno.' '.$prof_medico->apellido_dos }}</strong></span><br>
                                                                            <span>{{ $prof_medico->rut }}</span>
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            <span>{{ $prof_medico->TipoEspecialidad()->first()->nombre }}</span>
                                                                            @if (!empty($prof_medico->id_sub_tipo_especialidad))
                                                                                <br>
                                                                                <span>{{ $prof_medico->SubTipoEspecialidad()->first()->nombre }}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            @foreach($prof_medico->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get() as $key_lugar => $value_lugar)
                                                                                    <span>{{ $value_lugar->Direccion()->first()->direccion.', '.$value_lugar->Direccion()->first()->ciudad->nombre  }}</span><br>
                                                                            @endforeach
                                                                        </td>
                                                                            <td class="align-middle">
                                                                                @php
                                                                                    $infoProfesional = [
                                                                                        'titulo' => 'Información',
                                                                                        'id_profesional' => $prof_medico->id,
                                                                                        'id_usuario' => $prof_medico->id_usuario,
                                                                                        'id_lugar_atencion' => $lugarAtencionAccion,
                                                                                        'lugares' => $prof_medico->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get()->map(function ($lugar) {
                                                                                            $direccionLugar = $lugar->Direccion()->first();
                                                                                            $detalle = $direccionLugar ? $direccionLugar->direccion.', '.($direccionLugar->ciudad->nombre ?? '') : '';
                                                                                            return ['id' => $lugar->id, 'nombre' => trim($lugar->nombre.($detalle !== '' ? ' - '.$detalle : ''))];
                                                                                        })->values(),
                                                                                    ];
                                                                                @endphp
                                                                                <!--Botón Modal información-->
                                                                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="abrir_info_prof_vet({{ \Illuminate\Support\Js::from($infoProfesional) }});" data-toggle="tooltip" data-placement="top" title="Información"><i class="feather icon-info"></i></button>

                                                                                <button type="button" class="btn btn-primary btn-sm btn-icon" onclick="f_profesional({{ $prof_medico->id }});" data-toggle="tooltip" data-placement="top" title="Información profesional"><i class="feather icon-eye"></i></button>

                                                                                {{-- Oculto: el datos bancarios ahora se ve en el modal Información --}}
                                                                                {{-- <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datos_depositos({{ $prof_medico->id_usuario }});" data-toggle="tooltip" data-placement="top" title="Datos bancarios"><i class="feather icon-credit-card"></i></button> --}}

                                                                                {{-- Oculto: el horario ahora se ve en el modal Información --}}
                                                                                {{-- <button type="button" class="btn btn-purple btn-sm btn-icon" onclick="horario_profesional_cm({{ $prof_medico->id }}, {{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Horario y días de atención"><i class="feather icon-clock"></i></button> --}}
                                                                            </td>
                                                                            {{-- <td class="align-middle text-center">
                                                                                <!--Botón Modal convenios-->
                                                                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="convenio_profesional_cm({{ $prof_medico->id }},{{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Convenio"><i class="feather icon-file-text"></i></button>
                                                                            </td> --}}
                                                                            <td class="align-middle">
                                                                                <!--Botón Modal roles y permisos-->
                                                                                <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="mensaje({{ $prof_medico->id }});" data-toggle="tooltip" data-placement="top" title="Ver" @if(!$adm_medico) disabled @endif><i class="feather icon-mail"></i></button>
                                                                            </td>
                                                                            <td class="align-middle">
                                                                                <!--Botón Modal roles y permisos-->
                                                                                <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="historial({{ $prof_medico->id }});" @if(!$adm_medico) disabled @endif data-toggle="tooltip" data-placement="top" title="Ver"><i class="feather icon-mail"></i></button>
                                                                            </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
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
                    <!--Cierre: Tab odontologos-->

                    <!--Tab otros profesionales-->
                    <div class="tab-pane fade" id="otros_prof" role="tabpanel" aria-labelledby="otros_prof-tab">
                        <div class="row">
                          <div class="col-md-12 col-md-12 col-lg-12 col-xl-12">
                                <div class="card">
                                    <div class="card-header encabezado-card">
                                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-briefcase"></i></span>Otros profesionales de la salud</h5>
                                        <div class="encabezado-card-acciones">
                                            <button type="button" class="btn btn-info btn-sm" onclick="asociar_profesional();"><i class="feather icon-plus mr-1"></i>Vincular otros profesionales</button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 col-md-12 col-lg-12 col-xl-12">
                                                <div class="table-responsive">
            										<table id="profesionales_otros" class="display table table-striped dt-responsive nowrap" style="width:100%">
            											<thead>
            												<tr>
            													<th class="align-middle">Nombre / Rut</th>
            													<th class="align-middle">Profesión/Especialidad</th>
            													<th class="align-middle">Sucursales</th>
            													<th class="align-middle">Info</th>
            													{{-- Oculto: los convenios ahora se ven en el modal Información --}}
            													{{-- <th class="align-middle">Convenio</th> --}}
                                                                <th class="align-middle">Mensaje</th>
                                                                <th class="align-middle">Historial</th>
            												</tr>
            											</thead>
            											<tbody>
            												@if($lista_profesionales['OTROS'])
                                                                @foreach ($lista_profesionales['OTROS'] as $prof_medico )
                                                                    <tr>
                                                                        <td class="align-middle">
                                                                            <span><strong>{{ $prof_medico->nombre.' '.$prof_medico->apellido_uno.' '.$prof_medico->apellido_dos }}</strong></span><br>
                                                                            <span>{{ $prof_medico->rut }}</span>
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            <span>{{ $prof_medico->TipoEspecialidad()->first()->nombre }}</span>
                                                                            @if (!empty($prof_medico->id_sub_tipo_especialidad))
                                                                                <br>
                                                                                <span>{{ $prof_medico->SubTipoEspecialidad()->first()->nombre }}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="align-middle">
                                                                            @foreach($prof_medico->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get() as $key_lugar => $value_lugar)
                                                                                    <span>{{ $value_lugar->Direccion()->first()->direccion.', '.$value_lugar->Direccion()->first()->ciudad->nombre  }}</span><br>
                                                                            @endforeach
                                                                        </td>
                                                                            <td class="align-middle">
                                                                                @php
                                                                                    $infoProfesional = [
                                                                                        'titulo' => 'Información',
                                                                                        'id_profesional' => $prof_medico->id,
                                                                                        'id_usuario' => $prof_medico->id_usuario,
                                                                                        'id_lugar_atencion' => $lugarAtencionAccion,
                                                                                        'lugares' => $prof_medico->LugaresAtencion()->whereIn('lugares_atencion.id', $scopeLugarIds)->get()->map(function ($lugar) {
                                                                                            $direccionLugar = $lugar->Direccion()->first();
                                                                                            $detalle = $direccionLugar ? $direccionLugar->direccion.', '.($direccionLugar->ciudad->nombre ?? '') : '';
                                                                                            return ['id' => $lugar->id, 'nombre' => trim($lugar->nombre.($detalle !== '' ? ' - '.$detalle : ''))];
                                                                                        })->values(),
                                                                                    ];
                                                                                @endphp
                                                                                <!--Botón Modal información-->
                                                                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="abrir_info_prof_vet({{ \Illuminate\Support\Js::from($infoProfesional) }});" data-toggle="tooltip" data-placement="top" title="Información"><i class="feather icon-info"></i></button>

                                                                                <button type="button" class="btn btn-primary btn-sm btn-icon" onclick="f_profesional({{ $prof_medico->id }});" data-toggle="tooltip" data-placement="top" title="Información profesional"><i class="feather icon-eye"></i></button>

                                                                                {{-- Oculto: el datos bancarios ahora se ve en el modal Información --}}
                                                                                {{-- <button type="button" class="btn btn-success btn-sm btn-icon" onclick="datos_depositos({{ $prof_medico->id_usuario }});" data-toggle="tooltip" data-placement="top" title="Datos bancarios"><i class="feather icon-credit-card"></i></button> --}}

                                                                                {{-- Oculto: el horario ahora se ve en el modal Información --}}
                                                                                {{-- <button type="button" class="btn btn-purple btn-sm btn-icon" onclick="horario_profesional_cm({{ $prof_medico->id }}, {{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Horario y días de atención"><i class="feather icon-clock"></i></button> --}}
                                                                            </td>
                                                                            {{-- <td class="align-middle text-center">
                                                                                <!--Botón Modal convenios-->
                                                                                <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="convenio_profesional_cm({{ $prof_medico->id }},{{ $lugarAtencionAccion }});" data-toggle="tooltip" data-placement="top" title="Convenio"><i class="feather icon-file-text"></i></button>
                                                                            </td> --}}
                                                                            <td class="align-middle">
                                                                                <!--Botón Modal roles y permisos-->
                                                                                <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="mensaje({{ $prof_medico->id }});" data-toggle="tooltip" data-placement="top" title="Ver" @if(!$adm_medico) disabled @endif><i class="feather icon-mail"></i></button>
                                                                            </td>
                                                                            <td class="align-middle">
                                                                                <!--Botón Modal roles y permisos-->
                                                                                <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="historial({{ $prof_medico->id }});" @if(!$adm_medico) disabled @endif data-toggle="tooltip" data-placement="top" title="Ver"><i class="feather icon-mail"></i></button>
                                                                            </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
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
                    <!--Cierre: Tab personal administrativo-->
                </div>
                </div>
			</div>
		</div>
	</div>
</div>
<!--****Cierre Container Completo****-->
<input type="hidden" name="id_profesional_mensaje" id="id_profesional_mensaje" value="">
@endsection

@section('js-profesionales')
    <script>
$(document).ready(function(){
        $('#msj_para_difusion').select2();
        $('#adjunto').dropzone();
    });
        function f_profesional(id_profesional) {
            $.ajax({
                url: "{{ route('profesional.informacionProfesional') }}",
                type: 'get',
                data: {
                    id_profesional: id_profesional,
                },
            })
            .done(function(data) {
                if (data.estado !== 1 || !data.profesional) {
                    swal({
                        title: 'Información profesional',
                        text: data.msj || 'No fue posible cargar la información del veterinario.',
                        icon: 'error',
                        buttons: 'Aceptar',
                    });
                    return;
                }

                pintarFichaProfesional(data);
                $('#ficha_profesional').modal('show');
            })
            .fail(function() {
                swal({
                    title: 'Información profesional',
                    text: 'No fue posible cargar la información del veterinario.',
                    icon: 'error',
                    buttons: 'Aceptar',
                });
            });
        }

        function mensaje(id) {
            console.log(id);
            let url = "{{ ROUTE('adm_cm.dame_profesional_cm') }}";
            $.ajax({
                type:'post',
                url: url,
                data:{
                    id:id,
                    _token:CSRF_TOKEN
                },
                success: function(resp){
                    $('#id_profesional_mensaje').val(id);
                    console.log(resp);
                    let profesional = resp.profesional;
                    $('#para_destino').val(profesional.nombre+" "+profesional.apellido_uno+" "+profesional.apellido_dos);
                    $('#mensaje_profesional').modal('show');
                },
                error: function(error){
                    console.log(error);
                }
            })

        }

        function enviar_mensaje_difusion() {
            $('#mensaje_difusion').modal('show');
        }

        function historial(id){
            // abrir modal de historial
            $('#historial_mensajes_profesional').modal('show');
            let url = "{{ ROUTE('adm_cm.historial_mensajes_profesional',['id' => '__ID__']) }}";
            url = url.replace('__ID__',id);

            $('#historial_mensajes_profesional_body').html('<p class="historial-mensajes-cargando">Cargando mensajes...</p>');

            $.ajax({
                type:'get',
                url: url,
                success: function(resp){
                    // El diseño de la lista está en el modal (historial_mensajes.blade.php)
                    pintar_historial_mensajes(resp.mensajes);
                },
                error: function(error){
                    console.log(error);
                    $('#historial_mensajes_profesional_body').html('<p class="historial-mensajes-cargando">No fue posible cargar el historial de mensajes.</p>');
                }
            })
        }

        function validar_email_agenda() {
        if ($("#agregar_profesional_nuevo_correo").val().indexOf('@', 0) == -1 || $("#agregar_profesional_nuevo_correo")
            .val().indexOf(
                '.', 0) == -1) {
            swal({
                title: "El correo electrónico introducido no es correcto.",
                icon: "error",
                buttons: "Aceptar",
                DangerMode: true,
            })
            // alert('El correo electrónico introducido no es correcto.');
            $("#guardar_reserva_paciente").prop('disabled', true);
            return false;
        }

        let email = $('#agregar_profesional_nuevo_correo').val();
        let url = "#";

        $.ajax({
            url: url,
            type: "get",
            data: {

                email: email,

            }

        })
        .done(function(data) {
            if (data == 'fail') {

                // console.log(data);

                $('#mensaje_email_reserva').text('el email ya esta en nuestros registros');
                $('#mensaje_email_reserva').show();
                $('#agregar_profesional_nuevo_correo').focus();

                $("#guardar_reserva_paciente").prop('disabled', true);

            } else {
                $('#mensaje_email_reserva').text('');
                $('#mensaje_email_reserva').hide();
                $("#guardar_reserva_paciente").prop('disabled', false);
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });
    }
    </script>
@endsection

@section('page-script')
<script>
    $(function () {
        const pestanasPersonal = ['prof-vet', 'asistentes', 'otros_trabajadores', 'prof-salud', 'odontologos', 'otros_prof'];
        const tab = new URLSearchParams(window.location.search).get('tab');
        // Los enlaces antiguos a la pestaña Contratos se llevan a su nueva página
        if (tab === 'contratos') {
            window.location.replace("{{ route('adm_cm.contratos') }}");
            return;
        }
        if (pestanasPersonal.includes(tab)) {
            $('.pill-moderno a[href="#' + tab + '"]').first().tab('show');
        }

        // Las pestañas de los modales (Información) no van a la URL; si no, al recargar se perdería la pestaña de la página
        $('.pill-moderno a[data-toggle="tab"]').not('.modal .pill-moderno a').on('shown.bs.tab', function (e) {
            // La pestaña queda en la URL para que al recargar (después de guardar) se vuelva a la misma
            const url = new URL(window.location.href);
            url.searchParams.set('tab', $(e.target).attr('href').substring(1));
            window.history.replaceState(null, '', url.toString());

            // Las tablas de pestañas ocultas se recalculan al mostrarse para que no queden descuadradas
            if ($.fn.dataTable) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
            }
        });
    });
</script>
@include('app.adm_cm.partials.pestanas_personal_js')
@endsection

@section('modales-profesionales_inst')
    @include('app.adm_cm.modal_adm.asociar_profesional')
    @include('app.adm_cm.modal_adm.mensaje_profesional')
    @include('app.adm_cm.modal_adm.mensaje_difusion')
    @include('app.adm_cm.modal_adm.historial_mensajes')

    {{-- @include('app.adm_cm.modal_adm.datos_banco') --}}
    {{-- @include('app.adm_cm.modal_adm.horario_usuario') --}}
    {{-- @include('app.adm_cm.modal_adm.convenio_usuario') --}}
    {{-- El contacto ahora usa modales.personal.contacto_personal (mismo id #contacto_usuario) --}}
    @include('app.adm_cm.modal_adm.info_prof_vet')
    @include('app.general.buscador_profesionales.modals.ficha_profesional')
    {{-- @include('app.adm_cm.modales.personal.contacto_personal') --}}
    {{--  @include('app.adm_cm.modal_adm.editar_profesional')  --}}
    {{--  @include('app.adm_cm.modal_adm.registrar_profesional')  --}}
    {{--  @include('app.adm_cm.modal_adm.roles_permisos_prof')  --}}
    @include('app.adm_cm.modal_adm.liquidacion_profesionales')

    {{-- Pestaña Asistentes --}}
    @include('app.adm_cm.modales.personal.registrar_personal')
    {{-- Banco, horario y roles ahora se ven en el modal Información del asistente --}}
    {{-- @include('app.adm_cm.modales.personal.datos_banco') --}}
    {{-- @include('app.adm_cm.modales.personal.horario_personal') --}}
    {{-- @include('app.adm_cm.modales.personal.permisos_rol') --}}
    @include('app.adm_cm.modales.personal.info_asistente')
    @include('app.adm_cm.modales.personal.editar_personal')
    @include('app.adm_cm.modales.personal.finalizar_personal')
    @include('app.adm_cm.modales.personal.asociar_personal')
@endsection
