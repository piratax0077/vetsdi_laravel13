@extends('template.usuario.template')

@section('page-styles')
<style>
    .genealogy-shell{--tree-primary:#178b82;--tree-accent:#12b8ba;--tree-line:#9baebf}
    .genealogy-card{border:0;border-radius:14px;overflow:hidden;box-shadow:0 7px 18px rgba(30,51,70,.10)}
    .genealogy-card>.card-header{background:var(--tree-accent);border:0;padding:14px 18px}
    .genealogy-tree{text-align:center;padding:22px 16px 28px;overflow-x:auto}
    .genealogy-generation{display:flex;justify-content:center;gap:18px;min-width:720px}
    .genealogy-node{width:170px;min-height:164px;background:#fff;border:2px solid #dfe5ec;border-radius:14px;padding:12px;box-shadow:0 7px 18px rgba(0,0,0,.07)}
    .genealogy-node.main{background:var(--tree-primary);color:#fff;border-color:var(--tree-primary)}
    .genealogy-photo{width:82px;height:82px;margin:0 auto 8px;border-radius:50%;object-fit:cover;border:4px solid #eef3f7;background:#eef3f7}
    .genealogy-photo.is-viewable{cursor:zoom-in;transition:transform .18s ease,box-shadow .18s ease}
    .genealogy-photo.is-viewable:hover{transform:scale(1.06);box-shadow:0 5px 15px rgba(0,0,0,.2)}
    .genealogy-node.main .genealogy-photo{border-color:rgba(255,255,255,.72)}
    .genealogy-photo-empty{display:flex;align-items:center;justify-content:center;font-size:30px;color:#8ca0b2}
    .genealogy-label{font-size:12px;color:#6c757d;margin-bottom:4px}.genealogy-node.main .genealogy-label{color:#dff7f4}
    .genealogy-name{display:block;line-height:1.2;word-break:break-word}
    .genealogy-species{display:inline-block;margin-top:7px;padding:3px 9px;border-radius:12px;background:#e8f7f6;color:#167c75;font-size:11px;font-weight:700}
    .genealogy-node.main .genealogy-species{background:rgba(255,255,255,.2);color:#fff}
    .genealogy-connector{width:4px;height:28px;background:var(--tree-line);margin:8px auto;border-radius:8px}
    .genealogy-form .card-header{padding:14px 18px}.genealogy-form label{font-weight:600}
    .family-field{height:100%;padding:12px;border:1px solid #e0e6ed;border-radius:10px;background:#fbfcfd}
    .photo-picker{display:flex;align-items:center;gap:10px;margin-top:9px}
    .photo-preview{width:52px;height:52px;border-radius:50%;object-fit:cover;background:#edf2f5;border:2px solid #dfe6ec}
    .photo-preview.empty{display:flex;align-items:center;justify-content:center;color:#8797a7}
    .photo-picker input{font-size:12px;max-width:calc(100% - 62px)}
    .main-photo-box{display:flex;align-items:center;gap:16px;padding:14px;border:1px dashed #15aeb0;border-radius:12px;background:#f2ffff}
    .main-photo-box .photo-preview{width:86px;height:86px}
    .genealogy-viewer{display:none;position:fixed;z-index:1080;inset:0;background:rgba(8,20,29,.9);align-items:center;justify-content:center;padding:28px}
    .genealogy-viewer.open{display:flex}
    .genealogy-viewer-dialog{position:relative;width:min(940px,92vw);text-align:center}
    .genealogy-viewer-image{display:block;max-width:100%;max-height:76vh;margin:auto;border-radius:12px;object-fit:contain;background:#fff}
    .genealogy-viewer-caption{color:#fff;font-size:17px;font-weight:600;margin-top:13px}
    .genealogy-viewer-close,.genealogy-viewer-nav{position:absolute;border:0;color:#fff;background:rgba(16,164,163,.88);cursor:pointer;box-shadow:0 3px 12px rgba(0,0,0,.25)}
    .genealogy-viewer-close{right:-14px;top:-14px;width:42px;height:42px;border-radius:50%;font-size:25px}
    .genealogy-viewer-nav{top:45%;width:46px;height:58px;border-radius:9px;font-size:26px}
    .genealogy-viewer-prev{left:-60px}.genealogy-viewer-next{right:-60px}
    .genealogy-viewer-counter{display:block;color:#cce9e7;font-size:12px;margin-top:4px}
    @media(max-width:767px){.genealogy-form .btn{margin-bottom:8px}.genealogy-viewer-prev{left:4px}.genealogy-viewer-next{right:4px}}
</style>
@endsection

@section('content')
@php
    $g = $mascota->genealogia;
    $nombre = fn($relacion, $externo, $vacio) => optional(optional($g)->{$relacion})->nombre ?? optional($g)->{$externo} ?? $vacio;
    $fotoUrl = function ($ruta) {
        if (!$ruta) return null;
        if (\Illuminate\Support\Str::startsWith($ruta, ['http://', 'https://', '/'])) return $ruta;
        return asset('storage/' . ltrim($ruta, '/'));
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
        ['abueloPaterno','abuelo_paterno_nombre','abuelo_paterno_foto','abuelo_paterno_especie','Abuelo paterno','No registrado'],
        ['abuelaPaterna','abuela_paterna_nombre','abuela_paterna_foto','abuela_paterna_especie','Abuela paterna','No registrada'],
        ['abueloMaterno','abuelo_materno_nombre','abuelo_materno_foto','abuelo_materno_especie','Abuelo materno','No registrado'],
        ['abuelaMaterna','abuela_materna_nombre','abuela_materna_foto','abuela_materna_especie','Abuela materna','No registrada'],
        ['padre','padre_nombre','padre_foto','padre_especie','Padre','No registrado'],
        ['madre','madre_nombre','madre_foto','madre_especie','Madre','No registrada'],
    ];
@endphp
<div class="pcoded-main-container genealogy-shell"><div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <h5 class="font-weight-bold mb-2 mb-md-0">Genealogía de {{ $mascota->nombre }}</h5>
                <div class="d-flex flex-wrap genealogy-header-actions">
                    <a href="{{ route('paciente.dependiente.home', ['id_dependiente_activo' => $mascota->id]) }}" class="btn btn-outline-light btn-sm mr-2 mb-1">
                        <i class="feather icon-arrow-left mr-1"></i> Volver al escritorio de {{ $mascota->nombre }}
                    </a>
                    <a href="{{ route('mascotas.genealogia.index') }}" class="btn btn-light btn-sm mb-1">
                        <i class="fas fa-paw mr-1"></i> Ver mis mascotas
                    </a>
                </div>
            </div>
        </div>
    </div>
    @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="card genealogy-card">
        <div class="card-header"><h5 class="text-white mb-0"><i class="fas fa-sitemap"></i> Árbol genealógico</h5></div>
        <div class="card-body genealogy-tree">
            <div class="genealogy-generation">
                @foreach(array_slice($familiares, 0, 4) as [$relacion,$campoNombre,$campoFoto,$campoEspecie,$etiqueta,$vacio])
                    @php($url = $foto($relacion, $campoFoto))
                    @php($especie = $tipoEspecie(optional($g)->{$relacion}, optional($g)->{$campoEspecie}))
                    <div class="genealogy-node">
                        @if($url)<img class="genealogy-photo is-viewable js-genealogy-viewer" src="{{ $url }}" data-caption="{{ $nombre($relacion,$campoNombre,$vacio) }} · {{ $especie }}" alt="{{ $etiqueta }}">
                        @else<div class="genealogy-photo genealogy-photo-empty"><i class="fas fa-paw"></i></div>@endif
                        <div class="genealogy-label">{{ $etiqueta }}</div>
                        <strong class="genealogy-name">{{ $nombre($relacion,$campoNombre,$vacio) }}</strong>
                        <span class="genealogy-species">{{ $especie }}</span>
                    </div>
                @endforeach
            </div>
            <div class="genealogy-connector"></div>
            <div class="genealogy-generation">
                @foreach(array_slice($familiares, 4, 2) as [$relacion,$campoNombre,$campoFoto,$campoEspecie,$etiqueta,$vacio])
                    @php($url = $foto($relacion, $campoFoto))
                    @php($especie = $tipoEspecie(optional($g)->{$relacion}, optional($g)->{$campoEspecie}))
                    <div class="genealogy-node">
                        @if($url)<img class="genealogy-photo is-viewable js-genealogy-viewer" src="{{ $url }}" data-caption="{{ $nombre($relacion,$campoNombre,$vacio) }} · {{ $especie }}" alt="{{ $etiqueta }}">
                        @else<div class="genealogy-photo genealogy-photo-empty"><i class="fas fa-paw"></i></div>@endif
                        <div class="genealogy-label">{{ $etiqueta }}</div>
                        <strong class="genealogy-name">{{ $nombre($relacion,$campoNombre,$vacio) }}</strong>
                        <span class="genealogy-species">{{ $especie }}</span>
                    </div>
                @endforeach
            </div>
            <div class="genealogy-connector"></div>
            <div class="genealogy-generation">
                <div class="genealogy-node main">
                    @if($fotoUrl($mascota->foto_perfil))
                        <img class="genealogy-photo is-viewable js-genealogy-viewer" src="{{ $fotoUrl($mascota->foto_perfil) }}" data-caption="{{ $mascota->nombre }} · {{ $tipoEspecie($mascota) }}" alt="{{ $mascota->nombre }}">
                    @else<div class="genealogy-photo genealogy-photo-empty"><i class="fas fa-paw"></i></div>@endif
                    <div class="genealogy-label">Mascota</div><strong class="genealogy-name">{{ $mascota->nombre }}</strong>
                    <span class="genealogy-species">{{ $tipoEspecie($mascota) }}</span>
                </div>
            </div>
            @if($crias->isNotEmpty())
                <div class="genealogy-connector"></div>
                <div class="genealogy-generation">
                    @foreach($crias as $cria)
                        @php($criaMascota = $cria->mascota)
                        <div class="genealogy-node">
                            @if($criaMascota && $fotoUrl($criaMascota->foto_perfil))
                                <img class="genealogy-photo is-viewable js-genealogy-viewer" src="{{ $fotoUrl($criaMascota->foto_perfil) }}" data-caption="{{ $criaMascota->nombre }} · {{ $tipoEspecie($criaMascota) }}" alt="{{ $criaMascota->nombre }}">
                            @else<div class="genealogy-photo genealogy-photo-empty"><i class="fas fa-paw"></i></div>@endif
                            <div class="genealogy-label">Cría</div><strong class="genealogy-name">{{ optional($criaMascota)->nombre ?? 'Sin nombre' }}</strong>
                            <span class="genealogy-species">{{ $tipoEspecie($criaMascota) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" action="{{ route('mascotas.genealogia.store', $mascota) }}">
        @csrf
        <div class="card genealogy-card genealogy-form">
            <div class="card-header bg-white"><h5 class="mb-0">Crear/Editar genealogía y fotografías</h5></div>
            <div class="card-body">
                <div class="main-photo-box mb-4">
                    @if($fotoUrl($mascota->foto_perfil))
                        <img class="photo-preview" data-preview="foto_mascota" src="{{ $fotoUrl($mascota->foto_perfil) }}" alt="Foto de {{ $mascota->nombre }}">
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
                    @foreach([
                        ['id'=>'padre_id','nombre'=>'padre_nombre','foto'=>'padre_foto','especie'=>'padre_especie','etiqueta'=>'Padre','relacion'=>'padre'],
                        ['id'=>'madre_id','nombre'=>'madre_nombre','foto'=>'madre_foto','especie'=>'madre_especie','etiqueta'=>'Madre','relacion'=>'madre'],
                        ['id'=>'abuelo_paterno_id','nombre'=>'abuelo_paterno_nombre','foto'=>'abuelo_paterno_foto','especie'=>'abuelo_paterno_especie','etiqueta'=>'Abuelo paterno','relacion'=>'abueloPaterno'],
                        ['id'=>'abuela_paterna_id','nombre'=>'abuela_paterna_nombre','foto'=>'abuela_paterna_foto','especie'=>'abuela_paterna_especie','etiqueta'=>'Abuela paterna','relacion'=>'abuelaPaterna'],
                        ['id'=>'abuelo_materno_id','nombre'=>'abuelo_materno_nombre','foto'=>'abuelo_materno_foto','especie'=>'abuelo_materno_especie','etiqueta'=>'Abuelo materno','relacion'=>'abueloMaterno'],
                        ['id'=>'abuela_materna_id','nombre'=>'abuela_materna_nombre','foto'=>'abuela_materna_foto','especie'=>'abuela_materna_especie','etiqueta'=>'Abuela materna','relacion'=>'abuelaMaterna']
                    ] as $campo)
                        <div class="col-lg-4 col-md-6 mb-3"><div class="family-field">
                            <div class="form-group mb-2">
                                <label class="floating-label-activo-sm">{{ $campo['etiqueta'] }} registrado</label>
                                <select name="{{ $campo['id'] }}" class="form-control"><option value="">Seleccione</option>
                                    @foreach($mascotas as $opcion)<option value="{{ $opcion->id }}" @selected(old($campo['id'], optional($g)->{$campo['id']}) == $opcion->id)>{{ $opcion->nombre }}</option>@endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="floating-label-activo-sm">{{ $campo['etiqueta'] }} externo</label>
                                <input type="text" name="{{ $campo['nombre'] }}" class="form-control" maxlength="255" value="{{ old($campo['nombre'], optional($g)->{$campo['nombre']}) }}">
                            </div>
                            <div class="form-group mb-2">
                                <label class="floating-label-activo-sm">Especie del familiar externo</label>
                                <select name="{{ $campo['especie'] }}" class="form-control">
                                    <option value="">Seleccione</option>
                                    @foreach(['Canino','Felino','Otro'] as $opcionEspecie)
                                        <option value="{{ $opcionEspecie }}" @selected(old($campo['especie'], optional($g)->{$campo['especie']}) === $opcionEspecie)>{{ $opcionEspecie }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="photo-picker">
                                @if($foto($campo['relacion'], $campo['foto']))
                                    <img class="photo-preview" data-preview="{{ $campo['foto'] }}" src="{{ $foto($campo['relacion'], $campo['foto']) }}" alt="{{ $campo['etiqueta'] }}">
                                @else
                                    <div class="photo-preview empty" data-preview="{{ $campo['foto'] }}"><i class="fas fa-camera"></i></div>
                                @endif
                                <input type="file" name="{{ $campo['foto'] }}" class="form-control-file genealogy-photo-input" accept="image/jpeg,image/png,image/webp">
                            </div>
                            <small class="text-muted">Para familiar externo. Si está registrado se usa su foto de perfil.</small>
                        </div></div>
                    @endforeach
                </div>

                <div class="row">
                    <div class="col-md-4 form-group"><label>Número de registro</label><input type="text" name="numero_registro" class="form-control" value="{{ old('numero_registro', optional($g)->numero_registro) }}"></div>
                    <div class="col-md-4 form-group"><label>Criador</label><input type="text" name="criador" class="form-control" value="{{ old('criador', optional($g)->criador) }}"></div>
                    <div class="col-md-4 form-group"><label>Observaciones</label><textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', optional($g)->observaciones) }}</textarea></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><button class="btn btn-info btn-block" type="submit"><i class="fas fa-save"></i> Guardar genealogía</button></div>
                    <div class="col-md-4"><a class="btn btn-success btn-block" target="_blank" href="{{ route('mascotas.genealogia.certificado', $mascota) }}"><i class="fas fa-certificate"></i> Ver certificado</a></div>
                    <div class="col-md-4"><a class="btn btn-secondary btn-block" href="{{ route('paciente.mascotas.index') }}">Volver a mis mascotas</a></div>
                </div>
            </div>
        </div>
    </form>
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
<script>
document.querySelectorAll('.genealogy-photo-input').forEach(function (input) {
    input.addEventListener('change', function () {
        var file = this.files && this.files[0], preview = document.querySelector('[data-preview="' + this.name + '"]');
        if (!file || !preview) return;
        var reader = new FileReader();
        reader.onload = function (event) {
            if (preview.tagName !== 'IMG') {
                var image = document.createElement('img');
                image.className = preview.className.replace(' empty', '');
                image.dataset.preview = preview.dataset.preview;
                image.alt = 'Vista previa';
                preview.replaceWith(image);
                preview = image;
            }
            preview.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
});

(function () {
    var photos = Array.prototype.slice.call(document.querySelectorAll('.js-genealogy-viewer'));
    var viewer = document.getElementById('genealogyViewer');
    if (!viewer || !photos.length) return;
    var image = viewer.querySelector('.genealogy-viewer-image');
    var caption = viewer.querySelector('.genealogy-viewer-caption');
    var counter = viewer.querySelector('.genealogy-viewer-counter');
    var current = 0;

    function show(index) {
        current = (index + photos.length) % photos.length;
        image.src = photos[current].src;
        image.alt = photos[current].alt || 'Fotografía de mascota';
        caption.textContent = photos[current].dataset.caption || image.alt;
        counter.textContent = (current + 1) + ' de ' + photos.length;
        viewer.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeViewer() {
        viewer.classList.remove('open');
        document.body.style.overflow = '';
    }
    photos.forEach(function (photo, index) {
        photo.addEventListener('click', function () { show(index); });
    });
    viewer.querySelector('.genealogy-viewer-close').addEventListener('click', closeViewer);
    viewer.querySelector('.genealogy-viewer-prev').addEventListener('click', function () { show(current - 1); });
    viewer.querySelector('.genealogy-viewer-next').addEventListener('click', function () { show(current + 1); });
    viewer.addEventListener('click', function (event) { if (event.target === viewer) closeViewer(); });
    document.addEventListener('keydown', function (event) {
        if (!viewer.classList.contains('open')) return;
        if (event.key === 'Escape') closeViewer();
        if (event.key === 'ArrowLeft') show(current - 1);
        if (event.key === 'ArrowRight') show(current + 1);
    });
})();

window.setTimeout(function () {
    document.querySelectorAll('.alert-success').forEach(function (alert) {
        alert.style.transition = 'opacity .35s ease';
        alert.style.opacity = '0';
        window.setTimeout(function () { alert.remove(); }, 400);
    });
}, 3500);
</script>
@endsection
