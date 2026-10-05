@extends('template.usuario.template')

@section('page-styles')
<link rel="stylesheet" href="{{ asset('css/genealogia.css') }}?v={{ @filemtime(public_path('css/genealogia.css')) }}">
@endsection

@push('botones-header')
    <li class="d-flex align-items-center mr-1 mr-sm-2 elemento-escritorio-mascota">
        <a href="{{ route('paciente.dependiente.home', ['id_dependiente_activo' => $mascota->id]) }}" class="btn btn-outline-header btn-xxs d-inline-flex align-items-center text-nowrap boton-escritorio-mascota" data-toggle="tooltip" data-placement="bottom" title="Volver al escritorio de {{ $mascota->nombre }}">
            {{-- patita de línea, con el mismo trazo que los íconos feather --}}
            <svg class="icono-patita mr-1" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <ellipse cx="5" cy="10.5" rx="2" ry="2.5"/>
                <ellipse cx="9.5" cy="5.5" rx="2" ry="2.5"/>
                <ellipse cx="14.5" cy="5.5" rx="2" ry="2.5"/>
                <ellipse cx="19" cy="10.5" rx="2" ry="2.5"/>
                <path d="M12 12c-2.8 0-5.5 3.3-5.5 6 0 1.7 1.3 2.8 3 2.8.9 0 1.6-.5 2.5-.5s1.6.5 2.5.5c1.7 0 3-1.1 3-2.8 0-2.7-2.7-6-5.5-6z"/>
            </svg><span class="boton-escritorio-mascota-texto">Escritorio {{ \Illuminate\Support\Str::ucfirst($mascota->nombre) }}</span>
        </a>
    </li>
@endpush

@section('content')
@php
    $g = $mascota->genealogia;
