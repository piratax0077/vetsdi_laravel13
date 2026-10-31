<header class="navbar pcoded-header navbar-expand-lg navbar-light header-blue">
	<div class="m-header">
			<a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
			<a href="#!" class="b-brand">
				<img src="{{ asset('images/logo_pais.png') }}" alt="" class="logo" height="45px">
			</a>
			<a href="#!" class="mob-toggler">
				<i class="feather icon-more-vertical"></i>
			</a>
		</div>
		<div class="collapse navbar-collapse">
			<ul class="navbar-nav ml-auto">
                @if (\Route::has('adm_cm.home'))
                    {{-- Mismo botón de escritorio que usan los perfiles de tutor y profesional --}}
                    <li class="d-flex align-items-center mr-2">
                        <a href="{{ \App\Support\EscritorioVeterinario::url(request(), $institucion ?? null) }}" class="btn btn-outline-header btn-xxs d-inline-flex align-items-center" style="white-space:nowrap;" data-toggle="tooltip" data-placement="bottom" title="Volver a mi escritorio">
                            <i class="feather icon-home mr-1"></i>Mi escritorio
                        </a>
                    </li>
                @endif

                @include('template.partials.workspace_switcher')
			</li>
			<li>
                @include('template.partials.perfil_encabezado', ['tipoPerfil' => 'institucion'])
            </li>
		</ul>
	</div>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/7066bc9f2e.js" crossorigin="anonymous"></script>
</header>
