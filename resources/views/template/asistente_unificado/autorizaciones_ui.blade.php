@php
    $contextoUi = session('asistente_contexto', []);
    $asistenteUi = \App\Models\Asistente::where('id_usuario', auth()->id())->first();
    $permisosUi = $asistenteUi && !empty($contextoUi['id_profesional'])
        ? app(\App\Services\AutorizacionesAsistentes::class)->permisos($asistenteUi->id, (int) $contextoUi['id_lugar_atencion'], (int) $contextoUi['id_profesional']) : [];
    $rutasUi = [];
    foreach (\Illuminate\Support\Facades\Route::getRoutes() as $rutaUi) {
        foreach (config('asistentes.rutas') as $patronUi => $permisoUi) {
            if ($rutaUi->getName() && \Illuminate\Support\Str::is($patronUi, $rutaUi->getName()) && !str_contains($rutaUi->uri(), '{')) {
                $rutasUi[route($rutaUi->getName())] = $permisoUi;
                break;
            }
        }
    }
@endphp
<script>
    window.asistenteAutorizaciones = @json($permisosUi);
    window.asistenteRutasAutorizadas = @json($rutasUi);
</script>
<script src="{{ asset('js/asistente-autorizaciones.js') }}"></script>
