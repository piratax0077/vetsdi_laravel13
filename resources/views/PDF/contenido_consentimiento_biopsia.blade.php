@unless($vistaPrevia ?? false)
    @include('PDF.datos_consentimiento_veterinario')
@endunless
<p><strong>3. Descripción del procedimiento</strong></p>
<p>Por medio del presente documento, declaro haber sido informado/a por el/la Médico Veterinario/a Dr./Dra. {{ $profesional->nombreCompleto() }}
    (Registro/Colegiatura N.º <span @if($vistaPrevia ?? false) data-biopsia-campo="registro_profesional" @endif>{{ $datos['registro_profesional'] ?? (($vistaPrevia ?? false) ? '________' : 'Sin registro') }}</span>) sobre la necesidad de realizar una <strong>biopsia</strong> a mi mascota.
</p>
<ul>
    <li><strong>Zona anatómica a muestrear:</strong> <span @if($vistaPrevia ?? false) data-biopsia-campo="localizacion" @endif>{{ $datos['localizacion'] ?? '________' }}</span></li>
    <li><strong>Tipo de biopsia propuesta:</strong> <span @if($vistaPrevia ?? false) data-biopsia-campo="tecnica" @endif>{{ match ($datos['tecnica'] ?? null) {
        'Incisional' => 'Incisional (parcial)',
        'Excisional' => 'Escisional (extirpación completa)',
        'Tru-cut' => 'Punción / Tru-Cut',
        'Otra' => 'Otra: '.$datos['otra_tecnica'],
        default => $datos['tecnica'] ?? '________',
    } }}</span></li>
    <li><strong>Protocolo de inmovilización:</strong> <span @if($vistaPrevia ?? false) data-biopsia-campo="inmovilizacion" @endif>{{ implode(', ', $datos['inmovilizacion'] ?? []) ?: (($vistaPrevia ?? false) ? '________' : 'Sin registro') }}</span></li>
    <li><strong>Objetivo:</strong> Obtener una muestra de tejido para su envío a laboratorio especializado (estudio histopatológico / inmunohistoquímica / cultivo) con el fin de alcanzar un diagnóstico definitivo.</li>
</ul>
@if($vistaPrevia ?? false)
    <p class="d-none text-break" style="white-space: pre-wrap" data-biopsia-campo="descripcion" data-biopsia-opcional></p>
@elseif(!empty($datos['descripcion']))
    <p>{!! nl2br(e($datos['descripcion'])) !!}</p>
@endif

<p><strong>4. Riesgos y Posibles Complicaciones</strong></p>
<p>Comprendo que, aunque la toma de biopsia es un procedimiento diagnóstico habitual, conlleva riesgos inherentes que me han sido explicados con claridad:</p>
<ul>
    <li><strong>Riesgos anestésicos:</strong> Reacciones adversas a fármacos sedantes o anestésicos, depresión cardiorrespiratoria o, en casos excepcionales, fallecimiento del paciente.</li>
    <li><strong>Riesgos locales y quirúrgicos:</strong> Sangrado o hematomas en la zona de toma de muestra, infección secundaria, dolor postoperatorio, dehiscencia (apertura) de puntos o retraso en la cicatrización.</li>
    <li><strong>Riesgos diagnósticos:</strong> Posibilidad de que la muestra obtenida resulte insuficiente, necrótica o no representativa de la lesión principal, lo que podría requerir repetir el procedimiento en el futuro.</li>
</ul>
@if($vistaPrevia ?? false)
    <p class="d-none text-break"><strong>Riesgos adicionales informados:</strong><br><span style="white-space: pre-wrap" data-biopsia-campo="riesgos" data-biopsia-opcional></span></p>
@elseif(!empty($datos['riesgos']))
    <p><strong>Riesgos adicionales informados:</strong><br>{!! nl2br(e($datos['riesgos'])) !!}</p>
@endif

<p><strong>5. Alternativas y Consecuencias de No Realizar el Procedimiento</strong></p>
<p>Se me ha explicado que la alternativa a este procedimiento es el manejo empírico o sintomático sin confirmación diagnóstica. Entiendo que no realizar la biopsia impide conocer la naturaleza exacta de la lesión (inflamatoria, infecciosa o neoplásica) y limita la elección de un tratamiento específico y efectivo.</p>
@if($vistaPrevia ?? false)
    <p class="d-none text-break"><strong>Alternativas adicionales informadas:</strong><br><span style="white-space: pre-wrap" data-biopsia-campo="alternativas" data-biopsia-opcional></span></p>
@elseif(!empty($datos['alternativas']))
    <p><strong>Alternativas adicionales informadas:</strong><br>{!! nl2br(e($datos['alternativas'])) !!}</p>
@endif

<p><strong>6. Autorización de Envío de Muestra y Presupuesto</strong></p>
<ul>
    <li><strong>Laboratorio de destino:</strong> Autorizo el envío de la muestra biológica al laboratorio externo <span @if($vistaPrevia ?? false) data-biopsia-campo="laboratorio" @endif>{{ $datos['laboratorio'] ?? '________' }}</span>, asumiendo los costos asociados al análisis y transporte.</li>
    <li><strong>Situaciones imprevistas:</strong> Autorizo al equipo médico a realizar las maniobras de urgencia necesarias si surgiera alguna complicación vital durante el procedimiento o la anestesia.</li>
    <li><strong>Presupuesto:</strong> Declaro conocer y aceptar el presupuesto estimado para el procedimiento, insumos, anestesia y estudio de laboratorio, el cual asciende a $<span @if($vistaPrevia ?? false) data-biopsia-campo="presupuesto" @endif>{{ isset($datos['presupuesto']) ? number_format((int) $datos['presupuesto'], 0, ',', '.') : '________' }}</span>.</li>
</ul>

<p><strong>7. Declaración de Consentimiento y Firmas</strong></p>
<p>Certifico que he tenido la oportunidad de hacer preguntas, que todas mis dudas han sido resueltas satisfactoriamente en un lenguaje comprensible y que he informado al profesional sobre enfermedades previas, alergias o medicamentos que recibe actualmente mi mascota.</p>
<p>En virtud de lo anterior, <strong>OTORGO MI CONSENTIMIENTO</strong> libre y voluntario para la realización de la biopsia descrita.</p>
