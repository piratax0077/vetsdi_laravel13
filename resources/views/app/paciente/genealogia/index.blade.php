@extends('template.usuario.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="font-weight-bold mb-2 mb-md-0">Árbol genealógico de mascotas</h5>
                    <a href="{{ route('paciente.home') }}" class="btn btn-outline-light btn-sm">
                        <i class="feather icon-arrow-left mr-1"></i> Volver a mi escritorio
                    </a>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-info text-white"><h5 class="text-white mb-0">Mis mascotas</h5></div>
            <div class="card-body">
                <div class="row">
                    @forelse($mascotas as $mascota)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border">
                                <div class="card-body text-center">
                                    <h5 class="text-capitalize">{{ $mascota->nombre }}</h5>
                                    <p class="text-muted mb-3">{{ optional($mascota->especieMascota)->nombre ?? 'Sin especie' }} · {{ optional($mascota->razaMascota)->nombre ?? 'Sin raza' }}</p>
                                    <a class="btn btn-warning" href="{{ route('mascotas.genealogia.show', $mascota) }}">
                                        <i class="fas fa-sitemap"></i> Ver mi genealogía
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-info mb-0">No hay mascotas registradas.</div></div>
                    @endforelse
                </div>
                <a href="{{ route('paciente.mascotas.index') }}" class="btn btn-secondary mt-3">Volver a mis mascotas</a>
            </div>
        </div>
    </div>
</div>
@endsection
