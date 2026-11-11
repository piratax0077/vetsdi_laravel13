<?php

// Plantillas de muestra de indicaciones y consentimientos del profesional.
// Las usan la configuración (Indicaciones y consentimientos) y el sidebar de la ficha veterinaria
// mientras no existan los registros reales en la base de datos.

// Datos de muestra para la maqueta; se reemplazarán por los registros reales.
// Los datos automáticos se guardan como etiquetas que después se reemplazan por los datos reales de la atención.
$dato = fn ($clave, $texto) => '<span class="indicacion-dato" contenteditable="false" data-dato="' . $clave . '">' . $texto . '</span>';
$logoBase = asset('images/vet-color-h.svg');
$secciones = [
    'indicaciones' => [
        'pestana' => 'Indicaciones veterinarias',
        'titulo' => 'Mis indicaciones',
        'icono' => 'icon-file-text',
        'agregar' => 'Agregar indicación',
        'buscar' => 'Título de la indicación',
        'vacio' => 'No hay indicaciones que coincidan con la búsqueda.',
        'categorias' => ['Cuidados generales', 'Post-operatorio', 'Vacunación', 'Dieta y alimentación', 'Medicación'],
        'registros' => [
            ['id' => 1, 'titulo' => 'Reposo post-operatorio', 'categoria' => 'Post-operatorio', 'tipo' => 'texto', 'alcance' => 'personal', 'activo' => true, 'logo' => $logoBase,
                'contenido' => '<p>Estimado tutor de ' . $dato('mascota', 'Mascota') . ':</p><ul><li>Mantenga <b>reposo absoluto</b> durante 10 días.</li><li>Evite que lama o muerda la herida.</li><li>Control en 7 días.</li></ul><p>' . $dato('profesional', 'Profesional') . ' · ' . $dato('fecha', 'Fecha') . '</p>'],
            ['id' => 2, 'titulo' => 'Pauta de vacunación cachorros', 'categoria' => 'Vacunación', 'tipo' => 'archivo', 'alcance' => 'compartida', 'activo' => true, 'archivo' => 'pauta_vacunacion.pdf'],
            ['id' => 3, 'titulo' => 'Dieta hipoalergénica', 'categoria' => 'Dieta y alimentación', 'tipo' => 'texto', 'alcance' => 'personal', 'activo' => false, 'logo' => $logoBase,
                'contenido' => '<p>' . $dato('mascota', 'Mascota') . ' debe recibir <b>alimento hipoalergénico</b> de forma exclusiva durante <i>8 semanas</i>, sin premios ni snacks.</p>'],
        ],
    ],
    'consentimientos' => [
        'pestana' => 'Consentimientos informados',
        'titulo' => 'Consentimientos informados',
        'icono' => 'icon-clipboard',
        'agregar' => 'Agregar consentimiento',
        'buscar' => 'Título del consentimiento',
        'vacio' => 'No hay consentimientos que coincidan con la búsqueda.',
        'categorias' => ['Cirugía y anestesia', 'Hospitalización', 'Eutanasia', 'Procedimientos diagnósticos', 'Sedación'],
        'registros' => [
            ['id' => 11, 'titulo' => 'Cirugía y anestesia general', 'categoria' => 'Cirugía y anestesia', 'tipo' => 'texto', 'alcance' => 'compartida', 'activo' => true, 'logo' => $logoBase,
                'contenido' => '<p>Yo, ' . $dato('tutor', 'Tutor') . ', RUT ' . $dato('rut_tutor', 'RUT del tutor') . ', en calidad de tutor o responsable de ' . $dato('mascota', 'Mascota') . ', especie ' . $dato('especie', 'Especie') . ', raza ' . $dato('raza', 'Raza') . ', autorizo al médico veterinario ' . $dato('profesional', 'Profesional') . ' a realizar el procedimiento quirúrgico indicado y la <b>anestesia general</b> que este requiera.</p>'
                    . '<p>Declaro que se me explicaron en forma clara:</p><ul><li>El objetivo del procedimiento y sus alternativas.</li><li>Los riesgos propios de la cirugía y de la anestesia, incluida la posibilidad de complicaciones graves o muerte.</li><li>Los cuidados posteriores y los costos estimados.</li></ul>'
                    . '<p>Autorizo además los procedimientos adicionales que el equipo considere necesarios durante la intervención para resguardar la vida y el bienestar del paciente.</p>'],
            ['id' => 12, 'titulo' => 'Eutanasia', 'categoria' => 'Eutanasia', 'tipo' => 'texto', 'alcance' => 'personal', 'activo' => true, 'logo' => $logoBase,
                'contenido' => '<p>Yo, ' . $dato('tutor', 'Tutor') . ', RUT ' . $dato('rut_tutor', 'RUT del tutor') . ', tutor o responsable de ' . $dato('mascota', 'Mascota') . ', solicito y autorizo en forma libre y voluntaria la <b>eutanasia</b> de mi mascota, tras haber sido informado de su condición de salud y de las alternativas disponibles.</p>'
                    . '<p>Declaro que no ha mordido a ninguna persona ni animal en los últimos 10 días.</p>'],
            ['id' => 13, 'titulo' => 'Hospitalización (formato firmado)', 'categoria' => 'Hospitalización', 'tipo' => 'archivo', 'alcance' => 'compartida', 'activo' => true, 'archivo' => 'consentimiento_hospitalizacion.pdf'],
        ],
    ],
];

return $secciones;
