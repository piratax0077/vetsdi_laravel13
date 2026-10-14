@extends('perfil.layout_perfil')

@section('titulo', 'Completa tu perfil profesional')
@section('bajada', 'Cuéntanos tu profesión y, si la tienes, tu especialidad veterinaria.')

@section('campos')
    <fieldset class="perfil-bloque">
        <legend class="perfil-bloque-titulo">Tu profesión</legend>

        <div class="perfil-campo">
            <label for="id_especialidad">Profesión</label>
            <select name="id_especialidad" id="id_especialidad" class="@error('id_especialidad') es-invalido @enderror">
                <option value="{{ \App\Http\Controllers\Perfil\CompletarPerfilController::ID_ESPECIALIDAD_VETERINARIA }}" selected>Médico Veterinario</option>
            </select>
            <small class="perfil-ayuda">Si ejerces otra profesión del área, avísanos para habilitarla.</small>
            @error('id_especialidad')
                <small class="perfil-error" role="alert">{{ $message }}</small>
            @enderror
        </div>

        <div class="perfil-campo">
            <label for="id_tipo_especialidad">Especialidad veterinaria <span class="perfil-opcional">(opcional)</span></label>
            <select name="id_tipo_especialidad" id="id_tipo_especialidad" class="@error('id_tipo_especialidad') es-invalido @enderror">
                <option value="">No tengo especialidad</option>
                @foreach($especialidades as $especialidad)
                    <option value="{{ $especialidad->id }}" @selected(old('id_tipo_especialidad') == $especialidad->id)>{{ $especialidad->nombre }}</option>
                @endforeach
            </select>
            @error('id_tipo_especialidad')
                <small class="perfil-error" role="alert">{{ $message }}</small>
            @enderror
        </div>
    </fieldset>
@endsection
