@extends('template.paciente_dependiente.template')
@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            @include('template.partials.boton_volver', ['url' => route('paciente.dependiente.receta', ['id_dependiente_activo' => $id_dependiente_activo])])
            <!--Header-->
            <div class="row">
            <div class="col-md-12 mb-3">
                <h5 class="f-26 d-inline">Mis recetas</h5>
            </div>
        </div>
        <!--Cierre: Header-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 col-md-12">
                                    <table id="tabla_recetas_paciente_ro"
                                        class="display table table-striped table-hover dt-responsive nowrap table-sm"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-left align-middle">Fecha</th>
                                                <th class="text-left align-middle">Profesional</th>
                                                <th class="text-left align-middle">Diagnóstico</th>
                                                <th class="text-left align-middle">Estado</th>
                                                <th class="text-left align-middle">Receta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($fichas))
                                                @foreach ($fichas as $f)
                                                    @if ($f->RecetasEmitidas->isNotEmpty() || $f->Recetas->isNotEmpty())
                                                        <tr>
                                                            <td class="text-wrap text-left align-middle">
                                                                {{ \Carbon\Carbon::parse($f->RecetasEmitidas->first()?->created_at ?? $f->Recetas->first()?->created_at)->format('d/m/Y') }}
                                                            </td>
                                                            <td class="align-middle text-left">
                                                                <strong>{{ $f->Profesional->nombre ?? '' }}
                                                                    {{ $f->Profesional->apellido_uno ?? '' }}
                                                                    {{ $f->Profesional->apellido_dos ?? '' }}
                                                                </strong>
                                                                <br>
                                                                {{ $f->Profesional?->Especialidad?->nombre ?? 'Veterinaria' }}
                                                            </td>
                                                            <td class="align-middle text-left">{{ $f->hipotesis_diagnostico }}</td>
                                                            <td class="align-middle text-left">Emitida</td>
                                                            <td class="text-left align-middle">
                                                                <a href="{{ route('mascota.documento_emitido.ver', ['tipo' => 'receta', 'id_ficha_atencion' => $f->id]) }}" target="_blank" rel="noopener" class="btn btn-danger btn-sm">
                                                                    <i class="feather icon-file-plus"></i> Ver Receta
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Cierre: Container Completo-->
@endsection
@section('page-script')
    <script>
        $(document).ready(function() {
            $('#tabla_recetas_paciente_ro').DataTable({
                responsive: true,
            });
        });
    </script>
@endsection
