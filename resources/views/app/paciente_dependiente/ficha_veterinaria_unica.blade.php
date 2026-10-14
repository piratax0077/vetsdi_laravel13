@extends('template.paciente_dependiente.template')

@section('page-styles')
<link rel="stylesheet" href="{{ asset('css/ficha_veterinaria_unica.css') }}?v={{ @filemtime(public_path('css/ficha_veterinaria_unica.css')) }}">
@endsection

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        @include('general.secciones_ficha.fmu_mascota')
    </div>
</div>
@endsection

{{-- Esta plantilla no imprime el stack de la ficha; sin esto los botones "Ver" no responden --}}
@section('page-script')
    @stack('page-scripts')
@endsection
