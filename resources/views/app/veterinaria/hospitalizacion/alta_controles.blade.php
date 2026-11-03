<div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Alta y controles</h5></div><div class="card-body">
@if($internacion->estado === 'alta')
<p><strong>Alta:</strong> {{ $internacion->egreso }}</p><p style="white-space:pre-wrap">{{ $internacion->observaciones_alta }}</p>
<a class="btn btn-info mb-3" target="_blank" rel="noopener" href="{{ route('vet.hospital.profesional.carne',['id_institucion'=>$institucion->id,'internacion'=>$internacion->id]) }}">Carné de alta / imprimir</a>
@elseif($internacion->estado === 'hospitalizada')
<form method="post" action="{{ route('vet.hospital.profesional.alta') }}" class="mb-4">@csrf
<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="internacion_id" value="{{ $internacion->id }}">
<div class="form-group"><label class="floating-label-activo-sm" for="egreso-fecha">Fecha y hora del alta</label><input id="egreso-fecha" class="form-control" type="datetime-local" name="egreso" value="{{ old('egreso',now()->format('Y-m-d\TH:i')) }}" required></div>
<div class="form-group"><label class="floating-label-activo-sm" for="egreso-resumen">Resumen del alta y diagnóstico de egreso</label><textarea id="egreso-resumen" class="form-control" name="observaciones_alta" maxlength="10000" required>{{ old('observaciones_alta') }}</textarea></div>
<div class="form-group"><label class="floating-label-activo-sm" for="egreso-indicaciones">Tratamiento, cuidados en casa y signos de alarma</label><textarea id="egreso-indicaciones" class="form-control" name="indicaciones" maxlength="20000" required>{{ old('indicaciones',$internacion->indicaciones) }}</textarea></div>
<button class="btn btn-info" type="submit">Registrar alta</button>
</form>
@endif
<h6>Citaciones a control</h6>
<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Motivo</th><th>Estado / resultado</th><th>Seguimiento</th></tr></thead><tbody>
@forelse($controles as $control)<tr><td>{{ $control->fecha }}</td><td>{{ $control->motivo }}</td><td>{{ $control->estado }}<br>{{ $control->resultado }}</td><td>
@if($control->estado === 'programado')<form method="post" action="{{ route('vet.hospital.profesional.control') }}">@csrf
<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="internacion_id" value="{{ $internacion->id }}"><input type="hidden" name="control_id" value="{{ $control->id }}">
<select class="form-control form-control-sm mb-2" name="estado" aria-label="Estado del control" required><option value="realizado">Realizado</option><option value="cancelado">Cancelado</option><option value="no_asiste">No asiste</option></select>
<textarea class="form-control mb-2" name="resultado" maxlength="10000" aria-label="Resultado del control" placeholder="Resultado (obligatorio al realizar)"></textarea><button class="btn btn-outline-info btn-sm">Actualizar control</button></form>@endif
</td></tr>@empty<tr><td colspan="4">Sin controles programados.</td></tr>@endforelse
</tbody></table></div>
@if(in_array($internacion->estado,['hospitalizada','alta']))
<form method="post" action="{{ route('vet.hospital.profesional.control') }}">@csrf
<input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><input type="hidden" name="internacion_id" value="{{ $internacion->id }}">
<div class="form-row"><div class="col-md-4 form-group"><label class="floating-label-activo-sm" for="control-fecha">Próximo control</label><input id="control-fecha" class="form-control" type="datetime-local" name="fecha" value="{{ old('fecha') }}" required></div>
<div class="col-md-8 form-group"><label class="floating-label-activo-sm" for="control-motivo">Motivo y preparación</label><input id="control-motivo" class="form-control" name="motivo" maxlength="500" value="{{ old('motivo') }}" required></div></div>
<button class="btn btn-info" type="submit">Citar a control</button>
</form>
@endif
</div></div>
