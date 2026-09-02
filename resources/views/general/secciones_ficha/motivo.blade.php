@php
    $datosVeterinarios = isset($fichaVeterinariaGeneralData) && is_array($fichaVeterinariaGeneralData)
        ? $fichaVeterinariaGeneralData
        : [];
    $fichaActual = $fichaAtencion ?? null;
@endphp

<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
    <div class="card-a">
        <div class="card-header-a" id="anamnesis_veterinaria">
            <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left collapsed" type="button"
                data-toggle="collapse" data-target="#anamnesis_veterinaria_c" aria-expanded="false"
                aria-controls="anamnesis_veterinaria_c">
                Anamnesis
            </button>
        </div>
        <div id="anamnesis_veterinaria_c" class="collapse show" aria-labelledby="anamnesis_veterinaria">
            <div class="card-body-aten-a">
                <div class="form-row">
                    <div class="form-group col-12">
                        <label class="floating-label-activo-sm" for="motivo">Motivo de consulta</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="4" name="motivo" id="motivo"
                            placeholder="ESCRIBA LA INFORMACIÓN CÓMO LA DESCRIBE EL RESPONSABLE O TUTOR">{{ old('motivo', data_get($datosVeterinarios, 'motivo', data_get($fichaActual, 'motivo', ''))) }}</textarea>
                    </div>

                    <div class="form-group col-12">
                        <label class="floating-label-activo-sm" for="examen_fisico">Examen Físico</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="4" name="examen_fisico"
                            id="examen_fisico"
                            placeholder="DESCRIBA LAS CARACTERÍSTICAS QUE CONSIDERE NECESARIO">{{ old('examen_fisico', data_get($datosVeterinarios, 'examen_fisico', data_get($fichaActual, 'examen_fisico', ''))) }}</textarea>
                    </div>

                    <div class="form-group col-sm-12 col-xl-6">
                        <label class="floating-label-activo-sm" for="dieta">Dieta</label>
                        <textarea class="form-control caja-texto form-control-sm mb-9" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="dieta" id="dieta">{{ old('dieta', data_get($datosVeterinarios, 'dieta', '')) }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 col-xl-6">
                        <label class="floating-label-activo-sm" for="enfermedades_previas">Enfermedades previas</label>
                        <textarea class="form-control caja-texto form-control-sm mb-9" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="enfermedades_previas"
                            id="enfermedades_previas">{{ old('enfermedades_previas', data_get($datosVeterinarios, 'enfermedades_previas', '')) }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-6">
                        <label class="floating-label-activo-sm" for="esterilizado">Esterilizado</label>
                        <select class="form-control form-control-sm" name="esterilizado" id="esterilizado">
                            <option value="">Seleccione</option>
                            <option value="Si" @selected(old('esterilizado', data_get($datosVeterinarios, 'esterilizado')) === 'Si')>Sí</option>
                            <option value="No" @selected(old('esterilizado', data_get($datosVeterinarios, 'esterilizado')) === 'No')>No</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-xl-6">
                        <label class="floating-label-activo-sm" for="cirugias_previas">Cirugías previas</label>
                        <textarea class="form-control caja-texto form-control-sm mb-9" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="cirugias_previas"
                            id="cirugias_previas">{{ old('cirugias_previas', data_get($datosVeterinarios, 'cirugias_previas', '')) }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 col-xl-6">
                        <label class="floating-label-activo-sm" for="esquema_inmunizaciones">Esquema de Inmunizaciones</label>
                        <textarea class="form-control caja-texto form-control-sm mb-9" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="esquema_inmunizaciones"
                            id="esquema_inmunizaciones">{{ old('esquema_inmunizaciones', data_get($datosVeterinarios, 'esquema_inmunizaciones', '')) }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-xl-3">
                        <label class="floating-label-activo-sm" for="ultima_desparasitacion">Última Desparasitación</label>
                        <input type="date" class="form-control form-control-sm" name="ultima_desparasitacion"
                            id="ultima_desparasitacion"
                            value="{{ old('ultima_desparasitacion', data_get($datosVeterinarios, 'ultima_desparasitacion', '')) }}">
                    </div>
                    <div class="form-group col-sm-12 col-md-6 col-xl-3">
                        <label class="floating-label-activo-sm" for="producto_desparasitacion">Producto Desparasitación</label>
                        <input type="text" class="form-control form-control-sm" name="producto_desparasitacion"
                            id="producto_desparasitacion"
                            value="{{ old('producto_desparasitacion', data_get($datosVeterinarios, 'producto_desparasitacion', '')) }}">
                    </div>
                    <div class="form-group col-sm-12 col-xl-6">
                        <label class="floating-label-activo-sm" for="tratamientos_recientes">Tratamientos recientes</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="tratamientos_recientes"
                            id="tratamientos_recientes">{{ old('tratamientos_recientes', data_get($datosVeterinarios, 'tratamientos_recientes', '')) }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 col-xl-6">
                        <label class="floating-label-activo-sm" for="viajes_recientes">Viajes recientes</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="viajes_recientes"
                            id="viajes_recientes">{{ old('viajes_recientes', data_get($datosVeterinarios, 'viajes_recientes', '')) }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 col-md-6">
                        <label class="floating-label-activo-sm" for="vive_con_animales">¿Vive con animales?</label>
                        <select class="form-control form-control-sm" name="vive_con_animales" id="vive_con_animales">
                            <option value="">Seleccione</option>
                            <option value="Si" @selected(old('vive_con_animales', data_get($datosVeterinarios, 'vive_con_animales')) === 'Si')>Sí</option>
                            <option value="No" @selected(old('vive_con_animales', data_get($datosVeterinarios, 'vive_con_animales')) === 'No')>No</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-xl-6">
                        <label class="floating-label-activo-sm" for="cuales_animales">¿Cuáles?</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="cuales_animales"
                            id="cuales_animales">{{ old('cuales_animales', data_get($datosVeterinarios, 'cuales_animales', '')) }}</textarea>
                    </div>
                    <div class="form-group col-12">
                        <label class="floating-label-activo-sm" for="comportamiento_animal">Comportamiento del animal</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=4"
                            onblur="this.rows=1" name="comportamiento_animal" id="comportamiento_animal"
                            placeholder="ESCRIBA LA INFORMACIÓN CÓMO LA DESCRIBE EL RESPONSABLE">{{ old('comportamiento_animal', data_get($datosVeterinarios, 'comportamiento_animal', '')) }}</textarea>
                    </div>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>
