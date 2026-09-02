@if (isset($id_ficha_atencion) && !empty($id_ficha_atencion))
    <nav class="pcoded-navbar menu-light navbar-collapsed">
        <div class="navbar-wrapper">
            <div class="navbar-content scroll-div">
                <div class="">
                    <div class="main-menu-header">
                        <img class="img-radius" src="{{ asset('images/iconos/usuario_profesional.svg') }}"
                            alt="Profesional">
                        <div class="user-details">
                            <div id="more-details">{{ @Auth::user()->name }} <i class="fa fa-caret-down"></i></div>
                        </div>
                    </div>
                    <div id="nav-user-link">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <a href="{{ route('profesional.mi_perfil') }}" data-toggle="tooltip" title="Mi perfil">
                                    <i class="feather icon-user"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <form action="{{ ROUTE('logout') }}" method="post" id="closeSession">
                                    @csrf
                                    <a data-toggle="tooltip" title="Cerrar sesión" class="text-danger"
                                        href="javascript:{}" onclick="document.getElementById('closeSession').submit();">
                                        <i class="feather icon-power"></i>
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <ul class="nav pcoded-inner-navbar ">
                    <li class="nav-item pcoded-menu-caption text-center">
                        <!--<label>Menú</label>-->
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span class="pcoded-mtext text-center">Mi
                                Escritorio</span></a>
                        <ul class="pcoded-submenu">
                            {{-- <li><a href="{{ route('profesional.home') }}">Mi Escritorio Profesional</a></li> --}}
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.home') }}','Mi Escritorio Profesional');">Mi Escritorio Profesional</a></li>
                            {{-- <li><a href="{{ route('profesional.pacientes') }}">Mis pacientes</a></li> --}}
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.pacientes') }}','Mis pacientes');">Mis pacientes</a></li>
                            {{-- <li><a href="{{ route('profesional.configuracion') }}"> Panel de Configuración</a></li> --}}
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.configuracion') }}',' Panel de Configuración');"> Panel de Configuración</a></li>
                            {{-- <li><a href="{{ route('profesional.index_receta_online') }}">Receta Online</a></li>      --}}
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.index_receta_online') }}','Receta Online');">Receta Online</a></li>
                            {{-- <li><a href="{{ route('profesional.flujo_caja') }}">Flujo de Caja</a></li> --}}
                            <li><a onclick="menuValidarSalidaFicha('{{ route('profesional.flujo_caja') }}','Flujo de Caja');">Flujo de Caja</a></li>
                            <!--<li><a href="suscripcion.php">Pagos y Suscripción</a></li>-->
                        </ul>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i  class="feather icon-settings"></i></span><span class="pcoded-mtext text-center">Configuraciones</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="{{ route('profesional.mi_perfil') }}">Editar Perfil</a></li>
                            <!--<li><a href="suscripcion.php">Pagos y Suscripción</a></li>-->
                        </ul>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i  class="feather icon-settings"></i></span><span class="pcoded-mtext text-center">Configuraciones</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="javascript:void(0)" onclick="abrirTutorialSdi('agenda')">Abrir Academia VET SDI</a></li>
                            <!--<li><a href="suscripcion.php">Pagos y Suscripción</a></li>-->
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@else
    <nav class="pcoded-navbar menu-light navbar-collapsed">
        <div class="navbar-wrapper">
            <div class="navbar-content scroll-div">
                <div class="">
                    <div class="main-menu-header">
                        <img class="img-radius" src="{{ asset('images/iconos/usuario_profesional.svg') }}"
                            alt="Profesional">
                        <div class="user-details">
                            <div id="more-details">{{ @Auth::user()->name }} <i class="fa fa-caret-down"></i></div>
                        </div>
                    </div>
                    <div id="nav-user-link">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                <a href="{{ route('profesional.mi_perfil') }}" data-toggle="tooltip" title="Mi perfil">
                                    <i class="feather icon-user"></i>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <form action="{{ ROUTE('logout') }}" method="post" id="closeSession">
                                    @csrf
                                    <a data-toggle="tooltip" title="Cerrar sesión" class="text-danger"
                                        href="javascript:{}" onclick="document.getElementById('closeSession').submit();">
                                        <i class="feather icon-power"></i>
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <ul class="nav pcoded-inner-navbar ">
                    <li class="nav-item pcoded-menu-caption text-center">
                        <!--<label>Menú</label>-->
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span class="pcoded-mtext text-center">Mi
                                Escritorio</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="{{ route('profesional.home') }}">Mi Escritorio Profesional</a></li>
                            <li><a href="{{ route('profesional.pacientes') }}">Mis pacientes</a></li>
                            <li><a href="{{ route('profesional.configuracion') }}"> Panel de Configuración</a></li>
                            <li><a href="{{ route('profesional.index_receta_online') }}">Receta Online</a></li>
                            <li><a href="{{ route('profesional.flujo_caja') }}">Flujo de Caja</a></li>
                            <!--<li><a href="suscripcion.php">Pagos y Suscripción</a></li>-->
                        </ul>
                    </li>
                    <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i  class="feather icon-settings"></i></span><span class="pcoded-mtext text-center">Configuraciones</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="{{ route('profesional.mi_perfil') }}">Editar Perfil</a></li>
                            <!--<li><a href="suscripcion.php">Pagos y Suscripción</a></li>-->
                        </ul>
                    </li>
                     <li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i  class="feather icon-video"></i></span><span class="pcoded-mtext text-center">Tutoriales</span></a>
                        <ul class="pcoded-submenu">
                            <li><a href="javascript:void(0)" onclick="abrirTutorialSdi('agenda')">Funciones Agenda</a></li>
                            <li><a href="javascript:void(0)" onclick="abrirTutorialSdi('receta')">Receta online</a></li>
                            <li><a href="javascript:void(0)" onclick="abrirTutorialSdi('ficha')">Funciones Ficha de atención</a></li>
                            <li><a href="javascript:void(0)" onclick="abrirTutorialSdi('pacientes')">Funciones Mis pacientes</a></li>
                            <li><a href="javascript:void(0)" onclick="abrirTutorialSdi('app')">Descarga la App SDI</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endif

