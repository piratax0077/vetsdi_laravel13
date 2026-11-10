@extends('perfil.layout_perfil')
@section('titulo', 'Completa el perfil de '.$etiqueta)
@section('bajada', 'Registra los datos del centro. Luego podrás configurar sus servicios y recibir solicitudes.')
@section('campos')
<fieldset class="perfil-bloque">
    <legend class="perfil-bloque-titulo">Datos del centro</legend>
    <div class="perfil-fila">
        @foreach(['nombre' => 'Nombre del centro', 'direccion' => 'Dirección', 'telefono' => 'Teléfono', 'email' => 'Correo de contacto'] as $campo => $texto)
            <div class="perfil-campo">
                <label for="{{ $campo }}">{{ $texto }}</label>
                <input class="form-control" id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo, $campo === 'email' ? $usuario->email : '') }}" type="{{ $campo === 'email' ? 'email' : 'text' }}" required>
                @error($campo)<small class="perfil-error" role="alert">{{ $message }}</small>@enderror
            </div>
        @endforeach
    </div>
</fieldset>
@endsection
