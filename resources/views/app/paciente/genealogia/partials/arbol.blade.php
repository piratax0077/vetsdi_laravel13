{{-- Árbol genealógico compartido entre la genealogía (editable) y el certificado (solo lectura) --}}
@php
    $editable = $editable ?? false;
    $crias = $crias ?? collect();
    $f = $familiares;
    $sexoDe = fn ($sexo) => $sexo === 'M' ? 'macho' : ($sexo === 'F' ? 'hembra' : null);
    $datosFicha = fn ($familiar, $extra = []) => array_merge([
        'urlFoto' => $familiar['foto'],
        'etiquetaFicha' => $familiar['etiqueta'],
        'nombreFicha' => $familiar['nombre'],
        'especieFicha' => $familiar['especie'],
        'sexoFicha' => $sexoDe($familiar['sexo']),
    ], $editable ? $extra : []);

    // solo se dibujan las líneas que tienen al menos un integrante
    $lineas = [];
    foreach ([['Línea paterna', 'padre', 'abuelo_paterno', 'abuela_paterna'], ['Línea materna', 'madre', 'abuelo_materno', 'abuela_materna']] as [$titulo, $progenitor, $abuelo, $abuela]) {
        $abuelos = array_values(array_filter([$f[$abuelo], $f[$abuela]]));
        if ($f[$progenitor] || $abuelos) {
            $lineas[] = ['titulo' => $titulo, 'progenitor' => $progenitor, 'abuelos' => $abuelos];
        }
    }
    $sinFamiliares = !$lineas && $hermanos->isEmpty();
@endphp
<section class="arbol-genealogico" aria-label="Árbol genealógico de {{ $mascota->nombre }}">
    <div class="arbol-centro {{ $hermanos->isNotEmpty() ? 'con-hermanos' : '' }}">
        @if($hermanos->isNotEmpty())
            <span class="arbol-rama-titulo">{{ $mascota->nombre }} y sus hermanos</span>
        @endif
        <div class="arbol-centro-lista">
            @include('app.paciente.genealogia.partials.ficha', [
                'urlFoto' => $mascota->foto_url,
                'etiquetaFicha' => $etiquetaPrincipal ?? 'Mascota',
                'nombreFicha' => $mascota->nombre,
                'especieFicha' => $mascota->tipo_especie,
                'sexoFicha' => null,
                'principal' => true,
            ])
            @foreach($hermanos as $hermano)
                {{-- los hermanos por padres en común no se editan a mano --}}
                @include('app.paciente.genealogia.partials.ficha', $hermano['automatico']
                    ? $datosFicha($hermano) + ['notaFicha' => 'Por padres en común']
                    : $datosFicha($hermano, ['editarParentesco' => 'hermano', 'editarHermano' => $hermano['id']]))
            @endforeach
        </div>
    </div>

    @if($sinFamiliares)
        <p class="arbol-vacio">
            @if($editable)
                Aún no hay familiares registrados. Usa <strong>Agregar familiar</strong> para empezar el árbol.
            @else
                Sin familiares registrados.
            @endif
        </p>
    @endif

    @if($lineas)
        <div class="arbol-union arbol-union-padres {{ count($lineas) === 1 ? 'es-recta' : '' }}"></div>
        <div class="arbol-familia {{ count($lineas) === 1 ? 'una-linea' : '' }}">
            @foreach($lineas as $linea)
                <div class="arbol-rama">
                    <span class="arbol-rama-titulo">{{ $linea['titulo'] }}</span>
                    <div class="arbol-progenitor">
                        @if($f[$linea['progenitor']])
                            @include('app.paciente.genealogia.partials.ficha', $datosFicha($f[$linea['progenitor']], ['editarParentesco' => $linea['progenitor']]))
                        @elseif($editable)
                            {{-- falta el eslabón del medio: se muestra una etiqueta para no perder la línea --}}
                            <button type="button" class="arbol-sin-registrar js-agregar-familiar" data-parentesco="{{ $linea['progenitor'] }}">
                                <i class="fas fa-plus"></i> {{ $parentescos[$linea['progenitor']]['etiqueta'] }} sin registrar
                            </button>
                        @else
                            <span class="arbol-sin-registrar es-fija">{{ $parentescos[$linea['progenitor']]['etiqueta'] }} sin registrar</span>
                        @endif
                    </div>
                    @if($linea['abuelos'])
                        <div class="arbol-union {{ count($linea['abuelos']) === 1 ? 'es-recta' : '' }}"></div>
                        <div class="arbol-pareja {{ count($linea['abuelos']) === 1 ? 'una-ficha' : '' }}">
                            @foreach($linea['abuelos'] as $abuelo)
                                @include('app.paciente.genealogia.partials.ficha', $datosFicha($abuelo, ['editarParentesco' => $abuelo['parentesco']]))
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($crias->isNotEmpty())
        <div class="arbol-crias">
            <span class="arbol-rama-titulo">Crías de {{ $mascota->nombre }}</span>
            <div class="arbol-crias-lista">
                @foreach($crias as $cria)
                    @php $criaMascota = $cria->mascota; @endphp
                    @include('app.paciente.genealogia.partials.ficha', [
                        'urlFoto' => optional($criaMascota)->foto_url,
                        'etiquetaFicha' => 'Cría',
                        'nombreFicha' => optional($criaMascota)->nombre ?? 'Sin nombre',
                        'especieFicha' => $criaMascota ? $criaMascota->tipo_especie : 'Especie no indicada',
                        'sexoFicha' => $sexoDe(optional($criaMascota)->sexo),
                    ])
                @endforeach
            </div>
        </div>
    @endif
</section>
