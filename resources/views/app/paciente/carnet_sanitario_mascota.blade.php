@extends('template.usuario.template')

@section('page-styles')
<style>
    .carnet-shell { max-width: 1180px; margin: 0 auto; }
    .carnet-card { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 34px rgba(31, 62, 83, .13); }
    .carnet-header { background: linear-gradient(135deg, #137d73, #13b8b4); color: #fff; padding: 24px; }
    .carnet-photo { width: 88px; height: 88px; border-radius: 50%; object-fit: cover; border: 4px solid rgba(255,255,255,.85); }
    .carnet-meta { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
    .carnet-meta-item { padding: 12px 14px; border-radius: 12px; background: #f4f8fa; }
    .carnet-meta-item small { display: block; color: #758296; }
    .sanitario-title { color: #69379a; font-weight: 700; }
    .table-sanitario thead th { background: #eaf2f5; border: 0; color: #34465c; }
    .estado-proximo { color: #d67b00; font-weight: 600; }
    @media (max-width: 767px) {
        .carnet-meta { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .carnet-header { text-align: center; }
    }
    @media print {
        .pcoded-navbar, .navbar, .btn-print, .breadcrumb { display: none !important; }
        .pcoded-main-container { margin-left: 0 !important; }
        .pcoded-content { padding: 0 !important; }
        .carnet-card { box-shadow: none; border: 1px solid #dce4e8; }
    }
</style>
@endsection

@section('content')
@php
    $imgMascota = $mascota->sexo === 'M'
        ? asset('images/iconos/paciente-m.svg')
        : asset('images/iconos/paciente-f.svg');
    if (!empty($mascota->foto_perfil)) {
        $rutaFoto = str_replace('\\', '/', $mascota->foto_perfil);
        if (\Illuminate\Support\Str::startsWith($rutaFoto, ['http://', 'https://', '/'])) {
            $imgMascota = $rutaFoto;
        } elseif (\Illuminate\Support\Str::startsWith($rutaFoto, 'storage/')) {
            $imgMascota = asset($rutaFoto);
        } elseif (str_contains($rutaFoto, '/')) {
            $imgMascota = asset('storage/' . ltrim($rutaFoto, '/'));
        } else {
            $imgMascota = asset('storage/imagenes/temp/' . $rutaFoto);
        }
    }
    $especie = optional($mascota->especieMascota)->nombre ?: ($mascota->otra_especie ?: 'Sin registro');
    $raza = optional($mascota->razaMascota)->nombre ?: 'Sin registro';
@endphp
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="carnet-shell">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('paciente.mascotas.index') }}" class="btn btn-outline-secondary">
                    <i class="feather icon-arrow-left"></i> Mis mascotas
                </a>
                <button type="button" class="btn btn-info btn-print" onclick="window.print()">
                    <i class="feather icon-printer"></i> Imprimir carné
                </button>
            </div>

            <div class="card carnet-card">
                <div class="carnet-header">
                    <div class="d-flex flex-column flex-md-row align-items-center">
                        <img src="{{ $imgMascota }}" alt="Foto de {{ $mascota->nombre }}" class="carnet-photo mr-md-4">
                        <div class="mt-3 mt-md-0">
                            <div class="text-uppercase small">VET SDI · Carné sanitario veterinario</div>
                            <h2 class="mb-1 text-white">{{ $mascota->nombre }}</h2>
                            <div>{{ $especie }} · {{ $raza }}</div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="carnet-meta mb-4">
                        <div class="carnet-meta-item"><small>Tutor responsable</small><strong>{{ trim($paciente->nombres.' '.$paciente->apellido_uno.' '.$paciente->apellido_dos) }}</strong></div>
                        <div class="carnet-meta-item"><small>Microchip</small><strong>{{ $mascota->chip ?: 'Sin registro' }}</strong></div>
                        <div class="carnet-meta-item"><small>Fecha de nacimiento</small><strong>{{ $mascota->fecha_nacimiento ? $mascota->fecha_nacimiento->format('d-m-Y') : 'Sin registro' }}</strong></div>
                        <div class="carnet-meta-item"><small>Sexo</small><strong>{{ $mascota->sexo === 'M' ? 'Macho' : ($mascota->sexo === 'F' ? 'Hembra' : 'Sin registro') }}</strong></div>
                    </div>

                    <h4 class="sanitario-title"><i class="fas fa-syringe mr-2"></i>Registro de vacunas</h4>
                    <div class="table-responsive mb-4">
                        <table class="table table-sanitario table-striped">
                            <thead><tr><th>Fecha</th><th>Vacuna</th><th>Edad</th><th>Próxima dosis</th></tr></thead>
                            <tbody>
                            @forelse($vacunas as $vacuna)
                                <tr>
                                    <td>{{ !empty($vacuna['fecha_dosis']) ? \Carbon\Carbon::parse($vacuna['fecha_dosis'])->format('d-m-Y') : '-' }}</td>
                                    <td><strong>{{ $vacuna['vacuna'] ?? '-' }}</strong></td>
                                    <td>{{ $vacuna['edad'] ?? '-' }}</td>
                                    <td class="estado-proximo">{{ !empty($vacuna['proxima_dosis']) ? \Carbon\Carbon::parse($vacuna['proxima_dosis'])->format('d-m-Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No hay vacunas registradas.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h4 class="sanitario-title"><i class="fas fa-shield-alt mr-2"></i>Registro de desparasitación</h4>
                    <div class="table-responsive">
                        <table class="table table-sanitario table-striped">
                            <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo / vía</th><th>Dosis</th><th>Próxima dosis</th></tr></thead>
                            <tbody>
                            @forelse($desparasitaciones as $registro)
                                <tr>
                                    <td>{{ !empty($registro['fecha_dosis']) ? \Carbon\Carbon::parse($registro['fecha_dosis'])->format('d-m-Y') : '-' }}</td>
                                    <td><strong>{{ $registro['antiparasitario'] ?? '-' }}</strong></td>
                                    <td>{{ $registro['tipo'] ?? '-' }}{{ !empty($registro['via']) ? ' / '.$registro['via'] : '' }}</td>
                                    <td>{{ $registro['dosis'] ?? '-' }}</td>
                                    <td class="estado-proximo">{{ !empty($registro['proxima_dosis']) ? \Carbon\Carbon::parse($registro['proxima_dosis'])->format('d-m-Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No hay desparasitaciones registradas.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0 mt-3">Información obtenida de los registros sanitarios reales de la mascota en VET SDI.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
