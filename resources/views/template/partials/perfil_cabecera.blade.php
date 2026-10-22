{{--
    Cabecera de "Mi perfil", igual para todos los tipos de usuario: foto redonda a la izquierda
    y al lado el nombre con el rol. Va dentro del .row de .user-about-block, antes de las pestañas.

    Uso:
        @include('template.partials.perfil_cabecera', [
            'nombre' => 'Jaime Kriman Astorga',
            'rol' => 'Profesional',
            'foto' => $profesional->foto_perfil,
            'foto_base' => 'images/iconos/usuario_profesional.svg',
        ])

    Opcionales:
        id_foto      id de la imagen, para las vistas que la actualizan por js
        al_cambiar   js de "Cambiar foto de perfil"
        al_eliminar  js de "Eliminar fotografía"
        nombre_tal_cual  true para mostrar el nombre como viene, sin pasarlo a mayúscula inicial
                         (nombres de instituciones con siglas)
        datos_perfil lista con los datos que el usuario puede completar; con ella se muestra a la derecha
                     (solo en escritorio) el porcentaje de avance del perfil

    La vista que la usa va sin botón Volver: @section('sin_boton_volver', '1')
--}}
@php
    $base_cabecera = asset($foto_base ?? 'images/iconos/usuario.svg');
    $foto_cabecera = trim($foto ?? '');
    if ($foto_cabecera === '') {
        // Sin fotografía cargada se muestra el icono del tipo de usuario
        $foto_cabecera = $base_cabecera;
    } elseif (!\Illuminate\Support\Str::startsWith($foto_cabecera, ['http://', 'https://', '/'])) {
        $foto_cabecera = asset('storage/' . $foto_cabecera);
    }

    $nombre_cabecera = trim(preg_replace('/\s+/', ' ', $nombre ?? ''));
    if ($nombre_cabecera === '') {
        $nombre_cabecera = trim(Auth::user()->name ?? '');
    }
    if (empty($nombre_tal_cual)) {
        $nombre_cabecera = mb_convert_case($nombre_cabecera, MB_CASE_TITLE, 'UTF-8');
    }

    // Avance del perfil: datos con valor sobre el total (un id en 0 cuenta como vacío)
    $avance_cabecera = null;
    $faltan_cabecera = 0;
    if (!empty($datos_perfil) && is_array($datos_perfil)) {
        $completos_cabecera = collect($datos_perfil)
            ->filter(fn ($dato) => !in_array(trim((string) $dato), ['', '0'], true))
            ->count();
        $faltan_cabecera = count($datos_perfil) - $completos_cabecera;
        $avance_cabecera = (int) round($completos_cabecera * 100 / count($datos_perfil));
    }

    // Largo del medio círculo del medidor (pi por el radio, 52)
    $largo_medidor = 163.36;
    $detalle_avance = $faltan_cabecera === 0
        ? 'Tu perfil está completo'
        : ($faltan_cabecera === 1 ? 'Te falta 1 dato por completar' : 'Te faltan ' . $faltan_cabecera . ' datos por completar');
@endphp
<div class="col-12">
    <div class="perfil-cabecera">
        <div class="change-profile">
            <div class="dropdown w-auto d-inline-block">
                <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Cambiar foto de perfil">
                    <div class="profile-dp">
                        <div class="position-relative d-inline-block">
                            {{-- Si el archivo de la foto ya no existe, queda el icono base --}}
                            <img class="img-radius perfil-cabecera-foto" id="{{ $id_foto ?? 'profile-image' }}"
                                src="{{ $foto_cabecera }}" alt="Foto de perfil"
                                onerror="this.onerror=null;this.src='{{ $base_cabecera }}';">
                        </div>
                        <div class="overlay">
                            <span>Actualizar</span>
                        </div>
                    </div>
                    <span class="perfil-cabecera-camara" aria-hidden="true"><i class="feather icon-camera"></i></span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="{{ $al_cambiar ?? '' }}; return false;">
                        <i class="feather icon-upload-cloud mr-2"></i>Cambiar foto de perfil
                    </a>
                    <a class="dropdown-item" href="#" onclick="{{ $al_eliminar ?? '' }}; return false;">
                        <i class="feather icon-trash-2 mr-2"></i>Eliminar fotografía
                    </a>
                </div>
            </div>
        </div>
        <div class="perfil-cabecera-datos">
            <span class="perfil-cabecera-nombre">{{ $nombre_cabecera }}</span>
            @if (!empty($rol))
                <span class="perfil-cabecera-rol">{{ $rol }}</span>
            @endif
        </div>
        @if (!is_null($avance_cabecera))
            {{-- Avance del perfil, solo en pantallas de escritorio: medio círculo que se va llenando --}}
            <div class="perfil-cabecera-avance d-none d-lg-flex">
                <div class="perfil-cabecera-medidor" data-avance="{{ $avance_cabecera }}" data-largo="{{ $largo_medidor }}" title="{{ $detalle_avance }}">
                    <svg viewBox="0 0 120 66" aria-hidden="true" focusable="false">
                        <path class="perfil-cabecera-medidor-fondo" d="M 8 60 A 52 52 0 0 1 112 60"></path>
                        @if ($avance_cabecera > 0)
                            <path class="perfil-cabecera-medidor-avance" d="M 8 60 A 52 52 0 0 1 112 60"
                                stroke-dasharray="{{ round($avance_cabecera * $largo_medidor / 100, 2) }} {{ $largo_medidor }}"></path>
                        @endif
                    </svg>
                    <div class="perfil-cabecera-medidor-texto">
                        <span class="perfil-cabecera-medidor-etiqueta">Perfil completo</span>
                        <span class="perfil-cabecera-medidor-numero">{{ $avance_cabecera }}%</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@once
