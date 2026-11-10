@extends('template.profesional.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <a href="{{ route('profesional.configuracion') }}" class="btn btn-link">Volver</a>
        <h5 class="titulo-modulo f-26 mb-3">Personalizar mi ficha</h5>
        @if (session('ficha_personalizada'))
            <div class="alert alert-success" role="status">{{ session('ficha_personalizada') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('profesional.personalizar_ficha.guardar') }}">
            @csrf
            <div class="card">
                <div class="card-header"><h5>Ficha básica</h5></div>
                <div class="card-body">
                    <p class="mb-0">Motivo de consulta, examen físico, diagnóstico y crónicos siempre están disponibles.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5>Secciones especializadas</h5></div>
                <div class="card-body">
                    @foreach ($secciones as $clave => $nombre)
                        <div class="custom-control custom-switch mb-3">
                            <input type="checkbox" class="custom-control-input" id="ficha-{{ $clave }}"
                                name="secciones[]" value="{{ $clave }}" @checked(in_array($clave, (array) old('secciones', $visibles), true))>
                            <label class="custom-control-label" for="ficha-{{ $clave }}">{{ $nombre }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('profesional.configuracion') }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-info">Guardar</button>
        </form>
    </div>
</div>
@endsection
