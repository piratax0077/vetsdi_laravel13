@if($hospitalizacionHabilitada)
@if($plazas->isEmpty())
<div class="alert alert-info" role="status">
    {{ ($hayPlazasConfiguradas ?? false) ? 'No hay plazas disponibles para un nuevo ingreso. Las plazas están ocupadas o deshabilitadas.' : 'No hay plazas de hospitalización configuradas en este centro.' }}
    @if($puedeConfigurarPlazas ?? false)
    <a href="{{ route('vet.areas.index',['area'=>'hospitalizacion','id_institucion'=>$institucion->id,'vista'=>'plazas']) }}">Configurar plazas de hospitalización</a>.
    @else
    Solicite al administrador del centro que revise las plazas de hospitalización.
    @endif
</div>
@elseif(empty($mascotas) || count($mascotas) === 0)
<div class="alert alert-info" role="status">No hay pacientes vinculados al centro para registrar un ingreso hospitalario.</div>
@else
<button class="btn btn-info mb-3" data-toggle="modal" data-target="#ingreso_hospitalario">Registrar ingreso hospitalario</button>
<div class="modal fade" id="ingreso_hospitalario" tabindex="-1" aria-labelledby="ingreso_hospitalario_titulo" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
<div class="modal-header modal-header-purple"><h5 class="modal-title" id="ingreso_hospitalario_titulo">Ingreso de paciente hospitalizado</h5><button class="close" type="button" data-dismiss="modal" aria-label="Cerrar">×</button></div>
<form method="post" action="{{ route('vet.hospital.profesional.ingreso') }}">@csrf<input type="hidden" name="_hospital_form" value="ingreso">
<input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
<div class="modal-body">
<div class="form-group"><label class="floating-label-activo-sm" for="ingreso-mascota">Paciente del centro</label><select class="form-control" id="ingreso-mascota" name="id_mascota" required><option value="">Seleccione</option>@foreach($mascotas as $id=>$nombre)<option value="{{ $id }}" @selected((string)old('id_mascota') === (string)$id)>{{ $nombre }}</option>@endforeach</select></div>
<div class="form-group"><label class="floating-label-activo-sm" for="ingreso-plaza">Plaza disponible</label><select class="form-control" id="ingreso-plaza" name="plaza_id" required><option value="">Seleccione</option>@foreach($plazas as $plaza)<option value="{{ $plaza->id }}" @selected((string)old('plaza_id') === (string)$plaza->id)>{{ $plaza->codigo }}</option>@endforeach</select></div>
<div class="form-group"><label class="floating-label-activo-sm" for="ingreso-fecha">Fecha y hora de ingreso</label><input id="ingreso-fecha" class="form-control" type="datetime-local" name="ingreso" value="{{ old('ingreso',now()->format('Y-m-d\TH:i')) }}" required></div>
<div class="form-group"><label class="floating-label-activo-sm" for="ingreso-motivo">Motivo y diagnóstico de ingreso</label><textarea id="ingreso-motivo" class="form-control" name="motivo" maxlength="10000" required>{{ old('motivo') }}</textarea></div>
<div class="form-group"><label class="floating-label-activo-sm" for="ingreso-indicaciones">Indicaciones iniciales</label><textarea id="ingreso-indicaciones" class="form-control" name="indicaciones" maxlength="20000">{{ old('indicaciones') }}</textarea></div>
</div><div class="modal-footer"><button class="btn btn-outline-dark" type="button" data-dismiss="modal">Cancelar</button><button class="btn btn-info" type="submit">Registrar ingreso</button></div></form>
</div></div></div>
@endif
@endif
