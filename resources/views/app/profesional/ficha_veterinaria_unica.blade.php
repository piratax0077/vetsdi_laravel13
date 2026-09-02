@extends('template.profesional.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
<ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('profesional.home') }}" data-toggle="tooltip" data-placement="top"
                                    title="Volver a mi escritorio" aria-label="Volver a mi escritorio">
                                    <i class="feather icon-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('profesional.pacientes') }}">Mascotas y responsables</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">Ficha Veterinaria Única</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @include('general.secciones_ficha.fmu')
    </div>
</div>
@endsection
