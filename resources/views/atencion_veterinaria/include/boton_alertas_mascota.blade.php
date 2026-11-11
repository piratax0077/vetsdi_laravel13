{{-- Botón "Alertas" junto al botón + morado: vacunas y desparasitación atrasadas y enfermedades crónicas --}}
@php
    $alertasMascota = isset($mascota) && $mascota ? \App\Support\ResumenMascotaFicha::alertas($mascota) : null;
@endphp

@if (is_array($alertasMascota))
<style>
    .alertas-mascota__boton {
        position: fixed;
        right: 92px;
        bottom: 28px;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 40px;
        padding: 0 16px;
        border: 0;
        border-radius: 20px;
        background-color: #cc2b2b;
        color: #fff;
        font-family: 'Nunito', sans-serif;
        font-size: 14px;
        font-weight: 700;
        box-shadow: 0 1px 10px rgba(0, 0, 0, .3);
        cursor: pointer;
        transition: background-color .2s ease;
    }
    .alertas-mascota__boton:hover,
    .alertas-mascota__boton[aria-expanded="true"] { background-color: #a82020; }
    .alertas-mascota__boton:focus-visible { outline: 2px solid #f3b4b4; outline-offset: 2px; }
    .alertas-mascota__boton .feather { font-size: 16px; }
    .alertas-mascota__contador {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        background-color: #fff;
        color: #cc2b2b;
        font-size: 12px;
        line-height: 20px;
        text-align: center;
    }

    .alertas-mascota__panel {
        position: fixed;
        right: 20px;
        bottom: 84px;
        z-index: 10;
        width: 360px;
        max-width: calc(100vw - 32px);
        max-height: calc(100vh - 140px);
        overflow-y: auto;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 6px 24px rgba(39, 39, 39, .22);
        font-family: 'Nunito Sans', 'Nunito', sans-serif;
    }
    .alertas-mascota__panel[hidden] { display: none; }
    .alertas-mascota__cabecera {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background-color: #cc2b2b;
        border-radius: 10px 10px 0 0;
        color: #fff;
    }
    .alertas-mascota__cabecera h6 {
        margin: 0;
        color: #fff;
        font-family: 'Nunito', sans-serif;
        font-size: 15px;
        font-weight: 700;
    }
    .alertas-mascota__cerrar {
        border: 0;
        background: transparent;
        color: #fff;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }
    .alertas-mascota__lista {
        margin: 0;
        padding: 6px 14px 10px;
        list-style: none;
    }
    .alertas-mascota__item {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #eceef3;
    }
    .alertas-mascota__item:last-child { border-bottom: 0; }
    .alertas-mascota__icono {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #fdeceb;
        color: #c53a32;
        font-size: 16px;
        line-height: 32px;
        text-align: center;
    }
    .alertas-mascota__titulo {
        display: block;
        margin-bottom: 2px;
        color: #272727;
        font-family: 'Nunito', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }
    .alertas-mascota__detalle {
        display: block;
        color: #555;
        font-size: 13px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .alertas-mascota__vacio {
        padding: 16px 14px;
        color: #6c757d;
        font-size: 13px;
        text-align: center;
    }

    @media (max-width: 575.98px) {
        .alertas-mascota__boton { right: 88px; padding: 0 12px; }
        .alertas-mascota__panel { right: 16px; }
    }
</style>

<button type="button" class="alertas-mascota__boton" id="alertas_mascota_boton"
        aria-controls="alertas_mascota_panel" aria-expanded="false">
    <i class="feather icon-bell"></i>
    Alertas
    <span class="alertas-mascota__contador">{{ count($alertasMascota) }}</span>
</button>

<div class="alertas-mascota__panel" id="alertas_mascota_panel" role="dialog" aria-labelledby="alertas_mascota_titulo" hidden>
    <div class="alertas-mascota__cabecera">
        <h6 id="alertas_mascota_titulo"><i class="feather icon-bell mr-1"></i> Alertas de {{ $mascota->nombre }}</h6>
        <button type="button" class="alertas-mascota__cerrar" id="alertas_mascota_cerrar" aria-label="Cerrar">&times;</button>
    </div>

    @if (count($alertasMascota))
        <ul class="alertas-mascota__lista">
            @foreach ($alertasMascota as $alerta)
                <li class="alertas-mascota__item">
                    <span class="alertas-mascota__icono"><i class="feather {{ $alerta['icono'] }}"></i></span>
                    <div>
                        <span class="alertas-mascota__titulo">{{ $alerta['titulo'] }}</span>
                        @foreach ($alerta['detalle'] as $linea)
                            <span class="alertas-mascota__detalle">{{ $linea }}</span>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <div class="alertas-mascota__vacio">Esta mascota no tiene alertas: vacunas y desparasitación al día y sin enfermedades crónicas.</div>
    @endif
</div>

<script>
    (function () {
        var boton = document.getElementById('alertas_mascota_boton');
        var panel = document.getElementById('alertas_mascota_panel');
        var cerrar = document.getElementById('alertas_mascota_cerrar');
        if (!boton || !panel) return;

        function mostrar(abrir) {
            panel.hidden = !abrir;
            boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        }

        boton.addEventListener('click', function (evento) {
            evento.stopPropagation();
            mostrar(panel.hidden);
        });
        cerrar.addEventListener('click', function () { mostrar(false); });

        // Se cierra al hacer clic fuera del panel o con la tecla Escape
        document.addEventListener('click', function (evento) {
            if (!panel.hidden && !panel.contains(evento.target)) mostrar(false);
        });
        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape' && !panel.hidden) {
                mostrar(false);
                boton.focus();
            }
        });
    }());
</script>
@endif
