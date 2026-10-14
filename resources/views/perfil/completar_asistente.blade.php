@extends('perfil.layout_perfil')

@section('titulo', 'Completa tu perfil de asistente')
@section('bajada', 'Solo falta saber en qué modalidad trabajas.')

@section('campos')
    <fieldset class="perfil-bloque">
        <legend class="perfil-bloque-titulo" id="etiqueta-modalidad">Modalidad de trabajo</legend>

        <div class="perfil-opciones" role="radiogroup" aria-labelledby="etiqueta-modalidad">
            @foreach($modalidades as $id => $nombre)
                <label class="perfil-opcion" for="modalidad_{{ $id }}">
                    <input type="radio" name="id_modalidad" id="modalidad_{{ $id }}" value="{{ $id }}" @checked(old('id_modalidad') == $id)>
                    {{ $nombre }}
                </label>
            @endforeach
        </div>

        @error('id_modalidad')
            <small class="perfil-error" role="alert">{{ $message }}</small>
        @enderror
    </fieldset>
@endsection

@push('scripts')
    <script src="{{ asset('js/perfil/completar_perfil.js') }}?t={{ time() }}"></script>
@endpush
