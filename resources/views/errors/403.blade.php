@extends('errors::minimal')

@php
    // Algunos mensajes llegan en inglés desde el framework y el paquete de
    // roles y permisos, así que aquí los dejamos en español antes de mostrarlos
    $mensajesTraducidos = [
        'User is not logged in.' => 'Debe iniciar sesión para ver esta página',
        'User does not have the right roles.' => 'Usted no posee los roles necesarios para ver esta página',
        'User does not have the right permissions.' => 'Usted no posee los permisos necesarios para ver esta página',
        'User does not have any of the necessary access rights.' => 'Usted no posee los roles y permisos para visualizar esta página',
        'This action is unauthorized.' => 'No está autorizado para realizar esta acción',
        'Forbidden' => 'Acceso denegado',
    ];

    $mensajeOriginal = trim($exception->getMessage());
    $mensajePorDefecto = 'Usted no posee los roles y permisos para visualizar esta página';

    if ($mensajeOriginal === '') {
        $mensajeError = $mensajePorDefecto;
    } else {
        $mensajeError = $mensajesTraducidos[$mensajeOriginal] ?? $mensajeOriginal;
    }
@endphp

@section('title', __('Acceso Denegado'))
@section('code', '403')
@section('message', $mensajeError)
