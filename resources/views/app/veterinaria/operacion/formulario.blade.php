@php
    $formId = $accionForm.'-'.($registroForm->id ?? 'nuevo');
    $recuperar = old('_formulario') === $formId;
@endphp
<form method="post" action="{{ route('vet.operacion.guardar',['accion'=>$accionForm]) }}" class="vet-operacion-form">
    @csrf
    <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
    <input type="hidden" name="_seccion" value="{{ $seccion }}">
    <input type="hidden" name="_formulario" value="{{ $formId }}">
    @if(isset($registroForm->id))<input type="hidden" name="id" value="{{ $registroForm->id }}">@endif
    @foreach(($ocultos ?? []) as $nombre=>$valor)<input type="hidden" name="{{ $nombre }}" value="{{ $valor }}">@endforeach
    <div class="form-row">
        @foreach($camposForm as $campo)
        @php
            $nombre = $campo['nombre']; $tipo = $campo['tipo'];
            $valor = $recuperar ? old($nombre) : ($registroForm->{$nombre} ?? ($valores[$nombre] ?? ''));
            if ($tipo === 'datetime-local' && $valor) $valor = \Carbon\Carbon::parse($valor)->format('Y-m-d\TH:i');
            $opcional = str_contains($campo['etiqueta'],'opcional') || in_array($nombre,['resultado','egreso','observaciones_alta','fecha_pago','referencia']);
        @endphp
        <div class="form-group {{ in_array($tipo,['textarea','checklist']) ? 'col-12' : 'col-md-6' }}">
            <label for="{{ $formId }}-{{ $nombre }}">{{ $campo['etiqueta'] }}</label>
            @if($tipo === 'select')
            <select class="form-control form-control-sm" id="{{ $formId }}-{{ $nombre }}" name="{{ $nombre }}" @required(!$opcional)>
                <option value="">Seleccione</option>
                @foreach($campo['opciones'] as $key=>$etiqueta)<option value="{{ $key }}" @selected((string)$valor === (string)$key)>{{ $etiqueta }}</option>@endforeach
            </select>
            @elseif($tipo === 'textarea')
            <textarea class="form-control form-control-sm" name="{{ $nombre }}" id="{{ $formId }}-{{ $nombre }}" rows="3" maxlength="20000" @required(!$opcional)>{{ $valor }}</textarea>
            @elseif($tipo === 'checklist')
            <div class="d-flex flex-wrap">
                @foreach($campo['opciones'] as $key=>$etiqueta)
                <label class="mr-4"><input type="checkbox" name="{{ $nombre }}[]" value="{{ $key }}" @checked(in_array($key,(array)$valor))> {{ $etiqueta }}</label>
                @endforeach
            </div>
            @else
            <input class="form-control form-control-sm" name="{{ $nombre }}" id="{{ $formId }}-{{ $nombre }}" type="{{ $tipo }}" value="{{ $valor }}" @required(!$opcional) @if($tipo === 'number') min="0" step="{{ in_array($nombre,['peso','temperatura']) ? '0.001' : '1' }}" @endif @if($tipo === 'text') maxlength="255" @endif>
            @endif
        </div>
        @endforeach
    </div>
    <button type="submit" class="btn btn-info btn-sm"><i class="feather icon-save mr-1"></i>Guardar {{ isset($registroForm->id) ? 'cambios' : 'registro' }}</button>
    <a href="{{ route('vet.operacion.index',$ctx+['seccion'=>$seccion]) }}" class="btn btn-outline-dark btn-sm">Cancelar</a>
</form>
