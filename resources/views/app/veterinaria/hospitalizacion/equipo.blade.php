@extends('template.adm_cm.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
<h4>Equipo del servicio de hospitalización veterinaria</h4><p>{{ $institucion->nombre }}</p><a class="btn btn-outline-info mb-3" href="{{ route('vet.areas.gestion',['area'=>'hospitalizacion','id_institucion'=>$institucion->id]) }}">Volver a gestión</a>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@include('app.veterinaria.hospitalizacion.partials.equipo_contenido')
</div></div>
@endsection
