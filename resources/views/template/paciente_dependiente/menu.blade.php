<nav class="pcoded-navbar menu-light menu-lateral navbar-collapsed">
	<div class="navbar-wrapper">
		<div class="navbar-content scroll-div">
				@php
					$perfilMascota = $mascota ?? $paciente ?? null;
					$idDependienteActivo = $perfilMascota->id ?? $id_dependiente_activo ?? null;
					$nombreMascota = trim(($perfilMascota->nombre ?? $perfilMascota->nombres ?? '') . ' ' . ($perfilMascota->apellido_uno ?? ''));
					$nombreMascota = $nombreMascota !== '' ? $nombreMascota : (@Auth::user()->name ?? 'Mascota');

				$nombreResponsable = @Auth::user()->name ?? 'Sin responsable';
				if (!empty($responsable)) {
					$nombreResponsable = trim(($responsable->nombres ?? '') . ' ' . ($responsable->apellido_uno ?? '') . ' ' . ($responsable->apellido_dos ?? ''));
				}

				$fotoMascota = $perfilMascota->foto_url ?? asset('images/iconos/mascotas.svg');
			@endphp
			<div class="">
                <div class="main-menu-header">
					<img class="img-radius" src="{{ $fotoMascota }}" alt="Mascota">
					<div class="user-details">
						<div id="more-details">{{ $nombreMascota }}<i class="fa fa-caret-down"></i></div>
					</div>
				</div>
				<div id="nav-user-link">
					<ul class="list-inline">
						<li class="list-inline-item">
							{{-- <a href="{{ ROUTE('paciente.dependiente.perfil') }}" data-toggle="tooltip" title="Mi perfil"> --}}
								<i class="feather icon-user"></i>
							{{-- </a> --}}
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
						<span class="pcoded-mtext">Mi Escritorio</span>
					</a>
					<ul class="pcoded-submenu">
						<li><a href="{{ ROUTE('paciente.home') }}"><i class="feather icon-user"></i>Escritorio tutor</a></li>
						@if ($idDependienteActivo)
							<li><a href="{{ ROUTE('paciente.mascota.home', [$idDependienteActivo]) }}"><i class="feather icon-grid"></i>Mi Escritorio Mascota</a></li>
							<li><a href="{{ ROUTE('paciente.mascota.reservar_hora', [$idDependienteActivo,'0','0','0']) }}"><i class="feather icon-calendar"></i>Reservar Cita Veterinaria</a></li>
							<li><a href="{{ ROUTE('paciente.dependiente.mis_profesionales', [$idDependienteActivo]) }}"><i class="feather icon-users"></i>Mis Veterinarios</a></li>
							<li><a href="{{ ROUTE('paciente.dependiente.mi_ficha', [$idDependienteActivo]) }}"><i class="feather icon-clipboard"></i>Ficha Veterinaria Única</a></li>
							<li><a href="{{ ROUTE('paciente.dependiente.receta', [$idDependienteActivo]) }}"><i class="feather icon-file-text"></i>Documentos</a></li>
							<li><a href="{{ ROUTE('paciente.dependiente.receta.examen', [$idDependienteActivo]) }}"><i class="feather icon-activity"></i>Exámenes</a></li>
							<li><a href="{{ ROUTE('paciente.dependiente.receta.examen', [$idDependienteActivo]) }}"><i class="feather icon-check-square"></i>Controles</a></li>
							<li><a href="{{ ROUTE('registro_vacunas', ['id_dependiente_activo'=> $idDependienteActivo]) }}"><i class="feather icon-shield"></i>Registro Vacunas</a></li>
							<li><a href="{{ ROUTE('registro_desparasitacion', ['id_dependiente_activo'=> $idDependienteActivo]) }}"><i class="feather icon-droplet"></i>Registro Desparasitación</a></li>
						@endif
					</ul>
				</li>
				<li class="nav-item pcoded-menu-caption">
					<label>Cuenta</label>
				</li>
				<li class="nav-item pcoded-hasmenu">
					<a href="javascript:void(0)" class="nav-link">
						<span class="pcoded-micon">
							<i class="feather icon-settings"></i>
					</span>
						<span class="pcoded-mtext">Configuraciones</span></a>
					<ul class="pcoded-submenu">
						{{-- <li><a href="{{ ROUTE('paciente.dependiente.perfil') }}">Editar Perfil</a></li> --}}
						@if ($idDependienteActivo)
							<li><a href="{{ ROUTE('paciente.mascotas.pagos_suscripcion') }}"><i class="feather icon-credit-card"></i>Pagos y Suscripción</a></li>
						@endif
					</ul>
				</li>

			</ul>
		</div>
	</div>
</nav>
