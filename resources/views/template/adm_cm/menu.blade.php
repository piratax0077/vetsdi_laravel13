<nav class="pcoded-navbar menu-light menu-lateral navbar-collapsed">
	<div class="navbar-wrapper">
		<div class="navbar-content scroll-div">
			<div class="">
				<div class="main-menu-header">
					<img class="img-radius" src="{{ asset('images/iconos/cm-perfiles.png') }}" alt="Administrador centro médico">
					<div class="user-details">
						<div id="more-details">{{ $institucion->nombre ?? Auth::user()->name }} <i class="fa fa-caret-down"></i></div>
					</div>
				</div>
				<div id="nav-user-link">
					<ul class="list-inline">
						<li class="list-inline-item">
							<a href="{{ route('adm_cm.perfil_cm') }}" data-toggle="tooltip" title="Mi Perfil Centro Médico">
								<i class="feather icon-user"></i>
							</a>
						</li>
						<li class="list-inline-item"><a href="cerrar.php" data-toggle="tooltip" title="Cerrar sesión" class="text-danger"><i class="feather icon-power"></i></a></li>
					</ul>
				</div>
			</div>
			<ul class="nav pcoded-inner-navbar ">                <li class="nav-item pcoded-menu-caption"></li>                <li class="nav-item pcoded-hasmenu">                    <a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-shopping-cart"></i></span><span class="pcoded-mtext">Ventas · Veterfood</span></a>                    <ul class="pcoded-submenu">                        @foreach (['alimentos' => ['Alimentos', 'icon-package'], 'farmacia' => ['Farmacia', 'icon-plus-square'], 'cuidados' => ['Accesorios', 'icon-tag']] as $rubro => [$nombreRubro, $iconoRubro])                            <li><a href="{{ rtrim(config('services.veterfood.url'), '/') }}/central-ventas/rubros/{{ $rubro }}" target="_blank" rel="noopener noreferrer"><i class="feather {{ $iconoRubro }}"></i>{{ $nombreRubro }}</a></li>                        @endforeach                    </ul>                </li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link"><span class="pcoded-micon"><i class="feather icon-home"></i></span><span class="pcoded-mtext">Mi Escritorio</span></a>
					<ul class="pcoded-submenu">
						<li>
							<a href="{{ ROUTE('adm_cm.home') }}"><i class="feather icon-grid"></i>Mi Escritorio</a>
						</li>
						{{-- Gestión médica oculta por ahora
						<li>
							<a href="{{ ROUTE('adm_cm.adm_medico') }}">Administrador Médico</a>
						</li>
						--}}<!--
						<li>
							<a href="admin_cm.php">Administradores</a>
						</li>
						<li>
							<a href="sucursales_cm.php">Sucursales Centro Médico</a>
						</li>-->
						<li>
							<a href="{{ ROUTE('app.adm_cm.liquidacion_profesionales') }}"><i class="feather icon-file-text"></i>Liquidación Profesionales</a>
						</li>
						<li>
							<a href="{{ ROUTE('adm_cm.profesionales') }}"><i class="feather icon-users"></i>Profesionales</a>
						</li>
						<li>
							<a href="{{ ROUTE('adm_cm.personal') }}"><i class="feather icon-briefcase"></i>Personal</a>
						</li>
						<li>
							<a href="{{ ROUTE('adm_cm.pacientes') }}"><i class="feather icon-heart"></i>Pacientes</a>
						</li>
						<li>
							<a href="{{ ROUTE('adm_cm.administracion_cm') }}"><i class="feather icon-trending-up"></i>Admininistración Comercial</a>
						</li>
						<li>
							<a href="{{ ROUTE('adm_cm.laboratorio') }}"><i class="feather icon-droplet"></i>Laboratorio</a>
						</li><!--
						<li>
							<a href="suscripcion_estadisticas_cm.php">Estadísticas</a>
						</li><!--
						<li>
							<a href="suscripcion_soporte.php">Soporte</a>
						</li>
						<li>
							<a href="{{ ROUTE('adm_cm.configuracion') }}">Perfil Centro Médico</a>
						</li>-->

						<li>
							<a href="{{ ROUTE('adm_cm.pagos') }}"><i class="feather icon-credit-card"></i>Suscripciones y pagos</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
