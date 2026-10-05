@extends('perfil.layout_perfil')

@section('titulo', 'Completa el perfil de tu clínica')
@section('bajada', 'Son los datos de la empresa. El RUT de la persona que administra ya quedó registrado en tu cuenta.')

@section('campos')
    <fieldset class="perfil-bloque">
        <legend class="perfil-bloque-titulo">Datos de la empresa</legend>

        <div class="perfil-fila">
            <div class="perfil-campo">
                <label for="rut_empresa">RUT de la empresa</label>
                <input type="text" name="rut_empresa" id="rut_empresa" value="{{ old('rut_empresa') }}" maxlength="12" onkeyup="formatoRut(this)" class="@error('rut_empresa') es-invalido @enderror">
                @error('rut_empresa')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="giro">Giro</label>
                <input type="text" name="giro" id="giro" value="{{ old('giro') }}" class="@error('giro') es-invalido @enderror">
                @error('giro')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="razon_social">Razón social</label>
                <input type="text" name="razon_social" id="razon_social" value="{{ old('razon_social') }}" class="@error('razon_social') es-invalido @enderror">
                @error('razon_social')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="nombre_fantasia">Nombre de fantasía</label>
                <input type="text" name="nombre_fantasia" id="nombre_fantasia" value="{{ old('nombre_fantasia') }}" class="@error('nombre_fantasia') es-invalido @enderror">
                <small class="perfil-ayuda">Es el nombre con el que te conocen tus clientes.</small>
                @error('nombre_fantasia')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </fieldset>

    <fieldset class="perfil-bloque">
        <legend class="perfil-bloque-titulo">Dirección de la clínica</legend>

        <div class="perfil-fila">
            <div class="perfil-campo">
                <label for="id_region">Región</label>
                <select name="id_region" id="id_region" class="@error('id_region') es-invalido @enderror">
                    <option value="">Elige la región</option>
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
                <small class="perfil-ayuda">Escribe la calle y el número, por ejemplo: Los Alerces 120.</small>
                @error('direccion')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </fieldset>

    <fieldset class="perfil-bloque">
        <legend class="perfil-bloque-titulo">Contacto de la clínica</legend>

        <div class="perfil-fila">
            <div class="perfil-campo">
                <label for="telefono">Teléfono</label>
                <input type="tel" name="telefono" id="telefono" class="mask_telefono @error('telefono') es-invalido @enderror" value="{{ old('telefono') }}" placeholder="+56 9 1234 5678">
                @error('telefono')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>

            <div class="perfil-campo">
                <label for="email_contacto">Correo de contacto</label>
                <input type="email" name="email_contacto" id="email_contacto" value="{{ old('email_contacto') }}" class="@error('email_contacto') es-invalido @enderror">
                @error('email_contacto')
                    <small class="perfil-error" role="alert">{{ $message }}</small>
                @enderror
            </div>
        </div>
    </fieldset>
@endsection

@push('scripts')
    <script src="{{ asset('js/perfil/completar_perfil.js') }}?t={{ time() }}"></script>
@endpush
