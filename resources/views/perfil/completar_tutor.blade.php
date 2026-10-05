@extends('perfil.layout_perfil')

@section('titulo', 'Completa tu perfil de tutor')
@section('bajada', 'Necesitamos saber dónde vives y quién es tu primera mascota. Después podrás agregar más.')

@section('campos')
    <fieldset class="perfil-bloque">
        <legend class="perfil-bloque-titulo">Dirección de tu domicilio</legend>

        <div class="perfil-fila">
            <div class="perfil-campo">
                <label for="id_region">Región</label>
                <select name="id_region" id="id_region" class="@error('id_region') es-invalido @enderror">
                    <option value="">Elige tu región</option>
                    @foreach($regiones as $region)
                        <option value="{{ $region->id }}" @selected(old('id_region') == $region->id)>{{ $region->nombre }}</option>
                    @endforeach
                </select>
                @error('id_region')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="id_ciudad">Comuna</label>
                <select name="id_ciudad" id="id_ciudad"
                    class="@error('id_ciudad') es-invalido @enderror"
                    data-url="{{ route('home.buscar_ciudad_region') }}"
                    data-elegida="{{ old('id_ciudad') }}">
                    <option value="">Primero elige una región</option>
                </select>
                @error('id_ciudad')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo es-ancho">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" class="@error('direccion') es-invalido @enderror" autocomplete="street-address">
                <small class="perfil-ayuda">Escribe la calle y el número, por ejemplo: Av. Irarrázaval 1420.</small>
                @error('direccion')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo es-ancho">
                <label for="depto">Departamento o casa <span class="perfil-opcional">(opcional)</span></label>
                <input type="text" name="depto" id="depto" value="{{ old('depto') }}" class="@error('depto') es-invalido @enderror">
                @error('depto')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </fieldset>

    <fieldset class="perfil-bloque">
        <legend class="perfil-bloque-titulo">Tu primera mascota</legend>

        <div class="perfil-fila">
            <div class="perfil-campo">
                <label for="mascota_nombre">Nombre</label>
                <input type="text" name="mascota_nombre" id="mascota_nombre" value="{{ old('mascota_nombre') }}" class="@error('mascota_nombre') es-invalido @enderror">
                @error('mascota_nombre')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="mascota_especie">Especie</label>
                <select name="mascota_especie" id="mascota_especie" class="@error('mascota_especie') es-invalido @enderror">
                    <option value="">Elige la especie</option>
                    @foreach($especies as $especie)
                        <option value="{{ $especie->id }}" @selected(old('mascota_especie') == $especie->id)>{{ $especie->nombre }}</option>
                    @endforeach
                </select>
                @error('mascota_especie')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="mascota_raza">Raza</label>
                <select name="mascota_raza" id="mascota_raza"
                    class="@error('mascota_raza') es-invalido @enderror"
                    data-elegida="{{ old('mascota_raza') }}">
                    <option value="">Primero elige la especie</option>
                </select>
                @error('mascota_raza')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="mascota_chip">Número de chip <span class="perfil-opcional">(opcional)</span></label>
                <input type="text" name="mascota_chip" id="mascota_chip" value="{{ old('mascota_chip') }}" class="@error('mascota_chip') es-invalido @enderror">
                @error('mascota_chip')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo es-ancho">
                <span class="perfil-campo-titulo" id="etiqueta-sexo" style="display:block; margin-bottom:5px; font-size:13px; font-weight:700;">Sexo</span>
                <div class="perfil-opciones" role="radiogroup" aria-labelledby="etiqueta-sexo">
                    <label class="perfil-opcion" for="sexo_m">
                        <input type="radio" name="mascota_sexo" id="sexo_m" value="M" @checked(old('mascota_sexo') === 'M')>
                        Macho
                    </label>
                    <label class="perfil-opcion" for="sexo_h">
                        <input type="radio" name="mascota_sexo" id="sexo_h" value="H" @checked(old('mascota_sexo') === 'H')>
                        Hembra
                    </label>
                </div>
                @error('mascota_sexo')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="mascota_fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" name="mascota_fecha_nacimiento" id="mascota_fecha_nacimiento" value="{{ old('mascota_fecha_nacimiento') }}" max="{{ date('Y-m-d') }}" class="@error('mascota_fecha_nacimiento') es-invalido @enderror">
                @error('mascota_fecha_nacimiento')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="mascota_edad_aproximada">O su edad aproximada</label>
                <input type="number" name="mascota_edad_aproximada" id="mascota_edad_aproximada" value="{{ old('mascota_edad_aproximada') }}" min="0" max="40" class="@error('mascota_edad_aproximada') es-invalido @enderror">
                <small class="perfil-ayuda">En años. Úsalo solo si no sabes la fecha exacta.</small>
                @error('mascota_edad_aproximada')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </fieldset>
@endsection

@push('scripts')
    <script type="application/json" id="catalogo-razas">{!! json_encode($razasPorEspecie) !!}</script>
    <script src="{{ asset('js/perfil/completar_perfil.js') }}?t={{ time() }}"></script>
@endpush
