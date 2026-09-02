@extends('sitio.layout')
@section('title',$articulo->titulo.' · VeterChile')
@section('content')
<article class="sw-published-article"><header><div class="sw-wrap"><p class="sw-kicker">Consejos para tu mascota</p><h1>{{ $articulo->titulo }}</h1><p>{{ $articulo->resumen }}</p><small>Por {{ $articulo->sitio->titulo }} · {{ optional($articulo->publicado_at)->format('d-m-Y') }}</small></div></header>@if($articulo->imagen)<img class="sw-article-cover" src="{{ asset('storage/'.$articulo->imagen) }}" alt="{{ $articulo->titulo }}">@endif<div class="sw-wrap sw-article-content">{!! nl2br(e($articulo->contenido)) !!}<div class="sw-article-author-box"><strong>{{ $articulo->sitio->titulo }}</strong><span>{{ $articulo->sitio->slogan }}</span><a class="sw-btn sw-btn-solid" href="{{ route('sitio.ficha',$articulo->sitio->slug) }}">Ver página profesional</a></div></div></article>
@endsection
