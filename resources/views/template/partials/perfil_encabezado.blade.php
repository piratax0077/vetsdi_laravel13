{{--
    Menú de perfil del encabezado, igual para todos los escritorios.
    Uso: @include('template.partials.perfil_encabezado', ['tipoPerfil' => 'profesional'])
    tipoPerfil es opcional: un grupo (profesional, tutor, asistente, institucion, contador, administrador, otro)
    o un rol puntual (Tens, Ministerio...). Si no se indica, se toma según los roles del usuario.
--}}
@php
    $usuarioPerfil = Auth::user();

    // Cada rol con su grupo, el texto que se muestra y la ruta de su perfil (si tiene).
    // El orden define la prioridad cuando el usuario tiene varios roles
    $rolesConocidos = [
        'Profesional' => ['profesional', 'Profesional', 'profesional.mi_perfil'],
        'Asistente' => ['asistente', 'Asistente', 'asistente.perfil'],
        'AsistenteAdm' => ['asistente', 'Asistente administrativo', null],
        'AsistenteCaja' => ['asistente', 'Asistente de caja', 'asistentecm.perfil'],
        'AsistenteJefaCaja' => ['asistente', 'Jefatura de caja', 'asistentejcm.perfil'],
        'AsistenteManejoAgenda' => ['asistente', 'Asistente de agenda', 'asistentecm.ma.perfil'],
        'AsistenteOnline' => ['asistente', 'Asistente online', 'asistenteon.perfil'],
        'AsistenteLaboratorio' => ['asistente', 'Asistente de laboratorio', 'asistente.lab.perfil'],
        'AsistenteCargaExamenExterno' => ['asistente', 'Asistente de exámenes', null],
        'AsistenteConsulta' => ['asistente', 'Asistente de consulta', null],
        'AsistenteDental' => ['asistente', 'Asistente dental', null],
        'AsistenteDentalTecnica' => ['asistente', 'Asistente dental técnica', null],
        'AsistenteFarmacia' => ['asistente', 'Asistente de farmacia', null],
        'Institucion' => ['institucion', 'Institución', null],
        'Adm_Institucion' => ['institucion', 'Administrador de institución', null],
        'Contador' => ['contador', 'Contador', null],
        'Tens' => ['otro', 'TENS', null],
        'JefeTurno' => ['otro', 'Jefe de turno', null],
        'JefeServicio' => ['otro', 'Jefe de servicio', null],
        'RecepcionUrgencias' => ['otro', 'Recepción de urgencias', null],
        'Servicio' => ['otro', 'Servicio', null],
        'Ministerio' => ['otro', 'Ministerio', null],
        'Chofer' => ['otro', 'Chofer', null],
        'Admin' => ['administrador', 'Administrador', null],
        'Administrador-SDI' => ['administrador', 'Administrador general', null],
        'AdministradorMedico' => ['administrador', 'Administrador médico', null],
        'AdministradorTecnico' => ['administrador', 'Administrador técnico', null],
        'AdministradorLaboratorio' => ['administrador', 'Administrador de laboratorio', null],
        'AdministradorBodega' => ['administrador', 'Administrador de bodega', null],
        'Adm_Comercial' => ['administrador', 'Administrador comercial', null],
        'Paciente' => ['tutor', 'Tutor', 'paciente.perfil'],
    ];

    // Texto por defecto de cada grupo y la tabla de donde sale el nombre y la foto
    $gruposPerfil = [
        'profesional' => ['Profesional', \App\Models\Profesional::class, 'nombre', 'apellido'],
        'tutor' => ['Tutor', \App\Models\Paciente::class, 'nombres', 'apellido_uno'],
        'asistente' => ['Asistente', \App\Models\Asistente::class, 'nombres', 'apellido_uno'],
        'institucion' => ['Institución', \App\Models\Instituciones::class, 'nombre', null],
        'contador' => ['Contador', \App\Models\Contador::class, 'nombres', 'apellido_uno'],
        'administrador' => ['Administrador', null, null, null],
        'otro' => ['Usuario', null, null, null],
    ];

    $rolesUsuario = $usuarioPerfil ? $usuarioPerfil->getRoleNames()->all() : [];
    $tipoPerfil = $tipoPerfil ?? null;
    $rolElegido = null;

    // Primero se busca un rol del tipo pedido; si el usuario no tiene ninguno, cualquiera de los suyos
    foreach ([$tipoPerfil, null] as $tipoBuscado) {
        foreach ($rolesConocidos as $nombreRol => $datosRol) {
            if (in_array($nombreRol, $rolesUsuario) && (!$tipoBuscado || $datosRol[0] === $tipoBuscado || $nombreRol === $tipoBuscado)) {
                $rolElegido = $datosRol;
                break 2;
            }
        }
    }

    // El escritorio de un centro siempre muestra el centro, aunque quien lo administre no tenga el rol Institución
    if ($tipoPerfil === 'institucion' && ($rolElegido[0] ?? null) !== 'institucion') {
        $rolElegido = ['institucion', 'Institución', null];
    }

    $tipoPerfil = $rolElegido[0] ?? $tipoPerfil ?? 'otro';
    $grupoPerfil = $gruposPerfil[$tipoPerfil] ?? $gruposPerfil['otro'];
    $textoRol = $rolElegido[1] ?? $grupoPerfil[0];
    $rutaPerfil = !empty($rolElegido[2]) && \Route::has($rolElegido[2]) ? route($rolElegido[2]) : null;
    if ($tipoPerfil === 'institucion' && \Route::has('adm_cm.perfil_cm')) {
        $rutaPerfil = route('adm_cm.perfil_cm');
    }

    // Nombre y foto desde la ficha del tipo de usuario
    $fichaPerfil = null;
    $nombreEscritorio = null;
    if ($usuarioPerfil && $grupoPerfil[1]) {
        // En el escritorio de un centro se muestra el centro que está activo, no la persona
        if ($tipoPerfil === 'institucion' && class_exists(\App\Support\UserCenterContext::class)) {
            try {
                $centroActivo = \App\Support\UserCenterContext::forAdmin($usuarioPerfil, request())['active'] ?? null;
                if (!empty($centroActivo['id_institucion'])) {
                    $fichaPerfil = \App\Models\Instituciones::find($centroActivo['id_institucion']);
                    $nombreEscritorio = $centroActivo['label'] ?? null;
                    $textoRol = !empty($centroActivo['role_label'])
                        ? mb_strtoupper(mb_substr($centroActivo['role_label'], 0, 1)) . mb_strtolower(mb_substr($centroActivo['role_label'], 1))
                        : $textoRol;
                }
            } catch (\Throwable $error) {
                $fichaPerfil = null;
            }
        }

        try {
            $fichaPerfil = $fichaPerfil ?: $grupoPerfil[1]::where('id_usuario', $usuarioPerfil->id)->first();
        } catch (\Throwable $error) {
            // Si la tabla no está disponible se usa el nombre de la cuenta
            $fichaPerfil = null;
        }
    }

    $partesCuenta = preg_split('/\s+/', trim($usuarioPerfil->name ?? ''));
    $nombreFicha = $fichaPerfil ? trim($fichaPerfil->{$grupoPerfil[2]} ?? '') : '';
    $apellidoFicha = ($fichaPerfil && $grupoPerfil[3]) ? trim($fichaPerfil->{$grupoPerfil[3]} ?? '') : '';

    if ($tipoPerfil === 'institucion' && $nombreFicha !== '') {
        // Las instituciones van con su nombre completo
        $nombreCorto = $nombreFicha;
    } else {
        // Solo el primer nombre y el primer apellido para que quepa en el encabezado
        $primerNombre = preg_split('/\s+/', $nombreFicha)[0] ?: ($partesCuenta[0] ?? '');
        $primerApellido = preg_split('/\s+/', $apellidoFicha)[0] ?: ($partesCuenta[1] ?? '');
        $nombreCorto = mb_convert_case(trim($primerNombre . ' ' . $primerApellido), MB_CASE_TITLE, 'UTF-8');
    }

    $archivoFoto = $fichaPerfil->foto_perfil ?? null;
    if (empty($archivoFoto) && $tipoPerfil === 'institucion') {
        $archivoFoto = $fichaPerfil->logo ?? null;
    }
    if (empty($archivoFoto)) {
        $archivoFoto = $usuarioPerfil->profile_photo_path ?? null;
    }

    // Mientras no haya foto propia se usa un avatar de muestra
    $fotoMuestra = 'https://i.pravatar.cc/120?u=' . ($usuarioPerfil->id ?? 0);
    $fotoPerfil = empty($archivoFoto)
        ? $fotoMuestra
        : (\Illuminate\Support\Str::startsWith($archivoFoto, ['http://', 'https://', '/']) ? $archivoFoto : asset('storage/' . $archivoFoto));
