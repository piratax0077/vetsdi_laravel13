@php
    use App\Http\Controllers\SeleccionCuentaController;
    use App\Services\CuentasService;

    /*
     * Selector de cuenta del encabezado.
     *
     * Primero lista los roles del modelo de cuentas (tutor, profesional,
     * asistente y clínica). Después agrega los escritorios antiguos que ese
     * modelo todavía no cubre, para no dejar sin acceso a quien los usa.
     * Cambiar de escritorio no vuelve a pedir la contraseña: el servidor igual
     * revisa el rol y el perfil en cada ruta.
     */
    $workspaceOptions = collect();
    $workspaceUser = Auth::user();
    $rolActivo = session(SeleccionCuentaController::SESION_ROL_ACTIVO);
    $nombreRolActivo = null;

    if ($workspaceUser) {
        $cuentas = app(CuentasService::class);

        foreach ($cuentas->rolesActivos($workspaceUser) as $rolCuenta) {
            $esActivo = $rolActivo === $rolCuenta->tipo;

            if ($esActivo) {
                $nombreRolActivo = CuentasService::ETIQUETAS[$rolCuenta->tipo];
            }

            $workspaceOptions->push([
                'key' => $rolCuenta->tipo,
                'label' => CuentasService::ETIQUETAS[$rolCuenta->tipo],
                'url' => route('cuenta.entrar', ['tipo' => $rolCuenta->tipo]),
                'note' => $rolCuenta->perfil_completo ? null : 'Perfil por completar',
                'icon' => 'icon-user',
                'activo' => $esActivo,
            ]);
        }

        // Escritorios antiguos que el modelo de cuentas todavía no representa.
        $escritoriosAntiguos = [
            ['rol' => 'AsistenteCaja', 'ruta' => 'asistentecm.home', 'label' => 'Escritorio Asistente Centro Veterinario'],
            ['rol' => 'AsistenteLaboratorio', 'ruta' => 'asistente.lab.home', 'label' => 'Escritorio Asistente Laboratorio'],
            ['rol' => 'AsistenteManejoAgenda', 'ruta' => 'asistentecm.ma.home', 'label' => 'Escritorio Asistente Manejo Agenda'],
            ['rol' => 'AsistenteJefaCaja', 'ruta' => 'asistentejcm.home', 'label' => 'Escritorio Jefatura de Caja'],
            ['rol' => 'AsistenteOnline', 'ruta' => 'asistenteon.home', 'label' => 'Escritorio Asistente Online'],
            ['rol' => 'Contador', 'ruta' => 'contabilidad.home', 'label' => 'Escritorio Contabilidad'],
            ['rol' => 'AdministradorLaboratorio', 'ruta' => 'laboratorio.adm_general.home', 'label' => 'Escritorio Laboratorio'],
        ];

        foreach ($escritoriosAntiguos as $escritorio) {
            if ($workspaceUser->hasRole($escritorio['rol']) && \Route::has($escritorio['ruta'])) {
                $workspaceOptions->push([
                    'key' => $escritorio['ruta'],
                    'label' => $escritorio['label'],
                    'url' => route($escritorio['ruta']),
                    'note' => null,
                    'icon' => 'icon-grid',
                    'activo' => false,
                ]);
            }
        }

        $workspaceOptions = $workspaceOptions->unique('key')->values();
    }
@endphp

@if ($workspaceOptions->count() > 1)
    <li>
        <div class="dropdown drp-user">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Cambiar de escritorio" data-placement="button" aria-label="Cambiar de escritorio">
                <i class="feather icon-refresh-cw icono-header"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification">
                <div class="pro-head font-weight-bold f-16 py-2">
                    <span>{{ $workspaceUser->nombreParaMostrar() }}</span>
                    @if ($nombreRolActivo)
                        <small class="d-block font-weight-normal">Estás en: {{ $nombreRolActivo }}</small>
                    @endif
                </div>
                <ul></ul>
                <ul class="pro-body">
                    @foreach ($workspaceOptions as $workspaceOption)
                        <li>
                            <a href="{{ $workspaceOption['url'] }}" class="dropdown-item" @if($workspaceOption['activo']) aria-current="true" @endif>
                                <i class="feather {{ $workspaceOption['icon'] ?? 'icon-user' }}"></i>
                                {{ $workspaceOption['label'] }}
                                @if ($workspaceOption['activo'])
                                    <i class="feather icon-check text-success ml-1" aria-label="Escritorio actual"></i>
                                @endif
                                @if (!empty($workspaceOption['note']))
                                    <small class="d-block text-muted pl-4">{{ $workspaceOption['note'] }}</small>
                                @endif
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('cuenta.seleccion') }}" class="dropdown-item">
                            <i class="feather icon-grid"></i> Cambiar de escritorio
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </li>
@endif
