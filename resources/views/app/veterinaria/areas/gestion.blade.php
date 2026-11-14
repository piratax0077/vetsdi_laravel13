@extends('template.adm_cm.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content configuracion-administrador vista-area-veterinaria">
    <div class="d-flex justify-content-between align-items-center mb-3"><h4>Gestión del área · {{ $titulo }}</h4><a class="btn btn-outline-info btn-sm" href="{{ route('vet.areas.index',['area'=>$area,'id_institucion'=>$institucion->id]) }}">Volver al área</a></div>
    @if($area === 'hospitalizacion')
    <a class="btn btn-info mb-3" href="{{ route('vet.hospital.equipo',['id_institucion'=>$institucion->id]) }}">Equipo de profesionales y enfermería</a>
    @endif
    @if(session('gestion_guardada'))<div class="alert alert-success">{{ session('gestion_guardada') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @include('app.veterinaria.areas.partials.gestion_contenido')
</div></div>
@endsection
