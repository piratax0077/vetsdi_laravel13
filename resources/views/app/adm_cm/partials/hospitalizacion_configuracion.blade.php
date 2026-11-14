@if(($tipo_establecimiento ?? null) === 'hospital' || ($configuracion_establecimiento->hospitalizacion_habilitada ?? false))
<div class="tab-pane fade" id="hospitalizacion-config" role="tabpanel" aria-labelledby="hospitalizacion-config-tab">
    <div class="card">
        <div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-activity"></i></span>Hospitalización</h5></div>
        <div class="card-body">
            <p>Información y configuración del área hospitalaria del centro.</p>
            @if(!($configuracion_establecimiento->hospitalizacion_habilitada ?? false))<div class="alert alert-warning">El área está deshabilitada para nuevos ingresos. La historia se conserva para consulta.</div>@endif
            <ul class="nav pill-moderno pill-moderno-white mb-3" role="tablist" aria-label="Información del área hospitalaria">
                <li class="nav-item"><a id="hospital-info-pacientes-tab" class="nav-link active" data-toggle="pill" href="#hospital-info-pacientes" role="tab" aria-controls="hospital-info-pacientes" aria-selected="true">Pacientes del establecimiento</a></li>
                <li class="nav-item"><a id="hospital-info-hospitalizacion-tab" class="nav-link" data-toggle="pill" href="#hospital-info-hospitalizacion" role="tab" aria-controls="hospital-info-hospitalizacion" aria-selected="false">Hospitalización</a></li>
                <li class="nav-item"><a id="hospital-info-plazas-tab" class="nav-link" data-toggle="pill" href="#hospital-info-plazas" role="tab" aria-controls="hospital-info-plazas" aria-selected="false">Plazas de hospitalización</a></li>
            </ul>
            <div class="tab-content">
                <div id="hospital-info-pacientes" class="tab-pane fade show active" role="tabpanel" aria-labelledby="hospital-info-pacientes-tab">
                    <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Mascota</th><th>Especie</th></tr></thead><tbody>
                    @forelse(($informacionHospitalaria['pacientes'] ?? []) as $paciente)<tr><td>{{ $paciente->nombre }}</td><td>{{ $paciente->especie }}</td></tr>@empty<tr><td colspan="2" class="text-center text-muted">No hay pacientes vinculados al establecimiento.</td></tr>@endforelse
                    </tbody></table></div>
                    @isset($informacionHospitalaria['pacientes']){{ $informacionHospitalaria['pacientes']->links('pagination::bootstrap-4') }}@endisset
                </div>
                <div id="hospital-info-hospitalizacion" class="tab-pane fade" role="tabpanel" aria-labelledby="hospital-info-hospitalizacion-tab">
                    <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Mascota</th><th>Plaza</th><th>Responsable</th><th>Ingreso</th><th>Egreso</th><th>Estado</th></tr></thead><tbody>
                    @forelse(($informacionHospitalaria['hospitalizaciones'] ?? []) as $hospitalizacion)<tr><td>{{ $hospitalizacion->mascota ?? '—' }}</td><td>{{ $hospitalizacion->plaza ?? '—' }}</td><td>{{ $hospitalizacion->responsable }}</td><td>{{ substr((string) $hospitalizacion->ingreso, 0, 16) }}</td><td>{{ $hospitalizacion->egreso ? substr((string) $hospitalizacion->egreso, 0, 16) : '—' }}</td><td>{{ ['hospitalizada'=>'Hospitalizada','alta'=>'Alta','traslado'=>'Traslado','fallecida'=>'Fallecida','anulada'=>'Anulada'][$hospitalizacion->estado] ?? $hospitalizacion->estado }}</td></tr>@empty<tr><td colspan="6" class="text-center text-muted">No hay hospitalizaciones registradas.</td></tr>@endforelse
                    </tbody></table></div>
                    @isset($informacionHospitalaria['hospitalizaciones']){{ $informacionHospitalaria['hospitalizaciones']->links('pagination::bootstrap-4') }}@endisset
                </div>
                <div id="hospital-info-plazas" class="tab-pane fade" role="tabpanel" aria-labelledby="hospital-info-plazas-tab">
                    <a class="btn btn-outline-info btn-sm mb-3" href="{{ route('vet.areas.index',['area'=>'hospitalizacion','id_institucion'=>$institucion->id,'vista'=>'plazas']) }}">Configurar plazas</a>
                    <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Código</th><th>Tipo</th><th>Especies admitidas</th><th>Habilitada</th><th>Ocupación</th></tr></thead><tbody>
                    @forelse(($informacionHospitalaria['plazas'] ?? []) as $plaza)<tr><td>{{ $plaza->codigo }}</td><td>{{ $plaza->tipo }}</td><td>{{ $plaza->especies ?: '—' }}</td><td>{{ $plaza->activa ? 'Sí' : 'No' }}</td><td>{{ $plaza->ocupacion ? 'Ocupada' : ($plaza->activa ? 'Disponible' : 'Deshabilitada') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted">No hay plazas configuradas.</td></tr>@endforelse
                    </tbody></table></div>
                    @isset($informacionHospitalaria['plazas']){{ $informacionHospitalaria['plazas']->links('pagination::bootstrap-4') }}@endisset
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('js/configuracion-hospitalaria.js') }}" defer></script>
@endif
