@extends('app.peluqueria.layout')
@php
    // El perfil se busca aquí mismo: la pantalla solo muestra datos y usa los guardados que ya existen
    $perfil = \App\Models\Peluqueria::where('id_usuario', Auth::id())->first();
    abort_unless($perfil, 403);
    $correo_perfil = Auth::user()->email ?? '';

    // Datos de la cuenta para la pestaña del administrador
    $usuario_perfil = Auth::user();
    $nombres_admin = $usuario_perfil->nombres ?: $usuario_perfil->name;
    // En la etiqueta de la cabecera va el nombre y el primer apellido del administrador
    $nombre_corto_admin = trim($nombres_admin . ' ' . ($usuario_perfil->apellido_uno ?? ''));

    // Residencia del administrador: igual que el tutor, sale de su ficha de paciente si la tiene
    $paciente_admin = \App\Models\Paciente::where('id_usuario', $usuario_perfil->id)->first();
    $direccion_admin = $paciente_admin && $paciente_admin->id_direccion ? \App\Models\Direccion::find($paciente_admin->id_direccion) : null;
    $ciudad_admin = $direccion_admin && $direccion_admin->id_ciudad ? \App\Models\Ciudad::find($direccion_admin->id_ciudad) : null;
    $id_region_admin = $ciudad_admin->id_region ?? null;

    // Datos de la empresa: se leen si algún día existen en el perfil; mientras tanto quedan vacíos
    $empresa_perfil = [
        'rut' => $perfil->rut ?? '',
        'razon_social' => $perfil->razon_social ?? '',
        'giro' => $perfil->giro ?? '',
    ];

    // Días y horario de atención, guardados como lista por día si existen
    $dias_semana = [
        'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves',
        'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo',
    ];
    $horario_perfil = $perfil->horario ?? [];
    if (is_string($horario_perfil)) {
        $horario_perfil = json_decode($horario_perfil, true) ?: [];
    }
    $horario_registrado = !empty($horario_perfil);

    // Letra de cada día para el resumen
    $dias_cortos = ['lunes' => 'L', 'martes' => 'M', 'miercoles' => 'M', 'jueves' => 'J', 'viernes' => 'V', 'sabado' => 'S', 'domingo' => 'D'];

    // Si de lunes a viernes tienen el mismo horario (o no hay horario) se parte con la semana junta
    $dato_dia = fn ($dia) => [!empty($horario_perfil[$dia]['abierto']), $horario_perfil[$dia]['desde'] ?? null, $horario_perfil[$dia]['hasta'] ?? null];
    $semana_igual = collect(['martes', 'miercoles', 'jueves', 'viernes'])->every(fn ($dia) => $dato_dia($dia) === $dato_dia('lunes'));
    $horario_modo = ($perfil->horario_modo ?? null) === 'dias' || !$semana_igual ? 'dias' : 'semana';

    // Comuna y región con el mismo criterio del resto del sistema: la comuna es una ciudad y la región sale de ella
    $regiones_perfil = \App\Models\Region::orderBy('nombre')->get();
    $ciudad_perfil = !empty($perfil->id_ciudad) ? \App\Models\Ciudad::find($perfil->id_ciudad) : null;
    $id_region_perfil = $ciudad_perfil->id_region ?? null;
    $region_perfil = $id_region_perfil ? $regiones_perfil->firstWhere('id', $id_region_perfil) : null;
    $ciudades_perfil = $id_region_perfil
        ? \App\Models\Ciudad::where('id_region', $id_region_perfil)->orderBy('nombre')->get()
        : collect();
    $region_admin = $id_region_admin ? $regiones_perfil->firstWhere('id', $id_region_admin) : null;
    $ciudades_admin = $id_region_admin
        ? \App\Models\Ciudad::where('id_region', $id_region_admin)->orderBy('nombre')->get()
        : collect();
