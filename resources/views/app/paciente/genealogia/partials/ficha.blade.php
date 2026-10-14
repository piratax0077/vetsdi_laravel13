{{-- Ficha de un integrante del árbol genealógico; si trae parentesco se puede editar con un clic --}}
@php $editable = !empty($editarParentesco); @endphp
<div class="arbol-ficha {{ !empty($sexoFicha) ? 'arbol-ficha-' . $sexoFicha : '' }} {{ !empty($principal) ? 'es-principal' : '' }} {{ $editable ? 'es-editable js-editar-familiar' : '' }}"
    @if($editable)
        data-parentesco="{{ $editarParentesco }}"
        @if(!empty($editarHermano)) data-hermano="{{ $editarHermano }}" @endif
        role="button" tabindex="0" title="Editar {{ $etiquetaFicha }}"
    @endif>
    @if($editable)
        <span class="arbol-ficha-lapiz" aria-hidden="true"><i class="fas fa-pen"></i></span>
    @endif
    @if($urlFoto)
        <img class="arbol-foto js-genealogy-viewer" src="{{ $urlFoto }}" data-caption="{{ $nombreFicha }} · {{ $especieFicha }}" alt="{{ $nombreFicha }}">
    @else
        <span class="arbol-foto arbol-foto-vacia"><i class="fas fa-paw"></i></span>
    @endif
    <span class="arbol-etiqueta">
        @if(($sexoFicha ?? '') === 'macho')<i class="fas fa-mars"></i>@elseif(($sexoFicha ?? '') === 'hembra')<i class="fas fa-venus"></i>@endif
        {{ $etiquetaFicha }}
    </span>
    <strong class="arbol-nombre">{{ $nombreFicha }}</strong>
    <span class="arbol-especie">{{ $especieFicha }}</span>
    @if(!empty($notaFicha))
        <small class="arbol-nota">{{ $notaFicha }}</small>
    @endif
</div>
