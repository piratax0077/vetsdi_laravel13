@extends('template.usuario.template')
@section('content')
<div class="pcoded-main-container"><main class="pcoded-content">
    @include('app.centros_servicios.mensajes')
    <a class="btn btn-outline-secondary mb-3" href="{{ route('cuenta.seleccion') }}">Volver a mis perfiles</a>
    <div class="page-header"><h4>Administrar un centro de servicios para mascotas</h4></div>
    <div class="row">
        @foreach(\App\Services\CentrosServiciosMascotas::TIPOS as $tipo)
            <div class="col-md-6"><div class="card">
                <div class="card-header"><h5>{{ \App\Services\CuentasService::ETIQUETAS[$tipo] }}</h5></div>
                <div class="card-body">
                    <p>{{ \App\Services\CuentasService::DESCRIPCIONES[$tipo] }}</p>
                    @if(app(\App\Services\CuentasService::class)->tieneRol(auth()->user(), $tipo))
                        <a href="{{ route('cuenta.entrar', $tipo) }}" class="btn btn-info">Ingresar al perfil</a>
                    @else
                        <form method="post" action="{{ route('centros_servicios.activar') }}">
                            @csrf<input type="hidden" name="tipo" value="{{ $tipo }}"><button class="btn btn-info">Crear perfil</button>
                        </form>
                    @endif
                </div>
            </div></div>
        @endforeach
    </div>
</main></div>
@endsection
