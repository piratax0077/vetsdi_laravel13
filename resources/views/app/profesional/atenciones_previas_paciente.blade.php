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
		    <div class="col-md-12 mb-3 encabezado-pagina">
		        <h5 class="f-26 d-block mb-0">Historial de atenciones{{ isset($mascotaHistorial) && $mascotaHistorial ? ' de '.$mascotaHistorial->nombre : '' }}</h5>
		    </div>
		</div>
		<!--Cierre: Header-->
		<div class="row">
			<div class="col-sm-12">
				<input type="hidden" id="id_paciente" value="{{ request()->route('id') }}">
				{{-- El título ya va arriba, así que se incluye solo la tabla --}}
				@include('general.secciones_ficha.atenciones_previas_form', ['titulo' => 'NO'])
			</div>
		</div>

	</div>
</div>
@endsection
