@extends('perfil.layout_perfil')
@section('titulo', 'Completa tu perfil profesional de estética')
@section('bajada', 'Registra los datos de tu servicio de peluquería e higiene de mascotas.')
@section('campos')
    <label for="nombre">Nombre profesional</label>
    <input class="form-control mb-3" id="nombre" name="nombre" value="{{ old('nombre', $usuario->name) }}" required maxlength="255">
    <label for="telefono">Teléfono de contacto</label>
    <input class="form-control mb-3" id="telefono" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" required maxlength="30">
    <label for="direccion">Dirección de atención</label>
    <input class="form-control mb-3" id="direccion" name="direccion" value="{{ old('direccion') }}" required maxlength="255">
@endsection