<style>
    .tutorial-sdi-modal .modal-dialog {
        max-width: 1120px;
    }
    .tutorial-sdi-modal .modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(18, 38, 63, .28);
    }
    .tutorial-sdi-header {
        border: 0;
        padding: 1.1rem 1.4rem;
        background: linear-gradient(135deg, #12786f, #16b8b8);
    }
    .tutorial-sdi-kicker {
        display: block;
        margin-top: .2rem;
        color: rgba(255, 255, 255, .82);
        font-size: .8rem;
    }
    .tutorial-sdi-body {
        padding: 0;
        background: #f3f7fb;
    }
    .tutorial-sdi-layout {
        display: grid;
        grid-template-columns: 290px minmax(0, 1fr);
        min-height: 570px;
    }
    .tutorial-sdi-list {
        padding: 1rem;
        background: #fff;
        border-right: 1px solid #e5eaf0;
    }
    .tutorial-sdi-search {
        position: relative;
        margin-bottom: .85rem;
    }
    .tutorial-sdi-search i {
        position: absolute;
        top: 50%;
        left: .8rem;
        transform: translateY(-50%);
        color: #7b8794;
    }
    .tutorial-sdi-search input {
        height: 40px;
        padding-left: 2.25rem;
        border: 1px solid #dce3ea;
        border-radius: 10px;
    }
    .tutorial-sdi-item {
        display: flex;
        width: 100%;
        align-items: center;
        padding: .8rem;
        margin-bottom: .55rem;
        border: 1px solid transparent;
        border-radius: 12px;
        background: #f6f8fb;
        color: #344563;
        text-align: left;
        transition: .18s ease;
    }
    .tutorial-sdi-item:hover,
    .tutorial-sdi-item.active {
        border-color: #13b8b2;
        background: #e9fbfa;
        color: #107c78;
        transform: translateX(2px);
    }
    .tutorial-sdi-item-icon {
        display: inline-flex;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        align-items: center;
        justify-content: center;
        margin-right: .7rem;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 3px 9px rgba(25, 50, 75, .08);
        font-size: 1.05rem;
    }
    .tutorial-sdi-item strong,
    .tutorial-sdi-item small {
        display: block;
    }
    .tutorial-sdi-item small {
        margin-top: .15rem;
        color: #8492a6;
    }
    .tutorial-sdi-content {
        min-width: 0;
        padding: 1.25rem;
    }
    .tutorial-sdi-player {
        position: relative;
        overflow: hidden;
        width: 100%;
        padding-top: 56.25%;
        border-radius: 15px;
        background: #102a35;
        box-shadow: 0 12px 30px rgba(18, 38, 63, .18);
    }
    .tutorial-sdi-player iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
    .tutorial-sdi-copy {
        padding: 1rem .25rem 0;
    }
    .tutorial-sdi-copy h4 {
        margin-bottom: .35rem;
        color: #263a55;
        font-weight: 700;
    }
    .tutorial-sdi-copy p {
        margin-bottom: 0;
        color: #65758b;
    }
    @media (max-width: 767px) {
        .tutorial-sdi-layout {
            grid-template-columns: 1fr;
        }
        .tutorial-sdi-list {
            display: flex;
            overflow-x: auto;
            border-right: 0;
            border-bottom: 1px solid #e5eaf0;
        }
        .tutorial-sdi-search {
            display: none;
        }
        .tutorial-sdi-item {
            min-width: 220px;
            margin-right: .55rem;
            margin-bottom: 0;
        }
        .tutorial-sdi-content {
            padding: .85rem;
        }
    }
</style>

<div class="modal fade tutorial-sdi-modal" id="tutorialSdiModal" tabindex="-1" role="dialog"
    aria-labelledby="tutorialSdiTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header tutorial-sdi-header">
                <div>
                    <h5 class="modal-title text-white mb-0" id="tutorialSdiTitulo">
                        <i class="feather icon-play-circle mr-2"></i>Academia VET SDI
                    </h5>
                    <span class="tutorial-sdi-kicker">Aprende cada función con videos breves y prácticos</span>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal"
                    onclick="detenerTutorialSdi()" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body tutorial-sdi-body">
                <div class="tutorial-sdi-layout">
                    <aside class="tutorial-sdi-list">
                        <div class="tutorial-sdi-search">
                            <i class="feather icon-search"></i>
                            <input type="search" class="form-control" id="tutorialSdiBuscar"
                                placeholder="Buscar tutorial..." oninput="filtrarTutorialesSdi(this.value)">
                        </div>
                        <button class="tutorial-sdi-item" type="button" data-tutorial="agenda" onclick="seleccionarTutorialSdi('agenda')">
                            <span class="tutorial-sdi-item-icon"><i class="feather icon-calendar"></i></span>
                            <span><strong>Agenda veterinaria</strong><small>Horas, bloqueos y atención</small></span>
                        </button>
                        <button class="tutorial-sdi-item" type="button" data-tutorial="receta" onclick="seleccionarTutorialSdi('receta')">
                            <span class="tutorial-sdi-item-icon"><i class="feather icon-file-text"></i></span>
                            <span><strong>Receta online</strong><small>Indicar, firmar y validar</small></span>
                        </button>
                        <button class="tutorial-sdi-item" type="button" data-tutorial="ficha" onclick="seleccionarTutorialSdi('ficha')">
                            <span class="tutorial-sdi-item-icon"><i class="feather icon-clipboard"></i></span>
                            <span><strong>Ficha de atención</strong><small>Consulta veterinaria completa</small></span>
                        </button>
                        <button class="tutorial-sdi-item" type="button" data-tutorial="pacientes" onclick="seleccionarTutorialSdi('pacientes')">
                            <span class="tutorial-sdi-item-icon"><i class="feather icon-users"></i></span>
                            <span><strong>Mis pacientes</strong><small>Tutores, mascotas e historial</small></span>
                        </button>
                        <button class="tutorial-sdi-item" type="button" data-tutorial="app" onclick="seleccionarTutorialSdi('app')">
                            <span class="tutorial-sdi-item-icon"><i class="feather icon-smartphone"></i></span>
                            <span><strong>App VET SDI</strong><small>Descarga y primeros pasos</small></span>
                        </button>
                    </aside>
                    <section class="tutorial-sdi-content">
                        <div class="tutorial-sdi-player">
                            <iframe id="tutorialSdiIframe" src="" allow="autoplay; fullscreen; picture-in-picture"
                                allowfullscreen title="Tutorial VET SDI"></iframe>
                        </div>
                        <div class="tutorial-sdi-copy">
                            <h4 id="tutorialSdiNombre">Tutorial VET SDI</h4>
                            <p id="tutorialSdiDescripcion"></p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmLogoutModal" tabindex="-1" role="dialog" aria-labelledby="confirmLogoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmLogoutModalLabel">Confirmar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="menu_url_destino" id="menu_url_destino" value="">
                <input type="hidden" name="menu_nombre_destino" id="menu_nombre_destino" value="">
                <p>Esta por salir de la Ficha de Atención, los datos serán eliminados.</p>
                <p>¿Esta seguro que desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="feather icon-x"></i>Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="menuContinuar();"><i class="feather icon-check"></i>Continuar</button>
            </div>
        </div>
    </div>
</div>

<script>
    var tutorialesSdi = {
        agenda: {
            id: '1085553533',
            nombre: 'Agenda veterinaria',
            descripcion: 'Configura tu disponibilidad, revisa las reservas y comienza una atención desde la agenda.'
        },
        receta: {
            id: '1085535164',
            nombre: 'Receta veterinaria online',
            descripcion: 'Crea recetas, agrega medicamentos, aplica la firma digital SDI y genera el documento verificable.'
        },
        ficha: {
            id: '1085574920',
            nombre: 'Ficha veterinaria de atención',
            descripcion: 'Registra anamnesis, examen físico, diagnóstico, indicaciones, vacunas, exámenes y antecedentes.'
        },
        pacientes: {
            id: '1085574269',
            nombre: 'Tutores, mascotas y pacientes',
            descripcion: 'Busca tutores y mascotas, consulta su historial y administra la información veterinaria.'
        },
        app: {
            id: '1085548556',
            nombre: 'Aplicación VET SDI',
            descripcion: 'Descarga la aplicación, inicia sesión y conoce sus funciones principales.'
        }
    };

    function abrirTutorialSdi(clave) {
        seleccionarTutorialSdi(clave || 'agenda');
        $('#tutorialSdiModal').modal('show');
    }

    function detenerTutorialSdi() {
        var iframe = document.getElementById('tutorialSdiIframe');
        if (!iframe) return;

        if (iframe.contentWindow) {
            iframe.contentWindow.postMessage(JSON.stringify({method: 'pause'}), 'https://player.vimeo.com');
        }
        iframe.removeAttribute('src');
        iframe.setAttribute('src', 'about:blank');
    }

    function seleccionarTutorialSdi(clave) {
        var tutorial = tutorialesSdi[clave] || tutorialesSdi.agenda;
        var iframe = document.getElementById('tutorialSdiIframe');
        var nuevaUrl = 'https://player.vimeo.com/video/' + tutorial.id +
            '?title=0&byline=0&portrait=0&badge=0&pip=0&dnt=1&autoplay=1';

        if (iframe.getAttribute('src') !== nuevaUrl) {
            iframe.setAttribute('src', nuevaUrl);
        }

        document.getElementById('tutorialSdiNombre').textContent = tutorial.nombre;
        document.getElementById('tutorialSdiDescripcion').textContent = tutorial.descripcion;

        document.querySelectorAll('.tutorial-sdi-item').forEach(function (boton) {
            boton.classList.toggle('active', boton.getAttribute('data-tutorial') === clave);
        });
    }

    function filtrarTutorialesSdi(texto) {
        var busqueda = (texto || '').toLowerCase().trim();
        document.querySelectorAll('.tutorial-sdi-item').forEach(function (boton) {
            boton.style.display = boton.textContent.toLowerCase().indexOf(busqueda) !== -1 ? '' : 'none';
        });
    }

    $(document).on('hide.bs.modal hidden.bs.modal', '#tutorialSdiModal', function () {
        detenerTutorialSdi();
        document.getElementById('tutorialSdiBuscar').value = '';
        filtrarTutorialesSdi('');
    });

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) detenerTutorialSdi();
    });
    window.addEventListener('pagehide', detenerTutorialSdi);
    window.addEventListener('beforeunload', detenerTutorialSdi);

    function menuValidarSalidaFicha(url, nombre)
    {
        $('#confirmLogoutModal').modal('show');
        $('#menu_url_destino').val(url);
        $('#menu_nombre_destino').val(nombre);
    }

    function menuContinuar()
    {
        var temp = $('#menu_url_destino').val();
        $('#menu_url_destino').val('');
        $('#menu_nombre_destino').val('');
        window.location.href = temp;
    }
</script>
