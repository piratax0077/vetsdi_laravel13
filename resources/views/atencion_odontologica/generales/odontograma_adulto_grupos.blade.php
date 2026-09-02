
@php
    use Illuminate\Support\Str;

    $esMascotaOdontoGeneral = request()->filled('id_mascota');
    $nombreEspecieOdontoGeneral = strtolower(trim(
        (string) (
            optional(optional($mascota ?? null)->especieMascota)->nombre
            ?? optional($mascota ?? null)->especie
            ?? ''
        )
    ));
    $esCaninoOdontoGeneral = $esMascotaOdontoGeneral
        && Str::contains($nombreEspecieOdontoGeneral, ['canin', 'perro']);

    if ($esMascotaOdontoGeneral && $esCaninoOdontoGeneral) {
        $filasMascotaOdontoGeneral = [
            [110, 109, 108, 107, 106, 105, 104, 103, 102, 101],
            [201, 202, 203, 204, 205, 206, 207, 208, 209, 210],
            [411, 410, 409, 408, 407, 406, 405, 404, 403, 402, 401],
            [301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311],
        ];
        $baseImagenMascotaOdontoGeneral = 'images/dental/odontograma_canino';
    } elseif ($esMascotaOdontoGeneral) {
        $filasMascotaOdontoGeneral = [
            [109, 108, 107, 106, 104, 103, 102, 101],
            [201, 202, 203, 204, 206, 207, 208, 209],
            [409, 408, 407, 404, 403, 402, 401],
            [301, 302, 303, 304, 307, 308, 309],
        ];
        $baseImagenMascotaOdontoGeneral = 'images/dental/odontograma_felino/dientes';
    }

    // Crear un array para almacenar el estado final de cada pieza
    $piezasEstado = [];
    if(isset($odontograma_historial)){
        // Agrupar por pieza
        $historialPorPieza = [];
        foreach ($odontograma_historial as $pieza) {
            $codigoPieza = $pieza['pieza'];
            $historialPorPieza[$codigoPieza][] = $pieza;
        }

        foreach ($historialPorPieza as $codigoPieza => $historial) {
            $estadoFinal = 'normal';
            foreach ($historial as $pieza) {
                $tratamiento = $pieza['tratamiento'] ?? '';
                $diagnostico = $pieza['diagnostico'] ?? '';
                $estado = $pieza['estado'] ?? '';

                if (Str::contains($diagnostico, 'Carie')) {
                    $estadoFinal = 'carie';
                }
                // Prioridad: si hay algún implante con estado 0, es ausente
                if (Str::contains(Str::lower($tratamiento), 'implante')) {
                    if ($estado == '0') {
                        $estadoFinal = 'ausente';
                        break; // No importa lo demás, es ausente
                    } else {
                        $estadoFinal = 'implante';
                    }
                }

                // endodoncia
                if (Str::contains(Str::lower($tratamiento), 'endodoncia') ||
                    Str::contains(Str::lower($tratamiento), 'pulpotomia') ||
                    Str::contains(Str::lower($tratamiento), 'pulpectomia')) {
                    $estadoFinal = 'endodoncia';
                }
            }
            $piezasEstado[$codigoPieza] = $estadoFinal;
        }
    }
@endphp
<style>
.odontograma {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom:20px;
    margin-top:10px;

}

.fila {
    display: grid;
    grid-template-columns: repeat(var(--odonto-cols, 8), minmax(54px, 1fr));
    gap: 5px;
    overflow-x: auto;
}

.pieza {
        border: 1px solid #749ef1;
        background-color: #ddecff;
        text-align: center;
        padding: 8px 5px;
        cursor: pointer;
        border-radius: 13px;
        transition: 0.1s ease;
        font-size:0.85rem;
        color: #2353b5;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 80px;
        position: relative;
    }

.pieza img {
        width: 35px;
        height: 35px;
        object-fit: contain;
        margin-bottom: 5px;
        pointer-events: none;
    }

.pieza.seleccionada {
    background-color: #5b2584;
    color: #fff;
    border-color: #35104d;
    box-shadow: 0 0 0 2px rgba(53, 16, 77, .2);
}

