<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificado geneal&oacute;gico - {{ $mascota->nombre }}</title>
    <style>
        *{box-sizing:border-box}
        :root{--primary:#178b82;--accent:#12b8ba;--ink:#263b50;--muted:#6f8091;--line:#9baebf}
        body{margin:0;background:#edf2f5;color:var(--ink);font-family:Arial,sans-serif}
        .certificate{max-width:1060px;margin:24px auto;padding:34px 38px;background:#fff;border:4px solid var(--primary);border-radius:22px}
        .center{text-align:center}.muted{color:var(--muted)}
        .certificate-header{padding-bottom:16px;border-bottom:1px solid #d7e0e6}
        .certificate-header h1{margin:0 0 9px;font-size:30px}
        .certificate-header h2{margin:0 0 8px;font-size:23px}
        .certificate-header p{margin:0}
        .pet-summary{display:flex;align-items:center;justify-content:center;gap:18px;margin:20px 0;padding:15px;border-radius:15px;background:#eef9f8}
        .pet-photo,.family-photo{display:block;border-radius:50%;object-fit:cover;background:#e9eff3}
        .pet-photo{width:94px;height:94px;border:4px solid #fff;box-shadow:0 4px 13px rgba(23,139,130,.2)}
        .pet-photo-empty,.family-photo-empty{display:flex;align-items:center;justify-content:center;color:#8598a8}
        .pet-photo-empty{font-size:34px}
        .pet-summary h2{margin:0 0 7px;font-size:23px}
        .pet-meta{margin:3px 0;font-size:14px}
        .tree{padding:5px 0 12px;text-align:center;overflow:hidden}
        .generation{display:grid;gap:14px}
        .generation.four{grid-template-columns:repeat(4,1fr)}
        .generation.two{grid-template-columns:repeat(2,1fr);max-width:760px;margin:auto}
        .family-card{min-height:174px;padding:12px 10px;border:1px solid #d9e3e9;border-radius:14px;background:#fff}
        .family-photo{width:72px;height:72px;margin:0 auto 8px;border:3px solid #e9f0f3}
        .family-photo-empty{font-size:26px}
        .family-role{display:block;margin-bottom:4px;color:var(--muted);font-size:12px}
        .family-name{display:block;line-height:1.25;overflow-wrap:anywhere}
        .species{display:inline-block;margin-top:7px;padding:3px 9px;border-radius:12px;background:#e8f7f6;color:#167c75;font-size:11px;font-weight:bold}
        .connector{width:4px;height:25px;margin:7px auto;background:var(--line);border-radius:5px}
        .certified-pet{max-width:480px;margin:auto;padding:14px;border-radius:14px;background:var(--primary);color:#fff}
        .certified-pet small{display:block;color:#dff7f4}.certified-pet h2{margin:7px 0 0}
        .notes{margin-top:18px;padding-top:14px;border-top:1px solid #d7e0e6}
        .notes h3{margin:0 0 8px}.notes p{margin:0;white-space:pre-wrap}
        footer{margin-top:20px;font-size:13px}
        .actions{text-align:center;margin:20px}.btn{display:inline-block;border:0;border-radius:7px;padding:11px 18px;color:#fff;text-decoration:none;cursor:pointer;font-size:15px}.primary{background:var(--primary)}.secondary{background:#65727d}
        @media(max-width:760px){
            .certificate{margin:8px;padding:22px 14px}
            .generation.four{grid-template-columns:repeat(2,1fr)}
            .pet-summary{align-items:center}
        }
        @media print{
            @page{size:A4 portrait;margin:6mm}
            body{background:#fff}
            .certificate{max-width:none;margin:0;padding:11px 16px;border-width:2px;border-radius:12px;break-inside:avoid;page-break-inside:avoid}
            .certificate-header{padding-bottom:7px}.certificate-header h1{margin-bottom:3px;font-size:21px}.certificate-header h2{margin-bottom:3px;font-size:17px}.certificate-header p{font-size:11px}
            .pet-summary{gap:12px;margin:8px 0;padding:7px}.pet-summary h2{margin-bottom:2px;font-size:17px}.pet-meta{margin:1px 0;font-size:10px}
            .tree{padding:2px 0 4px}.generation{gap:8px}.family-card{min-height:106px;padding:6px 7px;border-radius:9px}.family-photo{width:43px;height:43px;margin-bottom:3px;border-width:2px}.family-role{margin-bottom:2px;font-size:8px}.family-name{font-size:10px}.species{margin-top:3px;padding:2px 6px;font-size:8px}
            .pet-photo{width:58px;height:58px;border-width:2px}.connector{width:3px;height:10px;margin:3px auto}.certified-pet{max-width:400px;padding:7px}.certified-pet small{font-size:8px}.certified-pet h2{margin-top:2px;font-size:15px}
            .notes{margin-top:7px;padding-top:6px}.notes h3{margin-bottom:3px;font-size:12px}.notes p{font-size:9px;line-height:1.25;max-height:34px;overflow:hidden}footer{margin-top:7px;font-size:9px}
            .no-print{display:none}
        }
    </style>
</head>
<body>
@php
    $g = $mascota->genealogia;
    $fotoUrl = function ($ruta) {
        if (!$ruta) return null;
        if (\Illuminate\Support\Str::startsWith($ruta, ['http://', 'https://', '/'])) return $ruta;
        return asset('storage/' . ltrim($ruta, '/'));
    };
    $nombre = function ($relacion, $externo) use ($g) {
        return optional(optional($g)->{$relacion})->nombre
            ?: optional($g)->{$externo}
            ?: 'Sin nombre registrado';
    };
    $foto = function ($relacion, $campoFoto) use ($g, $fotoUrl) {
        $registrada = optional(optional($g)->{$relacion})->foto_perfil;
        return $fotoUrl($registrada ?: optional($g)->{$campoFoto});
    };
    $tipoEspecie = function ($animal, $externa = null) {
        if ($animal) {
            $original = optional($animal->especieMascota)->nombre ?: $animal->otra_especie;
            $normalizada = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $original));
            if (\Illuminate\Support\Str::contains($normalizada, ['canin', 'perro'])) return 'Canino';
            if (\Illuminate\Support\Str::contains($normalizada, ['felin', 'gato'])) return 'Felino';
            return $original ?: 'Otro';
        }
        return $externa ?: 'Especie no indicada';
    };
    $familiares = [
        ['abueloPaterno','abuelo_paterno_nombre','abuelo_paterno_foto','abuelo_paterno_especie','Abuelo paterno'],
        ['abuelaPaterna','abuela_paterna_nombre','abuela_paterna_foto','abuela_paterna_especie','Abuela paterna'],
        ['abueloMaterno','abuelo_materno_nombre','abuelo_materno_foto','abuelo_materno_especie','Abuelo materno'],
        ['abuelaMaterna','abuela_materna_nombre','abuela_materna_foto','abuela_materna_especie','Abuela materna'],
        ['padre','padre_nombre','padre_foto','padre_especie','Padre'],
        ['madre','madre_nombre','madre_foto','madre_especie','Madre'],
    ];
@endphp
<main class="certificate">
    <header class="certificate-header center">
        <h1>VET SDI &middot; VETERCHILE</h1>
        <h2>Certificado geneal&oacute;gico de mascota</h2>
        <p class="muted">Pedigr&iacute; digital y registro familiar</p>
    </header>

    <section class="pet-summary">
        @if($fotoUrl($mascota->foto_perfil))
            <img class="pet-photo" src="{{ $fotoUrl($mascota->foto_perfil) }}" alt="{{ $mascota->nombre }}">
        @else
            <div class="pet-photo pet-photo-empty">&#128062;</div>
        @endif
        <div>
            <h2>{{ $mascota->nombre }}</h2>
            <p class="pet-meta"><strong>{{ $tipoEspecie($mascota) }}</strong></p>
            <p class="pet-meta">Registro: <strong>{{ optional($g)->numero_registro ?: 'Sin registro' }}</strong></p>
            <p class="pet-meta">Criador: <strong>{{ optional($g)->criador ?: 'No informado' }}</strong></p>
        </div>
    </section>

    <section class="tree">
        <div class="generation four">
            @foreach(array_slice($familiares, 0, 4) as [$relacion,$campoNombre,$campoFoto,$campoEspecie,$etiqueta])
                @php($url = $foto($relacion, $campoFoto))
                <div class="family-card">
                    @if($url)
                        <img class="family-photo" src="{{ $url }}" alt="{{ $etiqueta }}">
                    @else
                        <div class="family-photo family-photo-empty">&#128062;</div>
                    @endif
                    <small class="family-role">{{ $etiqueta }}</small>
                    <strong class="family-name">{{ $nombre($relacion, $campoNombre) }}</strong>
                    <span class="species">{{ $tipoEspecie(optional($g)->{$relacion}, optional($g)->{$campoEspecie}) }}</span>
                </div>
            @endforeach
        </div>
        <div class="connector"></div>
        <div class="generation two">
            @foreach(array_slice($familiares, 4, 2) as [$relacion,$campoNombre,$campoFoto,$campoEspecie,$etiqueta])
                @php($url = $foto($relacion, $campoFoto))
                <div class="family-card">
                    @if($url)
                        <img class="family-photo" src="{{ $url }}" alt="{{ $etiqueta }}">
                    @else
                        <div class="family-photo family-photo-empty">&#128062;</div>
                    @endif
                    <small class="family-role">{{ $etiqueta }}</small>
                    <strong class="family-name">{{ $nombre($relacion, $campoNombre) }}</strong>
                    <span class="species">{{ $tipoEspecie(optional($g)->{$relacion}, optional($g)->{$campoEspecie}) }}</span>
                </div>
            @endforeach
        </div>
        <div class="connector"></div>
        <div class="certified-pet">
            <small>Mascota certificada</small>
            <h2>{{ $mascota->nombre }} &middot; {{ $tipoEspecie($mascota) }}</h2>
        </div>
    </section>

    <section class="notes">
        <h3>Observaciones</h3>
        <p>{{ optional($g)->observaciones ?: 'Sin observaciones.' }}</p>
    </section>
    <footer class="center muted">Emitido digitalmente por VET SDI &middot; VETERCHILE</footer>
</main>
<div class="actions no-print">
    <button class="btn primary" onclick="window.print()">Imprimir / Guardar PDF</button>
    <a class="btn secondary" href="{{ route('mascotas.genealogia.show', $mascota) }}">Volver</a>
</div>
</body>
</html>
