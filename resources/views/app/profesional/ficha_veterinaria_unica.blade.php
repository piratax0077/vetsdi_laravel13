@extends('template.profesional.template')

@section('page-styles')
<link rel="stylesheet" href="{{ asset('css/ficha_veterinaria_unica.css') }}?v={{ @filemtime(public_path('css/ficha_veterinaria_unica.css')) }}">
@endsection

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        {{-- Misma ficha que ve el tutor en el escritorio de la mascota --}}
        @include('general.secciones_ficha.fmu_mascota')
    </div>
</div>
@endsection
