@extends('template.paciente_dependiente.template')

@section('page-styles')
<style>
    .fvu-page-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 0;
    }
    .fvu-page-breadcrumb .breadcrumb-item {
        display: inline-flex;
        align-items: center;
    }
    .fvu-page-breadcrumb .breadcrumb-item a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        min-height: 28px;
        line-height: 1;
    }
    .fvu-page-breadcrumb .breadcrumb-item i {
        font-size: 22px;
        line-height: 1;
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
<ul class="breadcrumb fvu-page-breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('paciente.mascota.home', ['id_mascota' => $mascota->id]) }}"
                                    data-toggle="tooltip" data-placement="top" title="Mi escritorio de mascota"
                                    aria-label="Mi escritorio de mascota">
                                    <i class="feather icon-home" aria-hidden="true"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">Mi Ficha Veterinaria Única</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @include('general.secciones_ficha.fmu_mascota')
    </div>
</div>
@endsection

