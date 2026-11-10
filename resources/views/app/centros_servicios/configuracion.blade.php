@extends('app.centros_servicios.layout')
@section('content')
<div class="page-header"><h4>Configuración de {{ $centro->nombre }}</h4></div>
@php($config = $centro->configuracion ?? [])
<form method="post" action="{{ route($centro->tipo.'.configuracion.guardar') }}">
    @csrf
    <div class="card">
        <div class="card-header"><h5>Datos del centro</h5></div>
        <div class="card-body form-row">
            @foreach(['nombre' => 'Nombre', 'direccion' => 'Dirección', 'telefono' => 'Teléfono', 'email' => 'Correo de contacto'] as $campo => $texto)
                <div class="form-group col-md-6">
                    <label for="{{ $campo }}">{{ $texto }}</label>
                    <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="{{ $campo === 'email' ? 'email' : 'text' }}" value="{{ old($campo, $centro->{$campo}) }}" required>
                </div>
            @endforeach
            <div class="form-group col-12">
                <label><input type="checkbox" name="publicado" value="1" @checked(old('publicado', $centro->publicado))> Publicar en el catálogo de tutores</label>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h5>Servicios y precios</h5></div>
        <div class="card-body">
            <div class="form-row">
                @if($centro->tipo === 'guarderia_hotel')
                    @foreach(['cupos_guarderia' => 'Cupos diarios de guardería', 'cupos_hotel' => 'Cupos de alojamiento por noche'] as $campo => $texto)
                        <div class="form-group col-md-6">
                            <label for="{{ $campo }}">{{ $texto }}</label>
                            <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="number" min="0" max="1000" value="{{ old($campo, $config[$campo] ?? 0) }}" required>
                        </div>
                    @endforeach
                    <fieldset class="form-group col-12">
                        <legend class="h6">Días de atención de la guardería</legend>
                        @foreach([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'] as $dia => $texto)
                            <label class="mr-3"><input type="checkbox" name="dias[]" value="{{ $dia }}" @checked(in_array($dia, old('dias', $config['dias'] ?? [1,2,3,4,5])))> {{ $texto }}</label>
                        @endforeach
                    </fieldset>
                @endif
                @foreach(['ofrece_retiro' => 'Retiro a domicilio'] + ($centro->tipo === 'guarderia_hotel' ? ['ofrece_peluqueria' => 'Acompañamiento a peluquería y belleza'] : ['ofrece_entierro' => 'Entierro']) as $campo => $texto)
                    <div class="form-group col-md-6"><label><input type="checkbox" name="{{ $campo }}" value="1" @checked(old($campo, $config[$campo] ?? false))> {{ $texto }}</label></div>
                @endforeach
            </div>
            <p class="text-muted">Si dejas un precio vacío, el centro podrá indicar el valor al confirmar.</p>
            <div class="form-row">
                @foreach(\App\Services\CentrosServiciosMascotas::PRECIOS[$centro->tipo] as $campo => $texto)
                    <div class="form-group col-md-4">
                        <label for="{{ $campo }}">{{ $texto }} (CLP)</label>
                        <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="number" min="0" max="100000000" value="{{ old($campo, $config[$campo] ?? '') }}">
                    </div>
                @endforeach
            </div>
            <a href="{{ route($centro->tipo.'.home') }}" class="btn btn-outline-secondary">Volver</a>
            <button class="btn btn-info">Guardar configuración</button>
        </div>
    </div>
</form>
@endsection
