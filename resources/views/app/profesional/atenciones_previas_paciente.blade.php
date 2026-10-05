@extends('template.profesional.template')

@section('page-styles')
<style>
p {
    color: #59636d;
    word-wrap: break-word !important;
    font-size: 14px;
}
</style>
@endsection

@section('content')

<div class="pcoded-main-container">
	<div class="pcoded-content">
		<!--Header-->
		<div class="row">
		    <div class="col-md-12 mb-2">
		        <h5 class="f-26 d-inline">Historial de atenciones{{ isset($mascotaHistorial) && $mascotaHistorial ? ' de '.$mascotaHistorial->nombre : '' }}</h5>
		    </div>
		</div>
		<div class="row  user-profile user-card  py-1 pb-3 mt-3 px-2 bg-gris">
		<!--Cierre: Header-->
			<!-- inculde -->
			<?php $fade = 'in'; $titulo = 'titulo'; ?>
			{{-- @include('atencion_medica.formularios.atenciones_previas_form' ) --}}
			<input type="hidden" id="id_paciente" value="{{ request()->route('id') }}">
			@include('general.secciones_ficha.atenciones_previas_form')
		</div>

	</div>
</div>
@endsection
