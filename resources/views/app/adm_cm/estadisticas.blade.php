@extends('template.adm_cm.template')
@section('content')
<!--****Container Completo****-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="row">
            <div class="col-md-12 mb-3 encabezado-pagina">
                <h5 class="f-26 d-inline">Estadísticas y atenciones del Centro</h5>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="row">
                <div class="col-md-4">
                    <div class="card subir">
                        <a href="{{ ROUTE('adm_cm.at_profesionales') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-70 text-center" src="{{ asset('images/iconos/inventario.svg') }}">
                                <h5 class="mt-2">Atenciones Profesionales</h5>
                            </div>
                        </a>
                    </div>
                </div>
				<div class="col-md-4">
                    <div class="card subir">
                        <a href="{{ ROUTE('adm_cm.pagos') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-70 text-center" src="{{ asset('images/iconos/inventario.svg') }}">
                                <h5 class="mt-2">Atenciones Laboratorios</h5>
                            </div>
                        </a>
                    </div>
                </div>
				<div class="col-md-4">
                    <div class="card subir">
                        <a href="{{ ROUTE('adm_cm.insumos') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-70 text-center" src="{{ asset('images/iconos/inventario.svg') }}">
                                <h5 class="mt-2">Inasistencias y Rechazos</h5>
                            </div>
                        </a>
                    </div>
                </div><div class="col-md-3">
                    <div class="card subir">
                        <a href="{{ ROUTE('adm_cm.estadisticas') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-70 text-center" src="{{ asset('images/iconos/estadisticas_2.svg') }}">
                                <h5 class="mt-2">Estadisticas Generales</h5>
                            </div>
                        </a>
                    </div>
                </div>
				
            </div>
        </div>
    </div>
</div>
<!--****Cierre Container Completo****-->
@endsection