@endphp
<div class="pcoded-main-container genealogy-shell"><div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <h5 class="text-dark mb-2 mb-md-0 f-24">Genealogía de {{ $mascota->nombre }}</h5>
                <div class="d-flex flex-wrap genealogy-header-actions">
                    <a href="{{ route('mascotas.genealogia.index') }}" class="btn btn-light btn-sm mr-2 mb-1">
                        <i class="fas fa-paw mr-1"></i> Ver mis mascotas
                    </a>
                    <button type="button" class="btn btn-info btn-sm mb-1 js-agregar-familiar">
                        <i class="fas fa-plus mr-1"></i> Agregar familiar
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- avisos flotantes en la esquina inferior derecha --}}
    <div class="notificaciones" id="notificaciones" aria-live="polite">
        @if(session('ok'))
            <div class="notificacion es-exito" role="status">
                <i class="fas fa-check-circle notificacion-icono" aria-hidden="true"></i>
                <span class="notificacion-texto">{{ session('ok') }}</span>
                <button type="button" class="notificacion-cerrar" aria-label="Cerrar aviso">&times;</button>
            </div>
        @endif
        @if($errors->any())
            <div class="notificacion es-error" role="alert">
                <i class="fas fa-exclamation-circle notificacion-icono" aria-hidden="true"></i>
                <div class="notificacion-texto">
                    @if($errors->count() === 1)
                        {{ $errors->first() }}
                    @else
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    @endif
                </div>
                <button type="button" class="notificacion-cerrar" aria-label="Cerrar aviso">&times;</button>
            </div>
        @endif
    </div>

    @include('app.paciente.genealogia.partials.arbol', ['editable' => true, 'crias' => $crias])

    <form method="POST" enctype="multipart/form-data" action="{{ route('mascotas.genealogia.store', $mascota) }}">
        @csrf
        <div class="card genealogy-card genealogy-form">
            <div class="card-header bg-white"><h5 class="mb-0">Datos del pedigrí</h5></div>
            <div class="card-body">
                <div class="main-photo-box mb-4">
                    @if($mascota->foto_url)
                        <img class="photo-preview" data-preview="foto_mascota" src="{{ $mascota->foto_url }}" alt="Foto de {{ $mascota->nombre }}">
                    @else<div class="photo-preview empty" data-preview="foto_mascota"><i class="fas fa-camera"></i></div>@endif
                    <div class="flex-grow-1">
                        <label class="d-block mb-1">Foto de {{ $mascota->nombre }}</label>
                        <input type="file" name="foto_mascota" class="form-control-file genealogy-photo-input" accept="image/jpeg,image/png,image/webp">
                        <div class="form-group mt-3 mb-0 genealogy-main-species">
                            <label class="floating-label-activo-sm">Especie de {{ $mascota->nombre }}</label>
                            <select name="especie_id" class="form-control" required>
                                <option value="">Seleccione</option>
                                @foreach($especies as $especie)
                                    <option value="{{ $especie->id }}" @selected((int) old('especie_id', $mascota->especie_id ?: $mascota->especie) === (int) $especie->id)>{{ $especie->nombre }}</option>
                                @endforeach
                            </select>
                            @error('especie_id')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                        </div>
                        <small class="text-muted">JPG, PNG o WEBP. Máximo 5 MB.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group"><label>Número de registro</label><input type="text" name="numero_registro" class="form-control" value="{{ old('numero_registro', optional($g)->numero_registro) }}"></div>
                    <div class="col-md-4 form-group"><label>Criador</label><input type="text" name="criador" class="form-control" value="{{ old('criador', optional($g)->criador) }}"></div>
                    <div class="col-md-4 form-group"><label>Observaciones</label><textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', optional($g)->observaciones) }}</textarea></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><button class="btn btn-info btn-block" type="submit"><i class="fas fa-save"></i> Guardar datos</button></div>
                    <div class="col-md-4"><a class="btn btn-success btn-block" target="_blank" href="{{ route('mascotas.genealogia.certificado', $mascota) }}"><i class="fas fa-certificate"></i> Ver certificado</a></div>
                    <div class="col-md-4"><a class="btn btn-secondary btn-block" href="{{ route('paciente.mascotas.index') }}">Volver a mis mascotas</a></div>
                </div>
            </div>
        </div>
    </form>

    @include('app.paciente.genealogia.partials.modal_familiar')

    <div class="genealogy-viewer" id="genealogyViewer" role="dialog" aria-modal="true" aria-label="Visor de fotografías">
        <div class="genealogy-viewer-dialog">
            <button type="button" class="genealogy-viewer-close" aria-label="Cerrar">&times;</button>
            <button type="button" class="genealogy-viewer-nav genealogy-viewer-prev" aria-label="Foto anterior">&#8249;</button>
            <img class="genealogy-viewer-image" src="" alt="">
            <button type="button" class="genealogy-viewer-nav genealogy-viewer-next" aria-label="Foto siguiente">&#8250;</button>
            <div class="genealogy-viewer-caption"></div>
            <span class="genealogy-viewer-counter"></span>
        </div>
    </div>
</div></div>
@endsection

@section('page-script')
@php
    // datos para el formulario de familiares; "anterior" reabre el modal si falló la validación
    $datosGenealogia = [
        'mascota' => ['nombre' => $mascota->nombre, 'especie' => $mascota->tipo_especie],
        'parentescos' => $parentescos,
        'familiares' => $familiares,
        'hermanos' => $hermanos->where('automatico', false)->keyBy('id'),
        'opciones' => $opcionesMascotas,
        'anterior' => old('formulario') === 'familiar' ? [
            'modo' => old('modo'),
            'parentesco' => old('parentesco'),
            'registro_hermano_id' => old('registro_hermano_id'),
            'origen' => old('origen'),
            'mascota_id' => old('mascota_id'),
            'nombre' => old('nombre'),
            'especie' => old('especie'),
            'sexo' => old('sexo'),
            'tipo' => old('tipo'),
        ] : null,
    ];
@endphp
<script type="application/json" id="datosGenealogia">@json($datosGenealogia)</script>
<script src="{{ asset('js/genealogia.js') }}?v={{ @filemtime(public_path('js/genealogia.js')) }}"></script>
@endsection
