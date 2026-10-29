@extends('template.adm_cm.template')
@section('content')

 <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
<div class="row"><div class="col-md-4"><div class="card subir"><a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'hospitalizacion', 'id_institucion'=>$institucion->id ?? null])) }}"><div class="card-body text-center"><i class="feather icon-grid"></i><h6>Hospitalización</h6></div></a></div></div><div class="col-md-4"><div class="card subir"><a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'peluqueria', 'id_institucion'=>$institucion->id ?? null])) }}"><div class="card-body text-center"><i class="feather icon-grid"></i><h6>Peluquería y belleza</h6></div></a></div></div><div class="col-md-4"><div class="card subir"><a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'configuracion', 'id_institucion'=>$institucion->id ?? null])) }}"><div class="card-body text-center"><i class="feather icon-grid"></i><h6>Tipo de establecimiento</h6></div></a></div></div></div>
            <!--Header-->
            <div class="page-header encabezado-escritorio">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            @include('template.include.saludo_escritorio', ['texto_saludo' => 'Bienvenido/a a tu escritorio de administrador general de hospital'])
                        </div>
                    </div>
                </div>
            </div>
            <!--Cierre: Header-->
            <!--Botones-->
            <div class="row">
                <div class="col-md-12">
                    <div class="card subir py-auto bg-info">
                        <div class="card-body text-center">
                             <h5 class=" mb-0 text-white f-24">Institucion {{ mb_strtoupper($institucion->nombre) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ URL('AdministradorHospital/Configuracion') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/panel_configuracion.svg') }}">
                                <h6 class="mt-2 mb-0">Configurar mi hospital</h6>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ route('sitio.configurar') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/docs.svg') }}" alt="Página web institucional">
                                <h6 class="mt-2 mb-0">Configurar página web institucional</h6>
                            </div>
                        </a>
                    </div>
                </div>

				<div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ ROUTE('adm_hospital.adm_medico') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/adm_medica.png') }}">
                                <h6 class="mt-2 mb-0">Administración médica</h6>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ ROUTE('adm_hospital.area_contratos_nuevos') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/adm_comercial.png') }}">
                                <h6 class="mt-2 mb-0">Contratos e incorporaciones</h6>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ ROUTE('adm_hospital.area_comercial') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/adm_comercial.png') }}">
                                <h6 class="mt-2 mb-0">Administración comercial</h6>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ ROUTE('adm_hospital.profesionales_institucion') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/profesionales.svg') }}">
                                <h6 class="mt-2 mb-0">Profesionales de la Institucion</h6>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ ROUTE('adm_hospital.mis_profesionales') }}">
							<div class="card-body text-center" style="cursor:pointer">
								<img class="wid-50 text-center" src="{{ asset('images/iconos/agenda.svg') }}">
                                <h6 class="mt-2 mb-0">Agenda de profesionales</h6>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ ROUTE('adm_hospital.pacientes') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/pacientes.svg') }}">
                                <h6 class="mt-2 mb-0">Pacientes</h6>
                            </div>
                        </a>
                    </div>
                </div>
				 <div class="col-md-3">
                    <div class="card subir py-auto">
                        <a href="{{ ROUTE('adm_hospital.personal') }}">
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-60 text-center"  src="{{ asset('images/iconos/personal.png') }}">
                                <h6 class="mt-1 mb-0">Manejo de Asistentes</h6>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card subir py-auto bg-warning">
                        <div class="card-body text-center" style="cursor:pointer">
                            <h6 class="mb-0 text-white f-20">Areas de la institucion</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto"><a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'laboratorio', 'id_institucion'=>$institucion->id ?? null])) }}">
                      {{--  <a href="{{ ROUTE('adm_cm.laboratorio') }}"></a>--}}
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/laboratorio.svg') }}">
                                <h6 class="mt-2 mb-0">Laboratorio</h6>
                            </div>
                        </a>
                    </a></div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto"><a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'imagenologia', 'id_institucion'=>$institucion->id ?? null])) }}">
                      {{-- <a href="{{ ROUTE('adm_cm.laboratorio') }}"></a>--}}
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/imagenologia.svg') }}">
                                <h6 class="mt-2 mb-0">Imagenología</h6>
                            </div>
                        </a>
                    </a></div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto"><a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'vacunatorio', 'id_institucion'=>$institucion->id ?? null])) }}">
                       {{-- <a href="{{ ROUTE('adm_cm.vacunatorio') }}"></a>--}}
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/vacunatorio.svg') }}">
                                <h6 class="mt-2 mb-0">Vacunatorio</h6>
                            </div>
                        </a>
                    </a></div>
                </div>
                <div class="col-md-3">
                    <div class="card subir py-auto"><a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'dental', 'id_institucion'=>$institucion->id ?? null])) }}">
                       {{-- <a href="{{ ROUTE('adm_cm.dental') }}"></a>--}}
                            <div class="card-body text-center" style="cursor:pointer">
                                <img class="wid-50 text-center"  src="{{ asset('images/iconos/dental.png') }}">
                                <h6 class="mt-2 mb-0">Dental</h6>
                            </div>
                        </a>
                    </a></div>
                </div>
				<div class="col-md-12">
					<div class="card subir py-auto">
						<a href="{{ route('vet.operacion.index', array_filter(['seccion'=>'asistentes', 'id_institucion'=>$institucion->id ?? null])) }}">
							<div class="card-body text-center" style="cursor:pointer">
								<img class="wid-50 text-center rounded" src="{{ asset('images/iconos/mis_asistentes.svg') }}">
								<h6 class="mt-1">Contratar asistentes en linea</h6>
							</div>
						</a>
					</div>
				</div>
            </div>
        </div>
    </div>
    <!--Cierre: Container Completo-->

@endsection
