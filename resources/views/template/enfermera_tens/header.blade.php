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
			</li>
			<li>
                @include('template.partials.perfil_encabezado', ['tipoPerfil' => 'Tens'])
            </li>
		</ul>
	</div>
</header>
