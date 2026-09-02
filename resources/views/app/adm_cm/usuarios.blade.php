@extends('template.adm_cm.template')

@section('content')
<style>
    .usuarios-panel .nav-link { border-radius: .35rem; margin: .2rem; }
    .usuarios-panel .nav-link.active { background: #17a2b8; color: #fff; }
    .usuarios-panel .usuario-vista { min-height: 230px; }
    .usuarios-panel .usuario-icono { width: 58px; height: 58px; object-fit: contain; }
    .usuarios-panel .accion-usuario { min-width: 180px; }
    .usuarios-roles {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .usuarios-rol {
        border: 1px solid #e3e8ef;
        border-radius: .5rem;
        padding: 1rem;
        background: #f8fafc;
    }
    .usuarios-rol h6 { font-weight: 700; margin-bottom: .2rem; }
    .usuarios-rol small { display: block; min-height: 34px; color: #6c757d; }
    .usuarios-rol .nav { margin-top: .65rem; }
    @media (max-width: 991.98px) {
        .usuarios-roles { grid-template-columns: 1fr; }
        .usuarios-rol small { min-height: 0; }
    }
</style>

<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10 font-weight-bold">Usuarios del Centro M&eacute;dico</h5>
                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('adm_cm.home') }}"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item">Usuarios</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card usuarios-panel">
            <div class="card-header bg-info text-white">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h4 class="text-white mb-1"><i class="feather icon-users mr-2"></i>Usuarios</h4>
                        <span>Busca, inscribe y administra todos los usuarios del centro desde un solo lugar.</span>
                    </div>
                    <div class="col-md-5 mt-3 mt-md-0">
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="feather icon-search"></i></span></div>
                            <input id="buscar-vista-usuario" type="search" class="form-control" placeholder="Buscar profesionales, mascotas, tutores...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="usuarios-roles" id="usuarios-tabs" role="tablist">
                    <section class="usuarios-rol" data-usuario-grupo>
                        <h6><i class="feather icon-activity text-info mr-1"></i>Equipo cl&iacute;nico</h6>
                        <small>Usuarios que realizan la atenci&oacute;n veterinaria.</small>
                        <ul class="nav nav-pills">
                            <li class="nav-item" data-usuario-tab="equipo clinico profesionales medicos veterinarios especialistas"><a class="nav-link" data-usuario-vista href="#usuarios-profesionales"><i class="feather icon-user-check mr-1"></i>Profesionales</a></li>
                        </ul>
                    </section>
                    <section class="usuarios-rol" data-usuario-grupo>
                        <h6><i class="feather icon-briefcase text-info mr-1"></i>Administraci&oacute;n y RR. HH.</h6>
                        <small>Contrataci&oacute;n, incorporaciones y personal asistente.</small>
                        <ul class="nav nav-pills">
                            <li class="nav-item" data-usuario-tab="administracion rrhh contratos incorporaciones"><a class="nav-link" data-usuario-vista href="#usuarios-contratos"><i class="feather icon-file-text mr-1"></i>Contratos</a></li>
                            <li class="nav-item" data-usuario-tab="administracion rrhh asistentes empleados personal"><a class="nav-link" data-usuario-vista href="#usuarios-asistentes"><i class="feather icon-users mr-1"></i>Asistentes</a></li>
                        </ul>
                    </section>
                    <section class="usuarios-rol" data-usuario-grupo>
                        <h6><i class="feather icon-heart text-info mr-1"></i>Pacientes y responsables</h6>
                        <small>Mascotas atendidas y las personas responsables.</small>
                        <ul class="nav nav-pills">
                            <li class="nav-item" data-usuario-tab="pacientes mascotas animales"><a class="nav-link" data-usuario-vista href="#usuarios-mascotas"><i class="feather icon-heart mr-1"></i>Mascotas</a></li>
                            <li class="nav-item" data-usuario-tab="responsables tutores propietarios clientes"><a class="nav-link" data-usuario-vista href="#usuarios-tutores"><i class="feather icon-user mr-1"></i>Tutores</a></li>
                        </ul>
                    </section>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade show active usuario-vista" id="usuarios-inicio">
                        <div class="text-center py-5">
                            <i class="feather icon-users text-info" style="font-size: 3rem"></i>
                            <h5 class="mt-3">Selecciona un rol de usuario</h5>
                            <p class="text-muted mb-0">Elige Profesionales, Contratos, Asistentes, Mascotas o Tutores para abrir sus opciones.</p>
                        </div>
                    </div>
                    <div class="tab-pane fade usuario-vista" id="usuarios-profesionales">
                        <div class="text-center py-4">
                            <img class="usuario-icono mb-3" src="{{ asset('images/iconos/profesionales.svg') }}" alt="Profesionales">
                            <h5>Profesionales</h5><p class="text-muted">Consulta la n&oacute;mina, especialidades, datos y asociaciones.</p>
                            <a class="btn btn-info accion-usuario" href="{{ route('adm_cm.profesionales') }}"><i class="feather icon-search mr-1"></i>Buscar profesionales</a>
                            <a class="btn btn-outline-info accion-usuario" href="{{ route('adm_cm.profesionales') }}"><i class="feather icon-user-plus mr-1"></i>Inscribir o asociar</a>
                        </div>
                    </div>
                    <div class="tab-pane fade usuario-vista" id="usuarios-contratos">
                        <div class="text-center py-4">
                            <img class="usuario-icono mb-3" src="{{ asset('images/iconos/cotizacion.svg') }}" alt="Contratos">
                            <h5>Contratos e incorporaciones</h5><p class="text-muted">Administra contratos de profesionales y personal del centro.</p>
                            <a class="btn btn-info accion-usuario" href="{{ route('adm_cm.area_contratos_nuevos') }}"><i class="feather icon-search mr-1"></i>Buscar contratos</a>
                            <a class="btn btn-outline-info accion-usuario" href="{{ route('adm_cm.area_contratos_nuevos') }}"><i class="feather icon-file-plus mr-1"></i>Nueva inscripci&oacute;n</a>
                        </div>
                    </div>
                    <div class="tab-pane fade usuario-vista" id="usuarios-asistentes">
                        <div class="text-center py-4">
                            <img class="usuario-icono mb-3" src="{{ asset('images/iconos/mis_asistentes.svg') }}" alt="Asistentes">
                            <h5>Asistentes</h5><p class="text-muted">Busca, registra, asocia y administra los asistentes.</p>
                            <a class="btn btn-info accion-usuario" href="{{ route('adm_cm.personal') }}"><i class="feather icon-search mr-1"></i>Buscar asistentes</a>
                            <a class="btn btn-outline-info accion-usuario" href="{{ route('adm_cm.personal') }}"><i class="feather icon-user-plus mr-1"></i>Registrar asistente</a>
                        </div>
                    </div>
                    <div class="tab-pane fade usuario-vista" id="usuarios-mascotas">
                        <div class="text-center py-4">
                            <img class="usuario-icono mb-3" src="{{ asset('images/iconos/pacientes.svg') }}" alt="Mascotas">
                            <h5>Mascotas</h5><p class="text-muted">Busca pacientes veterinarios y administra sus fichas.</p>
                            <a class="btn btn-info accion-usuario" href="{{ route('adm_cm.pacientes') }}"><i class="feather icon-search mr-1"></i>Buscar mascotas</a>
                            <a class="btn btn-outline-info accion-usuario" href="{{ route('adm_cm.pacientes') }}"><i class="feather icon-plus-circle mr-1"></i>Inscribir mascota</a>
                        </div>
                    </div>
                    <div class="tab-pane fade usuario-vista" id="usuarios-tutores">
                        <div class="text-center py-4">
                            <img class="usuario-icono mb-3" src="{{ asset('images/iconos/pacientes.svg') }}" alt="Tutores">
                            <h5>Tutores</h5><p class="text-muted">Busca responsables y vincula sus mascotas.</p>
                            <a class="btn btn-info accion-usuario" href="{{ route('adm_cm.pacientes') }}"><i class="feather icon-search mr-1"></i>Buscar tutores</a>
                            <a class="btn btn-outline-info accion-usuario" href="{{ route('adm_cm.pacientes') }}"><i class="feather icon-user-plus mr-1"></i>Inscribir tutor</a>
                        </div>
                    </div>
                </div>
                <div id="usuarios-sin-coincidencia" class="alert alert-light text-center d-none">No se encontr&oacute; una vista para esa b&uacute;squeda.</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('buscar-vista-usuario');
    const tabs = Array.from(document.querySelectorAll('[data-usuario-tab]'));
    const enlaces = Array.from(document.querySelectorAll('[data-usuario-vista]'));
    const vistas = Array.from(document.querySelectorAll('.usuario-vista'));
    const grupos = Array.from(document.querySelectorAll('[data-usuario-grupo]'));
    const aviso = document.getElementById('usuarios-sin-coincidencia');

    function abrirVista(enlace) {
        const destino = document.querySelector(enlace.getAttribute('href'));
        if (!destino) return;
        enlaces.forEach(item => item.classList.remove('active'));
        vistas.forEach(vista => vista.classList.remove('show', 'active'));
        enlace.classList.add('active');
        destino.classList.add('show', 'active');
    }

    enlaces.forEach(function (enlace) {
        enlace.addEventListener('click', function (event) {
            event.preventDefault();
            abrirVista(this);
        });
    });

    input.addEventListener('input', function () {
        const valor = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        const coincidencias = tabs.filter(tab => tab.dataset.usuarioTab.includes(valor));
        tabs.forEach(tab => tab.classList.toggle('d-none', valor !== '' && !tab.dataset.usuarioTab.includes(valor)));
        grupos.forEach(grupo => grupo.classList.toggle('d-none', !grupo.querySelector('[data-usuario-tab]:not(.d-none)')));
        aviso.classList.toggle('d-none', coincidencias.length !== 0);
        if (valor !== '' && coincidencias.length) { abrirVista(coincidencias[0].querySelector('a')); }
    });
});
</script>
@endsection
