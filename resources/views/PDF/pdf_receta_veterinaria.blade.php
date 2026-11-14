<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    @php
        // Formato elegido por el profesional en "Plantilla receta"; sin plantilla guardada sale como siempre
        $plantilla = $cuerpo['plantilla'] ?? \App\Models\PlantillaPdfProfesional::predeterminada('receta');
        $color = $plantilla['color'];
        $mostrar = $plantilla['mostrar'];
        $alineacionMarca = ['izquierda' => 'left', 'centro' => 'center', 'derecha' => 'right'][$plantilla['posicion_logo']] ?? 'left';
        $letra = \App\Models\PlantillaPdfProfesional::tamanoLetraPx($plantilla);
    @endphp
    <style>
        @page { margin: 28px 34px 34px; }
        body { font-family: DejaVu Sans, sans-serif; color: #27364a; font-size: {{ $letra }}px; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .header { border-bottom: 4px solid {{ $color }}; padding-bottom: 10px; margin-bottom: 14px; text-align: {{ $alineacionMarca }}; }
        .brand { color: {{ $color }}; font-size: 24px; font-weight: bold; }
        .logo { max-height: 60px; max-width: 220px; margin-bottom: 4px; }
        .title { color: #75429a; font-size: 17px; font-weight: bold; margin-top: 3px; }
        .controlled { display: inline-block; margin-top: 6px; padding: 4px 9px; color: #a21b36; border: 1px solid #dc7185; border-radius: 4px; font-weight: bold; }
        .meta, .items, .signature { width: 100%; border-collapse: collapse; }
        .meta { margin-bottom: 15px; }
        .meta td { width: 50%; padding: 6px 8px; border: 1px solid #dce4ea; vertical-align: top; }
        .label { color: #697789; font-size: 8px; text-transform: uppercase; }
        .value { font-size: {{ $letra + 1 }}px; font-weight: bold; margin: 2px 0; }
        .items th { background: {{ $color }}; color: #fff; padding: 7px; text-align: left; }
        .items td { padding: 7px; border-bottom: 1px solid #dce4ea; vertical-align: top; }
        .items tr:nth-child(even) td { background: #f5f8fa; }
        .lista-titulo { color: {{ $color }}; font-size: {{ $letra + 2 }}px; font-weight: bold; margin: 0 0 8px; }
        .medicamento { padding: 8px 0 8px 10px; border-left: 3px solid {{ $color }}; margin-bottom: 10px; }
        .medicamento-nombre { font-size: {{ $letra + 2 }}px; font-weight: bold; }
        .medicamento-dato { margin-top: 3px; }
        .medicamento-dato span { color: #697789; }
        .signature { margin-top: 24px; }
        .signature td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .qr { width: 78px; height: 78px; }
        .line { border-top: 1px solid #64748b; padding-top: 5px; margin: 0 18px; }
        .token { margin-top: 10px; color: #718096; font-size: 8px; word-break: break-all; }
        .footer { margin-top: 18px; border-top: 1px solid #cbd6dd; padding-top: 6px; color: #738092; font-size: 8px; }
    </style>
</head>
<body>
@forelse($cuerpo['recomendacion'] as $receta)
    @php
        $mascota = $cuerpo['mascota'];
        $tutor = $receta->paciente;
        $profesional = $receta->profesional;
        $nombreProfesional = trim(($profesional->nombre ?? '').' '.($profesional->apellido_uno ?? '').' '.($profesional->apellido_dos ?? ''));
        $lugar = $receta->lugar_atencion;
        $controlId = (int) $receta->control;
        $controlNombre = $cuerpo['controles_receta'][$controlId] ?? ('Tipo de receta '.$controlId);
        $esControlada = in_array($controlId, [3, 4], true);
        // Celdas de datos que el profesional dejó visibles, de a dos por fila
        $celdas = array_values(array_filter(['mascota', 'tutor', 'profesional', 'centro'], fn ($seccion) => !empty($mostrar[$seccion])));
    @endphp
    <section class="page">
        <div class="header">
            @if(!empty($plantilla['logo']))
                <img class="logo" src="{{ $plantilla['logo'] }}" alt="Logo">
            @elseif($plantilla['texto_marca'] !== '')
                <div class="brand">{{ $plantilla['texto_marca'] }}</div>
            @endif
            <div class="title">{{ $plantilla['titulo'] }}</div>
            @if($esControlada)
                <div class="controlled">RECETA CONTROLADA · {{ mb_strtoupper($controlNombre) }}</div>
            @else
                <div>{{ $controlNombre }}</div>
            @endif
        </div>

        @if(count($celdas))
            <table class="meta">
                @foreach(array_chunk($celdas, 2) as $fila)
                    <tr>
                        @foreach($fila as $seccion)
                            <td @if(count($fila) === 1) colspan="2" @endif>
                                @if($seccion === 'mascota')
                                    <div class="label">Mascota</div>
                                    <div class="value">{{ $mascota->nombre ?? 'Sin registro' }}</div>
                                    <div>{{ optional($mascota->especieMascota)->nombre ?? $mascota->especie ?? 'Especie sin registro' }}</div>
                                    <div>Raza: {{ optional($mascota->razaMascota)->nombre ?? 'Sin registro' }} · Sexo: {{ $mascota->sexo ?? 'Sin registro' }}</div>
                                    @if(!empty($mascota->chip))<div>Chip: {{ $mascota->chip }}</div>@endif
                                @elseif($seccion === 'tutor')
                                    <div class="label">Tutor responsable</div>
                                    <div class="value">{{ trim(($tutor->nombres ?? '').' '.($tutor->apellido_uno ?? '').' '.($tutor->apellido_dos ?? '')) }}</div>
                                    <div>RUT: {{ $tutor->rut ?? 'Sin registro' }}</div>
                                    <div>Teléfono: {{ $tutor->telefono_uno ?? 'Sin registro' }}</div>
                                @elseif($seccion === 'profesional')
                                    <div class="label">Médico/a veterinario/a</div>
                                    <div class="value">{{ $nombreProfesional }}</div>
                                    <div>RUT: {{ $profesional->rut ?? 'Sin registro' }}@if(!empty($profesional->num_colegio)) · Registro: {{ $profesional->num_colegio }}@endif</div>
                                @else
                                    <div class="label">Centro y fecha de emisión</div>
                                    <div class="value">{{ $lugar->nombre ?? 'Centro veterinario' }}</div>
                                    <div>{{ optional($cuerpo['fecha_atencion'])->format('d-m-Y H:i') ?? date('d-m-Y H:i') }}</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        @endif

        @if($plantilla['formato'] === 'lista')
            {{-- Formato lista: un medicamento debajo de otro, sin tabla --}}
            <div class="lista-titulo">Medicamentos indicados</div>
            @forelse($receta->detalle as $detalle)
                <div class="medicamento">
                    <div class="medicamento-nombre">{{ $detalle['producto'] ?? 'Sin registro' }}</div>
                    @if(!empty($detalle['farmaco']))<div class="medicamento-dato"><span>Fármaco:</span> {{ $detalle['farmaco'] }}</div>@endif
                    <div class="medicamento-dato"><span>Presentación:</span> {{ $detalle['presentacion'] ?? 'Sin registro' }}</div>
                    <div class="medicamento-dato"><span>Indicación / posología:</span> {{ $detalle['posologia'] ?? 'Sin registro' }}@if(!empty($detalle['via_administracion'])) · Vía: {{ $detalle['via_administracion'] }}@endif</div>
                    <div class="medicamento-dato"><span>Periodo:</span> {{ $detalle['periodo'] ?? 'Sin registro' }} · <span>Cantidad:</span> {{ $detalle['cantidad_compra'] ?? $detalle['cantidad'] ?? 'Sin registro' }}</div>
                </div>
            @empty
                <p>No hay medicamentos registrados en esta receta.</p>
            @endforelse
        @else
            <table class="items">
                <thead>
                    <tr>
                        <th style="width: 24%">Medicamento</th>
                        <th style="width: 16%">Presentación</th>
                        <th>Indicación / posología</th>
                        <th style="width: 15%">Periodo</th>
                        <th style="width: 13%">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($receta->detalle as $detalle)
                    <tr>
                        <td><strong>{{ $detalle['producto'] ?? 'Sin registro' }}</strong>@if(!empty($detalle['farmaco']))<br><small>{{ $detalle['farmaco'] }}</small>@endif</td>
                        <td>{{ $detalle['presentacion'] ?? 'Sin registro' }}</td>
                        <td>{{ $detalle['posologia'] ?? 'Sin registro' }}@if(!empty($detalle['via_administracion']))<br>Vía: {{ $detalle['via_administracion'] }}@endif</td>
                        <td>{{ $detalle['periodo'] ?? 'Sin registro' }}</td>
                        <td>{{ $detalle['cantidad_compra'] ?? $detalle['cantidad'] ?? 'Sin registro' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center">No hay medicamentos registrados en esta receta.</td></tr>
                @endforelse
                </tbody>
            </table>
        @endif

        @if(!empty($mostrar['firmas']))
            <table class="signature">
                <tr>
                    <td>
                        <img class="qr" src="data:image/svg+xml;base64,{{ base64_encode($receta->qr->documento) }}">
                        <div>Validación del documento</div>
                    </td>
                    <td><div class="line">Firma médico/a veterinario/a (opcional)</div></td>
                    <td>
                        <img class="qr" src="data:image/svg+xml;base64,{{ base64_encode($receta->qr_prof->profesional) }}">
                        <div>{{ $nombreProfesional }}</div>
                        <div>Firma digital profesional</div>
                    </td>
                </tr>
            </table>
        @endif

        @if(!empty($mostrar['codigo']))
            <div class="token">Código de validación: {{ $receta->qr->token }}</div>
        @endif
        <div class="footer">
            Documento veterinario asociado a la Ficha Veterinaria Única N.º {{ $receta->ficha_atencion->id }}.
            @if($plantilla['pie'] !== '')<br>{{ $plantilla['pie'] }}@endif
        </div>
    </section>
@empty
    <p>No existen recetas veterinarias registradas para esta atención.</p>
@endforelse
</body>
</html>
