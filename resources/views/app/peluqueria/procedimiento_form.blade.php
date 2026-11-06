{{-- Formulario de un servicio de peluquería; va dentro de su modal (agregar o editar) --}}
@php($formId = 'servicio-'.($procedimiento->id ?? 'nuevo'))
<form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.procedimientos.guardar') }}">@csrf
@if($procedimiento)<input type="hidden" name="id" value="{{ $procedimiento->id }}">@endif
<div class="modal-body">
<div class="form-row"><div class="form-group col-12"><label class="floating-label-activo-sm" for="{{ $formId }}-nombre">Servicio</label><input class="form-control form-control-sm" id="{{ $formId }}-nombre" name="nombre" value="{{ $procedimiento->nombre ?? '' }}" placeholder="Ej.: Corte de uñas" maxlength="150" required></div>
<div class="form-group col-md-4"><label class="floating-label-activo-sm" for="{{ $formId }}-duracion">Duración (minutos)</label><input class="form-control form-control-sm" id="{{ $formId }}-duracion" type="number" name="duracion" min="1" step="1" max="480" value="{{ $procedimiento->duracion ?? 30 }}" required></div><div class="form-group col-md-4"><label class="floating-label-activo-sm" for="{{ $formId }}-precio">Precio ($ CLP)</label><input class="form-control form-control-sm" id="{{ $formId }}-precio" type="number" name="precio" min="0" max="999999999" value="{{ $procedimiento->precio ?? 0 }}" required></div><div class="form-group col-md-4"><label class="floating-label-activo-sm" for="{{ $formId }}-activo">Disponibilidad</label><select class="form-control form-control-sm" id="{{ $formId }}-activo" name="activo"><option value="1" @selected(($procedimiento->activo??true))>Activo</option><option value="0" @selected($procedimiento && !$procedimiento->activo)>Inactivo</option></select></div></div>
<div class="form-group mb-0"><label class="floating-label-activo-sm" for="{{ $formId }}-indicaciones">Cuidados e indicaciones del servicio</label><textarea class="form-control caja-texto form-control-sm" id="{{ $formId }}-indicaciones" name="indicaciones" maxlength="3000" rows="3">{{ $procedimiento->indicaciones ?? '' }}</textarea></div>
</div>
<div class="modal-footer justify-content-end">
<button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
<button type="submit" class="btn btn-info"><i class="feather icon-save mr-1"></i>{{ $procedimiento?'Guardar cambios':'Guardar servicio' }}</button>
</div>
</form>