<script>
    // Muestra u oculta la contraseña al presionar el ojo del campo
    document.addEventListener('click', function (evento) {
        var boton = evento.target.closest ? evento.target.closest('.campo-clave-ver') : null;
        if (!boton || evento.ojo_atendido) return;
        evento.ojo_atendido = true;

        // Se busca primero el campo del mismo recuadro, por si en la página hay id repetidos
        var envoltura = boton.closest('.campo-clave');
        var campo = (envoltura && envoltura.querySelector('input')) || document.getElementById(boton.getAttribute('data-ver-clave'));
        if (!campo) return;

        var visible = campo.type === 'password';
        campo.type = visible ? 'text' : 'password';
        boton.setAttribute('aria-pressed', visible ? 'true' : 'false');
        boton.setAttribute('aria-label', visible ? 'Ocultar la contraseña' : 'Mostrar la contraseña');
        boton.innerHTML = '<i class="feather ' + (visible ? 'icon-eye' : 'icon-eye-off') + '" aria-hidden="true"></i>';
    });

    document.addEventListener('DOMContentLoaded', function () {
        // El medidor del perfil sube desde cero hasta el porcentaje que lleva completado
        var medidores = document.querySelectorAll('.perfil-cabecera-medidor');
        var sin_animacion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        for (var i = 0; i < medidores.length; i++) {
            (function (medidor) {
                var meta = parseInt(medidor.getAttribute('data-avance'), 10) || 0;
                var largo = parseFloat(medidor.getAttribute('data-largo')) || 0;
                var arco = medidor.querySelector('.perfil-cabecera-medidor-avance');
                var numero = medidor.querySelector('.perfil-cabecera-medidor-numero');
                var inicio = null;

                if (!meta || !arco || !numero || sin_animacion || !window.requestAnimationFrame) {
                    return;
                }

                function avanzar(ahora) {
                    if (inicio === null) {
                        inicio = ahora;
                    }

                    // Parte rápido y frena al llegar
                    var tramo = Math.min((ahora - inicio) / 1400, 1);
                    var valor = meta * (1 - Math.pow(1 - tramo, 3));

                    arco.setAttribute('stroke-dasharray', (valor * largo / 100).toFixed(2) + ' ' + largo);
                    numero.textContent = Math.round(valor) + '%';

                    if (tramo < 1) {
                        window.requestAnimationFrame(avanzar);
                    }
                }

                arco.setAttribute('stroke-dasharray', '0 ' + largo);
                numero.textContent = '0%';
                window.requestAnimationFrame(avanzar);
            })(medidores[i]);
        }

        // El botón Cancelar del pie de cada tarjeta la deja como estaba, igual que el botón de editar de arriba
        if (!window.jQuery) {
            return;
        }

        jQuery(document).on('click', '.pcoded-content .card .btn', function (evento) {
            var boton = jQuery(this);
            if (jQuery.trim(boton.text()) !== 'Cancelar') {
                return;
            }

            // Los que ya colapsan, cierran un modal o recargan la página siguen igual
            if (boton.is('[data-toggle], [data-dismiss]') || /reload/.test(boton.attr('onclick') || '') || boton.closest('.modal').length) {
                return;
            }

            // Se busca en la tarjeta el botón de editar que abre el bloque donde está este Cancelar
            var editar = boton.closest('.card').find('[data-toggle="collapse"]').filter(function () {
                var destino = jQuery(this).attr('data-target') || jQuery(this).attr('href');
                return destino && boton.closest(destino).length > 0;
            }).first();

            if (editar.length) {
                // Un Cancelar de tipo reset además devuelve los campos a como estaban
                if (boton.attr('type') !== 'reset') {
                    evento.preventDefault();
                }
                editar.trigger('click');
            }
        });
    });
</script>
@endonce
