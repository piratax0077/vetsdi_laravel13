<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
    <div class="card-a">
        <div class="card-header-a" id="motivodd">
            <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#{{ !empty($bloqueComunVeterinario) ? 'motivo_odontologico_c' : 'motivo_c' }}" aria-expanded="false" aria-controls="{{ !empty($bloqueComunVeterinario) ? 'motivo_odontologico_c' : 'motivo_c' }}">
                {{ !empty($bloqueComunVeterinario) ? 'Antecedentes y observaciones de la especialidad' : 'Motivo de la consulta y Examen físico general' }}
            </button>
        </div>
        <div id="{{ !empty($bloqueComunVeterinario) ? 'motivo_odontologico_c' : 'motivo_c' }}" class="collapse show" aria-labelledby="motivodd" data-parent="#motivodd">
            <div class="card-body-aten-a">
                <div class="form-row">
                    @empty($bloqueComunVeterinario)
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6">
                        <label class="floating-label-activo-sm">Motivo de consulta</label>
                        <input type="text" class="form-control form-control-sm" name="motivo" id="motivo" value="{{ old('motivo') }}">
                    </div>
                    @endempty
                    <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6">
                        <label class="floating-label-activo-sm">Antecedentes Especialidad</label>
                        <input type="text" class="form-control form-control-sm" name="antecedentes" id="antecedentes" value="{{ old('antecedentes') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-sm-12 col-md-12 col-lg-12 col-xl-12 ">
                        <label class="floating-label-activo-sm">Observaciones al Examen de la Especialidad</label>
                        @php($campoObservacion = !empty($bloqueComunVeterinario) ? 'observaciones_examen_especialidad' : 'examen_fisico')
                        <textarea class="form-control caja-texto form-control-sm mb-9" rows="1" onfocus="this.rows=4" onblur="this.rows=1;" name="{{ $campoObservacion }}" id="{{ $campoObservacion }}" placeholder="OBSERVACIONES DE LA CONSULTA Y EXAMEN FÍSICO RELEVANTE">{{ old($campoObservacion, data_get($fichaVeterinariaGeneralData ?? [], $campoObservacion)) }}</textarea>
                    </div>
                </div>
                <div class="form-row mb-2">
                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4 col-xxl-4">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="anestesia_local_dental();"><i class="fas fa-plus"></i> Anestesia local</button>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4 col-xxl-4">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="hemorragia_dental();"><i class="fas fa-plus"></i> Hemorragias</button>
                    </div>
                    <div class="form-group col-sm-12 col-md-12 col-lg-4 col-xl-4 col-xxl-4">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-block" onclick="fractura_dental();"><i class="fas fa-plus"></i> Fracturas</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
