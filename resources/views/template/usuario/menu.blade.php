<nav class="pcoded-navbar menu-light menu-lateral navbar-collapsed">
	<div class="navbar-wrapper">
		<div class="navbar-content scroll-div">
			<div class="">
				@php($pacienteMenu = \App\Models\Paciente::where('id_usuario', Auth::id())->first())
				@php($fotoPacienteMenu = !empty($pacienteMenu) && !empty($pacienteMenu->foto_perfil)
					? (\Illuminate\Support\Str::startsWith($pacienteMenu->foto_perfil, ['http://', 'https://', '/']) ? $pacienteMenu->foto_perfil : asset('storage/' . $pacienteMenu->foto_perfil))
					: asset('images/iconos/usuario.svg'))
				<div class="main-menu-header">
					<img class="img-radius" id="patient-menu-image" src="{{ $fotoPacienteMenu }}" alt="Imagen">
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
				<li class="nav-item pcoded-menu-caption"></li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link">
						<span class="pcoded-micon">
							<i class="feather icon-home"></i>
						</span>
						<span class="pcoded-mtext">Mi Escritorio vet</span>
					</a>
					<ul class="pcoded-submenu">
						<li><a href="{{ route('paciente.home') }}"><i class="feather icon-grid"></i>Inicio</a></li>
						<li><a href="{{ route('paciente.agendar_hora') }}"><i class="feather icon-calendar"></i>Buscar cita veterinaria o servicio</a></li>
                        <li><a href="{{ route('paciente.mascotas.index') }}"><i class="feather icon-heart"></i>Mis mascotas</a></li>
                        <li><a href="{{ route('paciente.mascotas.suscripcion_servicios') }}"><i class="feather icon-map-pin"></i>Servicios cercanos</a></li>
                        <li><a href="{{ route('paciente.integraciones.alimentos') }}" target="_blank" rel="noopener noreferrer"><i class="feather icon-shopping-cart"></i>Alimentos</a></li>
                        <li><a href="{{ route('paciente.integraciones.farmacia') }}" target="_blank" rel="noopener noreferrer"><i class="feather icon-plus-square"></i>Farmacia</a></li>
						<li><a href="{{ route('paciente.mascotas.promociones_generales') }}"><i class="feather icon-tag"></i>Promociones y beneficios</a></li>
					</ul>
				</li>
				<li class="nav-item pcoded-menu-caption">
					<label>Cuenta y ayuda</label>
				</li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link">
						<span class="pcoded-micon">
							<i class="feather icon-settings"></i>
						</span>
						<span class="pcoded-mtext">Configuraciones</span></a>
					<ul class="pcoded-submenu">
						<li><a href="{{ ROUTE('paciente.perfil') }}"><i class="feather icon-user"></i>Editar Perfil</a></li>
						{{--  <li><a href="{{ ROUTE('paciente.rompeclave') }}">Rompeclave</a></li>  --}}
						<li><a href="{{ ROUTE('paciente.mascotas.pagos_suscripcion') }}"><i class="feather icon-credit-card"></i>Suscripciones y facturación</a></li>
					</ul>
				</li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link" aria-label="Abrir tutoriales">
						<span class="pcoded-micon">
							<i class="feather icon-video"></i>
						</span>
						<span class="pcoded-mtext">Tutoriales</span>
					</a>
					<ul class="pcoded-submenu">
						<li><a href="https://vimeo.com/1099846864" target="_blank" rel="noopener noreferrer"><i class="feather icon-play-circle"></i>Ingreso a VET SDI</a></li>
						<li><a href="https://vimeo.com/1085553533" target="_blank" rel="noopener noreferrer"><i class="feather icon-play-circle"></i>Agenda y reserva de horas</a></li>
						<li><a href="https://vimeo.com/1085535164" target="_blank" rel="noopener noreferrer"><i class="feather icon-play-circle"></i>Receta en línea</a></li>
						<li><a href="https://vimeo.com/1085574920" target="_blank" rel="noopener noreferrer"><i class="feather icon-play-circle"></i>Ficha de atención</a></li>
						<li><a href="https://vimeo.com/1085548556" target="_blank" rel="noopener noreferrer"><i class="feather icon-play-circle"></i>Descargar la App SDI</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
