@extends('perfil.layout_perfil')
@section('titulo','Completa el perfil del centro estético')
@section('bajada','Registra los datos de la empresa. El centro administra el equipo y las sucursales; la atención corresponde al perfil profesional de estética.')
@section('campos')
<fieldset class="perfil-bloque"><legend class="perfil-bloque-titulo">Datos de la institución</legend><div class="perfil-fila">
@foreach(['nombre'=>'Nombre del centro','rut_empresa'=>'RUT de la empresa','razon_social'=>'Razón social','giro'=>'Giro','telefono'=>'Teléfono de contacto','email_contacto'=>'Correo del centro','direccion'=>'Dirección principal'] as $campo=>$titulo)
<div class="perfil-campo"><label for="{{ $campo }}">{{ $titulo }}</label><input class="form-control" id="{{ $campo }}" name="{{ $campo }}" type="{{ $campo==='email_contacto'?'email':'text' }}" value="{{ old($campo,$campo==='email_contacto'?$usuario->email:($campo==='telefono'?$usuario->telefono:'')) }}" maxlength="{{ $campo==='rut_empresa'?12:($campo==='telefono'?30:255) }}" required>@error($campo)<small class="perfil-error" role="alert">{{ $message }}</small>@enderror</div>
@endforeach
</div></fieldset>
@endsection
