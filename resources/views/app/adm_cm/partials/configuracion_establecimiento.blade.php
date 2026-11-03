@if($configuracion_establecimiento ?? null)
<div class="card" id="configuracion-establecimiento" style="scroll-margin-top:85px">
    <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-settings"></i></span>Configuración del establecimiento</h5></div>
    <div class="card-body">
        <p class="text-muted mb-4">Seleccione el tipo de establecimiento y sus áreas. Un centro puede habilitar observación u hospitalización. Los cambios conservan la historia registrada.</p>
        @if(session('establecimiento_guardado'))<div class="alert alert-success" role="status">{{ session('establecimiento_guardado') }}</div>@endif
        @if(old('_formulario') === 'establecimiento-perfil' && $errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @php($recuperarEstablecimiento = old('_formulario') === 'establecimiento-perfil')
        <form method="post" action="{{ route('vet.operacion.guardar',['accion'=>'configuracion']) }}">
            @csrf<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="_seccion" value="configuracion"><input type="hidden" name="_formulario" value="establecimiento-perfil">
            <div class="form-row pt-2">
                <div class="col-md-6"><div class="form-group"><label for="perfil-tipo-establecimiento" class="floating-label-activo-sm">Tipo de establecimiento</label><select id="perfil-tipo-establecimiento" name="tipo" class="form-control form-control-sm" required>@foreach(\App\Services\OperacionVeterinaria::TIPOS as $key=>$nombre)<option value="{{ $key }}" @selected(($recuperarEstablecimiento ? old('tipo') : $configuracion_establecimiento->tipo) === $key)>{{ $nombre }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group"><label for="perfil-hospitalizacion" class="floating-label-activo-sm">Hospitalización y observación</label><select id="perfil-hospitalizacion" name="hospitalizacion_habilitada" class="form-control form-control-sm" required>@foreach([1=>'Habilitada',0=>'Deshabilitada'] as $key=>$nombre)<option value="{{ $key }}" @selected((int)($recuperarEstablecimiento ? old('hospitalizacion_habilitada') : $configuracion_establecimiento->hospitalizacion_habilitada) === $key)>{{ $nombre }}</option>@endforeach</select></div></div>
                <div class="col-12"><fieldset class="form-group"><legend class="f-14">Áreas habilitadas</legend><div class="d-flex flex-wrap">@foreach(\App\Services\OperacionVeterinaria::SERVICIOS as $key=>$nombre)<label class="floating-label-activo-sm mr-3 mb-2"><input type="checkbox" name="servicios_habilitados[]" value="{{ $key }}" @checked(in_array($key,$recuperarEstablecimiento ? old('servicios_habilitados',[]) : $configuracion_establecimiento->servicios_habilitados))> {{ $nombre }}</label>@endforeach</div></fieldset></div>
            </div>
            <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save mr-1"></i>Guardar cambios</button>
            <a class="btn btn-outline-dark btn-sm" href="{{ route('adm_cm.configuracion',['id_institucion'=>$institucion->id]) }}#configuracion-establecimiento">Cancelar</a>
        </form>
    </div>
</div>
@endif
