<?php

// Las claves heredadas mantienen los medicamentos asociados a cada control.
return [
    'periodontal' => ['Enfermedad periodontal', ['piezas_afectadas' => ['Piezas afectadas', 'piezas', 1]]],
    'cpeso' => ['Obesidad y sobrepeso', ['peso' => ['Peso', 'kg'], 'variacion' => ['Variación', 'kg'], 'ideal' => ['Peso ideal', 'kg']]],
    'otitis' => ['Otitis externa', []],
    'osteoartritis' => ['Osteoartritis (artrosis)', ['peso' => ['Peso', 'kg']]],
    'dermatitis' => ['Dermatitis atópica', []],
    'mitral' => ['Enfermedad valvular mitral (cardiopatía)', ['respiracion' => ['Frecuencia respiratoria en reposo', 'resp/min'], 'peso' => ['Peso', 'kg']]],
    'displasia_cadera' => ['Displasia de cadera', ['peso' => ['Peso', 'kg']]],
    'displasia_codo' => ['Displasia de codo', ['peso' => ['Peso', 'kg']]],
    'epilepsia' => ['Epilepsia idiopática', ['crisis' => ['Crisis en los últimos 30 días', 'crisis', 1]]],
    'cinsufren' => ['Enfermedad renal crónica', ['creatinina' => ['Creatinina', 'mg/dL'], 'sdma' => ['SDMA', 'µg/dL'], 'upc' => ['Relación proteína/creatinina urinaria', 'UPC'], 'sistolica' => ['Presión sistólica', 'mmHg']]],
    'hipertiroidismo' => ['Hipertiroidismo', ['t4' => ['T4 total', 'µg/dL'], 'peso' => ['Peso', 'kg']]],
    'cdiabet' => ['Diabetes mellitus', ['glucosa' => ['Glucosa', 'mg/dL'], 'fructosamina' => ['Fructosamina', 'µmol/L'], 'peso' => ['Peso', 'kg']]],
    'chipertension' => ['Hipertensión arterial', ['sistolica' => ['Presión sistólica', 'mmHg'], 'diastolica' => ['Presión diastólica', 'mmHg']]],
    'asma_felina' => ['Asma felina', ['episodios' => ['Episodios en los últimos 30 días', 'episodios', 1], 'respiracion' => ['Frecuencia respiratoria en reposo', 'resp/min']]],
    'otra' => ['Otra', []],
];
