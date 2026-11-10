@extends('template.paciente_dependiente.template')
@section('content')
<!--Container Completo-->
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!--Header-->
        <div class="row">
            <div class="col-md-12 mb-3">
                <h5 class="f-26 d-inline">Mis documentos</h5>
            </div>
        </div>
        <!--Cierre: Header-->
        <!--Botones-->
        <div class="row m-b-30">
            <div class="col-md-12">
                <div class="card-deck">
                    <div class="card subir tarjeta-documento">
                        <a href="{{ ROUTE('paciente.dependiente.receta.receta', ['id_dependiente_activo' => $id_dependiente_activo]) }}">
                            <div class="card-body tarjeta-documento__cuerpo">
                                <span class="tarjeta-documento__icono">
                                    <img src="{{ asset('images/iconos/recetas-ro.svg') }}" alt="Mis Recetas">
                                </span>
                                <span class="tarjeta-documento__textos">
                                    <h4 class="tarjeta-documento__titulo">Recetas</h4>
                                    <span class="tarjeta-documento__descripcion">Indicaciones y medicamentos recetados a su mascota.</span>
                                </span>
                                <i class="feather icon-chevron-right tarjeta-documento__flecha" aria-hidden="true"></i>
                            </div>
                        </a>
                    </div>
                    <div class="card subir tarjeta-documento">
                        <a href="{{ ROUTE('paciente.dependiente.receta.examen', ['id_dependiente_activo' => $id_dependiente_activo]) }}">
                            <div class="card-body tarjeta-documento__cuerpo">
                                <span class="tarjeta-documento__icono">
                                    <img src="{{ asset('images/iconos/examenes-ro.svg') }}" alt="Mis Exámenes">
                                </span>
                                <span class="tarjeta-documento__textos">
                                    <h4 class="tarjeta-documento__titulo">Exámenes</h4>
                                    <span class="tarjeta-documento__descripcion">Resultados de exámenes y estudios de laboratorio o imagen.</span>
                                </span>
                                <i class="feather icon-chevron-right tarjeta-documento__flecha" aria-hidden="true"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--CIERRE:Botones-->
        <!--Botones-->
        <div class="row m-b-10">
            <div class="col-md-12">
                <div class="card-deck">
                    <div class="card subir tarjeta-documento">
                        <a href="{{ ROUTE('paciente.dependiente.receta.certificado', ['id_dependiente_activo' => $id_dependiente_activo]) }}">
                            <div class="card-body tarjeta-documento__cuerpo">
                                <span class="tarjeta-documento__icono">
                                    <img src="{{ asset('images/iconos/documentos-ro.svg') }}" alt="Mis Consentimientos">
                                </span>
                                <span class="tarjeta-documento__textos">
                                    <h4 class="tarjeta-documento__titulo">Consentimientos informados</h4>
                                    <span class="tarjeta-documento__descripcion">Autorizaciones firmadas para procedimientos veterinarios.</span>
                                </span>
                                <i class="feather icon-chevron-right tarjeta-documento__flecha" aria-hidden="true"></i>
                            </div>
                        </a>
                    </div>
                    <div class="card subir tarjeta-documento">
                        <a href="{{ ROUTE('paciente.dependiente.receta.licencia', ['id_dependiente_activo' => $id_dependiente_activo]) }}">
                            <div class="card-body tarjeta-documento__cuerpo">
                                <span class="tarjeta-documento__icono">
                                    <img src="{{ asset('images/iconos/certificados-ro.svg') }}" alt="Mis Documentos">
                                </span>
                                <span class="tarjeta-documento__textos">
                                    <h4 class="tarjeta-documento__titulo">Documentos</h4>
                                    <span class="tarjeta-documento__descripcion">Certificados y otros documentos emitidos por su veterinario.</span>
                                </span>
                                <i class="feather icon-chevron-right tarjeta-documento__flecha" aria-hidden="true"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--CIERRE:Botones-->
    </div>
</div>
<!--Cierre: Container Completo-->
@endsection
