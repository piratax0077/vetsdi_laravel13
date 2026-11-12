<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Firmar consentimiento veterinario</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header encabezado-card">
                        <img src="{{ asset('images/vet-color-h.svg') }}" width="145" alt="Veterchile">
                    </div>
                    <div class="card-body">
                        <h1 class="f-20">{{ $titulo }}</h1>
                        @if(session('firma_consulta'))
                            <div class="alert {{ session('firma_consulta.estado') ? 'alert-success' : 'alert-danger' }}" role="status">{{ session('firma_consulta.msj') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                        @endif
                        <a class="btn btn-info-light-c" href="{{ $archivo }}" target="_blank" rel="noopener noreferrer">{{ $firmado ? 'Ver consentimiento firmado' : 'Ver consentimiento' }}</a>
                        @if($original)
                            <a class="btn btn-info-light-c" href="{{ $original }}" target="_blank" rel="noopener noreferrer">Ver archivo original</a>
                        @endif
                        @if(!$firmado)
                            @if(!$codigoVigente)
                                <form method="post" action="{{ request()->fullUrl() }}" class="mt-4">
                                    @csrf
                                    <input type="hidden" name="accion" value="codigo">
                                    <p>Revisa el consentimiento. Para firmar, solicita un código al correo registrado del tutor.</p>
                                    <button type="submit" class="btn btn-info">Enviar código</button>
                                </form>
                            @else
                                <form method="post" action="{{ request()->fullUrl() }}" class="mt-4">
                                    @csrf
                                    <input type="hidden" name="accion" value="firmar">
                                    <div class="form-group">
                                        <label for="codigo">Código de 6 dígitos</label>
                                        <input class="form-control" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                                    </div>
                                    <div class="form-group">
                                        <label><input type="checkbox" name="acepta" value="1" required> Revisé el consentimiento y autorizo el procedimiento veterinario.</label>
                                    </div>
                                    <button type="submit" class="btn btn-purple">Firmar y autorizar</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
