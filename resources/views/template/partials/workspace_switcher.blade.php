@php
    $workspaceOptions = collect();
    $workspaceUser = Auth::user();

    if ($workspaceUser) {
        $professionalActiveContext = null;
        $adminActiveContext = null;

        if (class_exists(\App\Support\UserCenterContext::class)) {
            $professionalContext = \App\Support\UserCenterContext::forProfessional($workspaceUser, request());
            $professionalActiveContext = $professionalContext['active'] ?? null;

            $adminContext = \App\Support\UserCenterContext::forAdmin($workspaceUser, request());
            $adminActiveContext = $adminContext['active'] ?? null;
        }

        if (\Route::has('paciente.home')) {
            $workspaceOptions->push([
                'key' => 'paciente',
                'label' => 'Mis mascotas',
                'url' => route('paciente.home'),
                'note' => 'Escritorio de tutor',
                'icon' => 'icon-heart',
            ]);
        }

        $hasProfessionalWorkspace = $workspaceUser->hasRole('Profesional')
            || $workspaceUser->hasRole('Admin')
            || \App\Models\Profesional::where('id_usuario', $workspaceUser->id)->exists();

        if ($hasProfessionalWorkspace && \Route::has('profesional.home')) {
            $workspaceOptions->push([
                'key' => 'profesional',
                'label' => 'Escritorio profesional',
                'url' => route('profesional.home', array_filter([
                    'contexto' => $professionalActiveContext['key'] ?? null,
                ])),
                'note' => $professionalActiveContext['label'] ?? null,
                'icon' => 'icon-briefcase',
            ]);
        }

        if (($workspaceUser->hasRole('Asistente') || $workspaceUser->hasRole('Admin')) && \Route::has('asistente.home')) {
            $workspaceOptions->push([
                'key' => 'asistente',
                'label' => 'Escritorio Asistente',
                'url' => route('asistente.home'),
                'note' => null,
            ]);
        }

        if (($workspaceUser->hasRole('AsistenteCaja') || $workspaceUser->hasRole('AsistenteLaboratorio') || $workspaceUser->hasRole('Admin')) && \Route::has('asistentecm.home')) {
            $workspaceOptions->push([
                'key' => 'asistente_cm',
                'label' => 'Escritorio Asistente Centro Veterinario',
                'url' => route('asistentecm.home'),
                'note' => null,
            ]);
        }

        if (($workspaceUser->hasRole('AsistenteManejoAgenda') || $workspaceUser->hasRole('Admin')) && \Route::has('asistentecm.ma.home')) {
            $workspaceOptions->push([
                'key' => 'asistente_cm_ma',
                'label' => 'Escritorio Asistente Manejo Agenda',
                'url' => route('asistentecm.ma.home'),
                'note' => null,
            ]);
        }

        if (($workspaceUser->hasRole('AsistenteJefaCaja') || $workspaceUser->hasRole('Admin')) && \Route::has('asistentejcm.home')) {
            $workspaceOptions->push([
                'key' => 'asistente_jefa_caja',
                'label' => 'Escritorio Jefatura de Caja',
                'url' => route('asistentejcm.home'),
                'note' => null,
            ]);
        }

        if (($workspaceUser->hasRole('AsistenteOnline') || $workspaceUser->hasRole('Admin')) && \Route::has('asistenteon.home')) {
            $workspaceOptions->push([
                'key' => 'asistente_online',
                'label' => 'Escritorio Asistente Online',
                'url' => route('asistenteon.home'),
                'note' => null,
            ]);
        }

        if ($adminActiveContext && \Route::has('adm_cm.home')) {
            $workspaceOptions->push([
                'key' => 'adm_cm',
                'label' => 'Escritorio Centro Veterinario',
                'url' => route('adm_cm.home', array_filter([
                    'contexto' => $adminActiveContext['key'] ?? null,
                ])),
                'note' => $adminActiveContext['label'] ?? null,
            ]);
        }

        $workspaceOptions = $workspaceOptions->unique('key')->values();
    }
@endphp

@if ($workspaceOptions->count() > 1)
    <li>
        <div class="dropdown drp-user">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Cambiar entre Veterinario y Tutor" data-placement="button" aria-label="Cambiar escritorio">
                <i class="feather icon-refresh-cw" style="font-size: 1.2rem!important;"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification">
                <div class="pro-head font-weight-bold f-16 py-2">
                    <span>Cambiar escritorio</span>
                </div>
                <ul></ul>
                <ul class="pro-body">
                    @foreach ($workspaceOptions as $workspaceOption)
                        <li>
                            <a href="{{ $workspaceOption['url'] }}" class="dropdown-item">
                                <i class="feather {{ $workspaceOption['icon'] ?? 'icon-user' }}"></i>
                                {{ $workspaceOption['label'] }}
                                @if (!empty($workspaceOption['note']))
                                    <small class="d-block text-muted pl-4">{{ $workspaceOption['note'] }}</small>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </li>
@endif
