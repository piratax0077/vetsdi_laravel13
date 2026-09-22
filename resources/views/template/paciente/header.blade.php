<header class="navbar pcoded-header navbar-expand-lg navbar-light header-blue">
	<div class="m-header">
			<a class="mobile-menu on" id="mobile-collapse" href="#!"><span></span></a>
			<a href="#!" class="b-brand">
				<img src="{{ asset('images/logo_pais.png') }}" alt="" class="logo" height="45px">
			</a>
			<a href="#!" class="mob-toggler">
				<i class="feather icon-more-vertical icono-header"></i>
			</a>
		</div>
		<div class="collapse navbar-collapse">
			<ul class="navbar-nav ml-auto">
            <li class="d-flex align-items-center mr-2">
                <a href="{{ route('paciente.home') }}" class="btn btn-outline-header btn-xxs d-inline-flex align-items-center" style="white-space:nowrap;" data-toggle="tooltip" data-placement="bottom" title="Volver a mi escritorio">
                    <i class="feather icon-home mr-1"></i>Mi escritorio
                </a>
            </li>
            @include('template.partials.workspace_switcher')
			<li>
                <div class="dropdown drp-user">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="feather icon-user icono-header"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-notification">
                        <div class="pro-head">
                            <span>{{  @Auth::user()->name  }}</span>
                        </div>
                        {{--  <ul></ul>  --}}
                        <ul class="pro-body">
                            <li>
                                <form action="{{ ROUTE('logout') }}" method="post" id="closeSession">
                                    @csrf
                                    <a data-toggle="tooltip" title="Cerrar sesión" class="text-danger" href="javascript:{}" onclick="document.getElementById('closeSession').submit();">
                                        <i class="feather icon-power" style="font-size: 1.2rem;"></i> Cerrar sesión
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
		</ul>
	</div>
</header>
