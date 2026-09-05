@extends('template.adm_cm.template')

@section('content')

<style>
    .usuarios-card {
        overflow: hidden;
    }
    .usuarios-wrapper {
        flex: 0 0 100% !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .usuarios-buscador {
        width: 100%;
        max-width: 330px;
    }
    .usuarios-modulo {
        border: 1px solid #e4e9f2;
        border-radius: .5rem;
        padding: 1rem;
        text-align: center;
        background: #fff;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .usuarios-modulo:hover {
        transform: translateY(-3px);
        box-shadow: 0 .4rem 1rem rgba(35, 47, 62, .12);
    }
    .usuarios-modulo img {
        width: 46px;
        height: 46px;
        object-fit: contain;
        margin-bottom: .65rem;
    }
    .usuarios-modulo h6 {
        margin-bottom: .35rem;
        font-weight: 700;
    }
    .usuarios-modulo p {
        min-height: 38px;
        margin-bottom: .8rem;
        color: #6c757d;
        font-size: .82rem;
    }
    .usuarios-item {
        margin-bottom: 1rem;
    }
</style>



 <!--Container Completo-->

    <div class="pcoded-main-container">

        <div class="pcoded-content">

            <!--Header-->

            <div class="page-header">

                <div class="page-block">

                    <div class="row align-items-center">

                        <div class="col-md-12">

                            <div class="page-header-title">
                                @php
                                    $nombreAdministrador = trim(Auth::user()->name ?? Auth::user()->nombre ?? 'Administrador');
                                    $primerNombreAdministrador = collect(preg_split('/\s+/', $nombreAdministrador))
                                        ->filter()
                                        ->first() ?? 'Administrador';
                                @endphp
                                <h4 class="f-w-800 text-white">Hola, {{ $primerNombreAdministrador }}</h4>
                                <p class="text-white">Bienvenido a tu escritorio de Administrador General de {{ mb_strtoupper($institucion->nombre) }}</p>
                                @if (false && !empty($contextosCentro) && !empty($contextoActivo))
                                    <form method="GET" action="{{ route('adm_cm.home') }}" class="mt-3">
                                        <div class="form-row align-items-end">
                                            <div class="col-md-5">
                                                <label class="text-white mb-1">Institución / Sucursal activa</label>
                                                <select name="contexto" class="form-control form-control-sm">
                                                    @foreach ($contextosCentro as $contexto)
                                                        <option value="{{ $contexto['key'] }}" @selected(($contextoActivo['key'] ?? '') === $contexto['key'])>
                                                            {{ $contexto['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 mt-2 mt-md-0">
                                                <button type="submit" class="btn btn-light btn-sm btn-block">Cambiar</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!--Cierre: Header-->

            <!--Botones-->

            <div class="row">

                <div class="col-md-12">

                    <div class="card subir py-auto bg-info">

                        <div class="card-body text-center">

                             <h5 class=" mb-0 text-white f-24">Veterinaria {{ mb_strtoupper($institucion->nombre) }}</h5>

                        </div>

                    </div>

                </div>

            </div>

               

               

            <div class="row row-cols-2 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">

                <div class="col">

                    <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.configuracion') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-50 text-center" src="{{ asset('images/iconos/panel_configuracion.svg') }}">

                                <h6 class="mt-2 mb-0">Configurar mi VET</h6>

                            </div>

                        </a>

                    </div>

                </div>

                <div class="col">

                    <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.adm_medico') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-50 text-center" src="{{ asset('images/iconos/adm_medica.png') }}">

                                <h6 class="mt-2 mb-0">Administración médica</h6>

                            </div>

                        </a>

                    </div>

                </div>

                <div class="col">
                    <div class="card subir usuarios-card">
                        <a href="{{ ROUTE('adm_cm.usuarios') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/profesionales.svg') }}" alt="Usuarios">
                                <h6 class="mt-2 mb-0">Usuarios</h6>
                            </div>
                        </a>
                        <div class="d-none card-header bg-info text-white flex-column flex-md-row align-items-md-center justify-content-between">
                            <div>
                                <h4 class="mb-1 text-white"><i class="feather icon-users mr-2"></i>Usuarios</h4>
                                <small>Profesionales, contratos, asistentes, mascotas y tutores</small>
                            </div>
                            <div class="usuarios-buscador mt-3 mt-md-0">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="feather icon-search"></i></span>
                                    </div>
                                    <input type="search" id="buscar-modulo-usuario" class="form-control" placeholder="Buscar tipo de usuario..." aria-label="Buscar dentro de usuarios">
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-none">
                            <div class="row" id="modulos-usuarios">
                                <div class="col-sm-6 col-xl usuarios-item" data-usuario-item="profesionales medicos veterinarios especialistas asociar">
                                    <div class="usuarios-modulo h-100">
                                        <img src="{{ asset('images/iconos/profesionales.svg') }}" alt="Profesionales">
                                        <h6>Profesionales</h6>
                                        <p>Buscar, revisar y asociar profesionales.</p>
                                        <a class="btn btn-info btn-sm btn-block" href="{{ ROUTE('adm_cm.profesionales') }}"><i class="feather icon-search mr-1"></i>Buscar y administrar</a>
                                        <a class="btn btn-outline-info btn-sm btn-block" href="{{ ROUTE('adm_cm.profesionales') }}"><i class="feather icon-user-plus mr-1"></i>Inscribir o asociar</a>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl usuarios-item" data-usuario-item="contratos incorporaciones personal rrhh contratar">
                                    <div class="usuarios-modulo h-100">
                                        <img src="{{ asset('images/iconos/cotizacion.svg') }}" alt="Contratos">
                                        <h6>Contratos</h6>
                                        <p>Contratos e incorporaciones del centro.</p>
                                        <a class="btn btn-info btn-sm btn-block" href="{{ ROUTE('adm_cm.area_contratos_nuevos') }}"><i class="feather icon-search mr-1"></i>Buscar contratos</a>
                                        <a class="btn btn-outline-info btn-sm btn-block" href="{{ ROUTE('adm_cm.area_contratos_nuevos') }}"><i class="feather icon-file-plus mr-1"></i>Nueva inscripci&oacute;n</a>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl usuarios-item" data-usuario-item="asistentes empleados personal registrar asociar">
                                    <div class="usuarios-modulo h-100">
                                        <img src="{{ asset('images/iconos/mis_asistentes.svg') }}" alt="Asistentes">
                                        <h6>Asistentes</h6>
                                        <p>Buscar, registrar y asociar asistentes.</p>
                                        <a class="btn btn-info btn-sm btn-block" href="{{ ROUTE('adm_cm.personal') }}"><i class="feather icon-search mr-1"></i>Buscar asistentes</a>
                                        <a class="btn btn-outline-info btn-sm btn-block" href="{{ ROUTE('adm_cm.personal') }}"><i class="feather icon-user-plus mr-1"></i>Registrar asistente</a>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl usuarios-item" data-usuario-item="mascotas pacientes animales registrar ficha">
                                    <div class="usuarios-modulo h-100">
                                        <img src="{{ asset('images/iconos/pacientes.svg') }}" alt="Mascotas">
                                        <h6>Mascotas</h6>
                                        <p>Buscar mascotas y administrar sus fichas.</p>
                                        <a class="btn btn-info btn-sm btn-block" href="{{ ROUTE('adm_cm.pacientes') }}"><i class="feather icon-search mr-1"></i>Buscar mascotas</a>
                                        <a class="btn btn-outline-info btn-sm btn-block" href="{{ ROUTE('adm_cm.pacientes') }}"><i class="feather icon-plus-circle mr-1"></i>Inscribir mascota</a>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl usuarios-item" data-usuario-item="tutores responsables propietarios clientes registrar">
                                    <div class="usuarios-modulo h-100">
                                        <img src="{{ asset('images/iconos/pacientes.svg') }}" alt="Tutores">
                                        <h6>Tutores</h6>
                                        <p>Buscar responsables y vincular mascotas.</p>
                                        <a class="btn btn-info btn-sm btn-block" href="{{ ROUTE('adm_cm.pacientes') }}"><i class="feather icon-search mr-1"></i>Buscar tutores</a>
                                        <a class="btn btn-outline-info btn-sm btn-block" href="{{ ROUTE('adm_cm.pacientes') }}"><i class="feather icon-user-plus mr-1"></i>Inscribir tutor</a>
                                    </div>
                                </div>
                                <div class="col-12 d-none" id="usuarios-sin-resultados">
                                    <div class="alert alert-light text-center mb-0">No se encontraron opciones de usuario.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">



                    <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.area_comercial') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-50 text-center" src="{{ asset('images/iconos/adm_comercial.png') }}">

                                <h6 class="mt-2 mb-0">Administración comercial</h6>

                            </div>

                        </a>

                    </div>



                </div>

               {{-- <div class="col">

                     <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.mis_profesionales') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-45 text-center" src="{{ asset('images/iconos/agenda.svg') }}">

                                <h6 class="mt-2 mb-0">Info profesionales del CM</h6>

                            </div>

                        </a>

                    </div>

                </div>--}}



            </div>

               

           

                

            <div class="row">

                <div class="col-md-12">

                    <div class="card subir py-auto bg-warning">

                        <div class="card-body text-center" style="cursor:pointer">

                            <h6 class="mb-0 text-white f-20">Áreas del Centro Médico</h6>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.laboratorio') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/laboratorio.svg') }}">

                                <h6 class="mt-2 mb-0">Laboratorio</h6>

                            </div>

                        </a>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.examenes') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/imagenologia.svg') }}">

                                <h6 class="mt-2 mb-0">Imagenología</h6>

                            </div>

                        </a>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.vacunatorio') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/vacunatorio.svg') }}">

                                <h6 class="mt-2 mb-0">Vacunatorio</h6>

                            </div>

                        </a>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card subir py-auto">

                        <a href="{{ ROUTE('adm_cm.dental') }}">

                            <div class="card-body text-center" style="cursor:pointer">

                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/dental.png') }}">

                                <h6 class="mt-2 mb-0">Dental</h6>

                            </div>

                        </a>

                    </div>

                </div>

				<div class="col-md-12">

					<div class="card subir py-auto" onclick="en_construccion()";>

						<a href="#">

							<div class="card-body text-center" style="cursor:pointer">

								<img class="wid-50 text-center rounded" src="{{ asset('images/iconos/mis_asistentes.svg') }}">

								<h6 class="mt-1">Contratar asistentes en linea</h6>

							</div>

						</a>

					</div>

				</div>

            </div>

        </div>

    </div>

    <!--Cierre: Container Completo-->

    @include('app.adm_cm.modales.en_construccion')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buscador = document.getElementById('buscar-modulo-usuario');
            const items = Array.from(document.querySelectorAll('[data-usuario-item]'));
            const sinResultados = document.getElementById('usuarios-sin-resultados');

            if (!buscador) {
                return;
            }

            buscador.addEventListener('input', function () {
                const termino = this.value.toLocaleLowerCase('es').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                let visibles = 0;

                items.forEach(function (item) {
                    const contenido = item.dataset.usuarioItem.toLocaleLowerCase('es').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    const coincide = contenido.includes(termino);
                    item.classList.toggle('d-none', !coincide);
                    visibles += coincide ? 1 : 0;
                });

                sinResultados.classList.toggle('d-none', visibles !== 0);
            });
        });
    </script>

@endsection