.pieza.seleccionada img {
    filter: brightness(.55);
}

.pieza-mascota-general img {
    filter: brightness(0) saturate(100%) invert(24%) sepia(18%) saturate(1537%) hue-rotate(176deg) brightness(91%) contrast(92%);
}

.pieza-mascota-general.seleccionada img {
    filter: brightness(0) invert(1);
}

.avance-torta {
    --avance: 0%;
    width: 46px;
    height: 46px;
    margin: 0 auto;
    border-radius: 50%;
    background: conic-gradient(#5b2584 var(--avance), #dfe5ee 0);
    display: grid;
    place-items: center;
    position: relative;
}

.avance-torta::before {
    content: '';
    position: absolute;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fff;
}

.avance-torta span {
    position: relative;
    z-index: 1;
    color: #35104d;
    font-size: 11px;
    font-weight: 700;
}


</style>
<div class="odontograma">
    @if ($esMascotaOdontoGeneral)
        @foreach ($filasMascotaOdontoGeneral as $indiceFilaMascota => $filaMascotaOdontoGeneral)
            <div class="fila {{ $indiceFilaMascota === 1 ? 'mb-3' : '' }}" style="--odonto-cols: {{ count($filaMascotaOdontoGeneral) }};">
                @foreach ($filaMascotaOdontoGeneral as $piezaMascotaOdontoGeneral)
                    @php
                        $codigoPieza = (string) $piezaMascotaOdontoGeneral;
                        $imagenMascotaOdontoGeneral = $baseImagenMascotaOdontoGeneral . '/d' . $codigoPieza . '.png';
                    @endphp
                    <div class="pieza pieza-mascota-general" data-pieza="{{ $codigoPieza }}" onclick="seleccionarPiezaOdontoGeneralDirecta(event, this)">
                        <img src="{{ asset($imagenMascotaOdontoGeneral) }}" alt="{{ $codigoPieza }}">
                        <span>{{ $codigoPieza }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach
    @else
    <!-- Fila superior (1.8 al 1.1 y 2.1 al 2.8) -->
    <div class="fila mb-3">
        @for($i = 18; $i >= 11; $i--)
        @php
                $codigoPieza = '1.' . ($i % 10);
                $codigoPiezaImagen = '1' . ($i % 10);
                $estadoPieza = $piezasEstado[$codigoPieza] ?? 'normal';

                // Determinar la imagen según el estado para implantología
                switch ($estadoPieza) {
                    case 'carie':
                        $imagen = "images/dental/dientes/carie/carie{$codigoPiezaImagen}.png";
                        break;
                    case 'ausente':
                        $imagen = "images/dental/dientes/diente-ausente/dau{$codigoPiezaImagen}.png";
                        break;
                    case 'implante':
                        $imagen = "images/dental/dientes/implante/impl{$codigoPiezaImagen}.png";
                        break;
                    case 'endodoncia':
                        $imagen = "images/dental/dientes/endodoncia/endo{$codigoPiezaImagen}.png";
                        break;
                    default:
                        $imagen = "images/dental/dientes/d{$codigoPiezaImagen}.png";
                        break;
                }
            @endphp
            <div class="pieza" data-pieza="{{ $codigoPieza }}" onclick="seleccionarPiezaOdontoGeneralDirecta(event, this)">
                <img src="{{ asset($imagen) }}" alt="{{ $codigoPieza }}">
                <span>{{ $codigoPieza }}</span>
            </div>
        @endfor

        @for($i = 21; $i <= 28; $i++)
         @php
                $codigoPieza = '2.' . ($i % 10);
                $codigoPiezaImagen = '2' . ($i % 10);
                $estadoPieza = $piezasEstado[$codigoPieza] ?? 'normal';

                // Determinar la imagen según el estado para implantología
                switch ($estadoPieza) {
                    case 'carie':
                        $imagen = "images/dental/dientes/carie/carie{$codigoPiezaImagen}.png";
                        break;
                    case 'ausente':
                        $imagen = "images/dental/dientes/diente-ausente/dau{$codigoPiezaImagen}.png";
                        break;
                    case 'implante':
                        $imagen = "images/dental/dientes/implante/impl{$codigoPiezaImagen}.png";
                        break;
                    case 'endodoncia':
                        $imagen = "images/dental/dientes/endodoncia/endo{$codigoPiezaImagen}.png";
                        break;
                    default:
                        $imagen = "images/dental/dientes/d{$codigoPiezaImagen}.png";
                        break;
                }
            @endphp
            <div class="pieza" data-pieza="{{ $codigoPieza }}" onclick="seleccionarPiezaOdontoGeneralDirecta(event, this)">
                <img src="{{ asset($imagen) }}" alt="{{ $codigoPieza }}">
                <span>{{ $codigoPieza }}</span>
            </div>
        @endfor
    </div>

    <!-- Fila inferior (4.8 al 4.1 y 3.1 al 3.8) -->
    <div class="fila">
        @for($i = 48; $i >= 41; $i--)
         @php
                $codigoPieza = '4.' . ($i % 10);
                $codigoPiezaImagen = '4' . ($i % 10);
                $estadoPieza = $piezasEstado[$codigoPieza] ?? 'normal';

                // Determinar la imagen según el estado para implantología
                switch ($estadoPieza) {
                    case 'carie':
                        $imagen = "images/dental/dientes/carie/carie{$codigoPiezaImagen}.png";
                        break;
                    case 'ausente':
                        $imagen = "images/dental/dientes/diente-ausente/dau{$codigoPiezaImagen}.png";
                        break;
                    case 'implante':
                        $imagen = "images/dental/dientes/implante/impl{$codigoPiezaImagen}.png";
                        break;
                    case 'endodoncia':
                        $imagen = "images/dental/dientes/endodoncia/endo{$codigoPiezaImagen}.png";
                        break;
                    default:
                        $imagen = "images/dental/dientes/d{$codigoPiezaImagen}.png";
                        break;
                }
            @endphp
            <div class="pieza" data-pieza="{{ $codigoPieza }}" onclick="seleccionarPiezaOdontoGeneralDirecta(event, this)">
                <img src="{{ asset($imagen) }}" alt="{{ $codigoPieza }}">
                <span>{{ $codigoPieza }}</span>
            </div>
        @endfor

        @for($i = 31; $i <= 38; $i++)
         @php
                $codigoPieza = '3.' . ($i % 10);
                $codigoPiezaImagen = '3' . ($i % 10);
                $estadoPieza = $piezasEstado[$codigoPieza] ?? 'normal';

                // Determinar la imagen según el estado para implantología
                switch ($estadoPieza) {
                    case 'carie':
                        $imagen = "images/dental/dientes/carie/carie{$codigoPiezaImagen}.png";
                        break;
                    case 'ausente':
                        $imagen = "images/dental/dientes/diente-ausente/dau{$codigoPiezaImagen}.png";
                        break;
                    case 'implante':
                        $imagen = "images/dental/dientes/implante/impl{$codigoPiezaImagen}.png";
                        break;
                    case 'endodoncia':
                        $imagen = "images/dental/dientes/endodoncia/endo{$codigoPiezaImagen}.png";
                        break;
                    default:
                        $imagen = "images/dental/dientes/d{$codigoPiezaImagen}.png";
                        break;
                }
            @endphp
            <div class="pieza" data-pieza="{{ $codigoPieza }}" onclick="seleccionarPiezaOdontoGeneralDirecta(event, this)">
                <img src="{{ asset($imagen) }}" alt="{{ $codigoPieza }}">
                <span>{{ $codigoPieza }}</span>
            </div>
        @endfor
    </div>
    @endif
</div>

<script>
    function seleccionarPiezaOdontoGeneralDirecta(event, elemento) {
        event.preventDefault();
        event.stopPropagation();

        const codigo = String(elemento.getAttribute('data-pieza'));
        const select = $('#paciente_piezas_dentales_ex');
        const opcion = select.find("option[value='" + codigo + "']");

        if (!opcion.length) return;

        const seleccionar = !elemento.classList.contains('seleccionada');
        elemento.classList.toggle('seleccionada', seleccionar);
        opcion.prop('selected', seleccionar);
        select.trigger('change');
    }
</script>