@endphp

<div class="dropdown drp-user perfil-desplegable">
    <a href="#" class="dropdown-toggle perfil-encabezado" data-toggle="dropdown" aria-label="Mi cuenta">
        <span class="perfil-encabezado-datos">
            <span class="perfil-encabezado-nombre">{{ $nombreCorto }}</span>
            <span class="perfil-encabezado-rol">{{ $textoRol }}</span>
        </span>
        <img class="perfil-encabezado-foto" src="{{ $fotoPerfil }}" alt="Foto de perfil"
            onerror="this.onerror=null;this.src='{{ $fotoMuestra }}';">
    </a>
    <div class="dropdown-menu dropdown-menu-right profile-notification">
        <div class="pro-head">
            @if ($tipoPerfil === 'institucion')
                {{-- En el centro se ve el centro completo y quién lo está usando --}}
                <span class="d-block font-weight-bold">{{ $nombreEscritorio ?: $nombreCorto }}</span>
                <small>{{ $textoRol }} · {{ $usuarioPerfil->name ?? '' }}</small>
            @else
                <span class="d-block font-weight-bold">{{ $usuarioPerfil->name ?? $nombreCorto }}</span>
                <small>{{ $textoRol }}</small>
            @endif
        </div>
        <ul class="pro-body">
            @if ($rutaPerfil)
                <li>
                    <a href="{{ $rutaPerfil }}" class="dropdown-item">
                        <i class="feather icon-user"></i> Mi perfil
                    </a>
                </li>
            @endif
            <li>
                <form action="{{ route('logout') }}" method="post" id="closeSession">
                    @csrf
                    <a class="dropdown-item text-danger" href="javascript:{}" onclick="document.getElementById('closeSession').submit();">
                        <i class="feather icon-power"></i> Cerrar sesión
                    </a>
                </form>
            </li>
        </ul>
    </div>
