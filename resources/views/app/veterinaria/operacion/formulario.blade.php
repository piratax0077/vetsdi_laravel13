@php
    $formId = $accionForm.'-'.($registroForm->id ?? 'nuevo');
    $recuperar = old('_formulario') === $formId;
    $enModal = $enModal ?? false;
@endphp
<form method="post" action="{{ route('vet.operacion.guardar',['accion'=>$accionForm]) }}" class="vet-operacion-form">
    @csrf
    <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
    <input type="hidden" name="_seccion" value="{{ $seccion }}">
    <input type="hidden" name="_formulario" value="{{ $formId }}">
    @isset($areaCentro)<input type="hidden" name="_area" value="{{ $areaCentro }}">@endisset
    @if(isset($registroForm->id))<input type="hidden" name="id" value="{{ $registroForm->id }}">@endif
    @foreach(($ocultos ?? []) as $nombre=>$valor)<input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">@endforeach
    @if($enModal)<div class="modal-body">@endif
    @if($accionForm === 'servicio' && ($seccion ?? null) === 'peluqueria')
    <div class="form-group">
        <label class="floating-label-activo-sm" for="{{ $formId }}-servicio-comun">Servicios comunes</label>
        <select id="{{ $formId }}-servicio-comun" class="form-control form-control-sm" data-servicio-comun aria-describedby="{{ $formId }}-servicio-ayuda">
            <option value="">Seleccione o escriba un servicio personalizado</option>
            @foreach(['Baño y secado','Baño y corte de pelo','Corte de pelo','Corte higiénico','Corte de uñas','Cepillado y deslanado','Desenredado de manto','Limpieza externa de oídos','Limpieza de almohadillas','Peluquería felina'] as $servicioComun)
            <option value="{{ $servicioComun }}">{{ $servicioComun }}</option>
            @endforeach
            <option value="__personalizado">Otro servicio (personalizado)</option>
        </select>
        <small id="{{ $formId }}-servicio-ayuda" class="form-text text-muted">La selección completa el nombre. Puede editarlo y definir duración y precio.</small>
    </div>
    @endif
    <div class="form-row">
        @foreach($camposForm as $campo)
        @php
            $nombre = $campo['nombre']; $tipo = $campo['tipo'];
            $valor = $recuperar ? old($nombre) : ($registroForm->{$nombre} ?? ($valores[$nombre] ?? ''));
            if ($tipo === 'datetime-local' && $valor) $valor = \Carbon\Carbon::parse($valor)->format('Y-m-d\TH:i');
            $opcional = str_contains($campo['etiqueta'],'opcional') || in_array($nombre,['resultado','egreso','observaciones_alta','fecha_pago','referencia']);
        @endphp
        <div class="{{ in_array($tipo,['textarea','checklist']) ? 'col-12' : 'col-md-6' }}"><div class="form-group">
            {{-- Las casillas no tienen un campo sobre el que flotar la etiqueta --}}
            <label class="{{ $tipo === 'checklist' ? '' : 'floating-label-activo-sm' }}" for="{{ $formId }}-{{ $nombre }}">{{ $campo['etiqueta'] }}</label>
            @if($accionForm === 'servicio' && ($seccion ?? '') === 'imagenologia' && $nombre === 'nombre')
            @php
                $examenesRadiologicos = \App\Services\CatalogoRadiologicoVeterinario::EXAMENES;
                $nombresRadiologicos = collect($examenesRadiologicos)->flatMap(fn($examenes,$especie)=>array_map(fn($examen)=>$especie.' · Radiografía de '.$examen,$examenes));
            @endphp
            <select class="form-control form-control-sm" id="{{ $formId }}-{{ $nombre }}" name="nombre" required>
                <option value="">Seleccione un examen por especie</option>
                @if($valor && !$nombresRadiologicos->contains($valor))
                <option value="{{ $valor }}" selected>{{ $valor }} (actual)</option>
                @endif
                @foreach($examenesRadiologicos as $especie=>$examenes)
                <optgroup label="{{ $especie }}">
                    @foreach($examenes as $examen)
                    @php($nombreExamen = $especie.' · Radiografía de '.$examen)
                    <option value="{{ $nombreExamen }}" @selected((string)$valor === $nombreExamen)>{{ $examen }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            @elseif($tipo === 'select')
            <select class="form-control form-control-sm" id="{{ $formId }}-{{ $nombre }}" name="{{ $nombre }}" @required(!$opcional)>
                <option value="">Seleccione</option>
                @foreach($campo['opciones'] as $key=>$etiqueta)<option value="{{ $key }}" @selected((string)$valor === (string)$key)>{{ $etiqueta }}</option>@endforeach
            </select>
            @elseif($tipo === 'textarea')
            <textarea class="form-control form-control-sm" name="{{ $nombre }}" id="{{ $formId }}-{{ $nombre }}" rows="3" maxlength="20000" @required(!$opcional)>{{ $valor }}</textarea>
            @elseif($tipo === 'checklist')
            <div class="d-flex flex-wrap">
                @foreach($campo['opciones'] as $key=>$etiqueta)
                <label class="floating-label-activo-sm mr-4"><input type="checkbox" name="{{ $nombre }}[]" value="{{ $key }}" @checked(in_array($key,(array)$valor))> {{ $etiqueta }}</label>
                @endforeach
            </div>
            @else
            <input class="form-control form-control-sm" name="{{ $nombre }}" id="{{ $formId }}-{{ $nombre }}" type="{{ $tipo }}" value="{{ $valor }}" @required(!$opcional) @if($tipo === 'number') min="0" step="{{ in_array($nombre,['peso','temperatura']) ? '0.001' : '1' }}" @endif @if($tipo === 'text') maxlength="255" @endif>
            @endif
        </div></div>
        @endforeach
    </div>
    @if($enModal)
    </div>
    <div class="modal-footer justify-content-end">
        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-info"><i class="feather icon-save mr-1"></i>Guardar {{ ($seccion ?? '') === 'laboratorio' && $accionForm === 'servicio' ? 'examen' : (isset($registroForm->id) ? 'cambios' : 'registro') }}</button>
    </div>
    @else
    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save mr-1"></i>Guardar {{ ($seccion ?? '') === 'laboratorio' && $accionForm === 'servicio' ? 'examen' : (isset($registroForm->id) ? 'cambios' : 'registro') }}</button>
    <a href="{{ $urlSeccion() }}" class="btn btn-outline-dark btn-sm">Cancelar</a>
    @endif
</form>
