@extends('template.adm_cm.template')
@section('content')


<!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-3 encabezado-pagina">
                    <h5 class="f-26 d-inline">Escritorio Vacunatorio</h5>
                </div>
            </div>
            <!--Cierre: Header-->
            <!--Row Botones-->
            <div class="row m-b-30">
                <div class="col-md-12">
                    <div class="card-deck">
                        <!--Cierre de Card-->
                        <div class="card  subir py-1">
                            <a href="{{ ROUTE('adm_cm.vacunatorio_felicitreclamos') }}">
                                <div class="card-body text-center" style="cursor:pointer">
                                    <img class="wid-80 text-center mt-2" src="{{ asset('images/iconos/estadisticas_2.svg') }}">
                                    <h5 class="mt-3">Reclamos y Felicitaciones</h5>
                                </div>
                            </a>
                        </div>
                        <div class="card py-1 subir">
                            <a href="{{ route('adm_cm.vacunatorio.agenda') }}">
                                <div class="card-body text-center" style="cursor:pointer" data-url="enviar.php">
                                    <img class="wid-80 text-center" src="{{ asset('images/iconos/vacunas.svg') }}">
                                    <h5 class="mt-3">Mi agenda</h5>
                                </div>
                            </a>
                        </div>
                        <div class="card  subir py-1">
                            <a href="{{ ROUTE('adm_cm.vacunatorio_pedidos') }}">
                                <div class="card-body text-center" style="cursor:pointer">
                                    <img class="wid-80 text-center mt-2" src="{{ asset('images/iconos/inventario.svg') }}">
                                    <h5 class="mt-3">Pedidos de Vacunas e Insumos</h5>
                                </div>
                            </a>
                        </div>
                        <div class="card  subir py-1">
                            <a href="{{ ROUTE('adm_cm.vacunatorio_instalaciones') }}">
                                <div class="card-body text-center" style="cursor:pointer">
                                    <img class="wid-80 text-center mt-2" src="{{ asset('images/iconos/perfiles.svg') }}">
                                    <h5 class="mt-3">Instalaciones</h5>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


                        
    <!--Cierre: Container Completo-->
	@endsection
