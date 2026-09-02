<nav class="pcoded-navbar menu-light">
	<div class="navbar-wrapper">
		<div class="navbar-content scroll-div">
			<div class="">
				@php($pacienteMenu = \App\Models\Paciente::where('id_usuario', Auth::id())->first())
				@php($fotoPacienteMenu = !empty($pacienteMenu) && !empty($pacienteMenu->foto_perfil)
					? (\Illuminate\Support\Str::startsWith($pacienteMenu->foto_perfil, ['http://', 'https://', '/']) ? $pacienteMenu->foto_perfil : asset('storage/' . $pacienteMenu->foto_perfil))
					: asset('images/iconos/usuario.svg'))
				<div class="main-menu-header">
					<img class="img-radius" id="patient-menu-image" src="{{ $fotoPacienteMenu }}" alt="Imagen" style="width: 40px; height: 40px; object-fit: cover;">
					<div class="user-details">
						<div id="more-details">{{ @Auth::user()->name }}<i class="fa fa-caret-down"></i></div>
					</div>
				</div>
				<div id="nav-user-link">
					<ul class="list-inline">
						<li class="list-inline-item">
							<a href="{{ ROUTE('paciente.perfil') }}" data-toggle="tooltip" title="Mi perfil">
								<i class="feather icon-user"></i>
							</a>
						</li>
						<li class="list-inline-item">
							<form id="close" action="{{ ROUTE('logout') }}" method="POST">
								@csrf
								<a  href="javascript:{}" onclick="document.getElementById('close').submit();" data-toggle="tooltip" title="Cerrar sesión" class="text-danger" >
									<i class="feather icon-power"></i>
								</a>
							</form>
						</li>
					</ul>
				</div>
			</div>
			<ul class="nav pcoded-inner-navbar ">
				<li class="nav-item pcoded-menu-caption text-center">
				</li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link">
						<span class="pcoded-micon">
							<i class="feather icon-home"></i>
						</span>
						<span class="pcoded-mtext text-center">Mi Escritorio vet</span>
					</a>
					<ul class="pcoded-submenu">
						<li><a href="{{ route('paciente.home') }}">Inicio</a></li>
						<li><a href="{{ route('paciente.agendar_hora') }}">Buscar cita veterinaria o servicio</a></li>
                        <li><a href="{{ route('paciente.mascotas.index') }}">Mis mascotas</a></li>
                        <li><a href="{{ route('paciente.mascotas.suscripcion_servicios') }}">Servicios cercanos</a></li>
                        <li><a href="{{ route('paciente.integraciones.alimentos') }}" target="_blank" rel="noopener noreferrer">Alimentos</a></li>
                        <li><a href="{{ route('paciente.integraciones.farmacia') }}" target="_blank" rel="noopener noreferrer">Farmacia</a></li>
						<li><a href="{{ route('paciente.mascotas.promociones_generales') }}">Promociones y beneficios</a></li>
					</ul>
				</li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link">
						<span class="pcoded-micon">
							<i class="feather icon-settings"></i>
						</span>
						<span class="pcoded-mtext text-center">Configuraciones</span></a>
					<ul class="pcoded-submenu">
						<li><a href="{{ ROUTE('paciente.perfil') }}">Editar Perfil</a></li>
						{{--  <li><a href="{{ ROUTE('paciente.rompeclave') }}">Rompeclave</a></li>  --}}
						<li><a href="{{ ROUTE('paciente.mascotas.pagos_suscripcion') }}">Suscripciones y facturacion</a></li>
					</ul>
				</li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link" aria-label="Abrir tutoriales">
						<span class="pcoded-micon">
							<i class="feather icon-video"></i>
						</span>
						<span class="pcoded-mtext text-center">Tutoriales</span>
					</a>
					<ul class="pcoded-submenu">
						<li><a href="https://vimeo.com/1099846864" target="_blank" rel="noopener noreferrer">Ingreso a VET SDI</a></li>
						<li><a href="https://vimeo.com/1085553533" target="_blank" rel="noopener noreferrer">Agenda y reserva de horas</a></li>
						<li><a href="https://vimeo.com/1085535164" target="_blank" rel="noopener noreferrer">Receta en línea</a></li>
						<li><a href="https://vimeo.com/1085574920" target="_blank" rel="noopener noreferrer">Ficha de atención</a></li>
						<li><a href="https://vimeo.com/1085548556" target="_blank" rel="noopener noreferrer">Descargar la App SDI</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