@endphp
@section('content')
<div class="perfil-peluqueria">
    <div class="user-profile user-card mb-4">
        <div class="card-body py-0">
            <div class="user-about-block m-0">
                <div class="row">
                    <input type="file" id="logo_peluqueria_input" accept="image/png,image/jpeg,image/jpg,image/webp" class="d-none">
                    {{-- Arriba el nombre de la casa matriz y en la etiqueta quién administra la cuenta --}}
                    @include('template.partials.perfil_cabecera', [
                        'nombre' => $perfil->nombre,
                        'rol' => 'Administrador: ' . ($nombre_corto_admin !== '' ? $nombre_corto_admin : 'Sin registro'),
                        'foto' => $perfil->logo ?? null,
                        'foto_base' => 'images/iconos/usuario_profesional.svg',
                        'id_foto' => 'peluqueria-profile-image',
                        'al_cambiar' => 'seleccionar_logo_peluqueria()',
                        'al_eliminar' => 'eliminar_logo_peluqueria()',
                        'nombre_tal_cual' => true,
                        'datos_perfil' => [$perfil->nombre, $perfil->telefono, $perfil->direccion, $correo_perfil],
                    ])
                    <div class="col-md-12 m-0">
                        <ul class="nav nav-tabs profile-tabs nav-fill mt-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link text-reset active" id="administrador-tab" data-toggle="tab" href="#administrador" role="tab" aria-controls="administrador" aria-selected="true">Perfil administrador</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-reset" id="peluqueria-tab" data-toggle="tab" href="#peluqueria" role="tab" aria-controls="peluqueria" aria-selected="false">Perfil peluquería</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-reset" id="seguridad-tab" data-toggle="tab" href="#seguridad" role="tab" aria-controls="seguridad" aria-selected="false">Contraseña</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content">
        <!--PERFIL ADMINISTRADOR-->
        <div class="tab-pane fade show active" id="administrador" role="tabpanel" aria-labelledby="administrador-tab">
            <div class="row">
                <div class="col-12">
                    <!--Card: datos de la cuenta del administrador-->
                    <div class="card">
                        <div class="card-header-new d-flex align-items-center justify-content-between">
                            <h5 class="mb-0"><i class="feather icon-user icono-purple"></i> Perfil del administrador</h5>
                            <button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".info_admin" aria-expanded="false" aria-controls="info_admin_1 info_admin_2 info_admin_3" title="Editar">
                                <i class="feather icon-edit"></i>
                            </button>
                        </div>
                        <!--Datos-->
                        <div class="card-body info_admin collapse show" id="info_admin_1">
                            <div class="form-row">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Información personal</h6>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                    <label class="font-weight-bolder ml-0 mb-0">RUT</label>
                                    <div>{{ $usuario_perfil->rut ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                    <label class="font-weight-bolder ml-0 mb-0">Nombre</label>
                                    <div>{{ $nombres_admin ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                    <label class="font-weight-bolder ml-0 mb-0">Primer apellido</label>
                                    <div>{{ $usuario_perfil->apellido_uno ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                    <label class="font-weight-bolder ml-0 mb-0">Segundo apellido</label>
                                    <div>{{ $usuario_perfil->apellido_dos ?: 'Sin registro' }}</div>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Contacto</h6>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                    <label class="font-weight-bolder ml-0 mb-0">Correo electrónico</label>
                                    <div class="text-break">{{ $correo_perfil ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                    <label class="font-weight-bolder ml-0 mb-0">Teléfono</label>
                                    <div>{{ $usuario_perfil->telefono ?: 'Sin registro' }}</div>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Residencia</h6>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                    <label class="font-weight-bolder ml-0 mb-0">Dirección</label>
                                    <div class="text-break">{{ trim(($direccion_admin->direccion ?? '') . ' ' . ($direccion_admin->numero_dir ?? '')) ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3 mb-md-0">
                                    <label class="font-weight-bolder ml-0 mb-0">Región</label>
                                    <div>{{ $region_admin->nombre ?? 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6 col-lg-3 mb-0">
                                    <label class="font-weight-bolder ml-0 mb-0">Comuna</label>
                                    <div>{{ $ciudad_admin->nombre ?? 'Sin registro' }}</div>
                                </div>
                            </div>
                        </div>
                        <!--Cierre: Datos-->
                        <!--(Editar) Datos: el body y el footer van en el mismo formulario-->
                        <form id="form_administrador" class="m-0" novalidate>
                            <div class="card-body info_admin collapse" id="info_admin_2">
                                {{-- Aviso mientras el guardado de los datos del administrador no exista --}}
                                <div class="alert alert-info d-none" id="aviso_administrador" role="status"></div>
                                <div class="form-row">
                                    <div class="col-12">
                                        <h6 class="titulo-sm">Información personal</h6>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                        <label class="floating-label-activo" for="admin_rut">RUT</label>
                                        {{-- El RUT es el de la cuenta, aquí solo se muestra --}}
                                        <input type="text" class="form-control form-control-sm" id="admin_rut" value="{{ $usuario_perfil->rut }}" disabled>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                        <label class="floating-label-activo" for="admin_nombres">Nombre</label>
                                        <input type="text" class="form-control form-control-sm" id="admin_nombres" name="nombres" maxlength="255" value="{{ $nombres_admin }}" required>
                                        <div class="invalid-feedback">Escribe tu nombre.</div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                        <label class="floating-label-activo" for="admin_apellido_uno">Primer apellido</label>
                                        <input type="text" class="form-control form-control-sm" id="admin_apellido_uno" name="apellido_uno" maxlength="255" value="{{ $usuario_perfil->apellido_uno }}" required>
                                        <div class="invalid-feedback">Escribe tu primer apellido.</div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                        <label class="floating-label-activo" for="admin_apellido_dos">Segundo apellido</label>
                                        <input type="text" class="form-control form-control-sm" id="admin_apellido_dos" name="apellido_dos" maxlength="255" value="{{ $usuario_perfil->apellido_dos }}">
                                    </div>
                                </div>
                                <div class="form-row mt-2">
                                    <div class="col-12">
                                        <h6 class="titulo-sm">Contacto</h6>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                        <label class="floating-label-activo" for="admin_correo">Correo electrónico</label>
                                        {{-- El correo es el de la cuenta, aquí solo se muestra --}}
                                        <input type="email" class="form-control form-control-sm" id="admin_correo" value="{{ $correo_perfil }}" disabled>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                        <label class="floating-label-activo" for="admin_telefono">Teléfono</label>
                                        <input type="text" class="form-control form-control-sm" id="admin_telefono" name="telefono" maxlength="30" value="{{ $usuario_perfil->telefono }}">
                                    </div>
                                </div>
                                <div class="form-row mt-2">
                                    <div class="col-12">
                                        <h6 class="titulo-sm">Residencia</h6>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3">
                                        <label class="floating-label-activo" for="admin_direccion">Dirección</label>
                                        <input type="text" class="form-control form-control-sm" id="admin_direccion" name="direccion" maxlength="255" value="{{ trim(($direccion_admin->direccion ?? '') . ' ' . ($direccion_admin->numero_dir ?? '')) }}">
                                    </div>
                                    {{-- Primero la región y después la comuna, que se carga según la región --}}
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3 mb-md-0">
                                        <label class="floating-label-activo" for="admin_region">Región</label>
                                        <select class="form-control form-control-sm selector-region" id="admin_region" name="id_region" data-comuna="#admin_ciudad">
                                            <option value="">Seleccione</option>
                                            @foreach ($regiones_perfil as $region)
                                                <option value="{{ $region->id }}" @selected($region->id == $id_region_admin)>{{ $region->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6 col-lg-3 mb-0">
                                        <label class="floating-label-activo" for="admin_ciudad">Comuna</label>
                                        <select class="form-control form-control-sm" id="admin_ciudad" name="id_ciudad">
                                            <option value="">{{ $id_region_admin ? 'Seleccione su comuna' : 'Primero seleccione la región' }}</option>
                                            @foreach ($ciudades_admin as $ciudad)
                                                <option value="{{ $ciudad->id }}" @selected($ciudad_admin && $ciudad->id == $ciudad_admin->id)>{{ $ciudad->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white info_admin collapse" id="info_admin_3">
                                <div class="d-flex flex-wrap justify-content-end">
                                    <button type="reset" class="btn btn-outline-dark btn-sm mr-2" data-toggle="collapse" data-target=".info_admin"><i class="feather icon-x"></i> Cerrar</button>
                                    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>
                                </div>
                            </div>
                        </form>
                        <!--Cierre: (Editar) Datos-->
                    </div>
                    <!--Cierre: Card del administrador-->
                </div>
            </div>
        </div>
        <!--CIERRE: PERFIL ADMINISTRADOR-->

        <!--PERFIL PELUQUERÍA-->
        <div class="tab-pane fade" id="peluqueria" role="tabpanel" aria-labelledby="peluqueria-tab">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <!--Card única: empresa, contacto y ubicación-->
                    <div class="card">
                        <div class="card-header-new d-flex align-items-center justify-content-between">
                            <h5 class="mb-0"><i class="feather icon-scissors icono-purple"></i> Perfil de la peluquería <span class="titulo-casa-matriz">(Casa matriz)</span></h5>
                            <button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".info_pelu" aria-expanded="false" aria-controls="info_pelu_1 info_pelu_2 info_pelu_3" title="Editar">
                                <i class="feather icon-edit"></i>
                            </button>
                        </div>
                        <!--Datos-->
                        <div class="card-body info_pelu collapse show" id="info_pelu_1">
                            <div class="form-row">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Datos de la empresa</h6>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label class="font-weight-bolder ml-0 mb-0">RUT</label>
                                    <div>{{ $empresa_perfil['rut'] ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label class="font-weight-bolder ml-0 mb-0">Razón social</label>
                                    <div>{{ $empresa_perfil['razon_social'] ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label class="font-weight-bolder ml-0 mb-0">Nombre fantasía</label>
                                    <div>{{ $perfil->nombre ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label class="font-weight-bolder ml-0 mb-0">Giro</label>
                                    <div>{{ $empresa_perfil['giro'] ?: 'Sin registro' }}</div>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Contacto</h6>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label class="font-weight-bolder ml-0 mb-0">Correo electrónico</label>
                                    <div class="text-break">{{ $correo_perfil ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-sm-12 col-md-6">
                                    <label class="font-weight-bolder ml-0 mb-0">Teléfono</label>
                                    <div>{{ $perfil->telefono ?: 'Sin registro' }}</div>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-12">
                                    <h6 class="titulo-sm">Ubicación</h6>
                                </div>
                                <div class="form-group col-12">
                                    <label class="font-weight-bolder ml-0 mb-0">Dirección</label>
                                    <div>{{ $perfil->direccion ?: 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-12 col-sm-6">
                                    <label class="font-weight-bolder ml-0 mb-0">Región</label>
                                    <div>{{ $region_perfil->nombre ?? 'Sin registro' }}</div>
                                </div>
                                <div class="form-group col-12 col-sm-6">
                                    <label class="font-weight-bolder ml-0 mb-0">Comuna</label>
                                    <div>{{ $ciudad_perfil->nombre ?? 'Sin registro' }}</div>
                                </div>
                            </div>
                        </div>
                        <!--Cierre: Datos-->
                        <!--(Editar) Datos: el body y el footer van en el mismo formulario-->
                        <form method="post" action="{{ route('peluqueria.perfil') }}" class="m-0" id="form_peluqueria" novalidate>
                            @csrf
                            <div class="card-body info_pelu collapse" id="info_pelu_2">
                                <div class="form-row">
                                    <div class="col-12">
                                        <h6 class="titulo-sm">Datos de la empresa</h6>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label class="floating-label-activo" for="empresa_rut">RUT</label>
                                        <input type="text" class="form-control form-control-sm" id="empresa_rut" name="rut" maxlength="12" placeholder="76.123.456-7" value="{{ $empresa_perfil['rut'] }}">
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label class="floating-label-activo" for="empresa_razon_social">Razón social</label>
                                        <input type="text" class="form-control form-control-sm" id="empresa_razon_social" name="razon_social" maxlength="255" value="{{ $empresa_perfil['razon_social'] }}">
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label class="floating-label-activo" for="perfil_nombre">Nombre fantasía</label>
                                        <input type="text" class="form-control form-control-sm" id="perfil_nombre" name="nombre" maxlength="255" value="{{ $perfil->nombre }}" required>
                                        <div class="invalid-feedback">Escribe el nombre de la peluquería.</div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label class="floating-label-activo" for="empresa_giro">Giro</label>
                                        <input type="text" class="form-control form-control-sm" id="empresa_giro" name="giro" maxlength="255" placeholder="Ej.: Peluquería y estética de mascotas" value="{{ $empresa_perfil['giro'] }}">
                                    </div>
                                </div>
                                <div class="form-row mt-2">
                                    <div class="col-12">
                                        <h6 class="titulo-sm">Contacto</h6>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label class="floating-label-activo" for="perfil_correo">Correo electrónico</label>
                                        {{-- El correo es el de la cuenta, aquí solo se muestra --}}
                                        <input type="email" class="form-control form-control-sm" id="perfil_correo" value="{{ $correo_perfil }}" disabled>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label class="floating-label-activo" for="perfil_telefono">Teléfono</label>
                                        <input type="text" class="form-control form-control-sm" id="perfil_telefono" name="telefono" maxlength="30" value="{{ $perfil->telefono }}" required>
                                        <div class="invalid-feedback">Escribe un teléfono de contacto.</div>
                                    </div>
                                </div>
                                <div class="form-row mt-2">
                                    <div class="col-12">
                                        <h6 class="titulo-sm">Ubicación</h6>
                                    </div>
                                    <div class="form-group col-12">
                                        <label class="floating-label-activo" for="perfil_direccion">Dirección</label>
                                        <input type="text" class="form-control form-control-sm" id="perfil_direccion" name="direccion" maxlength="255" value="{{ $perfil->direccion }}" required>
                                        <div class="invalid-feedback">Escribe la dirección.</div>
                                    </div>
                                    <div class="form-group col-12 col-sm-6">
                                        <label class="floating-label-activo" for="perfil_region">Región</label>
                                        <select class="form-control form-control-sm selector-region" id="perfil_region" name="perfil_region" data-comuna="#perfil_ciudad">
                                            <option value="">Seleccione</option>
                                            @foreach ($regiones_perfil as $region)
                                                <option value="{{ $region->id }}" @selected($region->id == $id_region_perfil)>{{ $region->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-12 col-sm-6">
                                        <label class="floating-label-activo" for="perfil_ciudad">Comuna</label>
                                        <select class="form-control form-control-sm" id="perfil_ciudad" name="perfil_ciudad">
                                            <option value="">{{ $id_region_perfil ? 'Seleccione su comuna' : 'Primero seleccione la región' }}</option>
                                            @foreach ($ciudades_perfil as $ciudad)
                                                <option value="{{ $ciudad->id }}" @selected($ciudad_perfil && $ciudad->id == $ciudad_perfil->id)>{{ $ciudad->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white info_pelu collapse" id="info_pelu_3">
                                <div class="d-flex flex-wrap justify-content-end">
                                    {{-- Cerrar deja los campos como estaban y vuelve a mostrar los datos --}}
                                    <button type="reset" class="btn btn-outline-dark btn-sm mr-2" data-toggle="collapse" data-target=".info_pelu"><i class="feather icon-x"></i> Cerrar</button>
                                    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>
                                </div>
                            </div>
                        </form>
                        <!--Cierre: (Editar) Datos-->
                    </div>
                    <!--Cierre: Card única-->
                </div>
                <div class="col-12 col-lg-6">
                    <!--Card: días y horario de atención-->
                    <div class="card card-horario">
                        <div class="card-header-new d-flex align-items-center justify-content-between">
                            <h5 class="mb-0"><i class="feather icon-clock icono-purple"></i> Días y horario de atención</h5>
                            <button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".info_horario" aria-expanded="false" aria-controls="info_horario_1 info_horario_2 info_horario_3" title="Editar">
                                <i class="feather icon-edit"></i>
                            </button>
                        </div>
                        <!--Horario-->
                        <div class="card-body info_horario collapse show" id="info_horario_1">
                            @if ($horario_registrado)
                                {{-- Resumen de la semana: los días que atiende quedan en morado --}}
                                <div class="horario-semana" aria-label="Días de atención">
                                    @foreach ($dias_semana as $clave => $dia)
                                        @php($abierto_resumen = !empty($horario_perfil[$clave]['abierto']))
                                        <span class="horario-semana-dia {{ $abierto_resumen ? 'abierto' : '' }}" title="{{ $dia }}: {{ $abierto_resumen ? 'abierto' : 'cerrado' }}">{{ $dias_cortos[$clave] }}</span>
                                    @endforeach
                                </div>
                                @php($dias_a_mostrar = $horario_modo === 'semana' ? ['lunes' => 'Lunes a viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'] : $dias_semana)
                                <ul class="horario-lista">
                                    @foreach ($dias_a_mostrar as $clave => $dia)
                                        @php($abierto_lista = !empty($horario_perfil[$clave]['abierto']))
                                        <li class="{{ $abierto_lista ? '' : 'cerrado' }}">
                                            <span class="horario-lista-dia">{{ $dia }}</span>
                                            <span class="horario-lista-horas">
                                                @if ($abierto_lista)
                                                    {{ $horario_perfil[$clave]['desde'] }} – {{ $horario_perfil[$clave]['hasta'] }}
                                                @else
                                                    Cerrado
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="horario-vacio">
                                    <span class="horario-vacio-icono"><i class="feather icon-clock"></i></span>
                                    <p class="mb-2">Aún no registras tus días y horario de atención.</p>
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="collapse" data-target=".info_horario"><i class="feather icon-plus mr-1"></i>Agregar horario</button>
                                </div>
                            @endif
                        </div>
                        <!--Cierre: Horario-->
                        <!--(Editar) Horario: usa el mismo guardado del perfil, por eso lleva los datos obligatorios ocultos-->
                        <form method="post" action="{{ route('peluqueria.perfil') }}" class="m-0" id="form_horario" novalidate>
                            @csrf
                            <input type="hidden" name="nombre" value="{{ $perfil->nombre }}">
                            <input type="hidden" name="telefono" value="{{ $perfil->telefono }}">
                            <input type="hidden" name="direccion" value="{{ $perfil->direccion }}">
                            <div class="card-body info_horario collapse" id="info_horario_2">
                                @include('app.peluqueria.horario_atencion', ['prefijo' => 'horario', 'horario' => $horario_perfil, 'modo' => $horario_modo])
                            </div>
                            <div class="card-footer bg-white info_horario collapse" id="info_horario_3">
                                <div class="d-flex flex-wrap justify-content-end">
                                    {{-- Cerrar deja el horario como estaba y vuelve a mostrar el resumen --}}
                                    <button type="reset" class="btn btn-outline-dark btn-sm mr-2" data-toggle="collapse" data-target=".info_horario"><i class="feather icon-x"></i> Cerrar</button>
                                    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>
                                </div>
                            </div>
                        </form>
                        <!--Cierre: (Editar) Horario-->
                    </div>
                    <!--Cierre: Card de horario-->
                </div>
            </div>
        </div>
        <!--CIERRE: PERFIL PELUQUERÍA-->

        <!--SEGURIDAD-->
        <div class="tab-pane fade" id="seguridad" role="tabpanel" aria-labelledby="seguridad-tab">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between bg-white">
                            <h5 class="mb-0"><i class="feather icon-lock icono-purple"></i> Contraseña</h5>
                            <button type="button" class="btn btn-outline-purple btn-icon m-0 float-right" data-toggle="collapse" data-target=".pass_personal" aria-expanded="false" aria-controls="pass_personal_1 pass_personal_2" title="Editar">
                                <i class="feather icon-edit"></i>
                            </button>
                        </div>
                        <div class="card-body pass_personal collapse show" id="pass_personal_1">
                            <div class="form-row">
                                <div class="form-group col-12">
                                    <label class="font-weight-bolder ml-0 mb-0">Contraseña actual</label>
                                    <div>••••••••</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pass_personal collapse" id="pass_personal_2">
                            <form id="form_contrasena_peluqueria">
                                <div class="alert d-none" id="mensaje_contrasena" role="status"></div>
                                <div class="form-row">
                                    <div class="form-group col-12">
                                        <label class="floating-label-activo" for="contrasena_actual">Contraseña actual</label>
                                        <div class="campo-clave">
                                            <input type="password" class="form-control form-control-sm" id="contrasena_actual" name="contrasena_actual" autocomplete="current-password" required>
                                            <button type="button" class="campo-clave-ver" data-ver-clave="contrasena_actual" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <div class="form-group col-12">
                                        <label class="floating-label-activo" for="password_registro">Nueva contraseña</label>
                                        <div class="campo-clave">
                                            <input type="password" class="form-control form-control-sm" id="password_registro" name="password_registro" autocomplete="new-password" minlength="8" required>
                                            <button type="button" class="campo-clave-ver" data-ver-clave="password_registro" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <div class="form-group col-12">
                                        <label class="floating-label-activo" for="password_confirmacion_registro">Repita nueva contraseña</label>
                                        <div class="campo-clave">
                                            <input type="password" class="form-control form-control-sm" id="password_confirmacion_registro" name="password_confirmacion_registro" autocomplete="new-password" minlength="8" required>
                                            <button type="button" class="campo-clave-ver" data-ver-clave="password_confirmacion_registro" aria-label="Mostrar la contraseña" aria-pressed="false"><i class="feather icon-eye-off" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="reset" class="btn btn-outline-dark btn-sm mr-2"><i class="feather icon-x"></i> Cancelar</button>
                                        <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save"></i> Guardar cambios</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--CIERRE: SEGURIDAD-->
    </div>
</div>
@endsection
@section('page-script')
<script src="{{ asset('js/peluqueria-horario.js') }}"></script>
<script>
    // Al cambiar la región se cargan sus comunas, con la misma búsqueda que usan los demás perfiles
    $('.selector-region').on('change', function () {
        var comuna = $($(this).data('comuna'));
        var region = $(this).val();

        if (!region) {
            comuna.empty().append(new Option('Primero seleccione la región', ''));
            return;
        }

        comuna.empty().append(new Option('Cargando comunas...', ''));
        $.getJSON(@json(route('buscar_ciudad_region')), { region: region })
            .done(function (ciudades) {
                comuna.empty().append(new Option('Seleccione su comuna', ''));
                $.each(ciudades || [], function (i, ciudad) {
                    comuna.append(new Option(ciudad.nombre, ciudad.id));
                });
            })
            .fail(function () {
                comuna.empty().append(new Option('No fue posible cargar las comunas', ''));
            });
    });

    // Cerrar también devuelve la lista de comunas a la región que estaba guardada
    var comunas_iniciales = $('#perfil_ciudad').html();
    $('#info_pelu_3 button[type="reset"]').on('click', function () {
        setTimeout(function () {
            $('#perfil_ciudad').html(comunas_iniciales);
            $('#perfil_ciudad option[selected]').prop('selected', true);
        }, 0);
    });

    // Perfil de la peluquería: revisa los campos obligatorios antes de enviar
    document.getElementById('form_peluqueria').addEventListener('submit', function (evento) {
        if (!this.checkValidity()) {
            evento.preventDefault();
            this.classList.add('was-validated');
        }
    });

    // ---- Días y horario de atención (el editor está en peluqueria-horario.js) ----
    var editor_horario = $('#form_horario .horario-editor');

    // Cerrar deja el horario como estaba
    $('#info_horario_3 button[type="reset"]').on('click', function () {
        $('#form_horario').removeClass('was-validated');
        setTimeout(function () {
            horario_atencion.restablecer(editor_horario);
        }, 0);
    });

    document.getElementById('form_horario').addEventListener('submit', function (evento) {
        horario_atencion.validar(editor_horario);
        if (!this.checkValidity()) {
            evento.preventDefault();
            this.classList.add('was-validated');
        }
    });

    // Perfil del administrador: se revisan los datos y se avisa, porque aún no tienen dónde guardarse
    document.getElementById('form_administrador').addEventListener('submit', function (evento) {
        evento.preventDefault();
        var aviso = $('#aviso_administrador');
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            aviso.addClass('d-none');
            return;
        }
        aviso.removeClass('d-none').text('Los datos están completos. El guardado de los datos del administrador aún no está disponible.');
    });
    var comunas_admin_iniciales = $('#admin_ciudad').html();
    $('#info_admin_3 button[type="reset"]').on('click', function () {
        $('#form_administrador').removeClass('was-validated');
        $('#aviso_administrador').addClass('d-none');
        // La lista de comunas vuelve a la región que estaba guardada
        setTimeout(function () {
            $('#admin_ciudad').html(comunas_admin_iniciales);
            $('#admin_ciudad option[selected]').prop('selected', true);
        }, 0);
    });

    // Logotipo: la imagen elegida se recorta cuadrada al centro para que llene el círculo
    var logo_original = $('#peluqueria-profile-image').attr('src');
    var logo_base = @json(asset('images/iconos/usuario_profesional.svg'));

    function seleccionar_logo_peluqueria() {
        $('#logo_peluqueria_input').trigger('click');
    }

    $('#logo_peluqueria_input').on('change', function (evento) {
        var archivo = evento.target.files[0];
        if (!archivo) {
            return;
        }
        if (!/^image\/(png|jpe?g|webp)$/.test(archivo.type)) {
            alert('Debes seleccionar una imagen PNG, JPG o WEBP.');
            $(this).val('');
            return;
        }

        var lector = new FileReader();
        lector.onload = function (e) {
            var imagen = new Image();
            imagen.onload = function () {
                // Se toma el cuadrado más grande del centro y se deja en 512 x 512
                var lado = Math.min(imagen.width, imagen.height);
                var lienzo = document.createElement('canvas');
                lienzo.width = 512;
                lienzo.height = 512;
                lienzo.getContext('2d').drawImage(
                    imagen,
                    (imagen.width - lado) / 2, (imagen.height - lado) / 2, lado, lado,
                    0, 0, 512, 512
                );
                mostrar_logo(lienzo.toDataURL('image/png'));
            };
            imagen.src = e.target.result;
        };
        lector.readAsDataURL(archivo);
        $(this).val('');
    });

    function eliminar_logo_peluqueria() {
        if (confirm('El logotipo se quitará del perfil.')) {
            mostrar_logo(logo_base);
        }
    }

    // Muestra el logotipo en la cabecera y en el menú lateral
    function mostrar_logo(url) {
        $('#peluqueria-profile-image').attr('src', url);
        $('.pcoded-navbar .main-menu-header img').attr('src', url);
    }

    // Cambio de contraseña: usa el mismo guardado de los demás perfiles de usuario
    $('#form_contrasena_peluqueria').on('submit', function (evento) {
        evento.preventDefault();
        var formulario = $(this);
        var mensaje = $('#mensaje_contrasena');

        $.ajax({
            url: @json(route('perfil.usuario.contrasena')),
            type: 'POST',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formulario.serialize(),
        })
        .done(function (respuesta) {
            mensaje.removeClass('d-none alert-danger').addClass('alert-success').text(respuesta.message || 'Contraseña actualizada.');
            formulario.find('input').val('');
        })
        .fail(function (jqXHR) {
            var texto = 'No fue posible cambiar la contraseña.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                texto = Object.values(jqXHR.responseJSON.errors).flat().join(' ');
            } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                texto = jqXHR.responseJSON.message;
            }
            mensaje.removeClass('d-none alert-success').addClass('alert-danger').text(texto);
        });
    });
</script>
@endsection
