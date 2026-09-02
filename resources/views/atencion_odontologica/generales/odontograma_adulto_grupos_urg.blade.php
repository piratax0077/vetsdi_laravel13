
@php
    use Illuminate\Support\Str;

    $esMascota = request()->filled('id_mascota');
    $nombreEspecieMascotaUrg = strtolower(trim(
        (string) (
            optional(optional($mascota ?? null)->especieMascota)->nombre
            ?? optional($mascota ?? null)->especie
            ?? ''
        )
    ));
    $esCaninaUrg = $esMascota && Str::contains($nombreEspecieMascotaUrg, ['canin', 'perro']);

    // Crear un array para almacenar el estado final de cada pieza
    $piezasEstado = [];
    $fuenteHistorialUrg = collect($odontograma_historial ?? $odontograma ?? []);
    if ($fuenteHistorialUrg->isNotEmpty()) {
        // Agrupar por pieza
        $historialPorPieza = [];
        foreach ($fuenteHistorialUrg as $pieza) {
            $codigoPieza = (string) data_get($pieza, 'pieza', '');
            if ($codigoPieza === '') {
                continue;
            }
            $historialPorPieza[$codigoPieza][] = $pieza;
        }

        foreach ($historialPorPieza as $codigoPieza => $historial) {
            $estadoFinal = 'normal';
            foreach ($historial as $pieza) {
                $tratamiento = Str::lower((string) data_get($pieza, 'tratamiento', data_get($pieza, 'descripcion', '')));
                $diagnostico = Str::lower((string) data_get($pieza, 'diagnostico', ''));
                $estado = (string) data_get($pieza, 'estado', '');

                if (Str::contains($diagnostico, 'carie') || Str::contains($tratamiento, 'carie')) {
                    $estadoFinal = 'carie';
                }
                // Prioridad: si hay algún implante con estado 0, es ausente
                if (Str::contains($tratamiento, 'implante') || Str::contains($diagnostico, 'implante')) {
                    if ($estado === '0') {
                        $estadoFinal = 'ausente';
                        break; // No importa lo demás, es ausente
                    }
                    $estadoFinal = 'implante';
                }

                if (Str::contains($tratamiento, 'endodoncia') ||
                    Str::contains($tratamiento, 'pulpotomia') ||
                    Str::contains($tratamiento, 'pulpectomia')) {
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
        grid-template-columns: repeat(var(--odonto-cols, 8), 1fr);
        gap: 5px;
    }

    .pieza_urg {
        border: 2px solid #3974d5;
        background: linear-gradient(145deg, #ffffff 0%, #dbe9ff 100%);
        text-align: center;
        padding: 8px 5px;
        cursor: pointer;
        border-radius: 13px;
        transition: 0.1s ease;
        font-size:0.85rem;
        color: #123e8b;
        font-weight: 700;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 82px;
        position: relative;
    }

    .pieza_urg img {
        width: 39px;
        height: 39px;
        object-fit: contain;
        margin-bottom: 5px;
        pointer-events: none;
        filter: brightness(0) saturate(100%) invert(24%) sepia(18%) saturate(1537%) hue-rotate(176deg) brightness(91%) contrast(92%);
        opacity: .92;
        drop-shadow: 0 1px 1px rgba(15, 48, 88, .22);
    }

    .pieza_urg:hover {
        background: #c8ddff;
        border-color: #174fa9;
        box-shadow: 0 5px 12px rgba(35, 83, 181, .18);
        transform: translateY(-1px);
    }

    .pieza_urg.seleccionada {
        background-color: #5b2584;
        color: #fff;
        border-color: #35104d;
        box-shadow: 0 0 0 2px rgba(53, 16, 77, .2);
    }

    .pieza_urg.seleccionada img {
        filter: brightness(0) invert(1);
        opacity: 1;
    }

    .avance-torta {
        width: 46px;
        height: 46px;
        margin: 0 auto;
        position: relative;
    }

    .avance-torta svg {
        width: 46px;
        height: 46px;
        display: block;
        transform: rotate(-90deg);
    }

    .avance-torta span {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        color: #35104d;
        font-size: 11px;
        font-weight: 700;
    }

    .pieza-urg-marca {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }

    .pieza-urg-caries {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #ff1f1f;
        box-shadow: 0 0 0 2px rgba(255, 31, 31, 0.2);
    }

    .pieza-urg-implante {
        width: 3px;
        height: 50px;
        background: #111;
        transform: translate(-50%, -50%) rotate(16deg);
        border-radius: 2px;
    }
</style>
@php
    if ($esMascota) {
        if ($esCaninaUrg) {
            $filaSuperior = [110, 109, 108, 107, 106, 105, 104, 103, 102, 101, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210];
            $filaInferior = [411, 410, 409, 408, 407, 406, 405, 404, 403, 402, 401, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311];
            $baseImagenMascotaUrg = 'images/dental/odontograma_canino';
        } else {
            $filaSuperior = [109, 108, 107, 106, 104, 103, 102, 101, 201, 202, 203, 204, 206, 207, 208, 209];
            $filaInferior = [409, 408, 407, 404, 403, 402, 401, 301, 302, 303, 304, 307, 308, 309];
            $baseImagenMascotaUrg = 'images/dental/odontograma_felino/dientes';
        }
    }
@endphp
<div class="odontograma">
    @if ($esMascota)
        <div class="fila mb-3" style="--odonto-cols: {{ count($filaSuperior) }};">
            @foreach ($filaSuperior as $pieza)
                @php
                    $codigoPieza = (string) $pieza;
                    $estadoPieza = $piezasEstado[$codigoPieza] ?? 'normal';
                    $imagenMascotaUrg = "{$baseImagenMascotaUrg}/d{$codigoPieza}.png";
                @endphp
                <div class="pieza_urg" data-pieza_urg="{{ $codigoPieza }}" onclick="seleccionarPiezaUrgenciaDirecta(event, this)">
                    <img src="{{ asset($imagenMascotaUrg) }}" alt="{{ $codigoPieza }}">
                    @if ($estadoPieza === 'carie')
                        <span class="pieza-urg-marca pieza-urg-caries"></span>
                    @endif
                    @if ($estadoPieza === 'implante')
                        <span class="pieza-urg-marca pieza-urg-implante"></span>
                    @endif
                    <span>{{ $codigoPieza }}</span>
                </div>
            @endforeach
        </div>
        <div class="fila" style="--odonto-cols: {{ count($filaInferior) }};">
            @foreach ($filaInferior as $pieza)
                @php
                    $codigoPieza = (string) $pieza;
                    $estadoPieza = $piezasEstado[$codigoPieza] ?? 'normal';
                    $imagenMascotaUrg = "{$baseImagenMascotaUrg}/d{$codigoPieza}.png";
                @endphp
                <div class="pieza_urg" data-pieza_urg="{{ $codigoPieza }}" onclick="seleccionarPiezaUrgenciaDirecta(event, this)">
                    <img src="{{ asset($imagenMascotaUrg) }}" alt="{{ $codigoPieza }}">
                    @if ($estadoPieza === 'carie')
                        <span class="pieza-urg-marca pieza-urg-caries"></span>
                    @endif
                    @if ($estadoPieza === 'implante')
                        <span class="pieza-urg-marca pieza-urg-implante"></span>
                    @endif
                    <span>{{ $codigoPieza }}</span>
                </div>
            @endforeach
        </div>
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
                <div class="pieza_urg" data-pieza_urg="{{ $codigoPieza }}" onclick="seleccionarPiezaUrgenciaDirecta(event, this)">
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
                <div class="pieza_urg" data-pieza_urg="{{ $codigoPieza }}" onclick="seleccionarPiezaUrgenciaDirecta(event, this)">
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
                <div class="pieza_urg" data-pieza_urg="{{ $codigoPieza }}" onclick="seleccionarPiezaUrgenciaDirecta(event, this)">
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
                <div class="pieza_urg" data-pieza_urg="{{ $codigoPieza }}" onclick="seleccionarPiezaUrgenciaDirecta(event, this)">
                    <img src="{{ asset($imagen) }}" alt="{{ $codigoPieza }}">
                    <span>{{ $codigoPieza }}</span>
                </div>
            @endfor
        </div>
    @endif
</div>

<script>
    function seleccionarPiezaUrgenciaDirecta(evento, elemento) {
        evento.preventDefault();
        evento.stopPropagation();

        const codigo = elemento.getAttribute('data-pieza_urg');
        const select = document.getElementById('paciente_piezas_dentales_urg');
        if (!codigo || !select) {
            return;
        }

        const opcion = Array.from(select.options).find(item => item.value === codigo);
        if (!opcion) {
            return;
        }

        const seleccionar = !elemento.classList.contains('seleccionada');
        elemento.classList.toggle('seleccionada', seleccionar);
        opcion.selected = seleccionar;

        if (window.jQuery) {
            window.jQuery(select).trigger('change');
        } else {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
</script>