</div>

@once
<script>
    // Con mouse el menú del perfil se abre al pasar por encima; en pantallas touch se abre con un toque
    document.addEventListener('DOMContentLoaded', function () {
        var conMouse = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        if (!conMouse || !window.jQuery || !jQuery.fn.dropdown) {
            return;
        }

        document.querySelectorAll('.perfil-desplegable').forEach(function (contenedor) {
            var boton = contenedor.querySelector('.perfil-encabezado');
            var espera = null;
            if (!boton) {
                return;
            }

            contenedor.addEventListener('mouseenter', function () {
                clearTimeout(espera);
                if (!contenedor.classList.contains('show')) {
                    jQuery(boton).dropdown('show');
                }
            });

            // Pequeña espera para que no se cierre al bajar el mouse hacia el menú
            contenedor.addEventListener('mouseleave', function () {
                espera = setTimeout(function () {
                    if (contenedor.classList.contains('show')) {
                        jQuery(boton).dropdown('hide');
                    }
                }, 200);
            });

            // Si ya está abierto por el hover, el clic en el botón no lo cierra.
            // Se captura en el contenedor para adelantarse al clic de Bootstrap
            contenedor.addEventListener('click', function (evento) {
                if (boton.contains(evento.target) && contenedor.classList.contains('show')) {
                    evento.preventDefault();
                    evento.stopPropagation();
                }
            }, true);
        });
    });
</script>
@endonce
