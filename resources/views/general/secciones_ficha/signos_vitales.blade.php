<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
    <div class="card-a">
        <div class="card-header-a" id="signosvit-otros">
            <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left collapsed card-act-open" type="button" data-toggle="collapse" data-target="#signosvit-otros-c" aria-expanded="false" aria-controls="signosvit-otros-c">
                Signos vitales y otros
            </button>
        </div>
        <div id="signosvit-otros-c" class="collapse" aria-labelledby="signosvit-otros" data-parent="#signosvit-otros">
            <div class="card-body-aten-a">
                <div class="form-row">
                    <div class="form-group col-md-1">
                        @if (isset($fichaAtencion) && $fichaAtencion->temperatura !=null)
                        
                        <label class="floating-label-activo-sm">Tº</label>
                        <input type="text" class="form-control form-control-sm" name="temperatura" id="temperatura" value="{{ $fichaAtencion->temperatura }}">
                        @else
                        <label class="floating-label-activo-sm">Tº</label>
                        <input type="text" class="form-control form-control-sm" name="temperatura" id="temperatura" value="{!! old('temperatura') !!}">
                        @endif
                    </div>
                    <div class="form-group col-md-1">
                        @if (isset($fichaAtencion) && $fichaAtencion->pulso != null)
                        <label class="floating-label-activo-sm">Pulso</label>
                        <input type="text" class="form-control form-control-sm" name="pulso" id="pulso" value="{{ $fichaAtencion->pulso }}">
                        @else
                        <label class="floating-label-activo-sm">Pulso</label>
                        <input type="text" class="form-control form-control-sm" name="pulso" id="pulso" value="{!! old('pulso') !!}">
                        @endif
                    </div>
                    <div class="form-group col-md-2">
                        @if (isset($fichaAtencion) && $fichaAtencion->frecuencia_reposo
                        != null)
                        <label class="floating-label-activo-sm">Frec.
                            Reposo</label>
                        <input type="text" class="form-control form-control-sm" name="frecuencia_reposo" id="frecuencia_reposo" value="{{ $fichaAtencion->frecuencia_reposo }}">
                        @else
                        <label class="floating-label-activo-sm">Frec.
                            Reposo</label>
                        <input type="text" class="form-control form-control-sm" name="frecuencia_reposo" id="frecuencia_reposo" value="{!! old('frecuencia_reposo') !!}">
                        @endif
                    </div>
                    <div class="form-group col-md-2">
                        @if (isset($fichaAtencion) && $fichaAtencion->peso != null)
                        <label class="floating-label-activo-sm">Peso</label>
                        <input type="text" class="form-control form-control-sm" name="peso" id="peso" value="{{ $fichaAtencion->peso }}">
                        @else
                        <label class="floating-label-activo-sm">Peso</label>
                        <input type="text" class="form-control form-control-sm" name="peso" id="peso" value="{!! old('peso') !!}">
                        @endif
                    </div>
                    <div class="form-group col-md-2">
                        @if (isset($fichaAtencion) && $fichaAtencion->talla != null)
                        <label class="floating-label-activo-sm">Talla</label>
                        <input type="text" class="form-control form-control-sm" name="talla" id="talla" value="{{ $fichaAtencion->talla }}">
                        @else
                        <label class="floating-label-activo-sm">Talla</label>
                        <input type="text" class="form-control form-control-sm" name="talla" id="talla" value="{!! old('talla') !!}">
                        @endif
                    </div>
                    <div class="form-group col-md-2">
                        @if (isset($fichaAtencion) && $fichaAtencion->imc != null)
                        <label class="floating-label-activo-sm">IMC</label>
                        <input type="text" class="form-control form-control-sm" name="imc" id="imc" value="{{ $fichaAtencion->imc }}">
                        @else
                        <label class="floating-label-activo-sm">IMC</label>
                        <input type="text" class="form-control form-control-sm" name="imc" id="imc" value="{!! old('imc') !!}">
                        @endif
                    </div>
                    <div class="form-group col-md-2">
                        @if (isset($fichaAtencion) && $fichaAtencion->estado_nutricional
                        != null)
                        <label class="floating-label-activo-sm">Estado
                            Nutricional</label>
                        <input type="text" class="form-control form-control-sm" name="estado_nutricional" id="estado_nutricional" value="{{ $fichaAtencion->estado_nutricional }}">
                        @else
                        <label class="floating-label-activo-sm">Estado
                            Nutricional</label>
                        <input type="text" class="form-control form-control-sm" name="estado_nutricional" id="estado_nutricional" value="{!! old('estado_nutricional') !!}">
                        @endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group mb-1">
                        <label><strong>Presión arterial veterinaria</strong></label>
                        <div class="switch switch-success d-inline m-r-10">
                            <input type="checkbox" id="p_arterial" value="{!! old('p_arterial') !!}">
                            <label for="p_arterial" class="cr"></label>
                        </div>
                    </div>
                </div>
                <div class="form-row" id="form_1" style="display:none">
                    <div class="form-group col-md-3">
                        @if (isset($fichaAtencion) && $fichaAtencion->presion_bi !=
                        null)
                        <label class="floating-label-activo-sm">Sistólica (PAS)</label>
                        <input type="number" min="0" step="1" class="form-control form-control-sm" name="presion_bi" id="presion_bi" value="{{ $fichaAtencion->presion_bi }}" placeholder="mmHg">
                        @else
                        <label class="floating-label-activo-sm">Sistólica (PAS)</label>
                        <input type="number" min="0" step="1" class="form-control form-control-sm" name="presion_bi" id="presion_bi" value="{!! old('presion_bi') !!}" placeholder="mmHg">
                        @endif
                    </div>
                    <div class="form-group col-md-3">
                        @if (isset($fichaAtencion) && $fichaAtencion->presion_bd !=
                        null)
                        <label class="floating-label-activo-sm">Diastólica (PAD)</label>
                        <input type="number" min="0" step="1" class="form-control form-control-sm" name="presion_bd" id="presion_bd" value="{{ $fichaAtencion->presion_bd }}" placeholder="mmHg">
                        @else
                        <label class="floating-label-activo-sm">Diastólica (PAD)</label>
                        <input type="number" min="0" step="1" class="form-control form-control-sm" name="presion_bd" id="presion_bd" value="{!! old('presion_bd') !!}" placeholder="mmHg">
                        @endif
                    </div>
                    <div class="form-group col-md-3">
                        @if (isset($fichaAtencion) && $fichaAtencion->presion_de_pie !=
                        null)
                        <label class="floating-label-activo-sm">Media (PAM)</label>
                        <input type="number" min="0" step="0.1" class="form-control form-control-sm" name="presion_de_pie" id="presion_de_pie" value="{{ $fichaAtencion->presion_de_pie }}" placeholder="mmHg" readonly>
                        @else
                        <label class="floating-label-activo-sm">Media (PAM)</label>
                        <input type="number" min="0" step="0.1" class="form-control form-control-sm" name="presion_de_pie" id="presion_de_pie" value="{!! old('presion_de_pie') !!}" placeholder="mmHg" readonly>
                        @endif
                    </div>
                    <div class="form-group col-md-3">
                        @if (isset($fichaAtencion) && $fichaAtencion->presion_sentado !=
                        null)
                        <label class="floating-label-activo-sm">Sitio, manguito y método</label>
                        <input type="text" class="form-control form-control-sm" name="presion_sentado" id="presion_sentado" value="{{ $fichaAtencion->presion_sentado }}" placeholder="Ej.: cola, manguito 3, Doppler">
                        @else
                        <label class="floating-label-activo-sm">Sitio, manguito y método</label>
                        <input type="text" class="form-control form-control-sm" name="presion_sentado" id="presion_sentado" value="{!! old('presion_sentado') !!}" placeholder="Ej.: cola, manguito 3, Doppler">
                        @endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group mb-1">
                        <label><strong>Evaluación neurológica, perfusión y movilidad</strong></label>
                        <div class="switch switch-success d-inline m-r-10">
                            <input type="checkbox" id="com_trasl" value="{!! old('com_trasl') !!}">
                            <label for="com_trasl" class="cr"></label>
                        </div>
                    </div>
                </div>
                <div class="form-row" id="form_2" style="display:none">
                    <div class="form-group col-md-4">
                        @if (isset($fichaAtencion) &&
                        $fichaAtencion->ct_estado_conciencia != null)
                        <label class="floating-label-activo-sm">Estado de
                            conciencia</label>
                        <input type="text" class="form-control form-control-sm" name="ct_estado_conciencia" id="ct_estado_conciencia" value="{{ $fichaAtencion->ct_estado_conciencia }}" placeholder="Alerta, deprimido, estupor, coma">
                        @else
                        <label class="floating-label-activo-sm">Estado de
                            conciencia</label>
                        <input type="text" class="form-control form-control-sm" name="ct_estado_conciencia" id="ct_estado_conciencia" value="{!! old('ct_estado_conciencia') !!}" placeholder="Alerta, deprimido, estupor, coma">
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        @if (isset($fichaAtencion) && $fichaAtencion->ct_lenguaje !=
                        null)
                        <label class="floating-label-activo-sm">Mucosas y TRC</label>
                        <input type="text" class="form-control form-control-sm" name="ct_lenguaje" id="ct_lenguaje" value="{{ $fichaAtencion->ct_lenguaje }}" placeholder="Ej.: rosadas, húmedas; TRC 1,5 s">
                        @else
                        <label class="floating-label-activo-sm">Mucosas y TRC</label>
                        <input type="text" class="form-control form-control-sm" name="ct_lenguaje" id="ct_lenguaje" value="{!! old('ct_lenguaje') !!}" placeholder="Ej.: rosadas, húmedas; TRC 1,5 s">
                        @endif
                    </div>
                    <div class="form-group col-md-4">
                        @if (isset($fichaAtencion) && $fichaAtencion->ct_traslado !=
                        null)
                        <label class="floating-label-activo-sm">Movilidad / locomoción</label>
                        <input type="text" class="form-control form-control-sm" name="ct_traslado" id="ct_traslado" value="{{ $fichaAtencion->ct_traslado }}" placeholder="Normal, claudicación, no ambulatorio">
                        @else
                        <label class="floating-label-activo-sm">Movilidad / locomoción</label>
                        <input type="text" class="form-control form-control-sm" name="ct_traslado" id="ct_traslado" value="{!! old('ct_traslado') !!}" placeholder="Normal, claudicación, no ambulatorio">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        function calcularPamVeterinaria() {
            var pas = Number(document.getElementById('presion_bi')?.value);
            var pad = Number(document.getElementById('presion_bd')?.value);
            var pam = document.getElementById('presion_de_pie');

            if (!pam) return;
            if (!Number.isFinite(pas) || !Number.isFinite(pad) || pas <= 0 || pad <= 0 || pas < pad) {
                pam.value = '';
                return;
            }

            var resultado = pad + ((pas - pad) / 3);
            pam.value = Number.isInteger(resultado) ? resultado : resultado.toFixed(1);
            pam.dispatchEvent(new Event('change', { bubbles: true }));
        }

        document.addEventListener('input', function (evento) {
            if (evento.target && (evento.target.id === 'presion_bi' || evento.target.id === 'presion_bd')) {
                calcularPamVeterinaria();
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', calcularPamVeterinaria);
        } else {
            calcularPamVeterinaria();
        }
    })();
</script>
